<?php
require_once __DIR__ . '/../../../includes/auth.php';
require_once __DIR__ . '/../../../includes/conexion.php';
require_once __DIR__ . '/../../../includes/contenido.php';
$user      = Auth::require('/pages/corporativo/login.php');
$content   = Contenido::getAll();
$initials  = strtoupper(substr($user['nombre'], 0, 1));
$csrfToken = Auth::csrfToken();

$fotoPath = '';
try {
    $stmtFoto = $pdo->prepare('SELECT foto FROM usuarios WHERE id = ? LIMIT 1');
    $stmtFoto->execute([$user['id']]);
    $fotoRaw  = $stmtFoto->fetchColumn();
    $fotoPath = $fotoRaw ? '../../' . htmlspecialchars($fotoRaw) : '';
} catch (PDOException $e) {}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Proyectos | DEMATIQ Admin</title>
  <link rel="stylesheet" href="../../assets/css/admin.css?v=9">
  <link rel="icon" type="image/svg+xml" href="../../../assets/images/logos/favicon-d.svg">
  <link rel="stylesheet" href="../../assets/css/proyectos/proyectos.css?v=1">
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
      <span class="admin-topbar-title">Proyectos</span>
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
        <a class="user-dropdown-item danger" id="logoutLink" href="../../logout.php">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16,17 21,12 16,7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
          Cerrar sesión
        </a>
      </div>
    </div>
  </header>

  <div class="admin-content">

    <!-- ══ BANNER ══════════════════════════════════════ -->
    <div class="proy-banner" style="margin-bottom:16px;">
      <div class="proy-banner-mesh"></div>
      <div class="banner-inner">
        <div class="banner-chip"><span class="bdot"></span> Página activa</div>
        <h1 class="banner-title">Proyectos Destacados</h1>
        <p class="banner-desc">Administra el catálogo completo de proyectos. Activa "Destacado" para mostrar un proyecto en el carrusel de la página principal.</p>
        <div class="banner-section-cards">
          <div class="bsc">
            <div class="bsc-icon bsci-orange">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
            </div>
            <div class="bsc-info">
              <div class="bsc-label">Total</div>
              <div class="bsc-val" id="statTotal">0</div>
            </div>
          </div>
          <div class="bsc">
            <div class="bsc-icon bsci-amber">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26 12,2"/></svg>
            </div>
            <div class="bsc-info">
              <div class="bsc-label">Destacados</div>
              <div class="bsc-val" id="statDest">0</div>
            </div>
          </div>
          <div class="bsc">
            <div class="bsc-icon bsci-teal">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21,15 16,10 5,21"/></svg>
            </div>
            <div class="bsc-info">
              <div class="bsc-label">Con imagen</div>
              <div class="bsc-val" id="statImgs">0</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ══ UNSAVED NOTICE ══════════════════════════════ -->
    <div class="unsaved-notice hidden" id="unsavedNotice">
      <div class="un-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
      </div>
      <div class="un-text">
        <strong>Tienes cambios sin guardar</strong>
        <span>Guarda para que se reflejen en la página pública</span>
      </div>
      <button class="un-discard" onclick="cancelProjects()">No guardar</button>
      <button class="un-save" id="mainSaveBtn" onclick="saveProjects()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17,21 17,13 7,13 7,21"/><polyline points="7,3 7,8 15,8"/></svg>
        Guardar ahora
      </button>
    </div>

    <!-- ══ CARD: PROYECTOS ═════════════════════════════ -->
    <div class="section-card" data-accent="orange">
      <div class="sc-head">
        <div class="sc-icon si-orange">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
          </svg>
        </div>
        <div class="sc-head-text">
          <h3>Lista de proyectos</h3>
          <p>Activa "Destacado" en los que quieres mostrar en el carrusel de inicio</p>
          <p class="field-hint">Dimensión recomendada para la imagen: mínimo <strong>1000×625px</strong>, horizontal (16:10 o más ancha) — así se ve en el carrusel de la página principal.</p>
        </div>
        <button class="btn-admin btn-outline-admin" onclick="addProject()" style="flex-shrink:0;font-size:.8rem;padding:7px 14px;gap:6px">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Agregar proyecto
        </button>
      </div>

      <div id="projects-container"></div>

      <button class="add-proy-btn" onclick="addProject()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Agregar nuevo proyecto
      </button>
    </div>

    <!-- ══ SAVE BAR ═════════════════════════════════════ -->
    <div class="save-bar-sticky">
      <div class="save-spacer"></div>
      <a href="/pages/corporativo/proyecto.html" target="_blank" class="btn-admin btn-outline-admin">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
        Ver página
      </a>
    </div>

  </div>
</div>

<!-- ══ BLUR-SAVE PROMPT ══════════════════════════════ -->
<div class="blur-prompt" id="blurPrompt">
  <div class="bp-head">
    <div class="bp-head-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
    </div>
    <div class="bp-head-text">
      <strong>¿Guardar cambios?</strong>
      <span>Tienes cambios sin guardar</span>
    </div>
  </div>
  <div class="bp-actions">
    <button class="bp-yes" onclick="promptSave()">Sí, guardar</button>
    <button class="bp-no"  onclick="hideBlurPrompt()">Ahora no</button>
  </div>
  <div class="bp-bar" id="bpBar"></div>
</div>

<!-- ══ NAV-AWAY MODAL ════════════════════════════════ -->
<div class="nav-modal-backdrop" id="navModal">
  <div class="nav-modal">
    <div class="nm-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
    </div>
    <div class="nm-title">¿Guardar antes de salir?</div>
    <p class="nm-desc">Tienes cambios sin guardar en <strong>Proyectos</strong>. Si sales ahora se perderán.</p>
    <div class="nm-actions">
      <button class="nm-btn primary" onclick="modalSaveAndGo()">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17,21 17,13 7,13 7,21"/><polyline points="7,3 7,8 15,8"/></svg>
        Guardar y salir
      </button>
      <button class="nm-btn danger" onclick="modalDiscardAndGo()">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        Salir sin guardar
      </button>
      <button class="nm-btn cancel" onclick="modalCancel()">Seguir editando</button>
    </div>
  </div>
</div>

<!-- ══ LIGHTBOX (vista previa grande de la imagen) ══ -->
<div class="lightbox-backdrop" id="lightboxModal" onclick="if(event.target===this) closeLightbox()">
  <div class="lightbox-content" id="lightboxContent">
    <button type="button" class="lightbox-close" onclick="closeLightbox()" title="Cerrar">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
    <img id="lightboxImg" src="" alt="" style="display:none">
    <div class="lightbox-caption" id="lightboxCaption"></div>
  </div>
</div>

<?php $profileApiPath = '../../api/profile.php'; $fotoPrefix = '../../'; require __DIR__ . '/../../includes/profile-modal.php'; ?>

<script src="../../assets/js/auth.js?v=2"></script>
<script>const CSRF_TOKEN = '<?= $csrfToken ?>';</script>
<script src="../../assets/js/proyectos/proyectos.js?v=2"></script>

</body>
</html>
