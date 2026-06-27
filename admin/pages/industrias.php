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
  <title>Industrias | DEMATIQ Admin</title>
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
      <span class="admin-topbar-title">Industrias</span>
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
      <a href="/pages/corporativo/industrias.html" target="_blank" class="btn-admin btn-outline-admin">
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

<?php $profileApiPath = '../api/profile.php'; $fotoPrefix = '../'; require __DIR__ . '/../includes/profile-modal.php'; ?>

<script src="../assets/js/auth.js?v=2"></script>
<script>
  const CSRF_TOKEN = '<?= $csrfToken ?>';

  AdminSidebar.init('industrias', '../', '../../');

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
  async function saveIndustrias() {
    try {
      const res = await CM.set('industrias', industrias);
      if (res && res.ok) {
        showToast('Cambios guardados correctamente');
      } else {
        showToast(res?.error || 'Error al guardar', 'error');
      }
    } catch {
      showToast('Error de conexión', 'error');
    }
  }

  renderIndustrias();
</script>

</body>
</html>
