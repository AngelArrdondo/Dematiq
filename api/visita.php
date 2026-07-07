<?php
require_once __DIR__ . '/../includes/conexion.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false]);
    exit;
}

try {
    $pdo->prepare(
        'INSERT INTO visitas_diarias (fecha, total) VALUES (CURDATE(), 1)
         ON DUPLICATE KEY UPDATE total = total + 1'
    )->execute();
    echo json_encode(['ok' => true]);
} catch (PDOException $e) {
    http_response_code(503);
    echo json_encode(['ok' => false]);
}
