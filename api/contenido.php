<?php
require_once __DIR__ . '/../includes/contenido.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=60');

$clave = $_GET['clave'] ?? '';
echo $clave
    ? json_encode(Contenido::get($clave), JSON_UNESCAPED_UNICODE)
    : json_encode(Contenido::getAll(), JSON_UNESCAPED_UNICODE);
