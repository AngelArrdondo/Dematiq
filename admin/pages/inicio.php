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
} catch (PDOException $e) { /* column not yet migrated — ignore */ }
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Inicio | DEMATIQ Admin</title>
  <link rel="stylesheet" href="../assets/css/admin.css?v=7">
  <link rel="icon" type="image/svg+xml" href="../../assets/images/logos/favicon-d.svg">
  <style>
    /* ── Slide count badge ─────────────────── */
    .slides-count-badge {
      display: inline-flex; align-items: center; gap: 5px;
      background: rgba(46,107,207,.1); color: var(--accent-lt);
      font-size: .7rem; font-weight: 700;
      padding: 3px 9px; border-radius: 20px;
      margin-left: 6px; vertical-align: middle;
    }

    /* ── Slide card ────────────────────────── */
    .slide-card {
      background: #fff;
      border: 1.5px solid var(--border);
      border-radius: 14px;
      overflow: hidden;
      margin-bottom: 16px;
      transition: box-shadow .2s, border-color .2s;
    }
    .slide-card:focus-within {
      border-color: var(--accent-lt);
      box-shadow: 0 0 0 3px rgba(46,107,207,.08);
    }

    /* card header strip */
    .slide-card-header {
      display: flex; align-items: center; justify-content: space-between;
      padding: 10px 14px;
      background: linear-gradient(to right, #f0f4ff, #fafcff);
      border-bottom: 1px solid var(--border);
    }
    .slide-badge { display: flex; align-items: center; gap: 10px; }
    .slide-num {
      min-width: 26px; height: 26px; padding: 0 7px;
      background: linear-gradient(135deg, var(--accent), var(--accent-lt));
      color: #fff; border-radius: 7px;
      display: flex; align-items: center; justify-content: center;
      font-size: .72rem; font-weight: 700;
    }
    .slide-header-title {
      font-size: .82rem; font-weight: 600; color: var(--text);
      max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    }
    .slide-header-title.empty { color: var(--text-lt); font-style: italic; font-weight: 400; }

    /* two-column body */
    .slide-body {
      display: grid;
      grid-template-columns: 230px 1fr;
    }
    .slide-img-col {
      border-right: 1px solid var(--border);
      padding: 16px;
      display: flex; flex-direction: column; gap: 10px;
      background: #fafcff;
    }
    .slide-img-preview {
      width: 100%; aspect-ratio: 16/9;
      border-radius: 10px; overflow: hidden;
      background: var(--page-bg);
      border: 2px dashed #ccd8f0;
      position: relative;
      display: flex; align-items: center; justify-content: center;
    }
    .slide-img-preview img {
      position: absolute; inset: 0;
      width: 100%; height: 100%; object-fit: cover; display: block;
    }
    .img-placeholder {
      display: flex; flex-direction: column; align-items: center; gap: 6px;
      color: var(--text-lt); font-size: .68rem; text-align: center; padding: 8px;
    }
    .img-placeholder svg { opacity: .25; }
    .slide-path-input {
      width: 100%; padding: 7px 10px;
      border: 1px solid var(--border); border-radius: 8px;
      font-size: .72rem; font-family: monospace;
      color: var(--text-lt); background: #f0f4ff; outline: none;
      transition: border-color .15s, color .15s;
    }
    .slide-path-input:focus { border-color: var(--accent-lt); color: var(--text); }
    .slide-path-input::placeholder { color: #b0bbcc; }

    .slide-fields-col { padding: 18px 20px; }
    .slide-fields-col .form-group:last-child { margin-bottom: 0; }

    /* responsive collapse */
    @media (max-width: 640px) {
      .slide-body { grid-template-columns: 1fr; }
      .slide-img-col { border-right: none; border-bottom: 1px solid var(--border); }
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
      <button id="sidebar-toggle" class="mobile-menu-toggle" aria-label="Abrir menú de navegación" aria-expanded="false" aria-controls="admin-sidebar">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
        </svg>
      </button>
      <span class="admin-topbar-title">Inicio</span>
    </div>

    <!-- User menu -->
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
      <h1>Página: Inicio</h1>
      <p>Edita las diapositivas del hero carousel de la página principal.</p>
    </div>

    <div class="admin-card">
      <div class="admin-card-header">
        <div class="admin-card-title">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="2" y="7" width="20" height="15" rx="2"/><polyline points="17,2 12,7 7,2"/>
          </svg>
          Diapositivas del Hero
          <span class="slides-count-badge" id="slides-count"></span>
        </div>
        <button class="btn-admin btn-outline-admin" onclick="addSlide()" style="font-size:.8rem;padding:6px 12px">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
          </svg>
          Agregar
        </button>
      </div>
      <div id="slides-container"></div>
    </div>

    <div class="save-bar">
      <a href="/index.html" target="_blank" class="btn-admin btn-outline-admin">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
        </svg>
        Ver página
      </a>
      <button class="btn-admin btn-outline-admin" onclick="cancelInicio()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
        </svg>
        Cancelar
      </button>
      <button class="btn-admin btn-primary-admin" onclick="saveInicio()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/>
          <polyline points="17,21 17,13 7,13 7,21"/><polyline points="7,3 7,8 15,8"/>
        </svg>
        Guardar cambios
      </button>
    </div>

  </div>
</div>

<?php $profileApiPath = '../api/profile.php'; $fotoPrefix = '../'; require __DIR__ . '/../includes/profile-modal.php'; ?>

<script src="../assets/js/auth.js?v=2"></script>
<script>
  const CSRF_TOKEN = '<?= $csrfToken ?>';

  AdminSidebar.init('inicio', '../', '../../');

  /* ── User menu dropdown ─────────────────────────── */
  const userMenuBtn = document.getElementById('userMenuBtn');
  userMenuBtn.addEventListener('click', function (e) {
    e.stopPropagation();
    const open = this.classList.toggle('open');
    this.setAttribute('aria-expanded', open);
  });
  userMenuBtn.addEventListener('keydown', function (e) {
    if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); this.click(); }
    if (e.key === 'Escape') this.classList.remove('open');
  });
  document.addEventListener('click', () => userMenuBtn.classList.remove('open'));

  /* ── Inicio page logic ──────────────────────────── */
  let originalSlides = JSON.parse(JSON.stringify(CM.get('home').hero || []));
  let slides = originalSlides.map(s => Object.assign({}, s));

  function esc(str) {
    return String(str || '').replace(/"/g, '&quot;');
  }

  function setPreview(i, src) {
    const prev = document.getElementById('imgPreview' + i);
    if (!prev) return;
    if (!src) {
      prev.innerHTML = `<div class="img-placeholder">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21,15 16,10 5,21"/></svg>
        <span>Sin imagen</span></div>`;
      return;
    }
    const img = document.createElement('img');
    img.alt = 'preview';
    img.src = src;
    img.onerror = () => {
      prev.innerHTML = `<div class="img-placeholder">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="3"/><line x1="3" y1="3" x2="21" y2="21"/></svg>
        <span>No se pudo cargar</span></div>`;
    };
    prev.innerHTML = '';
    prev.appendChild(img);
  }

  function updateSlideImage(i, path) {
    slides[i].image = path;
    setPreview(i, path ? '../../' + path : '');
    const pathInput = document.getElementById('imgPath' + i);
    if (pathInput && pathInput.value !== path) pathInput.value = path;
  }

  const pickIcon = `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17,8 12,3 7,8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>`;
  const trashIcon = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3,6 5,6 21,6"/><path d="M19,6l-1,14a2,2,0,0,1-2,2H8a2,2,0,0,1-2-2L5,6"/><path d="M10,11v6"/><path d="M14,11v6"/></svg>`;

  function renderSlides() {
    const c = document.getElementById('slides-container');
    c.innerHTML = '';

    const badge = document.getElementById('slides-count');
    if (badge) badge.textContent = slides.length;

    slides.forEach((s, i) => {
      const titleText = s.title ? esc(s.title) : '';
      const div = document.createElement('div');
      div.className = 'slide-card';
      div.innerHTML = `
        <div class="slide-card-header">
          <div class="slide-badge">
            <span class="slide-num">${i + 1}</span>
            <span class="slide-header-title${s.title ? '' : ' empty'}" id="slideHeaderTitle${i}">
              ${titleText || 'Sin título'}
            </span>
          </div>
          <button class="btn-rm" onclick="removeSlide(${i})" title="Eliminar diapositiva" style="flex-shrink:0">
            ${trashIcon}
          </button>
        </div>
        <div class="slide-body">
          <div class="slide-img-col">
            <div class="slide-img-preview" id="imgPreview${i}"></div>
            <button type="button" class="btn-admin btn-outline-admin" id="imgPickBtn${i}"
              onclick="document.getElementById('imgFile${i}').click()"
              style="width:100%;justify-content:center;gap:6px;font-size:.8rem;padding:7px 10px">
              ${pickIcon} Seleccionar imagen
            </button>
            <input type="file" id="imgFile${i}" accept="image/jpeg,image/png,image/webp,image/gif"
              style="display:none" onchange="uploadSlideImage(${i},this)">
            <input type="text" class="slide-path-input" id="imgPath${i}" value="${esc(s.image)}"
              oninput="slides[${i}].image=this.value;updateSlideImage(${i},this.value)"
              placeholder="assets/images/general/slide.webp" title="Ruta de la imagen">
          </div>
          <div class="slide-fields-col">
            <div class="form-group">
              <label>Título</label>
              <input type="text" value="${titleText}"
                oninput="slides[${i}].title=this.value;
                  const el=document.getElementById('slideHeaderTitle${i}');
                  el.textContent=this.value||'Sin título';
                  el.className='slide-header-title'+(this.value?'':' empty')">
            </div>
            <div class="form-group">
              <label>Subtítulo</label>
              <input type="text" value="${esc(s.subtitle)}" oninput="slides[${i}].subtitle=this.value">
            </div>
          </div>
        </div>
      `;
      c.appendChild(div);
      setPreview(i, s.image ? '../../' + s.image : '');
    });

    if (slides.length === 0) {
      c.innerHTML = '<p style="text-align:center;padding:32px;color:var(--text-lt);font-size:.88rem">No hay diapositivas. Haz clic en <strong>Agregar</strong> para crear una.</p>';
    }
  }

  async function uploadSlideImage(i, input) {
    if (!input.files[0]) return;
    const file = input.files[0];
    if (file.size > 5 * 1024 * 1024) { showToast('Máximo 5 MB', 'error'); input.value = ''; return; }

    /* Show local preview immediately — no server round-trip needed */
    const localURL = URL.createObjectURL(file);
    setPreview(i, localURL);

    const btn = document.getElementById('imgPickBtn' + i);
    btn.disabled = true;
    btn.textContent = 'Subiendo…';

    const fd = new FormData();
    fd.append('image', file);

    try {
      const res  = await fetch('../api/contenido.php', { method: 'POST', headers: { 'X-CSRF-Token': CSRF_TOKEN }, body: fd });
      const json = await res.json();
      if (json.ok) {
        slides[i].image = json.path;
        const pathInput = document.getElementById('imgPath' + i);
        if (pathInput) pathInput.value = json.path;
        showToast('Imagen subida correctamente');
      } else {
        showToast(json.error || 'Error al subir', 'error');
        setPreview(i, slides[i].image ? '../../' + slides[i].image : '');
      }
    } catch {
      showToast('Error de conexión', 'error');
      setPreview(i, slides[i].image ? '../../' + slides[i].image : '');
    } finally {
      btn.disabled = false;
      btn.innerHTML = pickIcon + ' Seleccionar imagen';
      input.value = '';
    }
  }

  function addSlide()    { slides.push({ title: '', subtitle: '', image: '' }); renderSlides(); }
  function removeSlide(i) {
    if (slides.length <= 1) { showToast('Debe haber al menos una diapositiva', 'error'); return; }
    slides.splice(i, 1); renderSlides();
  }
  function cancelInicio() {
    slides = originalSlides.map(s => Object.assign({}, s));
    renderSlides();
    showToast('Cambios descartados');
  }
  async function saveInicio() {
    const home = CM.get('home');
    home.hero  = slides;
    try {
      const res = await CM.set('home', home);
      if (res && res.ok) {
        originalSlides = slides.map(s => Object.assign({}, s));
        showToast('Cambios guardados correctamente');
      } else {
        showToast(res?.error || 'Error al guardar', 'error');
      }
    } catch {
      showToast('Error de conexión', 'error');
    }
  }

  renderSlides();
</script>

</body>
</html>
