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
    $fotoPath = $fotoRaw ? htmlspecialchars($fotoRaw) : '';
} catch (PDOException $e) { /* column not yet migrated — ignore */ }
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Nosotros | DEMATIQ Admin</title>
  <link rel="stylesheet" href="../assets/css/admin.css?v=7">
  <link rel="icon" type="image/svg+xml" href="../../assets/images/logos/favicon-d.svg">
</head>
<body>

<script>window.__DB_CONTENT = <?= json_encode($content, JSON_UNESCAPED_UNICODE) ?>;</script>

<div id="sidebar-overlay" class="sidebar-overlay"></div>
<aside class="admin-sidebar"></aside>

<div class="admin-main">

  <header class="admin-topbar">
    <div style="display:flex;align-items:center;gap:12px">
      <button id="sidebar-toggle" class="mobile-menu-toggle" aria-label="Abrir menú de navegación" aria-expanded="false" aria-controls="admin-sidebar">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
        </svg>
      </button>
      <span class="admin-topbar-title">Nosotros</span>
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
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="user-menu-chevron" aria-hidden="true">
        <polyline points="6 9 12 15 18 9"/>
      </svg>
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

    <div class="section-header">
      <h1>Página: Sobre Nosotros</h1>
      <p>Edita el contenido de la sección "Sobre Nosotros".</p>
    </div>

    <div class="admin-card">
      <div class="admin-card-header">
        <div class="admin-card-title">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="2" y="7" width="20" height="15" rx="2"/><polyline points="17,2 12,7 7,2"/>
          </svg>
          Sección Hero
        </div>
      </div>
      <div class="form-group">
        <label>Etiqueta (tag)</label>
        <input type="text" id="hero-tag" value="<?= htmlspecialchars($d['hero']['tag'] ?? '') ?>" placeholder="Conócenos">
      </div>
      <div class="form-group">
        <label>Título principal (H1)</label>
        <input type="text" id="hero-h1" value="<?= htmlspecialchars($d['hero']['h1'] ?? '') ?>" placeholder="Sobre Nosotros">
      </div>
      <div class="form-group">
        <label>Subtítulo</label>
        <textarea id="hero-subtitle" placeholder="Empresa mexicana especializada…"><?= htmlspecialchars($d['hero']['subtitle'] ?? '') ?></textarea>
      </div>
    </div>

    <div class="admin-card">
      <div class="admin-card-header">
        <div class="admin-card-title">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/>
          </svg>
          ¿Quiénes Somos?
        </div>
      </div>
      <div class="form-group">
        <label>Párrafo 1</label>
        <textarea id="qs-p1"><?= htmlspecialchars($d['p1'] ?? '') ?></textarea>
      </div>
      <div class="form-group">
        <label>Párrafo 2</label>
        <textarea id="qs-p2"><?= htmlspecialchars($d['p2'] ?? '') ?></textarea>
      </div>
    </div>

    <div class="admin-card">
      <div class="admin-card-header">
        <div class="admin-card-title">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
          </svg>
          Nuestra Filosofía
        </div>
      </div>
      <div class="form-grid-2">
        <div class="form-group">
          <label>Misión</label>
          <textarea id="mision"><?= htmlspecialchars($d['mision'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
          <label>Visión</label>
          <textarea id="vision"><?= htmlspecialchars($d['vision'] ?? '') ?></textarea>
        </div>
      </div>
      <div class="form-group">
        <label>Valores</label>
        <textarea id="valores"><?= htmlspecialchars($d['valores'] ?? '') ?></textarea>
      </div>
    </div>

    <div class="save-bar">
      <a href="../../pages/corporativo/nosotros.html" target="_blank" class="btn-admin btn-outline-admin">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
        </svg>
        Ver página
      </a>
      <button class="btn-admin btn-primary-admin" onclick="saveNosotros()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/>
          <polyline points="17,21 17,13 7,13 7,21"/><polyline points="7,3 7,8 15,8"/>
        </svg>
        Guardar cambios
      </button>
    </div>

  </div>
</div>

<!-- ── Profile modal ──────────────────────────────────── -->
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
          <button class="profile-avatar-edit-btn" onclick="document.getElementById('avatarInput').click()" title="Cambiar foto" aria-label="Cambiar foto de perfil">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
          </button>
          <input type="file" id="avatarInput" accept="image/jpeg,image/png,image/webp,image/gif" style="display:none" onchange="uploadAvatar(this)">
        </div>
        <div class="profile-field-row">
          <div class="profile-field"><label for="profilePrimerNombre">Primer nombre *</label><input type="text" id="profilePrimerNombre" maxlength="60" placeholder="Primer nombre"></div>
          <div class="profile-field"><label for="profileSegundoNombre">Segundo nombre</label><input type="text" id="profileSegundoNombre" maxlength="60" placeholder="Opcional"></div>
        </div>
        <div class="profile-field-row">
          <div class="profile-field"><label for="profileApellidoPaterno">Apellido paterno *</label><input type="text" id="profileApellidoPaterno" maxlength="60" placeholder="Apellido paterno"></div>
          <div class="profile-field"><label for="profileApellidoMaterno">Apellido materno</label><input type="text" id="profileApellidoMaterno" maxlength="60" placeholder="Opcional"></div>
        </div>
        <div class="profile-field-row">
          <div class="profile-field"><label for="profileEmail">Email de contacto</label><input type="email" id="profileEmail" maxlength="150" placeholder="tucorreo@ejemplo.com"></div>
          <div class="profile-field"><label for="profileTelefono">Teléfono</label><input type="tel" id="profileTelefono" maxlength="30" placeholder="+52 55 0000 0000"></div>
        </div>
        <div class="profile-field"><label for="profileUsername">Usuario</label><input type="text" id="profileUsername" readonly></div>
        <button class="profile-save-btn" id="infoSaveBtn" onclick="saveProfileInfo()">Guardar cambios</button>
      </div>
      <div class="profile-tab-panel" id="tab-security">
        <div class="profile-field"><label for="currentPwd">Contraseña actual</label><input type="password" id="currentPwd" placeholder="••••••••"></div>
        <div class="profile-field"><label for="newPwd">Nueva contraseña</label><input type="password" id="newPwd" placeholder="Mínimo 8 caracteres"></div>
        <div class="profile-field"><label for="confirmPwd">Confirmar contraseña</label><input type="password" id="confirmPwd" placeholder="Repite la nueva contraseña"></div>
        <button class="profile-save-btn" id="pwdSaveBtn" onclick="changePassword()">Cambiar contraseña</button>
      </div>
    </div>
  </div>
</div>

<script src="../assets/js/auth.js?v=2"></script>
<script>
  const CSRF_TOKEN = '<?= $csrfToken ?>';

  AdminSidebar.init('nosotros', '../', '../../');

  /* ── User menu dropdown ─────────────────────────── */
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

  /* ── Profile modal ──────────────────────────────── */
  let profileData = null;

  function openProfileModal() {
    userMenuBtn.classList.remove('open');
    document.getElementById('profileModal').classList.add('open');
    loadProfile();
    document.querySelectorAll('.profile-tab').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.profile-tab-panel').forEach(p => p.classList.remove('active'));
    document.querySelector('.profile-tab[data-tab="info"]').classList.add('active');
    document.getElementById('tab-info').classList.add('active');
  }
  function closeProfileModal() { document.getElementById('profileModal').classList.remove('open'); }
  document.getElementById('profileModal').addEventListener('click', function(e) { if (e.target === this) closeProfileModal(); });
  document.addEventListener('keydown', e => { if (e.key === 'Escape') closeProfileModal(); });
  document.querySelectorAll('.profile-tab').forEach(btn => {
    btn.addEventListener('click', function() {
      document.querySelectorAll('.profile-tab').forEach(b => b.classList.remove('active'));
      document.querySelectorAll('.profile-tab-panel').forEach(p => p.classList.remove('active'));
      this.classList.add('active');
      document.getElementById('tab-' + this.dataset.tab).classList.add('active');
    });
  });

  async function loadProfile() {
    try {
      const res = await fetch('../api/profile.php?action=get');
      const json = await res.json();
      if (!json.ok) return;
      profileData = json.data;
      document.getElementById('profilePrimerNombre').value    = json.data.primer_nombre    || '';
      document.getElementById('profileSegundoNombre').value   = json.data.segundo_nombre   || '';
      document.getElementById('profileApellidoPaterno').value = json.data.apellido_paterno || '';
      document.getElementById('profileApellidoMaterno').value = json.data.apellido_materno || '';
      document.getElementById('profileEmail').value           = json.data.email_contacto   || '';
      document.getElementById('profileTelefono').value        = json.data.telefono         || '';
      document.getElementById('profileUsername').value        = json.data.username;
      setModalAvatar(json.data.foto, json.data.primer_nombre || json.data.nombre);
    } catch { /* silently ignore */ }
  }
  function setModalAvatar(foto, nombre) {
    const el = document.getElementById('modalAvatarCircle');
    el.innerHTML = foto ? `<img src="${foto}?t=${Date.now()}" alt="Avatar">` : (nombre||'?').charAt(0).toUpperCase();
  }
  function setTopbarAvatar(foto, nombre) {
    const el = document.getElementById('topbarAvatar');
    el.innerHTML = foto ? `<img src="${foto}?t=${Date.now()}" alt="Avatar" style="width:100%;height:100%;object-fit:cover;border-radius:50%">` : (nombre||'?').charAt(0).toUpperCase();
  }
  async function saveProfileInfo() {
    const primerNombre = document.getElementById('profilePrimerNombre').value.trim();
    const apellidoPaterno = document.getElementById('profileApellidoPaterno').value.trim();
    if (!primerNombre)    { showToast('El primer nombre es requerido', 'error'); return; }
    if (!apellidoPaterno) { showToast('El apellido paterno es requerido', 'error'); return; }
    const btn = document.getElementById('infoSaveBtn');
    btn.disabled = true; btn.textContent = 'Guardando…';
    const fd = new FormData();
    fd.append('action', 'update_info');
    fd.append('primer_nombre',    primerNombre);
    fd.append('segundo_nombre',   document.getElementById('profileSegundoNombre').value.trim());
    fd.append('apellido_paterno', apellidoPaterno);
    fd.append('apellido_materno', document.getElementById('profileApellidoMaterno').value.trim());
    fd.append('email_contacto',   document.getElementById('profileEmail').value.trim());
    fd.append('telefono',         document.getElementById('profileTelefono').value.trim());
    try {
      const res = await fetch('../api/profile.php', { method: 'POST', headers: { 'X-CSRF-Token': CSRF_TOKEN }, body: fd });
      const json = await res.json();
      if (json.ok) {
        showToast('Perfil actualizado');
        document.getElementById('topbarName').textContent = json.nombre;
        if (!profileData?.foto) setTopbarAvatar(null, primerNombre);
      } else { showToast(json.msg || 'Error al guardar', 'error'); }
    } catch { showToast('Error de conexión', 'error'); }
    finally { btn.disabled = false; btn.textContent = 'Guardar cambios'; }
  }
  async function uploadAvatar(input) {
    if (!input.files[0]) return;
    if (input.files[0].size > 2 * 1024 * 1024) { showToast('Máximo 2 MB', 'error'); input.value = ''; return; }
    const fd = new FormData();
    fd.append('action', 'upload_avatar');
    fd.append('avatar', input.files[0]);
    const editBtn = document.querySelector('.profile-avatar-edit-btn');
    editBtn.style.opacity = '.4';
    try {
      const res = await fetch('../api/profile.php', { method: 'POST', headers: { 'X-CSRF-Token': CSRF_TOKEN }, body: fd });
      const json = await res.json();
      if (json.ok) {
        showToast('Foto actualizada');
        if (profileData) profileData.foto = json.path;
        setModalAvatar(json.path, profileData?.nombre);
        setTopbarAvatar(json.path, profileData?.nombre);
      } else { showToast(json.msg || 'Error al subir', 'error'); }
    } catch { showToast('Error de conexión', 'error'); }
    finally { editBtn.style.opacity = '1'; input.value = ''; }
  }
  async function changePassword() {
    const current = document.getElementById('currentPwd').value;
    const newPwd  = document.getElementById('newPwd').value;
    const confirm = document.getElementById('confirmPwd').value;
    if (!current || !newPwd || !confirm) { showToast('Todos los campos son requeridos', 'error'); return; }
    if (newPwd !== confirm) { showToast('Las contraseñas no coinciden', 'error'); return; }
    if (newPwd.length < 8) { showToast('Mínimo 8 caracteres', 'error'); return; }
    const btn = document.getElementById('pwdSaveBtn');
    btn.disabled = true; btn.textContent = 'Cambiando…';
    const fd = new FormData();
    fd.append('action', 'change_password');
    fd.append('current_password', current);
    fd.append('new_password',     newPwd);
    fd.append('confirm_password', confirm);
    try {
      const res = await fetch('../api/profile.php', { method: 'POST', headers: { 'X-CSRF-Token': CSRF_TOKEN }, body: fd });
      const json = await res.json();
      if (json.ok) {
        showToast('Contraseña cambiada correctamente');
        ['currentPwd','newPwd','confirmPwd'].forEach(id => document.getElementById(id).value = '');
      } else { showToast(json.msg || 'Error', 'error'); }
    } catch { showToast('Error de conexión', 'error'); }
    finally { btn.disabled = false; btn.textContent = 'Cambiar contraseña'; }
  }

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
      if (res && res.ok) {
        showToast('Cambios guardados correctamente');
      } else {
        showToast(res?.error || 'Error al guardar', 'error');
      }
    } catch {
      showToast('Error de conexión', 'error');
    }
  }
</script>

</body>
</html>
