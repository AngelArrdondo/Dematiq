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

$logRows = [];
try {
    $stmt = $pdo->query(
        'SELECT la.id, la.username, la.ip, la.resultado, la.user_agent, la.creado_en,
                u.nombre
         FROM log_accesos la
         LEFT JOIN usuarios u ON u.id = la.usuario_id
         ORDER BY la.creado_en DESC
         LIMIT 200'
    );
    $logRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Accesos | DEMATIQ Admin</title>
  <link rel="stylesheet" href="../assets/css/admin.css?v=7">
  <link rel="icon" type="image/svg+xml" href="../../assets/images/logos/favicon-d.svg">
  <style>

    /* ─── BANNER ─────────────────────────────────────── */
    .acc-banner {
      background: linear-gradient(135deg,#020617 0%,#0f172a 45%,#1e3a5f 100%);
      border-radius: 20px;
      padding: 30px 32px;
      margin-bottom: 0;
      position: relative;
      overflow: hidden;
    }
    .acc-banner::before {
      content:'';position:absolute;
      width:500px;height:500px;border-radius:50%;
      background:radial-gradient(circle,rgba(125,211,252,.15) 0%,transparent 65%);
      top:-200px;right:-60px;pointer-events:none;
    }
    .acc-banner::after {
      content:'';position:absolute;
      width:200px;height:200px;border-radius:50%;
      background:radial-gradient(circle,rgba(56,189,248,.06) 0%,transparent 70%);
      bottom:-80px;left:35%;pointer-events:none;
    }
    .acc-banner-mesh {
      position:absolute;inset:0;pointer-events:none;overflow:hidden;
      background-image:radial-gradient(rgba(255,255,255,.035) 1px,transparent 1px);
      background-size:22px 22px;
    }
    .banner-inner { position:relative;z-index:1; }
    .banner-chip {
      display:inline-flex;align-items:center;gap:7px;
      background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.12);
      color:rgba(255,255,255,.65);font-size:.62rem;font-weight:700;
      letter-spacing:1.8px;text-transform:uppercase;
      padding:5px 12px;border-radius:20px;margin-bottom:14px;
    }
    .bdot{width:6px;height:6px;border-radius:50%;background:#7dd3fc;animation:bdot 2.2s ease-in-out infinite;}
    @keyframes bdot{0%,100%{opacity:1;box-shadow:0 0 0 0 rgba(125,211,252,.5);}50%{opacity:.7;box-shadow:0 0 0 5px rgba(125,211,252,0);}}
    .banner-title{font-size:1.65rem;font-weight:800;color:#fff;letter-spacing:-.025em;line-height:1.1;margin-bottom:6px;}
    .banner-desc{font-size:.82rem;color:rgba(255,255,255,.45);line-height:1.65;max-width:460px;margin-bottom:22px;}
    .banner-section-cards{display:flex;gap:12px;flex-wrap:wrap;}
    .bsc{display:flex;align-items:center;gap:10px;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.1);padding:10px 16px;border-radius:14px;flex:1;min-width:120px;transition:background .2s;}
    .bsc:hover{background:rgba(255,255,255,.11);}
    .bsc-icon{width:34px;height:34px;border-radius:10px;flex-shrink:0;display:flex;align-items:center;justify-content:center;}
    .bsc-icon svg{width:16px;height:16px;color:#fff;}
    .bsci-sky   {background:linear-gradient(135deg,#0369a1,#0ea5e9);}
    .bsci-green {background:linear-gradient(135deg,#065f46,#10b981);}
    .bsci-red   {background:linear-gradient(135deg,#991b1b,#dc2626);}
    .bsci-amber {background:linear-gradient(135deg,#92400e,#d97706);}
    .bsc-info{}
    .bsc-label{font-size:.6rem;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.8px;margin-bottom:2px;}
    .bsc-val{font-size:.88rem;font-weight:800;color:#fff;line-height:1.2;}
    @media(max-width:600px){.acc-banner{padding:22px 18px;}.banner-section-cards{flex-direction:column;}}

    /* ─── SECTION CARD ──────────────────────────────── */
    .section-card{background:#fff;border:1.5px solid var(--border);border-radius:20px;overflow:hidden;margin-bottom:14px;}
    .sc-head{display:flex;align-items:center;gap:16px;padding:18px 24px;border-bottom:1px solid var(--border);background:linear-gradient(to right,#f8faff,#fff);}
    .sc-icon{width:44px;height:44px;border-radius:12px;flex-shrink:0;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 14px rgba(0,0,0,.18);}
    .sc-icon svg{width:20px;height:20px;color:#fff;}
    .si-slate{background:linear-gradient(135deg,#334155,#475569);}
    .si-sky  {background:linear-gradient(135deg,#0369a1,#0ea5e9);}
    .sc-head-text{flex:1;min-width:0;}
    .sc-head-text h3{font-size:.95rem;font-weight:700;color:var(--text);}
    .sc-head-text p{font-size:.75rem;color:var(--text-lt);margin-top:2px;}

    /* ─── FILTER BAR ─────────────────────────────────── */
    .filter-bar{display:flex;align-items:center;gap:10px;padding:14px 20px;border-bottom:1px solid var(--border);flex-wrap:wrap;}

    /* chips de resultado */
    .res-chips{display:flex;gap:6px;flex-wrap:wrap;}
    .res-chip{display:inline-flex;align-items:center;gap:5px;padding:5px 12px;border-radius:20px;font-size:.76rem;font-weight:600;cursor:pointer;border:1.5px solid var(--border);background:#f8faff;color:var(--text-lt);transition:all .15s;}
    .res-chip:hover{border-color:#c7d5f0;color:var(--text);}
    .res-chip.active-all     {background:#1e293b;color:#fff;border-color:#1e293b;}
    .res-chip.active-exito   {background:#dcfce7;color:#166534;border-color:#86efac;}
    .res-chip.active-fallo   {background:#fee2e2;color:#991b1b;border-color:#fca5a5;}
    .res-chip.active-bloqueado{background:#fef9c3;color:#854d0e;border-color:#fde047;}
    .res-chip svg{width:10px;height:10px;}

    /* búsqueda */
    .search-wrap{position:relative;flex:1;min-width:180px;}
    .search-wrap svg{position:absolute;left:11px;top:50%;transform:translateY(-50%);width:14px;height:14px;color:#99a3b8;pointer-events:none;}
    .search-input{width:100%;box-sizing:border-box;padding:8px 12px 8px 32px;border:1.5px solid var(--border);border-radius:10px;font-size:.82rem;font-family:inherit;color:var(--text);background:#fafcff;outline:none;transition:border-color .15s,box-shadow .15s;}
    .search-input:focus{border-color:var(--accent-lt);box-shadow:0 0 0 3px rgba(46,107,207,.08);}
    .results-count{font-size:.73rem;color:var(--text-lt);white-space:nowrap;margin-left:auto;}

    /* ─── LOG ENTRIES LIST ───────────────────────────── */
    .log-list{display:flex;flex-direction:column;}

    .log-entry{display:flex;align-items:center;gap:14px;padding:13px 20px;border-bottom:1px solid var(--border);transition:background .15s;}
    .log-entry:last-child{border-bottom:none;}
    .log-entry:hover{background:#f8faff;}

    /* icono de resultado */
    .log-icon{width:36px;height:36px;border-radius:10px;flex-shrink:0;display:flex;align-items:center;justify-content:center;}
    .log-icon svg{width:15px;height:15px;color:#fff;}
    .log-icon.exito    {background:linear-gradient(135deg,#065f46,#10b981);box-shadow:0 2px 8px rgba(16,185,129,.25);}
    .log-icon.fallo    {background:linear-gradient(135deg,#991b1b,#dc2626);box-shadow:0 2px 8px rgba(220,38,38,.22);}
    .log-icon.bloqueado{background:linear-gradient(135deg,#92400e,#d97706);box-shadow:0 2px 8px rgba(217,119,6,.25);}

    /* info del evento */
    .log-info{flex:1;min-width:0;}
    .log-info-top{display:flex;align-items:baseline;gap:8px;flex-wrap:wrap;}
    .log-username{font-size:.87rem;font-weight:700;color:var(--text);}
    .log-nombre{font-size:.73rem;color:var(--text-lt);}
    .log-info-bottom{display:flex;align-items:center;gap:10px;margin-top:3px;flex-wrap:wrap;}
    .log-ip{font-family:monospace;font-size:.74rem;color:#5c6b8a;background:#eef2ff;padding:2px 7px;border-radius:5px;}
    .log-ua{font-size:.68rem;color:#b0b8cc;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:260px;}
    @media(max-width:640px){.log-ua{display:none;}}

    /* badge resultado */
    .res-badge{display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:20px;font-size:.7rem;font-weight:700;white-space:nowrap;flex-shrink:0;}
    .res-badge.exito    {background:#dcfce7;color:#166534;}
    .res-badge.fallo    {background:#fee2e2;color:#991b1b;}
    .res-badge.bloqueado{background:#fef9c3;color:#854d0e;}
    .res-badge svg{width:9px;height:9px;}

    /* fecha */
    .log-date{font-size:.72rem;color:var(--text-lt);white-space:nowrap;text-align:right;flex-shrink:0;min-width:80px;}
    .log-date strong{display:block;color:var(--text);font-size:.78rem;font-weight:600;}

    /* empty / loading */
    .log-empty{text-align:center;padding:50px 24px;color:var(--text-lt);}
    .log-empty svg{opacity:.15;margin-bottom:12px;}
    .log-empty p{font-size:.9rem;}

    /* ─── PAGINACIÓN / SHOW-MORE ─────────────────────── */
    .show-more-wrap{padding:14px 20px;text-align:center;border-top:1px solid var(--border);}
    .show-more-btn{display:inline-flex;align-items:center;gap:7px;padding:8px 20px;border-radius:10px;border:1.5px solid var(--border);background:#f8faff;color:var(--text-lt);font-size:.8rem;font-weight:600;cursor:pointer;transition:all .15s;}
    .show-more-btn:hover{border-color:var(--accent-lt);color:var(--accent);background:#eff4ff;}

    @media(max-width:600px){.filter-bar{gap:7px;}.log-entry{padding:11px 14px;gap:10px;}}
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
      <span class="admin-topbar-title">Registro de Accesos</span>
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
    <div class="acc-banner" style="margin-bottom:16px;">
      <div class="acc-banner-mesh"></div>
      <div class="banner-inner">
        <div class="banner-chip"><span class="bdot"></span> Solo lectura</div>
        <h1 class="banner-title">Registro de Accesos</h1>
        <p class="banner-desc">Últimos 200 intentos de inicio de sesión al panel de administración. Filtra por resultado o busca por usuario e IP.</p>
        <div class="banner-section-cards">
          <div class="bsc">
            <div class="bsc-icon bsci-sky">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            </div>
            <div class="bsc-info">
              <div class="bsc-label">Total</div>
              <div class="bsc-val" id="statTotal">0</div>
            </div>
          </div>
          <div class="bsc">
            <div class="bsc-icon bsci-green">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20,6 9,17 4,12"/></svg>
            </div>
            <div class="bsc-info">
              <div class="bsc-label">Exitosos</div>
              <div class="bsc-val" id="statExito">0</div>
            </div>
          </div>
          <div class="bsc">
            <div class="bsc-icon bsci-red">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </div>
            <div class="bsc-info">
              <div class="bsc-label">Fallidos</div>
              <div class="bsc-val" id="statFallo">0</div>
            </div>
          </div>
          <div class="bsc">
            <div class="bsc-icon bsci-amber">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
            </div>
            <div class="bsc-info">
              <div class="bsc-label">Bloqueados</div>
              <div class="bsc-val" id="statBloq">0</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ══ CARD: LOG ════════════════════════════════════ -->
    <div class="section-card">
      <div class="sc-head">
        <div class="sc-icon si-slate">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
          </svg>
        </div>
        <div class="sc-head-text">
          <h3>Intentos de inicio de sesión</h3>
          <p>Los 200 registros más recientes — se actualiza al recargar la página</p>
        </div>
        <button class="btn-admin btn-outline-admin" onclick="window.location.reload()" style="flex-shrink:0;font-size:.78rem;padding:7px 13px;gap:5px">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="23,4 23,10 17,10"/><polyline points="1,20 1,14 7,14"/><path d="M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15"/></svg>
          Actualizar
        </button>
      </div>

      <!-- filtros -->
      <div class="filter-bar">
        <div class="res-chips">
          <button class="res-chip active-all" data-filter="" onclick="setFilter(this,'')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
            Todos
          </button>
          <button class="res-chip" data-filter="exito" onclick="setFilter(this,'exito')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20,6 9,17 4,12"/></svg>
            Exitosos
          </button>
          <button class="res-chip" data-filter="fallo" onclick="setFilter(this,'fallo')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            Fallidos
          </button>
          <button class="res-chip" data-filter="bloqueado" onclick="setFilter(this,'bloqueado')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
            Bloqueados
          </button>
        </div>
        <div class="search-wrap">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input type="text" class="search-input" id="searchInput" placeholder="Buscar usuario o IP…" oninput="renderList()">
        </div>
        <span class="results-count" id="resultsCount"></span>
      </div>

      <!-- lista de eventos -->
      <div class="log-list" id="logList"></div>

      <div class="show-more-wrap" id="showMoreWrap" style="display:none">
        <button class="show-more-btn" onclick="showMore()">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="14" height="14"><polyline points="6,9 12,15 18,9"/></svg>
          Mostrar más registros
        </button>
      </div>

    </div>

  </div>
</div>

<?php $profileApiPath = '../api/profile.php'; $fotoPrefix = '../'; require __DIR__ . '/../includes/profile-modal.php'; ?>

<script src="../assets/js/auth.js?v=2"></script>
<script>
  const CSRF_TOKEN = '<?= $csrfToken ?>';

  AdminSidebar.init('accesos', '../', '../../');

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

  /* ─── datos (inyectados desde PHP) ──────────────── */
  const RAW = <?= json_encode($logRows, JSON_UNESCAPED_UNICODE) ?>;

  /* ─── estado ─────────────────────────────────────── */
  let activeFilter = '';
  let visibleCount = 50;
  const PAGE_SIZE  = 50;

  /* ─── helpers ────────────────────────────────────── */
  function escH(s) {
    return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  function fmtDate(ts) {
    if (!ts) return { date: '—', time: '' };
    const d = new Date(ts);
    return {
      date: d.toLocaleDateString('es-MX', { day:'2-digit', month:'short', year:'numeric' }),
      time: d.toLocaleTimeString('es-MX', { hour:'2-digit', minute:'2-digit' })
    };
  }

  function fmtRelative(ts) {
    if (!ts) return '—';
    const diff = Date.now() - new Date(ts).getTime();
    if (diff < 60000)        return 'hace un momento';
    if (diff < 3600000)      return `hace ${Math.floor(diff/60000)} min`;
    if (diff < 86400000)     return `hace ${Math.floor(diff/3600000)} h`;
    if (diff < 86400000 * 3) return `hace ${Math.floor(diff/86400000)} días`;
    const { date, time } = fmtDate(ts);
    return `${date} · ${time}`;
  }

  function parseUA(ua) {
    if (!ua) return '—';
    if (/Chrome/i.test(ua) && !/Edg/i.test(ua)) return 'Chrome';
    if (/Firefox/i.test(ua))  return 'Firefox';
    if (/Safari/i.test(ua) && !/Chrome/i.test(ua)) return 'Safari';
    if (/Edg/i.test(ua))      return 'Edge';
    if (/OPR|Opera/i.test(ua)) return 'Opera';
    return ua.slice(0, 40);
  }

  const ICONS = {
    exito:    `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20,6 9,17 4,12"/></svg>`,
    fallo:    `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>`,
    bloqueado:`<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>`,
  };
  const LABELS = { exito: 'Exitoso', fallo: 'Fallido', bloqueado: 'Bloqueado' };

  /* ─── estadísticas banner ────────────────────────── */
  function updateStats() {
    const counts = { exito: 0, fallo: 0, bloqueado: 0 };
    RAW.forEach(r => { if (counts[r.resultado] !== undefined) counts[r.resultado]++; });
    document.getElementById('statTotal').textContent = RAW.length;
    document.getElementById('statExito').textContent = counts.exito;
    document.getElementById('statFallo').textContent = counts.fallo;
    document.getElementById('statBloq').textContent  = counts.bloqueado;
  }

  /* ─── filtrar ────────────────────────────────────── */
  function filteredRows() {
    const q = (document.getElementById('searchInput').value || '').toLowerCase();
    return RAW.filter(r => {
      if (activeFilter && r.resultado !== activeFilter) return false;
      if (q && !(r.username||'').toLowerCase().includes(q) && !(r.ip||'').includes(q) && !(r.nombre||'').toLowerCase().includes(q)) return false;
      return true;
    });
  }

  /* ─── set filtro chip ────────────────────────────── */
  function setFilter(btn, val) {
    activeFilter = val;
    visibleCount = PAGE_SIZE;
    document.querySelectorAll('.res-chip').forEach(c => {
      c.className = 'res-chip';
      if (c.dataset.filter === val) c.classList.add('active-' + (val || 'all'));
    });
    renderList();
  }

  /* ─── render ─────────────────────────────────────── */
  function renderList() {
    const rows = filteredRows();
    const visible = rows.slice(0, visibleCount);

    document.getElementById('resultsCount').textContent =
      rows.length === RAW.length
        ? `${RAW.length} registro${RAW.length !== 1 ? 's' : ''}`
        : `${rows.length} de ${RAW.length}`;

    const list = document.getElementById('logList');

    if (!rows.length) {
      list.innerHTML = `<div class="log-empty">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        <p>No hay registros con estos filtros.</p>
      </div>`;
      document.getElementById('showMoreWrap').style.display = 'none';
      return;
    }

    list.innerHTML = visible.map(r => {
      const res = r.resultado || 'fallo';
      const { date, time } = fmtDate(r.creado_en);
      const icon = ICONS[res] || ICONS.fallo;
      const label = LABELS[res] || res;
      const browser = parseUA(r.user_agent);

      return `
        <div class="log-entry">
          <div class="log-icon ${res}">${icon}</div>
          <div class="log-info">
            <div class="log-info-top">
              <span class="log-username">${escH(r.username || '—')}</span>
              ${r.nombre ? `<span class="log-nombre">(${escH(r.nombre)})</span>` : ''}
            </div>
            <div class="log-info-bottom">
              ${r.ip ? `<span class="log-ip">${escH(r.ip)}</span>` : ''}
              ${browser !== '—' ? `<span class="log-ua" title="${escH(r.user_agent||'')}">via ${escH(browser)}</span>` : ''}
            </div>
          </div>
          <div style="display:flex;flex-direction:column;align-items:flex-end;gap:5px;flex-shrink:0;">
            <span class="res-badge ${res}">${icon.replace('2.5','2')} ${label}</span>
            <span class="log-date"><strong>${time}</strong>${date}</span>
          </div>
        </div>`;
    }).join('');

    const showMoreWrap = document.getElementById('showMoreWrap');
    if (rows.length > visibleCount) {
      showMoreWrap.style.display = '';
      showMoreWrap.querySelector('.show-more-btn').textContent =
        `Mostrar más (${rows.length - visibleCount} restantes)`;
      const btn = showMoreWrap.querySelector('.show-more-btn');
      btn.innerHTML = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="14" height="14"><polyline points="6,9 12,15 18,9"/></svg> Mostrar más (${rows.length - visibleCount} restantes)`;
    } else {
      showMoreWrap.style.display = 'none';
    }
  }

  function showMore() {
    visibleCount += PAGE_SIZE;
    renderList();
  }

  /* ─── arrancar ───────────────────────────────────── */
  updateStats();
  renderList();
</script>

</body>
</html>
