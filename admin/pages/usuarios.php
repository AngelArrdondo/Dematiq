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
    $fotoPath = $fotoRaw ? htmlspecialchars($fotoRaw) : '';
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

<!-- Profile modal (same structure as other pages) -->
<div class="profile-modal-overlay" id="profileModal" role="dialog" aria-modal="true" aria-labelledby="profileModalTitle">
  <div class="profile-modal-card">
    <div class="profile-modal-header">
      <h2 id="profileModalTitle">Mi Perfil</h2>
      <button class="profile-modal-close" onclick="closeProfileModal()" aria-label="Cerrar">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="profile-modal-body">
      <div class="profile-tabs">
        <button class="profile-tab active" data-tab="info">Información</button>
        <button class="profile-tab" data-tab="security">Contraseña</button>
      </div>
      <div class="profile-tab-panel active" id="tab-info">
        <div class="profile-avatar-wrap">
          <div class="profile-avatar-circle" id="modalAvatarCircle">
            <?php if ($fotoPath): ?><img src="<?= $fotoPath ?>" alt="Avatar"><?php else: ?><?= $initials ?><?php endif; ?>
          </div>
          <button class="profile-avatar-edit-btn" onclick="document.getElementById('avatarInput').click()" title="Cambiar foto">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
          </button>
          <input type="file" id="avatarInput" accept="image/jpeg,image/png,image/webp,image/gif" style="display:none" onchange="uploadAvatar(this)">
        </div>
        <div class="profile-field-row">
          <div class="profile-field"><label>Primer nombre *</label><input type="text" id="profilePrimerNombre" maxlength="60"></div>
          <div class="profile-field"><label>Segundo nombre</label><input type="text" id="profileSegundoNombre" maxlength="60"></div>
        </div>
        <div class="profile-field-row">
          <div class="profile-field"><label>Apellido paterno *</label><input type="text" id="profileApellidoPaterno" maxlength="60"></div>
          <div class="profile-field"><label>Apellido materno</label><input type="text" id="profileApellidoMaterno" maxlength="60"></div>
        </div>
        <div class="profile-field-row">
          <div class="profile-field"><label>Email</label><input type="email" id="profileEmail" maxlength="150"></div>
          <div class="profile-field"><label>Teléfono</label><input type="tel" id="profileTelefono" maxlength="30"></div>
        </div>
        <div class="profile-field"><label>Usuario</label><input type="text" id="profileUsername" readonly></div>
        <button class="profile-save-btn" id="infoSaveBtn" onclick="saveProfileInfo()">Guardar cambios</button>
      </div>
      <div class="profile-tab-panel" id="tab-security">
        <div class="profile-field"><label>Contraseña actual</label><input type="password" id="currentPwd"></div>
        <div class="profile-field"><label>Nueva contraseña</label><input type="password" id="newPwd"></div>
        <div class="profile-field"><label>Confirmar</label><input type="password" id="confirmPwd"></div>
        <button class="profile-save-btn" id="pwdSaveBtn" onclick="changePassword()">Cambiar contraseña</button>
      </div>
    </div>
  </div>
</div>

<script src="../assets/js/auth.js?v=2"></script>
<script>
  const CSRF_TOKEN = '<?= $csrfToken ?>';
  AdminSidebar.init('usuarios', '../', '../../');

  /* user menu */
  const userMenuBtn = document.getElementById('userMenuBtn');
  userMenuBtn.addEventListener('click', e => { e.stopPropagation(); const o=userMenuBtn.classList.toggle('open'); userMenuBtn.setAttribute('aria-expanded',o); });
  document.addEventListener('click', () => userMenuBtn.classList.remove('open'));

  /* profile modal helpers (same pattern as other pages) */
  let profileData = null;
  function openProfileModal(){ userMenuBtn.classList.remove('open'); document.getElementById('profileModal').classList.add('open'); loadProfile(); }
  function closeProfileModal(){ document.getElementById('profileModal').classList.remove('open'); }
  document.getElementById('profileModal').addEventListener('click', e => { if(e.target===document.getElementById('profileModal')) closeProfileModal(); });
  document.querySelectorAll('.profile-tab').forEach(btn => btn.addEventListener('click', function(){
    document.querySelectorAll('.profile-tab').forEach(b=>b.classList.remove('active'));
    document.querySelectorAll('.profile-tab-panel').forEach(p=>p.classList.remove('active'));
    this.classList.add('active'); document.getElementById('tab-'+this.dataset.tab).classList.add('active');
  }));
  async function loadProfile(){
    try{ const res=await fetch('../api/profile.php?action=get'); const json=await res.json(); if(!json.ok)return; profileData=json.data;
      document.getElementById('profilePrimerNombre').value=json.data.primer_nombre||''; document.getElementById('profileSegundoNombre').value=json.data.segundo_nombre||'';
      document.getElementById('profileApellidoPaterno').value=json.data.apellido_paterno||''; document.getElementById('profileApellidoMaterno').value=json.data.apellido_materno||'';
      document.getElementById('profileEmail').value=json.data.email_contacto||''; document.getElementById('profileTelefono').value=json.data.telefono||'';
      document.getElementById('profileUsername').value=json.data.username;
      setModalAvatar(json.data.foto,json.data.primer_nombre||json.data.nombre);
    }catch{}
  }
  function setModalAvatar(foto,nombre){ const el=document.getElementById('modalAvatarCircle'); el.innerHTML=foto?`<img src="${foto}?t=${Date.now()}" alt="Avatar">`:((nombre||'?').charAt(0).toUpperCase()); }
  function setTopbarAvatar(foto,nombre){ const el=document.getElementById('topbarAvatar'); el.innerHTML=foto?`<img src="${foto}?t=${Date.now()}" alt="Avatar" style="width:100%;height:100%;object-fit:cover;border-radius:50%">`:((nombre||'?').charAt(0).toUpperCase()); }
  async function saveProfileInfo(){
    const pn=document.getElementById('profilePrimerNombre').value.trim(), ap=document.getElementById('profileApellidoPaterno').value.trim();
    if(!pn){showToast('El primer nombre es requerido','error');return;} if(!ap){showToast('El apellido paterno es requerido','error');return;}
    const btn=document.getElementById('infoSaveBtn'); btn.disabled=true; btn.textContent='Guardando…';
    const fd=new FormData(); fd.append('action','update_info'); fd.append('primer_nombre',pn); fd.append('segundo_nombre',document.getElementById('profileSegundoNombre').value.trim());
    fd.append('apellido_paterno',ap); fd.append('apellido_materno',document.getElementById('profileApellidoMaterno').value.trim());
    fd.append('email_contacto',document.getElementById('profileEmail').value.trim()); fd.append('telefono',document.getElementById('profileTelefono').value.trim());
    try{ const res=await fetch('../api/profile.php',{method:'POST',headers:{'X-CSRF-Token':CSRF_TOKEN},body:fd}); const json=await res.json();
      if(json.ok){showToast('Perfil actualizado');document.getElementById('topbarName').textContent=json.nombre;if(!profileData?.foto)setTopbarAvatar(null,pn);}
      else showToast(json.msg||'Error al guardar','error');
    }catch{showToast('Error de conexión','error');}finally{btn.disabled=false;btn.textContent='Guardar cambios';}
  }
  async function uploadAvatar(input){
    if(!input.files[0])return; if(input.files[0].size>2*1024*1024){showToast('Máximo 2 MB','error');input.value='';return;}
    const fd=new FormData(); fd.append('action','upload_avatar'); fd.append('avatar',input.files[0]);
    const eb=document.querySelector('.profile-avatar-edit-btn'); eb.style.opacity='.4';
    try{ const res=await fetch('../api/profile.php',{method:'POST',headers:{'X-CSRF-Token':CSRF_TOKEN},body:fd}); const json=await res.json();
      if(json.ok){showToast('Foto actualizada');if(profileData)profileData.foto=json.path;setModalAvatar(json.path,profileData?.nombre);setTopbarAvatar(json.path,profileData?.nombre);}
      else showToast(json.msg||'Error al subir','error');
    }catch{showToast('Error de conexión','error');}finally{eb.style.opacity='1';input.value='';}
  }
  async function changePassword(){
    const cur=document.getElementById('currentPwd').value, np=document.getElementById('newPwd').value, cp=document.getElementById('confirmPwd').value;
    if(!cur||!np||!cp){showToast('Todos los campos son requeridos','error');return;} if(np!==cp){showToast('Las contraseñas no coinciden','error');return;} if(np.length<8){showToast('Mínimo 8 caracteres','error');return;}
    const btn=document.getElementById('pwdSaveBtn'); btn.disabled=true; btn.textContent='Cambiando…';
    const fd=new FormData(); fd.append('action','change_password'); fd.append('current_password',cur); fd.append('new_password',np); fd.append('confirm_password',cp);
    try{ const res=await fetch('../api/profile.php',{method:'POST',headers:{'X-CSRF-Token':CSRF_TOKEN},body:fd}); const json=await res.json();
      if(json.ok){showToast('Contraseña cambiada correctamente');['currentPwd','newPwd','confirmPwd'].forEach(id=>document.getElementById(id).value='');}
      else showToast(json.msg||'Error','error');
    }catch{showToast('Error de conexión','error');}finally{btn.disabled=false;btn.textContent='Cambiar contraseña';}
  }

  /* ── Usuarios logic ───────────────────────────────────────── */
  let usersData = [];

  function fmt(ts) {
    if (!ts) return '—';
    const d = new Date(ts); return d.toLocaleDateString('es-MX', { day:'2-digit', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit' });
  }

  async function loadUsers() {
    try {
      const res  = await fetch('../api/usuarios.php?action=list', { headers: {'X-CSRF-Token': CSRF_TOKEN} });
      const json = await res.json();
      if (!json.ok) { showToast('Error al cargar usuarios', 'error'); return; }
      usersData = json.data;
      document.getElementById('users-count').textContent = json.data.length;
      const tbody = document.getElementById('users-tbody');
      if (!json.data.length) { tbody.innerHTML = '<tr><td colspan="6" class="empty-state">Sin usuarios</td></tr>'; return; }
      tbody.innerHTML = json.data.map(u => `
        <tr>
          <td><strong>${escH(u.username)}</strong></td>
          <td>${escH(u.nombre)}</td>
          <td>${escH(u.email_contacto||'—')}</td>
          <td><span class="${u.activo=='1'?'badge-active':'badge-inactive'}">${u.activo=='1'?'Activo':'Inactivo'}</span></td>
          <td>${fmt(u.ultimo_acceso)}</td>
          <td>
            <div class="action-btns">
              <button class="btn-admin btn-outline-admin" style="font-size:.75rem;padding:5px 10px" onclick='openEdit(${JSON.stringify(u)})'>Editar</button>
              <button class="btn-admin btn-outline-admin" style="font-size:.75rem;padding:5px 10px" onclick='openReset(${u.id},"${escH(u.nombre)}")'>Reset pwd</button>
              <button class="btn-admin ${u.activo=='1'?'btn-outline-admin':'btn-primary-admin'}" style="font-size:.75rem;padding:5px 10px" onclick="toggleActivo(${u.id},${u.activo=='1'?0:1})">${u.activo=='1'?'Desactivar':'Activar'}</button>
            </div>
          </td>
        </tr>`).join('');
    } catch { showToast('Error de conexión', 'error'); }
  }

  function escH(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

  function openCreate() {
    document.getElementById('modalTitle').textContent = 'Nuevo usuario';
    document.getElementById('modalUserId').value = '';
    ['mPrimerNombre','mApellidoPaterno','mNombre','mUsername','mEmail','mTelefono','mPassword'].forEach(id => document.getElementById(id).value='');
    document.getElementById('mUsername').disabled = false;
    document.getElementById('passwordSection').style.display = '';
    document.getElementById('userModal').classList.add('open');
  }

  function openEdit(u) {
    document.getElementById('modalTitle').textContent = 'Editar usuario';
    document.getElementById('modalUserId').value      = u.id;
    document.getElementById('mPrimerNombre').value    = u.primer_nombre || '';
    document.getElementById('mApellidoPaterno').value = u.apellido_paterno || '';
    document.getElementById('mNombre').value          = u.nombre;
    document.getElementById('mUsername').value        = u.username;
    document.getElementById('mUsername').disabled     = true;
    document.getElementById('mEmail').value           = u.email_contacto || '';
    document.getElementById('mTelefono').value        = u.telefono || '';
    document.getElementById('passwordSection').style.display = 'none';
    document.getElementById('userModal').classList.add('open');
  }

  function closeModal() { document.getElementById('userModal').classList.remove('open'); }

  async function saveUser() {
    const id = document.getElementById('modalUserId').value;
    const fd = new FormData();
    fd.append('action', id ? 'update' : 'create');
    if (id) fd.append('id', id);
    fd.append('nombre',          document.getElementById('mNombre').value.trim());
    fd.append('primer_nombre',   document.getElementById('mPrimerNombre').value.trim());
    fd.append('apellido_paterno',document.getElementById('mApellidoPaterno').value.trim());
    fd.append('email_contacto',  document.getElementById('mEmail').value.trim());
    fd.append('telefono',        document.getElementById('mTelefono').value.trim());
    if (!id) {
      fd.append('username', document.getElementById('mUsername').value.trim());
      fd.append('password', document.getElementById('mPassword').value);
    }
    const btn = document.getElementById('modalSaveBtn'); btn.disabled=true; btn.textContent='Guardando…';
    try {
      const res  = await fetch('../api/usuarios.php', { method:'POST', headers:{'X-CSRF-Token':CSRF_TOKEN}, body:fd });
      const json = await res.json();
      if (json.ok) { showToast(id ? 'Usuario actualizado' : 'Usuario creado'); closeModal(); loadUsers(); }
      else showToast(json.error||'Error', 'error');
    } catch { showToast('Error de conexión','error'); }
    finally { btn.disabled=false; btn.textContent='Guardar'; }
  }

  function openReset(id, nombre) {
    document.getElementById('resetUserId').value = id;
    document.getElementById('resetUserName').textContent = 'Usuario: ' + nombre;
    document.getElementById('resetPwd').value = '';
    document.getElementById('resetPwd2').value = '';
    document.getElementById('resetModal').classList.add('open');
  }
  function closeReset() { document.getElementById('resetModal').classList.remove('open'); }

  async function doReset() {
    const id = document.getElementById('resetUserId').value;
    const p1 = document.getElementById('resetPwd').value;
    const p2 = document.getElementById('resetPwd2').value;
    if (!p1 || p1 !== p2) { showToast('Las contraseñas no coinciden','error'); return; }
    if (p1.length < 8)    { showToast('Mínimo 8 caracteres','error'); return; }
    const btn = document.getElementById('resetSaveBtn'); btn.disabled=true; btn.textContent='Guardando…';
    const fd=new FormData(); fd.append('action','reset_password'); fd.append('id',id); fd.append('new_password',p1);
    try {
      const res=await fetch('../api/usuarios.php',{method:'POST',headers:{'X-CSRF-Token':CSRF_TOKEN},body:fd});
      const json=await res.json();
      if(json.ok){showToast('Contraseña restablecida');closeReset();}
      else showToast(json.error||'Error','error');
    }catch{showToast('Error de conexión','error');}
    finally{btn.disabled=false;btn.textContent='Restablecer';}
  }

  async function toggleActivo(id, newVal) {
    const fd=new FormData(); fd.append('action','toggle_activo'); fd.append('id',id); fd.append('activo',newVal);
    try {
      const res=await fetch('../api/usuarios.php',{method:'POST',headers:{'X-CSRF-Token':CSRF_TOKEN},body:fd});
      const json=await res.json();
      if(json.ok){showToast(newVal?'Usuario activado':'Usuario desactivado');loadUsers();}
      else showToast(json.error||'Error','error');
    }catch{showToast('Error de conexión','error');}
  }

  document.getElementById('userModal').addEventListener('click', e => { if(e.target===document.getElementById('userModal')) closeModal(); });
  document.getElementById('resetModal').addEventListener('click', e => { if(e.target===document.getElementById('resetModal')) closeReset(); });
  document.addEventListener('keydown', e => { if(e.key==='Escape'){closeModal();closeReset();closeProfileModal();} });

  loadUsers();
</script>
</body>
</html>
