<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/contenido.php';
$user    = Auth::require('/pages/corporativo/login.php');
$content = Contenido::getAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Proyectos | DEMATIQ Admin</title>
  <link rel="stylesheet" href="../assets/css/admin.css?v=5">
  <link rel="icon" type="image/svg+xml" href="../../assets/images/logos/favicon-d.svg">
</head>
<body>

<script>window.__DB_CONTENT = <?= json_encode($content, JSON_UNESCAPED_UNICODE) ?>;</script>

<div id="sidebar-overlay" class="sidebar-overlay"></div>
<aside class="admin-sidebar"></aside>

<div class="admin-main">

  <div class="admin-topbar">
    <div style="display:flex;align-items:center;gap:12px">
      <button id="sidebar-toggle" class="mobile-menu-toggle" aria-label="Menú">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
        </svg>
      </button>
      <span class="admin-topbar-title">Proyectos Destacados</span>
    </div>
    <div class="admin-topbar-user">
      <span><?= htmlspecialchars($user['nombre']) ?></span>
      <div class="admin-avatar"><?= strtoupper(substr($user['nombre'], 0, 1)) ?></div>
    </div>
  </div>

  <div class="admin-content">

    <div class="section-header">
      <h1>Proyectos Destacados</h1>
      <p>Edita los proyectos del carrusel en la página principal.</p>
    </div>

    <div class="admin-card">
      <div class="admin-card-header">
        <div class="admin-card-title">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
          </svg>
          Lista de proyectos
        </div>
        <button class="btn-admin btn-outline-admin" onclick="addProject()" style="font-size:.8rem;padding:6px 12px">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
          </svg>
          Agregar
        </button>
      </div>
      <div id="projects-container"></div>
      <p style="font-size:.78rem;color:#5a6f96;margin-top:6px">
        Rutas relativas a la raíz del sitio. Imagen: <code>assets/images/general/cart.webp</code> · Enlace: <code>pages/ensamble/ensamble.html</code>
      </p>
    </div>

    <div class="save-bar">
      <a href="../../index.html" target="_blank" class="btn-admin btn-outline-admin">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
        </svg>
        Ver página
      </a>
      <button class="btn-admin btn-primary-admin" onclick="saveProjects()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/>
          <polyline points="17,21 17,13 7,13 7,21"/><polyline points="7,3 7,8 15,8"/>
        </svg>
        Guardar cambios
      </button>
    </div>

  </div>
</div>

<script src="../assets/js/auth.js?v=2"></script>
<script>
  AdminSidebar.init('proyectos', '../', '../../');

  let projects = (CM.get('proyectos') || []).map(p => Object.assign({}, p));

  function renderProjects() {
    const c = document.getElementById('projects-container');
    c.innerHTML = '';
    projects.forEach((p, i) => {
      const div = document.createElement('div');
      div.className = 'repeat-item';
      div.innerHTML = `
        <div class="repeat-item-header">
          <span class="repeat-item-title">Proyecto ${i + 1}</span>
          <button class="btn-rm" onclick="removeProject(${i})" title="Eliminar">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="3,6 5,6 21,6"/><path d="M19,6l-1,14a2,2,0,0,1-2,2H8a2,2,0,0,1-2-2L5,6"/><path d="M10,11v6"/><path d="M14,11v6"/>
            </svg>
          </button>
        </div>
        <div class="form-grid-2">
          <div class="form-group"><label>Nombre</label>
            <input type="text" value="${escHtml(p.nombre || '')}" oninput="projects[${i}].nombre=this.value">
          </div>
          <div class="form-group"><label>Enlace (href)</label>
            <input type="text" value="${escHtml(p.href || '')}" oninput="projects[${i}].href=this.value" placeholder="pages/ensamble/ensamble.html">
          </div>
        </div>
        <div class="form-group"><label>Descripción</label>
          <textarea oninput="projects[${i}].desc=this.value">${escHtml(p.desc || '')}</textarea>
        </div>
        <div class="form-group"><label>Ruta de imagen</label>
          <input type="text" value="${escHtml(p.img || '')}" oninput="projects[${i}].img=this.value; updatePreview(${i}, this.value)" placeholder="assets/images/general/cart.webp">
        </div>
        ${p.img ? `<img id="preview-${i}" src="../../${escHtml(p.img)}" alt="preview" style="max-height:90px;border-radius:6px;border:1px solid var(--border);margin-top:4px" onerror="this.style.display='none'">` : `<img id="preview-${i}" style="display:none">`}
      `;
      c.appendChild(div);
    });
  }

  function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  function updatePreview(i, val) {
    const img = document.getElementById('preview-' + i);
    if (!img) return;
    img.src = '../../' + val;
    img.style.display = val ? '' : 'none';
  }

  function addProject() {
    projects.push({ nombre: '', desc: '', img: '', href: '' });
    renderProjects();
    document.getElementById('projects-container').lastElementChild
      ?.querySelector('input')?.focus();
  }

  function removeProject(i) {
    if (projects.length <= 1) { showToast('Debe haber al menos un proyecto', 'error'); return; }
    projects.splice(i, 1);
    renderProjects();
  }

  function saveProjects() {
    CM.set('proyectos', projects);
    showToast('Proyectos guardados correctamente');
  }

  renderProjects();
</script>

</body>
</html>
