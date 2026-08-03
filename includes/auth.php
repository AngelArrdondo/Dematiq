<?php
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/totp.php';

define('MAX_INTENTOS',        5);
define('BLOQUEO_MINUTOS',     15);
define('SESSION_HORAS',       8);
define('IP_MAX_FALLOS',       20);  // máximo de fallos por IP en 15 min
define('PENDIENTE_2FA_MINUTOS', 10);
define('MAX_INTENTOS_2FA',    6);   // por sesión pendiente — ya se pasó la contraseña, solo frena fuerza bruta del código

class Auth {

    // ── Login ──────────────────────────────────────────────────────────
    public static function login(string $username, string $password, string $ip, string $ua, bool $remember = false): array {

        global $pdo;

        $username = trim($username);

        // 1. Rate limiting por IP — bloquea ataques de diccionario contra múltiples usuarios
        $stmt = $pdo->prepare(
            'SELECT COUNT(*) AS total,
                    TIMESTAMPDIFF(SECOND, NOW(), DATE_ADD(MIN(creado_en), INTERVAL ? MINUTE)) AS retry_after
             FROM log_accesos
             WHERE ip = ? AND resultado = "fallo"
               AND creado_en > DATE_SUB(NOW(), INTERVAL ? MINUTE)'
        );
        $stmt->execute([BLOQUEO_MINUTOS, $ip, BLOQUEO_MINUTOS]);
        $ipRow = $stmt->fetch();
        if ($ipRow['total'] >= IP_MAX_FALLOS) {
            return [
                'ok'          => false,
                'msg'         => 'Demasiados intentos desde tu red. Espera',
                'retry_after' => max(1, (int) $ipRow['retry_after']),
            ];
        }

        // 2. Buscar usuario
        try {
            $stmt = $pdo->prepare(
                'SELECT id, username, password_hash, nombre, activo, intentos_fallidos, bloqueado_hasta,
                        totp_habilitado
                 FROM usuarios WHERE username = ? LIMIT 1'
            );
            $stmt->execute([$username]);
            $user = $stmt->fetch();
        } catch (PDOException $e) {
            // Migración 008_2fa.sql aún no aplicada — no bloquear el login por eso
            error_log('Auth::login (sin columnas 2FA): ' . $e->getMessage());
            $stmt = $pdo->prepare(
                'SELECT id, username, password_hash, nombre, activo, intentos_fallidos, bloqueado_hasta
                 FROM usuarios WHERE username = ? LIMIT 1'
            );
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            if ($user) $user['totp_habilitado'] = 0;
        }

        // 3. Usuario no existe
        if (!$user) {
            self::registrarLog(null, $username, $ip, $ua, 'fallo');
            return ['ok' => false, 'msg' => 'Credenciales incorrectas.'];
        }

        // 4. Cuenta inactiva
        if (!$user['activo']) {
            self::registrarLog($user['id'], $username, $ip, $ua, 'bloqueado');
            return ['ok' => false, 'msg' => 'Cuenta desactivada. Contacta al administrador.'];
        }

        // 5. Bloqueo temporal por intentos fallidos
        if ($user['bloqueado_hasta'] && new DateTime() < new DateTime($user['bloqueado_hasta'])) {
            self::registrarLog($user['id'], $username, $ip, $ua, 'bloqueado');
            $retryAfter = max(1, strtotime($user['bloqueado_hasta']) - time());
            return [
                'ok'          => false,
                'msg'         => 'Cuenta bloqueada. Intenta de nuevo en',
                'retry_after' => $retryAfter,
            ];
        }

        // 6. Verificar contraseña
        if (!password_verify($password, $user['password_hash'])) {
            $intentos = $user['intentos_fallidos'] + 1;
            $bloqueo  = null;

            if ($intentos >= MAX_INTENTOS) {
                $bloqueo  = date('Y-m-d H:i:s', strtotime('+' . BLOQUEO_MINUTOS . ' minutes'));
                $intentos = 0;
            }

            $pdo->prepare(
                'UPDATE usuarios SET intentos_fallidos = ?, bloqueado_hasta = ? WHERE id = ?'
            )->execute([$intentos, $bloqueo, $user['id']]);

            self::registrarLog($user['id'], $username, $ip, $ua, 'fallo');

            $restantes = MAX_INTENTOS - $intentos;
            $msg = $bloqueo
                ? 'Demasiados intentos. Cuenta bloqueada. Intenta de nuevo en'
                : "Credenciales incorrectas. Intentos restantes: {$restantes}.";

            return [
                'ok'          => false,
                'msg'         => $msg,
                'retry_after' => $bloqueo ? BLOQUEO_MINUTOS * 60 : null,
            ];
        }

        // 7. Contraseña correcta — resetear intentos fallidos
        $pdo->prepare(
            'UPDATE usuarios SET intentos_fallidos = 0, bloqueado_hasta = NULL, ultimo_acceso = NOW() WHERE id = ?'
        )->execute([$user['id']]);

        // 8. Si tiene 2FA activo, no crear la sesión todavía — dejar un
        // estado "pendiente" de unos minutos y pedir el código TOTP.
        if (!empty($user['totp_habilitado'])) {
            $ptoken = bin2hex(random_bytes(32));
            $pexpira = date('Y-m-d H:i:s', strtotime('+' . PENDIENTE_2FA_MINUTOS . ' minutes'));

            $pdo->prepare(
                'INSERT INTO sesiones_2fa_pendientes (usuario_id, token, ip, user_agent, recordar, expira_en)
                 VALUES (?, ?, ?, ?, ?, ?)'
            )->execute([$user['id'], $ptoken, $ip, $ua, $remember ? 1 : 0, $pexpira]);

            setcookie('dematiq_2fa_pending', $ptoken, [
                'expires'  => strtotime('+' . PENDIENTE_2FA_MINUTOS . ' minutes'),
                'path'     => '/pages/corporativo',
                'httponly' => true,
                'samesite' => 'Strict',
                'secure'   => isset($_SERVER['HTTPS']),
            ]);

            return ['ok' => true, 'need2fa' => true];
        }

        self::registrarLog($user['id'], $username, $ip, $ua, 'exito');
        return array_merge(['ok' => true], self::crearSesion($user['id'], $user['nombre']));
    }

    // ── Crea la sesión real (token + cookie) tras un login completo ─────
    private static function crearSesion(int $userId, string $nombre): array {
        global $pdo;

        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);

        $token  = bin2hex(random_bytes(32));
        $expira = date('Y-m-d H:i:s', strtotime('+' . SESSION_HORAS . ' hours'));

        $pdo->prepare(
            'INSERT INTO sesiones (usuario_id, token, ip, user_agent, expira_en)
             VALUES (?, ?, ?, ?, ?)'
        )->execute([$userId, $token, $ip, $ua, $expira]);

        setcookie('dematiq_session', $token, [
            'expires'  => strtotime('+' . SESSION_HORAS . ' hours'),
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Strict',
            'secure'   => isset($_SERVER['HTTPS']),
        ]);

        return ['nombre' => $nombre];
    }

    // ── Segundo paso del login: verifica el código TOTP (o de recuperación) ──
    public static function verify2fa(string $codigo): array {
        global $pdo;

        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);

        $ptoken = $_COOKIE['dematiq_2fa_pending'] ?? '';
        if (!$ptoken) {
            return ['ok' => false, 'msg' => 'Tu sesión de verificación expiró. Inicia sesión de nuevo.'];
        }

        $stmt = $pdo->prepare(
            'SELECT p.id, p.usuario_id, p.intentos, p.recordar, u.username, u.nombre, u.totp_secret
             FROM sesiones_2fa_pendientes p
             JOIN usuarios u ON u.id = p.usuario_id
             WHERE p.token = ? AND p.expira_en > NOW() LIMIT 1'
        );
        $stmt->execute([$ptoken]);
        $pend = $stmt->fetch();

        if (!$pend) {
            self::limpiarPendiente2fa();
            return ['ok' => false, 'msg' => 'Tu sesión de verificación expiró. Inicia sesión de nuevo.'];
        }

        if ($pend['intentos'] >= MAX_INTENTOS_2FA) {
            $pdo->prepare('DELETE FROM sesiones_2fa_pendientes WHERE id = ?')->execute([$pend['id']]);
            self::limpiarPendiente2fa();
            return ['ok' => false, 'msg' => 'Demasiados intentos. Inicia sesión de nuevo.'];
        }

        $valido = false;

        // Código de recuperación (formato XXXX-XXXX) o código TOTP de 6 dígitos
        if (preg_match('/^[A-F0-9]{4}-[A-F0-9]{4}$/i', trim($codigo))) {
            $hash = hash('sha256', strtoupper(trim($codigo)));
            $rstmt = $pdo->prepare(
                'SELECT id FROM usuario_codigos_recuperacion
                 WHERE usuario_id = ? AND codigo_hash = ? AND usado = 0 LIMIT 1'
            );
            $rstmt->execute([$pend['usuario_id'], $hash]);
            $rcode = $rstmt->fetch();
            if ($rcode) {
                $pdo->prepare('UPDATE usuario_codigos_recuperacion SET usado = 1 WHERE id = ?')->execute([$rcode['id']]);
                $valido = true;
            }
        } elseif ($pend['totp_secret']) {
            $valido = Totp::verificar($pend['totp_secret'], $codigo);
        }

        if (!$valido) {
            $pdo->prepare('UPDATE sesiones_2fa_pendientes SET intentos = intentos + 1 WHERE id = ?')->execute([$pend['id']]);
            self::registrarLog($pend['usuario_id'], $pend['username'], $ip, $ua, 'fallo');
            $restantes = MAX_INTENTOS_2FA - $pend['intentos'] - 1;
            return ['ok' => false, 'msg' => "Código inválido. Intentos restantes: {$restantes}."];
        }

        $pdo->prepare('DELETE FROM sesiones_2fa_pendientes WHERE id = ?')->execute([$pend['id']]);
        self::limpiarPendiente2fa();
        self::registrarLog($pend['usuario_id'], $pend['username'], $ip, $ua, 'exito');

        return array_merge(
            ['ok' => true, 'recordar' => (bool) $pend['recordar'], 'username' => $pend['username']],
            self::crearSesion($pend['usuario_id'], $pend['nombre'])
        );
    }

    // ── ¿Hay un login a medias esperando el código 2FA? ─────────────────
    public static function pendiente2fa(): ?array {
        global $pdo;

        $ptoken = $_COOKIE['dematiq_2fa_pending'] ?? '';
        if (!$ptoken) return null;

        $stmt = $pdo->prepare(
            'SELECT u.username FROM sesiones_2fa_pendientes p
             JOIN usuarios u ON u.id = p.usuario_id
             WHERE p.token = ? AND p.expira_en > NOW() LIMIT 1'
        );
        $stmt->execute([$ptoken]);
        $row = $stmt->fetch();
        if (!$row) {
            self::limpiarPendiente2fa();
            return null;
        }
        return $row;
    }

    private static function limpiarPendiente2fa(): void {
        setcookie('dematiq_2fa_pending', '', [
            'expires'  => time() - 3600,
            'path'     => '/pages/corporativo',
            'httponly' => true,
            'samesite' => 'Strict',
            'secure'   => isset($_SERVER['HTTPS']),
        ]);
    }

    // ── Activar 2FA: genera un secreto pendiente de confirmar ───────────
    public static function setup2FA(int $userId): array {
        global $pdo;
        $stmt = $pdo->prepare('SELECT username FROM usuarios WHERE id = ? LIMIT 1');
        $stmt->execute([$userId]);
        $username = $stmt->fetchColumn();
        if (!$username) return ['ok' => false, 'msg' => 'Usuario no encontrado'];

        $secreto = Totp::generarSecreto();
        $pdo->prepare('UPDATE usuarios SET totp_secret_pendiente = ? WHERE id = ?')->execute([$secreto, $userId]);

        return [
            'ok'      => true,
            'secreto' => $secreto,
            'otpauth' => Totp::otpauthUri($secreto, $username),
        ];
    }

    // ── Confirmar 2FA con el primer código generado por la app ──────────
    public static function confirm2FA(int $userId, string $codigo): array {
        global $pdo;
        $stmt = $pdo->prepare('SELECT totp_secret_pendiente FROM usuarios WHERE id = ? LIMIT 1');
        $stmt->execute([$userId]);
        $secreto = $stmt->fetchColumn();

        if (!$secreto) {
            return ['ok' => false, 'msg' => 'No hay una activación de 2FA en curso. Vuelve a empezar.'];
        }
        if (!Totp::verificar($secreto, $codigo)) {
            return ['ok' => false, 'msg' => 'Código incorrecto. Revisa la hora de tu teléfono e intenta de nuevo.'];
        }

        $pdo->prepare(
            'UPDATE usuarios SET totp_secret = ?, totp_secret_pendiente = NULL, totp_habilitado = 1 WHERE id = ?'
        )->execute([$secreto, $userId]);

        return ['ok' => true, 'codigos' => self::generarCodigosRecuperacion($userId)];
    }

    // ── Desactivar 2FA (requiere confirmar la contraseña actual) ────────
    public static function disable2FA(int $userId, string $password): array {
        global $pdo;
        $stmt = $pdo->prepare('SELECT password_hash FROM usuarios WHERE id = ? LIMIT 1');
        $stmt->execute([$userId]);
        $hash = $stmt->fetchColumn();

        if (!$hash || !password_verify($password, $hash)) {
            return ['ok' => false, 'msg' => 'Contraseña incorrecta'];
        }

        $pdo->prepare(
            'UPDATE usuarios SET totp_secret = NULL, totp_secret_pendiente = NULL, totp_habilitado = 0 WHERE id = ?'
        )->execute([$userId]);
        $pdo->prepare('DELETE FROM usuario_codigos_recuperacion WHERE usuario_id = ?')->execute([$userId]);

        return ['ok' => true];
    }

    public static function regenerarCodigosRecuperacion(int $userId): array {
        return ['ok' => true, 'codigos' => self::generarCodigosRecuperacion($userId)];
    }

    private static function generarCodigosRecuperacion(int $userId, int $cantidad = 8): array {
        global $pdo;
        $pdo->prepare('DELETE FROM usuario_codigos_recuperacion WHERE usuario_id = ?')->execute([$userId]);

        $stmt = $pdo->prepare(
            'INSERT INTO usuario_codigos_recuperacion (usuario_id, codigo_hash) VALUES (?, ?)'
        );
        $codigos = [];
        for ($i = 0; $i < $cantidad; $i++) {
            $plano = strtoupper(bin2hex(random_bytes(4)));
            $formateado = substr($plano, 0, 4) . '-' . substr($plano, 4, 4);
            $stmt->execute([$userId, hash('sha256', $formateado)]);
            $codigos[] = $formateado;
        }
        return $codigos;
    }

    // ── Verificar sesión activa ────────────────────────────────────────
    public static function check(): ?array {
        global $pdo;

        $token = $_COOKIE['dematiq_session'] ?? '';
        if (!$token) return null;

        $stmt = $pdo->prepare(
            'SELECT u.id, u.username, u.nombre, s.user_agent
             FROM sesiones s
             JOIN usuarios u ON u.id = s.usuario_id
             WHERE s.token = ? AND s.expira_en > NOW() AND u.activo = 1
             LIMIT 1'
        );
        $stmt->execute([$token]);
        $row = $stmt->fetch();
        if (!$row) return null;

        // Verificar que el User-Agent no cambió (vinculación de sesión)
        $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);
        if ($row['user_agent'] !== $ua) {
            return null;
        }

        return ['id' => $row['id'], 'username' => $row['username'], 'nombre' => $row['nombre']];
    }

    // ── Requerir login (redirige si no hay sesión) ─────────────────────
    public static function require(string $redirect = '/pages/corporativo/login.php', bool $checkProfile = true): array {
        $user = self::check();
        if (!$user) {
            header('Location: ' . $redirect);
            exit;
        }
        self::csrfToken(); // asegura que la cookie CSRF exista para el JS
        if ($checkProfile && !self::isProfileComplete($user['id'])) {
            header('Location: /admin/complete-profile.php');
            exit;
        }
        return $user;
    }

    // ── Verificar si el perfil del usuario está completo ───────────────
    public static function isProfileComplete(int $userId): bool {
        global $pdo;
        try {
            $stmt = $pdo->prepare(
                'SELECT primer_nombre, apellido_paterno, apellido_materno, email_contacto, telefono
                 FROM usuarios WHERE id = ? LIMIT 1'
            );
            $stmt->execute([$userId]);
            $row = $stmt->fetch();
            if (!$row) return false;
            return $row['primer_nombre'] !== '' && $row['apellido_paterno'] !== ''
                && !empty($row['apellido_materno']) && !empty($row['email_contacto'])
                && !empty($row['telefono']);
        } catch (PDOException $e) {
            error_log('isProfileComplete: ' . $e->getMessage());
            return true; // columnas no migradas aún — no bloquear el login
        }
    }

    // ── Cerrar sesión ──────────────────────────────────────────────────
    public static function logout(): void {
        global $pdo;

        $token = $_COOKIE['dematiq_session'] ?? '';
        if ($token) {
            $pdo->prepare('DELETE FROM sesiones WHERE token = ?')->execute([$token]);
        }

        setcookie('dematiq_session', '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Strict',
            'secure'   => isset($_SERVER['HTTPS']),
        ]);
    }

    // ── Invalidar todas las sesiones del usuario (excepto la actual) ───
    public static function invalidarOtrasSesiones(int $userId): void {
        global $pdo;
        $current = $_COOKIE['dematiq_session'] ?? '';
        $pdo->prepare(
            'DELETE FROM sesiones WHERE usuario_id = ? AND token != ?'
        )->execute([$userId, $current]);
    }

    // ── CSRF — genera y devuelve el token (double-submit cookie) ───────
    public static function csrfToken(): string {
        $token = $_COOKIE['dematiq_csrf'] ?? '';
        if (strlen($token) !== 64) {
            $token = bin2hex(random_bytes(32));
            setcookie('dematiq_csrf', $token, [
                'expires'  => 0,
                'path'     => '/admin',
                'httponly' => false,     // JS necesita leerlo
                'samesite' => 'Strict',
                'secure'   => isset($_SERVER['HTTPS']),
            ]);
            $_COOKIE['dematiq_csrf'] = $token;
        }
        return $token;
    }

    // ── CSRF — verifica que el token enviado coincide con la cookie ────
    public static function csrfVerify(): bool {
        $cookie = $_COOKIE['dematiq_csrf'] ?? '';
        $header = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        $post   = $_POST['_csrf']              ?? '';
        $sent   = $header ?: $post;
        return $cookie !== '' && $sent !== '' && hash_equals($cookie, $sent);
    }

    // ── Registro de auditoría ──────────────────────────────────────────
    private static function registrarLog(?int $uid, string $user, string $ip, string $ua, string $res): void {
        global $pdo;
        $pdo->prepare(
            'INSERT INTO log_accesos (usuario_id, username, ip, user_agent, resultado)
             VALUES (?, ?, ?, ?, ?)'
        )->execute([$uid, $user, $ip, $ua, $res]);
    }
}
