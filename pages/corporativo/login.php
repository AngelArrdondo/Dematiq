<?php
require_once __DIR__ . '/../../includes/auth.php';

// Si ya tiene sesión activa, redirigir al panel
if (Auth::check()) {
    header('Location: ../../admin/dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $ip       = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $ua       = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);

    if ($username === '' || $password === '') {
        $error = 'Completa todos los campos.';
    } else {
        $result = Auth::login($username, $password, $ip, $ua);
        if ($result['ok']) {
            header('Location: ../../admin/dashboard.php');
            exit;
        }
        $error = $result['msg'];
    }
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
      width: min(800px, calc(100vw - 40px));
      min-height: 500px;
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
      padding: 38px 32px; display: flex; flex-direction: column; overflow: hidden;
    }
    #net-canvas { position: absolute; inset: 0; opacity: .55; }
    .brand-content { position: relative; z-index: 2; display: flex; flex-direction: column; height: 100%; }

    .brand-logo { display:flex; align-items:center; gap:12px; margin-bottom:28px; animation: fadeRight .7s .1s both; }
    .brand-logo-icon {
      width:42px; height:42px;
      background: linear-gradient(135deg, var(--brand), var(--mid));
      border-radius:11px; display:flex; align-items:center; justify-content:center;
      border:1px solid rgba(255,255,255,.18);
      box-shadow: 0 8px 28px rgba(26,74,158,.6), inset 0 1px 0 rgba(255,255,255,.15);
    }
    .brand-logo-icon img { height:24px; width:auto; object-fit:contain; filter:brightness(0) invert(1); }
    .brand-logo-name { font-size:1.25rem; font-weight:700; letter-spacing:3px;
      background: linear-gradient(90deg,#fff 0%,var(--teal) 100%);
      -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; }
    .brand-logo-sub { font-size:.65rem; color:rgba(255,255,255,.38); letter-spacing:2px; text-transform:uppercase; margin-top:3px; }

    .brand-headline { margin-bottom:22px; animation: fadeRight .7s .2s both; }
    .brand-headline h1 { font-size:1.55rem; font-weight:800; color:#fff; line-height:1.18; letter-spacing:-.5px; }
    .brand-headline h1 em { font-style:normal; background:linear-gradient(90deg,var(--teal),var(--mid));
      -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; }
    .brand-headline p { font-size:.83rem; color:rgba(255,255,255,.45); margin-top:8px; line-height:1.6; }

    .brand-features { flex:1; }
    .feature-item { display:flex; align-items:center; gap:12px; margin-bottom:11px; opacity:0; animation:fadeRight .55s both; }
    .feature-item:nth-child(1){animation-delay:.3s} .feature-item:nth-child(2){animation-delay:.38s}
    .feature-item:nth-child(3){animation-delay:.46s} .feature-item:nth-child(4){animation-delay:.54s}
    .feature-dot { width:32px;height:32px;flex-shrink:0;border-radius:9px;
      background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);
      display:flex;align-items:center;justify-content:center; }
    .feature-dot svg { width:15px;height:15px;color:var(--teal); }
    .feature-item span { font-size:.83rem;font-weight:500;color:rgba(255,255,255,.65); }

    @keyframes fadeRight { from{opacity:0;transform:translateX(-20px)} to{opacity:1;transform:translateX(0)} }

    .brand-footer { margin-top:36px;display:flex;align-items:center;justify-content:space-between; animation:fadeRight .7s .62s both; }
    .clock-time { font-size:1.45rem;font-weight:700;color:#fff;letter-spacing:2px;font-variant-numeric:tabular-nums; }
    .clock-date { font-size:.68rem;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:1.5px;margin-top:3px; }
    .secure-badge { display:flex;align-items:center;gap:6px;background:rgba(0,255,185,.08);
      border:1px solid rgba(0,255,185,.22);border-radius:20px;padding:5px 12px;
      font-size:.7rem;font-weight:600;color:var(--teal); }
    .secure-badge .pulse { width:7px;height:7px;background:#22c55e;border-radius:50%;
      box-shadow:0 0 0 0 rgba(34,197,94,.5);animation:pulse 2s infinite; }
    @keyframes pulse { 0%{box-shadow:0 0 0 0 rgba(34,197,94,.5)} 70%{box-shadow:0 0 0 6px rgba(34,197,94,0)} 100%{box-shadow:0 0 0 0 rgba(34,197,94,0)} }

    /* ── Panel derecho ── */
    .form-panel {
      flex:1; background:var(--form-bg);
      padding:38px 40px 34px; display:flex; flex-direction:column; justify-content:center;
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

    .field-wrap { position:relative; margin-bottom:18px; animation:fadeLeft .65s .25s both; }
    .field-icon { position:absolute;left:16px;top:50%;transform:translateY(-50%);
      color:var(--text-lt);pointer-events:none;transition:color .22s;z-index:2; }
    .field-icon svg { width:17px;height:17px;display:block; }
    .field-wrap input {
      width:100%; padding:20px 46px 8px 46px;
      background:var(--white);border:1.5px solid var(--border);border-radius:13px;
      font-size:.95rem;font-family:inherit;color:var(--text-dark);outline:none;
      transition:border-color .22s,box-shadow .22s;
    }
    .field-wrap input::placeholder { color:transparent; }
    .field-wrap label { position:absolute;left:46px;top:50%;transform:translateY(-50%);
      font-size:.88rem;font-weight:500;color:var(--text-lt);pointer-events:none;transition:all .22s cubic-bezier(.4,0,.2,1); }
    .field-wrap input:focus,
    .field-wrap input:not(:placeholder-shown) { border-color:var(--mid);box-shadow:0 0 0 4px rgba(46,107,207,.1); }
    .field-wrap input:focus + label,
    .field-wrap input:not(:placeholder-shown) + label {
      top:10px;transform:translateY(0);font-size:.64rem;font-weight:700;
      letter-spacing:.6px;text-transform:uppercase;color:var(--brand);
    }
    .field-wrap input:focus ~ .field-icon { color:var(--mid); }

    .eye-btn {
      position:absolute;right:14px;top:50%;transform:translateY(-50%);
      background:none;border:none;cursor:pointer;color:var(--text-lt);
      padding:5px;border-radius:8px;display:flex;align-items:center;transition:color .18s,background .18s;z-index:3;
    }
    .eye-btn:hover { color:var(--mid);background:rgba(26,74,158,.07); }
    .eye-btn svg { width:17px;height:17px; }

    .btn-wrap { animation:fadeLeft .65s .35s both; }
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

    .form-divider { display:flex;align-items:center;gap:12px;margin:22px 0 0;animation:fadeLeft .65s .42s both; }
    .form-divider::before,.form-divider::after { content:'';flex:1;height:1px;background:var(--border); }
    .form-divider span { font-size:.74rem;color:var(--text-lt);white-space:nowrap;font-weight:500; }

    .back-link { text-align:center;margin-top:22px;animation:fadeLeft .65s .5s both; }
    .back-link a { font-size:.8rem;font-weight:500;color:var(--text-lt);text-decoration:none;
      display:inline-flex;align-items:center;gap:5px;transition:color .18s; }
    .back-link a:hover { color:var(--teal); }
    .back-link a svg { width:13px;height:13px; }

    @media (max-width:600px) {
      html { height:auto;overflow-x:hidden; }
      body { height:auto;overflow-y:auto;align-items:flex-start;min-height:100dvh; }
      .login-scene { padding:0;width:100%;min-height:100dvh;perspective:none;display:flex;align-items:stretch; }
      .glow-ring { display:none; }
      .login-card { flex-direction:column;width:100%;min-height:100dvh;border-radius:0;box-shadow:none; }
      .brand-panel { flex:none;padding:20px 24px; }
      .brand-content { flex-direction:row;align-items:center;justify-content:space-between;height:auto; }
      .brand-logo { margin-bottom:0; }
      .brand-headline,.brand-features { display:none; }
      .brand-footer { margin-top:0;flex-direction:column;align-items:flex-end; }
      .brand-clock  { display:none; }
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
          <div class="brand-logo-icon">
            <img src="../../assets/images/logos/logo1.png" alt="DEMATIQ" onerror="this.style.display='none'">
          </div>
          <div>
            <div class="brand-logo-name">DEMATIQ</div>
            <div class="brand-logo-sub">Admin Panel</div>
          </div>
        </div>
        <div class="brand-headline">
          <h1>Control total<br>en tus <em>manos.</em></h1>
          <p>Gestiona tu empresa desde un solo lugar, rápido y seguro.</p>
        </div>
        <div class="brand-features">
          <div class="feature-item">
            <div class="feature-dot"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg></div>
            <span>Gestión de contenido web</span>
          </div>
          <div class="feature-item">
            <div class="feature-dot"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg></div>
            <span>Estadísticas en tiempo real</span>
          </div>
          <div class="feature-item">
            <div class="feature-dot"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 01-2.83 2.83l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg></div>
            <span>Configuración avanzada</span>
          </div>
        </div>
        <div class="brand-footer">
          <div class="brand-clock">
            <div class="clock-time" id="clock-time">00:00:00</div>
            <div class="clock-date" id="clock-date">—</div>
          </div>
          <div class="secure-badge"><span class="pulse"></span>SSL Seguro</div>
        </div>
      </div>
    </div>

    <!-- Panel derecho -->
    <div class="form-panel">
      <div class="form-header">
        <h2>Bienvenido de vuelta</h2>
        <p>Ingresa tus credenciales para acceder al panel de administración.</p>
      </div>

      <?php if ($error): ?>
      <div class="form-error show">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <?= htmlspecialchars($error) ?>
      </div>
      <?php endif; ?>

      <form method="POST" action="" autocomplete="off">

        <div class="field-wrap">
          <input type="email" id="login-user" name="username"
                 value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                 placeholder="Correo electrónico" autocomplete="email" required autofocus>
          <label for="login-user">Correo electrónico</label>
          <span class="field-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
          </span>
        </div>

        <div class="field-wrap">
          <input type="password" id="login-pass" name="password"
                 placeholder="Contraseña" autocomplete="current-password" required>
          <label for="login-pass">Contraseña</label>
          <span class="field-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
          </span>
          <button type="button" class="eye-btn" id="eye-btn" tabindex="-1">
            <svg id="eye-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
            </svg>
          </button>
        </div>

        <div class="btn-wrap">
          <button type="submit" class="submit-btn">
            <span>Acceder al panel</span>
            <span class="arrow">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </span>
          </button>
        </div>
      </form>

      <div class="form-divider"><span>DEMATIQ © 2024</span></div>
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
/* Background canvas y animaciones — igual que login.php */
(function(){const c=document.getElementById('bg-canvas'),x=c.getContext('2d');let W,H,s=[];function r(){W=c.width=innerWidth;H=c.height=innerHeight}function mk(){return{x:Math.random()*W,y:Math.random()*H,r:Math.random()*1.2+.3,a:Math.random(),da:(Math.random()*.4+.1)*(Math.random()<.5?1:-1)*.008,vx:(Math.random()-.5)*.15,vy:(Math.random()-.5)*.15}}function init(){r();s=Array.from({length:160},mk)}function draw(){x.clearRect(0,0,W,H);for(const p of s){p.x+=p.vx;p.y+=p.vy;p.a+=p.da;if(p.a<0||p.a>1)p.da*=-1;if(p.x<-2)p.x=W+2;if(p.x>W+2)p.x=-2;if(p.y<-2)p.y=H+2;if(p.y>H+2)p.y=-2;x.beginPath();x.arc(p.x,p.y,p.r,0,Math.PI*2);x.fillStyle=`rgba(200,220,255,${p.a*.55})`;x.fill()}requestAnimationFrame(draw)}addEventListener('resize',r);init();draw()})();

(function(){const c=document.getElementById('net-canvas'),x=c.getContext('2d'),p=c.parentElement;let W,H,n=[];const C=38,D=110;function r(){const rc=p.getBoundingClientRect();W=c.width=rc.width;H=c.height=rc.height}function mk(){return{x:Math.random()*W,y:Math.random()*H,vx:(Math.random()-.5)*.55,vy:(Math.random()-.5)*.55,r:Math.random()*2+1.2}}function draw(){x.clearRect(0,0,W,H);for(const a of n){a.x+=a.vx;a.y+=a.vy;if(a.x<0||a.x>W)a.vx*=-1;if(a.y<0||a.y>H)a.vy*=-1;for(const b of n){const dx=a.x-b.x,dy=a.y-b.y,d=Math.sqrt(dx*dx+dy*dy);if(d<D){x.beginPath();x.moveTo(a.x,a.y);x.lineTo(b.x,b.y);const al=(1-d/D)*.35,r2=Math.round(0+(46-0)*(d/D)),g2=Math.round(255+(107-255)*(d/D)),b2=Math.round(185+(207-185)*(d/D));x.strokeStyle=`rgba(${r2},${g2},${b2},${al})`;x.lineWidth=.8;x.stroke()}}x.beginPath();x.arc(a.x,a.y,a.r,0,Math.PI*2);x.fillStyle='rgba(0,255,185,.65)';x.fill()}requestAnimationFrame(draw)}function init(){r();n=Array.from({length:C},mk);draw()}addEventListener('resize',()=>r());init()})();

(function(){const t=document.getElementById('clock-time'),d=document.getElementById('clock-date');if(!t)return;const D=['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'],M=['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];function tick(){const n=new Date();t.textContent=`${String(n.getHours()).padStart(2,'0')}:${String(n.getMinutes()).padStart(2,'0')}:${String(n.getSeconds()).padStart(2,'0')}`;d.textContent=`${D[n.getDay()]} ${n.getDate()} ${M[n.getMonth()]} ${n.getFullYear()}`}tick();setInterval(tick,1000)})();

(function(){const i=document.getElementById('login-pass'),b=document.getElementById('eye-btn'),s=document.getElementById('eye-svg');const O='<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>',X='<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';let show=false;b.addEventListener('click',()=>{show=!show;i.type=show?'text':'password';s.innerHTML=show?X:O})})();
</script>
</body>
</html>
