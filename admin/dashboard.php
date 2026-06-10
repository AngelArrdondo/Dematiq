<?php
require_once __DIR__ . '/../includes/auth.php';
$user = Auth::require('/pages/corporativo/login.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard | DEMATIQ Admin</title>
  <link rel="stylesheet" href="assets/css/admin.css?v=5">
  <link rel="icon" type="image/svg+xml" href="../assets/images/logos/favicon-d.svg">
</head>
<body>

<div id="sidebar-overlay" class="sidebar-overlay"></div>
<aside class="admin-sidebar" aria-label="Navegación del panel"></aside>

<div class="admin-main">

  <header class="admin-topbar">
    <div style="display:flex;align-items:center;gap:12px">
      <button id="sidebar-toggle" class="mobile-menu-toggle" aria-label="Abrir menú de navegación" aria-expanded="false" aria-controls="admin-sidebar">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
        </svg>
      </button>
      <span class="admin-topbar-title">Dashboard</span>
    </div>
    <div class="admin-topbar-user">
      <span><?= htmlspecialchars($user['nombre']) ?></span>
      <div class="admin-avatar" aria-hidden="true"><?= strtoupper(substr($user['nombre'], 0, 1)) ?></div>
    </div>
  </header>

  <main class="admin-content">

    <!-- Welcome -->
    <div class="dash-welcome">
      <div class="dash-welcome-text">
        <p class="dash-greeting" id="dash-greeting">Bienvenido,</p>
        <p class="dash-name"><?= htmlspecialchars($user['nombre']) ?></p>
        <p class="dash-date" id="dash-date" aria-live="polite"></p>
      </div>
      <div class="dash-welcome-actions">
        <label class="btn-admin btn-outline-admin" style="cursor:pointer;" tabindex="0" role="button" aria-label="Importar contenido desde archivo JSON">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" width="15" height="15"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
          Importar
          <input type="file" accept=".json" id="import-file" style="display:none" onchange="handleImport(event)" aria-label="Seleccionar archivo JSON para importar">
        </label>
        <button class="btn-admin btn-primary-admin" onclick="CM.exportAll()" aria-label="Exportar contenido como archivo JSON">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" width="15" height="15"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
          Exportar
        </button>
      </div>
    </div>

    <!-- Stats -->
    <div class="stats-grid" role="list" aria-label="Estadísticas del sitio">

      <article class="stat-card stat-blue" role="listitem">
        <div class="stat-icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
        </div>
        <div class="stat-body">
          <span class="stat-value" id="stat-partners">—</span>
          <span class="stat-label">Partners activos</span>
        </div>
      </article>

      <article class="stat-card stat-teal" role="listitem">
        <div class="stat-icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12,2 2,7 12,12 22,7"/><polyline points="2,17 12,22 22,17"/><polyline points="2,12 12,17 22,12"/></svg>
        </div>
        <div class="stat-body">
          <span class="stat-value" id="stat-industrias">—</span>
          <span class="stat-label">Industrias</span>
        </div>
      </article>

      <article class="stat-card stat-orange" role="listitem">
        <div class="stat-icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/></svg>
        </div>
        <div class="stat-body">
          <span class="stat-value" id="stat-servicios">—</span>
          <span class="stat-label">Servicios</span>
        </div>
      </article>

      <article class="stat-card stat-green" role="listitem">
        <div class="stat-icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
        </div>
        <div class="stat-body">
          <span class="stat-value" id="stat-proyectos">—</span>
          <span class="stat-label">Proyectos</span>
        </div>
      </article>

      <article class="stat-card stat-purple" role="listitem">
        <div class="stat-icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
        </div>
        <div class="stat-body">
          <span class="stat-value" id="stat-visits">—</span>
          <span class="stat-label">Visitas al sitio</span>
        </div>
      </article>

    </div>

    <!-- Quick nav -->
    <section aria-labelledby="quicknav-heading">
      <div class="section-header">
        <h2 id="quicknav-heading">Acceso rápido</h2>
        <p>Edita el contenido de cada sección del sitio</p>
      </div>

      <div class="pages-grid" role="list">

        <a href="pages/inicio.php" class="page-card" role="listitem">
          <div class="page-card-icon" style="background:linear-gradient(135deg,#1a4a9e,#2e6bcf)" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
          </div>
          <div class="page-card-title">Inicio</div>
          <div class="page-card-desc">Hero slider y contenido principal de la portada.</div>
        </a>

        <a href="pages/nosotros.php" class="page-card" role="listitem">
          <div class="page-card-icon" style="background:linear-gradient(135deg,#0f766e,#14b8a6)" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
          </div>
          <div class="page-card-title">Nosotros</div>
          <div class="page-card-desc">Misión, visión, valores y descripción de la empresa.</div>
        </a>

        <a href="pages/industrias.php" class="page-card" role="listitem">
          <div class="page-card-icon" style="background:linear-gradient(135deg,#7c3aed,#a855f7)" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12,2 2,7 12,12 22,7"/><polyline points="2,17 12,22 22,17"/><polyline points="2,12 12,17 22,12"/></svg>
          </div>
          <div class="page-card-title">Industrias</div>
          <div class="page-card-desc">Sectores industriales y sus descripciones.</div>
        </a>

        <a href="pages/servicios.php" class="page-card" role="listitem">
          <div class="page-card-icon" style="background:linear-gradient(135deg,#b45309,#f59e0b)" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/></svg>
          </div>
          <div class="page-card-title">Servicios</div>
          <div class="page-card-desc">Catálogo de servicios y soluciones ofrecidas.</div>
        </a>

        <a href="pages/partners.php" class="page-card" role="listitem">
          <div class="page-card-icon" style="background:linear-gradient(135deg,#be123c,#f43f5e)" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26 12,2"/></svg>
          </div>
          <div class="page-card-title">Socios</div>
          <div class="page-card-desc">Logos y enlaces de partners estratégicos.</div>
        </a>

        <a href="pages/contacto.php" class="page-card" role="listitem">
          <div class="page-card-icon" style="background:linear-gradient(135deg,#0369a1,#0ea5e9)" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-5.99-5.99 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 15.6 15.6 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 15.8 15.8 0 002.81.7A2 2 0 0122 16.92z"/></svg>
          </div>
          <div class="page-card-title">Contacto</div>
          <div class="page-card-desc">Datos de contacto, mapa y redes sociales.</div>
        </a>

        <a href="pages/proyectos.php" class="page-card" role="listitem">
          <div class="page-card-icon" style="background:linear-gradient(135deg,#065f46,#10b981)" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
          </div>
          <div class="page-card-title">Proyectos</div>
          <div class="page-card-desc">Carrusel de proyectos destacados en la portada.</div>
        </a>

      </div>
    </section>

    <!-- Ver sitio -->
    <section aria-labelledby="viewsite-heading" style="margin-top:28px">
      <div class="section-header">
        <h2 id="viewsite-heading">Ver en el sitio</h2>
        <p>Abre cada página pública en una nueva pestaña</p>
      </div>
      <div class="site-links-grid">

        <a href="../index.html" target="_blank" class="site-link-card">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9,22 9,12 15,12 15,22"/></svg>
          Inicio
        </a>

        <a href="../pages/corporativo/nosotros.html" target="_blank" class="site-link-card">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
          Nosotros
        </a>

        <a href="../pages/corporativo/industrias.html" target="_blank" class="site-link-card">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12,2 2,7 12,12 22,7"/><polyline points="2,17 12,22 22,17"/><polyline points="2,12 12,17 22,12"/></svg>
          Industrias
        </a>

        <a href="../pages/servicios/servicios.html" target="_blank" class="site-link-card">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/></svg>
          Servicios
        </a>

        <a href="../pages/corporativo/soluciones.html" target="_blank" class="site-link-card">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
          Proyectos
        </a>

        <a href="../pages/corporativo/Contacto.html" target="_blank" class="site-link-card">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-5.99-5.99 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 15.6 15.6 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 15.8 15.8 0 002.81.7A2 2 0 0122 16.92z"/></svg>
          Contacto
        </a>

        <a href="../pages/manufactura/maqindus.html" target="_blank" class="site-link-card">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93l-1.41 1.41M4.93 4.93l1.41 1.41M12 2v2M12 20v2M20 12h2M2 12h2M17.66 17.66l-1.41-1.41M6.34 17.66l1.41-1.41"/></svg>
          Manufactura
        </a>

        <a href="../pages/ensamble/ensamble.html" target="_blank" class="site-link-card">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 3 21 3 21 8"/><line x1="4" y1="20" x2="21" y2="3"/><polyline points="21 16 21 21 16 21"/><line x1="15" y1="15" x2="21" y2="21"/></svg>
          Ensamble
        </a>

      </div>
    </section>

  </main>
</div>

<script src="assets/js/auth.js?v=2"></script>
<script>
  AdminSidebar.init('dashboard', './', '../');

  document.getElementById('stat-partners').textContent   = (CM.get('partners')   || []).length;
  document.getElementById('stat-industrias').textContent = (CM.get('industrias')  || []).length;
  document.getElementById('stat-servicios').textContent  = (CM.get('servicios')   || []).length;
  document.getElementById('stat-proyectos').textContent  = (CM.get('proyectos')   || []).length;
  document.getElementById('stat-visits').textContent =
    parseInt(localStorage.getItem('dematiq_visits') || '0', 10).toLocaleString('es-MX');

  (function () {
    const h = new Date().getHours();
    document.getElementById('dash-greeting').textContent =
      h < 12 ? 'Buenos días,' : h < 19 ? 'Buenas tardes,' : 'Buenas noches,';
    const days   = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
    const months = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
    const now    = new Date();
    document.getElementById('dash-date').textContent =
      `${days[now.getDay()]}, ${now.getDate()} de ${months[now.getMonth()]} de ${now.getFullYear()}`;
  })();

  async function handleImport(e) {
    const file = e.target.files[0];
    if (!file) return;
    try {
      await CM.importAll(file);
      showToast('Contenido importado correctamente');
      setTimeout(() => location.reload(), 900);
    } catch (err) { showToast(err.message, 'error'); }
  }
</script>

</body>
</html>
