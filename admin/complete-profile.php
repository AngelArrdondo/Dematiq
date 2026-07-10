<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/conexion.php';
$user      = Auth::require('/pages/corporativo/login.php', false);
$csrfToken = Auth::csrfToken();

if (Auth::isProfileComplete($user['id'])) {
    header('Location: /admin/dashboard.php');
    exit;
}

$initials    = strtoupper(substr($user['nombre'] ?? $user['username'], 0, 1));
$displayName = $user['nombre'] ?? $user['username'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Completa tu perfil | DEMATIQ Admin</title>
  <link rel="icon" type="image/svg+xml" href="../assets/images/logos/favicon-d.svg">
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
      --danger:    #ef4444;
      --success:   #22c55e;
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

    /* ── Scene & card ── */
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
      width: min(860px, calc(100vw - 40px));
      min-height: 540px;
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
      flex: 0 0 40%; position: relative;
      background: linear-gradient(155deg, #0d2155 0%, #000028 100%);
      padding: 36px 28px; display: flex; flex-direction: column; overflow: hidden;
    }
    #net-canvas { position: absolute; inset: 0; opacity: .55; }
    .brand-content {
      position: relative; z-index: 2;
      display: flex; flex-direction: column;
      align-items: center; justify-content: space-between;
      height: 100%; text-align: center;
    }

    .brand-logo { display:flex; align-items:center; justify-content:center; animation: fadeRight .7s .1s both; }
    .brand-logo img { width: 85%; max-width: 220px; height: auto; object-fit: contain; }

    .brand-divider {
      width: 100%; display: flex; align-items: center; gap: 12px;
      margin: 12px 0; animation: fadeRight .7s .25s both;
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

    /* Avatar en panel izquierdo */
    .brand-avatar-wrap {
      display: flex; flex-direction: column; align-items: center;
      gap: 10px; flex: 1; justify-content: center;
      animation: fadeRight .7s .3s both;
    }
    .brand-avatar-ring {
      position: relative; cursor: pointer;
    }
    .brand-avatar-circle {
      width: 124px; height: 124px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--brand), var(--glow));
      color: #fff;
      font-size: 3.2rem; font-weight: 900;
      display: flex; align-items: center; justify-content: center;
      overflow: hidden;
      border: 3px solid rgba(255,255,255,.15);
      transition: border-color .2s, box-shadow .2s;
    }
    .brand-avatar-ring:hover .brand-avatar-circle {
      border-color: var(--teal);
      box-shadow: 0 0 0 4px rgba(0,255,185,.18);
    }
    .brand-avatar-circle img { width: 100%; height: 100%; object-fit: cover; }
    .brand-avatar-overlay {
      position: absolute; inset: 0; border-radius: 50%;
      background: rgba(0,0,0,.5);
      display: flex; flex-direction: column;
      align-items: center; justify-content: center; gap: 3px;
      opacity: 0; transition: opacity .18s;
    }
    .brand-avatar-ring:hover .brand-avatar-overlay { opacity: 1; }
    .brand-avatar-overlay span {
      font-size: .6rem; font-weight: 700; color: #fff;
      letter-spacing: .05em; text-transform: uppercase;
    }
    .brand-avatar-name {
      font-size: .82rem; font-weight: 600; color: rgba(255,255,255,.75);
      letter-spacing: .2px;
    }
    .brand-avatar-hint {
      font-size: .65rem; color: rgba(255,255,255,.3);
    }
    @keyframes specBadgePulse {
      0%   { box-shadow: 0 0 0 0 rgba(0,255,185,.55); }
      70%  { box-shadow: 0 0 0 8px rgba(0,255,185,0); }
      100% { box-shadow: 0 0 0 0 rgba(0,255,185,0); }
    }
    .spec-badge {
      position: absolute; top: -6px; right: -6px; z-index: 5;
      display: flex; align-items: center; justify-content: center;
      width: 22px; height: 22px; border-radius: 50%;
      background: var(--teal); color: #04211a;
      border: 2px solid rgba(255,255,255,.9);
      box-shadow: 0 1px 5px rgba(0,0,0,.45);
      font: 800 13px/1 -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
      cursor: help;
      animation: specBadgePulse 1.4s ease-out 2;
      transition: transform .15s ease;
    }
    .spec-badge:hover, .spec-badge:focus-visible { transform: scale(1.15); outline: none; }
    .spec-badge::after {
      content: attr(data-tip);
      position: absolute; bottom: calc(100% + 11px); right: -4px;
      transform: translateY(4px);
      background: #0a0f24; color: #fff;
      font: 500 12.5px/1.5 -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
      padding: 10px 13px; border-radius: 9px;
      width: max-content; max-width: 230px; white-space: normal; text-align: left;
      opacity: 0; visibility: hidden; pointer-events: none;
      transition: opacity .15s ease, transform .15s ease;
      box-shadow: 0 10px 26px rgba(0,0,0,.45); z-index: 60;
    }
    .spec-badge:hover::after, .spec-badge:focus-visible::after {
      opacity: 1; visibility: visible; transform: translateY(0);
    }

    .version-badge {
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
      flex: 1; background: var(--form-bg);
      padding: 36px 36px 28px;
      display: flex; flex-direction: column; justify-content: center;
      overflow-y: auto;
    }

    .form-header { margin-bottom: 20px; animation: fadeLeft .65s .15s both; }
    .form-header h2 { font-size: 1.35rem; font-weight: 700; color: var(--text-dark); letter-spacing: -.4px; }
    .form-header p  { font-size: .83rem; color: var(--text-lt); margin-top: 5px; line-height: 1.5; }
    @keyframes fadeLeft { from{opacity:0;transform:translateX(20px)} to{opacity:1;transform:translateX(0)} }

    /* ── Sección ── */
    .form-section-label {
      font-size: .68rem; font-weight: 700;
      color: var(--text-lt); text-transform: uppercase;
      letter-spacing: .07em;
      display: flex; align-items: center; gap: 8px;
      margin: 14px 0 10px;
      animation: fadeLeft .65s .2s both;
    }
    .form-section-label::after {
      content: ''; flex: 1; height: 1px; background: var(--border);
    }

    /* ── Campo ── */
    .field-row {
      display: grid; grid-template-columns: 1fr 1fr; gap: 12px;
      animation: fadeLeft .65s .25s both;
    }
    .field-wrap { margin-bottom: 12px; }
    .field-wrap label {
      display: block; margin-bottom: 6px; padding-left: 2px;
      font-size: .73rem; font-weight: 700; color: var(--text-lt);
      letter-spacing: .4px; text-transform: uppercase; transition: color .22s;
    }
    .field-wrap label .req { color: var(--brand); font-weight: 900; font-size: .8rem; }
    .field-wrap:focus-within label { color: var(--brand); }
    .input-row { position: relative; }
    .field-icon {
      position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
      color: var(--text-lt); pointer-events: none; transition: color .22s; z-index: 2;
    }
    .field-icon svg { width: 15px; height: 15px; display: block; }
    .field-wrap:focus-within .field-icon { color: var(--mid); }
    .field-wrap input {
      width: 100%; padding: 11px 12px 11px 38px;
      background: var(--white); border: 1.5px solid var(--border); border-radius: 12px;
      font-size: .9rem; font-family: inherit; color: var(--text-dark); outline: none;
      transition: border-color .22s, box-shadow .22s;
    }
    .field-wrap input::placeholder { color: #c0ccdf; }
    .field-wrap input:focus { border-color: var(--mid); box-shadow: 0 0 0 4px rgba(46,107,207,.1); }
    .field-wrap input.is-error { border-color: var(--danger); box-shadow: 0 0 0 3px rgba(239,68,68,.1); }
    .field-wrap input.is-ok    { border-color: var(--success); }

    /* ── Error por campo ── */
    .field-err {
      display: none; align-items: center; gap: 4px;
      font-size: .73rem; color: var(--danger); margin-top: 4px; padding-left: 2px;
    }
    .field-err.show { display: flex; }
    .field-err svg { flex-shrink: 0; }

    /* ── Botón guardar ── */
    .btn-wrap { margin-top: 18px; animation: fadeLeft .65s .4s both; }
    .submit-btn {
      width: 100%; padding: 14px 24px;
      background: linear-gradient(135deg, var(--brand) 0%, var(--mid) 60%, var(--glow) 100%);
      background-size: 220% 220%;
      color: #fff; border: none; border-radius: 13px;
      font-size: .98rem; font-weight: 700; font-family: inherit; letter-spacing: .3px;
      cursor: pointer; overflow: hidden; position: relative;
      transition: transform .22s, box-shadow .22s;
      box-shadow: 0 8px 28px rgba(26,74,158,.42);
      animation: gradShift 5s ease infinite;
      display: flex; align-items: center; justify-content: center; gap: 8px;
    }
    @keyframes gradShift { 0%{background-position:0% 50%} 50%{background-position:100% 50%} 100%{background-position:0% 50%} }
    .submit-btn:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 14px 36px rgba(26,74,158,.55); }
    .submit-btn:active:not(:disabled) { transform: translateY(0); }
    .submit-btn:disabled { opacity: .65; cursor: not-allowed; transform: none; }
    .submit-btn .arrow { display: inline-flex; transition: transform .22s; }
    .submit-btn:hover:not(:disabled) .arrow { transform: translateX(4px); }
    .submit-btn .arrow svg { width: 17px; height: 17px; }

    .spinner {
      display: none; width: 18px; height: 18px;
      border: 2px solid rgba(255,255,255,.3);
      border-top-color: #fff; border-radius: 50%;
      animation: spin .6s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }
    .submit-btn.loading .spinner { display: block; }
    .submit-btn.loading .btn-label,
    .submit-btn.loading .arrow  { display: none; }

    /* ── Mensaje global ── */
    .form-msg {
      display: none; align-items: center; gap: 9px;
      border-radius: 10px; padding: 11px 14px;
      font-size: .82rem; font-weight: 500; margin-top: 12px;
    }
    .form-msg.ok    { background: #f0fdf4; border: 1px solid #bbf7d0; border-left: 3px solid var(--success); color: #15803d; }
    .form-msg.error { background: #fff1f1; border: 1px solid #fca5a5; border-left: 3px solid var(--danger); color: #b91c1c;
                      animation: shake .45s cubic-bezier(.36,.07,.19,.97) both; }
    .form-msg.show  { display: flex; }
    @keyframes shake { 10%,90%{transform:translateX(-2px)} 20%,80%{transform:translateX(4px)} 30%,50%,70%{transform:translateX(-6px)} 40%,60%{transform:translateX(6px)} }

    .form-footer {
      display: flex; align-items: center; gap: 12px;
      margin-top: 18px; animation: fadeLeft .65s .5s both;
    }
    .form-footer::before, .form-footer::after {
      content: ''; flex: 1; height: 1px; background: var(--border);
    }
    .form-footer span { font-size: .73rem; color: var(--text-lt); white-space: nowrap; font-weight: 500; }

    /* ── Responsive ── */
    @media (max-width: 640px) {
      html { height: auto; overflow-x: hidden; }
      body { height: auto; overflow-y: auto; align-items: flex-start; min-height: 100dvh; overflow: auto; }
      .login-scene { padding: 0; width: 100%; min-height: 100dvh; perspective: none; display: flex; align-items: stretch; }
      .glow-ring { display: none; }
      .login-card { flex-direction: column; width: 100%; min-height: 100dvh; border-radius: 0; box-shadow: none; }

      .brand-panel { flex: none; padding: 18px 20px 14px; }
      .brand-content { flex-direction: row; align-items: center; justify-content: space-between; height: auto; }
      .brand-logo { flex: none; }
      .brand-logo img { width: 140px; }
      .brand-divider, .version-badge { display: none; }
      .brand-avatar-wrap { flex-direction: row; gap: 12px; justify-content: flex-end; flex: none; }
      .brand-avatar-circle { width: 52px; height: 52px; font-size: 1.4rem; }
      .brand-avatar-overlay span { font-size: .55rem; }
      .brand-avatar-name  { font-size: .75rem; }
      .brand-avatar-hint  { display: none; }

      .form-panel { flex: 1; padding: 24px 20px 36px; justify-content: flex-start; }
      .field-wrap input { font-size: 16px; }
      .submit-btn { min-height: 52px; }
    }

    @media (max-width: 420px) {
      .field-row { grid-template-columns: 1fr; gap: 0; }
    }
  </style>
</head>
<body>

<canvas id="bg-canvas"></canvas>
<div class="bg-overlay"></div>

<div class="login-scene">
  <div class="glow-ring"></div>
  <div class="login-card">

    <!-- Panel izquierdo — marca + avatar -->
    <div class="brand-panel">
      <canvas id="net-canvas"></canvas>
      <div class="brand-content">

        <div class="brand-logo">
          <img src="../assets/images/logos/logo1.webp" alt="DEMATIQ"
               onerror="this.src='../assets/images/logos/DEMATIQ_logo_blanco_fondo_azul.webp'">
        </div>

        <div class="brand-divider"><div class="brand-divider-dot"></div></div>

        <div class="brand-avatar-wrap">
          <div class="brand-avatar-ring"
               onclick="document.getElementById('avatarFile').click()"
               title="Subir foto de perfil">
            <div class="brand-avatar-circle" id="setupAvatar"><?= $initials ?></div>
            <div class="brand-avatar-overlay">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
                <polyline points="17 8 12 3 7 8"/>
                <line x1="12" y1="3" x2="12" y2="15"/>
              </svg>
              <span>Subir foto</span>
            </div>
            <span class="spec-badge" tabindex="0" onclick="event.stopPropagation()" data-tip="Foto cuadrada (1:1), mínimo 300×300px, con tu rostro centrado y ocupando la mayor parte del encuadre — se recorta en círculo, así que cualquier cosa cerca de las esquinas se corta.">i</span>
          </div>
          <div class="brand-avatar-name"><?= htmlspecialchars($displayName) ?></div>
          <div class="brand-avatar-hint">Toca para cambiar foto</div>
          <input type="file" id="avatarFile" accept="image/jpeg,image/png,image/webp,image/gif"
                 style="display:none" onchange="uploadAvatar(this)">
        </div>

        <div class="brand-divider"><div class="brand-divider-dot"></div></div>

        <div class="version-badge">Perfil &nbsp;·&nbsp; <span>Paso inicial</span></div>

      </div>
    </div>

    <!-- Panel derecho — formulario -->
    <div class="form-panel">

      <div class="form-header">
        <h2>Completa tu perfil</h2>
        <p>Necesitamos algunos datos antes de continuar.<br>Los campos con <strong style="color:var(--brand)">*</strong> son obligatorios.</p>
      </div>

      <!-- Datos personales -->
      <div class="form-section-label">Datos personales</div>

      <div class="field-row">
        <div class="field-wrap">
          <label for="spn">Primer nombre <span class="req">*</span></label>
          <div class="input-row">
            <input type="text" id="spn" maxlength="60" placeholder="Ej. Juan" autocomplete="given-name">
            <span class="field-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </span>
          </div>
          <div class="field-err" id="err-spn">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            Campo requerido
          </div>
        </div>
        <div class="field-wrap">
          <label for="ssn">Segundo nombre</label>
          <div class="input-row">
            <input type="text" id="ssn" maxlength="60" placeholder="Opcional" autocomplete="additional-name">
            <span class="field-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </span>
          </div>
        </div>
      </div>

      <div class="field-row" style="animation-delay:.3s">
        <div class="field-wrap">
          <label for="sap">Apellido paterno <span class="req">*</span></label>
          <div class="input-row">
            <input type="text" id="sap" maxlength="60" placeholder="Ej. García" autocomplete="family-name">
            <span class="field-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
            </span>
          </div>
          <div class="field-err" id="err-sap">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            Campo requerido
          </div>
        </div>
        <div class="field-wrap">
          <label for="sam">Apellido materno <span class="req">*</span></label>
          <div class="input-row">
            <input type="text" id="sam" maxlength="60" placeholder="Ej. López" autocomplete="family-name">
            <span class="field-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
            </span>
          </div>
          <div class="field-err" id="err-sam">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            Campo requerido
          </div>
        </div>
      </div>

      <!-- Contacto -->
      <div class="form-section-label" style="animation-delay:.35s">Contacto</div>

      <div class="field-row" style="animation-delay:.38s">
        <div class="field-wrap">
          <label for="sem">Email <span class="req">*</span></label>
          <div class="input-row">
            <input type="email" id="sem" maxlength="150" placeholder="correo@ejemplo.com" autocomplete="email">
            <span class="field-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            </span>
          </div>
          <div class="field-err" id="err-sem">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span id="err-sem-text">Campo requerido</span>
          </div>
        </div>
        <div class="field-wrap">
          <label for="stel">Teléfono <span class="req">*</span></label>
          <div class="input-row">
            <input type="tel" id="stel" maxlength="30" placeholder="+52 55 0000 0000" autocomplete="tel">
            <span class="field-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.8 19.79 19.79 0 01.99 1.18 2 2 0 012.98 0h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L7.09 7.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 14.92z"/></svg>
            </span>
          </div>
          <div class="field-err" id="err-stel">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            Campo requerido
          </div>
        </div>
      </div>

      <div class="btn-wrap">
        <button class="submit-btn" id="saveBtn" onclick="saveProfile()">
          <span class="spinner"></span>
          <span class="btn-label">Guardar y continuar</span>
          <span class="arrow">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
          </span>
        </button>
      </div>

      <div class="form-msg" id="formMsg"></div>

      <div class="form-footer"><span>DEMATIQ © 2025</span></div>

    </div><!-- /form-panel -->

  </div><!-- /login-card -->
</div><!-- /login-scene -->

<script>
const CSRF_TOKEN = '<?= $csrfToken ?>';

/* ── Partículas de fondo ── */
(function(){const c=document.getElementById('bg-canvas'),x=c.getContext('2d');let W,H,s=[];function r(){W=c.width=innerWidth;H=c.height=innerHeight}function mk(){return{x:Math.random()*W,y:Math.random()*H,r:Math.random()*1.2+.3,a:Math.random(),da:(Math.random()*.4+.1)*(Math.random()<.5?1:-1)*.008,vx:(Math.random()-.5)*.15,vy:(Math.random()-.5)*.15}}function init(){r();s=Array.from({length:160},mk)}function draw(){x.clearRect(0,0,W,H);for(const p of s){p.x+=p.vx;p.y+=p.vy;p.a+=p.da;if(p.a<0||p.a>1)p.da*=-1;if(p.x<-2)p.x=W+2;if(p.x>W+2)p.x=-2;if(p.y<-2)p.y=H+2;if(p.y>H+2)p.y=-2;x.beginPath();x.arc(p.x,p.y,p.r,0,Math.PI*2);x.fillStyle=`rgba(200,220,255,${p.a*.55})`;x.fill()}requestAnimationFrame(draw)}addEventListener('resize',r);init();draw()})();

/* ── Red de nodos (panel izquierdo) ── */
(function(){const c=document.getElementById('net-canvas'),x=c.getContext('2d'),p=c.parentElement;let W,H,n=[];const C=38,D=110;function r(){const rc=p.getBoundingClientRect();W=c.width=rc.width;H=c.height=rc.height}function mk(){return{x:Math.random()*W,y:Math.random()*H,vx:(Math.random()-.5)*.55,vy:(Math.random()-.5)*.55,r:Math.random()*2+1.2}}function draw(){x.clearRect(0,0,W,H);for(const a of n){a.x+=a.vx;a.y+=a.vy;if(a.x<0||a.x>W)a.vx*=-1;if(a.y<0||a.y>H)a.vy*=-1;for(const b of n){const dx=a.x-b.x,dy=a.y-b.y,d=Math.sqrt(dx*dx+dy*dy);if(d<D){x.beginPath();x.moveTo(a.x,a.y);x.lineTo(b.x,b.y);const al=(1-d/D)*.35,r2=Math.round(0+(46-0)*(d/D)),g2=Math.round(255+(107-255)*(d/D)),b2=Math.round(185+(207-185)*(d/D));x.strokeStyle=`rgba(${r2},${g2},${b2},${al})`;x.lineWidth=.8;x.stroke()}}x.beginPath();x.arc(a.x,a.y,a.r,0,Math.PI*2);x.fillStyle='rgba(0,255,185,.65)';x.fill()}requestAnimationFrame(draw)}function init(){r();n=Array.from({length:C},mk);draw()}addEventListener('resize',()=>r());init()})();

/* ── Validación por campo ── */
function setErr(id, msg) {
  const inp = document.getElementById(id);
  inp.classList.add('is-error'); inp.classList.remove('is-ok');
  const el = document.getElementById('err-' + id);
  if (!el) return;
  const sp = el.querySelector('span') || el.childNodes[el.childNodes.length - 1];
  if (msg && sp) { if (sp.nodeType === 3) sp.textContent = msg; else sp.textContent = msg; }
  el.classList.add('show');
}
function clearErr(id) {
  const inp = document.getElementById(id);
  inp.classList.remove('is-error');
  const el = document.getElementById('err-' + id);
  if (el) el.classList.remove('show');
}
function setOk(id) {
  document.getElementById(id).classList.remove('is-error');
  document.getElementById(id).classList.add('is-ok');
  clearErr(id);
}

['spn','ssn','sap','sam','sem','stel'].forEach(id =>
  document.getElementById(id).addEventListener('input', () => clearErr(id))
);

/* ── Mensaje global ── */
function showMsg(msg, type = 'ok') {
  const el = document.getElementById('formMsg');
  el.textContent = msg;
  el.className = 'form-msg show ' + type;
  if (type === 'error') void el.offsetWidth; // re-trigger shake
  el.className = 'form-msg show ' + type;
}

/* ── Guardar perfil ── */
async function saveProfile() {
  const pn  = document.getElementById('spn').value.trim();
  const sn  = document.getElementById('ssn').value.trim();
  const ap  = document.getElementById('sap').value.trim();
  const am  = document.getElementById('sam').value.trim();
  const em  = document.getElementById('sem').value.trim();
  const tel = document.getElementById('stel').value.trim();

  let valid = true;
  if (!pn) { setErr('spn'); valid = false; } else setOk('spn');
  if (!ap) { setErr('sap'); valid = false; } else setOk('sap');
  if (!am) { setErr('sam'); valid = false; } else setOk('sam');
  if (!em) { setErr('sem', 'Campo requerido'); valid = false; }
  else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(em)) { setErr('sem', 'Correo inválido'); valid = false; }
  else setOk('sem');
  if (!tel) { setErr('stel'); valid = false; } else setOk('stel');

  if (!valid) {
    const first = document.querySelector('.is-error');
    if (first) first.scrollIntoView({ behavior: 'smooth', block: 'center' });
    return;
  }

  const btn = document.getElementById('saveBtn');
  btn.disabled = true; btn.classList.add('loading');

  const fd = new FormData();
  fd.append('action',           'update_info');
  fd.append('primer_nombre',    pn);
  fd.append('segundo_nombre',   sn);
  fd.append('apellido_paterno', ap);
  fd.append('apellido_materno', am);
  fd.append('email_contacto',   em);
  fd.append('telefono',         tel);

  try {
    const res  = await fetch('api/profile.php', { method: 'POST', headers: { 'X-CSRF-Token': CSRF_TOKEN }, body: fd });
    const text = await res.text();
    let json;
    try { json = JSON.parse(text); } catch {
      console.error('Respuesta no-JSON del servidor:', text);
      showMsg(`Error del servidor (HTTP ${res.status}). Revisa la consola para más detalles.`, 'error');
      btn.disabled = false; btn.classList.remove('loading');
      return;
    }
    if (json.ok) {
      showMsg('¡Perfil guardado! Redirigiendo…', 'ok');
      setTimeout(() => { window.location.href = 'dashboard.php'; }, 900);
    } else {
      showMsg(json.msg || 'Error al guardar', 'error');
      btn.disabled = false; btn.classList.remove('loading');
    }
  } catch (e) {
    console.error('Error de red:', e);
    showMsg('No se pudo conectar con el servidor. Revisa que el sitio esté activo.', 'error');
    btn.disabled = false; btn.classList.remove('loading');
  }
}

/* ── Subir avatar ── */
async function uploadAvatar(input) {
  if (!input.files[0]) return;
  if (input.files[0].size > 2 * 1024 * 1024) {
    showMsg('Máximo 2 MB por imagen', 'error'); input.value = ''; return;
  }
  const fd = new FormData();
  fd.append('action', 'upload_avatar');
  fd.append('avatar', input.files[0]);
  try {
    const res  = await fetch('api/profile.php', { method: 'POST', headers: { 'X-CSRF-Token': CSRF_TOKEN }, body: fd });
    const text = await res.text();
    let json;
    try { json = JSON.parse(text); } catch {
      console.error('Respuesta no-JSON al subir avatar:', text);
      showMsg(`Error del servidor (HTTP ${res.status}).`, 'error');
      return;
    }
    if (json.ok) {
      document.getElementById('setupAvatar').innerHTML =
        `<img src="${json.path}?t=${Date.now()}" alt="Avatar">`;
      showMsg('Foto actualizada', 'ok');
    } else {
      showMsg(json.msg || 'Error al subir la foto', 'error');
    }
  } catch (e) {
    console.error('Error de red al subir avatar:', e);
    showMsg('No se pudo conectar con el servidor.', 'error');
  } finally { input.value = ''; }
}
</script>
</body>
</html>
