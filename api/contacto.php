<?php
ini_set('display_errors', 0);   // evita que warnings contaminen el JSON

require_once __DIR__ . '/../includes/conexion.php';
require_once __DIR__ . '/../includes/formguard.php';
require_once __DIR__ . '/../includes/PHPMailer/Exception.php';
require_once __DIR__ . '/../includes/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../includes/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: https://dematiq.com.mx');
header('Access-Control-Allow-Methods: POST');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Método no permitido.']);
    exit;
}

$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

// Leer body JSON o form-data
$data = json_decode(file_get_contents('php://input'), true);
if (!$data) $data = $_POST;

// Honeypot — campo invisible para humanos; si viene lleno, es un bot.
// Respondemos éxito falso para no revelarle que fue detectado.
if (trim($data['sitio_web'] ?? '') !== '') {
    FormGuard::registrar('contacto', $ip, 'honeypot');
    echo json_encode(['ok' => true, 'mensaje' => '¡Mensaje enviado! Te respondemos en menos de 24 horas.']);
    exit;
}

if (FormGuard::golpeado('contacto', $ip, 5, 15)) {
    http_response_code(429);
    echo json_encode(['ok' => false, 'error' => 'Demasiados intentos. Espera unos minutos e intenta de nuevo.']);
    exit;
}

$nombre  = trim($data['nombre']  ?? '');
$correo  = trim($data['correo']  ?? '');
$asunto  = trim($data['asunto']  ?? '');
$mensaje = trim($data['mensaje'] ?? '');

// Validación básica
if (!$nombre || !$correo || !$mensaje) {
    FormGuard::registrar('contacto', $ip, 'bloqueado');
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Nombre, correo y mensaje son obligatorios.']);
    exit;
}

if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    FormGuard::registrar('contacto', $ip, 'bloqueado');
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

// En producción (Hostinger): definidas via SetEnv en el .htaccess del
// servidor (no versionado en git, ver .env.example). El puerto 465/smtps
// se cuelga indefinidamente en este hosting; 587 con STARTTLS sí responde.
// SMTP_USER requiere una "contraseña de aplicación" de Gmail (no la contraseña normal de la cuenta).
$smtpHost = getenv('SMTP_HOST') ?: 'smtp.gmail.com';
$smtpUser = getenv('SMTP_USER') ?: 'dematiq3@gmail.com';
$smtpPass = getenv('SMTP_PASS') ?: '';
$smtpPort = getenv('SMTP_PORT') ?: 587;
$smtpSecure = getenv('SMTP_SECURE') ?: PHPMailer::ENCRYPTION_STARTTLS;

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

    FormGuard::registrar('contacto', $ip, 'enviado');
    echo json_encode(['ok' => true, 'mensaje' => '¡Mensaje enviado! Te respondemos en menos de 24 horas.']);
} catch (PHPMailerException $e) {
    error_log('Error al enviar correo de contacto: ' . $mail->ErrorInfo);
    FormGuard::registrar('contacto', $ip, 'bloqueado');
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'No se pudo enviar el mensaje. Intenta de nuevo o escríbenos directamente.']);
}
