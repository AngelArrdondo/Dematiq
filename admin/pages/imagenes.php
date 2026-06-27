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
    .folder-tabs { display:flex; gap:4px; padding:14px 16px 0; border-bottom:1px solid var(--border); }
    .folder-tab  { padding:8px 18px; border-radius:10px 10px 0 0; font-size:.82rem; font-weight:600; color:var(--text-lt); cursor:pointer; background:none; border:none; border-bottom:3px solid transparent; transition:color .15s,border-color .15s; }
    .folder-tab.active { color:var(--accent); border-bottom-color:var(--accent); }
    .folder-tab:hover:not(.active) { color:var(--text); }
    .folder-toolbar { display:flex;align-items:center;gap:10px;padding:12px 16px;border-bottom:1px solid var(--border);flex-wrap:wrap; }
    .folder-toolbar span { font-size:.8rem;color:var(--text-lt); }
    .img-grid { display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:14px;padding:16px; }
    .img-item { background:#fff;border:1.5px solid var(--border);border-radius:12px;overflow:hidden;transition:box-shadow .2s,border-color .2s;position:relative; }
    .img-item:hover { box-shadow:0 4px 18px rgba(0,0,0,.1);border-color:var(--accent-lt); }
    .img-preview { width:100%;height:110px;object-fit:cover;display:block;background:#f0f4ff; }
    .img-preview.err { object-fit:contain;padding:14px;opacity:.4; }
    .img-name { font-size:.72rem;color:var(--text);padding:7px 10px 4px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-weight:600; }
    .img-size { font-size:.67rem;color:var(--text-lt);padding:0 10px 7px; }
    .img-del-btn { position:absolute;top:6px;right:6px;width:26px;height:26px;border-radius:7px;border:none;background:rgba(220,50,50,.85);color:#fff;cursor:pointer;display:flex;align-items:center;justify-content:center;opacity:0;transition:opacity .15s; }
    .img-item:hover .img-del-btn { opacity:1; }
    .img-del-btn:hover { background:#c0392b; }
    .upload-zone { border:2px dashed var(--border);border-radius:14px;padding:32px;text-align:center;color:var(--text-lt);cursor:pointer;transition:border-color .2s,background .2s;margin:12px 16px; }
    .upload-zone:hover, .upload-zone.drag { border-color:var(--accent-lt);background:#f0f4ff; }
    .upload-zone svg { display:block;margin:0 auto 10px; }
    .upload-zone p { font-size:.85rem;margin:0; }
    .upload-zone input[type=file] { display:none; }
    .upload-progress { margin:4px 16px;height:4px;border-radius:4px;background:#e0e7ff;overflow:hidden;display:none; }
    .upload-progress-bar { height:100%;background:var(--accent);width:0%;transition:width .3s; }
    .empty-folder { text-align:center;padding:50px;color:var(--text-lt);font-size:.88rem; }
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
      <span class="admin-topbar-title">Imágenes</span>
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
      <h1>Gestión de Imágenes</h1>
      <p>Sube o elimina imágenes del sitio. Máximo 5 MB por archivo.</p>
    </div>

    <div class="admin-card" style="padding:0;overflow:hidden">

      <!-- Folder tabs -->
      <div class="folder-tabs">
        <button class="folder-tab active" onclick="switchFolder('general', this)">General</button>
        <button class="folder-tab" onclick="switchFolder('partners', this)">Partners</button>
        <button class="folder-tab" onclick="switchFolder('avatares', this)">Avatares</button>
      </div>

      <!-- Toolbar -->
      <div class="folder-toolbar">
        <button class="btn-admin btn-primary-admin" onclick="document.getElementById('uploadInput').click()" style="font-size:.8rem;padding:7px 14px">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17,8 12,3 7,8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
          Subir imagen
        </button>
        <input type="file" id="uploadInput" accept="image/*" multiple style="display:none" onchange="uploadFiles(this.files)">
        <span id="folderCountLabel" style="margin-left:auto"></span>
      </div>

      <!-- Upload progress -->
      <div class="upload-progress" id="uploadProgress"><div class="upload-progress-bar" id="uploadProgressBar"></div></div>

      <!-- Drop zone -->
      <div class="upload-zone" id="dropZone" onclick="document.getElementById('uploadInput').click()">
        <input type="file" accept="image/*" multiple>
        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="opacity:.4"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17,8 12,3 7,8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
        <p>Arrastra imágenes aquí o haz clic para seleccionar</p>
        <p style="font-size:.75rem;margin-top:5px">JPG, PNG, WebP, GIF · máx 5 MB</p>
      </div>

      <!-- Image grid -->
      <div class="img-grid" id="imgGrid"></div>

    </div>
  </div>
</div>

<?php $profileApiPath = '../api/profile.php'; $fotoPrefix = '../'; require __DIR__ . '/../includes/profile-modal.php'; ?>

<script src="../assets/js/auth.js?v=2"></script>
<script>
  const CSRF_TOKEN = '<?= $csrfToken ?>';
  AdminSidebar.init('imagenes', '../', '../../');

  const userMenuBtn = document.getElementById('userMenuBtn');
  userMenuBtn.addEventListener('click', e => { e.stopPropagation(); const o=userMenuBtn.classList.toggle('open'); userMenuBtn.setAttribute('aria-expanded',o); });
  document.addEventListener('click', () => userMenuBtn.classList.remove('open'));


  /* ── Images logic ─────────────────────────────────────────── */
  let imagesData   = {};
  let currentFolder= 'general';

  function fmtSize(bytes) {
    if (!bytes) return '0 B';
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1048576) return (bytes/1024).toFixed(1) + ' KB';
    return (bytes/1048576).toFixed(1) + ' MB';
  }

  function switchFolder(key, tab) {
    currentFolder = key;
    document.querySelectorAll('.folder-tab').forEach(t => t.classList.remove('active'));
    tab.classList.add('active');
    renderGrid();
  }

  function renderGrid() {
    const imgs  = imagesData[currentFolder] || [];
    const grid  = document.getElementById('imgGrid');
    document.getElementById('folderCountLabel').textContent = imgs.length + ' imagen' + (imgs.length===1?'':'es');
    if (!imgs.length) {
      grid.innerHTML = '<div class="empty-folder" style="grid-column:1/-1">Sin imágenes en esta carpeta</div>';
      return;
    }
    grid.innerHTML = imgs.map((img, i) => `
      <div class="img-item" id="imgItem_${i}">
        <button class="img-del-btn" onclick="deleteImage('${img.path.replace(/'/g,"\\'")}',${i})" title="Eliminar">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3,6 5,6 21,6"/><path d="M19,6l-1,14a2,2,0,0,1-2,2H8a2,2,0,0,1-2-2L5,6"/></svg>
        </button>
        <img class="img-preview" src="${img.path}?t=${Date.now()}" alt="${img.name}" loading="lazy" onerror="this.classList.add('err')">
        <div class="img-name" title="${img.name}">${img.name}</div>
        <div class="img-size">${fmtSize(img.size)}</div>
      </div>`).join('');
  }

  async function loadImages() {
    try {
      const res  = await fetch('../api/imagenes.php?action=list', { headers:{'X-CSRF-Token':CSRF_TOKEN} });
      const json = await res.json();
      if (!json.ok) { showToast('Error al cargar imágenes','error'); return; }
      imagesData = json.data;
      renderGrid();
    } catch { showToast('Error de conexión','error'); }
  }

  async function deleteImage(path, idx) {
    if (!confirm('¿Eliminar esta imagen permanentemente?')) return;
    const fd=new FormData(); fd.append('action','delete'); fd.append('path',path);
    try {
      const res  = await fetch('../api/imagenes.php', { method:'POST', headers:{'X-CSRF-Token':CSRF_TOKEN}, body:fd });
      const json = await res.json();
      if (json.ok) {
        showToast('Imagen eliminada');
        const arr = imagesData[currentFolder];
        const pos = arr.findIndex(img => img.path === path);
        if (pos > -1) arr.splice(pos, 1);
        renderGrid();
      } else showToast(json.error||'Error','error');
    } catch { showToast('Error de conexión','error'); }
  }

  async function uploadFiles(files) {
    if (!files || !files.length) return;
    const bar = document.getElementById('uploadProgress');
    const fill = document.getElementById('uploadProgressBar');
    bar.style.display = 'block';
    let done = 0;
    const total = files.length;

    // Map folder key to upload folder name expected by contenido.php
    const folderMap = { general:'general', partners:'partners', avatares:'avatares' };
    const folderName = folderMap[currentFolder] || 'general';

    for (const file of files) {
      if (file.size > 5*1024*1024) { showToast(`${file.name}: máx 5 MB`, 'error'); done++; continue; }
      const fd=new FormData(); fd.append('image', file); fd.append('folder', folderName);
      try {
        const res  = await fetch('../api/contenido.php', { method:'POST', headers:{'X-CSRF-Token':CSRF_TOKEN}, body:fd });
        const json = await res.json();
        if (json.ok) {
          if (!imagesData[currentFolder]) imagesData[currentFolder]=[];
          imagesData[currentFolder].unshift({ name:file.name, path:json.path, size:file.size });
        } else showToast(json.error||`Error: ${file.name}`,'error');
      } catch { showToast('Error de conexión','error'); }
      done++;
      fill.style.width = Math.round((done/total)*100) + '%';
    }
    setTimeout(() => { bar.style.display='none'; fill.style.width='0%'; }, 800);
    renderGrid();
    document.getElementById('uploadInput').value = '';
  }

  /* drag-and-drop */
  const dz = document.getElementById('dropZone');
  dz.addEventListener('dragover',  e => { e.preventDefault(); dz.classList.add('drag'); });
  dz.addEventListener('dragleave', () => dz.classList.remove('drag'));
  dz.addEventListener('drop', e => {
    e.preventDefault(); dz.classList.remove('drag');
    uploadFiles(e.dataTransfer.files);
  });

  loadImages();
</script>
</body>
</html>
