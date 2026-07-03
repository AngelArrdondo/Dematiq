<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/conexion.php';
require_once __DIR__ . '/../../includes/contenido.php';
$user      = Auth::require('/pages/corporativo/login.php');
$content   = Contenido::getAll();
$d         = $content['nosotros'] ?? [];
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
  <title>Nosotros | DEMATIQ Admin</title>
  <link rel="stylesheet" href="../assets/css/admin.css?v=7">
  <link rel="icon" type="image/svg+xml" href="../../assets/images/logos/favicon-d.svg">
  <style>

    /* ─── BANNER ─────────────────────────────────────── */
    .nos-banner {
      background: linear-gradient(135deg,#071f3a 0%,#0a3048 45%,#0e6b82 100%);
      border-radius: 20px;
      padding: 30px 32px;
      margin-bottom: 0;
      position: relative;
      overflow: hidden;
    }
    .nos-banner::before {
      content:'';position:absolute;
      width:500px;height:500px;border-radius:50%;
      background:radial-gradient(circle,rgba(14,116,144,.25) 0%,transparent 65%);
      top:-200px;right:-60px;pointer-events:none;
    }
    .nos-banner::after {
      content:'';position:absolute;
      width:200px;height:200px;border-radius:50%;
      background:radial-gradient(circle,rgba(52,211,153,.07) 0%,transparent 70%);
      bottom:-80px;left:35%;pointer-events:none;
    }
    /* decorative mesh dots */
    .nos-banner-mesh {
      position:absolute;inset:0;pointer-events:none;overflow:hidden;
      background-image:radial-gradient(rgba(255,255,255,.04) 1px, transparent 1px);
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
    .bsc {
      display:flex;align-items:center;gap:10px;
      background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.12);
      padding:10px 16px;border-radius:14px;flex:1;min-width:140px;
      transition:background .2s;
    }
    .bsc:hover{background:rgba(255,255,255,.12);}
    .bsc-icon{
      width:34px;height:34px;border-radius:10px;flex-shrink:0;
      display:flex;align-items:center;justify-content:center;
    }
    .bsc-icon svg{width:16px;height:16px;color:#fff;}
    .bsci-teal   {background:linear-gradient(135deg,#0e7490,#06b6d4);}
    .bsci-indigo {background:linear-gradient(135deg,#1a4a9e,#4f6fc2);}
    .bsci-green  {background:linear-gradient(135deg,#065f46,#10b981);}
    .bsc-info{}
    .bsc-label{font-size:.6rem;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.8px;margin-bottom:2px;}
    .bsc-val  {font-size:.82rem;font-weight:700;color:#fff;line-height:1.2;max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
    @media(max-width:600px){.nos-banner{padding:22px 18px;}.banner-section-cards{flex-direction:column;}}

    /* ─── UNSAVED NOTICE (top, animated) ────────────── */
    .unsaved-notice {
      display:flex;align-items:center;gap:12px;
      background:linear-gradient(90deg,#fffbeb,#fef3c7);
      border:1.5px solid #fcd34d;border-radius:14px;
      padding:11px 16px;margin-bottom:16px;
      animation:slideIn .35s ease;
    }
    .unsaved-notice.hidden{display:none;}
    @keyframes slideIn{from{opacity:0;transform:translateY(-10px);}to{opacity:1;transform:translateY(0);}}
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

    /* ─── CARDS ──────────────────────────────────────── */
    .section-card {
      background:#fff;border:1.5px solid var(--border);border-radius:20px;
      overflow:hidden;margin-bottom:14px;
      transition:box-shadow .2s,border-color .2s;
    }
    .section-card:focus-within{
      border-color:var(--accent-lt);
      box-shadow:0 0 0 3px rgba(46,107,207,.07),0 4px 24px rgba(0,0,0,.07);
    }
    .sc-head {
      display:flex;align-items:center;gap:16px;
      padding:18px 24px;
      border-bottom:1px solid var(--border);
      background:linear-gradient(to right,#f8faff,#fff);
    }
    .sc-icon{width:44px;height:44px;border-radius:12px;flex-shrink:0;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 14px rgba(0,0,0,.18);}
    .sc-icon svg{width:20px;height:20px;color:#fff;}
    .si-teal  {background:linear-gradient(135deg,#0e7490,#06b6d4);}
    .si-indigo{background:linear-gradient(135deg,#1a4a9e,#4f6fc2);}
    .si-green {background:linear-gradient(135deg,#065f46,#10b981);}
    .sc-head-text h3{font-size:.95rem;font-weight:700;color:var(--text);}
    .sc-head-text p{font-size:.75rem;color:var(--text-lt);margin-top:2px;}
    .sc-body{padding:24px;}

    /* hero split */
    .hero-split{display:grid;grid-template-columns:1fr 1fr;gap:28px;align-items:start;}
    @media(max-width:720px){.hero-split{grid-template-columns:1fr;}}

    /* fields */
    .field{margin-bottom:18px;}
    .field:last-child{margin-bottom:0;}
    .field-top{display:flex;align-items:baseline;justify-content:space-between;margin-bottom:7px;}
    .field-top label{font-size:.71rem;font-weight:700;color:var(--text-lt);text-transform:uppercase;letter-spacing:.5px;}
    .field-cnt{font-size:.65rem;color:#b0b8cc;font-variant-numeric:tabular-nums;}
    .field-hint{font-size:.65rem;color:#aab;font-style:italic;margin-top:4px;}
    input.fi,textarea.fi{
      width:100%;box-sizing:border-box;padding:10px 13px;
      border:1.5px solid var(--border);border-radius:10px;
      font-size:.87rem;font-family:inherit;color:var(--text);
      background:#fafcff;outline:none;
      transition:border-color .15s,box-shadow .15s,background .15s;
    }
    input.fi:focus,textarea.fi:focus{
      border-color:var(--accent-lt);background:#fff;
      box-shadow:0 0 0 3px rgba(46,107,207,.1);
    }
    textarea.fi{resize:vertical;line-height:1.6;}
    .tag-wrap{position:relative;}
    .tag-hash{position:absolute;left:12px;top:50%;transform:translateY(-50%);font-size:.75rem;font-weight:800;color:#0e7490;pointer-events:none;}
    input.fi.tag-fi{padding-left:26px;font-weight:700;letter-spacing:.4px;}

    /* live preview (hero right col) */
    .lp-box{
      background:linear-gradient(160deg,#f0f9ff,#f5fafe);
      border:1.5px solid #b2e8f5;border-radius:14px;padding:18px 20px;
      position:sticky;top:90px;
    }
    .lp-hd{display:flex;align-items:center;gap:5px;font-size:.62rem;font-weight:700;color:#0e7490;text-transform:uppercase;letter-spacing:1px;margin-bottom:14px;}
    .lp-hd svg{width:11px;height:11px;}
    .lp-tag{font-size:.6rem;font-weight:800;letter-spacing:2px;color:#0e7490;text-transform:uppercase;margin-bottom:4px;}
    .lp-h1{font-size:1rem;font-weight:800;color:#071f3a;line-height:1.2;margin-bottom:8px;}
    .lp-rule{width:24px;height:3px;border-radius:2px;background:linear-gradient(90deg,#0e7490,#06b6d4);margin-bottom:8px;}
    .lp-sub{font-size:.72rem;color:#4a6070;line-height:1.6;}

    /* párrafos */
    .para-item{border:1.5px solid var(--border);border-radius:14px;overflow:hidden;margin-bottom:14px;}
    .para-item:last-child{margin-bottom:0;}
    .para-head{display:flex;align-items:center;gap:10px;padding:10px 16px;background:#f7f9ff;border-bottom:1px solid var(--border);}
    .para-badge{width:26px;height:26px;border-radius:8px;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:800;color:#fff;}
    .pb-1{background:linear-gradient(135deg,#1a4a9e,#4f6fc2);}
    .pb-2{background:linear-gradient(135deg,#0e7490,#06b6d4);}
    .para-head span{font-size:.78rem;font-weight:700;color:var(--text);}
    .para-head .field-cnt{margin-left:auto;}
    .para-item textarea{
      display:block;width:100%;box-sizing:border-box;padding:14px 16px;
      border:none;outline:none;resize:vertical;min-height:100px;
      font-size:.87rem;font-family:inherit;color:var(--text);background:#fff;line-height:1.6;
    }
    .para-item textarea:focus{background:#fafcff;}

    /* filosofía */
    .filo-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:20px;}
    @media(max-width:760px){.filo-grid{grid-template-columns:1fr;}}
    .filo-card{border-radius:16px;overflow:hidden;border:1.5px solid var(--border);transition:border-color .2s,box-shadow .2s;}
    .filo-card:focus-within{border-color:var(--accent-lt);box-shadow:0 0 0 3px rgba(46,107,207,.08);}
    .fc-accent{height:3px;}
    .fc-accent.m{background:linear-gradient(90deg,#1a4a9e,#4f6fc2);}
    .fc-accent.v{background:linear-gradient(90deg,#0e7490,#06b6d4);}
    .fc-accent.val{background:linear-gradient(90deg,#065f46,#10b981);}
    .fc-head{display:flex;align-items:center;gap:10px;padding:12px 16px 10px;background:#fff;border-bottom:1px solid var(--border);}
    .fc-icon{width:32px;height:32px;border-radius:9px;flex-shrink:0;display:flex;align-items:center;justify-content:center;}
    .fc-icon svg{width:14px;height:14px;color:#fff;}
    .fci-m{background:linear-gradient(135deg,#1a4a9e,#4f6fc2);}
    .fci-v{background:linear-gradient(135deg,#0e7490,#06b6d4);}
    .fci-val{background:linear-gradient(135deg,#065f46,#10b981);}
    .fc-head-text h4{font-size:.82rem;font-weight:700;color:var(--text);}
    .fc-head-text p{font-size:.64rem;color:var(--text-lt);margin-top:1px;}
    .filo-card textarea{
      display:block;width:100%;box-sizing:border-box;padding:14px 16px;
      border:none;outline:none;resize:vertical;min-height:110px;
      font-size:.83rem;font-family:inherit;color:var(--text);background:#fff;line-height:1.6;
    }
    .filo-card textarea:focus{background:#fafcff;}
    .filo-preview{background:linear-gradient(135deg,#f0f9ff,#f7fffe);border:1.5px solid #c5eef7;border-radius:14px;padding:18px 20px;}
    .fp-hd{display:flex;align-items:center;gap:6px;font-size:.62rem;font-weight:700;color:#0e7490;text-transform:uppercase;letter-spacing:1px;margin-bottom:14px;}
    .fp-hd svg{width:11px;height:11px;}
    .fp-cards{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;}
    @media(max-width:760px){.fp-cards{grid-template-columns:1fr;}}
    .fpc{background:#fff;border-radius:12px;border-left:3px solid;padding:12px 14px;box-shadow:0 2px 8px rgba(0,0,0,.05);}
    .fpc.m{border-color:#1a4a9e;}.fpc.v{border-color:#0e7490;}.fpc.val{border-color:#10b981;}
    .fpc-lbl{font-size:.6rem;font-weight:800;text-transform:uppercase;letter-spacing:.8px;margin-bottom:6px;}
    .fpc.m .fpc-lbl{color:#1a4a9e;}.fpc.v .fpc-lbl{color:#0e7490;}.fpc.val .fpc-lbl{color:#065f46;}
    .fpc-txt{font-size:.72rem;color:#4a6080;line-height:1.55;}

    /* ─── SAVE BAR ───────────────────────────────────── */
    .save-bar-sticky{
      position:sticky;bottom:0;
      background:rgba(240,244,255,.92);backdrop-filter:blur(12px);
      border-top:1px solid rgba(221,229,245,.9);
      padding:12px 24px;
      display:flex;align-items:center;gap:10px;flex-wrap:wrap;
      z-index:50;margin:8px -24px 0;
    }
    .save-spacer{flex:1;}

    /* ─── BLUR-SAVE PROMPT ───────────────────────────── */
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
    .bp-yes{
      flex:1;background:linear-gradient(135deg,#0e7490,#06b6d4);color:#fff;
      border:none;cursor:pointer;font-size:.78rem;font-weight:700;
      padding:8px 0;border-radius:9px;transition:opacity .15s;
    }
    .bp-yes:hover{opacity:.88;}
    .bp-no{
      flex:1;background:#f1f4fa;color:var(--text-lt);
      border:none;cursor:pointer;font-size:.78rem;font-weight:600;
      padding:8px 0;border-radius:9px;transition:background .15s;
    }
    .bp-no:hover{background:#e5eaf5;}
    .bp-bar{height:3px;border-radius:2px;background:#f59e0b;margin-top:12px;transform-origin:left;animation:none;}
    .bp-bar.ticking{animation:bpTick 6s linear forwards;}
    @keyframes bpTick{from{transform:scaleX(1);}to{transform:scaleX(0);}}

    /* ─── NAV-AWAY MODAL ─────────────────────────────── */
    .nav-modal-backdrop{
      position:fixed;inset:0;background:rgba(7,31,58,.55);backdrop-filter:blur(4px);
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
    .nm-btn{
      display:flex;align-items:center;justify-content:center;gap:8px;
      padding:11px 16px;border-radius:12px;font-size:.85rem;font-weight:700;
      cursor:pointer;border:none;transition:all .15s;
    }
    .nm-btn.primary{background:linear-gradient(135deg,#0e7490,#06b6d4);color:#fff;}
    .nm-btn.primary:hover{opacity:.9;}
    .nm-btn.danger{background:#fff1f0;color:#dc2626;border:1.5px solid #fecaca;}
    .nm-btn.danger:hover{background:#fee2e2;}
    .nm-btn.cancel{background:#f1f4fa;color:var(--text-lt);}
    .nm-btn.cancel:hover{background:#e5eaf5;}

    @media(max-width:600px){.sc-body{padding:16px 18px;}.blur-prompt{right:12px;bottom:70px;width:230px;}}
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
      <span class="admin-topbar-title">Nosotros</span>
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
    <div class="nos-banner" style="margin-bottom:16px;">
      <div class="nos-banner-mesh"></div>
      <div class="banner-inner">
        <div class="banner-chip"><span class="bdot"></span> Página activa</div>
        <h1 class="banner-title">Sobre Nosotros</h1>
        <p class="banner-desc">Controla el encabezado, la presentación de la empresa y los textos de filosofía que aparecen en la página pública.</p>
        <div class="banner-section-cards">
          <div class="bsc">
            <div class="bsc-icon bsci-teal">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
            </div>
            <div class="bsc-info">
              <div class="bsc-label">Etiqueta</div>
              <div class="bsc-val" id="statTag"><?= htmlspecialchars($d['hero']['tag'] ?? 'Conócenos') ?></div>
            </div>
          </div>
          <div class="bsc">
            <div class="bsc-icon bsci-indigo">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16M4 12h8M4 18h12"/></svg>
            </div>
            <div class="bsc-info">
              <div class="bsc-label">Título H1</div>
              <div class="bsc-val" id="statH1"><?= htmlspecialchars($d['hero']['h1'] ?? 'Sobre Nosotros') ?></div>
            </div>
          </div>
          <div class="bsc">
            <div class="bsc-icon bsci-green">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26 12,2"/></svg>
            </div>
            <div class="bsc-info">
              <div class="bsc-label">Secciones</div>
              <div class="bsc-val">Hero · Quiénes · Filosofía</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ══ UNSAVED NOTICE (arriba, animado) ═══════════ -->
    <div class="unsaved-notice hidden" id="unsavedNotice">
      <div class="un-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
      </div>
      <div class="un-text">
        <strong>Tienes cambios sin guardar</strong>
        <span>Guarda para que se reflejen en la página pública</span>
      </div>
      <button class="un-save" onclick="saveNosotros()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17,21 17,13 7,13 7,21"/><polyline points="7,3 7,8 15,8"/></svg>
        Guardar ahora
      </button>
    </div>

    <!-- ══ CARD 1: HERO ═════════════════════════════════ -->
    <div class="section-card">
      <div class="sc-head">
        <div class="sc-icon si-teal">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/>
          </svg>
        </div>
        <div class="sc-head-text">
          <h3>Hero de la página</h3>
          <p>Encabezado principal al entrar a Nosotros</p>
        </div>
      </div>
      <div class="sc-body">
        <div class="hero-split">
          <div>
            <div class="field">
              <div class="field-top">
                <label>Etiqueta (tag)</label>
                <span class="field-cnt" id="cntTag">0</span>
              </div>
              <div class="tag-wrap">
                <span class="tag-hash">#</span>
                <input type="text" id="hero-tag" class="fi tag-fi"
                  value="<?= htmlspecialchars($d['hero']['tag'] ?? '') ?>"
                  placeholder="Conócenos" maxlength="40"
                  oninput="onHeroInput()" onblur="onFieldBlur()">
              </div>
              <p class="field-hint">Aparece encima del título en la página pública</p>
            </div>
            <div class="field">
              <div class="field-top">
                <label>Título principal (H1)</label>
                <span class="field-cnt" id="cntH1">0</span>
              </div>
              <input type="text" id="hero-h1" class="fi"
                value="<?= htmlspecialchars($d['hero']['h1'] ?? '') ?>"
                placeholder="Sobre Nosotros" maxlength="80"
                oninput="onHeroInput()" onblur="onFieldBlur()">
            </div>
            <div class="field">
              <div class="field-top">
                <label>Subtítulo / descripción</label>
                <span class="field-cnt" id="cntSub">0</span>
              </div>
              <textarea id="hero-subtitle" class="fi" rows="3" maxlength="300"
                placeholder="Empresa mexicana especializada en automatización y ensamble industrial…"
                oninput="onHeroInput()" onblur="onFieldBlur()"><?= htmlspecialchars($d['hero']['subtitle'] ?? '') ?></textarea>
            </div>
          </div>
          <!-- live preview sticky -->
          <div>
            <div class="lp-box">
              <div class="lp-hd">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                Preview en vivo
              </div>
              <div class="lp-tag" id="prevTag"><?= htmlspecialchars($d['hero']['tag'] ?? 'Conócenos') ?></div>
              <div class="lp-h1"  id="prevH1"><?= htmlspecialchars($d['hero']['h1'] ?? 'Sobre Nosotros') ?></div>
              <div class="lp-rule"></div>
              <div class="lp-sub" id="prevSub"><?= htmlspecialchars($d['hero']['subtitle'] ?? '') ?></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ══ CARD 2: QUIÉNES SOMOS ════════════════════════ -->
    <div class="section-card">
      <div class="sc-head">
        <div class="sc-icon si-indigo">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/>
            <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
          </svg>
        </div>
        <div class="sc-head-text">
          <h3>¿Quiénes Somos?</h3>
          <p>Dos párrafos de presentación de la empresa</p>
        </div>
      </div>
      <div class="sc-body">
        <div class="para-item">
          <div class="para-head">
            <div class="para-badge pb-1">1</div>
            <span>Párrafo principal</span>
            <span class="field-cnt" id="cntP1">0 car.</span>
          </div>
          <textarea id="qs-p1" maxlength="600"
            placeholder="En DEMATIQ somos una empresa especializada en soluciones tecnológicas e industriales…"
            oninput="checkDirty(); cnt('qs-p1','cntP1')" onblur="onFieldBlur()"><?= htmlspecialchars($d['p1'] ?? '') ?></textarea>
        </div>
        <div class="para-item">
          <div class="para-head">
            <div class="para-badge pb-2">2</div>
            <span>Párrafo complementario</span>
            <span class="field-cnt" id="cntP2">0 car.</span>
          </div>
          <textarea id="qs-p2" maxlength="600"
            placeholder="Trabajamos de la mano con nuestros clientes y socios estratégicos…"
            oninput="checkDirty(); cnt('qs-p2','cntP2')" onblur="onFieldBlur()"><?= htmlspecialchars($d['p2'] ?? '') ?></textarea>
        </div>
      </div>
    </div>

    <!-- ══ CARD 3: FILOSOFÍA ════════════════════════════ -->
    <div class="section-card">
      <div class="sc-head">
        <div class="sc-icon si-green">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26 12,2"/>
          </svg>
        </div>
        <div class="sc-head-text">
          <h3>Nuestra Filosofía</h3>
          <p>Misión, visión y valores — aparecen como tarjetas en la página</p>
        </div>
      </div>
      <div class="sc-body">
        <div class="filo-grid">
          <div class="filo-card">
            <div class="fc-accent m"></div>
            <div class="fc-head">
              <div class="fc-icon fci-m">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="4"/></svg>
              </div>
              <div class="fc-head-text"><h4>Misión</h4><p>¿Para qué existimos?</p></div>
            </div>
            <textarea id="mision" maxlength="400"
              placeholder="Brindar soluciones integrales en automatización…"
              oninput="checkDirty(); updateFilo()" onblur="onFieldBlur()"><?= htmlspecialchars($d['mision'] ?? '') ?></textarea>
          </div>
          <div class="filo-card">
            <div class="fc-accent v"></div>
            <div class="fc-head">
              <div class="fc-icon fci-v">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              </div>
              <div class="fc-head-text"><h4>Visión</h4><p>¿A dónde vamos?</p></div>
            </div>
            <textarea id="vision" maxlength="400"
              placeholder="Ser líderes en innovación tecnológica…"
              oninput="checkDirty(); updateFilo()" onblur="onFieldBlur()"><?= htmlspecialchars($d['vision'] ?? '') ?></textarea>
          </div>
          <div class="filo-card">
            <div class="fc-accent val"></div>
            <div class="fc-head">
              <div class="fc-icon fci-val">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9,11 12,14 22,4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
              </div>
              <div class="fc-head-text"><h4>Valores</h4><p>¿Cómo lo hacemos?</p></div>
            </div>
            <textarea id="valores" maxlength="400"
              placeholder="Compromiso, innovación, calidad…"
              oninput="checkDirty(); updateFilo()" onblur="onFieldBlur()"><?= htmlspecialchars($d['valores'] ?? '') ?></textarea>
          </div>
        </div>
        <div class="filo-preview">
          <div class="fp-hd">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            Así se verá en la página
          </div>
          <div class="fp-cards">
            <div class="fpc m"><div class="fpc-lbl">Misión</div><div class="fpc-txt" id="prevMision"><?= htmlspecialchars($d['mision'] ?? '') ?></div></div>
            <div class="fpc v"><div class="fpc-lbl">Visión</div><div class="fpc-txt" id="prevVision"><?= htmlspecialchars($d['vision'] ?? '') ?></div></div>
            <div class="fpc val"><div class="fpc-lbl">Valores</div><div class="fpc-txt" id="prevValores"><?= htmlspecialchars($d['valores'] ?? '') ?></div></div>
          </div>
        </div>
      </div>
    </div>

    <!-- ══ SAVE BAR ═════════════════════════════════════ -->
    <div class="save-bar-sticky">
      <div class="save-spacer"></div>
      <a href="/pages/corporativo/nosotros.html" target="_blank" class="btn-admin btn-outline-admin">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
        Ver página
      </a>
      <button class="btn-admin btn-outline-admin" onclick="cancelNosotros()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        Descartar
      </button>
      <button class="btn-admin btn-primary-admin" onclick="saveNosotros()">
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
    <p class="nm-desc">Tienes cambios sin guardar en la página <strong>Nosotros</strong>. Si sales ahora se perderán.</p>
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
  AdminSidebar.init('nosotros', '../', '../../');

  /* user menu */
  const userMenuBtn = document.getElementById('userMenuBtn');
  userMenuBtn.addEventListener('click', function(e){
    e.stopPropagation();
    const open = this.classList.toggle('open');
    this.setAttribute('aria-expanded', open);
  });
  userMenuBtn.addEventListener('keydown', function(e){
    if(e.key==='Enter'||e.key===' '){e.preventDefault();this.click();}
    if(e.key==='Escape')this.classList.remove('open');
  });
  document.addEventListener('click', ()=>userMenuBtn.classList.remove('open'));

  /* ─── original snapshot ─────────────────────────── */
  const orig = {
    tag:      document.getElementById('hero-tag').value,
    h1:       document.getElementById('hero-h1').value,
    subtitle: document.getElementById('hero-subtitle').value,
    p1:       document.getElementById('qs-p1').value,
    p2:       document.getElementById('qs-p2').value,
    mision:   document.getElementById('mision').value,
    vision:   document.getElementById('vision').value,
    valores:  document.getElementById('valores').value,
  };
  let dirty = false;

  /* ─── dirty state ────────────────────────────────── */
  function getValues() {
    return {
      tag:      document.getElementById('hero-tag').value,
      h1:       document.getElementById('hero-h1').value,
      subtitle: document.getElementById('hero-subtitle').value,
      p1:       document.getElementById('qs-p1').value,
      p2:       document.getElementById('qs-p2').value,
      mision:   document.getElementById('mision').value,
      vision:   document.getElementById('vision').value,
      valores:  document.getElementById('valores').value,
    };
  }
  function checkDirty() {
    const cur = getValues();
    const changed = Object.keys(orig).some(k => cur[k] !== orig[k]);
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

  /* ─── char counters ──────────────────────────────── */
  function cnt(id, cntId) {
    const el = document.getElementById(id);
    const ct = document.getElementById(cntId);
    if(el && ct) ct.textContent = el.value.length + ' car.';
  }

  /* ─── hero live preview ──────────────────────────── */
  function onHeroInput() {
    checkDirty();
    const tag = document.getElementById('hero-tag').value;
    const h1  = document.getElementById('hero-h1').value;
    const sub = document.getElementById('hero-subtitle').value;
    document.getElementById('prevTag').textContent = tag || 'Conócenos';
    document.getElementById('prevH1').textContent  = h1  || 'Sobre Nosotros';
    document.getElementById('prevSub').textContent = sub;
    document.getElementById('statTag').textContent = tag || '—';
    document.getElementById('statH1').textContent  = h1  || '—';
    cnt('hero-tag','cntTag'); cnt('hero-h1','cntH1'); cnt('hero-subtitle','cntSub');
  }
  function updateFilo() {
    document.getElementById('prevMision').textContent  = document.getElementById('mision').value;
    document.getElementById('prevVision').textContent  = document.getElementById('vision').value;
    document.getElementById('prevValores').textContent = document.getElementById('valores').value;
  }

  /* ─── init ───────────────────────────────────────── */
  (function(){
    cnt('hero-tag','cntTag'); cnt('hero-h1','cntH1');
    cnt('hero-subtitle','cntSub');
    cnt('qs-p1','cntP1'); cnt('qs-p2','cntP2');
  })();

  /* ─── BLUR-SAVE PROMPT ───────────────────────────── */
  let blurTimer = null;
  let bpAutoTimer = null;

  function onFieldBlur() {
    if (!dirty) return;
    clearTimeout(blurTimer);
    blurTimer = setTimeout(() => {
      if (dirty) showBlurPrompt();
    }, 1400); /* 1.4s — si el usuario salta entre campos no aparece */
  }

  /* cancelar si el usuario foca otro input antes del timeout */
  document.addEventListener('focusin', function(e) {
    if (e.target.matches('input,textarea')) clearTimeout(blurTimer);
  });

  function showBlurPrompt() {
    const el = document.getElementById('blurPrompt');
    const bar = document.getElementById('bpBar');
    el.classList.add('show');
    bar.classList.remove('ticking');
    void bar.offsetWidth; /* reiniciar animación */
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
    await saveNosotros();
  }

  /* ─── CANCEL / SAVE ──────────────────────────────── */
  function cancelNosotros() {
    document.getElementById('hero-tag').value      = orig.tag;
    document.getElementById('hero-h1').value       = orig.h1;
    document.getElementById('hero-subtitle').value = orig.subtitle;
    document.getElementById('qs-p1').value         = orig.p1;
    document.getElementById('qs-p2').value         = orig.p2;
    document.getElementById('mision').value        = orig.mision;
    document.getElementById('vision').value        = orig.vision;
    document.getElementById('valores').value       = orig.valores;
    onHeroInput(); updateFilo();
    cnt('qs-p1','cntP1'); cnt('qs-p2','cntP2');
    clearDirty();
    showToast('Cambios descartados');
  }

  function viewPublic(url) { window.open(url + '?v=' + Date.now(), 'dematiq_public'); }

  async function saveNosotros() {
    try {
      const res = await CM.set('nosotros', {
        hero: {
          tag:      document.getElementById('hero-tag').value.trim(),
          h1:       document.getElementById('hero-h1').value.trim(),
          subtitle: document.getElementById('hero-subtitle').value.trim()
        },
        p1:      document.getElementById('qs-p1').value.trim(),
        p2:      document.getElementById('qs-p2').value.trim(),
        mision:  document.getElementById('mision').value.trim(),
        vision:  document.getElementById('vision').value.trim(),
        valores: document.getElementById('valores').value.trim()
      });
      if (res?.ok) {
        /* actualizar snapshot para que los valores guardados sean el nuevo "original" */
        Object.assign(orig, getValues());
        clearDirty();
        showToast('Cambios guardados correctamente');
        viewPublic('/pages/corporativo/nosotros.html');
      }
      else showToast(res?.error || 'Error al guardar', 'error');
    } catch { showToast('Error de conexión', 'error'); }
  }

  /* ─── NAVEGACIÓN CON CAMBIOS PENDIENTES ─────────── */
  let pendingNavUrl = null;

  /* interceptar links del sidebar y logout después de que se renderice */
  function interceptNavLinks() {
    const links = document.querySelectorAll(
      '.admin-sidebar a[href], #logoutLink'
    );
    links.forEach(link => {
      link.addEventListener('click', function(e) {
        if (!dirty) return;
        e.preventDefault();
        pendingNavUrl = this.href;
        document.getElementById('navModal').classList.add('show');
      });
    });
  }
  /* El sidebar se construye dinámicamente: esperamos un tick */
  setTimeout(interceptNavLinks, 200);

  /* también con el botón de cerrar del sidebar overlay */
  document.getElementById('sidebar-overlay')?.addEventListener('click', () => {
    if (dirty && pendingNavUrl) return; /* ya hay un pendiente */
  });

  async function modalSaveAndGo() {
    await saveNosotros();
    if (pendingNavUrl) window.location.href = pendingNavUrl;
  }
  function modalDiscardAndGo() {
    dirty = false; /* permitir navegación */
    if (pendingNavUrl) window.location.href = pendingNavUrl;
  }
  function modalCancel() {
    pendingNavUrl = null;
    document.getElementById('navModal').classList.remove('show');
  }

  /* cerrar modal con Escape / guardar con Ctrl+S */
  document.addEventListener('keydown', e => {
    if ((e.ctrlKey || e.metaKey) && e.key === 's') { e.preventDefault(); saveNosotros(); return; }
    if (e.key === 'Escape') modalCancel();
  });

  /* beforeunload nativo (cierre de pestaña / recargar) */
  window.addEventListener('beforeunload', function(e) {
    if (dirty) {
      e.preventDefault();
      e.returnValue = '¿Salir sin guardar los cambios?';
      return e.returnValue;
    }
  });
</script>

</body>
</html>
