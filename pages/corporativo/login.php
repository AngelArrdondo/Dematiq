<?php
require_once __DIR__ . '/../../includes/auth.php';

if (Auth::check()) {
    header('Location: ../../admin/dashboard.php');
    exit;
}

$error = '';
$retry_after = null;
$remembered_user = $_COOKIE['dematiq_remember_user'] ?? '';
$show2fa = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['step'] ?? '') === '2fa') {
    // Paso 2: ya se validó la contraseña, solo falta el código TOTP
    $codigo = trim($_POST['codigo'] ?? '');
    $result = Auth::verify2fa($codigo);

    if ($result['ok']) {
        if (!empty($result['recordar'])) {
            setcookie('dematiq_remember_user', $result['username'], [
                'expires'  => time() + (30 * 24 * 3600),
                'path'     => '/',
                'httponly' => true,
                'samesite' => 'Strict',
            ]);
        } else {
            setcookie('dematiq_remember_user', '', ['expires' => time() - 3600, 'path' => '/']);
        }
        header('Location: ../../admin/dashboard.php');
        exit;
    }
    $error   = $result['msg'];
    $show2fa = true;

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    $ip       = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $ua       = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);

    if ($username === '' || $password === '') {
        $error = 'Completa todos los campos.';
    } else {
        $result = Auth::login($username, $password, $ip, $ua, $remember);

        if ($result['ok'] && !empty($result['need2fa'])) {
            // Contraseña correcta — falta el código de la app autenticadora
            $show2fa = true;
        } elseif ($result['ok']) {
            if ($remember) {
                setcookie('dematiq_remember_user', $username, [
                    'expires'  => time() + (30 * 24 * 3600),
                    'path'     => '/',
                    'httponly' => true,
                    'samesite' => 'Strict',
                ]);
            } else {
                setcookie('dematiq_remember_user', '', ['expires' => time() - 3600, 'path' => '/']);
            }
            header('Location: ../../admin/dashboard.php');
            exit;
        } else {
            $error = $result['msg'];
            $retry_after = $result['retry_after'] ?? null;
        }
    }
}

// Recarga de página en medio del paso 2 (p.ej. F5) — no volver a pedir la contraseña
if (!$show2fa && $_SERVER['REQUEST_METHOD'] !== 'POST' && Auth::pendiente2fa()) {
    $show2fa = true;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Iniciar sesión | DEMATIQ Admin</title>
  <link rel="icon" type="image/svg+xml" href="../../assets/images/logos/favicon-d.svg">
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap');

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --navy:      #000028;
      --deep:      #0d2155;
      --brand:     #1a4a9e;
      --mid:       #2e6bcf;
      --glow:      #4d8de8;
      --teal:      #00ffb9;
      --white:     #ffffff;
      --form-bg:   #f5f8ff;
      --text-dark: #1e2d50;
      --text-lt:   #5a6f96;
      --border:    #c8d8f0;
    }

    html, body { height: 100%; }

    body {
      font-family: 'Roboto', sans-serif;
      background: #000028;
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      overflow: hidden;
    }

    #bg-canvas { position: fixed; inset: 0; z-index: 0; }

    .bg-overlay {
      position: fixed; inset: 0; z-index: 1;
      background:
        radial-gradient(ellipse 70% 60% at 15% 25%, rgba(26,74,158,.48) 0%, transparent 60%),
        radial-gradient(ellipse 50% 50% at 85% 75%, rgba(0,255,185,.06) 0%, transparent 55%),
        linear-gradient(180deg, #000028 0%, #000028 100%);
    }

    .login-scene {
      position: relative; z-index: 10;
      padding: 20px; perspective: 1400px;
    }

    .glow-ring {
      position: absolute; inset: -2px; border-radius: 30px;
      background: conic-gradient(from var(--a,0deg), transparent 30%, rgba(26,74,158,.8) 42%,
        rgba(0,255,185,.9) 50%, rgba(26,74,158,.8) 58%, transparent 70%);
      animation: spinGlow 5s linear infinite;
      z-index: -1; filter: blur(3px);
    }
    @property --a { syntax: '<angle>'; inherits: false; initial-value: 0deg; }
    @keyframes spinGlow { to { --a: 360deg; } }

    .login-card {
      display: flex;
      width: min(820px, calc(100vw - 40px));
      min-height: 520px;
      border-radius: 28px; overflow: hidden;
      box-shadow: 0 50px 120px rgba(0,0,0,.65), 0 0 0 1px rgba(255,255,255,.06);
      animation: cardIn .75s cubic-bezier(.22,.68,0,1.15) both;
    }
    @keyframes cardIn {
      from { opacity:0; transform: translateY(48px) scale(.94); }
      to   { opacity:1; transform: translateY(0)   scale(1);   }
    }

    /* ── Panel izquierdo ── */
    .brand-panel {
      flex: 0 0 42%; position: relative;
      background: linear-gradient(155deg, #0d2155 0%, #000028 100%);
      padding: 40px 32px; display: flex; flex-direction: column; overflow: hidden;
    }
    #net-canvas { position: absolute; inset: 0; opacity: .55; }
    .brand-content {
      position: relative; z-index: 2;
      display: flex; flex-direction: column;
      align-items: center; justify-content: space-between;
      height: 100%; text-align: center;
    }

    /* Logo */
    .brand-logo { display:flex; align-items:center; justify-content:center; flex:1; animation: fadeRight .7s .1s both; }
    .brand-logo img { width: 90%; max-width: 280px; height: auto; object-fit: contain; }

    /* Separador decorativo */
    .brand-divider {
      width: 100%; display: flex; align-items: center; gap: 12px;
      margin: 8px 0; animation: fadeRight .7s .3s both;
    }
    .brand-divider::before, .brand-divider::after {
      content: ''; flex: 1; height: 1px;
      background: linear-gradient(90deg, transparent, rgba(0,255,185,.3), transparent);
    }
    .brand-divider-dot {
      width: 6px; height: 6px; border-radius: 50%;
      background: var(--teal); opacity: .6;
      box-shadow: 0 0 8px var(--teal);
    }

    /* Reloj */
    .brand-clock { text-align: center; animation: fadeRight .7s .4s both; margin-bottom: 4px; }
    .clock-time {
      font-size: 2rem; font-weight: 700; color: #fff;
      letter-spacing: 3px; font-variant-numeric: tabular-nums;
      text-shadow: 0 0 20px rgba(0,255,185,.3);
    }
    .clock-date {
      font-size: .72rem; color: rgba(255,255,255,.4);
      text-transform: uppercase; letter-spacing: 2px; margin-top: 5px;
    }

    /* Badge versión */
    .version-badge {
      margin-top: 14px;
      display: inline-flex; align-items: center; gap: 5px;
      background: rgba(255,255,255,.04); border: 1px solid rgba(255,255,255,.1);
      border-radius: 20px; padding: 4px 12px;
      font-size: .65rem; font-weight: 600; color: rgba(255,255,255,.3);
      letter-spacing: 1px; text-transform: uppercase;
      animation: fadeRight .7s .5s both;
    }
    .version-badge span { color: rgba(0,255,185,.5); }

    @keyframes fadeRight { from{opacity:0;transform:translateX(-20px)} to{opacity:1;transform:translateX(0)} }

    /* ── Panel derecho ── */
    .form-panel {
      flex:1; background:var(--form-bg);
      padding:40px 40px 34px; display:flex; flex-direction:column; justify-content:center;
    }

    .form-header { margin-bottom:24px; animation:fadeLeft .65s .15s both; }
    .form-header h2 { font-size:1.4rem;font-weight:700;color:var(--text-dark);letter-spacing:-.4px; }
    .form-header p  { font-size:.83rem;color:var(--text-lt);margin-top:6px;line-height:1.5; }
    @keyframes fadeLeft { from{opacity:0;transform:translateX(20px)} to{opacity:1;transform:translateX(0)} }

    .form-error {
      display:none; align-items:center;gap:9px;
      background:#fff1f1;border:1px solid #fca5a5;border-left:3px solid #ef4444;
      border-radius:10px;padding:11px 14px;
      font-size:.82rem;font-weight:500;color:#b91c1c;margin-bottom:20px;
    }
    .form-error.show { display:flex; animation:shake .45s cubic-bezier(.36,.07,.19,.97) both; }
    @keyframes shake { 10%,90%{transform:translateX(-2px)} 20%,80%{transform:translateX(4px)} 30%,50%,70%{transform:translateX(-6px)} 40%,60%{transform:translateX(6px)} }

    .field-wrap { margin-bottom:18px; animation:fadeLeft .65s .25s both; }
    .field-wrap label {
      display:block; margin-bottom:7px; padding-left:3px;
      font-size:.74rem; font-weight:700; color:var(--text-lt);
      letter-spacing:.5px; text-transform:uppercase; transition:color .22s;
    }
    .field-wrap:focus-within label { color:var(--brand); }
    .input-row { position:relative; }
    .field-icon { position:absolute;left:16px;top:50%;transform:translateY(-50%);
      color:var(--text-lt);pointer-events:none;transition:color .22s;z-index:2; }
    .field-icon svg { width:17px;height:17px;display:block; }
    .field-wrap input {
      width:100%; padding:13px 46px;
      background:var(--white);border:1.5px solid var(--border);border-radius:13px;
      font-size:.95rem;font-family:inherit;color:var(--text-dark);outline:none;
      transition:border-color .22s,box-shadow .22s;
    }
    .field-wrap input::placeholder { color:var(--border); }
    .field-wrap input:focus { border-color:var(--mid);box-shadow:0 0 0 4px rgba(46,107,207,.1); }
    .field-wrap:focus-within .field-icon { color:var(--mid); }

    .eye-btn {
      position:absolute;right:14px;top:50%;transform:translateY(-50%);
      background:none;border:none;cursor:pointer;color:var(--text-lt);
      padding:5px;border-radius:8px;display:flex;align-items:center;transition:color .18s,background .18s;z-index:3;
    }
    .eye-btn:hover { color:var(--mid);background:rgba(26,74,158,.07); }
    .eye-btn svg { width:17px;height:17px; }

    /* Fila recordar + olvidé */
    .form-extras {
      display: flex; align-items: center; justify-content: space-between;
      margin-bottom: 20px; animation: fadeLeft .65s .3s both;
    }
    .remember-label {
      display: flex; align-items: center; gap: 8px;
      font-size: .82rem; font-weight: 500; color: var(--text-lt);
      cursor: pointer; user-select: none;
    }
    .remember-label input[type="checkbox"] { display: none; }
    .remember-box {
      width: 17px; height: 17px; border-radius: 5px;
      border: 1.5px solid var(--border); background: var(--white);
      display: flex; align-items: center; justify-content: center;
      transition: border-color .18s, background .18s; flex-shrink: 0;
    }
    .remember-label input:checked ~ .remember-box {
      background: var(--brand); border-color: var(--brand);
    }
    .remember-box svg { width: 10px; height: 10px; color: #fff; opacity: 0; transition: opacity .15s; }
    .remember-label input:checked ~ .remember-box svg { opacity: 1; }
    .forgot-link {
      font-size: .8rem; font-weight: 500; color: var(--text-lt);
      text-decoration: none; transition: color .18s;
    }
    .forgot-link:hover { color: var(--brand); }

    .btn-wrap { animation:fadeLeft .65s .38s both; }
    .submit-btn {
      width:100%;padding:15px 24px;
      background:linear-gradient(135deg,var(--brand) 0%,var(--mid) 60%,var(--glow) 100%);
      background-size:220% 220%;color:#fff;border:none;border-radius:13px;
      font-size:1rem;font-weight:700;font-family:inherit;letter-spacing:.3px;
      cursor:pointer;overflow:hidden;position:relative;
      transition:transform .22s,box-shadow .22s;
      box-shadow:0 8px 28px rgba(26,74,158,.42);
      animation:gradShift 5s ease infinite;
      display:flex;align-items:center;justify-content:center;gap:8px;
    }
    @keyframes gradShift { 0%{background-position:0% 50%} 50%{background-position:100% 50%} 100%{background-position:0% 50%} }
    .submit-btn:hover:not(:disabled) { transform:translateY(-2px);box-shadow:0 14px 36px rgba(26,74,158,.55); }
    .submit-btn:disabled { opacity:.65;cursor:not-allowed; }
    .submit-btn .arrow { display:inline-flex;transition:transform .22s; }
    .submit-btn:hover:not(:disabled) .arrow { transform:translateX(4px); }
    .submit-btn .arrow svg { width:17px;height:17px; }

    /* Spinner */
    .spinner {
      display: none; width: 18px; height: 18px;
      border: 2px solid rgba(255,255,255,.3);
      border-top-color: #fff; border-radius: 50%;
      animation: spin .6s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }
    .submit-btn.loading .spinner { display: block; }
    .submit-btn.loading .btn-label,
    .submit-btn.loading .arrow { display: none; }

    .form-divider { display:flex;align-items:center;gap:12px;margin:22px 0 0;animation:fadeLeft .65s .46s both; }
    .form-divider::before,.form-divider::after { content:'';flex:1;height:1px;background:var(--border); }
    .form-divider span { font-size:.74rem;color:var(--text-lt);white-space:nowrap;font-weight:500; }

    .back-link { text-align:center;margin-top:16px;animation:fadeLeft .65s .52s both; }
    .back-link a { font-size:.8rem;font-weight:500;color:var(--text-lt);text-decoration:none;
      display:inline-flex;align-items:center;gap:5px;transition:color .18s; }
    .back-link a:hover { color:var(--brand); }
    .back-link a svg { width:13px;height:13px; }

    @media (max-width:600px) {
      html { height:auto;overflow-x:hidden; }
      body { height:auto;overflow-y:auto;align-items:flex-start;min-height:100dvh; }
      .login-scene { padding:0;width:100%;min-height:100dvh;perspective:none;display:flex;align-items:stretch; }
      .glow-ring { display:none; }
      .login-card { flex-direction:column;width:100%;min-height:100dvh;border-radius:0;box-shadow:none; }
      .brand-panel { flex:none;padding:24px 24px 20px; }
      .brand-content { flex-direction:row;align-items:center;justify-content:space-between;height:auto; }
      .brand-logo { flex:none; }
      .brand-logo img { width:180px; }
      .brand-divider, .version-badge { display:none; }
      .brand-clock { text-align:right; }
      .clock-time { font-size:1.3rem; }
      .form-panel { flex:1;padding:32px 24px 40px;justify-content:flex-start; }
      .field-wrap input { font-size:16px; }
      .submit-btn { min-height:52px; }
    }
  </style>
</head>
<body>

<canvas id="bg-canvas"></canvas>
<div class="bg-overlay"></div>

<div class="login-scene">
  <div class="glow-ring"></div>
  <div class="login-card">

    <!-- Panel izquierdo -->
    <div class="brand-panel">
      <canvas id="net-canvas"></canvas>
      <div class="brand-content">

        <div class="brand-logo">
          <img src="../../assets/images/logos/logo1.webp" alt="DEMATIQ">
        </div>

        <div class="brand-divider"><div class="brand-divider-dot"></div></div>

        <div class="brand-clock">
          <div class="clock-time" id="clock-time">00:00:00</div>
          <div class="clock-date" id="clock-date">—</div>
        </div>

        <div class="version-badge">v1.0 &nbsp;·&nbsp; <span>Sistema activo</span></div>

      </div>
    </div>

    <!-- Panel derecho -->
    <div class="form-panel">
      <div class="form-header">
        <?php if ($show2fa): ?>
          <h2>Verificación en dos pasos</h2>
          <p>Ingresa el código de 6 dígitos de tu app autenticadora.</p>
        <?php else: ?>
          <h2>Bienvenido de vuelta</h2>
          <p>Ingresa tus credenciales para acceder al panel de administración.</p>
        <?php endif; ?>
      </div>

      <?php if ($error): ?>
      <div class="form-error show" id="form-error" <?= $retry_after ? 'data-retry="' . (int) $retry_after . '"' : '' ?>>
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <span id="error-text"><?= htmlspecialchars($error) ?></span>
      </div>
      <?php endif; ?>

      <?php if ($show2fa): ?>
      <form method="POST" action="" autocomplete="off" id="login-form">
        <input type="hidden" name="step" value="2fa">

        <div class="field-wrap">
          <label for="twofa-code">Código de verificación</label>
          <div class="input-row">
            <input type="text" id="twofa-code" name="codigo"
                   inputmode="numeric" maxlength="9"
                   placeholder="123456" autocomplete="one-time-code" required autofocus>
            <span class="field-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            </span>
          </div>
        </div>

        <p style="font-size:.78rem;color:var(--text-lt);margin:-8px 0 20px;line-height:1.5;">
          ¿Perdiste el acceso a tu app? Usa uno de tus códigos de recuperación (formato XXXX-XXXX).
        </p>

        <div class="btn-wrap">
          <button type="submit" class="submit-btn" id="submit-btn">
            <span class="spinner"></span>
            <span class="btn-label">Verificar</span>
            <span class="arrow">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </span>
          </button>
        </div>
      </form>
      <?php else: ?>
      <form method="POST" action="" autocomplete="off" id="login-form">

        <div class="field-wrap">
          <label for="login-user">Correo electrónico</label>
          <div class="input-row">
            <input type="email" id="login-user" name="username"
                   value="<?= htmlspecialchars($_POST['username'] ?? $remembered_user) ?>"
                   placeholder="ejemplo@correo.com" autocomplete="email" required autofocus>
            <span class="field-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            </span>
          </div>
        </div>

        <div class="field-wrap">
          <label for="login-pass">Contraseña</label>
          <div class="input-row">
            <input type="password" id="login-pass" name="password"
                   placeholder="••••••••" autocomplete="current-password" required>
            <span class="field-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
            </span>
            <button type="button" class="eye-btn" id="eye-btn" tabindex="-1">
              <svg id="eye-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
              </svg>
            </button>
          </div>
        </div>

        <div class="form-extras">
          <label class="remember-label">
            <input type="checkbox" name="remember" id="remember" <?= $remembered_user ? 'checked' : '' ?>>
            <span class="remember-box">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            </span>
            Recordar sesión
          </label>
          <a href="forgot-password.php" class="forgot-link">¿Olvidaste tu contraseña?</a>
        </div>

        <div class="btn-wrap">
          <button type="submit" class="submit-btn" id="submit-btn">
            <span class="spinner"></span>
            <span class="btn-label">Acceder al panel</span>
            <span class="arrow">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </span>
          </button>
        </div>
      </form>
      <?php endif; ?>

      <div class="form-divider"><span>DEMATIQ © 2025</span></div>
      <div class="back-link">
        <a href="../../index.html">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
          Volver al sitio principal
        </a>
      </div>
    </div>

  </div>
</div>

<script>
(function(){const c=document.getElementById('bg-canvas'),x=c.getContext('2d');let W,H,s=[];function r(){W=c.width=innerWidth;H=c.height=innerHeight}function mk(){return{x:Math.random()*W,y:Math.random()*H,r:Math.random()*1.2+.3,a:Math.random(),da:(Math.random()*.4+.1)*(Math.random()<.5?1:-1)*.008,vx:(Math.random()-.5)*.15,vy:(Math.random()-.5)*.15}}function init(){r();s=Array.from({length:160},mk)}function draw(){x.clearRect(0,0,W,H);for(const p of s){p.x+=p.vx;p.y+=p.vy;p.a+=p.da;if(p.a<0||p.a>1)p.da*=-1;if(p.x<-2)p.x=W+2;if(p.x>W+2)p.x=-2;if(p.y<-2)p.y=H+2;if(p.y>H+2)p.y=-2;x.beginPath();x.arc(p.x,p.y,p.r,0,Math.PI*2);x.fillStyle=`rgba(200,220,255,${p.a*.55})`;x.fill()}requestAnimationFrame(draw)}addEventListener('resize',r);init();draw()})();

(function(){const c=document.getElementById('net-canvas'),x=c.getContext('2d'),p=c.parentElement;let W,H,n=[];const C=38,D=110;function r(){const rc=p.getBoundingClientRect();W=c.width=rc.width;H=c.height=rc.height}function mk(){return{x:Math.random()*W,y:Math.random()*H,vx:(Math.random()-.5)*.55,vy:(Math.random()-.5)*.55,r:Math.random()*2+1.2}}function draw(){x.clearRect(0,0,W,H);for(const a of n){a.x+=a.vx;a.y+=a.vy;if(a.x<0||a.x>W)a.vx*=-1;if(a.y<0||a.y>H)a.vy*=-1;for(const b of n){const dx=a.x-b.x,dy=a.y-b.y,d=Math.sqrt(dx*dx+dy*dy);if(d<D){x.beginPath();x.moveTo(a.x,a.y);x.lineTo(b.x,b.y);const al=(1-d/D)*.35,r2=Math.round(0+(46-0)*(d/D)),g2=Math.round(255+(107-255)*(d/D)),b2=Math.round(185+(207-185)*(d/D));x.strokeStyle=`rgba(${r2},${g2},${b2},${al})`;x.lineWidth=.8;x.stroke()}}x.beginPath();x.arc(a.x,a.y,a.r,0,Math.PI*2);x.fillStyle='rgba(0,255,185,.65)';x.fill()}requestAnimationFrame(draw)}function init(){r();n=Array.from({length:C},mk);draw()}addEventListener('resize',()=>r());init()})();

(function(){const t=document.getElementById('clock-time'),d=document.getElementById('clock-date');if(!t)return;const D=['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'],M=['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];function tick(){const n=new Date();t.textContent=`${String(n.getHours()).padStart(2,'0')}:${String(n.getMinutes()).padStart(2,'0')}:${String(n.getSeconds()).padStart(2,'0')}`;d.textContent=`${D[n.getDay()]} ${n.getDate()} ${M[n.getMonth()]} ${n.getFullYear()}`}tick();setInterval(tick,1000)})();

(function(){const i=document.getElementById('login-pass'),b=document.getElementById('eye-btn'),s=document.getElementById('eye-svg');if(!i||!b||!s)return;const O='<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>',X='<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';let show=false;b.addEventListener('click',()=>{show=!show;i.type=show?'text':'password';s.innerHTML=show?X:O})})();

(function(){const f=document.getElementById('login-form'),btn=document.getElementById('submit-btn');f.addEventListener('submit',()=>{btn.classList.add('loading');btn.disabled=true;});})();

(function(){
  const box = document.getElementById('form-error');
  if (!box || !box.dataset.retry) return;
  let remaining = parseInt(box.dataset.retry, 10);
  if (!remaining || remaining <= 0) return;

  const textEl = document.getElementById('error-text');
  const baseMsg = textEl.textContent;
  const submitBtn = document.getElementById('submit-btn');
  submitBtn.disabled = true;

  const fmt = s => `${Math.floor(s / 60)}:${String(s % 60).padStart(2, '0')}`;

  const tick = () => {
    if (remaining <= 0) {
      textEl.textContent = 'Ya puedes intentar de nuevo.';
      submitBtn.disabled = false;
      clearInterval(timer);
      return;
    }
    textEl.textContent = `${baseMsg} ${fmt(remaining)}`;
    remaining--;
  };

  tick();
  const timer = setInterval(tick, 1000);
})();
</script>
</body>
</html>
