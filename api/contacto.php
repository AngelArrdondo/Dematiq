<?php
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Método no permitido.']);
    exit;
}

// Leer body JSON o form-data
$data = json_decode(file_get_contents('php://input'), true);
if (!$data) $data = $_POST;

$nombre  = trim($data['nombre']  ?? '');
$correo  = trim($data['correo']  ?? '');
$asunto  = trim($data['asunto']  ?? '');
$mensaje = trim($data['mensaje'] ?? '');

// Validación básica
if (!$nombre || !$correo || !$mensaje) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Nombre, correo y mensaje son obligatorios.']);
    exit;
}

if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Correo electrónico inválido.']);
    exit;
}

// Sanear para evitar header injection
$nombre  = strip_tags($nombre);
$asunto  = strip_tags($asunto) ?: 'Consulta desde DEMATIQ';
$mensaje = strip_tags($mensaje);

$destinatario = 'ventas@dematiq.com.mx';

$cuerpo  = "Has recibido un nuevo mensaje desde el formulario de contacto de DEMATIQ.\n\n";
$cuerpo .= "Nombre:  {$nombre}\n";
$cuerpo .= "Correo:  {$correo}\n";
$cuerpo .= "Asunto:  {$asunto}\n\n";
$cuerpo .= "Mensaje:\n{$mensaje}\n";
$cuerpo .= "\n-- \nFormulario web · dematiq.com.mx\n";

$headers  = "From: noreply@dematiq.com.mx\r\n";
$headers .= "Reply-To: {$correo}\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
$headers .= "Content-Transfer-Encoding: 8bit\r\n";
$headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

$enviado = mail($destinatario, "=?UTF-8?B?" . base64_encode($asunto) . "?=", $cuerpo, $headers);

if ($enviado) {
    echo json_encode(['ok' => true, 'mensaje' => '¡Mensaje enviado! Te respondemos en menos de 24 horas.']);
} else {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'No se pudo enviar el mensaje. Intenta de nuevo o escríbenos directamente.']);
}
