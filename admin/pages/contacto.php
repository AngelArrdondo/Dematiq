<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/conexion.php';
require_once __DIR__ . '/../../includes/contenido.php';
$user      = Auth::require('/pages/corporativo/login.php');
$content   = Contenido::getAll();
$d         = $content['contacto'] ?? [];
$initials  = strtoupper(substr($user['nombre'], 0, 1));
$csrfToken = Auth::csrfToken();

$fotoPath = '';
try {
    $stmtFoto = $pdo->prepare('SELECT foto FROM usuarios WHERE id = ? LIMIT 1');
    $stmtFoto->execute([$user['id']]);
    $fotoRaw  = $stmtFoto->fetchColumn();
    $fotoPath = $fotoRaw ? '../' . htmlspecialchars($fotoRaw) : '';
} catch (PDOException $e) {}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contacto | DEMATIQ Admin</title>
  <link rel="stylesheet" href="../assets/css/admin.css?v=7">
  <link rel="icon" type="image/svg+xml" href="../../assets/images/logos/favicon-d.svg">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
  <style>
    /* ════════════════════════════════════════════
       CONTACTO ADMIN — Brand-aligned design
       ════════════════════════════════════════════ */

    /* Brand tokens (mirrors site variables.css) */
    :root {
      --brand-dark:   #0d2155;
      --brand-mid:    #1a4a9e;
      --brand-light:  #2e6bcf;
      --brand-xlight: #e8f0fc;
      --wa-green:     #25d366;
      --wa-bg:        #e8f8ef;
    }

    /* ── Section tag ─────────────────────────── */
    .ct-tag {
      display: inline-flex; align-items: center; gap: 6px;
      font-size: .65rem; font-weight: 700; letter-spacing: 2.5px;
      text-transform: uppercase; color: var(--brand-light);
      margin-bottom: 4px;
    }
    .ct-tag::before {
      content: ''; display: block; width: 18px; height: 2px;
      background: var(--brand-light); border-radius: 2px; flex-shrink: 0;
    }

    /* ── Contact card (overrides admin-card) ─── */
    .ct-card {
      background: #fff;
      border: 1px solid #dde5f5;
      border-radius: 16px;
      overflow: hidden;
      margin-bottom: 20px;
      box-shadow: 0 4px 24px rgba(13,33,85,.07);
    }

    .ct-card-head {
      display: flex; align-items: center; gap: 14px;
      padding: 18px 22px 16px;
      border-bottom: 1px solid #edf1fb;
    }

    .ct-icon {
      width: 40px; height: 40px; border-radius: 10px; flex-shrink: 0;
      display: flex; align-items: center; justify-content: center;
      background: linear-gradient(135deg, var(--brand-dark), var(--brand-mid));
    }
    .ct-icon svg { width: 18px; height: 18px; color: #fff; }
    .ct-icon.green { background: linear-gradient(135deg, #128c44, var(--wa-green)); }
    .ct-icon.teal  { background: linear-gradient(135deg, #0e6655, #1abc9c); }
    .ct-icon.navy  { background: linear-gradient(135deg, #000028, var(--brand-dark)); }
    .ct-icon.social{ background: linear-gradient(135deg, #6a11cb, #2575fc); }

    .ct-card-head-text h3 {
      font-size: .95rem; font-weight: 700; color: var(--brand-dark); margin: 0;
    }
    .ct-card-head-text p {
      font-size: .75rem; color: var(--text-lt); margin: 2px 0 0;
    }

    .ct-body { padding: 20px 22px; }

    /* ── Field ───────────────────────────────── */
    .ct-label {
      display: block; font-size: .72rem; font-weight: 700;
      letter-spacing: .6px; text-transform: uppercase;
      color: var(--text-lt); margin-bottom: 7px;
    }
    .ct-input {
      width: 100%; padding: 11px 14px; font-size: 16px;
      border: 1.5px solid #dde5f5; border-radius: 10px;
      color: var(--brand-dark); background: #f8faff; outline: none;
      transition: border-color .15s, box-shadow .15s, background .15s;
      font-family: 'Roboto', sans-serif; box-sizing: border-box;
    }
    .ct-input:focus {
      border-color: var(--brand-mid); background: #fff;
      box-shadow: 0 0 0 3px rgba(26,74,158,.1);
    }
    .ct-input::placeholder { color: #a0afc4; }
    textarea.ct-input { resize: vertical; min-height: 72px; }

    .ct-field { margin-bottom: 18px; }
    .ct-field:last-child { margin-bottom: 0; }

    .ct-row {
      display: grid; grid-template-columns: 1fr 1fr; gap: 16px;
    }
    @media (max-width: 540px) { .ct-row { grid-template-columns: 1fr; } }

    /* ── WhatsApp builder ────────────────────── */
    .wa-builder {
      display: flex; gap: 0; border: 1.5px solid #dde5f5;
      border-radius: 10px; overflow: hidden; background: #f8faff;
      transition: border-color .15s, box-shadow .15s;
    }
    .wa-builder:focus-within {
      border-color: var(--brand-mid); background: #fff;
      box-shadow: 0 0 0 3px rgba(26,74,158,.1);
    }
    .wa-country-sel {
      flex: 0 0 auto; border: none; border-right: 1.5px solid #dde5f5;
      background: transparent; font-size: 14px; color: var(--brand-dark);
      padding: 11px 10px; outline: none; cursor: pointer;
      font-family: 'Roboto', sans-serif; font-weight: 600;
    }
    .wa-number-inp {
      flex: 1; border: none; background: transparent; font-size: 16px;
      color: var(--brand-dark); padding: 11px 14px; outline: none;
      font-family: 'Roboto', sans-serif; min-width: 0;
    }

    .wa-chips {
      display: flex; gap: 10px; margin-top: 10px; flex-wrap: wrap;
    }
    .wa-chip {
      flex: 1; min-width: 140px; border-radius: 10px;
      padding: 10px 14px; border: 1px solid #dde5f5; background: #f8faff;
    }
    .wa-chip-label {
      font-size: .65rem; font-weight: 700; letter-spacing: .8px;
      text-transform: uppercase; color: var(--text-lt); margin-bottom: 4px;
    }
    .wa-chip-val {
      font-size: .88rem; font-weight: 600; color: var(--brand-mid);
      word-break: break-all; font-family: 'Roboto Mono', monospace;
    }
    .wa-chip.green .wa-chip-val { color: var(--wa-green); }

    /* ── Schedule builder ────────────────────── */
    .sched-slot {
      border: 1.5px solid #dde5f5; border-radius: 12px;
      background: #f8faff; padding: 14px 16px; margin-bottom: 10px;
    }
    .sched-slot-top {
      display: flex; align-items: center; gap: 10px;
      flex-wrap: wrap; margin-bottom: 12px;
    }
    .sched-slot-label {
      font-size: .7rem; font-weight: 700; text-transform: uppercase;
      letter-spacing: .8px; color: var(--text-lt); flex: 0 0 auto;
    }
    .sched-days {
      display: flex; gap: 5px; flex-wrap: wrap; flex: 1;
    }
    .sched-day {
      width: 38px; height: 38px; border-radius: 9px;
      border: 1.5px solid #dde5f5; background: #fff;
      font-size: .7rem; font-weight: 700; cursor: pointer;
      color: var(--text-lt); transition: all .14s;
      display: flex; align-items: center; justify-content: center;
      -webkit-tap-highlight-color: transparent;
    }
    .sched-day.on {
      background: linear-gradient(135deg, var(--brand-dark), var(--brand-mid));
      border-color: var(--brand-mid); color: #fff;
      box-shadow: 0 2px 8px rgba(26,74,158,.28);
    }
    .sched-day:hover:not(.on) { border-color: var(--brand-light); color: var(--brand-light); background: var(--brand-xlight); }

    .sched-rm {
      margin-left: auto; width: 32px; height: 32px; border-radius: 8px;
      border: none; background: #fee2e2; color: #dc3545; cursor: pointer;
      display: flex; align-items: center; justify-content: center;
      transition: background .14s; flex-shrink: 0;
    }
    .sched-rm:hover { background: #dc3545; color: #fff; }

    .sched-time-row {
      display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
    }
    .sched-time-label {
      font-size: .72rem; font-weight: 600; color: var(--text-lt); flex-shrink: 0;
    }
    .sched-time-inp {
      padding: 9px 12px; border: 1.5px solid #dde5f5; border-radius: 9px;
      font-size: 15px; color: var(--brand-dark); background: #fff; outline: none;
      transition: border-color .14s; cursor: pointer; font-family: 'Roboto', sans-serif;
    }
    .sched-time-inp:focus { border-color: var(--brand-mid); box-shadow: 0 0 0 3px rgba(26,74,158,.1); }
    .sched-time-sep {
      font-size: 1rem; color: var(--text-lt); font-weight: 700;
    }

    .sched-add {
      width: 100%; padding: 11px; border: 1.5px dashed #c8d8f0;
      border-radius: 10px; background: none; font-size: .82rem;
      color: var(--text-lt); cursor: pointer; transition: all .14s;
      display: flex; align-items: center; justify-content: center; gap: 6px;
      -webkit-tap-highlight-color: transparent;
    }
    .sched-add:hover { border-color: var(--brand-light); color: var(--brand-mid); background: var(--brand-xlight); }

    .sched-preview {
      margin-top: 12px; padding: 11px 14px; border-radius: 10px;
      background: var(--brand-xlight); border: 1px solid #c8d8f0;
      display: flex; align-items: center; gap: 8px;
    }
    .sched-preview-label {
      font-size: .68rem; font-weight: 700; text-transform: uppercase;
      letter-spacing: .8px; color: var(--brand-light); white-space: nowrap; flex-shrink: 0;
    }
    .sched-preview-val {
      font-size: .88rem; font-weight: 600; color: var(--brand-dark);
    }

    /* ── Map ─────────────────────────────────── */
    .map-search {
      display: flex; gap: 8px; margin-bottom: 12px; flex-wrap: wrap;
    }
    .map-search-inp {
      flex: 1; min-width: 160px; padding: 11px 14px; font-size: 16px;
      border: 1.5px solid #dde5f5; border-radius: 10px;
      color: var(--brand-dark); background: #f8faff; outline: none;
      transition: border-color .15s; font-family: 'Roboto', sans-serif;
    }
    .map-search-inp:focus { border-color: var(--brand-mid); background: #fff; box-shadow: 0 0 0 3px rgba(26,74,158,.1); }
    .map-btn {
      display: flex; align-items: center; gap: 7px;
      padding: 11px 16px; border-radius: 10px; border: 1.5px solid #dde5f5;
      background: #fff; font-size: .82rem; font-weight: 600; color: var(--brand-dark);
      cursor: pointer; transition: all .15s; white-space: nowrap;
      -webkit-tap-highlight-color: transparent;
    }
    .map-btn svg { width: 15px; height: 15px; flex-shrink: 0; }
    .map-btn:hover { background: var(--brand-xlight); border-color: var(--brand-mid); color: var(--brand-mid); }
    .map-btn.primary {
      background: linear-gradient(135deg, var(--brand-dark), var(--brand-mid));
      border-color: transparent; color: #fff;
      box-shadow: 0 3px 10px rgba(26,74,158,.28);
    }
    .map-btn.primary:hover { filter: brightness(1.1); }

    #leaflet-map {
      height: 310px; border-radius: 12px; overflow: hidden;
      border: 2.5px solid var(--brand-mid);
      box-shadow: 0 4px 20px rgba(13,33,85,.15), 0 0 0 5px rgba(26,74,158,.1);
      margin-bottom: 12px;
    }

    .map-coords-row {
      display: flex; align-items: center; gap: 10px;
      padding: 10px 14px; background: var(--brand-xlight);
      border-radius: 10px; border: 1px solid #c8d8f0;
    }
    .map-coords-row svg { width: 15px; height: 15px; color: var(--brand-light); flex-shrink: 0; }
    .map-coords-row input {
      flex: 1; border: none; background: transparent; font-size: .88rem;
      font-family: 'Roboto Mono', monospace; color: var(--brand-mid);
      font-weight: 600; outline: none; min-width: 0;
    }
    .map-tip {
      font-size: .72rem; color: var(--text-lt); margin-top: 7px;
      display: flex; align-items: center; gap: 5px;
    }
    .map-tip svg { width: 12px; height: 12px; flex-shrink: 0; }

    /* ── Social grid ─────────────────────────── */
    .social-grid {
      display: grid; grid-template-columns: 1fr 1fr; gap: 12px;
    }
    .social-field {
      background: #f8faff; border: 1.5px solid #dde5f5; border-radius: 12px;
      padding: 14px 16px; transition: border-color .15s;
    }
    .social-field:focus-within { border-color: var(--brand-mid); background: #fff; }
    .social-field-head {
      display: flex; align-items: center; gap: 8px; margin-bottom: 10px;
    }
    .social-ico {
      width: 28px; height: 28px; border-radius: 7px;
      display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .social-ico svg, .social-ico img { width: 16px; height: 16px; }
    .social-ico.fb   { background: #1877f2; color: #fff; }
    .social-ico.ig   { background: linear-gradient(135deg,#f09433,#e6683c,#dc2743,#cc2366,#bc1888); color: #fff; }
    .social-ico.li   { background: #0a66c2; color: #fff; }
    .social-ico.yt   { background: #ff0000; color: #fff; }
    .social-name { font-size: .78rem; font-weight: 700; color: var(--brand-dark); }
    .social-field input {
      width: 100%; border: none; background: transparent; font-size: .84rem;
      color: var(--text-lt); outline: none; font-family: 'Roboto', sans-serif;
      min-width: 0;
    }
    .social-field input::placeholder { color: #b0bdd6; }
    .social-field input:focus { color: var(--brand-dark); }

    @media (max-width: 480px) { .social-grid { grid-template-columns: 1fr; } }

    /* ── Save bar (branded) ──────────────────── */
    .ct-save-bar {
      display: flex; align-items: center; justify-content: flex-end;
      gap: 10px; padding: 16px 0 8px; flex-wrap: wrap;
    }
    .ct-btn-view {
      display: flex; align-items: center; gap: 7px;
      padding: 12px 20px; border-radius: 10px;
      border: 1.5px solid #dde5f5; background: #fff;
      font-size: .88rem; font-weight: 600; color: var(--brand-dark);
      text-decoration: none; transition: all .15s;
      -webkit-tap-highlight-color: transparent;
    }
    .ct-btn-view:hover { border-color: var(--brand-mid); color: var(--brand-mid); background: var(--brand-xlight); }
    .ct-btn-save {
      display: flex; align-items: center; gap: 8px;
      padding: 13px 26px; border-radius: 10px; border: none;
      background: linear-gradient(135deg, var(--brand-dark), var(--brand-mid));
      color: #fff; font-size: .92rem; font-weight: 700; cursor: pointer;
      box-shadow: 0 4px 14px rgba(26,74,158,.35); transition: all .15s;
      -webkit-tap-highlight-color: transparent;
    }
    .ct-btn-save:hover { filter: brightness(1.1); transform: translateY(-1px); box-shadow: 0 6px 18px rgba(26,74,158,.42); }
    .ct-btn-save:active { transform: scale(.98); }
    .ct-btn-save svg, .ct-btn-view svg { width: 16px; height: 16px; flex-shrink: 0; }

    @media (max-width: 420px) {
      .ct-btn-view, .ct-btn-save { width: 100%; justify-content: center; }
    }

    /* ── Overrides for topbar ────────────────── */
    @media (max-width: 600px) {
      .ct-body { padding: 16px; }
      .ct-card-head { padding: 14px 16px 12px; }
    }
  </style>
</head>
<body>

<script>window.__DB_CONTENT = <?= json_encode($content, JSON_UNESCAPED_UNICODE) ?>;</script>

<div id="sidebar-overlay" class="sidebar-overlay"></div>
<aside class="admin-sidebar"></aside>

<div class="admin-main">

  <header class="admin-topbar">
    <div style="display:flex;align-items:center;gap:12px">
      <button id="sidebar-toggle" class="mobile-menu-toggle" aria-label="Abrir menú" aria-expanded="false" aria-controls="admin-sidebar">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
        </svg>
      </button>
      <span class="admin-topbar-title">Contacto</span>
    </div>
    <div class="user-menu" id="userMenuBtn" role="button" tabindex="0" aria-haspopup="true" aria-expanded="false">
      <div class="admin-avatar" id="topbarAvatar" style="overflow:hidden">
        <?php if ($fotoPath): ?>
          <img src="<?= $fotoPath ?>" alt="Avatar" style="width:100%;height:100%;object-fit:cover;border-radius:50%">
        <?php else: ?>
          <?= $initials ?>
        <?php endif; ?>
      </div>
      <div class="user-menu-info">
        <span class="user-menu-name" id="topbarName"><?= htmlspecialchars($user['nombre']) ?></span>
        <span class="user-menu-role">Administrador</span>
      </div>
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="user-menu-chevron" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
      <div class="user-dropdown" id="userDropdown" role="menu">
        <button class="user-dropdown-item" role="menuitem" onclick="openProfileModal()">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          Mi Perfil
        </button>
        <div class="user-dropdown-sep"></div>
        <a class="user-dropdown-item danger" role="menuitem" href="../logout.php">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16,17 21,12 16,7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
          Cerrar sesión
        </a>
      </div>
    </div>
  </header>

  <div class="admin-content">

    <!-- Header -->
    <div class="section-header">
      <div class="ct-tag">Página web</div>
      <h1 style="font-size:1.4rem;font-weight:700;color:var(--brand-dark);margin:4px 0 4px">Información de Contacto</h1>
      <p style="color:var(--text-lt);font-size:.88rem;margin:0">Edita los datos de contacto que se muestran en el sitio.</p>
    </div>

    <!-- ── Email ─────────────────────────────── -->
    <div class="ct-card">
      <div class="ct-card-head">
        <div class="ct-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><polyline points="2,4 12,13 22,4"/></svg>
        </div>
        <div class="ct-card-head-text">
          <h3>Correo electrónico</h3>
          <p>Dirección de email principal de contacto</p>
        </div>
      </div>
      <div class="ct-body">
        <div class="ct-field">
          <label class="ct-label">Email</label>
          <input type="email" id="email" class="ct-input"
            value="<?= htmlspecialchars($d['email'] ?? '') ?>"
            placeholder="ventas@dematiq.com.mx">
        </div>
      </div>
    </div>

    <!-- ── WhatsApp ───────────────────────────── -->
    <div class="ct-card">
      <div class="ct-card-head">
        <div class="ct-icon green">
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.124.558 4.122 1.529 5.855L.057 23.886a.5.5 0 00.611.637l6.239-1.637A11.94 11.94 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22a9.94 9.94 0 01-5.094-1.396l-.364-.217-3.773.99 1.006-3.671-.238-.378A9.952 9.952 0 012 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z"/></svg>
        </div>
        <div class="ct-card-head-text">
          <h3>WhatsApp</h3>
          <p>Selecciona el país y escribe el número local</p>
        </div>
      </div>
      <div class="ct-body">
        <label class="ct-label">País y número</label>
        <div class="wa-builder">
          <select id="wa-country" class="wa-country-sel" onchange="updateWA()">
            <option value="52"  data-fmt="mx">🇲🇽 +52 México</option>
            <option value="1"   data-fmt="us">🇺🇸 +1 EE.UU / Canadá</option>
            <option value="34"  data-fmt="plain">🇪🇸 +34 España</option>
            <option value="55"  data-fmt="plain">🇧🇷 +55 Brasil</option>
            <option value="54"  data-fmt="plain">🇦🇷 +54 Argentina</option>
            <option value="57"  data-fmt="plain">🇨🇴 +57 Colombia</option>
            <option value="56"  data-fmt="plain">🇨🇱 +56 Chile</option>
            <option value="51"  data-fmt="plain">🇵🇪 +51 Perú</option>
            <option value="49"  data-fmt="plain">🇩🇪 +49 Alemania</option>
            <option value="33"  data-fmt="plain">🇫🇷 +33 Francia</option>
            <option value="44"  data-fmt="plain">🇬🇧 +44 Reino Unido</option>
            <option value="39"  data-fmt="plain">🇮🇹 +39 Italia</option>
            <option value="81"  data-fmt="plain">🇯🇵 +81 Japón</option>
            <option value="86"  data-fmt="plain">🇨🇳 +86 China</option>
            <option value="82"  data-fmt="plain">🇰🇷 +82 Corea del Sur</option>
          </select>
          <input type="tel" id="wa-local" class="wa-number-inp"
            placeholder="4427214891" oninput="updateWA()">
        </div>
        <div class="wa-chips">
          <div class="wa-chip">
            <div class="wa-chip-label">Texto visible en el sitio</div>
            <div class="wa-chip-val" id="wa-display-val">—</div>
          </div>
          <div class="wa-chip green">
            <div class="wa-chip-label">Número de enlace (wa.me/…)</div>
            <div class="wa-chip-val" id="wa-link-val">—</div>
          </div>
        </div>
      </div>
    </div>

    <!-- ── Horario ────────────────────────────── -->
    <div class="ct-card">
      <div class="ct-card-head">
        <div class="ct-icon teal">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12,6 12,12 16,14"/></svg>
        </div>
        <div class="ct-card-head-text">
          <h3>Horario de atención</h3>
          <p>Activa los días y ajusta la hora de apertura y cierre</p>
        </div>
      </div>
      <div class="ct-body">
        <div id="sched-slots"></div>
        <button type="button" class="sched-add" onclick="addSlot()">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Agregar otro turno
        </button>
        <div class="sched-preview">
          <span class="sched-preview-label">Vista previa</span>
          <span class="sched-preview-val" id="sched-preview-val">—</span>
        </div>
      </div>
    </div>

    <!-- ── Dirección ──────────────────────────── -->
    <div class="ct-card">
      <div class="ct-card-head">
        <div class="ct-icon navy">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9,22 9,12 15,12 15,22"/></svg>
        </div>
        <div class="ct-card-head-text">
          <h3>Dirección</h3>
          <p>Se llena automáticamente al elegir en el mapa</p>
        </div>
      </div>
      <div class="ct-body">
        <div class="ct-field">
          <label class="ct-label">Dirección completa</label>
          <div style="position:relative">
            <textarea id="direccion" class="ct-input" style="padding-right:42px" placeholder="Haz clic en el mapa o usa tu ubicación…"><?= htmlspecialchars($d['direccion'] ?? '') ?></textarea>
            <span id="dir-auto-badge" title="Se llenó desde el mapa" style="
              display:none; position:absolute; top:10px; right:10px;
              background:var(--brand-xlight); border:1px solid #c8d8f0;
              border-radius:6px; padding:2px 7px; font-size:.65rem;
              font-weight:700; letter-spacing:.6px; text-transform:uppercase;
              color:var(--brand-mid); pointer-events:none; white-space:nowrap;">Auto</span>
          </div>
          <p style="font-size:.72rem;color:var(--text-lt);margin:7px 0 0;display:flex;align-items:center;gap:5px">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            Haz clic en el mapa de abajo y la dirección se llenará sola. También puedes editarla manualmente.
          </p>
        </div>
      </div>
    </div>

    <!-- ── Mapa ───────────────────────────────── -->
    <div class="ct-card">
      <div class="ct-card-head">
        <div class="ct-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
        </div>
        <div class="ct-card-head-text">
          <h3>Ubicación en el mapa</h3>
          <p>Haz clic en el mapa o busca por dirección</p>
        </div>
      </div>
      <div class="ct-body">
        <div class="map-search">
          <input type="text" id="map-search-input" class="map-search-inp"
            placeholder="Buscar empresa, calle o colonia…"
            onkeydown="if(event.key==='Enter')geocodeSearch()">
          <button type="button" class="map-btn" onclick="geocodeSearch()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            Buscar
          </button>
          <button type="button" class="map-btn primary" onclick="getMyLocation()" id="btn-my-loc" title="Usar mi ubicación actual">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3"/><path d="M12 8a4 4 0 100 8 4 4 0 000-8z" opacity=".3"/></svg>
            Mi ubicación
          </button>
        </div>

        <div id="leaflet-map"></div>

        <div class="map-coords-row">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <input type="text" id="map-coords" placeholder="Haz clic en el mapa para fijar coordenadas" readonly>
        </div>
        <p class="map-tip">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          Haz clic en el mapa o arrastra el pin — la dirección se llenará automáticamente
        </p>
      </div>
    </div>

    <!-- ── Redes sociales ─────────────────────── -->
    <div class="ct-card">
      <div class="ct-card-head">
        <div class="ct-icon social">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
        </div>
        <div class="ct-card-head-text">
          <h3>Redes sociales</h3>
          <p>URLs completas de cada perfil</p>
        </div>
      </div>
      <div class="ct-body">
        <div class="social-grid">

          <div class="social-field">
            <div class="social-field-head">
              <div class="social-ico fb">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>
              </div>
              <span class="social-name">Facebook</span>
            </div>
            <input type="url" id="social-fb"
              value="<?= htmlspecialchars($d['social']['facebook'] ?? '') ?>"
              placeholder="https://facebook.com/dematiq">
          </div>

          <div class="social-field">
            <div class="social-field-head">
              <div class="social-ico ig">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
              </div>
              <span class="social-name">Instagram</span>
            </div>
            <input type="url" id="social-ig"
              value="<?= htmlspecialchars($d['social']['instagram'] ?? '') ?>"
              placeholder="https://instagram.com/dematiq">
          </div>

          <div class="social-field">
            <div class="social-field-head">
              <div class="social-ico li">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/></svg>
              </div>
              <span class="social-name">LinkedIn</span>
            </div>
            <input type="url" id="social-li"
              value="<?= htmlspecialchars($d['social']['linkedin'] ?? '') ?>"
              placeholder="https://linkedin.com/company/dematiq">
          </div>

          <div class="social-field">
            <div class="social-field-head">
              <div class="social-ico yt">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M22.54 6.42a2.78 2.78 0 00-1.95-1.96C18.88 4 12 4 12 4s-6.88 0-8.59.46a2.78 2.78 0 00-1.95 1.96A29 29 0 001 12a29 29 0 00.46 5.58A2.78 2.78 0 003.41 19.54C5.12 20 12 20 12 20s6.88 0 8.59-.46a2.78 2.78 0 001.95-1.96A29 29 0 0023 12a29 29 0 00-.46-5.58z"/><polygon points="9.75,15.02 15.5,12 9.75,8.98 9.75,15.02" fill="#fff"/></svg>
              </div>
              <span class="social-name">YouTube</span>
            </div>
            <input type="url" id="social-yt"
              value="<?= htmlspecialchars($d['social']['youtube'] ?? '') ?>"
              placeholder="https://youtube.com/@dematiq">
          </div>

        </div>
      </div>
    </div>

    <!-- ── Save bar ───────────────────────────── -->
    <div class="ct-save-bar">
      <a href="../../pages/corporativo/Contacto.html" target="_blank" class="ct-btn-view">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
        Ver página
      </a>
      <button class="ct-btn-save" onclick="saveContacto()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17,21 17,13 7,13 7,21"/><polyline points="7,3 7,8 15,8"/></svg>
        Guardar cambios
      </button>
    </div>

  </div>
</div>

<?php $profileApiPath = '../api/profile.php'; $fotoPrefix = '../'; require __DIR__ . '/../includes/profile-modal.php'; ?>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="../assets/js/auth.js?v=2"></script>
<script>
const CSRF_TOKEN = '<?= $csrfToken ?>';
const _D = <?= json_encode($d, JSON_UNESCAPED_UNICODE) ?>;

AdminSidebar.init('contacto', '../', '../../');

/* ── User menu ─────────────────────────────────── */
const userMenuBtn = document.getElementById('userMenuBtn');
userMenuBtn.addEventListener('click', function(e) {
  e.stopPropagation();
  const open = this.classList.toggle('open');
  this.setAttribute('aria-expanded', open);
});
userMenuBtn.addEventListener('keydown', function(e) {
  if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); this.click(); }
  if (e.key === 'Escape') this.classList.remove('open');
});
document.addEventListener('click', () => userMenuBtn.classList.remove('open'));

/* ══ WHATSAPP ════════════════════════════════════ */
function fmtPhone(code, local) {
  const d = local.replace(/\D/g,'');
  if (code === '52' && d.length === 10)
    return `+52 (${d.slice(0,3)}) ${d.slice(3,6)}-${d.slice(6)}`;
  if (code === '1' && d.length === 10)
    return `+1 (${d.slice(0,3)}) ${d.slice(3,6)}-${d.slice(6)}`;
  return `+${code} ${local.trim()}`;
}
function buildLink(code, local) {
  const d = local.replace(/\D/g,'');
  const c = (code !== '52' && code !== '1' && d.startsWith('0')) ? d.slice(1) : d;
  return code + c;
}
function updateWA() {
  const code = document.getElementById('wa-country').value;
  const loc  = document.getElementById('wa-local').value.trim();
  if (!loc) {
    document.getElementById('wa-display-val').textContent = '—';
    document.getElementById('wa-link-val').textContent    = '—';
    return;
  }
  document.getElementById('wa-display-val').textContent = fmtPhone(code, loc);
  document.getElementById('wa-link-val').textContent    = buildLink(code, loc);
}
function initWA() {
  const raw = _D.whatsappNum || '';
  if (!raw) return;
  const codes = ['52','1','34','55','54','57','56','51','49','33','44','39','81','86','82'];
  let matched = '', local = raw;
  for (const c of codes) {
    if (raw.startsWith(c)) { matched = c; local = raw.slice(c.length); break; }
  }
  if (matched) document.getElementById('wa-country').value = matched;
  document.getElementById('wa-local').value = local;
  updateWA();
}

/* ══ SCHEDULE ════════════════════════════════════ */
const DAYS = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];
let slots = [];

function renderSlots() {
  const c = document.getElementById('sched-slots');
  c.innerHTML = '';
  slots.forEach((slot, i) => {
    const wrap = document.createElement('div');
    wrap.className = 'sched-slot';

    const top = document.createElement('div');
    top.className = 'sched-slot-top';

    const lbl = document.createElement('span');
    lbl.className = 'sched-slot-label';
    lbl.textContent = slots.length > 1 ? `Turno ${i+1}` : 'Días';
    top.appendChild(lbl);

    const days = document.createElement('div');
    days.className = 'sched-days';
    DAYS.forEach((d, j) => {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'sched-day' + (slot.days.includes(j) ? ' on' : '');
      btn.textContent = d;
      btn.addEventListener('click', () => {
        const pos = slots[i].days.indexOf(j);
        if (pos >= 0) slots[i].days.splice(pos, 1);
        else { slots[i].days.push(j); slots[i].days.sort((a,b)=>a-b); }
        renderSlots();
      });
      days.appendChild(btn);
    });
    top.appendChild(days);

    if (slots.length > 1) {
      const rm = document.createElement('button');
      rm.className = 'sched-rm'; rm.type = 'button';
      rm.title = 'Eliminar turno';
      rm.innerHTML = `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>`;
      rm.addEventListener('click', () => { slots.splice(i,1); renderSlots(); });
      top.appendChild(rm);
    }

    wrap.appendChild(top);

    const timeRow = document.createElement('div');
    timeRow.className = 'sched-time-row';

    const lblA = document.createElement('span'); lblA.className='sched-time-label'; lblA.textContent='Abre:';
    const inA  = document.createElement('input'); inA.type='time'; inA.className='sched-time-inp'; inA.value=slot.from;
    inA.addEventListener('change', () => { slots[i].from = inA.value; updateSchedPreview(); });

    const sep  = document.createElement('span'); sep.className='sched-time-sep'; sep.textContent='—';

    const lblC = document.createElement('span'); lblC.className='sched-time-label'; lblC.textContent='Cierra:';
    const inC  = document.createElement('input'); inC.type='time'; inC.className='sched-time-inp'; inC.value=slot.to;
    inC.addEventListener('change', () => { slots[i].to = inC.value; updateSchedPreview(); });

    [lblA, inA, sep, lblC, inC].forEach(el => timeRow.appendChild(el));
    wrap.appendChild(timeRow);
    c.appendChild(wrap);
  });
  updateSchedPreview();
}

function addSlot() {
  slots.push({ days:[1,2,3,4,5], from:'08:00', to:'18:00' });
  renderSlots();
}

function fmtDays(days) {
  if (!days.length) return '';
  let consec = true;
  for (let i=1;i<days.length;i++) if (days[i]!==days[i-1]+1){consec=false;break;}
  if (consec && days.length > 2) return `${DAYS[days[0]]} – ${DAYS[days[days.length-1]]}`;
  if (days.length === 1) return DAYS[days[0]];
  return days.map(d=>DAYS[d]).join(', ');
}
function fmtT(t) {
  if (!t) return '';
  const [h,m] = t.split(':');
  return m==='00' ? `${parseInt(h)}:00` : `${parseInt(h)}:${m}`;
}
function updateSchedPreview() {
  const parts = slots
    .filter(s => s.days.length && s.from && s.to)
    .map(s => `${fmtDays(s.days)} · ${fmtT(s.from)} – ${fmtT(s.to)}`);
  document.getElementById('sched-preview-val').textContent = parts.join(' | ') || '—';
}
function getHorario() {
  return slots
    .filter(s=>s.days.length&&s.from&&s.to)
    .map(s=>`${fmtDays(s.days)} · ${fmtT(s.from)} – ${fmtT(s.to)}`)
    .join(' | ');
}

function parseHorario(str) {
  if (!str) return null;
  const results = [];
  for (const part of str.split(' | ')) {
    const di = part.indexOf(' · ');
    if (di < 0) continue;
    const dayPart = part.slice(0, di).trim();
    const timePart = part.slice(di+3).trim();
    const tm = timePart.match(/^(\d+:\d+)\s*–\s*(\d+:\d+)$/);
    if (!tm) continue;
    const pad = t => { const[h,m]=t.split(':'); return `${h.padStart(2,'0')}:${m.padStart(2,'0')}`; };
    let dayIdxs = [];
    if (dayPart.includes('–')) {
      const [f,t2] = dayPart.split('–').map(s=>s.trim());
      const fi=DAYS.indexOf(f), ti=DAYS.indexOf(t2);
      if (fi>=0&&ti>=0) for(let d=fi;d<=ti;d++) dayIdxs.push(d);
    } else {
      dayPart.split(',').forEach(d => { const i=DAYS.indexOf(d.trim()); if(i>=0) dayIdxs.push(i); });
    }
    if (dayIdxs.length) results.push({ days:dayIdxs, from:pad(tm[1]), to:pad(tm[2]) });
  }
  return results.length ? results : null;
}

function initSchedule() {
  const p = parseHorario(_D.horario || '');
  slots = p || [{ days:[1,2,3,4,5], from:'08:00', to:'18:00' }];
  renderSlots();
}

/* ══ MAP ═════════════════════════════════════════ */
let map, marker;
const DEF_LAT = 20.621222, DEF_LNG = -100.470500;

function initMap() {
  const raw = (_D.mapCoords||'').trim();
  let lat = DEF_LAT, lng = DEF_LNG;
  if (raw) {
    const p = raw.split(',');
    if (p.length===2) { lat=parseFloat(p[0])||DEF_LAT; lng=parseFloat(p[1])||DEF_LNG; }
  }
  map = L.map('leaflet-map').setView([lat,lng], raw?16:13);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{
    attribution:'© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>', maxZoom:19
  }).addTo(map);
  if (raw) {
    placeMarker(lat, lng);
    document.getElementById('map-coords').value = `${lat},${lng}`;
    /* On load, dirección already comes from DB — don't overwrite */
  }
  map.on('click', e => { placeMarker(e.latlng.lat, e.latlng.lng); setCoords(e.latlng.lat, e.latlng.lng); });
}

function placeMarker(lat,lng) {
  if (marker) marker.setLatLng([lat,lng]);
  else {
    marker = L.marker([lat,lng],{draggable:true}).addTo(map);
    marker.on('dragend', e => { const p=e.target.getLatLng(); setCoords(p.lat,p.lng); });
  }
}
function setCoords(lat, lng) {
  document.getElementById('map-coords').value = `${lat.toFixed(6)},${lng.toFixed(6)}`;
  reverseGeocode(lat, lng);
}

async function reverseGeocode(lat, lng) {
  try {
    const res  = await fetch(
      `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&accept-language=es`,
      { headers: { 'Accept-Language': 'es' } }
    );
    const json = await res.json();
    if (!json || !json.display_name) return;

    /* Build a cleaner address from the structured parts */
    const a = json.address || {};
    const parts = [
      a.road || a.pedestrian || a.footway || '',
      a.house_number ? `#${a.house_number}` : '',
      a.suburb || a.neighbourhood || a.quarter || '',
      a.city || a.town || a.village || a.municipality || '',
      a.state || '',
      a.country || ''
    ].filter(Boolean);
    const addr = parts.length >= 3 ? parts.join(', ') : json.display_name;

    const el = document.getElementById('direccion');
    el.value = addr;
    document.getElementById('dir-auto-badge').style.display = 'inline-block';
  } catch { /* silent — coords still saved */ }
}

async function geocode(q, fillAddress = false) {
  try {
    const res  = await fetch(
      `https://nominatim.openstreetmap.org/search?format=json&limit=1&q=${encodeURIComponent(q)}`,
      { headers: { 'Accept-Language': 'es' } }
    );
    const json = await res.json();
    if (!json.length) { showToast('No se encontró la dirección','error'); return; }
    const la = parseFloat(json[0].lat), lo = parseFloat(json[0].lon);
    map.setView([la, lo], 17);
    placeMarker(la, lo);
    document.getElementById('map-coords').value = `${la.toFixed(6)},${lo.toFixed(6)}`;
    if (fillAddress) reverseGeocode(la, lo);
    showToast('Ubicación encontrada');
  } catch { showToast('Error al buscar','error'); }
}

function geocodeSearch() {
  const q = document.getElementById('map-search-input').value.trim();
  if (q) geocode(q, true);
}

function getMyLocation() {
  if (!navigator.geolocation) { showToast('Tu navegador no soporta geolocalización','error'); return; }
  const btn = document.getElementById('btn-my-loc');
  btn.disabled = true;
  btn.style.opacity = '.6';
  navigator.geolocation.getCurrentPosition(
    pos => {
      const la = pos.coords.latitude, lo = pos.coords.longitude;
      map.setView([la, lo], 17);
      placeMarker(la, lo);
      setCoords(la, lo);
      showToast('Ubicación obtenida');
      btn.disabled = false; btn.style.opacity = '1';
    },
    err => {
      const msgs = { 1:'Permiso denegado', 2:'Posición no disponible', 3:'Tiempo agotado' };
      showToast(msgs[err.code] || 'Error de geolocalización','error');
      btn.disabled = false; btn.style.opacity = '1';
    },
    { enableHighAccuracy: true, timeout: 10000 }
  );
}

/* ══ SAVE ════════════════════════════════════════ */
async function saveContacto() {
  const code = document.getElementById('wa-country').value;
  const loc  = document.getElementById('wa-local').value.trim();
  try {
    const res = await CM.set('contacto', {
      email:       document.getElementById('email').value.trim(),
      whatsapp:    loc ? fmtPhone(code,loc) : '',
      whatsappNum: loc ? buildLink(code,loc) : '',
      horario:     getHorario(),
      direccion:   document.getElementById('direccion').value.trim(),
      mapCoords:   document.getElementById('map-coords').value.trim(),
      social: {
        facebook:  document.getElementById('social-fb').value.trim(),
        instagram: document.getElementById('social-ig').value.trim(),
        linkedin:  document.getElementById('social-li').value.trim(),
        youtube:   document.getElementById('social-yt').value.trim()
      }
    });
    if (res && res.ok) showToast('Cambios guardados correctamente');
    else showToast(res?.error||'Error al guardar','error');
  } catch { showToast('Error de conexión','error'); }
}

/* ── Init ────────────────────────────────────── */
initWA();
initSchedule();
document.addEventListener('DOMContentLoaded', initMap);
</script>

</body>
</html>
