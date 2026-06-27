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
  <title>Máquinas | DEMATIQ Admin</title>
  <link rel="stylesheet" href="../assets/css/admin.css?v=7">
  <link rel="icon" type="image/svg+xml" href="../../assets/images/logos/favicon-d.svg">
  <style>
    .page-selector-bar {
      background: #fff; border: 1.5px solid var(--border); border-radius: 14px;
      padding: 18px 20px; margin-bottom: 20px;
      display: flex; align-items: center; gap: 14px; flex-wrap: wrap;
    }
    .page-selector-bar label { font-size:.85rem; font-weight:600; color:var(--text); white-space:nowrap; }
    .page-selector-bar select {
      flex: 1; min-width: 220px; padding: 8px 12px;
      border: 1.5px solid var(--border); border-radius: 9px;
      font-size: .88rem; color: var(--text); background: #f8faff;
      outline: none; cursor: pointer; transition: border-color .15s;
    }
    .page-selector-bar select:focus { border-color: var(--accent-lt); }
    .meta-fields { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 18px; }
    @media (max-width:600px) { .meta-fields { grid-template-columns: 1fr; } }
    .slides-count-badge {
      display: inline-flex; align-items: center;
      background: rgba(46,107,207,.1); color: var(--accent-lt);
      font-size: .7rem; font-weight: 700; padding: 3px 9px; border-radius: 20px;
      margin-left: 6px; vertical-align: middle;
    }
    .tab-card {
      background: #fff; border: 1.5px solid var(--border); border-radius: 14px;
      overflow: hidden; margin-bottom: 14px; transition: box-shadow .2s, border-color .2s;
    }
    .tab-card:focus-within { border-color: var(--accent-lt); box-shadow: 0 0 0 3px rgba(46,107,207,.08); }
    .tab-card-header {
      display: flex; align-items: center; justify-content: space-between;
      padding: 10px 14px;
      background: linear-gradient(to right, #f0f4ff, #fafcff);
      border-bottom: 1px solid var(--border);
    }
    .tab-badge { display: flex; align-items: center; gap: 10px; }
    .tab-num {
      min-width: 26px; height: 26px; padding: 0 7px;
      background: linear-gradient(135deg, var(--accent), var(--accent-lt));
      color: #fff; border-radius: 7px;
      display: flex; align-items: center; justify-content: center;
      font-size: .72rem; font-weight: 700;
    }
    .tab-header-title {
      font-size: .82rem; font-weight: 600; color: var(--text);
      max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    }
    .tab-header-title.empty { color: var(--text-lt); font-style: italic; font-weight: 400; }
    .tab-body { padding: 16px 20px; }
    .img-list { display: flex; flex-direction: column; gap: 10px; margin-top: 12px; }
    .img-row {
      display: grid; grid-template-columns: 100px 1fr auto; gap: 10px; align-items: center;
      background: #f8faff; border: 1px solid var(--border); border-radius: 10px; padding: 8px 10px;
    }
    .img-thumb {
      width: 100px; height: 60px; border-radius: 7px; overflow: hidden;
      background: #eef1f8; border: 1px dashed #ccd8f0;
      display: flex; align-items: center; justify-content: center;
    }
    .img-thumb img { width:100%; height:100%; object-fit:cover; display:block; }
    .img-thumb .no-img { font-size:.65rem; color:#aab; text-align:center; padding:4px; }
    .img-path-input {
      width: 100%; padding: 6px 9px; border: 1px solid var(--border); border-radius: 7px;
      font-size: .72rem; font-family: monospace; color: var(--text-lt); background: #fff;
      outline: none; transition: border-color .15s;
    }
    .img-path-input:focus { border-color: var(--accent-lt); color: var(--text); }
    .btn-rm {
      background: none; border: none; cursor: pointer; padding: 4px 6px;
      color: #e53e3e; border-radius: 6px; transition: background .15s;
      display: flex; align-items: center;
    }
    .btn-rm:hover { background: #fff0f0; }
    .add-img-btn {
      margin-top: 8px; font-size: .78rem; padding: 5px 12px;
      display: inline-flex; align-items: center; gap: 5px;
    }
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
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
        </svg>
      </button>
      <span class="admin-topbar-title">Máquinas</span>
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
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="user-menu-chevron" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
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
      <h1>Páginas de Máquinas y Soluciones</h1>
      <p>Edita los tabs e imágenes de cada página de soluciones.</p>
    </div>

    <div class="page-selector-bar">
      <label for="pageSelect">Página:</label>
      <select id="pageSelect" onchange="loadPage(this.value)">
        <option value="ensamble">Ensamble</option>
        <option value="maqcontrol">Control de Torque</option>
        <option value="maqprob">Probadoras de Fuga</option>
        <option value="maqinspe">Inspección</option>
        <option value="maclim">Limpieza</option>
        <option value="maqmar">Marcado</option>
        <option value="macrobot">Celdas Robóticas</option>
        <option value="maqindus">Manufactura / Maquinados</option>
      </select>
    </div>

    <div class="admin-card" style="padding:20px">
      <div class="meta-fields">
        <div class="form-group" style="margin:0">
          <label>Título de la página (h1)</label>
          <input type="text" id="fieldTitulo" class="form-control" placeholder="Ej: Máquinas de Control de Torque">
        </div>
        <div class="form-group" style="margin:0">
          <label>Subtítulo de sección (h2)</label>
          <input type="text" id="fieldSubtitulo" class="form-control" placeholder="Ej: Soluciones de Torque">
        </div>
      </div>
    </div>

    <div class="admin-card">
      <div class="admin-card-header">
        <div class="admin-card-title">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/>
          </svg>
          Tabs de la página
          <span class="slides-count-badge" id="tabs-count"></span>
        </div>
        <button class="btn-admin btn-outline-admin" onclick="addTab()" style="font-size:.8rem;padding:6px 12px">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Agregar tab
        </button>
      </div>
      <div id="tabs-container" style="padding:12px 16px"></div>
    </div>

    <div class="save-bar">
      <a id="verPaginaBtn" href="#" target="_blank" class="btn-admin btn-outline-admin">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
        Ver página
      </a>
      <button class="btn-admin btn-primary-admin" onclick="savePage()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17,21 17,13 7,13 7,21"/><polyline points="7,3 7,8 15,8"/></svg>
        Guardar cambios
      </button>
    </div>

  </div>
</div>

<?php $profileApiPath = '../api/profile.php'; $fotoPrefix = '../'; require __DIR__ . '/../includes/profile-modal.php'; ?>

<script src="../assets/js/auth.js?v=2"></script>
<script>
  const CSRF_TOKEN = '<?= $csrfToken ?>';
  AdminSidebar.init('maquinas', '../', '../../');

  const PAGE_URLS = {
    ensamble:   '../../pages/ensamble/ensamble.html',
    maqcontrol: '../../pages/maquinas/maqcontrol.html',
    maqprob:    '../../pages/maquinas/maqprob.html',
    maqinspe:   '../../pages/maquinas/maqinspe.html',
    maclim:     '../../pages/maquinas/maclim.html',
    maqmar:     '../../pages/maquinas/maqmar.html',
    macrobot:   '../../pages/maquinas/macrobot.html',
    maqindus:   '../../pages/manufactura/maqindus.html'
  };

  /* ── User menu ─────────────────────────────────────── */
  const userMenuBtn = document.getElementById('userMenuBtn');
  userMenuBtn.addEventListener('click', function(e) { e.stopPropagation(); const o = this.classList.toggle('open'); this.setAttribute('aria-expanded', o); });
  userMenuBtn.addEventListener('keydown', function(e) { if (e.key==='Enter'||e.key===' '){e.preventDefault();this.click();} if(e.key==='Escape')this.classList.remove('open'); });
  document.addEventListener('click', () => userMenuBtn.classList.remove('open'));

  /* ── Máquinas page logic ───────────────────────────── */
  let currentKey  = 'ensamble';
  let currentData = { titulo: '', subtitulo: '', tabs: [] };

  const trashIcon = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3,6 5,6 21,6"/><path d="M19,6l-1,14a2,2,0,0,1-2,2H8a2,2,0,0,1-2-2L5,6"/><path d="M10,11v6"/><path d="M14,11v6"/></svg>`;
  const pickIcon  = `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17,8 12,3 7,8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>`;
  const plusIcon  = `<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>`;

  function esc(s) { return String(s||'').replace(/"/g,'&quot;'); }

  function loadPage(key) {
    currentKey = key;
    const raw  = CM.get(key) || {};
    currentData = {
      titulo:    raw.titulo    || '',
      subtitulo: raw.subtitulo || '',
      tabs:      (raw.tabs     || []).map(t => ({ nombre: t.nombre || '', images: (t.images || []).slice() }))
    };
    document.getElementById('fieldTitulo').value    = currentData.titulo;
    document.getElementById('fieldSubtitulo').value = currentData.subtitulo;
    const btn = document.getElementById('verPaginaBtn');
    if (btn) btn.href = PAGE_URLS[key] || '#';
    renderTabs();
  }

  function renderTabs() {
    const c = document.getElementById('tabs-container');
    const badge = document.getElementById('tabs-count');
    if (badge) badge.textContent = currentData.tabs.length;
    c.innerHTML = '';
    if (!currentData.tabs.length) {
      c.innerHTML = '<p style="text-align:center;padding:24px;color:var(--text-lt);font-size:.88rem">Sin tabs. Haz clic en <strong>Agregar tab</strong>.</p>';
      return;
    }
    currentData.tabs.forEach((tab, ti) => {
      const div = document.createElement('div');
      div.className = 'tab-card';
      const imgsHTML = tab.images.map((img, ii) => `
        <div class="img-row" id="imgRow_${ti}_${ii}">
          <div class="img-thumb" id="imgThumb_${ti}_${ii}"></div>
          <input type="text" class="img-path-input" value="${esc(img)}"
            oninput="currentData.tabs[${ti}].images[${ii}]=this.value;setThumb(${ti},${ii},this.value)"
            placeholder="assets/images/general/foto.webp">
          <div style="display:flex;flex-direction:column;gap:4px">
            <button type="button" class="btn-admin btn-outline-admin" style="font-size:.7rem;padding:4px 8px;gap:4px"
              onclick="document.getElementById('imgFile_${ti}_${ii}').click()">${pickIcon}</button>
            <input type="file" id="imgFile_${ti}_${ii}" accept="image/*" style="display:none"
              onchange="uploadTabImage(${ti},${ii},this)">
            <button type="button" class="btn-rm" onclick="removeImage(${ti},${ii})" title="Quitar imagen">${trashIcon}</button>
          </div>
        </div>`).join('');

      div.innerHTML = `
        <div class="tab-card-header">
          <div class="tab-badge">
            <span class="tab-num">${ti + 1}</span>
            <span class="tab-header-title${tab.nombre ? '' : ' empty'}" id="tabTitle_${ti}">${esc(tab.nombre) || 'Nuevo tab'}</span>
          </div>
          <button class="btn-rm" onclick="removeTab(${ti})" title="Eliminar tab">${trashIcon}</button>
        </div>
        <div class="tab-body">
          <div class="form-group" style="margin-bottom:12px">
            <label>Nombre del tab</label>
            <input type="text" value="${esc(tab.nombre)}" placeholder="Ej: Máquinas Automáticas"
              oninput="currentData.tabs[${ti}].nombre=this.value;const el=document.getElementById('tabTitle_${ti}');el.textContent=this.value||'Nuevo tab';el.className='tab-header-title'+(this.value?'':' empty')">
          </div>
          <label style="font-size:.8rem;font-weight:600;color:var(--text-lt)">Imágenes</label>
          <div class="img-list" id="imgList_${ti}">${imgsHTML}</div>
          <button type="button" class="btn-admin btn-outline-admin add-img-btn" onclick="addImage(${ti})">${plusIcon} Agregar imagen</button>
        </div>`;
      c.appendChild(div);
      tab.images.forEach((img, ii) => setThumb(ti, ii, img));
    });
  }

  function setThumb(ti, ii, src) {
    const el = document.getElementById('imgThumb_' + ti + '_' + ii);
    if (!el) return;
    if (!src) { el.innerHTML = '<span class="no-img">Sin imagen</span>'; return; }
    const img = document.createElement('img');
    img.src = '../../' + src;
    img.onerror = () => { el.innerHTML = '<span class="no-img">No cargó</span>'; };
    el.innerHTML = '';
    el.appendChild(img);
  }

  function addTab()              { currentData.tabs.push({ nombre: '', images: [] }); renderTabs(); }
  function removeTab(ti)         { currentData.tabs.splice(ti, 1); renderTabs(); }
  function addImage(ti)          { currentData.tabs[ti].images.push(''); renderTabs(); }
  function removeImage(ti, ii)   { currentData.tabs[ti].images.splice(ii, 1); renderTabs(); }

  async function uploadTabImage(ti, ii, input) {
    if (!input.files[0]) return;
    const file = input.files[0];
    if (file.size > 5*1024*1024) { showToast('Máximo 5 MB', 'error'); input.value=''; return; }
    const localURL = URL.createObjectURL(file);
    const thumb = document.getElementById('imgThumb_' + ti + '_' + ii);
    if (thumb) { const img = document.createElement('img'); img.src = localURL; thumb.innerHTML=''; thumb.appendChild(img); }
    const fd = new FormData(); fd.append('image', file); fd.append('folder', 'general');
    try {
      const res  = await fetch('../api/contenido.php', { method:'POST', headers:{'X-CSRF-Token':CSRF_TOKEN}, body:fd });
      const json = await res.json();
      if (json.ok) {
        currentData.tabs[ti].images[ii] = json.path;
        const pi = document.querySelector(`#imgRow_${ti}_${ii} .img-path-input`);
        if (pi) pi.value = json.path;
        showToast('Imagen subida');
      } else { showToast(json.error||'Error al subir','error'); }
    } catch { showToast('Error de conexión','error'); }
    finally { input.value=''; }
  }

  async function savePage() {
    currentData.titulo    = document.getElementById('fieldTitulo').value.trim();
    currentData.subtitulo = document.getElementById('fieldSubtitulo').value.trim();
    try {
      const res = await CM.set(currentKey, currentData);
      if (res && res.ok) { showToast('Cambios guardados correctamente'); }
      else { showToast(res?.error || 'Error al guardar', 'error'); }
    } catch { showToast('Error de conexión', 'error'); }
  }

  loadPage('ensamble');
</script>

</body>
</html>
