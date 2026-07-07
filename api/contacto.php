<?php
require_once __DIR__ . '/../includes/PHPMailer/Exception.php';
require_once __DIR__ . '/../includes/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../includes/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

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

$destinatario = getenv('SMTP_TO') ?: 'ventas@dematiq.com.mx';

$cuerpo  = "Has recibido un nuevo mensaje desde el formulario de contacto de DEMATIQ.\n\n";
$cuerpo .= "Nombre:  {$nombre}\n";
$cuerpo .= "Correo:  {$correo}\n";
$cuerpo .= "Asunto:  {$asunto}\n\n";
$cuerpo .= "Mensaje:\n{$mensaje}\n";
$cuerpo .= "\n-- \nFormulario web · dematiq.com.mx\n";

// Configura estas variables en cPanel → Software → PHP → Environment Variables
// (o directamente aquí si no hay acceso a variables de entorno en el hosting).
$smtpHost = getenv('SMTP_HOST') ?: 'mail.dematiq.com.mx';
$smtpUser = getenv('SMTP_USER') ?: 'ventas@dematiq.com.mx';
$smtpPass = getenv('SMTP_PASS') ?: '';
$smtpPort = getenv('SMTP_PORT') ?: 465;
$smtpSecure = getenv('SMTP_SECURE') ?: PHPMailer::ENCRYPTION_SMTPS;

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = $smtpHost;
    $mail->SMTPAuth   = true;
    $mail->Username   = $smtpUser;
    $mail->Password   = $smtpPass;
    $mail->SMTPSecure = $smtpSecure;
    $mail->Port       = (int) $smtpPort;
    $mail->CharSet    = 'UTF-8';

    $mail->setFrom($smtpUser, 'DEMATIQ - Formulario web');
    $mail->addAddress($destinatario);
    $mail->addReplyTo($correo, $nombre);

    $mail->Subject = $asunto;
    $mail->Body    = $cuerpo;
    $mail->isHTML(false);

    $mail->send();

    echo json_encode(['ok' => true, 'mensaje' => '¡Mensaje enviado! Te respondemos en menos de 24 horas.']);
} catch (PHPMailerException $e) {
    error_log('Error al enviar correo de contacto: ' . $mail->ErrorInfo);
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'No se pudo enviar el mensaje. Intenta de nuevo o escríbenos directamente.']);
}
