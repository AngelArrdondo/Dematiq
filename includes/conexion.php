<?php
// En producción (cPanel/Hostinger): define estas variables en
// cPanel/hPanel → Software → PHP → Environment Variables.
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
        ]
    );
} catch (PDOException $e) {
    error_log('DB Connection failed: ' . $e->getMessage());
    http_response_code(503);
    die(json_encode(['error' => 'Error de servicio. Intente más tarde.']));
}
