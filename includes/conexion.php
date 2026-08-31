<?php
// PHP no toma la zona horaria del sistema/MySQL por defecto (queda en UTC), lo
// que desalinea cualquier date()/strtotime() comparado luego contra NOW() de
// MySQL (p.ej. expiración de sesiones). Fijarla aquí, cargado por casi todo
// el proyecto, evita depender de que el hosting tenga date.timezone en su php.ini.
date_default_timezone_set('America/Mexico_City');

// En producción (Hostinger): definidas via SetEnv en el .htaccess del
// servidor (no versionado en git, ver .env.example).
// En local: define las mismas variables en tu entorno (no hardcodees el password aquí).
define('DB_HOST',    getenv('DB_HOST') ?: 'localhost');
define('DB_NAME',    getenv('DB_NAME') ?: 'dematiq_db');
define('DB_USER',    getenv('DB_USER') ?: 'dematiq_app');
define('DB_PASS',    getenv('DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');

try {
    $pdo = new PDO(
        sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET),
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            // Reutiliza la conexión entre requests del mismo worker de PHP-FPM en vez
            // de abrir una nueva cada vez — evita agotar el límite de MySQL de
            // Hostinger (max_connections_per_hour) bajo tráfico normal.
            PDO::ATTR_PERSISTENT         => true,
        ]
    );
} catch (PDOException $e) {
    error_log('DB Connection failed: ' . $e->getMessage());
    http_response_code(503);
    die(json_encode(['error' => 'Error de servicio. Intente más tarde.']));
}
