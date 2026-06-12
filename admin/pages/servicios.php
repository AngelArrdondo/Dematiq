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
  <title>Servicios | DEMATIQ Admin</title>
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
      <span class="admin-topbar-title">Servicios</span>
    </div>
    <div class="admin-topbar-user">
      <span><?= htmlspecialchars($user['nombre']) ?></span>
      <div class="admin-avatar"><?= strtoupper(substr($user['nombre'], 0, 1)) ?></div>
    </div>
  </div>

  <div class="admin-content">

    <div class="section-header">
      <h1>Página: Servicios de Ingeniería</h1>
      <p>Edita las categorías de servicios y sus imágenes asociadas.</p>
    </div>

    <div class="admin-card">
      <div class="admin-card-header">
        <div class="admin-card-title">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/>
          </svg>
          Categorías de servicios
        </div>
        <button class="btn-admin btn-outline-admin" onclick="addServicio()" style="font-size:.8rem;padding:6px 12px">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
          </svg>
          Agregar
        </button>
      </div>
      <div id="servicios-container"></div>
      <p style="font-size:.78rem;color:#5a6f96;margin-top:6px">
        Ruta de imagen relativa a la raíz del sitio. Ejemplo: <code>assets/images/general/img1.webp</code>
      </p>
    </div>

    <div class="save-bar">
      <a href="../../pages/servicios/servicios.html" target="_blank" class="btn-admin btn-outline-admin">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
        </svg>
        Ver página
      </a>
      <button class="btn-admin btn-primary-admin" onclick="saveServicios()">
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
  AdminSidebar.init('servicios', '../', '../../');

  let servicios = (CM.get('servicios') || []).map(s => Object.assign({}, s));

  function renderServicios() {
    const c = document.getElementById('servicios-container');
    c.innerHTML = '';
    servicios.forEach((s, i) => {
      const div = document.createElement('div');
      div.className = 'repeat-item item-media-layout';
      div.innerHTML = `
        <div style="width:80px;height:56px;border:1px solid var(--border);border-radius:8px;background:#f9fbff;overflow:hidden;flex-shrink:0;display:flex;align-items:center;justify-content:center">
          ${s.image ? `<img src="../../${s.image}" alt="${s.nombre}" style="max-width:100%;max-height:100%;object-fit:cover" onerror="this.style.display='none'">` : ''}
        </div>
        <div>
          <div class="repeat-item-header">
            <span class="repeat-item-title">${s.nombre || 'Servicio ' + (i + 1)}</span>
            <button class="btn-rm" onclick="removeServicio(${i})" title="Eliminar">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="3,6 5,6 21,6"/><path d="M19,6l-1,14a2,2,0,0,1-2,2H8a2,2,0,0,1-2-2L5,6"/>
                <path d="M10,11v6"/><path d="M14,11v6"/>
              </svg>
            </button>
          </div>
          <div class="field-grid-2">
            <div class="form-group" style="margin:0"><label>Nombre del servicio</label>
              <input type="text" value="${s.nombre || ''}" oninput="servicios[${i}].nombre=this.value" placeholder="Programación de PLC">
            </div>
            <div class="form-group" style="margin:0"><label>Ruta de imagen</label>
              <input type="text" value="${s.image || ''}" oninput="servicios[${i}].image=this.value;renderServicios()" placeholder="assets/images/general/img1.webp">
            </div>
          </div>
        </div>
      `;
      c.appendChild(div);
    });
  }

  function addServicio() { servicios.push({ id: 'servicio_' + Date.now(), nombre: '', image: '' }); renderServicios(); }
  function removeServicio(i) { servicios.splice(i, 1); renderServicios(); }
  function saveServicios() { CM.set('servicios', servicios); showToast('Cambios guardados correctamente'); }

  renderServicios();
</script>

</body>
</html>
