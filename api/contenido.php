<?php
ini_set('display_errors', 0);   // evita que warnings contaminen el JSON

require_once __DIR__ . '/../includes/contenido.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

$clave = $_GET['clave'] ?? '';
echo $clave
    ? json_encode(Contenido::get($clave), JSON_UNESCAPED_UNICODE)
    : json_encode(Contenido::getAll(), JSON_UNESCAPED_UNICODE);
