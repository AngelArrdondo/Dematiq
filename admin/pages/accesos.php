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

// Load last 200 rows server-side for initial render
$logRows = [];
try {
    $stmt    = $pdo->query(
        'SELECT la.id, la.username, la.ip, la.resultado, la.creado_en,
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
    .log-table { width:100%; border-collapse:collapse; font-size:.82rem; }
    .log-table th { background:#f0f4ff; padding:9px 13px; text-align:left; font-weight:700; color:var(--accent); font-size:.76rem; text-transform:uppercase; letter-spacing:.04em; border-bottom:2px solid var(--border); white-space:nowrap; }
    .log-table td { padding:9px 13px; border-bottom:1px solid var(--border); color:var(--text); vertical-align:middle; }
    .log-table tr:last-child td { border-bottom:none; }
    .log-table tr:hover td { background:#f8faff; }
    .badge-exito    { display:inline-block;padding:2px 8px;border-radius:20px;font-size:.7rem;font-weight:700;background:#e6f4ea;color:#1e7e34; }
    .badge-fallo    { display:inline-block;padding:2px 8px;border-radius:20px;font-size:.7rem;font-weight:700;background:#fef0f0;color:#c0392b; }
    .badge-bloqueado{ display:inline-block;padding:2px 8px;border-radius:20px;font-size:.7rem;font-weight:700;background:#fff8e1;color:#b7700a; }
    .filter-bar { display:flex;align-items:center;gap:10px;flex-wrap:wrap;padding:14px 16px;border-bottom:1px solid var(--border); }
    .filter-bar label { font-size:.78rem;font-weight:600;color:var(--text-lt); }
    .filter-bar select { padding:6px 10px;border:1.5px solid var(--border);border-radius:8px;font-size:.82rem;color:var(--text);background:#f8faff;outline:none;cursor:pointer; }
    .filter-bar select:focus { border-color:var(--accent-lt); }
    .table-wrap { overflow-x:auto; }
    .ua-cell { max-width:220px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; color:var(--text-lt); font-size:.72rem; }
    .ip-cell  { font-family:monospace; font-size:.8rem; white-space:nowrap; }
    .stats-row { display:flex;gap:12px;flex-wrap:wrap;margin-bottom:18px; }
    .stat-chip { background:#fff;border:1.5px solid var(--border);border-radius:12px;padding:12px 18px;min-width:110px;text-align:center; }
    .stat-chip .stat-num { font-size:1.5rem;font-weight:800;color:var(--accent); }
    .stat-chip .stat-lbl { font-size:.7rem;color:var(--text-lt);margin-top:2px; }
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
      <span class="admin-topbar-title">Registro de Accesos</span>
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
      <h1>Registro de Accesos</h1>
      <p>Últimos 200 intentos de inicio de sesión.</p>
    </div>

    <div class="stats-row" id="statsRow"></div>

    <div class="admin-card">
      <div class="filter-bar">
        <label>Filtrar:</label>
        <select id="filterResultado" onchange="renderTable()">
          <option value="">Todos</option>
          <option value="exito">Exitosos</option>
          <option value="fallo">Fallidos</option>
          <option value="bloqueado">Bloqueados</option>
        </select>
        <input type="text" class="form-control" id="filterUser" placeholder="Buscar usuario / IP…"
               oninput="renderTable()" style="max-width:220px;padding:6px 10px;font-size:.82rem">
        <span style="margin-left:auto;font-size:.78rem;color:var(--text-lt)" id="countLabel"></span>
      </div>
      <div class="table-wrap">
        <table class="log-table">
          <thead>
            <tr>
              <th>#</th><th>Usuario</th><th>IP</th><th>Resultado</th><th>Fecha</th><th>User-Agent</th>
            </tr>
          </thead>
          <tbody id="log-tbody"></tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Profile modal -->
<div class="profile-modal-overlay" id="profileModal" role="dialog" aria-modal="true" aria-labelledby="profileModalTitle">
  <div class="profile-modal-card">
    <div class="profile-modal-header">
      <h2 id="profileModalTitle">Mi Perfil</h2>
      <button class="profile-modal-close" onclick="closeProfileModal()">
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
          <button class="profile-avatar-edit-btn" onclick="document.getElementById('avatarInput').click()">
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
  AdminSidebar.init('accesos', '../', '../../');

  const userMenuBtn = document.getElementById('userMenuBtn');
  userMenuBtn.addEventListener('click', e => { e.stopPropagation(); const o=userMenuBtn.classList.toggle('open'); userMenuBtn.setAttribute('aria-expanded',o); });
  document.addEventListener('click', () => userMenuBtn.classList.remove('open'));

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
      if(json.ok){showToast('Perfil actualizado');document.getElementById('topbarName').textContent=json.nombre;}
      else showToast(json.msg||'Error','error');
    }catch{showToast('Error de conexión','error');}finally{btn.disabled=false;btn.textContent='Guardar cambios';}
  }
  async function uploadAvatar(input){
    if(!input.files[0])return; if(input.files[0].size>2*1024*1024){showToast('Máximo 2 MB','error');input.value='';return;}
    const fd=new FormData(); fd.append('action','upload_avatar'); fd.append('avatar',input.files[0]);
    const eb=document.querySelector('.profile-avatar-edit-btn'); eb.style.opacity='.4';
    try{ const res=await fetch('../api/profile.php',{method:'POST',headers:{'X-CSRF-Token':CSRF_TOKEN},body:fd}); const json=await res.json();
      if(json.ok){showToast('Foto actualizada');if(profileData)profileData.foto=json.path;setModalAvatar(json.path,profileData?.nombre);setTopbarAvatar(json.path,profileData?.nombre);}
      else showToast(json.msg||'Error','error');
    }catch{showToast('Error de conexión','error');}finally{eb.style.opacity='1';input.value='';}
  }
  async function changePassword(){
    const cur=document.getElementById('currentPwd').value, np=document.getElementById('newPwd').value, cp=document.getElementById('confirmPwd').value;
    if(!cur||!np||!cp){showToast('Todos los campos son requeridos','error');return;} if(np!==cp){showToast('Las contraseñas no coinciden','error');return;} if(np.length<8){showToast('Mínimo 8 caracteres','error');return;}
    const btn=document.getElementById('pwdSaveBtn'); btn.disabled=true; btn.textContent='Cambiando…';
    const fd=new FormData(); fd.append('action','change_password'); fd.append('current_password',cur); fd.append('new_password',np); fd.append('confirm_password',cp);
    try{ const res=await fetch('../api/profile.php',{method:'POST',headers:{'X-CSRF-Token':CSRF_TOKEN},body:fd}); const json=await res.json();
      if(json.ok){showToast('Contraseña cambiada');['currentPwd','newPwd','confirmPwd'].forEach(id=>document.getElementById(id).value='');}
      else showToast(json.msg||'Error','error');
    }catch{showToast('Error de conexión','error');}finally{btn.disabled=false;btn.textContent='Cambiar contraseña';}
  }
  document.addEventListener('keydown', e => { if(e.key==='Escape') closeProfileModal(); });

  /* ── Access log ───────────────────────────────────────────── */
  const RAW = <?= json_encode($logRows, JSON_UNESCAPED_UNICODE) ?>;

  function badge(r) {
    return `<span class="badge-${r}">${r}</span>`;
  }
  function fmt(ts) {
    if (!ts) return '—';
    return new Date(ts).toLocaleString('es-MX', {day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'});
  }

  function renderStats() {
    const counts = { exito: 0, fallo: 0, bloqueado: 0 };
    RAW.forEach(r => { if (counts[r.resultado] !== undefined) counts[r.resultado]++; });
    document.getElementById('statsRow').innerHTML = [
      { lbl:'Total', num: RAW.length, color:'var(--accent)' },
      { lbl:'Exitosos', num: counts.exito, color:'#1e7e34' },
      { lbl:'Fallidos', num: counts.fallo, color:'#c0392b' },
      { lbl:'Bloqueados', num: counts.bloqueado, color:'#b7700a' },
    ].map(s => `<div class="stat-chip"><div class="stat-num" style="color:${s.color}">${s.num}</div><div class="stat-lbl">${s.lbl}</div></div>`).join('');
  }

  function renderTable() {
    const filtR = document.getElementById('filterResultado').value;
    const filtU = (document.getElementById('filterUser').value || '').toLowerCase();
    const rows  = RAW.filter(r => {
      if (filtR && r.resultado !== filtR) return false;
      if (filtU && !(r.username||'').toLowerCase().includes(filtU) && !(r.ip||'').includes(filtU)) return false;
      return true;
    });
    document.getElementById('countLabel').textContent = `Mostrando ${rows.length} de ${RAW.length}`;
    const tbody = document.getElementById('log-tbody');
    if (!rows.length) { tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:30px;color:var(--text-lt)">Sin resultados</td></tr>'; return; }
    tbody.innerHTML = rows.map((r, i) => `
      <tr>
        <td style="color:var(--text-lt);font-size:.75rem">${r.id}</td>
        <td><strong>${escH(r.username||'—')}</strong>${r.nombre?`<br><span style="font-size:.72rem;color:var(--text-lt)">${escH(r.nombre)}</span>`:''}</td>
        <td class="ip-cell">${escH(r.ip)}</td>
        <td>${badge(r.resultado)}</td>
        <td style="white-space:nowrap;font-size:.78rem">${fmt(r.creado_en)}</td>
        <td class="ua-cell" title="${escH(r.user_agent||'')}">${escH(r.user_agent||'—')}</td>
      </tr>`).join('');
  }
  function escH(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

  renderStats();
  renderTable();
</script>
</body>
</html>
