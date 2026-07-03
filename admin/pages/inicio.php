<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/conexion.php';
require_once __DIR__ . '/../../includes/contenido.php';
$user      = Auth::require('/pages/corporativo/login.php');
$content   = Contenido::getAll();
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
  <title>Inicio | DEMATIQ Admin</title>
  <link rel="stylesheet" href="../assets/css/admin.css?v=7">
  <link rel="icon" type="image/svg+xml" href="../../assets/images/logos/favicon-d.svg">
  <style>

    /* ══ BANNER ═══════════════════════════════════════════ */
    .inicio-banner {
      background: linear-gradient(135deg, #071740 0%, #0d2155 45%, #1a4a9e 100%);
      border-radius: 20px;
      padding: 30px 32px;
      margin-bottom: 16px;
      position: relative;
      overflow: hidden;
    }
    .banner-mesh {
      position:absolute;inset:0;pointer-events:none;overflow:hidden;
      background-image:radial-gradient(rgba(255,255,255,.04) 1px,transparent 1px);
      background-size:22px 22px;
    }
    .inicio-banner::before {
      content: '';
      position: absolute;
      width: 480px; height: 480px; border-radius: 50%;
      background: radial-gradient(circle, rgba(46,107,207,.22) 0%, transparent 65%);
      top: -200px; right: -40px;
      pointer-events: none;
    }
    .inicio-banner::after {
      content: '';
      position: absolute;
      width: 220px; height: 220px; border-radius: 50%;
      background: radial-gradient(circle, rgba(255,255,255,.04) 0%, transparent 70%);
      bottom: -90px; left: 38%;
      pointer-events: none;
    }
    .banner-inner { position: relative; z-index: 1; }
    .banner-chip {
      display: inline-flex; align-items: center; gap: 7px;
      background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.15);
      color: rgba(255,255,255,.75);
      font-size: .62rem; font-weight: 700; letter-spacing: 1.8px; text-transform: uppercase;
      padding: 5px 12px; border-radius: 20px; margin-bottom: 14px;
    }
    .banner-chip-dot {
      width: 6px; height: 6px; border-radius: 50%; background: #4ade80;
      animation: pulse-dot 2.2s ease-in-out infinite;
    }
    @keyframes pulse-dot {
      0%, 100% { opacity: 1; box-shadow: 0 0 0 0 rgba(74,222,128,.4); }
      50%       { opacity: .7; box-shadow: 0 0 0 5px rgba(74,222,128,0); }
    }
    .banner-title { font-size: 1.65rem; font-weight: 800; color: #fff; letter-spacing: -.025em; line-height: 1.1; margin-bottom: 6px; }
    .banner-desc  { font-size: .82rem; color: rgba(255,255,255,.5); line-height: 1.65; max-width: 440px; margin-bottom: 22px; }

    /* bsc = banner section card */
    .banner-section-cards { display: flex; gap: 12px; flex-wrap: wrap; }
    .bsc {
      display: flex; align-items: center; gap: 10px;
      background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.12);
      padding: 10px 16px; border-radius: 14px; flex: 1; min-width: 130px;
      transition: background .2s;
    }
    .bsc:hover { background: rgba(255,255,255,.13); }
    .bsc-icon { width: 34px; height: 34px; border-radius: 10px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; }
    .bsc-icon svg { width: 16px; height: 16px; color: #fff; }
    .bsci-blue   { background: linear-gradient(135deg, #1a4a9e, #4f6fc2); }
    .bsci-teal   { background: linear-gradient(135deg, #0e7490, #06b6d4); }
    .bsci-violet { background: linear-gradient(135deg, #6d28d9, #8b5cf6); }
    .bsc-label { font-size: .6rem; color: rgba(255,255,255,.4); text-transform: uppercase; letter-spacing: .8px; margin-bottom: 2px; }
    .bsc-val   { font-size: .82rem; font-weight: 700; color: #fff; max-width: 140px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .bsc-val.dim { color: rgba(255,255,255,.35); font-style: italic; font-size: .75rem; }

    @media (max-width: 600px) { .inicio-banner { padding: 22px 18px; } .banner-section-cards { flex-direction: column; } }

    /* ══ SECTION CARDS ═════════════════════════════════════ */
    .section-card {
      background: #fff;
      border: 1.5px solid var(--border);
      border-radius: 20px;
      overflow: hidden;
      margin-bottom: 14px;
      transition: box-shadow .2s, border-color .2s;
    }
    .section-card:focus-within {
      border-color: var(--accent-lt);
      box-shadow: 0 0 0 3px rgba(46,107,207,.07), 0 4px 24px rgba(0,0,0,.07);
    }
    .sc-head {
      display: flex; align-items: center; gap: 16px;
      padding: 18px 24px;
      border-bottom: 1px solid var(--border);
      background: linear-gradient(to right, #f8faff, #fff);
    }
    .sc-icon { width: 44px; height: 44px; border-radius: 12px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 14px rgba(0,0,0,.18); }
    .sc-icon svg { width: 20px; height: 20px; color: #fff; }
    .si-blue   { background: linear-gradient(135deg, #1a4a9e, #4f6fc2); }
    .si-teal   { background: linear-gradient(135deg, #0e7490, #06b6d4); }
    .si-violet { background: linear-gradient(135deg, #6d28d9, #8b5cf6); }
    .sc-head-text { flex: 1; min-width: 0; }
    .sc-head-text h3 { font-size: .95rem; font-weight: 700; color: var(--text); }
    .sc-head-text p  { font-size: .75rem; color: var(--text-lt); margin-top: 2px; }
    .sc-body { padding: 24px; }

    /* badge split */
    .badge-split { display: grid; grid-template-columns: 1fr 1fr; gap: 28px; align-items: start; }
    @media (max-width: 720px) { .badge-split { grid-template-columns: 1fr; } }

    /* live preview badge box */
    .badge-lp {
      background: linear-gradient(160deg, #f0f4ff, #f7f9ff);
      border: 1.5px solid #d0daf5; border-radius: 14px; padding: 18px 20px;
      position: sticky; top: 90px;
    }
    .badge-lp-title {
      font-size: .62rem; font-weight: 700; color: #1a4a9e;
      text-transform: uppercase; letter-spacing: 1px;
      display: flex; align-items: center; gap: 5px; margin-bottom: 14px;
    }
    .badge-lp-title svg { width: 11px; height: 11px; }
    .badge-lp-frame {
      background: linear-gradient(160deg, #071740, #0d2155);
      border-radius: 12px; padding: 22px 18px;
      display: flex; flex-direction: column; align-items: center;
      gap: 6px; text-align: center;
    }
    .badge-lp-text {
      font-size: .6rem; font-weight: 800; letter-spacing: 2px;
      color: rgba(255,255,255,.6); text-transform: uppercase; transition: .2s;
    }
    .badge-lp-logo {
      height: 16px; object-fit: contain;
      filter: brightness(0) invert(1); opacity: .85;
    }
    .badge-lp-btns { display: flex; gap: 5px; margin-top: 2px; }
    .badge-lp-btn {
      font-size: .5rem; font-weight: 700; padding: 3px 9px; border-radius: 4px; color: #fff;
      background: linear-gradient(135deg, #1a4a9e, #2e6bcf);
    }
    .badge-lp-btn.ghost { background: rgba(255,255,255,.15); border: 1px solid rgba(255,255,255,.3); }

    /* poster layout */
    .poster-layout {
      display: grid; grid-template-columns: 220px 1fr; gap: 22px; align-items: start;
    }
    .poster-frame {
      width: 100%; aspect-ratio: 16/9;
      border-radius: 12px; overflow: hidden;
      background: #0d2155;
      border: 2px solid var(--border);
      position: relative;
      cursor: pointer;
      transition: border-color .2s, box-shadow .2s, transform .2s;
    }
    .poster-frame:hover {
      border-color: var(--accent-lt);
      box-shadow: 0 6px 20px rgba(46,107,207,.2);
      transform: translateY(-2px);
    }
    .poster-frame img {
      position: absolute; inset: 0;
      width: 100%; height: 100%; object-fit: cover; display: block;
    }
    .poster-no-img {
      position: absolute; inset: 0;
      display: flex; flex-direction: column; align-items: center; justify-content: center;
      gap: 6px; color: rgba(255,255,255,.25); font-size: .65rem;
    }
    .poster-no-img svg { opacity: .2; }
    .poster-hover-hint {
      position: absolute; inset: 0;
      background: rgba(13,33,85,.55);
      display: flex; align-items: center; justify-content: center;
      opacity: 0; transition: opacity .18s;
      color: #fff; font-size: .72rem; font-weight: 700; gap: 6px;
      backdrop-filter: blur(2px);
    }
    .poster-hover-hint svg { width: 16px; height: 16px; }
    .poster-frame:hover .poster-hover-hint { opacity: 1; }

    .poster-controls { display: flex; flex-direction: column; gap: 10px; }
    .upload-zone {
      display: flex; align-items: center; justify-content: center;
      flex-direction: column; gap: 8px;
      padding: 16px; border-radius: 12px;
      border: 2px dashed #c7d5f0;
      background: #f5f8ff;
      cursor: pointer; transition: all .18s;
      font-size: .78rem; color: var(--text-lt);
      min-height: 80px;
    }
    .upload-zone:hover, .upload-zone.dragging {
      border-color: var(--accent-lt); background: #eef3ff;
      color: var(--accent);
    }
    .upload-zone svg { width: 22px; height: 22px; opacity: .5; transition: opacity .18s; }
    .upload-zone:hover svg { opacity: .9; }
    .upload-zone strong { font-weight: 700; }
    .upload-zone span { font-size: .7rem; }

    /* ══ VIDEO UPLOAD ═══════════════════════════════════════ */
    .video-layout {
      display: grid; grid-template-columns: 240px 1fr; gap: 22px; align-items: start;
    }
    @media (max-width: 640px) { .video-layout { grid-template-columns: 1fr; } }

    .video-preview-frame {
      width: 100%; aspect-ratio: 16/9;
      border-radius: 12px; overflow: hidden;
      background: #071740; border: 2px solid var(--border);
      position: relative;
      display: flex; align-items: center; justify-content: center;
      transition: border-color .2s;
    }
    .video-preview-frame:has(video[src]:not([src=""])) { border-color: #6d28d9; }
    .video-preview-frame video { width: 100%; height: 100%; object-fit: cover; display: block; }
    .video-no-preview {
      position: absolute; inset: 0;
      display: flex; flex-direction: column; align-items: center; justify-content: center;
      gap: 6px; color: rgba(255,255,255,.2); font-size: .65rem; text-align: center;
    }
    .video-no-preview svg { opacity: .15; }

    /* analysis panel */
    .video-analysis {
      background: #faf8ff; border: 1.5px solid #ddd6fe;
      border-radius: 12px; padding: 14px 16px;
      margin-top: 10px; display: none;
    }
    .video-analysis.visible { display: block; }
    .va-title {
      font-size: .7rem; font-weight: 700; color: #6d28d9;
      text-transform: uppercase; letter-spacing: .6px;
      margin-bottom: 10px; display: flex; align-items: center; gap: 6px;
    }
    .va-title svg { width: 13px; height: 13px; }
    .va-grid {
      display: grid; grid-template-columns: 1fr 1fr; gap: 5px 16px;
      margin-bottom: 10px;
    }
    .va-row { display: flex; flex-direction: column; gap: 1px; }
    .va-lbl { font-size: .62rem; color: var(--text-lt); text-transform: uppercase; letter-spacing: .4px; }
    .va-val { font-size: .78rem; font-weight: 700; color: var(--text); font-family: monospace; }

    .va-checks { display: flex; flex-direction: column; gap: 4px; }
    .vcheck {
      display: flex; align-items: center; gap: 7px;
      font-size: .73rem; padding: 5px 9px; border-radius: 7px;
    }
    .vcheck.ok   { background: #f0fdf4; color: #15803d; }
    .vcheck.warn { background: #fffbeb; color: #92400e; }
    .vcheck.bad  { background: #fff1f2; color: #9f1239; }
    .vcheck.info { background: #f0f4ff; color: #3730a3; }
    .vcheck svg  { width: 13px; height: 13px; flex-shrink: 0; }

    /* progress bar */
    .video-progress-wrap {
      display: none; border-radius: 9px; overflow: hidden;
      background: #e8eef8; height: 32px; position: relative;
    }
    .video-progress-wrap.visible { display: block; }
    .video-progress-bar {
      height: 100%;
      background: linear-gradient(90deg, #6d28d9, #8b5cf6);
      border-radius: 9px; transition: width .25s ease; width: 0%;
    }
    .video-progress-label {
      position: absolute; inset: 0;
      display: flex; align-items: center; justify-content: center;
      font-size: .72rem; font-weight: 700; color: var(--text);
      white-space: nowrap; pointer-events: none;
    }
    .video-progress-wrap.done .video-progress-bar { background: linear-gradient(90deg, #059669, #10b981); }
    .video-progress-wrap.done .video-progress-label { color: #065f46; }

    /* status pill */
    .video-status-pill {
      display: inline-flex; align-items: center; gap: 5px;
      padding: 5px 11px; border-radius: 20px;
      font-size: .7rem; font-weight: 700; white-space: nowrap;
      transition: all .2s; margin-left: auto;
    }
    .video-status-pill.active { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
    .video-status-pill.empty  { background: #fef9c3; color: #a16207; border: 1px solid #fde68a; }
    .video-status-dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; }

    .video-manual-note {
      font-size: .72rem; color: var(--text-lt); line-height: 1.55;
    }
    .video-manual-note code {
      background: #eef2ff; color: var(--accent);
      padding: 1px 5px; border-radius: 3px; font-size: .7rem;
    }

    /* ══ FIELDS ════════════════════════════════════════════ */
    .field { margin-bottom: 18px; }
    .field:last-child { margin-bottom: 0; }
    .field-top { display: flex; align-items: baseline; justify-content: space-between; margin-bottom: 7px; }
    .field-top label { font-size: .71rem; font-weight: 700; color: var(--text-lt); text-transform: uppercase; letter-spacing: .5px; }
    .field-cnt { font-size: .65rem; color: #b0b8cc; font-variant-numeric: tabular-nums; }
    .field-hint { font-size: .65rem; color: #aab; font-style: italic; margin-top: 4px; }
    input.fi, textarea.fi {
      width: 100%; box-sizing: border-box; padding: 10px 13px;
      border: 1.5px solid var(--border); border-radius: 10px;
      font-size: .87rem; font-family: inherit; color: var(--text);
      background: #fafcff; outline: none;
      transition: border-color .15s, box-shadow .15s, background .15s;
    }
    input.fi:focus, textarea.fi:focus {
      border-color: var(--accent-lt); background: #fff;
      box-shadow: 0 0 0 3px rgba(46,107,207,.1);
    }

    /* ══ SAVE BAR ══════════════════════════════════════════ */
    .save-bar-sticky {
      position: sticky; bottom: 0;
      background: rgba(240,244,255,.92);
      backdrop-filter: blur(10px);
      border-top: 1px solid rgba(221,229,245,.8);
      padding: 14px var(--content-pad);
      display: flex; gap: 10px; justify-content: flex-end; flex-wrap: wrap;
      z-index: 50;
      margin: 0 calc(-1 * var(--content-pad)) 0;
    }

    /* ══ UNSAVED NOTICE ════════════════════════════════════ */
    .unsaved-notice {
      display:flex;align-items:center;gap:12px;
      background:linear-gradient(90deg,#fffbeb,#fef3c7);
      border:1.5px solid #fcd34d;border-radius:14px;
      padding:11px 16px;margin-bottom:16px;
      animation:slideInN .35s ease;
    }
    .unsaved-notice.hidden{display:none;}
    @keyframes slideInN{from{opacity:0;transform:translateY(-10px);}to{opacity:1;transform:translateY(0);}}
    .un-icon{width:36px;height:36px;border-radius:10px;background:#fbbf24;flex-shrink:0;display:flex;align-items:center;justify-content:center;}
    .un-icon svg{width:17px;height:17px;color:#fff;}
    .un-text{flex:1;}
    .un-text strong{display:block;font-size:.82rem;font-weight:700;color:#92400e;}
    .un-text span  {font-size:.72rem;color:#b45309;}
    .un-save{
      display:inline-flex;align-items:center;gap:6px;
      background:#f59e0b;color:#fff;border:none;cursor:pointer;
      font-size:.76rem;font-weight:700;padding:7px 14px;border-radius:9px;
      transition:background .15s,transform .1s;white-space:nowrap;
    }
    .un-save:hover{background:#d97706;transform:translateY(-1px);}
    .un-save svg{width:14px;height:14px;}

    /* ══ BLUR-SAVE PROMPT ═══════════════════════════════════ */
    .blur-prompt{
      position:fixed;bottom:80px;right:24px;z-index:500;
      background:#fff;border-radius:18px;
      box-shadow:0 12px 40px rgba(0,0,0,.18),0 0 0 1px rgba(0,0,0,.06);
      padding:18px 20px;width:260px;
      transform:translateY(16px) scale(.97);opacity:0;
      pointer-events:none;
      transition:all .25s cubic-bezier(.34,1.56,.64,1);
    }
    .blur-prompt.show{transform:translateY(0) scale(1);opacity:1;pointer-events:auto;}
    .bp-head{display:flex;align-items:center;gap:10px;margin-bottom:12px;}
    .bp-head-icon{width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,#f59e0b,#fbbf24);flex-shrink:0;display:flex;align-items:center;justify-content:center;}
    .bp-head-icon svg{width:16px;height:16px;color:#fff;}
    .bp-head-text strong{display:block;font-size:.82rem;font-weight:700;color:var(--text);}
    .bp-head-text span  {font-size:.7rem;color:var(--text-lt);}
    .bp-actions{display:flex;gap:8px;}
    .bp-yes{flex:1;background:linear-gradient(135deg,#1a4a9e,#2e6bcf);color:#fff;border:none;cursor:pointer;font-size:.78rem;font-weight:700;padding:8px 0;border-radius:9px;transition:opacity .15s;}
    .bp-yes:hover{opacity:.88;}
    .bp-no{flex:1;background:#f1f4fa;color:var(--text-lt);border:none;cursor:pointer;font-size:.78rem;font-weight:600;padding:8px 0;border-radius:9px;transition:background .15s;}
    .bp-no:hover{background:#e5eaf5;}
    .bp-bar{height:3px;border-radius:2px;background:#f59e0b;margin-top:12px;transform-origin:left;animation:none;}
    .bp-bar.ticking{animation:bpTick 6s linear forwards;}
    @keyframes bpTick{from{transform:scaleX(1);}to{transform:scaleX(0);}}

    /* ══ NAV-AWAY MODAL ═════════════════════════════════════ */
    .nav-modal-backdrop{
      position:fixed;inset:0;background:rgba(7,23,64,.55);backdrop-filter:blur(4px);
      z-index:900;display:flex;align-items:center;justify-content:center;
      opacity:0;pointer-events:none;transition:opacity .25s;
    }
    .nav-modal-backdrop.show{opacity:1;pointer-events:auto;}
    .nav-modal{
      background:#fff;border-radius:22px;padding:32px;width:360px;max-width:90vw;
      box-shadow:0 24px 80px rgba(0,0,0,.3);
      transform:scale(.94);transition:transform .25s cubic-bezier(.34,1.56,.64,1);
    }
    .nav-modal-backdrop.show .nav-modal{transform:scale(1);}
    .nm-icon{width:52px;height:52px;border-radius:16px;background:linear-gradient(135deg,#f59e0b,#fbbf24);display:flex;align-items:center;justify-content:center;margin-bottom:16px;}
    .nm-icon svg{width:24px;height:24px;color:#fff;}
    .nm-title{font-size:1.05rem;font-weight:800;color:var(--text);margin-bottom:6px;}
    .nm-desc{font-size:.82rem;color:var(--text-lt);line-height:1.6;margin-bottom:22px;}
    .nm-actions{display:flex;flex-direction:column;gap:8px;}
    .nm-btn{display:flex;align-items:center;justify-content:center;gap:8px;padding:11px 16px;border-radius:12px;font-size:.85rem;font-weight:700;cursor:pointer;border:none;transition:all .15s;}
    .nm-btn.primary{background:linear-gradient(135deg,#1a4a9e,#2e6bcf);color:#fff;}
    .nm-btn.primary:hover{opacity:.9;}
    .nm-btn.danger{background:#fff1f0;color:#dc2626;border:1.5px solid #fecaca;}
    .nm-btn.danger:hover{background:#fee2e2;}
    .nm-btn.cancel{background:#f1f4fa;color:var(--text-lt);}
    .nm-btn.cancel:hover{background:#e5eaf5;}

    /* ══ RESPONSIVE ════════════════════════════════════════ */
    @media (max-width: 600px) {
      .poster-layout { grid-template-columns: 1fr; }
      .sc-body { padding: 16px 18px; }
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
      <button id="sidebar-toggle" class="mobile-menu-toggle" aria-label="Abrir menú" aria-expanded="false">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
        </svg>
      </button>
      <span class="admin-topbar-title">Inicio</span>
    </div>

    <div class="user-menu" id="userMenuBtn" role="button" tabindex="0" aria-haspopup="true" aria-expanded="false">
      <div class="admin-avatar" style="overflow:hidden">
        <?php if ($fotoPath): ?>
          <img src="<?= $fotoPath ?>" alt="Avatar" style="width:100%;height:100%;object-fit:cover;border-radius:50%">
        <?php else: ?>
          <?= $initials ?>
        <?php endif; ?>
      </div>
      <div class="user-menu-info">
        <span class="user-menu-name"><?= htmlspecialchars($user['nombre']) ?></span>
        <span class="user-menu-role">Administrador</span>
      </div>
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="user-menu-chevron">
        <polyline points="6 9 12 15 18 9"/>
      </svg>
      <div class="user-dropdown" id="userDropdown" role="menu">
        <button class="user-dropdown-item" onclick="openProfileModal()">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          Mi Perfil
        </button>
        <div class="user-dropdown-sep"></div>
        <a class="user-dropdown-item danger" id="logoutLink" href="../logout.php">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16,17 21,12 16,7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
          Cerrar sesión
        </a>
      </div>
    </div>
  </header>

  <div class="admin-content">

    <!-- ── BANNER ─────────────────────────────────────────── -->
    <div class="inicio-banner">
      <div class="banner-mesh"></div>
      <div class="banner-inner">
        <div class="banner-chip">
          <span class="banner-chip-dot"></span>
          Portada activa
        </div>
        <h1 class="banner-title">Hero Principal</h1>
        <p class="banner-desc">Controla el video de fondo, la imagen de respaldo y el texto de bienvenida que ven los visitantes al entrar al sitio.</p>
        <div class="banner-section-cards">
          <div class="bsc">
            <div class="bsc-icon bsci-blue">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
            </div>
            <div>
              <div class="bsc-label">Bienvenida</div>
              <div class="bsc-val" id="statBadge">—</div>
            </div>
          </div>
          <div class="bsc">
            <div class="bsc-icon bsci-teal">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21,15 16,10 5,21"/></svg>
            </div>
            <div>
              <div class="bsc-label">Poster</div>
              <div class="bsc-val" id="statPoster">—</div>
            </div>
          </div>
          <div class="bsc">
            <div class="bsc-icon bsci-violet">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="23,7 16,12 23,17 23,7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
            </div>
            <div>
              <div class="bsc-label">Video</div>
              <div class="bsc-val" id="statVideo">—</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ══ UNSAVED NOTICE ═══════════════════════════════════ -->
    <div class="unsaved-notice hidden" id="unsavedNotice">
      <div class="un-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
      </div>
      <div class="un-text">
        <strong>Tienes cambios sin guardar</strong>
        <span>Guarda para que se reflejen en la página pública</span>
      </div>
      <button class="un-save" onclick="saveInicio()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17,21 17,13 7,13 7,21"/><polyline points="7,3 7,8 15,8"/></svg>
        Guardar ahora
      </button>
    </div>

    <!-- ── 1. TEXTO DE BIENVENIDA ─────────────────────────── -->
    <div class="section-card">
      <div class="sc-head">
        <div class="sc-icon si-blue">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
        </div>
        <div class="sc-head-text">
          <h3>Texto de bienvenida</h3>
          <p>Aparece sobre el logo en el hero de la portada</p>
        </div>
      </div>
      <div class="sc-body">
        <div class="badge-split">
          <div>
            <div class="field-top">
              <label>Texto de bienvenida</label>
              <span class="field-cnt" id="badgeCount">0/60</span>
            </div>
            <div class="badge-input-wrap">
              <input type="text" id="heroBadge" class="fi" placeholder="Bienvenido" maxlength="60"
                oninput="onBadgeChange(this.value)" onblur="onFieldBlur()">
            </div>
            <p class="field-hint">Aparece encima del logo en el hero · mayúsculas recomendadas</p>
          </div>
          <div>
            <div class="badge-lp">
              <div class="badge-lp-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                Preview en vivo
              </div>
              <div class="badge-lp-frame">
                <div class="badge-lp-text" id="badgePreviewText">Bienvenido</div>
                <img src="../../assets/images/logos/logo1.webp" class="badge-lp-logo" alt="DEMATIQ" onerror="this.style.display='none'">
                <div class="badge-lp-btns">
                  <span class="badge-lp-btn">Cotiza</span>
                  <span class="badge-lp-btn ghost">Servicios</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ── 2. IMAGEN DE RESPALDO ──────────────────────────── -->
    <div class="section-card">
      <div class="sc-head">
        <div class="sc-icon si-teal">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21,15 16,10 5,21"/></svg>
        </div>
        <div class="sc-head-text">
          <h3>Imagen de respaldo <span style="font-size:.72rem;font-weight:400;color:var(--text-lt)">(poster)</span></h3>
          <p>Se muestra mientras carga el video o si el video no está disponible</p>
        </div>
      </div>
      <div class="sc-body">
        <div class="poster-layout">
          <!-- preview -->
          <div class="poster-frame" id="posterFrame" onclick="document.getElementById('posterFile').click()" title="Clic para seleccionar imagen">
            <div class="poster-no-img" id="posterNoImg">
              <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21,15 16,10 5,21"/></svg>
              <span>Sin imagen</span>
            </div>
            <img id="posterImg" src="" alt="Poster" style="display:none">
            <div class="poster-hover-hint">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17,8 12,3 7,8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
              Cambiar imagen
            </div>
          </div>

          <!-- controls -->
          <div class="poster-controls">
            <div class="upload-zone" id="uploadZone"
              onclick="document.getElementById('posterFile').click()"
              ondragover="onDragOver(event)" ondragleave="onDragLeave(event)" ondrop="onDrop(event)">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17,8 12,3 7,8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
              <div><strong id="uploadZoneText">Clic o arrastra una imagen</strong></div>
              <span>JPG, PNG, WebP · máx 5 MB</span>
            </div>
            <input type="file" id="posterFile" accept="image/jpeg,image/png,image/webp,image/gif" style="display:none" onchange="uploadPoster(this)">
            <div class="field" style="margin-bottom:0">
              <div class="field-top">
                <label>Ruta manual</label>
              </div>
              <input type="text" class="fi" id="posterPath" style="font-family:monospace;font-size:.78rem"
                oninput="setPosterPreview(this.value)" onblur="onFieldBlur()"
                placeholder="assets/images/general/index.webp">
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ── 3. VIDEO ───────────────────────────────────────── -->
    <div class="section-card">
      <div class="sc-head">
        <div class="sc-icon si-violet">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="23,7 16,12 23,17 23,7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
        </div>
        <div class="sc-head-text">
          <h3>Video del hero</h3>
          <p>MP4 en bucle — sube directamente o escribe la ruta manualmente</p>
        </div>
        <div class="video-status-pill empty" id="videoPill">
          <span class="video-status-dot"></span>
          Sin video
        </div>
      </div>
      <div class="sc-body">
        <div class="video-layout">

          <!-- left: preview + analysis -->
          <div>
            <div class="video-preview-frame">
              <div class="video-no-preview" id="videoNoPreview">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><polygon points="23,7 16,12 23,17 23,7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
                <span>Sin video</span>
              </div>
              <video id="videoPlayer" muted loop playsinline style="display:none"
                onerror="this.style.display='none';document.getElementById('videoNoPreview').style.display=''"></video>
            </div>

            <div class="video-analysis" id="videoAnalysis">
              <div class="va-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                Análisis del archivo
              </div>
              <div class="va-grid">
                <div class="va-row"><span class="va-lbl">Tamaño</span><span class="va-val" id="vStatSize">—</span></div>
                <div class="va-row"><span class="va-lbl">Resolución</span><span class="va-val" id="vStatRes">—</span></div>
                <div class="va-row"><span class="va-lbl">Duración</span><span class="va-val" id="vStatDur">—</span></div>
                <div class="va-row"><span class="va-lbl">Bitrate est.</span><span class="va-val" id="vStatBitrate">—</span></div>
              </div>
              <div class="va-checks" id="videoChecks"></div>
            </div>
          </div>

          <!-- right: upload + path -->
          <div style="display:flex;flex-direction:column;gap:10px">
            <div class="upload-zone" id="videoUploadZone"
              onclick="document.getElementById('videoFile').click()"
              ondragover="onVideoDragOver(event)" ondragleave="onVideoDragLeave(event)" ondrop="onVideoDrop(event)">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="24" height="24"><polygon points="23,7 16,12 23,17 23,7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
              <div><strong id="videoUploadZoneText">Clic o arrastra un video MP4</strong></div>
              <span>MP4, WebM · máx 200 MB · recomendado 1080p</span>
            </div>
            <input type="file" id="videoFile" accept="video/mp4,video/webm,video/ogg,.mp4,.webm,.ogv"
              style="display:none" onchange="onVideoFileSelect(this)">

            <div class="video-progress-wrap" id="videoProg">
              <div class="video-progress-bar" id="videoProgBar"></div>
              <div class="video-progress-label" id="videoProgLabel">0%</div>
            </div>

            <div class="field" style="margin-bottom:0">
              <div class="field-top">
                <label>Ruta del video</label>
              </div>
              <input type="text" class="fi" id="heroVideo" style="font-family:monospace;font-size:.78rem"
                placeholder="assets/videos/hero.mp4"
                oninput="onVideoChange(this.value)" onblur="onFieldBlur()">
              <p class="field-hint">También puedes subir el archivo directamente a <code style="background:#eef2ff;color:var(--accent);padding:1px 5px;border-radius:3px;font-size:.7rem">assets/videos/</code> en el servidor y escribir la ruta arriba.</p>
            </div>
          </div>

        </div>
      </div>
    </div>

    <!-- save bar sticky -->
    <div class="save-bar-sticky" style="justify-content:flex-end">
      <a href="/index.html" target="_blank" class="btn-admin btn-outline-admin">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
        Ver sitio
      </a>
      <button class="btn-admin btn-outline-admin" onclick="cancelInicio()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        Descartar
      </button>
      <button class="btn-admin btn-primary-admin" onclick="saveInicio()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17,21 17,13 7,13 7,21"/><polyline points="7,3 7,8 15,8"/></svg>
        Guardar cambios
      </button>
    </div>

  </div>
</div>

<!-- ══ BLUR-SAVE PROMPT ═════════════════════════════════ -->
<div class="blur-prompt" id="blurPrompt">
  <div class="bp-head">
    <div class="bp-head-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
    </div>
    <div class="bp-head-text">
      <strong>¿Guardar cambios?</strong>
      <span>Tienes cambios sin guardar</span>
    </div>
  </div>
  <div class="bp-actions">
    <button class="bp-yes" onclick="promptSave()">Sí, guardar</button>
    <button class="bp-no"  onclick="hideBlurPrompt()">Ahora no</button>
  </div>
  <div class="bp-bar" id="bpBar"></div>
</div>

<!-- ══ NAV-AWAY MODAL ═══════════════════════════════════ -->
<div class="nav-modal-backdrop" id="navModal">
  <div class="nav-modal">
    <div class="nm-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
    </div>
    <div class="nm-title">¿Guardar antes de salir?</div>
    <p class="nm-desc">Tienes cambios sin guardar en <strong>Inicio</strong>. Si sales ahora se perderán.</p>
    <div class="nm-actions">
      <button class="nm-btn primary" onclick="modalSaveAndGo()">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17,21 17,13 7,13 7,21"/><polyline points="7,3 7,8 15,8"/></svg>
        Guardar y salir
      </button>
      <button class="nm-btn danger" onclick="modalDiscardAndGo()">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        Salir sin guardar
      </button>
      <button class="nm-btn cancel" onclick="modalCancel()">Seguir editando</button>
    </div>
  </div>
</div>

<?php $profileApiPath = '../api/profile.php'; $fotoPrefix = '../'; require __DIR__ . '/../includes/profile-modal.php'; ?>

<script src="../assets/js/auth.js?v=2"></script>
<script>
  const CSRF_TOKEN = '<?= $csrfToken ?>';

  AdminSidebar.init('inicio', '../', '../../');

  /* user menu */
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

  /* ── Load saved values ───────────────────────── */
  const DEFAULTS = { video: 'assets/videos/hero.mp4', poster: 'assets/images/general/index.webp', badge: 'Bienvenido' };
  const rawHome  = CM.get('home');
  const hero = (rawHome && rawHome.hero && !Array.isArray(rawHome.hero))
    ? Object.assign({}, DEFAULTS, rawHome.hero)
    : Object.assign({}, DEFAULTS);

  let original = Object.assign({}, hero);
  let dirty = false;

  /* ── Dirty tracking ──────────────────────────── */
  function getValues() {
    return {
      badge:  document.getElementById('heroBadge').value,
      poster: document.getElementById('posterPath').value,
      video:  document.getElementById('heroVideo').value,
    };
  }
  function checkDirty() {
    const cur = getValues();
    const changed = cur.badge !== original.badge
                 || cur.poster !== original.poster
                 || cur.video  !== original.video;
    changed ? markDirty() : clearDirty();
  }
  function markDirty() {
    dirty = true;
    document.getElementById('unsavedNotice').classList.remove('hidden');
  }
  function clearDirty() {
    dirty = false;
    document.getElementById('unsavedNotice').classList.add('hidden');
    hideBlurPrompt();
  }

  /* ── Blur-save prompt ────────────────────────── */
  let blurTimer = null;
  let bpAutoTimer = null;

  function onFieldBlur() {
    if (!dirty) return;
    clearTimeout(blurTimer);
    blurTimer = setTimeout(() => { if (dirty) showBlurPrompt(); }, 1400);
  }
  document.addEventListener('focusin', function(e) {
    if (e.target.matches('input,textarea')) clearTimeout(blurTimer);
  });
  function showBlurPrompt() {
    const el = document.getElementById('blurPrompt');
    const bar = document.getElementById('bpBar');
    el.classList.add('show');
    bar.classList.remove('ticking');
    void bar.offsetWidth;
    bar.classList.add('ticking');
    clearTimeout(bpAutoTimer);
    bpAutoTimer = setTimeout(hideBlurPrompt, 6000);
  }
  function hideBlurPrompt() {
    document.getElementById('blurPrompt').classList.remove('show');
    document.getElementById('bpBar').classList.remove('ticking');
    clearTimeout(bpAutoTimer);
  }
  async function promptSave() {
    hideBlurPrompt();
    await saveInicio();
  }

  /* ── Nav-away modal ──────────────────────────── */
  let pendingNavUrl = null;
  function interceptNavLinks() {
    document.querySelectorAll('.admin-sidebar a[href], #logoutLink').forEach(link => {
      link.addEventListener('click', function(e) {
        if (!dirty) return;
        e.preventDefault();
        pendingNavUrl = this.href;
        document.getElementById('navModal').classList.add('show');
      });
    });
  }
  setTimeout(interceptNavLinks, 200);

  async function modalSaveAndGo() { await saveInicio(); if (pendingNavUrl) window.location.href = pendingNavUrl; }
  function modalDiscardAndGo()    { dirty = false; if (pendingNavUrl) window.location.href = pendingNavUrl; }
  function modalCancel()          { pendingNavUrl = null; document.getElementById('navModal').classList.remove('show'); }
  document.addEventListener('keydown', e => {
    if ((e.ctrlKey || e.metaKey) && e.key === 's') { e.preventDefault(); saveInicio(); return; }
    if (e.key === 'Escape') modalCancel();
  });

  window.addEventListener('beforeunload', function(e) {
    if (dirty) { e.preventDefault(); e.returnValue = '¿Salir sin guardar los cambios?'; return e.returnValue; }
  });

  document.getElementById('heroBadge').value  = hero.badge  || '';
  document.getElementById('posterPath').value = hero.poster || '';
  document.getElementById('heroVideo').value  = hero.video  || '';

  onBadgeChange(hero.badge  || '');
  setPosterPreview(hero.poster || '');
  onVideoChange(hero.video  || '');

  /* ── Badge ───────────────────────────────────── */
  function onBadgeChange(val) {
    const text = val.trim() || 'Bienvenido';
    document.getElementById('badgePreviewText').textContent = text;
    document.getElementById('badgeCount').textContent       = val.length + '/60';
    const s = document.getElementById('statBadge');
    s.textContent = val.trim() || '—';
    s.className   = val.trim() ? 'bsc-val' : 'bsc-val dim';
    checkDirty();
  }

  /* ── Poster ──────────────────────────────────── */
  function setPosterPreview(src) {
    const frame      = document.getElementById('posterFrame');
    const img        = document.getElementById('posterImg');
    const noImg      = document.getElementById('posterNoImg');
    const deviceImg  = document.getElementById('devicePoster');
    const deviceNone = document.getElementById('deviceNoPoster');
    const statPoster = document.getElementById('statPoster');

    if (!src) {
      img.style.display    = 'none';
      noImg.style.display  = '';
      deviceImg.style.display  = 'none';
      deviceNone.style.display = '';
      statPoster.textContent   = '—';
      statPoster.className     = 'bsc-val dim';
      return;
    }
    const fullSrc = '../../' + src;
    img.onload = () => {
      img.style.display   = 'block';
      noImg.style.display = 'none';
      deviceImg.style.display  = 'block';
      deviceNone.style.display = 'none';
    };
    img.onerror = () => {
      img.style.display   = 'none';
      noImg.style.display = '';
      deviceImg.style.display  = 'none';
      deviceNone.style.display = '';
    };
    img.src = fullSrc;
    deviceImg.src = fullSrc;
    const short = src.split('/').pop();
    statPoster.textContent = short || src;
    statPoster.className   = 'bsc-val';
    checkDirty();
  }

  /* ── Video — status pill + banner ───────────── */
  function onVideoChange(val) {
    const pill        = document.getElementById('videoPill');
    const deviceBadge = document.getElementById('deviceVideoBadge');
    const statVideo   = document.getElementById('statVideo');
    const hasVal      = val.trim().length > 0;
    if (hasVal) {
      pill.className = 'video-status-pill active';
      pill.innerHTML = '<span class="video-status-dot"></span> Video configurado';
      deviceBadge.className = 'video-badge on';
      deviceBadge.innerHTML = '<span class="video-badge-dot"></span> Video activo';
      const short = val.trim().split('/').pop();
      statVideo.textContent = short;
      statVideo.className   = 'bsc-val';
    } else {
      pill.className = 'video-status-pill empty';
      pill.innerHTML = '<span class="video-status-dot"></span> Sin video';
      deviceBadge.className = 'video-badge off';
      deviceBadge.innerHTML = '<span class="video-badge-dot"></span> Sin video';
      statVideo.textContent = '—';
      statVideo.className   = 'bsc-val dim';
    }
    checkDirty();
  }

  /* ── Video — quality analysis (client-side) ── */
  const VCHECK_ICONS = {
    ok:   '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20,6 9,17 4,12"/></svg>',
    warn: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
    bad:  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',
    info: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
  };

  function analyzeVideo(file, localURL) {
    const probe = document.createElement('video');
    probe.preload = 'metadata';
    probe.src = localURL;
    probe.onloadedmetadata = () => {
      const w      = probe.videoWidth;
      const h      = probe.videoHeight;
      const dur    = probe.duration;
      const sizeMB = file.size / 1024 / 1024;
      const kbps   = dur > 0 ? Math.round(file.size * 8 / dur / 1000) : 0;

      document.getElementById('vStatSize').textContent    = sizeMB.toFixed(1) + ' MB';
      document.getElementById('vStatRes').textContent     = w && h ? w + '×' + h : '—';
      document.getElementById('vStatDur').textContent     = isFinite(dur) ? Math.round(dur) + ' seg' : '—';
      document.getElementById('vStatBitrate').textContent = kbps > 0 ? kbps.toLocaleString() + ' kbps' : '—';

      const checks = [];

      /* Formato */
      if (file.type === 'video/mp4' || file.name.toLowerCase().endsWith('.mp4')) {
        checks.push({ t: 'ok',   m: 'Formato MP4 — óptimo para todos los navegadores' });
      } else if (file.type === 'video/webm') {
        checks.push({ t: 'warn', m: 'WebM: sin soporte en Safari. Recomendamos MP4 (H.264)' });
      } else {
        checks.push({ t: 'bad',  m: 'Formato no recomendado — convierte a MP4 (H.264)' });
      }

      /* Tamaño */
      if (sizeMB <= 15)       checks.push({ t: 'ok',   m: 'Peso excelente — carga rápida en web' });
      else if (sizeMB <= 40)  checks.push({ t: 'ok',   m: 'Peso aceptable para un hero de video' });
      else if (sizeMB <= 80)  checks.push({ t: 'warn', m: 'Archivo pesado — considera comprimirlo (HandBrake o FFmpeg)' });
      else                    checks.push({ t: 'bad',  m: 'Muy pesado — puede ralentizar la carga del sitio' });

      /* Resolución */
      if (w && h) {
        if (w <= 1920 && h <= 1080)       checks.push({ t: 'ok',   m: 'Resolución ' + w + '×' + h + ' — ideal para web (≤ 1080p)' });
        else if (w <= 2560 && h <= 1440)  checks.push({ t: 'warn', m: 'Resolución 1440p — considera exportar en 1080p para reducir peso' });
        else                              checks.push({ t: 'bad',  m: '4K (' + w + '×' + h + ') — muy pesado para hero web; exporta en 1080p' });
      }

      /* Duración */
      if (isFinite(dur)) {
        if (dur <= 30)       checks.push({ t: 'ok',   m: 'Duración ideal para loop (' + Math.round(dur) + ' seg ≤ 30 seg)' });
        else if (dur <= 60)  checks.push({ t: 'info', m: 'Duración larga (' + Math.round(dur) + ' seg) — los loops cortos pesan menos' });
        else                 checks.push({ t: 'warn', m: 'Video muy largo — el hero usa loops; se recomienda < 30 seg' });
      }

      /* Bitrate */
      if (kbps > 0) {
        if (kbps <= 5000)        checks.push({ t: 'ok',   m: 'Bitrate eficiente — buena relación calidad/peso' });
        else if (kbps <= 12000)  checks.push({ t: 'warn', m: 'Bitrate alto (' + kbps.toLocaleString() + ' kbps) — puede tardar en cargar' });
        else                     checks.push({ t: 'bad',  m: 'Bitrate muy alto — comprime para web (target 2000–5000 kbps)' });
      }

      document.getElementById('videoChecks').innerHTML = checks
        .map(c => `<div class="vcheck ${c.t}">${VCHECK_ICONS[c.t]}<span>${c.m}</span></div>`)
        .join('');

      document.getElementById('videoAnalysis').classList.add('visible');
    };
  }

  /* ── Video — drag & drop ─────────────────── */
  function onVideoDragOver(e) {
    e.preventDefault();
    document.getElementById('videoUploadZone').classList.add('dragging');
    document.getElementById('videoUploadZoneText').textContent = 'Suelta para analizar y subir';
  }
  function onVideoDragLeave() {
    document.getElementById('videoUploadZone').classList.remove('dragging');
    document.getElementById('videoUploadZoneText').textContent = 'Clic o arrastra un video MP4';
  }
  function onVideoDrop(e) {
    e.preventDefault();
    onVideoDragLeave();
    const file = e.dataTransfer.files[0];
    if (file) uploadVideoFile(file);
  }
  function onVideoFileSelect(input) {
    if (!input.files[0]) return;
    uploadVideoFile(input.files[0]);
    input.value = '';
  }

  /* ── Video — upload con progreso ─────────── */
  function uploadVideoFile(file) {
    const ALLOWED_TYPES = ['video/mp4','video/webm','video/ogg','video/quicktime','video/x-m4v'];
    const ALLOWED_EXTS  = /\.(mp4|webm|ogv|ogg|mov|m4v)$/i;
    if (!ALLOWED_TYPES.includes(file.type) && !ALLOWED_EXTS.test(file.name)) {
      showToast('Solo se permiten videos MP4, WebM u OGG', 'error'); return;
    }
    if (file.size > 200 * 1024 * 1024) {
      showToast('El video supera el límite de 200 MB', 'error'); return;
    }

    const localURL = URL.createObjectURL(file);

    /* preview local inmediato */
    const player = document.getElementById('videoPlayer');
    player.src = localURL;
    player.style.display = 'block';
    document.getElementById('videoNoPreview').style.display = 'none';
    player.play().catch(() => {});

    /* análisis de calidad */
    analyzeVideo(file, localURL);

    /* bloquear zona de upload */
    const zone = document.getElementById('videoUploadZone');
    zone.style.pointerEvents = 'none'; zone.style.opacity = '.45';

    /* progress bar */
    const progWrap = document.getElementById('videoProg');
    const progBar  = document.getElementById('videoProgBar');
    const progLbl  = document.getElementById('videoProgLabel');
    progWrap.classList.remove('done');
    progWrap.classList.add('visible');
    progBar.style.width = '0%';
    progLbl.textContent = 'Preparando subida…';

    const xhr = new XMLHttpRequest();
    xhr.open('POST', '../api/contenido.php');
    xhr.setRequestHeader('X-CSRF-Token', CSRF_TOKEN);

    xhr.upload.onprogress = (e) => {
      if (!e.lengthComputable) return;
      const pct    = Math.round(e.loaded / e.total * 100);
      const loaded = (e.loaded / 1024 / 1024).toFixed(1);
      const total  = (e.total  / 1024 / 1024).toFixed(1);
      progBar.style.width = pct + '%';
      progLbl.textContent = `Subiendo ${pct}% — ${loaded} / ${total} MB`;
    };

    xhr.onload = () => {
      zone.style.pointerEvents = ''; zone.style.opacity = '';
      let json;
      try { json = JSON.parse(xhr.responseText); } catch { json = {}; }

      if (json.ok) {
        document.getElementById('heroVideo').value = json.path;
        onVideoChange(json.path); /* checkDirty() ya se llama dentro */
        progBar.style.width = '100%';
        progLbl.textContent = '¡Video subido correctamente!';
        progWrap.classList.add('done');
        setTimeout(() => progWrap.classList.remove('visible', 'done'), 3000);
        showToast('Video subido correctamente');
        URL.revokeObjectURL(localURL);
      } else {
        showToast(json.error || 'Error al subir el video', 'error');
        progWrap.classList.remove('visible');
        player.style.display = 'none';
        player.src = '';
        document.getElementById('videoNoPreview').style.display = '';
        document.getElementById('videoAnalysis').classList.remove('visible');
      }
    };

    xhr.onerror = () => {
      zone.style.pointerEvents = ''; zone.style.opacity = '';
      showToast('Error de conexión — intenta de nuevo', 'error');
      progWrap.classList.remove('visible');
    };

    const fd = new FormData();
    fd.append('video', file);
    xhr.send(fd);
  }

  /* cargar preview si ya hay video configurado */
  if (hero.video) {
    const player = document.getElementById('videoPlayer');
    player.src = '../../' + hero.video;
    player.oncanplay = () => {
      player.style.display = 'block';
      document.getElementById('videoNoPreview').style.display = 'none';
    };
  }

  /* ── Drag & drop zone ────────────────────────── */
  function onDragOver(e) {
    e.preventDefault();
    document.getElementById('uploadZone').classList.add('dragging');
    document.getElementById('uploadZoneText').textContent = 'Suelta para subir';
  }
  function onDragLeave(e) {
    document.getElementById('uploadZone').classList.remove('dragging');
    document.getElementById('uploadZoneText').textContent = 'Clic o arrastra una imagen';
  }
  function onDrop(e) {
    e.preventDefault();
    onDragLeave(e);
    const file = e.dataTransfer.files[0];
    if (file) uploadPosterFile(file);
  }

  /* ── Upload poster ───────────────────────────── */
  const pickIcon = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="22" height="22"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17,8 12,3 7,8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>`;

  async function uploadPoster(input) {
    if (!input.files[0]) return;
    await uploadPosterFile(input.files[0]);
    input.value = '';
  }

  async function uploadPosterFile(file) {
    if (file.size > 5 * 1024 * 1024) { showToast('Máximo 5 MB por imagen', 'error'); return; }
    if (!file.type.startsWith('image/')) { showToast('Solo se permiten imágenes', 'error'); return; }

    const localURL = URL.createObjectURL(file);
    document.getElementById('posterPath').value = '';
    setPosterPreview('');
    const img = document.getElementById('posterImg');
    img.src = localURL; img.style.display = 'block';
    document.getElementById('posterNoImg').style.display = 'none';
    document.getElementById('devicePoster').src = localURL;
    document.getElementById('devicePoster').style.display = 'block';
    document.getElementById('deviceNoPoster').style.display = 'none';

    const zone = document.getElementById('uploadZone');
    zone.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="22" height="22"><circle cx="12" cy="12" r="10"/><polyline points="12,6 12,12 16,14"/></svg><div><strong>Subiendo imagen…</strong></div><span>Por favor espera</span>';

    const fd = new FormData();
    fd.append('image', file);
    try {
      const res  = await fetch('../api/contenido.php', { method: 'POST', headers: { 'X-CSRF-Token': CSRF_TOKEN }, body: fd });
      const json = await res.json();
      if (json.ok) {
        document.getElementById('posterPath').value = json.path;
        setPosterPreview(json.path); /* checkDirty() ya se llama dentro */
        showToast('Imagen subida correctamente');
      } else {
        showToast(json.error || 'Error al subir', 'error');
      }
    } catch {
      showToast('Error de conexión', 'error');
    } finally {
      zone.innerHTML = pickIcon + '<div><strong id="uploadZoneText">Clic o arrastra una imagen</strong></div><span>JPG, PNG, WebP · máx 5 MB</span>';
    }
  }

  /* ── Cancel / Save ───────────────────────────── */
  function cancelInicio() {
    document.getElementById('heroBadge').value  = original.badge  || '';
    document.getElementById('posterPath').value = original.poster || '';
    document.getElementById('heroVideo').value  = original.video  || '';
    onBadgeChange(original.badge  || '');
    setPosterPreview(original.poster || '');
    onVideoChange(original.video  || '');
    /* reset video preview */
    const player = document.getElementById('videoPlayer');
    player.pause(); player.src = original.video ? '../../' + original.video : '';
    if (original.video) {
      player.style.display = 'block';
      document.getElementById('videoNoPreview').style.display = 'none';
    } else {
      player.style.display = 'none';
      document.getElementById('videoNoPreview').style.display = '';
    }
    document.getElementById('videoAnalysis').classList.remove('visible');
    document.getElementById('videoProg').classList.remove('visible', 'done');
    clearDirty();
    showToast('Cambios descartados');
  }

  function viewPublic(url) { window.open(url + '?v=' + Date.now(), 'dematiq_public'); }

  async function saveInicio() {
    const home = CM.get('home') || {};
    home.hero = {
      badge:  document.getElementById('heroBadge').value.trim(),
      poster: document.getElementById('posterPath').value.trim(),
      video:  document.getElementById('heroVideo').value.trim()
    };
    try {
      const res = await CM.set('home', home);
      if (res && res.ok) {
        original = Object.assign({}, home.hero);
        clearDirty();
        showToast('Cambios guardados correctamente');
        viewPublic('/index.html');
      } else {
        showToast(res?.error || 'Error al guardar', 'error');
      }
    } catch {
      showToast('Error de conexión', 'error');
    }
  }
</script>

<!-- stubs para JS legacy — nunca visibles al usuario -->
<div style="display:none;position:fixed;top:-9999px;left:-9999px;pointer-events:none" aria-hidden="true">
  <span id="deviceBadge">Bienvenido</span>
  <span id="deviceVideoBadge"></span>
  <div  id="deviceNoPoster"></div>
  <img  id="devicePoster" src="" alt="">
</div>
</body>
</html>
