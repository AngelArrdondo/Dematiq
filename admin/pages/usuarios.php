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
  <title>Usuarios | DEMATIQ Admin</title>
  <link rel="stylesheet" href="../assets/css/admin.css?v=7">
  <link rel="icon" type="image/svg+xml" href="../../assets/images/logos/favicon-d.svg">
  <style>

    /* ─── BANNER ─────────────────────────────────────── */
    .usr-banner {
      background: linear-gradient(135deg,#1a0610 0%,#3b0d20 40%,#9d174d 100%);
      border-radius: 20px;
      padding: 30px 32px;
      margin-bottom: 0;
      position: relative;
      overflow: hidden;
    }
    .usr-banner::before {
      content:'';position:absolute;
      width:500px;height:500px;border-radius:50%;
      background:radial-gradient(circle,rgba(190,24,93,.25) 0%,transparent 65%);
      top:-200px;right:-60px;pointer-events:none;
    }
    .usr-banner::after {
      content:'';position:absolute;
      width:200px;height:200px;border-radius:50%;
      background:radial-gradient(circle,rgba(249,168,212,.07) 0%,transparent 70%);
      bottom:-80px;left:35%;pointer-events:none;
    }
    .usr-banner-mesh {
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
    .bdot{width:6px;height:6px;border-radius:50%;background:#f9a8d4;animation:bdot 2.2s ease-in-out infinite;}
    @keyframes bdot{0%,100%{opacity:1;box-shadow:0 0 0 0 rgba(249,168,212,.5);}50%{opacity:.7;box-shadow:0 0 0 5px rgba(249,168,212,0);}}
    .banner-title{font-size:1.65rem;font-weight:800;color:#fff;letter-spacing:-.025em;line-height:1.1;margin-bottom:6px;}
    .banner-desc{font-size:.82rem;color:rgba(255,255,255,.5);line-height:1.65;max-width:440px;margin-bottom:22px;}
    .banner-section-cards{display:flex;gap:12px;flex-wrap:wrap;}
    .bsc{display:flex;align-items:center;gap:10px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.12);padding:10px 16px;border-radius:14px;flex:1;min-width:130px;transition:background .2s;}
    .bsc:hover{background:rgba(255,255,255,.12);}
    .bsc-icon{width:34px;height:34px;border-radius:10px;flex-shrink:0;display:flex;align-items:center;justify-content:center;}
    .bsc-icon svg{width:16px;height:16px;color:#fff;}
    .bsci-rose  {background:linear-gradient(135deg,#9d174d,#db2777);}
    .bsci-pink  {background:linear-gradient(135deg,#831843,#be185d);}
    .bsci-green {background:linear-gradient(135deg,#065f46,#10b981);}
    .bsc-info{}
    .bsc-label{font-size:.6rem;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.8px;margin-bottom:2px;}
    .bsc-val{font-size:.82rem;font-weight:700;color:#fff;line-height:1.2;max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
    @media(max-width:600px){.usr-banner{padding:22px 18px;}.banner-section-cards{flex-direction:column;}}

    /* ─── SECTION CARD ──────────────────────────────── */
    .section-card{background:#fff;border:1.5px solid var(--border);border-radius:20px;overflow:hidden;margin-bottom:14px;}
    .sc-head{display:flex;align-items:center;gap:16px;padding:18px 24px;border-bottom:1px solid var(--border);background:linear-gradient(to right,#f8faff,#fff);}
    .sc-icon{width:44px;height:44px;border-radius:12px;flex-shrink:0;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 14px rgba(0,0,0,.18);}
    .sc-icon svg{width:20px;height:20px;color:#fff;}
    .si-rose{background:linear-gradient(135deg,#9d174d,#db2777);}
    .sc-head-text{flex:1;min-width:0;}
    .sc-head-text h3{font-size:.95rem;font-weight:700;color:var(--text);}
    .sc-head-text p{font-size:.75rem;color:var(--text-lt);margin-top:2px;}

    /* ─── SEARCH BAR ─────────────────────────────────── */
    .search-bar{display:flex;align-items:center;gap:10px;padding:14px 20px;border-bottom:1px solid var(--border);flex-wrap:wrap;}
    .search-wrap{position:relative;flex:1;min-width:180px;}
    .search-wrap svg{position:absolute;left:11px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#99a3b8;pointer-events:none;}
    .search-input{width:100%;box-sizing:border-box;padding:8px 12px 8px 34px;border:1.5px solid var(--border);border-radius:10px;font-size:.85rem;font-family:inherit;color:var(--text);background:#fafcff;outline:none;transition:border-color .15s,box-shadow .15s;}
    .search-input:focus{border-color:var(--accent-lt);box-shadow:0 0 0 3px rgba(46,107,207,.08);}
    .filter-select{padding:8px 12px;border:1.5px solid var(--border);border-radius:10px;font-size:.82rem;font-family:inherit;color:var(--text);background:#fafcff;outline:none;cursor:pointer;appearance:none;background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 24 24' fill='none' stroke='%2399a3b8' stroke-width='2' xmlns='http://www.w3.org/2000/svg'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 8px center;background-size:14px;padding-right:28px;transition:border-color .15s;}
    .filter-select:focus{border-color:var(--accent-lt);}
    .results-count{font-size:.75rem;color:var(--text-lt);white-space:nowrap;margin-left:auto;}

    /* ─── USERS GRID ─────────────────────────────────── */
    .users-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;padding:20px 24px;}
    @media(max-width:640px){.users-grid{grid-template-columns:1fr;padding:14px 16px;}}

    /* ─── USER CARD ──────────────────────────────────── */
    .user-card{border:1.5px solid var(--border);border-radius:16px;overflow:hidden;background:#fff;transition:box-shadow .2s,border-color .2s,transform .18s;}
    .user-card:hover{box-shadow:0 6px 24px rgba(0,0,0,.09);border-color:#c7d5f0;transform:translateY(-1px);}
    .user-card.inactive{opacity:.7;}
    .user-card-top{display:flex;align-items:center;justify-content:space-between;padding:16px 16px 10px;}

    /* avatar */
    .user-avatar{width:52px;height:52px;border-radius:14px;overflow:hidden;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:1.3rem;font-weight:800;color:#fff;background:linear-gradient(135deg,#9d174d,#db2777);box-shadow:0 4px 12px rgba(157,23,77,.3);}
    .user-avatar img{width:100%;height:100%;object-fit:cover;display:block;}

    /* status toggle inline */
    .status-toggle-wrap{display:flex;flex-direction:column;align-items:center;gap:3px;}
    .status-label{font-size:.6rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;}
    .status-label.active{color:#065f46;}
    .status-label.inactive{color:#991b1b;}
    .toggle-switch{position:relative;width:36px;height:20px;}
    .toggle-switch input{opacity:0;width:0;height:0;}
    .toggle-track{position:absolute;inset:0;border-radius:10px;cursor:pointer;transition:background .2s;background:#d1d5db;}
    .toggle-track::after{content:'';position:absolute;width:14px;height:14px;border-radius:50%;background:#fff;top:3px;left:3px;transition:transform .2s;box-shadow:0 1px 3px rgba(0,0,0,.2);}
    .toggle-switch input:checked + .toggle-track{background:#10b981;}
    .toggle-switch input:checked + .toggle-track::after{transform:translateX(16px);}

    /* card body */
    .user-card-body{padding:0 16px 14px;}
    .user-card-name{font-size:.95rem;font-weight:800;color:var(--text);line-height:1.2;margin-bottom:2px;}
    .user-card-username{font-size:.75rem;color:#9d174d;font-weight:600;margin-bottom:6px;}
    .user-card-info{display:flex;flex-direction:column;gap:3px;margin-bottom:12px;}
    .user-info-row{display:flex;align-items:center;gap:6px;font-size:.75rem;color:var(--text-lt);}
    .user-info-row svg{width:11px;height:11px;flex-shrink:0;opacity:.5;}
    .user-info-row span{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
    .user-card-access{font-size:.7rem;color:#b0b8cc;padding:8px 0;border-top:1px solid var(--border);margin-bottom:10px;}
    .user-card-access span{color:var(--text-lt);}

    /* action buttons */
    .user-card-actions{display:flex;gap:7px;}
    .uca-btn{flex:1;display:flex;align-items:center;justify-content:center;gap:5px;padding:7px 8px;border-radius:9px;font-size:.74rem;font-weight:600;cursor:pointer;border:1.5px solid var(--border);background:#f8faff;color:var(--text-lt);transition:all .15s;}
    .uca-btn:hover{border-color:var(--accent-lt);color:var(--accent);background:#eff4ff;}
    .uca-btn.danger:hover{border-color:#fecaca;color:#dc2626;background:#fff1f0;}
    .uca-btn svg{width:12px;height:12px;}

    /* self-badge */
    .self-badge{display:inline-flex;align-items:center;gap:4px;font-size:.6rem;font-weight:700;padding:2px 7px;border-radius:5px;background:#fdf2f8;color:#9d174d;border:1px solid #fbcfe8;margin-left:6px;vertical-align:middle;}

    /* ─── EMPTY STATE ────────────────────────────────── */
    .users-empty{text-align:center;padding:50px 24px;color:var(--text-lt);}
    .users-empty svg{opacity:.15;margin-bottom:12px;}
    .users-empty p{font-size:.9rem;margin-bottom:4px;}

    /* ─── MODAL (crear / editar) ─────────────────────── */
    .modal-overlay{position:fixed;inset:0;background:rgba(26,6,16,.55);backdrop-filter:blur(4px);z-index:9000;display:none;align-items:center;justify-content:center;}
    .modal-overlay.open{display:flex;}
    .modal-card{background:#fff;border-radius:22px;box-shadow:0 24px 80px rgba(0,0,0,.28);width:min(500px,93vw);max-height:92vh;overflow-y:auto;}
    .modal-header{padding:20px 24px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:14px;}
    .modal-header-icon{width:40px;height:40px;border-radius:11px;background:linear-gradient(135deg,#9d174d,#db2777);display:flex;align-items:center;justify-content:center;flex-shrink:0;}
    .modal-header-icon svg{width:18px;height:18px;color:#fff;}
    .modal-header h3{font-size:1rem;font-weight:800;color:var(--text);flex:1;}
    .modal-close{background:none;border:none;cursor:pointer;padding:4px;color:var(--text-lt);border-radius:7px;transition:background .15s;}
    .modal-close:hover{background:#f0f4ff;}
    .modal-body{padding:22px 24px;display:flex;flex-direction:column;gap:16px;}
    .modal-footer{padding:14px 24px 20px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end;}

    .field{margin:0;}
    .field-top{display:flex;align-items:baseline;justify-content:space-between;margin-bottom:7px;}
    .field-top label{font-size:.71rem;font-weight:700;color:var(--text-lt);text-transform:uppercase;letter-spacing:.5px;}
    .field-hint{font-size:.65rem;color:#aab;font-style:italic;margin-top:4px;}
    input.fi{width:100%;box-sizing:border-box;padding:10px 13px;border:1.5px solid var(--border);border-radius:10px;font-size:.87rem;font-family:inherit;color:var(--text);background:#fafcff;outline:none;transition:border-color .15s,box-shadow .15s;}
    input.fi:focus{border-color:var(--accent-lt);background:#fff;box-shadow:0 0 0 3px rgba(46,107,207,.1);}
    input.fi:disabled{background:#f1f4fa;color:var(--text-lt);cursor:not-allowed;}
    .field-row{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
    @media(max-width:480px){.field-row{grid-template-columns:1fr;}}

    /* ─── MODAL: reset pwd ───────────────────────────── */
    .reset-modal-icon{width:52px;height:52px;border-radius:16px;background:linear-gradient(135deg,#f59e0b,#fbbf24);display:flex;align-items:center;justify-content:center;margin-bottom:14px;}
    .reset-modal-icon svg{width:24px;height:24px;color:#fff;}

    @media(max-width:600px){.modal-body{padding:16px 18px;}.modal-header{padding:16px 18px 12px;}.modal-footer{padding:12px 18px 16px;}}
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
      <span class="admin-topbar-title">Usuarios</span>
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
    <div class="usr-banner" style="margin-bottom:16px;">
      <div class="usr-banner-mesh"></div>
      <div class="banner-inner">
        <div class="banner-chip"><span class="bdot"></span> Panel de administración</div>
        <h1 class="banner-title">Gestión de Usuarios</h1>
        <p class="banner-desc">Administra las cuentas de acceso al panel. Crea nuevos usuarios, edita sus datos y controla su estado de acceso.</p>
        <div class="banner-section-cards">
          <div class="bsc">
            <div class="bsc-icon bsci-rose">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
            </div>
            <div class="bsc-info">
              <div class="bsc-label">Total</div>
              <div class="bsc-val" id="statTotal">—</div>
            </div>
          </div>
          <div class="bsc">
            <div class="bsc-icon bsci-green">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22,4 12,14.01 9,11.01"/></svg>
            </div>
            <div class="bsc-info">
              <div class="bsc-label">Activos</div>
              <div class="bsc-val" id="statActive">—</div>
            </div>
          </div>
          <div class="bsc">
            <div class="bsc-icon bsci-pink">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12,6 12,12 16,14"/></svg>
            </div>
            <div class="bsc-info">
              <div class="bsc-label">Último acceso</div>
              <div class="bsc-val" id="statLast">—</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ══ CARD: USUARIOS ══════════════════════════════ -->
    <div class="section-card">
      <div class="sc-head">
        <div class="sc-icon si-rose">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/>
            <path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/>
          </svg>
        </div>
        <div class="sc-head-text">
          <h3>Usuarios del sistema</h3>
          <p>Todas las cuentas con acceso al panel de administración</p>
        </div>
        <button class="btn-admin btn-primary-admin" onclick="openCreate()" style="flex-shrink:0;font-size:.8rem;padding:7px 14px;gap:6px;background:linear-gradient(135deg,#9d174d,#db2777);border:none;">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Nuevo usuario
        </button>
      </div>

      <!-- barra búsqueda / filtro -->
      <div class="search-bar">
        <div class="search-wrap">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input type="text" class="search-input" id="searchInput" placeholder="Buscar por nombre o usuario…" oninput="filterUsers()">
        </div>
        <select class="filter-select" id="filterStatus" onchange="filterUsers()">
          <option value="">Todos</option>
          <option value="1">Activos</option>
          <option value="0">Inactivos</option>
        </select>
        <span class="results-count" id="resultsCount"></span>
      </div>

      <div id="users-grid" class="users-grid">
        <div style="grid-column:1/-1;text-align:center;padding:40px;color:var(--text-lt);font-size:.9rem">Cargando usuarios…</div>
      </div>
    </div>

  </div>
</div>

<!-- ══ MODAL: CREAR / EDITAR ═════════════════════════ -->
<div class="modal-overlay" id="userModal">
  <div class="modal-card">
    <div class="modal-header">
      <div class="modal-header-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      </div>
      <h3 id="modalTitle">Nuevo usuario</h3>
      <button class="modal-close" onclick="closeModal()">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="modalUserId">
      <div class="field-row">
        <div class="field">
          <div class="field-top"><label>Primer nombre *</label></div>
          <input type="text" class="fi" id="mPrimerNombre" placeholder="Primer nombre">
        </div>
        <div class="field">
          <div class="field-top"><label>Apellido paterno *</label></div>
          <input type="text" class="fi" id="mApellidoPaterno" placeholder="Apellido paterno">
        </div>
      </div>
      <div class="field-row">
        <div class="field">
          <div class="field-top"><label>Nombre completo *</label></div>
          <input type="text" class="fi" id="mNombre" placeholder="Nombre para mostrar">
        </div>
        <div class="field">
          <div class="field-top"><label>Usuario *</label></div>
          <input type="text" class="fi" id="mUsername" placeholder="usuario123" autocomplete="off">
          <p class="field-hint" id="usernameHint">Letras, números y _ (3–60 chars)</p>
        </div>
      </div>
      <div class="field-row">
        <div class="field">
          <div class="field-top"><label>Email</label></div>
          <input type="email" class="fi" id="mEmail" placeholder="correo@ejemplo.com">
        </div>
        <div class="field">
          <div class="field-top"><label>Teléfono</label></div>
          <input type="tel" class="fi" id="mTelefono" placeholder="+52 55 0000 0000">
        </div>
      </div>
      <div id="passwordSection">
        <div class="field">
          <div class="field-top"><label>Contraseña *</label></div>
          <input type="password" class="fi" id="mPassword" placeholder="Mínimo 8 caracteres" autocomplete="new-password">
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn-admin btn-outline-admin" onclick="closeModal()">Cancelar</button>
      <button class="btn-admin btn-primary-admin" id="modalSaveBtn" onclick="saveUser()"
        style="background:linear-gradient(135deg,#9d174d,#db2777);border:none;">
        Guardar
      </button>
    </div>
  </div>
</div>

<!-- ══ MODAL: RESET CONTRASEÑA ═══════════════════════ -->
<div class="modal-overlay" id="resetModal">
  <div class="modal-card">
    <div class="modal-header">
      <div class="modal-header-icon" style="background:linear-gradient(135deg,#b45309,#d97706)">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
      </div>
      <h3>Restablecer contraseña</h3>
      <button class="modal-close" onclick="closeReset()">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="resetUserId">
      <p id="resetUserName" style="font-size:.85rem;color:var(--text-lt);margin:0;padding:10px 14px;background:#fffbeb;border-radius:10px;border:1px solid #fde68a;"></p>
      <div class="field">
        <div class="field-top"><label>Nueva contraseña *</label></div>
        <input type="password" class="fi" id="resetPwd" placeholder="Mínimo 8 caracteres" autocomplete="new-password">
      </div>
      <div class="field">
        <div class="field-top"><label>Confirmar contraseña *</label></div>
        <input type="password" class="fi" id="resetPwd2" placeholder="Repite la contraseña">
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn-admin btn-outline-admin" onclick="closeReset()">Cancelar</button>
      <button class="btn-admin btn-primary-admin" id="resetSaveBtn" onclick="doReset()"
        style="background:linear-gradient(135deg,#b45309,#d97706);border:none;">
        Restablecer
      </button>
    </div>
  </div>
</div>

<?php $profileApiPath = '../api/profile.php'; $fotoPrefix = '../'; require __DIR__ . '/../includes/profile-modal.php'; ?>

<script src="../assets/js/auth.js?v=2"></script>
<script>
  const CSRF_TOKEN = '<?= $csrfToken ?>';
  const SELF_ID    = <?= (int) $user['id'] ?>;

  AdminSidebar.init('usuarios', '../', '../../');

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
  let allUsers = [];

  /* ─── helpers ────────────────────────────────────── */
  function escH(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

  function fmtDate(ts) {
    if (!ts) return 'Nunca';
    const d = new Date(ts);
    const now = new Date();
    const diff = now - d;
    if (diff < 60000)          return 'Hace un momento';
    if (diff < 3600000)        return `Hace ${Math.floor(diff/60000)} min`;
    if (diff < 86400000)       return `Hace ${Math.floor(diff/3600000)} h`;
    if (diff < 86400000 * 7)   return `Hace ${Math.floor(diff/86400000)} días`;
    return d.toLocaleDateString('es-MX', { day:'2-digit', month:'short', year:'numeric' });
  }

  function fmtShort(ts) {
    if (!ts) return '—';
    return new Date(ts).toLocaleDateString('es-MX', { day:'2-digit', month:'short' });
  }

  function initials(nombre) {
    return (nombre || '?').trim().split(/\s+/).map(w => w[0]).slice(0,2).join('').toUpperCase();
  }

  /* avatar colors based on name */
  const AVATAR_COLORS = [
    'linear-gradient(135deg,#9d174d,#db2777)',
    'linear-gradient(135deg,#1e40af,#3b82f6)',
    'linear-gradient(135deg,#065f46,#10b981)',
    'linear-gradient(135deg,#92400e,#f59e0b)',
    'linear-gradient(135deg,#4338ca,#6d28d9)',
    'linear-gradient(135deg,#0e7490,#06b6d4)',
    'linear-gradient(135deg,#9a3412,#ea580c)',
    'linear-gradient(135deg,#134e4a,#14b8a6)',
  ];
  function avatarColor(id) { return AVATAR_COLORS[(id || 0) % AVATAR_COLORS.length]; }

  /* ─── cargar usuarios ────────────────────────────── */
  async function loadUsers() {
    try {
      const res  = await fetch('../api/usuarios.php?action=list', { headers: { 'X-CSRF-Token': CSRF_TOKEN } });
      const json = await res.json();
      if (json.ok) {
        allUsers = json.data || [];
        updateBanner();
        filterUsers();
      } else {
        showToast(json.error || 'Error al cargar usuarios', 'error');
      }
    } catch {
      showToast('Error de conexión', 'error');
    }
  }

  function updateBanner() {
    const active = allUsers.filter(u => parseInt(u.activo)).length;
    const lastTs = allUsers.map(u => u.ultimo_acceso).filter(Boolean).sort().reverse()[0];
    document.getElementById('statTotal').textContent  = allUsers.length;
    document.getElementById('statActive').textContent = active;
    document.getElementById('statLast').textContent   = fmtShort(lastTs);
  }

  /* ─── filtrar y renderizar ───────────────────────── */
  function filterUsers() {
    const q      = (document.getElementById('searchInput').value || '').toLowerCase();
    const status = document.getElementById('filterStatus').value;
    const list   = allUsers.filter(u => {
      const matchQ = !q ||
        (u.nombre || '').toLowerCase().includes(q) ||
        (u.username || '').toLowerCase().includes(q) ||
        (u.email_contacto || '').toLowerCase().includes(q);
      const matchS = status === '' || String(u.activo) === status;
      return matchQ && matchS;
    });
    document.getElementById('resultsCount').textContent =
      list.length === allUsers.length
        ? `${allUsers.length} usuario${allUsers.length !== 1 ? 's' : ''}`
        : `${list.length} de ${allUsers.length}`;
    renderGrid(list);
  }

  function renderGrid(list) {
    const grid = document.getElementById('users-grid');
    if (!list.length) {
      grid.innerHTML = `<div style="grid-column:1/-1" class="users-empty">
        <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
        <p>No se encontraron usuarios.</p>
      </div>`;
      return;
    }

    grid.innerHTML = list.map(u => {
      const isSelf   = parseInt(u.id) === SELF_ID;
      const isActive = parseInt(u.activo) === 1;
      const foto     = u.foto ? `../../${escH(u.foto)}` : '';

      return `
        <div class="user-card${isActive ? '' : ' inactive'}" id="ucard-${u.id}">
          <div class="user-card-top">
            <div class="user-avatar" style="background:${avatarColor(u.id)}">
              ${foto ? `<img src="${foto}" alt="${escH(u.nombre)}" onerror="this.style.display='none'">` : ''}
              <span id="uavg-${u.id}" style="${foto ? 'display:none' : ''}">${escH(initials(u.nombre))}</span>
            </div>
            <div class="status-toggle-wrap">
              <span class="status-label ${isActive ? 'active' : 'inactive'}" id="ustlbl-${u.id}">${isActive ? 'Activo' : 'Inactivo'}</span>
              <label class="toggle-switch" title="${isActive ? 'Desactivar usuario' : 'Activar usuario'}">
                <input type="checkbox" ${isActive ? 'checked' : ''} ${isSelf ? 'disabled' : ''}
                  onchange="toggleActivo(${u.id}, this.checked, this)">
                <span class="toggle-track"></span>
              </label>
            </div>
          </div>
          <div class="user-card-body">
            <div class="user-card-name">
              ${escH(u.nombre || u.username)}
              ${isSelf ? '<span class="self-badge"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="8" height="8"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg> Tú</span>' : ''}
            </div>
            <div class="user-card-username">@${escH(u.username)}</div>
            <div class="user-card-info">
              ${u.email_contacto ? `<div class="user-info-row"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg><span title="${escH(u.email_contacto)}">${escH(u.email_contacto)}</span></div>` : ''}
              ${u.telefono ? `<div class="user-info-row"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 8.81 19.79 19.79 0 01.12 2.18 2 2 0 012.1 0h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L6.41 7.5a16 16 0 006.09 6.09l.94-.95a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 14.92z"/></svg><span>${escH(u.telefono)}</span></div>` : ''}
            </div>
            <div class="user-card-access">
              Último acceso: <span>${fmtDate(u.ultimo_acceso)}</span>
            </div>
            <div class="user-card-actions">
              <button class="uca-btn" onclick="openEdit(${u.id})" title="Editar usuario">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Editar
              </button>
              <button class="uca-btn" onclick="openReset(${u.id}, '${escH(u.nombre || u.username)}')" title="Cambiar contraseña">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                Contraseña
              </button>
            </div>
          </div>
        </div>`;
    }).join('');
  }

  /* ─── toggle activo ──────────────────────────────── */
  async function toggleActivo(id, checked, chk) {
    const prev = !checked;
    try {
      const fd = new FormData();
      fd.append('action', 'toggle_activo');
      fd.append('id', id);
      fd.append('activo', checked ? '1' : '0');
      const res  = await fetch('../api/usuarios.php', { method: 'POST', headers: { 'X-CSRF-Token': CSRF_TOKEN }, body: fd });
      const json = await res.json();
      if (json.ok) {
        const u = allUsers.find(u => parseInt(u.id) === id);
        if (u) u.activo = checked ? 1 : 0;
        const card  = document.getElementById(`ucard-${id}`);
        const label = document.getElementById(`ustlbl-${id}`);
        if (card)  { card.classList.toggle('inactive', !checked); }
        if (label) { label.textContent = checked ? 'Activo' : 'Inactivo'; label.className = `status-label ${checked ? 'active' : 'inactive'}`; }
        updateBanner();
        showToast(checked ? 'Usuario activado' : 'Usuario desactivado');
      } else {
        chk.checked = prev;
        showToast(json.error || 'Error al cambiar estado', 'error');
      }
    } catch {
      chk.checked = prev;
      showToast('Error de conexión', 'error');
    }
  }

  /* ─── modal crear ────────────────────────────────── */
  function openCreate() {
    document.getElementById('modalTitle').textContent = 'Nuevo usuario';
    document.getElementById('modalUserId').value = '';
    document.getElementById('mPrimerNombre').value  = '';
    document.getElementById('mApellidoPaterno').value = '';
    document.getElementById('mNombre').value    = '';
    document.getElementById('mUsername').value  = '';
    document.getElementById('mUsername').disabled = false;
    document.getElementById('mEmail').value     = '';
    document.getElementById('mTelefono').value  = '';
    document.getElementById('mPassword').value  = '';
    document.getElementById('passwordSection').style.display = '';
    document.getElementById('usernameHint').style.display = '';
    document.getElementById('userModal').classList.add('open');
    setTimeout(() => document.getElementById('mPrimerNombre').focus(), 80);
  }

  /* ─── modal editar ───────────────────────────────── */
  function openEdit(id) {
    const u = allUsers.find(u => parseInt(u.id) === id);
    if (!u) return;
    document.getElementById('modalTitle').textContent = 'Editar usuario';
    document.getElementById('modalUserId').value       = u.id;
    document.getElementById('mPrimerNombre').value     = u.primer_nombre || '';
    document.getElementById('mApellidoPaterno').value  = u.apellido_paterno || '';
    document.getElementById('mNombre').value           = u.nombre || '';
    document.getElementById('mUsername').value         = u.username || '';
    document.getElementById('mUsername').disabled      = true;
    document.getElementById('mEmail').value            = u.email_contacto || '';
    document.getElementById('mTelefono').value         = u.telefono || '';
    document.getElementById('passwordSection').style.display = 'none';
    document.getElementById('usernameHint').style.display = 'none';
    document.getElementById('userModal').classList.add('open');
    setTimeout(() => document.getElementById('mNombre').focus(), 80);
  }

  function closeModal() { document.getElementById('userModal').classList.remove('open'); }

  /* ─── guardar usuario ────────────────────────────── */
  async function saveUser() {
    const id       = document.getElementById('modalUserId').value;
    const isCreate = !id;
    const nombre   = document.getElementById('mNombre').value.trim();
    const username = document.getElementById('mUsername').value.trim();
    const password = document.getElementById('mPassword').value;

    if (!nombre) { showToast('El nombre completo es requerido', 'error'); return; }
    if (isCreate && !username) { showToast('El nombre de usuario es requerido', 'error'); return; }
    if (isCreate && !password) { showToast('La contraseña es requerida', 'error'); return; }
    if (isCreate && password.length < 8) { showToast('La contraseña debe tener al menos 8 caracteres', 'error'); return; }

    const btn = document.getElementById('modalSaveBtn');
    btn.disabled = true; btn.textContent = 'Guardando…';

    const fd = new FormData();
    fd.append('action', isCreate ? 'create' : 'update');
    if (!isCreate) fd.append('id', id);
    fd.append('nombre',           nombre);
    fd.append('primer_nombre',    document.getElementById('mPrimerNombre').value.trim());
    fd.append('apellido_paterno', document.getElementById('mApellidoPaterno').value.trim());
    fd.append('email_contacto',   document.getElementById('mEmail').value.trim());
    fd.append('telefono',         document.getElementById('mTelefono').value.trim());
    if (isCreate) { fd.append('username', username); fd.append('password', password); }

    try {
      const res  = await fetch('../api/usuarios.php', { method: 'POST', headers: { 'X-CSRF-Token': CSRF_TOKEN }, body: fd });
      const json = await res.json();
      if (json.ok) {
        closeModal();
        showToast(isCreate ? 'Usuario creado correctamente' : 'Datos actualizados');
        await loadUsers();
      } else {
        showToast(json.error || 'Error al guardar', 'error');
      }
    } catch {
      showToast('Error de conexión', 'error');
    } finally {
      btn.disabled = false; btn.textContent = 'Guardar';
    }
  }

  /* ─── reset contraseña ───────────────────────────── */
  function openReset(id, nombre) {
    document.getElementById('resetUserId').value    = id;
    document.getElementById('resetUserName').textContent = `Restableciendo contraseña de: ${nombre}`;
    document.getElementById('resetPwd').value  = '';
    document.getElementById('resetPwd2').value = '';
    document.getElementById('resetModal').classList.add('open');
    setTimeout(() => document.getElementById('resetPwd').focus(), 80);
  }
  function closeReset() { document.getElementById('resetModal').classList.remove('open'); }

  async function doReset() {
    const id   = document.getElementById('resetUserId').value;
    const pwd  = document.getElementById('resetPwd').value;
    const pwd2 = document.getElementById('resetPwd2').value;
    if (pwd.length < 8)  { showToast('Mínimo 8 caracteres', 'error'); return; }
    if (pwd !== pwd2)    { showToast('Las contraseñas no coinciden', 'error'); return; }

    const btn = document.getElementById('resetSaveBtn');
    btn.disabled = true; btn.textContent = 'Guardando…';

    const fd = new FormData();
    fd.append('action', 'reset_password');
    fd.append('id', id);
    fd.append('new_password', pwd);

    try {
      const res  = await fetch('../api/usuarios.php', { method: 'POST', headers: { 'X-CSRF-Token': CSRF_TOKEN }, body: fd });
      const json = await res.json();
      if (json.ok) {
        closeReset();
        showToast('Contraseña restablecida correctamente');
      } else {
        showToast(json.error || 'Error al restablecer', 'error');
      }
    } catch {
      showToast('Error de conexión', 'error');
    } finally {
      btn.disabled = false; btn.textContent = 'Restablecer';
    }
  }

  /* ─── cerrar modales con Escape ──────────────────── */
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') { closeModal(); closeReset(); }
  });
  document.getElementById('userModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
  });
  document.getElementById('resetModal').addEventListener('click', function(e) {
    if (e.target === this) closeReset();
  });

  /* ─── arrancar ───────────────────────────────────── */
  loadUsers();
</script>

</body>
</html>
