<?php
require_once __DIR__ . '/conexion.php';

define('MAX_INTENTOS',     5);
define('BLOQUEO_MINUTOS',  15);
define('SESSION_HORAS',    8);
define('IP_MAX_FALLOS',    20);  // máximo de fallos por IP en 15 min

class Auth {

    // ── Login ──────────────────────────────────────────────────────────
    public static function login(string $username, string $password, string $ip, string $ua): array {

        global $pdo;

        $username = trim($username);

        // 1. Rate limiting por IP — bloquea ataques de diccionario contra múltiples usuarios
        $stmt = $pdo->prepare(
            'SELECT COUNT(*) FROM log_accesos
             WHERE ip = ? AND resultado = "fallo"
               AND creado_en > DATE_SUB(NOW(), INTERVAL ? MINUTE)'
        );
        $stmt->execute([$ip, BLOQUEO_MINUTOS]);
        if ($stmt->fetchColumn() >= IP_MAX_FALLOS) {
            return ['ok' => false, 'msg' => 'Demasiados intentos desde tu red. Espera ' . BLOQUEO_MINUTOS . ' minutos.'];
        }

        // 2. Buscar usuario
        $stmt = $pdo->prepare(
            'SELECT id, password_hash, nombre, activo, intentos_fallidos, bloqueado_hasta
             FROM usuarios WHERE username = ? LIMIT 1'
        );
        $stmt->execute([$username]);
        $user = $stmt->fetch();

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
            $min = (int) ceil((strtotime($user['bloqueado_hasta']) - time()) / 60);
            return ['ok' => false, 'msg' => "Cuenta bloqueada. Intenta en {$min} min."];
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
                ? 'Demasiados intentos. Cuenta bloqueada ' . BLOQUEO_MINUTOS . ' minutos.'
                : "Credenciales incorrectas. Intentos restantes: {$restantes}.";

            return ['ok' => false, 'msg' => $msg];
        }

        // 7. Login correcto — resetear intentos y crear sesión
        $pdo->prepare(
            'UPDATE usuarios SET intentos_fallidos = 0, bloqueado_hasta = NULL, ultimo_acceso = NOW() WHERE id = ?'
        )->execute([$user['id']]);

        $token   = bin2hex(random_bytes(32));
        $expira  = date('Y-m-d H:i:s', strtotime('+' . SESSION_HORAS . ' hours'));

        $pdo->prepare(
            'INSERT INTO sesiones (usuario_id, token, ip, user_agent, expira_en)
             VALUES (?, ?, ?, ?, ?)'
        )->execute([$user['id'], $token, $ip, $ua, $expira]);

        self::registrarLog($user['id'], $username, $ip, $ua, 'exito');

        setcookie('dematiq_session', $token, [
            'expires'  => strtotime('+' . SESSION_HORAS . ' hours'),
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Strict',
            'secure'   => isset($_SERVER['HTTPS']),
        ]);

        return ['ok' => true, 'nombre' => $user['nombre']];
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
    public static function require(string $redirect = '/pages/corporativo/login.php'): array {
        $user = self::check();
        if (!$user) {
            header('Location: ' . $redirect);
            exit;
        }
        self::csrfToken(); // asegura que la cookie CSRF exista para el JS
        return $user;
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
