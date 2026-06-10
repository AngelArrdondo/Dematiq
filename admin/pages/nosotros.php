<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/contenido.php';
$user    = Auth::require('/pages/corporativo/login.php');
$content = Contenido::getAll();
$d       = $content['nosotros'] ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Nosotros | DEMATIQ Admin</title>
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
      <span class="admin-topbar-title">Nosotros</span>
    </div>
    <div class="admin-topbar-user">
      <span><?= htmlspecialchars($user['nombre']) ?></span>
      <div class="admin-avatar"><?= strtoupper(substr($user['nombre'], 0, 1)) ?></div>
    </div>
  </div>

  <div class="admin-content">

    <div class="section-header">
      <h1>Página: Sobre Nosotros</h1>
      <p>Edita el contenido de la sección "Sobre Nosotros".</p>
    </div>

    <div class="admin-card">
      <div class="admin-card-header">
        <div class="admin-card-title">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="2" y="7" width="20" height="15" rx="2"/><polyline points="17,2 12,7 7,2"/>
          </svg>
          Sección Hero
        </div>
      </div>
      <div class="form-group">
        <label>Etiqueta (tag)</label>
        <input type="text" id="hero-tag" value="<?= htmlspecialchars($d['hero']['tag'] ?? '') ?>" placeholder="Conócenos">
      </div>
      <div class="form-group">
        <label>Título principal (H1)</label>
        <input type="text" id="hero-h1" value="<?= htmlspecialchars($d['hero']['h1'] ?? '') ?>" placeholder="Sobre Nosotros">
      </div>
      <div class="form-group">
        <label>Subtítulo</label>
        <textarea id="hero-subtitle" placeholder="Empresa mexicana especializada…"><?= htmlspecialchars($d['hero']['subtitle'] ?? '') ?></textarea>
      </div>
    </div>

    <div class="admin-card">
      <div class="admin-card-header">
        <div class="admin-card-title">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/>
          </svg>
          ¿Quiénes Somos?
        </div>
      </div>
      <div class="form-group">
        <label>Párrafo 1</label>
        <textarea id="qs-p1"><?= htmlspecialchars($d['p1'] ?? '') ?></textarea>
      </div>
      <div class="form-group">
        <label>Párrafo 2</label>
        <textarea id="qs-p2"><?= htmlspecialchars($d['p2'] ?? '') ?></textarea>
      </div>
    </div>

    <div class="admin-card">
      <div class="admin-card-header">
        <div class="admin-card-title">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
          </svg>
          Nuestra Filosofía
        </div>
      </div>
      <div class="form-grid-2">
        <div class="form-group">
          <label>Misión</label>
          <textarea id="mision"><?= htmlspecialchars($d['mision'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
          <label>Visión</label>
          <textarea id="vision"><?= htmlspecialchars($d['vision'] ?? '') ?></textarea>
        </div>
      </div>
      <div class="form-group">
        <label>Valores</label>
        <textarea id="valores"><?= htmlspecialchars($d['valores'] ?? '') ?></textarea>
      </div>
    </div>

    <div class="save-bar">
      <a href="../../pages/corporativo/nosotros.html" target="_blank" class="btn-admin btn-outline-admin">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
        </svg>
        Ver página
      </a>
      <button class="btn-admin btn-primary-admin" onclick="saveNosotros()">
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
  AdminSidebar.init('nosotros', '../', '../../');

  function saveNosotros() {
    CM.set('nosotros', {
      hero: {
        tag:      document.getElementById('hero-tag').value.trim(),
        h1:       document.getElementById('hero-h1').value.trim(),
        subtitle: document.getElementById('hero-subtitle').value.trim()
      },
      p1:      document.getElementById('qs-p1').value.trim(),
      p2:      document.getElementById('qs-p2').value.trim(),
      mision:  document.getElementById('mision').value.trim(),
      vision:  document.getElementById('vision').value.trim(),
      valores: document.getElementById('valores').value.trim()
    });
    showToast('Cambios guardados correctamente');
  }
</script>

</body>
</html>
