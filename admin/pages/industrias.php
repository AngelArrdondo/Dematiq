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
  <title>Industrias | DEMATIQ Admin</title>
  <link rel="stylesheet" href="../assets/css/admin.css?v=7">
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
      <span class="admin-topbar-title">Industrias</span>
    </div>
    <div class="admin-topbar-user">
      <span><?= htmlspecialchars($user['nombre']) ?></span>
      <div class="admin-avatar"><?= strtoupper(substr($user['nombre'], 0, 1)) ?></div>
    </div>
  </div>

  <div class="admin-content">

    <div class="section-header">
      <h1>Página: Industrias</h1>
      <p>Edita el nombre y descripción de cada sector industrial.</p>
    </div>

    <div class="admin-card">
      <div class="admin-card-header">
        <div class="admin-card-title">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polygon points="12,2 2,7 12,12 22,7"/><polyline points="2,17 12,22 22,17"/><polyline points="2,12 12,17 22,12"/>
          </svg>
          Sectores industriales
        </div>
        <button class="btn-admin btn-outline-admin" onclick="addIndustria()" style="font-size:.8rem;padding:6px 12px">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
          </svg>
          Agregar
        </button>
      </div>
      <div id="industrias-container"></div>
    </div>

    <div class="save-bar">
      <a href="../../pages/corporativo/industrias.html" target="_blank" class="btn-admin btn-outline-admin">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
        </svg>
        Ver página
      </a>
      <button class="btn-admin btn-primary-admin" onclick="saveIndustrias()">
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
  AdminSidebar.init('industrias', '../', '../../');

  let industrias = (CM.get('industrias') || []).map(ind => Object.assign({}, ind));

  function renderIndustrias() {
    const c = document.getElementById('industrias-container');
    c.innerHTML = '';
    industrias.forEach((ind, i) => {
      const div = document.createElement('div');
      div.className = 'repeat-item';
      div.innerHTML = `
        <div class="repeat-item-header">
          <span class="repeat-item-title">${ind.nombre || 'Industria ' + (i + 1)}</span>
          <button class="btn-rm" onclick="removeIndustria(${i})" title="Eliminar">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="3,6 5,6 21,6"/><path d="M19,6l-1,14a2,2,0,0,1-2,2H8a2,2,0,0,1-2-2L5,6"/>
              <path d="M10,11v6"/><path d="M14,11v6"/>
            </svg>
          </button>
        </div>
        <div class="form-grid-2">
          <div class="form-group">
            <label>Nombre del sector</label>
            <input type="text" value="${ind.nombre || ''}"
              oninput="industrias[${i}].nombre=this.value;document.querySelectorAll('.repeat-item-title')[${i}].textContent=this.value||'Industria ${i+1}'"
              placeholder="Automotriz">
          </div>
          <div class="form-group">
            <label>ID (identificador único)</label>
            <input type="text" value="${ind.id || ''}" oninput="industrias[${i}].id=this.value" placeholder="automotriz">
          </div>
        </div>
        <div class="form-group">
          <label>Descripción</label>
          <textarea oninput="industrias[${i}].descripcion=this.value">${ind.descripcion || ''}</textarea>
        </div>
      `;
      c.appendChild(div);
    });
  }

  function addIndustria() { industrias.push({ id: '', nombre: '', descripcion: '' }); renderIndustrias(); }
  function removeIndustria(i) { industrias.splice(i, 1); renderIndustrias(); }
  function saveIndustrias() { CM.set('industrias', industrias); showToast('Cambios guardados correctamente'); }

  renderIndustrias();
</script>

</body>
</html>
