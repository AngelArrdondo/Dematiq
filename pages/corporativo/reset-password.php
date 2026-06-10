<?php
require_once __DIR__ . '/../../includes/conexion.php';

$token      = trim($_GET['token'] ?? '');
$error      = '';
$success    = false;
$token_data = null;

function load_token(string $raw_token): ?array {
    if (!$raw_token || strlen($raw_token) !== 64) return null;
    $file = __DIR__ . '/../../includes/tokens/' . hash('sha256', $raw_token) . '.json';
    if (!file_exists($file)) return null;
    $data = json_decode(file_get_contents($file), true);
    if (!$data || strtotime($data['expira']) < time()) {
        @unlink($file);
        return null;
    }
    $data['_file'] = $file;
    return $data;
}

$token_data = load_token($token);

if (!$token_data) {
    $error = 'El enlace es inválido o ha expirado.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $token_data) {
    $pass1 = $_POST['password']  ?? '';
    $pass2 = $_POST['password2'] ?? '';

    if (strlen($pass1) < 8) {
        $error = 'La contraseña debe tener al menos 8 caracteres.';
    } elseif ($pass1 !== $pass2) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        $hash = password_hash($pass1, PASSWORD_BCRYPT, ['cost' => 12]);
        $pdo->prepare(
            'UPDATE usuarios SET password_hash = ?, intentos_fallidos = 0, bloqueado_hasta = NULL WHERE id = ?'
        )->execute([$hash, $token_data['usuario_id']]);

        @unlink($token_data['_file']);
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Nueva contraseña | DEMATIQ</title>
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
      border-radius: 10px; padding: 14px; font-size: .85rem; color: #15803d; margin-bottom: 20px;
    }
    .msg-err {
      background: #fff1f1; border: 1px solid #fca5a5; border-left: 3px solid #ef4444;
      border-radius: 10px; padding: 12px 14px; font-size: .82rem; color: #b91c1c; margin-bottom: 20px;
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
    .eye-btn {
      position: absolute; right: 14px; top: 50%; transform: translateY(-50%);
      background: none; border: none; cursor: pointer; color: var(--text-lt);
      padding: 5px; border-radius: 8px; display: flex; align-items: center; transition: color .18s; z-index: 3;
    }
    .eye-btn:hover { color: var(--mid); }
    .eye-btn svg { width: 17px; height: 17px; }
    .strength-bar { height: 4px; border-radius: 4px; background: var(--border); margin-top: 6px; overflow: hidden; }
    .strength-fill { height: 100%; width: 0; border-radius: 4px; transition: width .3s, background .3s; }
    .strength-label { font-size: .7rem; color: var(--text-lt); margin-top: 4px; }
    .btn {
      width: 100%; padding: 15px; border: none; border-radius: 13px;
      background: linear-gradient(135deg, var(--brand), var(--mid));
      color: #fff; font-size: 1rem; font-weight: 700; font-family: inherit;
      cursor: pointer; transition: transform .2s, box-shadow .2s;
      box-shadow: 0 8px 24px rgba(26,74,158,.4); margin-top: 4px;
    }
    .btn:hover { transform: translateY(-2px); box-shadow: 0 12px 32px rgba(26,74,158,.5); }
    .btn-login {
      display: inline-flex; align-items: center; gap: 6px;
      background: linear-gradient(135deg, var(--brand), var(--mid));
      color: #fff; padding: 12px 24px; border-radius: 13px; text-decoration: none;
      font-weight: 700; font-size: .9rem; transition: transform .2s, box-shadow .2s;
      box-shadow: 0 8px 24px rgba(26,74,158,.4);
    }
    .btn-login:hover { transform: translateY(-2px); }
    .back { text-align: center; margin-top: 20px; }
    .back a { font-size: .8rem; color: var(--text-lt); text-decoration: none; display: inline-flex; align-items: center; gap: 5px; transition: color .18s; }
    .back a:hover { color: var(--brand); }
    .back a svg { width: 13px; height: 13px; }
    .success-icon { text-align: center; margin-bottom: 20px; }
    .success-icon svg { width: 56px; height: 56px; color: #22c55e; }
  </style>
</head>
<body>
<canvas id="bg-canvas"></canvas>
<div class="bg-overlay"></div>

<div class="card">
  <div class="card-logo">
    <img src="../../assets/images/logos/logo1.png" alt="DEMATIQ">
  </div>

  <?php if ($success): ?>
    <div class="success-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="9 12 11 14 15 10"/></svg>
    </div>
    <h2 style="text-align:center">¡Contraseña actualizada!</h2>
    <p class="sub" style="text-align:center">Tu contraseña fue cambiada exitosamente.</p>
    <div style="text-align:center;margin-top:8px">
      <a href="login.php" class="btn-login">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        Ir al inicio de sesión
      </a>
    </div>

  <?php elseif (!$token_data): ?>
    <div class="msg-err"><?= htmlspecialchars($error) ?></div>
    <div class="back">
      <a href="forgot-password.php">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        Solicitar nuevo enlace
      </a>
    </div>

  <?php else: ?>
    <h2>Nueva contraseña</h2>
    <p class="sub">Elige una contraseña segura de al menos 8 caracteres.</p>

    <?php if ($error): ?>
      <div class="msg-err"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="field-wrap">
        <input type="password" id="pass1" name="password" placeholder="Nueva contraseña" required autofocus>
        <label for="pass1">Nueva contraseña</label>
        <span class="field-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
        </span>
        <button type="button" class="eye-btn" data-target="pass1" tabindex="-1">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
        </button>
        <div class="strength-bar"><div class="strength-fill" id="strength-fill"></div></div>
        <div class="strength-label" id="strength-label"></div>
      </div>

      <div class="field-wrap">
        <input type="password" id="pass2" name="password2" placeholder="Confirmar contraseña" required>
        <label for="pass2">Confirmar contraseña</label>
        <span class="field-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
        </span>
        <button type="button" class="eye-btn" data-target="pass2" tabindex="-1">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
        </button>
      </div>

      <button type="submit" class="btn">Guardar contraseña</button>
    </form>

    <div class="back">
      <a href="login.php">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        Volver al inicio de sesión
      </a>
    </div>
  <?php endif; ?>
</div>

<script>
(function(){const c=document.getElementById('bg-canvas'),x=c.getContext('2d');let W,H,s=[];function r(){W=c.width=innerWidth;H=c.height=innerHeight}function mk(){return{x:Math.random()*W,y:Math.random()*H,r:Math.random()*1.2+.3,a:Math.random(),da:(Math.random()*.4+.1)*(Math.random()<.5?1:-1)*.008,vx:(Math.random()-.5)*.15,vy:(Math.random()-.5)*.15}}function init(){r();s=Array.from({length:120},mk)}function draw(){x.clearRect(0,0,W,H);for(const p of s){p.x+=p.vx;p.y+=p.vy;p.a+=p.da;if(p.a<0||p.a>1)p.da*=-1;if(p.x<-2)p.x=W+2;if(p.x>W+2)p.x=-2;if(p.y<-2)p.y=H+2;if(p.y>H+2)p.y=-2;x.beginPath();x.arc(p.x,p.y,p.r,0,Math.PI*2);x.fillStyle=`rgba(200,220,255,${p.a*.5})`;x.fill()}requestAnimationFrame(draw)}addEventListener('resize',r);init();draw()})();

// Eye toggle
document.querySelectorAll('.eye-btn').forEach(btn => {
  const EYE = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
  const HIDE = '<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';
  let show = false;
  btn.addEventListener('click', () => {
    show = !show;
    const inp = document.getElementById(btn.dataset.target);
    inp.type = show ? 'text' : 'password';
    btn.querySelector('svg').innerHTML = show ? HIDE : EYE;
  });
});

// Strength meter
const p1 = document.getElementById('pass1');
const fill = document.getElementById('strength-fill');
const label = document.getElementById('strength-label');
if (p1) {
  p1.addEventListener('input', () => {
    const v = p1.value;
    let score = 0;
    if (v.length >= 8)  score++;
    if (v.length >= 12) score++;
    if (/[A-Z]/.test(v)) score++;
    if (/[0-9]/.test(v)) score++;
    if (/[^A-Za-z0-9]/.test(v)) score++;
    const levels = [
      {w:'20%', bg:'#ef4444', t:'Muy débil'},
      {w:'40%', bg:'#f97316', t:'Débil'},
      {w:'60%', bg:'#eab308', t:'Regular'},
      {w:'80%', bg:'#22c55e', t:'Fuerte'},
      {w:'100%',bg:'#16a34a', t:'Muy fuerte'},
    ];
    const l = levels[Math.max(0, score - 1)] || levels[0];
    fill.style.width = v.length ? l.w : '0';
    fill.style.background = l.bg;
    label.textContent = v.length ? l.t : '';
  });
}
</script>
</body>
</html>
