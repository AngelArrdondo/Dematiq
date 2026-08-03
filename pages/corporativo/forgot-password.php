<?php
require_once __DIR__ . '/../../includes/conexion.php';
require_once __DIR__ . '/../../includes/formguard.php';
require_once __DIR__ . '/../../includes/PHPMailer/Exception.php';
require_once __DIR__ . '/../../includes/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../../includes/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

$msg       = '';
$error     = '';
$submitted = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $ip    = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

    // Honeypot — campo invisible para humanos
    if (trim($_POST['sitio_web'] ?? '') !== '') {
        FormGuard::registrar('recuperacion', $ip, 'honeypot');
        $submitted = true;
        $msg = 'Si el correo está registrado, te enviamos un enlace para restablecer tu contraseña. Revisa tu bandeja de entrada (y spam).';
    } elseif (FormGuard::golpeado('recuperacion', $ip, 3, 15)) {
        $error = 'Demasiados intentos. Espera unos minutos e intenta de nuevo.';
    } elseif ($email === '') {
        $error = 'Ingresa tu correo electrónico.';
    } else {
        FormGuard::registrar('recuperacion', $ip, 'enviado');
        $stmt = $pdo->prepare('SELECT id, nombre, activo FROM usuarios WHERE username = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && $user['activo']) {
            $token      = bin2hex(random_bytes(32));
            $expira     = date('Y-m-d H:i:s', strtotime('+30 minutes'));
            $tokens_dir = __DIR__ . '/../../includes/tokens';
            if (!is_dir($tokens_dir)) {
                mkdir($tokens_dir, 0700, true);
            }
            $file = $tokens_dir . '/' . hash('sha256', $token) . '.json';

            $written = file_put_contents($file, json_encode([
                'usuario_id' => $user['id'],
                'email'      => $email,
                'expira'     => $expira,
            ]));

            if ($written === false) {
                error_log("No se pudo escribir el token de recuperación en {$file} — revisa permisos del directorio includes/tokens.");
            } else {

            $base       = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
            $token_link = $base . '/pages/corporativo/reset-password.php?token=' . $token;

            // En producción (Hostinger): definidas via SetEnv en el .htaccess del
            // servidor (no versionado en git, ver .env.example). El puerto 465/smtps
            // se cuelga indefinidamente en este hosting; 587 con STARTTLS sí responde.
            $smtpHost   = getenv('SMTP_HOST') ?: 'mail.dematiq.com.mx';
            $smtpUser   = getenv('SMTP_USER') ?: 'ventas@dematiq.com.mx';
            $smtpPass   = getenv('SMTP_PASS') ?: '';
            $smtpPort   = getenv('SMTP_PORT') ?: 587;
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

                $mail->setFrom($smtpUser, 'DEMATIQ - Panel de administración');
                $mail->addAddress($email, $user['nombre']);

                $mail->Subject = 'Recuperar contraseña — DEMATIQ Admin';
                $mail->isHTML(false);
                $mail->Body    = "Hola {$user['nombre']},\n\n"
                    . "Recibimos una solicitud para restablecer tu contraseña del panel de administración de DEMATIQ.\n\n"
                    . "Este enlace es válido por 30 minutos:\n{$token_link}\n\n"
                    . "Si tú no pediste este cambio, ignora este correo — tu contraseña actual sigue funcionando.\n";

                $mail->send();
            } catch (PHPMailerException $e) {
                error_log('Error al enviar correo de recuperación de contraseña: ' . $mail->ErrorInfo);
                @unlink($file);
            }
            }
        }

        // Mismo mensaje exista o no la cuenta (y haya fallado o no el envío):
        // no revelar si un correo está registrado.
        $submitted = true;
        $msg = 'Si el correo está registrado, te enviamos un enlace para restablecer tu contraseña. Revisa tu bandeja de entrada (y spam).';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Recuperar contraseña | DEMATIQ</title>
  <link rel="icon" type="image/svg+xml" href="../../assets/images/logos/favicon-d.svg">
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap');
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --navy: #000028; --brand: #1a4a9e; --mid: #2e6bcf; --teal: #00ffb9;
      --form-bg: #f5f8ff; --text-dark: #1e2d50; --text-lt: #5a6f96; --border: #c8d8f0;
    }
    body {
      font-family: 'Roboto', sans-serif; background: var(--navy);
      display: flex; align-items: center; justify-content: center;
      min-height: 100vh; padding: 20px;
    }
    #bg-canvas { position: fixed; inset: 0; z-index: 0; }
    .bg-overlay {
      position: fixed; inset: 0; z-index: 1;
      background: radial-gradient(ellipse 70% 60% at 20% 30%, rgba(26,74,158,.4) 0%, transparent 60%);
    }
    .card {
      position: relative; z-index: 10;
      background: var(--form-bg); border-radius: 24px;
      padding: 44px 40px; width: min(440px, 100%);
      box-shadow: 0 40px 100px rgba(0,0,0,.6), 0 0 0 1px rgba(255,255,255,.06);
      animation: cardIn .65s cubic-bezier(.22,.68,0,1.15) both;
    }
    @keyframes cardIn { from{opacity:0;transform:translateY(32px) scale(.96)} to{opacity:1;transform:none} }
    .card-logo { display: flex; justify-content: center; margin-bottom: 28px; }
    .card-logo img { height: 52px; width: auto; object-fit: contain; }
    h2 { font-size: 1.3rem; font-weight: 700; color: var(--text-dark); letter-spacing: -.3px; }
    p.sub { font-size: .83rem; color: var(--text-lt); margin-top: 6px; line-height: 1.55; margin-bottom: 24px; }
    .msg-ok {
      background: #f0fdf4; border: 1px solid #86efac; border-left: 3px solid #22c55e;
      border-radius: 10px; padding: 12px 14px; font-size: .82rem; color: #15803d;
      margin-bottom: 20px;
    }
    .msg-err {
      background: #fff1f1; border: 1px solid #fca5a5; border-left: 3px solid #ef4444;
      border-radius: 10px; padding: 12px 14px; font-size: .82rem; color: #b91c1c;
      margin-bottom: 20px;
    }
    .field-wrap { position: relative; margin-bottom: 18px; }
    .field-wrap input {
      width: 100%; padding: 20px 46px 8px 46px;
      background: #fff; border: 1.5px solid var(--border); border-radius: 13px;
      font-size: .95rem; font-family: inherit; color: var(--text-dark); outline: none;
      transition: border-color .22s, box-shadow .22s;
    }
    .field-wrap input::placeholder { color: transparent; }
    .field-wrap label {
      position: absolute; left: 46px; top: 50%; transform: translateY(-50%);
      font-size: .88rem; font-weight: 500; color: var(--text-lt);
      pointer-events: none; transition: all .22s;
    }
    .field-wrap input:focus, .field-wrap input:not(:placeholder-shown) {
      border-color: var(--mid); box-shadow: 0 0 0 4px rgba(46,107,207,.1);
    }
    .field-wrap input:focus + label, .field-wrap input:not(:placeholder-shown) + label {
      top: 10px; transform: translateY(0); font-size: .64rem; font-weight: 700;
      letter-spacing: .6px; text-transform: uppercase; color: var(--brand);
    }
    .field-icon { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-lt); }
    .field-icon svg { width: 17px; height: 17px; display: block; }
    .btn {
      width: 100%; padding: 15px; border: none; border-radius: 13px;
      background: linear-gradient(135deg, var(--brand), var(--mid));
      color: #fff; font-size: 1rem; font-weight: 700; font-family: inherit;
      cursor: pointer; transition: transform .2s, box-shadow .2s;
      box-shadow: 0 8px 24px rgba(26,74,158,.4);
    }
    .btn:hover { transform: translateY(-2px); box-shadow: 0 12px 32px rgba(26,74,158,.5); }
    .back { text-align: center; margin-top: 20px; }
    .back a { font-size: .8rem; color: var(--text-lt); text-decoration: none; display: inline-flex; align-items: center; gap: 5px; transition: color .18s; }
    .back a:hover { color: var(--brand); }
    .back a svg { width: 13px; height: 13px; }
  </style>
</head>
<body>
<canvas id="bg-canvas"></canvas>
<div class="bg-overlay"></div>

<div class="card">
  <div class="card-logo">
    <img src="../../assets/images/logos/logo1.webp" alt="DEMATIQ">
  </div>
  <h2>Recuperar contraseña</h2>
  <p class="sub">Ingresa tu correo y se generará un enlace para restablecer tu contraseña.</p>

  <?php if ($error): ?>
    <div class="msg-err"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <?php if ($msg): ?>
    <div class="msg-ok"><?= htmlspecialchars($msg) ?></div>
  <?php endif; ?>

  <?php if (!$submitted): ?>
  <form method="POST">
    <div style="position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden" aria-hidden="true">
      <label for="fp-sitio-web">No llenar este campo</label>
      <input type="text" id="fp-sitio-web" name="sitio_web" tabindex="-1" autocomplete="off">
    </div>
    <div class="field-wrap">
      <input type="email" name="email" placeholder="Correo electrónico"
             value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autofocus>
      <label>Correo electrónico</label>
      <span class="field-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
      </span>
    </div>
    <button type="submit" class="btn">Generar enlace</button>
  </form>
  <?php endif; ?>

  <div class="back">
    <a href="login.php">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
      Volver al inicio de sesión
    </a>
  </div>
</div>

<script>
(function(){const c=document.getElementById('bg-canvas'),x=c.getContext('2d');let W,H,s=[];function r(){W=c.width=innerWidth;H=c.height=innerHeight}function mk(){return{x:Math.random()*W,y:Math.random()*H,r:Math.random()*1.2+.3,a:Math.random(),da:(Math.random()*.4+.1)*(Math.random()<.5?1:-1)*.008,vx:(Math.random()-.5)*.15,vy:(Math.random()-.5)*.15}}function init(){r();s=Array.from({length:120},mk)}function draw(){x.clearRect(0,0,W,H);for(const p of s){p.x+=p.vx;p.y+=p.vy;p.a+=p.da;if(p.a<0||p.a>1)p.da*=-1;if(p.x<-2)p.x=W+2;if(p.x>W+2)p.x=-2;if(p.y<-2)p.y=H+2;if(p.y>H+2)p.y=-2;x.beginPath();x.arc(p.x,p.y,p.r,0,Math.PI*2);x.fillStyle=`rgba(200,220,255,${p.a*.5})`;x.fill()}requestAnimationFrame(draw)}addEventListener('resize',r);init();draw()})();
</script>
</body>
</html>
