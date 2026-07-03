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
  <title>Industrias | DEMATIQ Admin</title>
  <link rel="stylesheet" href="../assets/css/admin.css?v=7">
  <link rel="icon" type="image/svg+xml" href="../../assets/images/logos/favicon-d.svg">
  <style>

    /* ─── BANNER ─────────────────────────────────────── */
    .ind-banner {
      background: linear-gradient(135deg,#071f3a 0%,#0a3d2e 45%,#065f46 100%);
      border-radius: 20px;
      padding: 30px 32px;
      margin-bottom: 0;
      position: relative;
      overflow: hidden;
    }
    .ind-banner::before {
      content:'';position:absolute;
      width:500px;height:500px;border-radius:50%;
      background:radial-gradient(circle,rgba(16,185,129,.18) 0%,transparent 65%);
      top:-200px;right:-60px;pointer-events:none;
    }
    .ind-banner::after {
      content:'';position:absolute;
      width:200px;height:200px;border-radius:50%;
      background:radial-gradient(circle,rgba(52,211,153,.07) 0%,transparent 70%);
      bottom:-80px;left:35%;pointer-events:none;
    }
    .ind-banner-mesh {
      position:absolute;inset:0;pointer-events:none;overflow:hidden;
      background-image:radial-gradient(rgba(255,255,255,.04) 1px,transparent 1px);
      background-size:22px 22px;
    }
    .banner-inner { position:relative;z-index:1; }
    .banner-chip {
      display:inline-flex;align-items:center;gap:7px;
      background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.15);
      color:rgba(255,255,255,.75);font-size:.62rem;font-weight:700;
      letter-spacing:1.8px;text-transform:uppercase;
      padding:5px 12px;border-radius:20px;margin-bottom:14px;
    }
    .bdot{width:6px;height:6px;border-radius:50%;background:#34d399;animation:bdot 2.2s ease-in-out infinite;}
    @keyframes bdot{0%,100%{opacity:1;box-shadow:0 0 0 0 rgba(52,211,153,.5);}50%{opacity:.7;box-shadow:0 0 0 5px rgba(52,211,153,0);}}
    .banner-title{font-size:1.65rem;font-weight:800;color:#fff;letter-spacing:-.025em;line-height:1.1;margin-bottom:6px;}
    .banner-desc{font-size:.82rem;color:rgba(255,255,255,.5);line-height:1.65;max-width:420px;margin-bottom:22px;}
    .banner-section-cards{display:flex;gap:12px;flex-wrap:wrap;}
    .bsc{display:flex;align-items:center;gap:10px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.12);padding:10px 16px;border-radius:14px;flex:1;min-width:140px;transition:background .2s;}
    .bsc:hover{background:rgba(255,255,255,.12);}
    .bsc-icon{width:34px;height:34px;border-radius:10px;flex-shrink:0;display:flex;align-items:center;justify-content:center;}
    .bsc-icon svg{width:16px;height:16px;color:#fff;}
    .bsci-green  {background:linear-gradient(135deg,#065f46,#10b981);}
    .bsci-teal   {background:linear-gradient(135deg,#0e7490,#06b6d4);}
    .bsci-indigo {background:linear-gradient(135deg,#1a4a9e,#4f6fc2);}
    .bsc-info{}
    .bsc-label{font-size:.6rem;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.8px;margin-bottom:2px;}
    .bsc-val{font-size:.82rem;font-weight:700;color:#fff;line-height:1.2;max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
    @media(max-width:600px){.ind-banner{padding:22px 18px;}.banner-section-cards{flex-direction:column;}}

    /* ─── UNSAVED NOTICE ────────────────────────────── */
    .unsaved-notice{display:flex;align-items:center;gap:12px;background:linear-gradient(90deg,#fffbeb,#fef3c7);border:1.5px solid #fcd34d;border-radius:14px;padding:11px 16px;margin-bottom:16px;animation:slideIn .35s ease;}
    .unsaved-notice.hidden{display:none;}
    @keyframes slideIn{from{opacity:0;transform:translateY(-10px);}to{opacity:1;transform:translateY(0);}}
    .un-icon{width:36px;height:36px;border-radius:10px;background:#fbbf24;flex-shrink:0;display:flex;align-items:center;justify-content:center;}
    .un-icon svg{width:17px;height:17px;color:#fff;}
    .un-text{flex:1;}
    .un-text strong{display:block;font-size:.82rem;font-weight:700;color:#92400e;}
    .un-text span{font-size:.72rem;color:#b45309;}
    .un-save{display:inline-flex;align-items:center;gap:6px;background:#f59e0b;color:#fff;border:none;cursor:pointer;font-size:.76rem;font-weight:700;padding:7px 14px;border-radius:9px;transition:background .15s,transform .1s;white-space:nowrap;}
    .un-save:hover{background:#d97706;transform:translateY(-1px);}
    .un-save svg{width:14px;height:14px;}

    /* ─── SECTION CARD ──────────────────────────────── */
    .section-card{background:#fff;border:1.5px solid var(--border);border-radius:20px;overflow:hidden;margin-bottom:14px;transition:box-shadow .2s,border-color .2s;}
    .sc-head{display:flex;align-items:center;gap:16px;padding:18px 24px;border-bottom:1px solid var(--border);background:linear-gradient(to right,#f8faff,#fff);}
    .sc-icon{width:44px;height:44px;border-radius:12px;flex-shrink:0;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 14px rgba(0,0,0,.18);}
    .sc-icon svg{width:20px;height:20px;color:#fff;}
    .si-green{background:linear-gradient(135deg,#065f46,#10b981);}
    .sc-head-text{flex:1;min-width:0;}
    .sc-head-text h3{font-size:.95rem;font-weight:700;color:var(--text);}
    .sc-head-text p{font-size:.75rem;color:var(--text-lt);margin-top:2px;}

    /* ─── FIELDS ────────────────────────────────────── */
    .field{margin-bottom:18px;}
    .field:last-child{margin-bottom:0;}
    .field-top{display:flex;align-items:baseline;justify-content:space-between;margin-bottom:7px;}
    .field-top label{font-size:.71rem;font-weight:700;color:var(--text-lt);text-transform:uppercase;letter-spacing:.5px;}
    .field-cnt{font-size:.65rem;color:#b0b8cc;font-variant-numeric:tabular-nums;}
    .field-hint{font-size:.65rem;color:#aab;font-style:italic;margin-top:4px;}
    input.fi,textarea.fi{width:100%;box-sizing:border-box;padding:10px 13px;border:1.5px solid var(--border);border-radius:10px;font-size:.87rem;font-family:inherit;color:var(--text);background:#fafcff;outline:none;transition:border-color .15s,box-shadow .15s,background .15s;}
    input.fi:focus,textarea.fi:focus{border-color:var(--accent-lt);background:#fff;box-shadow:0 0 0 3px rgba(46,107,207,.1);}
    textarea.fi{resize:vertical;line-height:1.6;}

    /* ─── SLIDE CARDS ───────────────────────────────── */
    .slide-card{border-bottom:1.5px solid var(--border);}
    .slide-card:last-child{border-bottom:none;}
    .slide-card-header{display:flex;align-items:center;justify-content:space-between;padding:12px 20px;background:linear-gradient(to right,#f5f8ff,#fff);border-bottom:1px solid var(--border);gap:10px;}
    .slide-badge{display:flex;align-items:center;gap:10px;min-width:0;}
    .slide-num{width:28px;height:28px;border-radius:8px;background:linear-gradient(135deg,#065f46,#10b981);color:#fff;font-size:.75rem;font-weight:800;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
    .slide-header-title{font-size:.87rem;font-weight:700;color:var(--text);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
    .slide-header-title.empty{color:var(--text-lt);font-style:italic;font-weight:400;}

    /* ─── SLIDE BODY ─────────────────────────────────── */
    .slide-body{display:grid;grid-template-columns:340px 1fr;align-items:start;}
    @media(max-width:840px){.slide-body{grid-template-columns:1fr;}}

    .slide-img-col{border-right:1px solid var(--border);padding:18px 16px;display:flex;flex-direction:column;gap:12px;background:#fafcff;}
    @media(max-width:840px){.slide-img-col{border-right:none;border-bottom:1px solid var(--border);}}

    .carousel-preview-label{font-size:.62rem;font-weight:700;color:var(--text-lt);text-transform:uppercase;letter-spacing:.8px;display:flex;align-items:center;gap:5px;margin-bottom:2px;}
    .carousel-preview-label svg{width:10px;height:10px;opacity:.6;}

    /* las dos imágenes lado a lado */
    .dual-preview{display:grid;grid-template-columns:1fr 1fr;gap:10px;}
    .img-slot{display:flex;flex-direction:column;gap:6px;}
    .img-slot-label{font-size:.6rem;font-weight:700;color:var(--text-lt);text-transform:uppercase;letter-spacing:.6px;display:flex;align-items:center;gap:4px;}
    .img-slot-num{width:16px;height:16px;border-radius:4px;font-size:.6rem;font-weight:800;color:#fff;background:linear-gradient(135deg,#065f46,#10b981);display:flex;align-items:center;justify-content:center;flex-shrink:0;}

    .img-preview-box{width:100%;aspect-ratio:16/9;border-radius:8px;overflow:hidden;background:#eef2ff;border:1.5px dashed #c7d5f0;position:relative;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:border-color .18s,box-shadow .2s,transform .18s;}
    .img-preview-box:hover{border-color:var(--accent-lt);box-shadow:0 4px 14px rgba(46,107,207,.18);transform:translateY(-1px);}
    .img-preview-box img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;display:block;}
    .img-placeholder{display:flex;flex-direction:column;align-items:center;gap:4px;color:var(--text-lt);font-size:.6rem;text-align:center;}
    .img-placeholder svg{opacity:.2;}
    .img-hover-hint{position:absolute;inset:0;background:rgba(7,31,58,.5);display:flex;align-items:center;justify-content:center;gap:4px;opacity:0;transition:opacity .15s;color:#fff;font-size:.65rem;font-weight:700;backdrop-filter:blur(2px);}
    .img-hover-hint svg{width:12px;height:12px;}
    .img-preview-box:hover .img-hover-hint{opacity:1;}

    .img-upload-btn{display:flex;align-items:center;justify-content:center;gap:5px;padding:5px 8px;border-radius:7px;border:1.5px solid var(--border);background:#fff;color:var(--text-lt);font-size:.68rem;font-weight:600;cursor:pointer;transition:all .15s;width:100%;box-sizing:border-box;}
    .img-upload-btn:hover{border-color:var(--accent-lt);color:var(--accent);background:#f0f4ff;}
    .img-upload-btn svg{width:11px;height:11px;}
    .img-upload-btn:disabled{opacity:.5;pointer-events:none;}

    .img-path-field{width:100%;box-sizing:border-box;padding:6px 9px;border:1.5px solid var(--border);border-radius:8px;font-size:.65rem;font-family:monospace;color:var(--text-lt);background:#f7f9ff;outline:none;transition:border-color .15s,color .15s;}
    .img-path-field:focus{border-color:var(--accent-lt);color:var(--text);}
    .img-path-field::placeholder{color:#b5c0d4;}

    .slide-fields-col{padding:20px 22px;display:flex;flex-direction:column;}

    /* ─── EMPTY STATE ────────────────────────────────── */
    .ind-empty{text-align:center;padding:40px 24px;color:var(--text-lt);}
    .ind-empty svg{opacity:.15;margin-bottom:10px;}
    .ind-empty p{font-size:.88rem;margin-bottom:4px;}
    .ind-empty strong{color:var(--text);font-size:.82rem;}

    /* ─── ADD BUTTON ─────────────────────────────────── */
    .add-ind-btn{display:flex;align-items:center;justify-content:center;gap:8px;margin:16px 24px;padding:10px;border-radius:12px;border:1.5px dashed #c7d5f0;background:#f7f9ff;color:var(--accent);font-size:.82rem;font-weight:700;cursor:pointer;transition:all .18s;}
    .add-ind-btn:hover{border-color:var(--accent-lt);background:#eef3ff;}
    .add-ind-btn svg{width:16px;height:16px;}

    /* ─── SAVE BAR ───────────────────────────────────── */
    .save-bar-sticky{position:sticky;bottom:0;background:rgba(240,244,255,.92);backdrop-filter:blur(12px);border-top:1px solid rgba(221,229,245,.9);padding:12px 24px;display:flex;align-items:center;gap:10px;flex-wrap:wrap;z-index:50;margin:8px -24px 0;}
    .save-spacer{flex:1;}

    /* ─── BLUR-SAVE PROMPT ───────────────────────────── */
    .blur-prompt{position:fixed;bottom:80px;right:24px;z-index:500;background:#fff;border-radius:18px;box-shadow:0 12px 40px rgba(0,0,0,.18),0 0 0 1px rgba(0,0,0,.06);padding:18px 20px;width:260px;transform:translateY(16px) scale(.97);opacity:0;pointer-events:none;transition:all .25s cubic-bezier(.34,1.56,.64,1);}
    .blur-prompt.show{transform:translateY(0) scale(1);opacity:1;pointer-events:auto;}
    .bp-head{display:flex;align-items:center;gap:10px;margin-bottom:12px;}
    .bp-head-icon{width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,#f59e0b,#fbbf24);flex-shrink:0;display:flex;align-items:center;justify-content:center;}
    .bp-head-icon svg{width:16px;height:16px;color:#fff;}
    .bp-head-text strong{display:block;font-size:.82rem;font-weight:700;color:var(--text);}
    .bp-head-text span{font-size:.7rem;color:var(--text-lt);}
    .bp-actions{display:flex;gap:8px;}
    .bp-yes{flex:1;background:linear-gradient(135deg,#065f46,#10b981);color:#fff;border:none;cursor:pointer;font-size:.78rem;font-weight:700;padding:8px 0;border-radius:9px;transition:opacity .15s;}
    .bp-yes:hover{opacity:.88;}
    .bp-no{flex:1;background:#f1f4fa;color:var(--text-lt);border:none;cursor:pointer;font-size:.78rem;font-weight:600;padding:8px 0;border-radius:9px;transition:background .15s;}
    .bp-no:hover{background:#e5eaf5;}
    .bp-bar{height:3px;border-radius:2px;background:#f59e0b;margin-top:12px;transform-origin:left;animation:none;}
    .bp-bar.ticking{animation:bpTick 6s linear forwards;}
    @keyframes bpTick{from{transform:scaleX(1);}to{transform:scaleX(0);}}

    /* ─── NAV-AWAY MODAL ─────────────────────────────── */
    .nav-modal-backdrop{position:fixed;inset:0;background:rgba(7,31,58,.55);backdrop-filter:blur(4px);z-index:900;display:flex;align-items:center;justify-content:center;opacity:0;pointer-events:none;transition:opacity .25s;}
    .nav-modal-backdrop.show{opacity:1;pointer-events:auto;}
    .nav-modal{background:#fff;border-radius:22px;padding:32px;width:360px;max-width:90vw;box-shadow:0 24px 80px rgba(0,0,0,.3);transform:scale(.94);transition:transform .25s cubic-bezier(.34,1.56,.64,1);}
    .nav-modal-backdrop.show .nav-modal{transform:scale(1);}
    .nm-icon{width:52px;height:52px;border-radius:16px;background:linear-gradient(135deg,#f59e0b,#fbbf24);display:flex;align-items:center;justify-content:center;margin-bottom:16px;}
    .nm-icon svg{width:24px;height:24px;color:#fff;}
    .nm-title{font-size:1.05rem;font-weight:800;color:var(--text);margin-bottom:6px;}
    .nm-desc{font-size:.82rem;color:var(--text-lt);line-height:1.6;margin-bottom:22px;}
    .nm-actions{display:flex;flex-direction:column;gap:8px;}
    .nm-btn{display:flex;align-items:center;justify-content:center;gap:8px;padding:11px 16px;border-radius:12px;font-size:.85rem;font-weight:700;cursor:pointer;border:none;transition:all .15s;}
    .nm-btn.primary{background:linear-gradient(135deg,#065f46,#10b981);color:#fff;}
    .nm-btn.primary:hover{opacity:.9;}
    .nm-btn.danger{background:#fff1f0;color:#dc2626;border:1.5px solid #fecaca;}
    .nm-btn.danger:hover{background:#fee2e2;}
    .nm-btn.cancel{background:#f1f4fa;color:var(--text-lt);}
    .nm-btn.cancel:hover{background:#e5eaf5;}

    @media(max-width:600px){.slide-fields-col{padding:16px 18px;}.blur-prompt{right:12px;bottom:70px;width:230px;}}
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
      <span class="admin-topbar-title">Industrias</span>
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

    <!-- ══ BANNER ══════════════════════════════════════ -->
    <div class="ind-banner" style="margin-bottom:16px;">
      <div class="ind-banner-mesh"></div>
      <div class="banner-inner">
        <div class="banner-chip"><span class="bdot"></span> Página activa</div>
        <h1 class="banner-title">Industrias</h1>
        <p class="banner-desc">Administra los sectores industriales, sus imágenes en carrusel y la descripción que aparecen en la página pública.</p>
        <div class="banner-section-cards">
          <div class="bsc">
            <div class="bsc-icon bsci-green">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12,2 2,7 12,12 22,7"/><polyline points="2,17 12,22 22,17"/><polyline points="2,12 12,17 22,12"/></svg>
            </div>
            <div class="bsc-info">
              <div class="bsc-label">Sectores</div>
              <div class="bsc-val" id="statTotal">0</div>
            </div>
          </div>
          <div class="bsc">
            <div class="bsc-icon bsci-teal">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21,15 16,10 5,21"/></svg>
            </div>
            <div class="bsc-info">
              <div class="bsc-label">Con imágenes</div>
              <div class="bsc-val" id="statImgs">0</div>
            </div>
          </div>
          <div class="bsc">
            <div class="bsc-icon bsci-indigo">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><polyline points="8,21 12,17 16,21"/><line x1="12" y1="17" x2="12" y2="3"/></svg>
            </div>
            <div class="bsc-info">
              <div class="bsc-label">Carrusel</div>
              <div class="bsc-val">2 imgs / sector</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ══ UNSAVED NOTICE ══════════════════════════════ -->
    <div class="unsaved-notice hidden" id="unsavedNotice">
      <div class="un-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
      </div>
      <div class="un-text">
        <strong>Tienes cambios sin guardar</strong>
        <span>Guarda para que se reflejen en la página pública</span>
      </div>
      <button class="un-save" onclick="saveIndustrias()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17,21 17,13 7,13 7,21"/><polyline points="7,3 7,8 15,8"/></svg>
        Guardar ahora
      </button>
    </div>

    <!-- ══ CARD: SECTORES ══════════════════════════════ -->
    <div class="section-card">
      <div class="sc-head">
        <div class="sc-icon si-green">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polygon points="12,2 2,7 12,12 22,7"/><polyline points="2,17 12,22 22,17"/><polyline points="2,12 12,17 22,12"/>
          </svg>
        </div>
        <div class="sc-head-text">
          <h3>Sectores industriales</h3>
          <p>Nombre, descripción y 2 imágenes en carrusel por sector</p>
        </div>
        <button class="btn-admin btn-outline-admin" onclick="addIndustria()" style="flex-shrink:0;font-size:.8rem;padding:7px 14px;gap:6px">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Agregar sector
        </button>
      </div>

      <div id="industrias-container"></div>

      <button class="add-ind-btn" onclick="addIndustria()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Agregar nuevo sector
      </button>
    </div>

    <!-- ══ SAVE BAR ═════════════════════════════════════ -->
    <div class="save-bar-sticky">
      <div class="save-spacer"></div>
      <a href="/pages/corporativo/industrias.html" target="_blank" class="btn-admin btn-outline-admin">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
        Ver página
      </a>
      <button class="btn-admin btn-outline-admin" onclick="cancelIndustrias()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        Descartar
      </button>
      <button class="btn-admin btn-primary-admin" onclick="saveIndustrias()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17,21 17,13 7,13 7,21"/><polyline points="7,3 7,8 15,8"/></svg>
        Guardar cambios
      </button>
    </div>

  </div>
</div>

<!-- ══ BLUR-SAVE PROMPT ══════════════════════════════ -->
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

<!-- ══ NAV-AWAY MODAL ════════════════════════════════ -->
<div class="nav-modal-backdrop" id="navModal">
  <div class="nav-modal">
    <div class="nm-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
    </div>
    <div class="nm-title">¿Guardar antes de salir?</div>
    <p class="nm-desc">Tienes cambios sin guardar en <strong>Industrias</strong>. Si sales ahora se perderán.</p>
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

  AdminSidebar.init('industrias', '../', '../../');

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

  /* ─── datos ──────────────────────────────────────── */
  let industrias = (CM.get('industrias') || []).map(ind => ({
    id:          ind.id          || '',
    nombre:      ind.nombre      || '',
    descripcion: ind.descripcion || '',
    imagen1:     ind.imagen1 || ind.imagen || '',  /* backward compat con campo único */
    imagen2:     ind.imagen2     || '',
  }));
  let origJSON = JSON.stringify(industrias);
  let dirty    = false;

  /* ─── dirty state ────────────────────────────────── */
  function markDirty() {
    dirty = true;
    document.getElementById('unsavedNotice').classList.remove('hidden');
    updateBanner();
  }
  function clearDirty() {
    dirty    = false;
    origJSON = JSON.stringify(industrias);
    document.getElementById('unsavedNotice').classList.add('hidden');
    hideBlurPrompt();
    updateBanner();
  }
  function checkDirty() {
    JSON.stringify(industrias) !== origJSON ? markDirty() : clearDirty();
  }

  /* ─── banner stats ───────────────────────────────── */
  function updateBanner() {
    document.getElementById('statTotal').textContent = industrias.length || '0';
    document.getElementById('statImgs').textContent  = industrias.filter(ind => ind.imagen1 || ind.imagen2).length || '0';
  }

  /* ─── blur prompt ────────────────────────────────── */
  let blurTimer = null, bpAutoTimer = null;

  function onFieldBlur() {
    if (!dirty) return;
    clearTimeout(blurTimer);
    blurTimer = setTimeout(() => { if (dirty) showBlurPrompt(); }, 1400);
  }
  document.addEventListener('focusin', e => {
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
  async function promptSave() { hideBlurPrompt(); await saveIndustrias(); }

  /* ─── nav-away modal ─────────────────────────────── */
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

  async function modalSaveAndGo() { await saveIndustrias(); if (pendingNavUrl) window.location.href = pendingNavUrl; }
  function modalDiscardAndGo()    { dirty = false; if (pendingNavUrl) window.location.href = pendingNavUrl; }
  function modalCancel()          { pendingNavUrl = null; document.getElementById('navModal').classList.remove('show'); }
  document.addEventListener('keydown', e => {
    if ((e.ctrlKey || e.metaKey) && e.key === 's') { e.preventDefault(); saveIndustrias(); return; }
    if (e.key === 'Escape') modalCancel();
  });
  window.addEventListener('beforeunload', function(e) {
    if (dirty) { e.preventDefault(); e.returnValue = '¿Salir sin guardar los cambios?'; return e.returnValue; }
  });

  /* ─── preview imagen ─────────────────────────────── */
  function setPreview(i, slot, src) {
    const box = document.getElementById(`imgPreview${i}_${slot}`);
    if (!box) return;
    const img = box.querySelector('img');
    const ph  = box.querySelector('.img-placeholder');
    if (!src) {
      if (img) img.style.display = 'none';
      if (ph)  ph.style.display  = '';
      return;
    }
    if (img) {
      img.src = '../../' + src;
      img.style.display = 'block';
      img.onerror = () => { img.style.display = 'none'; if (ph) ph.style.display = ''; };
      if (ph) ph.style.display = 'none';
    }
  }

  /* ─── upload imagen ──────────────────────────────── */
  async function uploadImage(i, slot, input) {
    if (!input.files[0]) return;
    const file = input.files[0];
    if (file.size > 5 * 1024 * 1024) { showToast('Máximo 5 MB por imagen', 'error'); input.value = ''; return; }

    /* preview local inmediato */
    const box = document.getElementById(`imgPreview${i}_${slot}`);
    const img = box?.querySelector('img');
    const ph  = box?.querySelector('.img-placeholder');
    if (img) { img.src = URL.createObjectURL(file); img.style.display = 'block'; }
    if (ph)  ph.style.display = 'none';

    const btn = document.getElementById(`imgBtn${i}_${slot}`);
    if (btn) { btn.disabled = true; btn.textContent = 'Subiendo…'; }

    const fd = new FormData();
    fd.append('image', file);
    fd.append('folder', 'general');
    try {
      const res  = await fetch('../api/contenido.php', { method: 'POST', headers: { 'X-CSRF-Token': CSRF_TOKEN }, body: fd });
      const json = await res.json();
      if (json.ok) {
        industrias[i][`imagen${slot}`] = json.path;
        const pathEl = document.getElementById(`imgPath${i}_${slot}`);
        if (pathEl) pathEl.value = json.path;
        setPreview(i, slot, json.path);
        markDirty();
        showToast('Imagen subida correctamente');
      } else {
        showToast(json.error || 'Error al subir', 'error');
        setPreview(i, slot, industrias[i][`imagen${slot}`] || '');
      }
    } catch {
      showToast('Error de conexión', 'error');
      setPreview(i, slot, industrias[i][`imagen${slot}`] || '');
    } finally {
      if (btn) {
        btn.disabled = false;
        btn.innerHTML = uploadIcon + ` Subir imagen ${slot}`;
      }
      input.value = '';
    }
  }

  /* ─── render ─────────────────────────────────────── */
  const uploadIcon = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="11" height="11"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17,8 12,3 7,8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>`;
  const trashIcon  = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="14" height="14"><polyline points="3,6 5,6 21,6"/><path d="M19,6l-1,14a2,2,0,0,1-2,2H8a2,2,0,0,1-2-2L5,6"/><path d="M10,11v6"/><path d="M14,11v6"/></svg>`;

  function esc(s)    { return String(s || '').replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
  function escTxt(s) { return String(s || '').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

  function imgSlot(i, slot, val) {
    return `
      <div class="img-slot">
        <div class="img-slot-label"><span class="img-slot-num">${slot}</span> Imagen ${slot}</div>
        <div class="img-preview-box" id="imgPreview${i}_${slot}"
          onclick="document.getElementById('imgFile${i}_${slot}').click()" title="Clic para cambiar imagen">
          <img src="" alt="" style="display:none">
          <div class="img-placeholder">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21,15 16,10 5,21"/></svg>
            <span>Sin imagen</span>
          </div>
          <div class="img-hover-hint">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17,8 12,3 7,8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            Cambiar
          </div>
        </div>
        <button class="img-upload-btn" id="imgBtn${i}_${slot}"
          onclick="document.getElementById('imgFile${i}_${slot}').click()">
          ${uploadIcon} Subir imagen ${slot}
        </button>
        <input type="file" id="imgFile${i}_${slot}" accept="image/*" style="display:none"
          onchange="uploadImage(${i},${slot},this)">
        <input type="text" class="img-path-field" id="imgPath${i}_${slot}" value="${esc(val||'')}"
          placeholder="assets/images/general/foto${slot}.webp"
          oninput="industrias[${i}].imagen${slot}=this.value;setPreview(${i},${slot},this.value);checkDirty()"
          onblur="onFieldBlur()">
      </div>`;
  }

  function renderIndustrias() {
    const c = document.getElementById('industrias-container');
    c.innerHTML = '';

    if (!industrias.length) {
      c.innerHTML = `<div class="ind-empty">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><polygon points="12,2 2,7 12,12 22,7"/><polyline points="2,17 12,22 22,17"/><polyline points="2,12 12,17 22,12"/></svg>
        <p>No hay sectores configurados.</p>
        <strong>Usa el botón <em>Agregar sector</em> para añadir uno.</strong>
      </div>`;
      updateBanner();
      return;
    }

    industrias.forEach((ind, i) => {
      const div = document.createElement('div');
      div.className = 'slide-card';
      div.innerHTML = `
        <div class="slide-card-header">
          <div class="slide-badge">
            <span class="slide-num">${i + 1}</span>
            <span class="slide-header-title${ind.nombre ? '' : ' empty'}" id="indTitle${i}">${esc(ind.nombre) || 'Nuevo sector'}</span>
          </div>
          <button class="btn-rm" onclick="removeIndustria(${i})" title="Eliminar sector" style="flex-shrink:0">${trashIcon}</button>
        </div>
        <div class="slide-body">

          <div class="slide-img-col">
            <div class="carousel-preview-label">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21,15 16,10 5,21"/></svg>
              Imágenes del carrusel
            </div>
            <div class="dual-preview">
              ${imgSlot(i, 1, ind.imagen1)}
              ${imgSlot(i, 2, ind.imagen2)}
            </div>
          </div>

          <div class="slide-fields-col">
            <div class="field">
              <div class="field-top"><label>Nombre del sector</label></div>
              <input type="text" class="fi" value="${esc(ind.nombre||'')}" placeholder="Automotriz"
                oninput="industrias[${i}].nombre=this.value;const el=document.getElementById('indTitle${i}');el.textContent=this.value||'Nuevo sector';el.className='slide-header-title'+(this.value?'':' empty');checkDirty()"
                onblur="onFieldBlur()">
            </div>
            <div class="field">
              <div class="field-top"><label>ID único</label></div>
              <input type="text" class="fi" value="${esc(ind.id||'')}" placeholder="automotriz"
                style="font-family:monospace;font-size:.82rem"
                oninput="industrias[${i}].id=this.value;checkDirty()"
                onblur="onFieldBlur()">
              <p class="field-hint">Sin espacios ni acentos — identifica el panel en la web pública</p>
            </div>
            <div class="field">
              <div class="field-top"><label>Descripción</label></div>
              <textarea class="fi" rows="6"
                oninput="industrias[${i}].descripcion=this.value;checkDirty()"
                onblur="onFieldBlur()">${escTxt(ind.descripcion||'')}</textarea>
            </div>
          </div>

        </div>`;
      c.appendChild(div);
      if (ind.imagen1) setPreview(i, 1, ind.imagen1);
      if (ind.imagen2) setPreview(i, 2, ind.imagen2);
    });

    updateBanner();
  }

  function addIndustria() {
    industrias.push({ id: '', nombre: '', descripcion: '', imagen1: '', imagen2: '' });
    renderIndustrias();
    markDirty();
    const cards = document.querySelectorAll('.slide-card');
    if (cards.length) cards[cards.length - 1].scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  function removeIndustria(i) {
    industrias.splice(i, 1);
    renderIndustrias();
    checkDirty();
  }

  function cancelIndustrias() {
    industrias = JSON.parse(origJSON);
    renderIndustrias();
    clearDirty();
    showToast('Cambios descartados');
  }

  function viewPublic(url) { window.open(url + '?v=' + Date.now(), 'dematiq_public'); }

  async function saveIndustrias() {
    try {
      const res = await CM.set('industrias', industrias);
      if (res && res.ok) { clearDirty(); showToast('Cambios guardados correctamente'); viewPublic('/pages/corporativo/industrias.html'); }
      else showToast(res?.error || 'Error al guardar', 'error');
    } catch { showToast('Error de conexión', 'error'); }
  }

  renderIndustrias();
</script>

</body>
</html>
