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
  <title>Marcas Asociadas | DEMATIQ Admin</title>
  <link rel="stylesheet" href="../assets/css/admin.css?v=7">
  <link rel="icon" type="image/svg+xml" href="../../assets/images/logos/favicon-d.svg">
  <style>

    /* ── BANNER ─────────────────────────────────────────── */
    .marcas-banner {
      background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 45%, #312e81 100%);
      border-radius: 20px; padding: 30px 32px;
      margin-bottom: 16px; position: relative; overflow: hidden;
    }
    .marcas-banner::before {
      content:''; position:absolute; width:500px; height:500px; border-radius:50%;
      background:radial-gradient(circle,rgba(99,102,241,.22) 0%,transparent 65%);
      top:-200px; right:-60px; pointer-events:none;
    }
    .marcas-banner::after {
      content:''; position:absolute; width:220px; height:220px; border-radius:50%;
      background:radial-gradient(circle,rgba(255,255,255,.04) 0%,transparent 70%);
      bottom:-90px; left:38%; pointer-events:none;
    }
    .banner-mesh {
      position:absolute; inset:0; pointer-events:none; overflow:hidden;
      background-image:radial-gradient(rgba(255,255,255,.04) 1px,transparent 1px);
      background-size:22px 22px;
    }
    .banner-inner { position:relative; z-index:1; }
    .banner-chip {
      display:inline-flex; align-items:center; gap:7px;
      background:rgba(255,255,255,.1); border:1px solid rgba(255,255,255,.15);
      color:rgba(255,255,255,.75); font-size:.62rem; font-weight:700;
      letter-spacing:1.8px; text-transform:uppercase;
      padding:5px 12px; border-radius:20px; margin-bottom:14px;
    }
    .banner-chip-dot {
      width:6px; height:6px; border-radius:50%; background:#a5b4fc;
      animation:pulse-dot 2.2s ease-in-out infinite;
    }
    @keyframes pulse-dot {
      0%,100%{opacity:1;box-shadow:0 0 0 0 rgba(165,180,252,.4);}
      50%{opacity:.7;box-shadow:0 0 0 5px rgba(165,180,252,0);}
    }
    .banner-title { font-size:1.65rem; font-weight:800; color:#fff; letter-spacing:-.025em; line-height:1.1; margin-bottom:6px; }
    .banner-desc  { font-size:.82rem; color:rgba(255,255,255,.5); line-height:1.65; max-width:480px; margin-bottom:22px; }
    .banner-stats { display:flex; gap:12px; flex-wrap:wrap; }
    .bstat {
      display:flex; align-items:center; gap:10px;
      background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.12);
      padding:10px 16px; border-radius:14px; min-width:120px;
      transition:background .2s;
    }
    .bstat-icon { width:34px; height:34px; border-radius:10px; flex-shrink:0; display:flex; align-items:center; justify-content:center; }
    .bstat-icon svg { width:16px; height:16px; color:#fff; }
    .bstat-label { font-size:.6rem; color:rgba(255,255,255,.4); text-transform:uppercase; letter-spacing:.8px; margin-bottom:2px; }
    .bstat-val   { font-size:.85rem; font-weight:700; color:#fff; }

    /* ── BRAND LIST ─────────────────────────────────────── */
    .brand-card {
      background:#fff; border:1.5px solid var(--border);
      border-radius:16px; overflow:hidden; margin-bottom:12px;
      transition:box-shadow .2s, border-color .2s;
    }
    .brand-card:focus-within { border-color:var(--accent-lt); box-shadow:0 0 0 3px rgba(46,107,207,.07); }

    .brand-card-header {
      display:flex; align-items:center; gap:12px;
      padding:11px 16px;
      background:linear-gradient(to right,#f0f4ff,#fafcff);
      border-bottom:1px solid var(--border);
    }
    .brand-num {
      min-width:26px; height:26px; padding:0 7px;
      background:linear-gradient(135deg,#4f46e5,#6366f1);
      color:#fff; border-radius:7px;
      display:flex; align-items:center; justify-content:center;
      font-size:.72rem; font-weight:700; flex-shrink:0;
    }
    .brand-header-name {
      flex:1; font-size:.85rem; font-weight:600; color:var(--text);
      min-width:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;
    }
    .brand-header-name.empty { color:var(--text-lt); font-style:italic; font-weight:400; }
    .brand-header-actions { display:flex; gap:6px; align-items:center; flex-shrink:0; }
    .brand-move-btn {
      width:28px; height:28px; border:1.5px solid var(--border);
      background:#fff; border-radius:7px; cursor:pointer;
      display:flex; align-items:center; justify-content:center;
      color:var(--text-lt); transition:all .15s;
    }
    .brand-move-btn:hover:not(:disabled) { border-color:var(--accent-lt); color:var(--accent); background:#f0f4ff; }
    .brand-move-btn:disabled { opacity:.3; cursor:default; }
    .brand-move-btn svg { width:13px; height:13px; }
    .btn-rm {
      width:30px; height:30px; border:none; cursor:pointer;
      background:#fff1f0; color:#dc2626; border-radius:8px;
      display:flex; align-items:center; justify-content:center;
      transition:background .14s; flex-shrink:0;
    }
    .btn-rm:hover { background:#fee2e2; }
    .btn-rm svg { width:14px; height:14px; }

    .brand-body {
      display:grid; grid-template-columns:200px 1fr;
    }
    .brand-img-col {
      border-right:1px solid var(--border); padding:16px;
      display:flex; flex-direction:column; gap:10px;
      background:#fafcff;
    }
    .brand-img-preview {
      width:100%; aspect-ratio:16/6; border-radius:10px; overflow:hidden;
      background:#f0f4ff; border:2px dashed #c7d5f0;
      position:relative; display:flex; align-items:center; justify-content:center;
      cursor:pointer; transition:border-color .18s, box-shadow .18s;
    }
    .brand-img-preview:hover { border-color:var(--accent-lt); box-shadow:0 4px 14px rgba(46,107,207,.12); }
    .brand-img-preview img {
      max-width:80%; max-height:80%; object-fit:contain; display:block;
    }
    .img-placeholder {
      display:flex; flex-direction:column; align-items:center; gap:6px;
      color:var(--text-lt); font-size:.65rem; text-align:center; padding:8px;
      pointer-events:none;
    }
    .img-placeholder svg { opacity:.25; }
    .brand-img-hint {
      position:absolute; inset:0; background:rgba(79,70,229,.5);
      display:flex; align-items:center; justify-content:center;
      color:#fff; font-size:.65rem; font-weight:700; gap:5px;
      opacity:0; transition:opacity .16s;
    }
    .brand-img-hint svg { width:14px; height:14px; }
    .brand-img-preview:hover .brand-img-hint { opacity:1; }
    .brand-path-input {
      width:100%; padding:7px 10px; border:1px solid var(--border); border-radius:8px;
      font-size:.7rem; font-family:monospace; color:var(--text-lt);
      background:#f0f4ff; outline:none; transition:border-color .15s, color .15s; box-sizing:border-box;
    }
    .brand-path-input:focus { border-color:var(--accent-lt); color:var(--text); }
    .brand-path-input::placeholder { color:#b0bbcc; }

    .brand-fields-col { padding:18px 20px; }
    .brand-fields-col .form-group:last-child { margin-bottom:0; }

    /* upload progress */
    .upload-mini-prog {
      display:none; height:4px; border-radius:2px;
      background:#e0e7ff; overflow:hidden; margin-top:6px;
    }
    .upload-mini-prog.visible { display:block; }
    .upload-mini-prog-bar { height:100%; background:linear-gradient(90deg,#4f46e5,#6366f1); transition:width .2s; }

    /* marquee preview */
    .marquee-preview-wrap {
      background:#0f172a; border-radius:14px; padding:18px 20px; overflow:hidden;
      margin-bottom:16px; position:relative;
    }
    .marquee-preview-label {
      font-size:.62rem; font-weight:700; color:rgba(255,255,255,.35);
      text-transform:uppercase; letter-spacing:1.2px; margin-bottom:12px;
    }
    .marquee-preview-track {
      display:flex; gap:28px; align-items:center; overflow:hidden;
      mask-image:linear-gradient(to right,transparent,#000 8%,#000 92%,transparent);
    }
    .marquee-preview-item {
      flex-shrink:0; height:28px; display:flex; align-items:center;
    }
    .marquee-preview-item img {
      max-height:28px; max-width:80px; object-fit:contain;
      filter:brightness(0) invert(1); opacity:.65;
    }

    /* empty state */
    .brands-empty {
      text-align:center; padding:40px 20px; color:var(--text-lt);
    }
    .brands-empty svg { opacity:.18; margin-bottom:10px; }
    .brands-empty p { font-size:.88rem; }

    @media (max-width: 600px) {
      .brand-body { grid-template-columns:1fr; }
      .brand-img-col { border-right:none; border-bottom:1px solid var(--border); }
      .banner-stats { flex-direction:column; }
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
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
        </svg>
      </button>
      <span class="admin-topbar-title">Marcas Asociadas</span>
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

    <!-- Banner -->
    <div class="marcas-banner">
      <div class="banner-mesh"></div>
      <div class="banner-inner">
        <div class="banner-chip">
          <span class="banner-chip-dot"></span>
          Carrusel del Inicio
        </div>
        <h1 class="banner-title">Marcas Asociadas</h1>
        <p class="banner-desc">Gestiona los logos de tecnología que aparecen en el carrusel animado de la portada del sitio.</p>
        <div class="banner-stats">
          <div class="bstat">
            <div class="bstat-icon" style="background:linear-gradient(135deg,#4f46e5,#6366f1)">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26 12,2"/></svg>
            </div>
            <div>
              <div class="bstat-label">Total de marcas</div>
              <div class="bstat-val" id="statTotal">—</div>
            </div>
          </div>
          <div class="bstat">
            <div class="bstat-icon" style="background:linear-gradient(135deg,#0e7490,#06b6d4)">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21,15 16,10 5,21"/></svg>
            </div>
            <div>
              <div class="bstat-label">Con logo</div>
              <div class="bstat-val" id="statConLogo">—</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Marquee preview -->
    <div class="marquee-preview-wrap">
      <div class="marquee-preview-label">Vista previa del carrusel</div>
      <div class="marquee-preview-track" id="marqueePreview"></div>
    </div>

    <!-- Brand list -->
    <div class="admin-card" style="margin-bottom:0">
      <div class="admin-card-header">
        <div class="admin-card-title">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26 12,2"/>
          </svg>
          Lista de marcas
        </div>
        <button class="btn-admin btn-outline-admin" onclick="addMarca()" style="font-size:.8rem;padding:6px 14px">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="14" height="14"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Agregar marca
        </button>
      </div>

      <div id="marcasContainer"></div>
    </div>

    <!-- Save bar -->
    <div class="save-bar">
      <a href="/index.html" target="_blank" class="btn-admin btn-outline-admin">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
        Ver en sitio
      </a>
      <button class="btn-admin btn-outline-admin" onclick="cancelMarcas()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        Descartar
      </button>
      <button class="btn-admin btn-primary-admin" onclick="saveMarcas()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17,21 17,13 7,13 7,21"/><polyline points="7,3 7,8 15,8"/></svg>
        Guardar cambios
      </button>
    </div>

  </div>
</div>

<input type="file" id="marcaFileInput" accept="image/*,.svg" style="display:none">

<?php $profileApiPath = '../api/profile.php'; $fotoPrefix = '../'; require __DIR__ . '/../includes/profile-modal.php'; ?>

<script src="../assets/js/auth.js?v=2"></script>
<script>
  const CSRF_TOKEN = '<?= $csrfToken ?>';

  AdminSidebar.init('marcas', '../', '../../');

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
  document.addEventListener('keydown', e => {
    if ((e.ctrlKey || e.metaKey) && e.key === 's') { e.preventDefault(); saveMarcas(); }
  });

  /* ── Data ─────────────────────────────────────── */
  let marcas = (CM.get('marcas') || []).map(m => Object.assign({}, m));
  let original = JSON.parse(JSON.stringify(marcas));

  /* ── Helpers ──────────────────────────────────── */
  const esc = str => String(str || '').replace(/"/g, '&quot;');

  const pickIcon  = `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17,8 12,3 7,8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>`;
  const trashIcon = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3,6 5,6 21,6"/><path d="M19,6l-1,14a2,2,0,0,1-2,2H8a2,2,0,0,1-2-2L5,6"/><path d="M10,11v6"/><path d="M14,11v6"/></svg>`;
  const upIcon    = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"/></svg>`;
  const downIcon  = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>`;

  /* ── Preview ──────────────────────────────────── */
  function updatePreview() {
    const track = document.getElementById('marqueePreview');
    const withLogo = marcas.filter(m => m.logo);
    if (!withLogo.length) {
      track.innerHTML = '<span style="color:rgba(255,255,255,.2);font-size:.75rem">Sin logos configurados</span>';
      return;
    }
    track.innerHTML = withLogo.slice(0, 12).map(m =>
      `<div class="marquee-preview-item"><img src="../../${m.logo}" alt="${esc(m.nombre)}" onerror="this.parentNode.style.display='none'"></div>`
    ).join('');
  }

  /* ── Stats ────────────────────────────────────── */
  function updateStats() {
    document.getElementById('statTotal').textContent   = marcas.length;
    document.getElementById('statConLogo').textContent = marcas.filter(m => m.logo).length;
  }

  /* ── Image preview ────────────────────────────── */
  function setPreview(i, src) {
    const prev = document.getElementById('brandPreview' + i);
    if (!prev) return;
    const hint = prev.querySelector('.brand-img-hint');
    if (!src) {
      prev.innerHTML = `<div class="img-placeholder"><svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21,15 16,10 5,21"/></svg><span>Sin logo</span></div>`;
      if (hint) prev.appendChild(hint);
      return;
    }
    const img = document.createElement('img');
    img.src = src;
    img.onerror = () => {
      prev.innerHTML = `<div class="img-placeholder"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="3"/><line x1="3" y1="3" x2="21" y2="21"/></svg><span>Sin vista previa</span></div>`;
      if (hint) prev.appendChild(hint);
    };
    prev.innerHTML = '';
    prev.appendChild(img);
    if (hint) prev.appendChild(hint);
    else {
      const h = document.createElement('div');
      h.className = 'brand-img-hint';
      h.innerHTML = `${pickIcon} Cambiar`;
      prev.appendChild(h);
    }
  }

  /* ── Upload ───────────────────────────────────── */
  let _uploadTarget = null;
  document.getElementById('marcaFileInput').onchange = async function() {
    if (_uploadTarget === null || !this.files[0]) return;
    const i = _uploadTarget;
    const file = this.files[0];
    this.value = '';
    if (file.size > 5 * 1024 * 1024) { showToast('Máximo 5 MB', 'error'); return; }

    const localURL = URL.createObjectURL(file);
    setPreview(i, localURL);

    const btn = document.getElementById('pickBtn' + i);
    if (btn) { btn.disabled = true; btn.textContent = 'Subiendo…'; }

    const prog = document.getElementById('prog' + i);
    const progBar = document.getElementById('progBar' + i);
    if (prog) prog.classList.add('visible');
    if (progBar) progBar.style.width = '0%';

    const fd = new FormData();
    fd.append('image', file);
    fd.append('folder', 'partners');

    try {
      const xhr = new XMLHttpRequest();
      xhr.open('POST', '../api/contenido.php');
      xhr.setRequestHeader('X-CSRF-Token', CSRF_TOKEN);
      xhr.upload.onprogress = e => {
        if (e.lengthComputable && progBar) progBar.style.width = Math.round(e.loaded / e.total * 100) + '%';
      };
      xhr.onload = () => {
        let json = {};
        try { json = JSON.parse(xhr.responseText); } catch {}
        if (json.ok) {
          marcas[i].logo = json.path;
          setPreview(i, '../../' + json.path);
          const pi = document.getElementById('imgPath' + i);
          if (pi) pi.value = json.path;
          updatePreview(); updateStats();
          showToast('Logo subido correctamente');
        } else {
          showToast(json.error || 'Error al subir', 'error');
          setPreview(i, marcas[i].logo ? '../../' + marcas[i].logo : '');
        }
        if (btn) { btn.disabled = false; btn.innerHTML = pickIcon + ' Seleccionar logo'; }
        if (prog) prog.classList.remove('visible');
        URL.revokeObjectURL(localURL);
      };
      xhr.onerror = () => {
        showToast('Error de conexión', 'error');
        if (btn) { btn.disabled = false; btn.innerHTML = pickIcon + ' Seleccionar logo'; }
        if (prog) prog.classList.remove('visible');
      };
      xhr.send(fd);
    } catch {
      showToast('Error de conexión', 'error');
      if (btn) { btn.disabled = false; btn.innerHTML = pickIcon + ' Seleccionar logo'; }
    }
  };

  function pickLogo(i) {
    _uploadTarget = i;
    document.getElementById('marcaFileInput').click();
  }

  /* ── Reorder ──────────────────────────────────── */
  function moveMarca(i, dir) {
    const j = i + dir;
    if (j < 0 || j >= marcas.length) return;
    [marcas[i], marcas[j]] = [marcas[j], marcas[i]];
    renderMarcas();
  }

  /* ── Render ───────────────────────────────────── */
  function renderMarcas() {
    const c = document.getElementById('marcasContainer');
    updateStats();
    updatePreview();

    if (!marcas.length) {
      c.innerHTML = `<div class="brands-empty">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26 12,2"/></svg>
        <p>No hay marcas. Usa <strong>Agregar marca</strong> para añadir la primera.</p>
      </div>`;
      return;
    }

    c.innerHTML = '';
    marcas.forEach((m, i) => {
      const div = document.createElement('div');
      div.className = 'brand-card';
      div.innerHTML = `
        <div class="brand-card-header">
          <span class="brand-num">${i + 1}</span>
          <span class="brand-header-name${m.nombre ? '' : ' empty'}" id="headerName${i}">${esc(m.nombre) || 'Nueva marca'}</span>
          <div class="brand-header-actions">
            <button class="brand-move-btn" onclick="moveMarca(${i},-1)" title="Subir" ${i === 0 ? 'disabled' : ''}>${upIcon}</button>
            <button class="brand-move-btn" onclick="moveMarca(${i},1)"  title="Bajar" ${i === marcas.length - 1 ? 'disabled' : ''}>${downIcon}</button>
            <button class="btn-rm" onclick="removeMarca(${i})" title="Eliminar">${trashIcon}</button>
          </div>
        </div>
        <div class="brand-body">
          <div class="brand-img-col">
            <div class="brand-img-preview" id="brandPreview${i}" onclick="pickLogo(${i})" title="Clic para cambiar logo">
              <div class="brand-img-hint">${pickIcon} Cambiar</div>
            </div>
            <button type="button" class="btn-admin btn-outline-admin" id="pickBtn${i}"
              onclick="pickLogo(${i})"
              style="width:100%;justify-content:center;gap:6px;font-size:.78rem;padding:7px 10px">
              ${pickIcon} Seleccionar logo
            </button>
            <div class="upload-mini-prog" id="prog${i}">
              <div class="upload-mini-prog-bar" id="progBar${i}" style="width:0%"></div>
            </div>
            <input type="text" class="brand-path-input" id="imgPath${i}" value="${esc(m.logo)}"
              placeholder="assets/images/partners/logo.svg"
              oninput="marcas[${i}].logo=this.value;setPreview(${i},this.value?'../../'+this.value:'');updatePreview();updateStats()">
          </div>
          <div class="brand-fields-col">
            <div class="form-group">
              <label>Nombre de la marca</label>
              <input type="text" value="${esc(m.nombre)}" placeholder="Ej. Siemens"
                oninput="marcas[${i}].nombre=this.value;const el=document.getElementById('headerName${i}');el.textContent=this.value||'Nueva marca';el.className='brand-header-name'+(this.value?'':' empty')">
            </div>
            <div class="form-group">
              <label>URL del sitio web <span style="font-weight:400;color:var(--text-lt)">(opcional)</span></label>
              <input type="url" value="${esc(m.url || '')}" placeholder="https://www.siemens.com"
                oninput="marcas[${i}].url=this.value">
              <p class="field-hint">Si se ingresa, el logo será clickeable en el carrusel.</p>
            </div>
          </div>
        </div>`;
      c.appendChild(div);
      setPreview(i, m.logo ? '../../' + m.logo : '');
    });
  }

  /* ── CRUD ─────────────────────────────────────── */
  function addMarca() {
    marcas.push({ nombre:'', logo:'', url:'' });
    renderMarcas();
    setTimeout(() => {
      const cards = document.querySelectorAll('.brand-card');
      if (cards.length) cards[cards.length - 1].scrollIntoView({ behavior:'smooth', block:'center' });
    }, 80);
  }

  function removeMarca(i) {
    marcas.splice(i, 1);
    renderMarcas();
  }

  function cancelMarcas() {
    marcas = JSON.parse(JSON.stringify(original));
    renderMarcas();
    showToast('Cambios descartados');
  }

  async function saveMarcas() {
    try {
      const res = await CM.set('marcas', marcas);
      if (res && res.ok) {
        original = JSON.parse(JSON.stringify(marcas));
        showToast('Cambios guardados correctamente');
      } else {
        showToast(res?.error || 'Error al guardar', 'error');
      }
    } catch {
      showToast('Error de conexión', 'error');
    }
  }

  /* ── Init ─────────────────────────────────────── */
  renderMarcas();
</script>

</body>
</html>
