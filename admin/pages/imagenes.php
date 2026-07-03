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
  <title>Imágenes | DEMATIQ Admin</title>
  <link rel="stylesheet" href="../assets/css/admin.css?v=7">
  <link rel="icon" type="image/svg+xml" href="../../assets/images/logos/favicon-d.svg">
  <style>

    /* ─── BANNER ─────────────────────────────────────── */
    .img-banner {
      background: linear-gradient(135deg,#001a2e 0%,#002d40 45%,#0c4a6e 100%);
      border-radius: 20px;
      padding: 30px 32px;
      margin-bottom: 0;
      position: relative;
      overflow: hidden;
    }
    .img-banner::before {
      content:'';position:absolute;
      width:500px;height:500px;border-radius:50%;
      background:radial-gradient(circle,rgba(6,182,212,.2) 0%,transparent 65%);
      top:-200px;right:-60px;pointer-events:none;
    }
    .img-banner::after {
      content:'';position:absolute;
      width:200px;height:200px;border-radius:50%;
      background:radial-gradient(circle,rgba(103,232,249,.06) 0%,transparent 70%);
      bottom:-80px;left:35%;pointer-events:none;
    }
    .img-banner-mesh {
      position:absolute;inset:0;pointer-events:none;overflow:hidden;
      background-image:radial-gradient(rgba(255,255,255,.035) 1px,transparent 1px);
      background-size:22px 22px;
    }
    .banner-inner { position:relative;z-index:1; }
    .banner-chip {
      display:inline-flex;align-items:center;gap:7px;
      background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.12);
      color:rgba(255,255,255,.7);font-size:.62rem;font-weight:700;
      letter-spacing:1.8px;text-transform:uppercase;
      padding:5px 12px;border-radius:20px;margin-bottom:14px;
    }
    .bdot{width:6px;height:6px;border-radius:50%;background:#67e8f9;animation:bdot 2.2s ease-in-out infinite;}
    @keyframes bdot{0%,100%{opacity:1;box-shadow:0 0 0 0 rgba(103,232,249,.5);}50%{opacity:.7;box-shadow:0 0 0 5px rgba(103,232,249,0);}}
    .banner-title{font-size:1.65rem;font-weight:800;color:#fff;letter-spacing:-.025em;line-height:1.1;margin-bottom:6px;}
    .banner-desc{font-size:.82rem;color:rgba(255,255,255,.45);line-height:1.65;max-width:460px;margin-bottom:22px;}
    .banner-section-cards{display:flex;gap:12px;flex-wrap:wrap;}
    .bsc{display:flex;align-items:center;gap:10px;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.1);padding:10px 16px;border-radius:14px;flex:1;min-width:120px;transition:background .2s;}
    .bsc:hover{background:rgba(255,255,255,.11);}
    .bsc-icon{width:34px;height:34px;border-radius:10px;flex-shrink:0;display:flex;align-items:center;justify-content:center;}
    .bsc-icon svg{width:16px;height:16px;color:#fff;}
    .bsci-cyan  {background:linear-gradient(135deg,#0e7490,#06b6d4);}
    .bsci-ocean {background:linear-gradient(135deg,#0c4a6e,#0369a1);}
    .bsci-teal  {background:linear-gradient(135deg,#134e4a,#0d9488);}
    .bsc-label{font-size:.6rem;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.8px;margin-bottom:2px;}
    .bsc-val{font-size:.88rem;font-weight:800;color:#fff;line-height:1.2;}
    @media(max-width:600px){.img-banner{padding:22px 18px;}.banner-section-cards{flex-direction:column;}}

    /* ─── SECTION CARD ──────────────────────────────── */
    .section-card{background:#fff;border:1.5px solid var(--border);border-radius:20px;overflow:hidden;margin-bottom:14px;}
    .sc-head{display:flex;align-items:center;gap:16px;padding:18px 24px;border-bottom:1px solid var(--border);background:linear-gradient(to right,#f0fcff,#fff);}
    .sc-icon{width:44px;height:44px;border-radius:12px;flex-shrink:0;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 14px rgba(0,0,0,.18);}
    .sc-icon svg{width:20px;height:20px;color:#fff;}
    .si-cyan {background:linear-gradient(135deg,#0e7490,#06b6d4);}
    .si-ocean{background:linear-gradient(135deg,#0c4a6e,#0369a1);}
    .sc-head-text{flex:1;min-width:0;}
    .sc-head-text h3{font-size:.95rem;font-weight:700;color:var(--text);}
    .sc-head-text p{font-size:.75rem;color:var(--text-lt);margin-top:2px;}

    /* ─── FOLDER CHIPS ───────────────────────────────── */
    .folder-chips-wrap{display:flex;align-items:center;gap:8px;padding:14px 20px;border-bottom:1px solid var(--border);flex-wrap:wrap;}
    .folder-chip{display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border-radius:20px;font-size:.78rem;font-weight:600;cursor:pointer;border:1.5px solid var(--border);background:#f8faff;color:var(--text-lt);transition:all .18s;}
    .folder-chip:hover{border-color:#7dd3fc;color:#0369a1;background:#ecfeff;}
    .folder-chip.active{background:linear-gradient(135deg,#0e7490,#06b6d4);color:#fff;border-color:transparent;box-shadow:0 3px 10px rgba(6,182,212,.3);}
    .folder-chip-count{font-size:.65rem;font-weight:800;background:rgba(255,255,255,.25);padding:1px 6px;border-radius:8px;}
    .folder-chip:not(.active) .folder-chip-count{background:rgba(0,0,0,.07);color:inherit;}

    /* ─── UPLOAD ZONE ────────────────────────────────── */
    .upload-section{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:12px;flex-wrap:wrap;background:linear-gradient(to right,#f0fcff,#f8fffd);}
    .upload-zone{flex:1;min-width:260px;border:1.5px dashed #a5f3fc;border-radius:14px;padding:18px 20px;display:flex;align-items:center;gap:14px;cursor:pointer;background:#fff;transition:border-color .2s,background .2s;}
    .upload-zone:hover,.upload-zone.drag{border-color:#06b6d4;background:#ecfeff;}
    .upload-zone-icon{width:40px;height:40px;border-radius:11px;background:linear-gradient(135deg,#0e7490,#06b6d4);flex-shrink:0;display:flex;align-items:center;justify-content:center;}
    .upload-zone-icon svg{width:18px;height:18px;color:#fff;}
    .upload-zone-text{flex:1;min-width:0;}
    .upload-zone-text strong{display:block;font-size:.84rem;font-weight:700;color:var(--text);margin-bottom:2px;}
    .upload-zone-text span{font-size:.72rem;color:var(--text-lt);}
    .upload-progress-wrap{width:100%;margin-top:10px;display:none;}
    .upload-progress{height:5px;border-radius:4px;background:#e0f7fa;overflow:hidden;}
    .upload-progress-bar{height:100%;background:linear-gradient(90deg,#0e7490,#06b6d4);width:0%;transition:width .3s;border-radius:4px;}
    .upload-progress-label{font-size:.7rem;color:#0e7490;margin-top:4px;}
    .upload-btn-area{display:flex;flex-direction:column;gap:6px;flex-shrink:0;}

    /* ─── SEARCH / SORT ──────────────────────────────── */
    .grid-toolbar{display:flex;align-items:center;gap:10px;padding:12px 20px;border-bottom:1px solid var(--border);flex-wrap:wrap;}
    .search-wrap{position:relative;flex:1;min-width:180px;}
    .search-wrap svg{position:absolute;left:11px;top:50%;transform:translateY(-50%);width:14px;height:14px;color:#99a3b8;pointer-events:none;}
    .search-input{width:100%;box-sizing:border-box;padding:8px 12px 8px 32px;border:1.5px solid var(--border);border-radius:10px;font-size:.82rem;font-family:inherit;color:var(--text);background:#fafcff;outline:none;transition:border-color .15s,box-shadow .15s;}
    .search-input:focus{border-color:#06b6d4;box-shadow:0 0 0 3px rgba(6,182,212,.1);}
    .sort-select{padding:8px 12px;border:1.5px solid var(--border);border-radius:10px;font-size:.8rem;font-family:inherit;color:var(--text);background:#fafcff;outline:none;cursor:pointer;appearance:none;background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 24 24' fill='none' stroke='%2399a3b8' stroke-width='2' xmlns='http://www.w3.org/2000/svg'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 8px center;background-size:14px;padding-right:28px;transition:border-color .15s;}
    .sort-select:focus{border-color:#06b6d4;}
    .grid-count{font-size:.73rem;color:var(--text-lt);white-space:nowrap;margin-left:auto;}

    /* ─── IMAGE GRID ─────────────────────────────────── */
    .img-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(170px,1fr));gap:14px;padding:18px 20px;}
    @media(max-width:480px){.img-grid{grid-template-columns:repeat(auto-fill,minmax(130px,1fr));padding:12px 14px;gap:10px;}}

    .img-card{border:1.5px solid var(--border);border-radius:14px;overflow:hidden;background:#fff;transition:box-shadow .2s,border-color .2s,transform .18s;position:relative;}
    .img-card:hover{box-shadow:0 6px 22px rgba(0,0,0,.1);border-color:#67e8f9;transform:translateY(-2px);}

    .img-thumb-wrap{width:100%;aspect-ratio:4/3;overflow:hidden;background:#ecfeff;position:relative;cursor:pointer;}
    .img-thumb{width:100%;height:100%;object-fit:cover;display:block;transition:transform .25s;}
    .img-card:hover .img-thumb{transform:scale(1.04);}
    .img-thumb.err{object-fit:contain;padding:20px;opacity:.35;transform:none!important;}

    .img-overlay{position:absolute;inset:0;background:linear-gradient(to top,rgba(0,42,64,.7) 0%,transparent 55%);opacity:0;transition:opacity .2s;display:flex;flex-direction:column;justify-content:flex-end;padding:10px;}
    .img-card:hover .img-overlay{opacity:1;}
    .img-overlay-actions{display:flex;gap:6px;}
    .ioa-btn{flex:1;display:flex;align-items:center;justify-content:center;gap:4px;padding:5px 6px;border-radius:7px;font-size:.67rem;font-weight:700;cursor:pointer;border:none;transition:all .15s;}
    .ioa-btn.copy{background:rgba(255,255,255,.15);color:#fff;backdrop-filter:blur(4px);}
    .ioa-btn.copy:hover{background:rgba(255,255,255,.28);}
    .ioa-btn.preview{background:rgba(255,255,255,.15);color:#fff;backdrop-filter:blur(4px);}
    .ioa-btn.preview:hover{background:rgba(255,255,255,.28);}
    .ioa-btn.del{background:rgba(220,38,38,.75);color:#fff;backdrop-filter:blur(4px);}
    .ioa-btn.del:hover{background:rgba(220,38,38,.95);}
    .ioa-btn svg{width:11px;height:11px;}

    .img-info{padding:8px 10px 10px;}
    .img-name{font-size:.72rem;font-weight:700;color:var(--text);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;margin-bottom:2px;}
    .img-meta{display:flex;align-items:center;justify-content:space-between;}
    .img-size{font-size:.64rem;color:var(--text-lt);}
    .img-ext{font-size:.6rem;font-weight:700;padding:1px 6px;border-radius:5px;background:#e0f7fa;color:#0e7490;text-transform:uppercase;}

    /* ─── EMPTY STATE ────────────────────────────────── */
    .img-empty{grid-column:1/-1;text-align:center;padding:50px 24px;color:var(--text-lt);}
    .img-empty svg{opacity:.15;margin-bottom:12px;}
    .img-empty p{font-size:.9rem;margin-bottom:4px;}
    .img-empty strong{font-size:.82rem;color:var(--text);}

    /* ─── LIGHTBOX ───────────────────────────────────── */
    .lightbox{position:fixed;inset:0;background:rgba(0,10,20,.92);backdrop-filter:blur(8px);z-index:9500;display:none;align-items:center;justify-content:center;padding:20px;}
    .lightbox.open{display:flex;}
    .lightbox-inner{position:relative;max-width:90vw;max-height:88vh;}
    .lightbox-inner img{display:block;max-width:100%;max-height:88vh;border-radius:12px;box-shadow:0 30px 80px rgba(0,0,0,.5);}
    .lightbox-close{position:absolute;top:-14px;right:-14px;width:32px;height:32px;border-radius:50%;background:#fff;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 14px rgba(0,0,0,.3);transition:transform .15s;}
    .lightbox-close:hover{transform:scale(1.1);}
    .lightbox-close svg{width:14px;height:14px;}
    .lightbox-caption{text-align:center;margin-top:12px;font-size:.78rem;color:rgba(255,255,255,.55);}

    /* ─── DELETE CONFIRM MODAL ───────────────────────── */
    .del-modal-backdrop{position:fixed;inset:0;background:rgba(0,10,20,.55);backdrop-filter:blur(4px);z-index:9000;display:none;align-items:center;justify-content:center;}
    .del-modal-backdrop.open{display:flex;}
    .del-modal{background:#fff;border-radius:22px;padding:28px;width:340px;max-width:92vw;box-shadow:0 24px 80px rgba(0,0,0,.3);}
    .del-modal-icon{width:48px;height:48px;border-radius:14px;background:linear-gradient(135deg,#991b1b,#dc2626);display:flex;align-items:center;justify-content:center;margin-bottom:14px;}
    .del-modal-icon svg{width:22px;height:22px;color:#fff;}
    .del-modal h3{font-size:.98rem;font-weight:800;color:var(--text);margin-bottom:6px;}
    .del-modal p{font-size:.82rem;color:var(--text-lt);line-height:1.6;margin-bottom:20px;}
    .del-modal p strong{color:var(--text);font-family:monospace;font-size:.78rem;}
    .del-modal-actions{display:flex;gap:8px;}
    .del-btn{flex:1;padding:10px;border-radius:11px;font-size:.84rem;font-weight:700;cursor:pointer;border:none;transition:all .15s;}
    .del-btn.confirm{background:linear-gradient(135deg,#991b1b,#dc2626);color:#fff;}
    .del-btn.confirm:hover{opacity:.9;}
    .del-btn.cancel{background:#f1f4fa;color:var(--text-lt);}
    .del-btn.cancel:hover{background:#e5eaf5;}
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
      <span class="admin-topbar-title">Gestor de Imágenes</span>
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
        <a class="user-dropdown-item danger" href="../logout.php">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16,17 21,12 16,7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
          Cerrar sesión
        </a>
      </div>
    </div>
  </header>

  <div class="admin-content">

    <!-- ══ BANNER ══════════════════════════════════════ -->
    <div class="img-banner" style="margin-bottom:16px;">
      <div class="img-banner-mesh"></div>
      <div class="banner-inner">
        <div class="banner-chip"><span class="bdot"></span> Gestor de archivos</div>
        <h1 class="banner-title">Imágenes del Sitio</h1>
        <p class="banner-desc">Sube, previsualiza y elimina imágenes organizadas por carpeta. Máximo 5 MB por archivo — JPG, PNG, WebP y GIF.</p>
        <div class="banner-section-cards">
          <div class="bsc">
            <div class="bsc-icon bsci-cyan">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21,15 16,10 5,21"/></svg>
            </div>
            <div class="bsc-info">
              <div class="bsc-label">Total imágenes</div>
              <div class="bsc-val" id="statTotal">—</div>
            </div>
          </div>
          <div class="bsc">
            <div class="bsc-icon bsci-ocean">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/></svg>
            </div>
            <div class="bsc-info">
              <div class="bsc-label">Carpeta actual</div>
              <div class="bsc-val" id="statFolder">—</div>
            </div>
          </div>
          <div class="bsc">
            <div class="bsc-icon bsci-teal">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17,8 12,3 7,8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            </div>
            <div class="bsc-info">
              <div class="bsc-label">Carpetas</div>
              <div class="bsc-val">3 carpetas</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ══ CARD: EXPLORADOR ════════════════════════════ -->
    <div class="section-card">

      <!-- header -->
      <div class="sc-head">
        <div class="sc-icon si-cyan">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21,15 16,10 5,21"/>
          </svg>
        </div>
        <div class="sc-head-text">
          <h3>Explorador de imágenes</h3>
          <p>Arrastra archivos al área de subida o haz clic para seleccionarlos</p>
        </div>
        <button class="btn-admin btn-primary-admin"
          onclick="document.getElementById('uploadInput').click()"
          style="flex-shrink:0;font-size:.8rem;padding:7px 14px;gap:6px;background:linear-gradient(135deg,#0e7490,#06b6d4);border:none;">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17,8 12,3 7,8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
          Subir imágenes
        </button>
        <input type="file" id="uploadInput" accept="image/*" multiple style="display:none" onchange="uploadFiles(this.files)">
      </div>

      <!-- chips de carpeta -->
      <div class="folder-chips-wrap">
        <button class="folder-chip active" data-key="general"   onclick="switchFolder('general',this)">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21,15 16,10 5,21"/></svg>
          General <span class="folder-chip-count" id="cnt-general">…</span>
        </button>
        <button class="folder-chip" data-key="partners"  onclick="switchFolder('partners',this)">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
          Partners <span class="folder-chip-count" id="cnt-partners">…</span>
        </button>
        <button class="folder-chip" data-key="avatares"  onclick="switchFolder('avatares',this)">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          Avatares <span class="folder-chip-count" id="cnt-avatares">…</span>
        </button>
      </div>

      <!-- zona de subida -->
      <div class="upload-section">
        <div class="upload-zone" id="dropZone" onclick="document.getElementById('uploadInput').click()">
          <div class="upload-zone-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17,8 12,3 7,8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
          </div>
          <div class="upload-zone-text">
            <strong>Arrastra imágenes aquí o haz clic</strong>
            <span>JPG, PNG, WebP, GIF · máx 5 MB por archivo</span>
          </div>
        </div>
        <div id="progressWrap" class="upload-progress-wrap">
          <div class="upload-progress"><div class="upload-progress-bar" id="uploadProgressBar"></div></div>
          <div class="upload-progress-label" id="progressLabel">Subiendo…</div>
        </div>
      </div>

      <!-- barra búsqueda / ordenar -->
      <div class="grid-toolbar">
        <div class="search-wrap">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input type="text" class="search-input" id="searchInput" placeholder="Buscar por nombre…" oninput="renderGrid()">
        </div>
        <select class="sort-select" id="sortSelect" onchange="renderGrid()">
          <option value="newest">Más recientes</option>
          <option value="oldest">Más antiguas</option>
          <option value="az">A → Z</option>
          <option value="za">Z → A</option>
          <option value="size">Mayor tamaño</option>
        </select>
        <span class="grid-count" id="gridCount"></span>
      </div>

      <!-- grid de imágenes -->
      <div class="img-grid" id="imgGrid">
        <div class="img-empty">
          <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21,15 16,10 5,21"/></svg>
          <p>Cargando imágenes…</p>
        </div>
      </div>

    </div>

  </div>
</div>

<!-- ══ LIGHTBOX ══════════════════════════════════════ -->
<div class="lightbox" id="lightbox" onclick="closeLightbox()">
  <div class="lightbox-inner" onclick="event.stopPropagation()">
    <img id="lightboxImg" src="" alt="">
    <button class="lightbox-close" onclick="closeLightbox()">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
    <div class="lightbox-caption" id="lightboxCaption"></div>
  </div>
</div>

<!-- ══ DELETE CONFIRM MODAL ══════════════════════════ -->
<div class="del-modal-backdrop" id="delModal">
  <div class="del-modal">
    <div class="del-modal-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3,6 5,6 21,6"/><path d="M19,6l-1,14a2,2,0,0,1-2,2H8a2,2,0,0,1-2-2L5,6"/><path d="M10,11v6"/><path d="M14,11v6"/></svg>
    </div>
    <h3>¿Eliminar imagen?</h3>
    <p>Se eliminará permanentemente del servidor:<br><strong id="delModalPath"></strong></p>
    <div class="del-modal-actions">
      <button class="del-btn cancel" onclick="closeDelModal()">Cancelar</button>
      <button class="del-btn confirm" id="delConfirmBtn" onclick="confirmDelete()">Sí, eliminar</button>
    </div>
  </div>
</div>

<?php $profileApiPath = '../api/profile.php'; $fotoPrefix = '../'; require __DIR__ . '/../includes/profile-modal.php'; ?>

<script src="../assets/js/auth.js?v=2"></script>
<script>
  const CSRF_TOKEN = '<?= $csrfToken ?>';
  AdminSidebar.init('imagenes', '../', '../../');

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

  /* ─── estado ─────────────────────────────────────── */
  let imagesData    = {};
  let currentFolder = 'general';
  let pendingDelete = null; // { path }

  /* ─── helpers ────────────────────────────────────── */
  function fmtSize(bytes) {
    if (!bytes) return '—';
    if (bytes < 1024)    return bytes + ' B';
    if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / 1048576).toFixed(1) + ' MB';
  }

  function extOf(name) {
    return (name || '').split('.').pop().toLowerCase().slice(0, 4);
  }

  function escH(s) {
    return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  /* ─── banner stats ───────────────────────────────── */
  function updateStats() {
    const total = Object.values(imagesData).reduce((a, arr) => a + (arr?.length || 0), 0);
    const cur   = (imagesData[currentFolder] || []).length;
    document.getElementById('statTotal').textContent  = total;
    document.getElementById('statFolder').textContent = cur + ' imagen' + (cur !== 1 ? 'es' : '');
    ['general','partners','avatares'].forEach(k => {
      const cnt = (imagesData[k] || []).length;
      const el  = document.getElementById('cnt-' + k);
      if (el) el.textContent = cnt;
    });
  }

  /* ─── cambiar carpeta ────────────────────────────── */
  function switchFolder(key, btn) {
    currentFolder = key;
    document.querySelectorAll('.folder-chip').forEach(c => c.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('searchInput').value = '';
    document.getElementById('sortSelect').value  = 'newest';
    updateStats();
    renderGrid();
  }

  /* ─── render grid ────────────────────────────────── */
  function renderGrid() {
    const q    = (document.getElementById('searchInput').value || '').toLowerCase();
    const sort = document.getElementById('sortSelect').value;
    let list   = (imagesData[currentFolder] || []).filter(img => !q || img.name.toLowerCase().includes(q));

    if      (sort === 'oldest') list = [...list].reverse();
    else if (sort === 'az')     list = [...list].sort((a,b) => a.name.localeCompare(b.name));
    else if (sort === 'za')     list = [...list].sort((a,b) => b.name.localeCompare(a.name));
    else if (sort === 'size')   list = [...list].sort((a,b) => (b.size||0) - (a.size||0));

    document.getElementById('gridCount').textContent =
      list.length === (imagesData[currentFolder]||[]).length
        ? `${list.length} imagen${list.length !== 1 ? 'es' : ''}`
        : `${list.length} de ${(imagesData[currentFolder]||[]).length}`;

    const grid = document.getElementById('imgGrid');

    if (!list.length) {
      grid.innerHTML = `<div class="img-empty">
        <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21,15 16,10 5,21"/></svg>
        <p>${q ? 'Sin resultados para "' + escH(q) + '"' : 'Sin imágenes en esta carpeta'}</p>
        ${!q ? '<strong>Arrastra archivos o usa el botón Subir imágenes</strong>' : ''}
      </div>`;
      return;
    }

    grid.innerHTML = list.map(img => {
      const ext  = extOf(img.name);
      const path = img.path || '';
      const safe = path.replace(/'/g, "\\'");
      return `
        <div class="img-card">
          <div class="img-thumb-wrap" onclick="openLightbox('${escH(path)}','${escH(img.name)}')">
            <img class="img-thumb" src="${escH(path)}?t=${Date.now()}"
              alt="${escH(img.name)}" loading="lazy"
              onerror="this.classList.add('err')">
            <div class="img-overlay">
              <div class="img-overlay-actions">
                <button class="ioa-btn preview" onclick="event.stopPropagation();openLightbox('${escH(path)}','${escH(img.name)}')" title="Ver imagen">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                  Ver
                </button>
                <button class="ioa-btn copy" onclick="event.stopPropagation();copyPath('${escH(path)}')" title="Copiar ruta">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
                  Ruta
                </button>
                <button class="ioa-btn del" onclick="event.stopPropagation();openDelModal('${safe}')" title="Eliminar">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3,6 5,6 21,6"/><path d="M19,6l-1,14a2,2,0,0,1-2,2H8a2,2,0,0,1-2-2L5,6"/></svg>
                </button>
              </div>
            </div>
          </div>
          <div class="img-info">
            <div class="img-name" title="${escH(img.name)}">${escH(img.name)}</div>
            <div class="img-meta">
              <span class="img-size">${fmtSize(img.size)}</span>
              <span class="img-ext">${ext}</span>
            </div>
          </div>
        </div>`;
    }).join('');
  }

  /* ─── cargar imágenes ────────────────────────────── */
  async function loadImages() {
    try {
      const res  = await fetch('../api/imagenes.php?action=list', { headers: { 'X-CSRF-Token': CSRF_TOKEN } });
      const json = await res.json();
      if (!json.ok) { showToast('Error al cargar imágenes', 'error'); return; }
      imagesData = json.data || {};
      updateStats();
      renderGrid();
    } catch { showToast('Error de conexión', 'error'); }
  }

  /* ─── lightbox ───────────────────────────────────── */
  function openLightbox(path, name) {
    document.getElementById('lightboxImg').src     = path;
    document.getElementById('lightboxCaption').textContent = name;
    document.getElementById('lightbox').classList.add('open');
  }
  function closeLightbox() { document.getElementById('lightbox').classList.remove('open'); }
  document.addEventListener('keydown', e => { if (e.key === 'Escape') { closeLightbox(); closeDelModal(); } });

  /* ─── copiar ruta ────────────────────────────────── */
  async function copyPath(path) {
    try {
      await navigator.clipboard.writeText(path);
      showToast('Ruta copiada al portapapeles');
    } catch {
      showToast('No se pudo copiar', 'error');
    }
  }

  /* ─── modal eliminar ─────────────────────────────── */
  function openDelModal(path) {
    pendingDelete = path;
    document.getElementById('delModalPath').textContent = path;
    document.getElementById('delModal').classList.add('open');
  }
  function closeDelModal() {
    document.getElementById('delModal').classList.remove('open');
    pendingDelete = null;
  }

  async function confirmDelete() {
    if (!pendingDelete) return;
    const path = pendingDelete;
    closeDelModal();

    const fd = new FormData();
    fd.append('action', 'delete');
    fd.append('path', path);
    try {
      const res  = await fetch('../api/imagenes.php', { method: 'POST', headers: { 'X-CSRF-Token': CSRF_TOKEN }, body: fd });
      const json = await res.json();
      if (json.ok) {
        showToast('Imagen eliminada');
        const arr = imagesData[currentFolder];
        if (arr) {
          const pos = arr.findIndex(img => img.path === path);
          if (pos > -1) arr.splice(pos, 1);
        }
        updateStats();
        renderGrid();
      } else {
        showToast(json.error || 'Error al eliminar', 'error');
      }
    } catch {
      showToast('Error de conexión', 'error');
    }
  }

  /* ─── subir imágenes ─────────────────────────────── */
  async function uploadFiles(files) {
    if (!files || !files.length) return;
    const wrap  = document.getElementById('progressWrap');
    const bar   = document.getElementById('uploadProgressBar');
    const label = document.getElementById('progressLabel');
    wrap.style.display = 'block';

    const folderMap  = { general: 'general', partners: 'partners', avatares: 'avatares' };
    const folderName = folderMap[currentFolder] || 'general';
    let done = 0;
    const total = files.length;

    for (const file of files) {
      if (file.size > 5 * 1024 * 1024) {
        showToast(`${file.name}: máximo 5 MB`, 'error');
        done++; continue;
      }
      label.textContent = `Subiendo ${done + 1} de ${total}: ${file.name}`;
      const fd = new FormData();
      fd.append('image', file);
      fd.append('folder', folderName);
      try {
        const res  = await fetch('../api/contenido.php', { method: 'POST', headers: { 'X-CSRF-Token': CSRF_TOKEN }, body: fd });
        const json = await res.json();
        if (json.ok) {
          if (!imagesData[currentFolder]) imagesData[currentFolder] = [];
          imagesData[currentFolder].unshift({ name: file.name, path: json.path, size: file.size });
        } else {
          showToast(json.error || `Error: ${file.name}`, 'error');
        }
      } catch { showToast('Error de conexión', 'error'); }
      done++;
      bar.style.width = Math.round((done / total) * 100) + '%';
    }

    label.textContent = `${total} imagen${total !== 1 ? 'es' : ''} procesada${total !== 1 ? 's' : ''}`;
    setTimeout(() => { wrap.style.display = 'none'; bar.style.width = '0%'; }, 1200);
    document.getElementById('uploadInput').value = '';
    updateStats();
    renderGrid();
    if (total === done) showToast(total === 1 ? 'Imagen subida correctamente' : `${total} imágenes subidas`);
  }

  /* ─── drag-and-drop ──────────────────────────────── */
  const dz = document.getElementById('dropZone');
  dz.addEventListener('dragover',  e => { e.preventDefault(); dz.classList.add('drag'); });
  dz.addEventListener('dragleave', () => dz.classList.remove('drag'));
  dz.addEventListener('drop', e => {
    e.preventDefault();
    dz.classList.remove('drag');
    uploadFiles(e.dataTransfer.files);
  });

  /* ─── arrancar ───────────────────────────────────── */
  loadImages();
</script>

</body>
</html>
