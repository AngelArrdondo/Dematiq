<?php
ini_set('display_errors', 0);   // evita que warnings contaminen el JSON

require_once __DIR__ . '/../includes/conexion.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false]);
    exit;
}

try {
    // No usar CURDATE(): corre en la zona horaria del servidor de MySQL (normalmente
    // UTC en Hostinger), no en America/Mexico_City, así que el "día" no coincidía con
    // la medianoche local (el contador arrancaba de madrugada en vez de a las 12am).
    $hoy = date('Y-m-d');
    $stmt = $pdo->prepare(
        'INSERT INTO visitas_diarias (fecha, total) VALUES (:fecha, 1)
         ON DUPLICATE KEY UPDATE total = total + 1'
    );
    $stmt->execute(['fecha' => $hoy]);
    echo json_encode(['ok' => true]);
} catch (PDOException $e) {
    error_log('Error al registrar visita diaria: ' . $e->getMessage());
    http_response_code(503);
    echo json_encode(['ok' => false]);
}
