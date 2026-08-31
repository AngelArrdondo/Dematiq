<?php
require_once __DIR__ . '/../../../includes/auth.php';
require_once __DIR__ . '/../../../includes/conexion.php';
require_once __DIR__ . '/../../../includes/contenido.php';
$user      = Auth::require('/pages/corporativo/login.php');
$content   = Contenido::getAll();
$d         = $content['navegacion'] ?? [];
$initials  = strtoupper(substr($user['nombre'], 0, 1));
$csrfToken = Auth::csrfToken();

$fotoPath = '';
try {
    $stmtFoto = $pdo->prepare('SELECT foto FROM usuarios WHERE id = ? LIMIT 1');
    $stmtFoto->execute([$user['id']]);
    $fotoRaw  = $stmtFoto->fetchColumn();
    $fotoPath = $fotoRaw ? '../../' . htmlspecialchars($fotoRaw) : '';
} catch (PDOException $e) {
    error_log('No se pudo obtener foto de perfil (topbar admin): ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Navegación | DEMATIQ Admin</title>
  <link rel="stylesheet" href="../../assets/css/admin.css?v=16">
  <link rel="icon" type="image/svg+xml" href="../../../assets/images/logos/favicon-d.svg">
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
      <span class="admin-topbar-title">Navegación</span>
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
        <a class="user-dropdown-item danger" href="../../logout.php">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16,17 21,12 16,7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
          Cerrar sesión
        </a>
      </div>
    </div>
  </header>

  <div class="admin-content">

    <div class="admin-card" style="margin-bottom:0">
      <div class="admin-card-header">
        <div class="admin-card-title">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
          </svg>
          Textos del menú principal
        </div>
      </div>
      <p style="padding:0 20px;margin-top:-8px;margin-bottom:14px;font-size:.78rem;color:var(--text-lt)">
        Edita el texto y, si quieres, el destino de cada enlace del menú de navegación de todas las páginas del sitio.
        Deja un campo vacío para usar el valor por defecto (texto o destino). El destino puede ser una ruta interna del sitio (ej. <code>/pages/corporativo/Contacto.html</code>) o un link externo completo (ej. <code>https://ejemplo.com</code>).
      </p>

      <?php
        $destinos = [
          'inicio'     => '/index.html',
          'nosotros'   => '/pages/corporativo/nosotros.html',
          'proyectos'  => '/pages/corporativo/soluciones.html',
          'industrias' => '/pages/corporativo/industrias.html',
          'contacto'   => '/pages/corporativo/Contacto.html',
        ];
      ?>

      <div style="padding:0 20px">
        <div class="form-grid-2">
          <div class="form-group">
            <label for="navInicio">Inicio</label>
            <input type="text" id="navInicio" maxlength="40" placeholder="Inicio" autocomplete="off" value="<?= htmlspecialchars($d['inicio'] ?? '') ?>">
            <label for="navInicioUrl" style="display:block;margin-top:8px">URL de destino</label>
            <input type="text" id="navInicioUrl" maxlength="255" placeholder="<?= $destinos['inicio'] ?>" autocomplete="off" value="<?= htmlspecialchars($d['inicioUrl'] ?? '') ?>">
            <p style="font-size:.72rem;color:var(--text-lt);margin:5px 0 0">Déjalo vacío para usar <code><?= $destinos['inicio'] ?></code></p>
          </div>
          <div class="form-group">
            <label for="navNosotros">Sobre Nosotros</label>
            <input type="text" id="navNosotros" maxlength="40" placeholder="Sobre Nosotros" autocomplete="off" value="<?= htmlspecialchars($d['nosotros'] ?? '') ?>">
            <label for="navNosotrosUrl" style="display:block;margin-top:8px">URL de destino</label>
            <input type="text" id="navNosotrosUrl" maxlength="255" placeholder="<?= $destinos['nosotros'] ?>" autocomplete="off" value="<?= htmlspecialchars($d['nosotrosUrl'] ?? '') ?>">
            <p style="font-size:.72rem;color:var(--text-lt);margin:5px 0 0">Déjalo vacío para usar <code><?= $destinos['nosotros'] ?></code></p>
          </div>
          <div class="form-group">
            <label for="navProyectos">Proyectos</label>
            <input type="text" id="navProyectos" maxlength="40" placeholder="Proyectos" autocomplete="off" value="<?= htmlspecialchars($d['proyectos'] ?? '') ?>">
            <label for="navProyectosUrl" style="display:block;margin-top:8px">URL de destino</label>
            <input type="text" id="navProyectosUrl" maxlength="255" placeholder="<?= $destinos['proyectos'] ?>" autocomplete="off" value="<?= htmlspecialchars($d['proyectosUrl'] ?? '') ?>">
            <p style="font-size:.72rem;color:var(--text-lt);margin:5px 0 0">Déjalo vacío para usar <code><?= $destinos['proyectos'] ?></code></p>
          </div>
          <div class="form-group">
            <label for="navIndustrias">Industrias</label>
            <input type="text" id="navIndustrias" maxlength="40" placeholder="Industrias" autocomplete="off" value="<?= htmlspecialchars($d['industrias'] ?? '') ?>">
            <label for="navIndustriasUrl" style="display:block;margin-top:8px">URL de destino</label>
            <input type="text" id="navIndustriasUrl" maxlength="255" placeholder="<?= $destinos['industrias'] ?>" autocomplete="off" value="<?= htmlspecialchars($d['industriasUrl'] ?? '') ?>">
            <p style="font-size:.72rem;color:var(--text-lt);margin:5px 0 0">Déjalo vacío para usar <code><?= $destinos['industrias'] ?></code></p>
          </div>
          <div class="form-group">
            <label for="navContacto">Contacto</label>
            <input type="text" id="navContacto" maxlength="40" placeholder="Contacto" autocomplete="off" value="<?= htmlspecialchars($d['contacto'] ?? '') ?>">
            <label for="navContactoUrl" style="display:block;margin-top:8px">URL de destino</label>
            <input type="text" id="navContactoUrl" maxlength="255" placeholder="<?= $destinos['contacto'] ?>" autocomplete="off" value="<?= htmlspecialchars($d['contactoUrl'] ?? '') ?>">
            <p style="font-size:.72rem;color:var(--text-lt);margin:5px 0 0">Déjalo vacío para usar <code><?= $destinos['contacto'] ?></code></p>
          </div>
          <div class="form-group">
            <label for="navTienda">Tienda</label>
            <input type="text" id="navTienda" maxlength="40" placeholder="Tienda" autocomplete="off" value="<?= htmlspecialchars($d['tienda'] ?? '') ?>">
            <label for="navTiendaUrl" style="display:block;margin-top:8px">URL de destino</label>
            <input type="url" id="navTiendaUrl" maxlength="255" placeholder="https://tienda.dematiq.com.mx/" autocomplete="off" value="<?= htmlspecialchars($d['tiendaUrl'] ?? '') ?>">
            <p style="font-size:.72rem;color:var(--text-lt);margin:5px 0 0">Link externo — se abre en una pestaña nueva. Déjalo vacío para usar <code>https://tienda.dematiq.com.mx/</code></p>
          </div>
        </div>
      </div>
    </div>

    <div class="admin-card">
      <div class="admin-card-header">
        <div class="admin-card-title">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>
          </svg>
          Enlaces junto al inicio de sesión
        </div>
      </div>
      <p style="padding:0 20px;margin-top:-8px;margin-bottom:14px;font-size:.78rem;color:var(--text-lt)">
        Agrega botones adicionales que se muestran en el encabezado, justo al lado del botón de inicio de sesión, en todas las páginas del sitio (por ejemplo WhatsApp, promociones, redes sociales). Se abren en una pestaña nueva.
      </p>

      <div style="padding:0 20px 20px">
        <div id="extrasContainer"></div>
        <button type="button" class="btn-admin btn-outline-admin" id="addExtraBtn" onclick="addExtra()">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Agregar enlace
        </button>
      </div>
    </div>

    <!-- Save bar -->
    <div class="save-bar">
      <a href="/index.html" target="_blank" class="btn-admin btn-outline-admin">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
        Ver en sitio
      </a>
      <button class="btn-admin btn-primary-admin" id="mainSaveBtn" onclick="saveNavegacion()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17,21 17,13 7,13 7,21"/><polyline points="7,3 7,8 15,8"/></svg>
        Guardar cambios
      </button>
    </div>

  </div>
</div>

<?php $profileApiPath = '../../api/profile.php'; $fotoPrefix = '../../'; require __DIR__ . '/../../includes/profile-modal.php'; ?>

<script src="../../assets/js/auth.js?v=5"></script>
<script>const CSRF_TOKEN = '<?= $csrfToken ?>';</script>
<script src="../../assets/js/navegacion/navegacion.js?v=1"></script>

</body>
</html>
