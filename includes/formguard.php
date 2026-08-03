<?php
// Rate limiting + registro de honeypot para formularios públicos sin
// autenticación (contacto, recuperación de contraseña). No confundir con
// el rate limiting de Auth::login, que es por cuenta/IP y vive en auth.php.
class FormGuard {

    public static function golpeado(string $tipo, string $ip, int $maxIntentos, int $ventanaMinutos): bool {
        global $pdo;
        $stmt = $pdo->prepare(
            'SELECT COUNT(*) FROM log_formularios
             WHERE tipo = ? AND ip = ? AND creado_en > DATE_SUB(NOW(), INTERVAL ? MINUTE)'
        );
        $stmt->execute([$tipo, $ip, $ventanaMinutos]);
        return (int) $stmt->fetchColumn() >= $maxIntentos;
    }

    public static function registrar(string $tipo, string $ip, string $resultado): void {
        global $pdo;
        $pdo->prepare(
            'INSERT INTO log_formularios (tipo, ip, resultado) VALUES (?, ?, ?)'
        )->execute([$tipo, $ip, $resultado]);
    }
}
