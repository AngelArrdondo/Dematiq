<?php
// Shared profile modal — include after setting $profileApiPath, $fotoPrefix, $fotoPath, $initials
// $fotoPrefix: '' for dashboard, '../../' for pages in /admin/pages/<nombre>/
// $fotoPath must already have $fotoPrefix applied (it's rendered by the topbar before this include)
$_pApi       = $profileApiPath ?? 'api/profile.php';
$_fotoPrefix = $fotoPrefix ?? '';
?>
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

      <!-- Info tab -->
      <div class="profile-tab-panel active" id="tab-info">
        <div class="profile-avatar-wrap">
          <div class="profile-avatar-circle" id="modalAvatarCircle">
            <?php if (!empty($fotoPath)): ?>
              <img src="<?= $fotoPath ?>" alt="Avatar">
            <?php else: ?>
              <?= $initials ?? '?' ?>
            <?php endif; ?>
          </div>
          <button class="profile-avatar-edit-btn" onclick="document.getElementById('avatarInput').click()" title="Cambiar foto" aria-label="Cambiar foto de perfil">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
          </button>
          <input type="file" id="avatarInput" accept="image/jpeg,image/png,image/webp,image/gif" style="display:none" onchange="uploadAvatar(this)">
        </div>

        <div class="profile-field-row">
          <div class="profile-field">
            <label for="profilePrimerNombre">Primer nombre *</label>
            <input type="text" id="profilePrimerNombre" maxlength="60" placeholder="Primer nombre">
          </div>
          <div class="profile-field">
            <label for="profileSegundoNombre">Segundo nombre</label>
            <input type="text" id="profileSegundoNombre" maxlength="60" placeholder="Opcional">
          </div>
        </div>
        <div class="profile-field-row">
          <div class="profile-field">
            <label for="profileApellidoPaterno">Apellido paterno *</label>
            <input type="text" id="profileApellidoPaterno" maxlength="60" placeholder="Apellido paterno">
          </div>
          <div class="profile-field">
            <label for="profileApellidoMaterno">Apellido materno *</label>
            <input type="text" id="profileApellidoMaterno" maxlength="60" placeholder="Apellido materno">
          </div>
        </div>
        <div class="profile-field-row">
          <div class="profile-field">
            <label for="profileEmail">Email de contacto *</label>
            <input type="email" id="profileEmail" maxlength="150" placeholder="tucorreo@ejemplo.com">
          </div>
          <div class="profile-field">
            <label for="profileTelefono">Teléfono *</label>
            <input type="tel" id="profileTelefono" maxlength="30" placeholder="+52 55 0000 0000">
          </div>
        </div>
        <div class="profile-field">
          <label for="profileUsername">Usuario</label>
          <input type="text" id="profileUsername" readonly>
        </div>
        <button class="profile-save-btn" id="infoSaveBtn" onclick="saveProfileInfo()">Guardar cambios</button>
      </div>

      <!-- Security tab -->
      <div class="profile-tab-panel" id="tab-security">
        <div class="profile-field">
          <label for="currentPwd">Contraseña actual</label>
          <input type="password" id="currentPwd" placeholder="••••••••">
        </div>
        <div class="profile-field">
          <label for="newPwd">Nueva contraseña</label>
          <input type="password" id="newPwd" placeholder="Mínimo 8 caracteres">
        </div>
        <div class="profile-field">
          <label for="confirmPwd">Confirmar contraseña</label>
          <input type="password" id="confirmPwd" placeholder="Repite la nueva contraseña">
        </div>
        <button class="profile-save-btn" id="pwdSaveBtn" onclick="changePassword()">Cambiar contraseña</button>
      </div>
    </div>
  </div>
</div>
<script>
(function () {
  var _api        = '<?= htmlspecialchars($_pApi, ENT_QUOTES) ?>';
  var _fotoPrefix = '<?= htmlspecialchars($_fotoPrefix, ENT_QUOTES) ?>';
  var profileData = null;

  window.openProfileModal = function () {
    var mb = document.getElementById('userMenuBtn');
    if (mb) mb.classList.remove('open');
    document.getElementById('profileModal').classList.add('open');
    loadProfile();
    document.querySelectorAll('.profile-tab').forEach(function (b) { b.classList.remove('active'); });
    document.querySelectorAll('.profile-tab-panel').forEach(function (p) { p.classList.remove('active'); });
    document.querySelector('.profile-tab[data-tab="info"]').classList.add('active');
    document.getElementById('tab-info').classList.add('active');
  };

  window.closeProfileModal = function () {
    document.getElementById('profileModal').classList.remove('open');
  };

  document.getElementById('profileModal').addEventListener('click', function (e) {
    if (e.target === this) closeProfileModal();
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeProfileModal();
  });

  document.querySelectorAll('.profile-tab').forEach(function (btn) {
    btn.addEventListener('click', function () {
      document.querySelectorAll('.profile-tab').forEach(function (b) { b.classList.remove('active'); });
      document.querySelectorAll('.profile-tab-panel').forEach(function (p) { p.classList.remove('active'); });
      this.classList.add('active');
      document.getElementById('tab-' + this.dataset.tab).classList.add('active');
    });
  });

  async function loadProfile() {
    try {
      const res  = await fetch(_api + '?action=get');
      const text = await res.text();
      let json; try { json = JSON.parse(text); } catch { return; }
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
    } catch {}
  }

  function setModalAvatar(foto, nombre) {
    const el = document.getElementById('modalAvatarCircle');
    if (foto) {
      el.innerHTML = `<img src="${_fotoPrefix}${foto}?t=${Date.now()}" alt="Avatar">`;
    } else {
      el.textContent = (nombre || '?').charAt(0).toUpperCase();
    }
  }

  window.setTopbarAvatar = function (foto, nombre) {
    const el = document.getElementById('topbarAvatar');
    if (foto) {
      el.innerHTML = `<img src="${_fotoPrefix}${foto}?t=${Date.now()}" alt="Avatar" style="width:100%;height:100%;object-fit:cover;border-radius:50%">`;
    } else {
      el.textContent = (nombre || '?').charAt(0).toUpperCase();
    }
  };

  window.saveProfileInfo = async function () {
    const primerNombre    = document.getElementById('profilePrimerNombre').value.trim();
    const segundoNombre   = document.getElementById('profileSegundoNombre').value.trim();
    const apellidoPaterno = document.getElementById('profileApellidoPaterno').value.trim();
    const apellidoMaterno = document.getElementById('profileApellidoMaterno').value.trim();
    const email           = document.getElementById('profileEmail').value.trim();
    const telefono        = document.getElementById('profileTelefono').value.trim();

    if (!primerNombre)    { showToast('El primer nombre es requerido', 'error'); return; }
    if (!apellidoPaterno) { showToast('El apellido paterno es requerido', 'error'); return; }
    if (!apellidoMaterno) { showToast('El apellido materno es requerido', 'error'); return; }
    if (!email)           { showToast('El email de contacto es requerido', 'error'); return; }
    if (!telefono)        { showToast('El teléfono es requerido', 'error'); return; }

    const btn = document.getElementById('infoSaveBtn');
    btn.disabled = true; btn.textContent = 'Guardando…';

    const fd = new FormData();
    fd.append('action',           'update_info');
    fd.append('primer_nombre',    primerNombre);
    fd.append('segundo_nombre',   segundoNombre);
    fd.append('apellido_paterno', apellidoPaterno);
    fd.append('apellido_materno', apellidoMaterno);
    fd.append('email_contacto',   email);
    fd.append('telefono',         telefono);

    try {
      const res  = await fetch(_api, { method: 'POST', headers: { 'X-CSRF-Token': CSRF_TOKEN }, body: fd });
      const text = await res.text();
      let json; try { json = JSON.parse(text); } catch { showToast('Error del servidor', 'error'); return; }
      if (json.ok) {
        showToast('Perfil actualizado');
        document.getElementById('topbarName').textContent = json.nombre;
        if (profileData) {
          profileData.nombre           = json.nombre;
          profileData.primer_nombre    = primerNombre;
          profileData.apellido_paterno = apellidoPaterno;
        }
        if (!profileData?.foto) setTopbarAvatar(null, primerNombre);
      } else {
        showToast(json.msg || 'Error al guardar', 'error');
      }
    } catch { showToast('Error de conexión', 'error'); }
    finally { btn.disabled = false; btn.textContent = 'Guardar cambios'; }
  };

  window.uploadAvatar = async function (input) {
    if (!input.files[0]) return;
    if (input.files[0].size > 2 * 1024 * 1024) { showToast('Máximo 2 MB', 'error'); input.value = ''; return; }
    const fd = new FormData();
    fd.append('action', 'upload_avatar');
    fd.append('avatar', input.files[0]);
    const editBtn = document.querySelector('.profile-avatar-edit-btn');
    editBtn.style.opacity = '.4';
    try {
      const res  = await fetch(_api, { method: 'POST', headers: { 'X-CSRF-Token': CSRF_TOKEN }, body: fd });
      const json = await res.json();
      if (json.ok) {
        showToast('Foto de perfil actualizada');
        if (profileData) profileData.foto = json.path;
        setModalAvatar(json.path, profileData?.nombre);
        setTopbarAvatar(json.path, profileData?.nombre);
      } else { showToast(json.msg || 'Error al subir', 'error'); }
    } catch { showToast('Error de conexión', 'error'); }
    finally { editBtn.style.opacity = '1'; input.value = ''; }
  };

  window.changePassword = async function () {
    const current = document.getElementById('currentPwd').value;
    const newPwd  = document.getElementById('newPwd').value;
    const confirm = document.getElementById('confirmPwd').value;
    if (!current || !newPwd || !confirm) { showToast('Todos los campos son requeridos', 'error'); return; }
    if (newPwd !== confirm)              { showToast('Las contraseñas no coinciden', 'error');    return; }
    if (newPwd.length < 8)              { showToast('Mínimo 8 caracteres', 'error');             return; }
    const btn = document.getElementById('pwdSaveBtn');
    btn.disabled = true; btn.textContent = 'Cambiando…';
    const fd = new FormData();
    fd.append('action',           'change_password');
    fd.append('current_password', current);
    fd.append('new_password',     newPwd);
    fd.append('confirm_password', confirm);
    try {
      const res  = await fetch(_api, { method: 'POST', headers: { 'X-CSRF-Token': CSRF_TOKEN }, body: fd });
      const json = await res.json();
      if (json.ok) {
        showToast('Contraseña cambiada correctamente');
        document.getElementById('currentPwd').value = '';
        document.getElementById('newPwd').value     = '';
        document.getElementById('confirmPwd').value = '';
      } else { showToast(json.msg || 'Error', 'error'); }
    } catch { showToast('Error de conexión', 'error'); }
    finally { btn.disabled = false; btn.textContent = 'Cambiar contraseña'; }
  };
})();
</script>
