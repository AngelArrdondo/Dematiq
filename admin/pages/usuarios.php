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
    .users-table { width:100%; border-collapse:collapse; font-size:.85rem; }
    .users-table th { background:#f0f4ff; padding:10px 14px; text-align:left; font-weight:700; color:var(--accent); font-size:.78rem; text-transform:uppercase; letter-spacing:.04em; border-bottom:2px solid var(--border); }
    .users-table td { padding:11px 14px; border-bottom:1px solid var(--border); color:var(--text); vertical-align:middle; }
    .users-table tr:last-child td { border-bottom:none; }
    .users-table tr:hover td { background:#f8faff; }
    .badge-active   { display:inline-block; padding:2px 9px; border-radius:20px; font-size:.72rem; font-weight:700; background:#e6f4ea; color:#1e7e34; }
    .badge-inactive { display:inline-block; padding:2px 9px; border-radius:20px; font-size:.72rem; font-weight:700; background:#fef0f0; color:#c0392b; }
    .action-btns { display:flex; gap:6px; }
    .modal-overlay { position:fixed; inset:0; background:rgba(0,0,0,.45); backdrop-filter:blur(3px); z-index:9000; display:none; align-items:center; justify-content:center; }
    .modal-overlay.open { display:flex; }
    .modal-card { background:#fff; border-radius:18px; box-shadow:0 20px 60px rgba(0,0,0,.25); width:min(500px,93vw); max-height:90vh; overflow-y:auto; }
    .modal-header { padding:18px 22px 14px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; }
    .modal-header h3 { font-size:1rem; font-weight:700; color:var(--text); }
    .modal-body { padding:20px 22px; display:flex; flex-direction:column; gap:14px; }
    .modal-footer { padding:14px 22px 18px; border-top:1px solid var(--border); display:flex; gap:10px; justify-content:flex-end; }
    .input-group label { font-size:.78rem; font-weight:600; color:var(--text-lt); margin-bottom:4px; display:block; }
    .input-group .form-control { width:100%; }
    .input-row { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
    @media(max-width:520px){.input-row{grid-template-columns:1fr}}
    .table-wrap { overflow-x:auto; }
    .empty-state { text-align:center; padding:40px; color:var(--text-lt); font-size:.9rem; }
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
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
      </button>
      <span class="admin-topbar-title">Usuarios</span>
    </div>
    <div class="user-menu" id="userMenuBtn" role="button" tabindex="0" aria-haspopup="true" aria-expanded="false">
      <div class="admin-avatar" id="topbarAvatar" style="overflow:hidden">
        <?php if ($fotoPath): ?><img src="<?= $fotoPath ?>" alt="Avatar" style="width:100%;height:100%;object-fit:cover;border-radius:50%"><?php else: ?><?= $initials ?><?php endif; ?>
      </div>
      <div class="user-menu-info">
        <span class="user-menu-name" id="topbarName"><?= htmlspecialchars($user['nombre']) ?></span>
        <span class="user-menu-role">Administrador</span>
      </div>
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="user-menu-chevron"><polyline points="6 9 12 15 18 9"/></svg>
      <div class="user-dropdown" id="userDropdown" role="menu">
        <button class="user-dropdown-item" role="menuitem" onclick="openProfileModal()">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          Mi Perfil
        </button>
        <div class="user-dropdown-sep"></div>
        <a class="user-dropdown-item danger" role="menuitem" href="../logout.php">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16,17 21,12 16,7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
          Cerrar sesión
        </a>
      </div>
    </div>
  </header>

  <div class="admin-content">
    <div class="section-header">
      <h1>Gestión de Usuarios</h1>
      <p>Administra las cuentas de acceso al panel.</p>
    </div>

    <div class="admin-card">
      <div class="admin-card-header">
        <div class="admin-card-title">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
          Usuarios del sistema
          <span class="slides-count-badge" id="users-count">…</span>
        </div>
        <button class="btn-admin btn-primary-admin" onclick="openCreate()" style="font-size:.8rem;padding:7px 14px">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Nuevo usuario
        </button>
      </div>
      <div class="table-wrap">
        <table class="users-table">
          <thead>
            <tr>
              <th>Usuario</th><th>Nombre</th><th>Email</th><th>Estado</th><th>Último acceso</th><th>Acciones</th>
            </tr>
          </thead>
          <tbody id="users-tbody">
            <tr><td colspan="6" class="empty-state">Cargando…</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Create / Edit modal -->
<div class="modal-overlay" id="userModal">
  <div class="modal-card">
    <div class="modal-header">
      <h3 id="modalTitle">Nuevo usuario</h3>
      <button class="profile-modal-close" onclick="closeModal()">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="modalUserId" value="">
      <div class="input-row">
        <div class="input-group"><label>Primer nombre *</label><input type="text" class="form-control" id="mPrimerNombre" placeholder="Primer nombre"></div>
        <div class="input-group"><label>Apellido paterno *</label><input type="text" class="form-control" id="mApellidoPaterno" placeholder="Apellido paterno"></div>
      </div>
      <div class="input-row">
        <div class="input-group"><label>Nombre completo *</label><input type="text" class="form-control" id="mNombre" placeholder="Nombre para mostrar"></div>
        <div class="input-group"><label>Usuario *</label><input type="text" class="form-control" id="mUsername" placeholder="usuario123" autocomplete="off"></div>
      </div>
      <div class="input-row">
        <div class="input-group"><label>Email</label><input type="email" class="form-control" id="mEmail" placeholder="correo@ejemplo.com"></div>
        <div class="input-group"><label>Teléfono</label><input type="tel" class="form-control" id="mTelefono" placeholder="+52 55 0000 0000"></div>
      </div>
      <div id="passwordSection">
        <div class="input-group"><label>Contraseña *</label><input type="password" class="form-control" id="mPassword" placeholder="Mínimo 8 caracteres" autocomplete="new-password"></div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn-admin btn-outline-admin" onclick="closeModal()">Cancelar</button>
      <button class="btn-admin btn-primary-admin" id="modalSaveBtn" onclick="saveUser()">Guardar</button>
    </div>
  </div>
</div>

<!-- Reset password modal -->
<div class="modal-overlay" id="resetModal">
  <div class="modal-card">
    <div class="modal-header">
      <h3>Restablecer contraseña</h3>
      <button class="profile-modal-close" onclick="closeReset()">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="resetUserId" value="">
      <p id="resetUserName" style="font-size:.85rem;color:var(--text-lt);margin:0"></p>
      <div class="input-group"><label>Nueva contraseña *</label><input type="password" class="form-control" id="resetPwd" placeholder="Mínimo 8 caracteres" autocomplete="new-password"></div>
      <div class="input-group"><label>Confirmar *</label><input type="password" class="form-control" id="resetPwd2" placeholder="Repite la contraseña"></div>
    </div>
    <div class="modal-footer">
      <button class="btn-admin btn-outline-admin" onclick="closeReset()">Cancelar</button>
      <button class="btn-admin btn-primary-admin" id="resetSaveBtn" onclick="doReset()">Restablecer</button>
    </div>
  </div>
</div>

<?php $profileApiPath = '../api/profile.php'; $fotoPrefix = '../'; require __DIR__ . '/../includes/profile-modal.php'; ?>

<script src="../assets/js/auth.js?v=2"></script>
<script>
  const CSRF_TOKEN = '<?= $csrfToken ?>';
  AdminSidebar.init('usuarios', '../', '../../');

  /* user menu */
  const userMenuBtn = document.getElementById('userMenuBtn');
  userMenuBtn.addEventListener('click', e => { e.stopPropagation(); const o=userMenuBtn.classList.toggle('open'); userMenuBtn.setAttribute('aria-expanded',o); });
  document.addEventListener('click', () => userMenuBtn.classList.remove('open'));

  /* profile modal helpers (same pattern as other pages) */

  loadUsers();
</script>
</body>
</html>
