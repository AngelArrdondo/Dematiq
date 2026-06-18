<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/contenido.php';

header('Content-Type: application/json; charset=utf-8');

if (!Auth::check()) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// Verificar CSRF en escrituras
if ($method === 'POST' && !Auth::csrfVerify()) {
    http_response_code(403);
    echo json_encode(['error' => 'Solicitud no autorizada']);
    exit;
}

if ($method === 'GET') {
    $clave = $_GET['clave'] ?? '';
    echo $clave
        ? json_encode(Contenido::get($clave), JSON_UNESCAPED_UNICODE)
        : json_encode(Contenido::getAll(), JSON_UNESCAPED_UNICODE);

} elseif ($method === 'POST') {
    $body  = json_decode(file_get_contents('php://input'), true);
    $clave = $body['clave'] ?? '';
    $valor = $body['valor'] ?? null;

    if (!$clave || $valor === null) {
        http_response_code(400);
        echo json_encode(['error' => 'Faltan parámetros']);
        exit;
    }

    Contenido::set($clave, $valor);
    echo json_encode(['ok' => true]);

} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
}
