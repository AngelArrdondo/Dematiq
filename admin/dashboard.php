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
  <link rel="stylesheet" href="assets/css/admin.css">
  <link rel="icon" type="image/svg+xml" href="../assets/images/logos/favicon-d.svg">
</head>
<body>

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
      <span class="admin-topbar-title">Dashboard</span>
    </div>
    <div class="admin-topbar-user">
      <span><?= htmlspecialchars($user['nombre']) ?></span>
      <div class="admin-avatar"><?= strtoupper(substr($user['nombre'], 0, 1)) ?></div>
    </div>
  </div>

  <div class="admin-content">
    <div class="admin-page-header">
      <h1 class="admin-page-title">Panel de control</h1>
      <p class="admin-page-sub">Resumen general de DEMATIQ</p>
    </div>

    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
        </div>
        <div class="stat-info">
          <span class="stat-label">Partners</span>
          <span class="stat-value" id="stat-partners">—</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
        </div>
        <div class="stat-info">
          <span class="stat-label">Industrias</span>
          <span class="stat-value" id="stat-industrias">—</span>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
        </div>
        <div class="stat-info">
          <span class="stat-label">Visitas</span>
          <span class="stat-value" id="stat-visits">—</span>
        </div>
      </div>
    </div>

    <div class="dashboard-actions">
      <label class="btn-secondary" style="cursor:pointer;">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
        Importar contenido
        <input type="file" accept=".json" id="import-file" style="display:none" onchange="handleImport(event)">
      </label>
    </div>
  </div>

</div>

<script src="assets/js/auth.js"></script>
<script>
  AdminSidebar.init('dashboard', './', '../');

  document.getElementById('stat-partners').textContent   = (CM.get('partners') || []).length;
  document.getElementById('stat-industrias').textContent = (CM.get('industrias') || []).length;
  document.getElementById('stat-visits').textContent     =
    parseInt(localStorage.getItem('dematiq_visits') || '0', 10).toLocaleString('es-MX');

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
