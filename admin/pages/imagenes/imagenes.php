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
} catch (PDOException $e) {
    error_log('No se pudo obtener foto de perfil (topbar admin): ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Imágenes | DEMATIQ Admin</title>
  <link rel="stylesheet" href="../../assets/css/admin.css?v=15">
  <link rel="icon" type="image/svg+xml" href="../../../assets/images/logos/favicon-d.svg">
  <link rel="stylesheet" href="../../assets/css/imagenes/imagenes.css?v=2">
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
      <span class="admin-topbar-title">Gestor de Imágenes</span>
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

    <!-- ══ BANNER ══════════════════════════════════════ -->
    <div class="img-banner" style="margin-bottom:16px;">
      <div class="img-banner-mesh"></div>
      <div class="banner-inner">
        <div class="banner-chip"><span class="bdot"></span> Gestor de archivos</div>
        <h1 class="banner-title">Imágenes del Sitio</h1>
        <p class="banner-desc">Sube, previsualiza y elimina imágenes organizadas por carpeta. Máximo 5 MB por archivo — JPG, PNG, WebP y GIF.</p>
        <div class="banner-section-cards">
          <div class="bsc">
            <div class="bsc-icon bsci-cyan">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21,15 16,10 5,21"/></svg>
            </div>
            <div class="bsc-info">
              <div class="bsc-label">Total imágenes</div>
              <div class="bsc-val" id="statTotal">—</div>
            </div>
          </div>
          <div class="bsc">
            <div class="bsc-icon bsci-ocean">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/></svg>
            </div>
            <div class="bsc-info">
              <div class="bsc-label">Carpeta actual</div>
              <div class="bsc-val" id="statFolder">—</div>
            </div>
          </div>
          <div class="bsc">
            <div class="bsc-icon bsci-teal">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17,8 12,3 7,8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            </div>
            <div class="bsc-info">
              <div class="bsc-label">Carpetas</div>
              <div class="bsc-val">3 carpetas</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ══ CARD: EXPLORADOR ════════════════════════════ -->
    <div class="section-card">

      <!-- header -->
      <div class="sc-head">
        <div class="sc-icon si-cyan">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21,15 16,10 5,21"/>
          </svg>
        </div>
        <div class="sc-head-text">
          <h3>Explorador de imágenes</h3>
          <p>Arrastra archivos al área de subida o haz clic para seleccionarlos</p>
        </div>
        <button class="btn-admin btn-primary-admin"
          onclick="document.getElementById('uploadInput').click()"
          style="flex-shrink:0;font-size:.8rem;padding:7px 14px;gap:6px;background:linear-gradient(135deg,#0e7490,#06b6d4);border:none;">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17,8 12,3 7,8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
          Subir imágenes
        </button>
        <span class="spec-badge spec-badge-r" tabindex="0" style="flex-shrink:0" data-tip="Esta librería no recorta ni redimensiona — sube la imagen ya en el tamaño final que necesitas. Antes de subir, revisa el ícono (i) de la sección donde la vas a usar (Inicio, Nosotros, Industrias, Marcas, etc.) para la medida y proporción exactas — cada una tiene su propia recomendación.">i</span>
        <input type="file" id="uploadInput" accept="image/*" multiple style="display:none" onchange="uploadFiles(this.files)">
      </div>

      <!-- chips de carpeta -->
      <div class="folder-chips-wrap">
        <button class="folder-chip active" data-key="general"   onclick="switchFolder('general',this)">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21,15 16,10 5,21"/></svg>
          General <span class="folder-chip-count" id="cnt-general">…</span>
        </button>
        <button class="folder-chip" data-key="partners"  onclick="switchFolder('partners',this)">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
          Partners <span class="folder-chip-count" id="cnt-partners">…</span>
        </button>
        <button class="folder-chip" data-key="avatares"  onclick="switchFolder('avatares',this)">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          Avatares <span class="folder-chip-count" id="cnt-avatares">…</span>
        </button>
      </div>

      <!-- zona de subida -->
      <div class="upload-section">
        <div class="upload-zone" id="dropZone" onclick="document.getElementById('uploadInput').click()">
          <div class="upload-zone-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17,8 12,3 7,8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
          </div>
          <div class="upload-zone-text">
            <strong>Arrastra imágenes aquí o haz clic</strong>
            <span>JPG, PNG, WebP, GIF · máx 5 MB por archivo</span>
          </div>
        </div>
        <div id="progressWrap" class="upload-progress-wrap">
          <div class="upload-progress"><div class="upload-progress-bar" id="uploadProgressBar"></div></div>
          <div class="upload-progress-label" id="progressLabel">Subiendo…</div>
        </div>
      </div>

      <!-- barra búsqueda / ordenar -->
      <div class="grid-toolbar">
        <div class="search-wrap">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input type="text" class="search-input" id="searchInput" placeholder="Buscar por nombre…" oninput="renderGrid()">
        </div>
        <select class="sort-select" id="sortSelect" onchange="renderGrid()">
          <option value="newest">Más recientes</option>
          <option value="oldest">Más antiguas</option>
          <option value="az">A → Z</option>
          <option value="za">Z → A</option>
          <option value="size">Mayor tamaño</option>
        </select>
        <span class="grid-count" id="gridCount"></span>
      </div>

      <!-- grid de imágenes -->
      <div class="img-grid" id="imgGrid">
        <div class="img-empty">
          <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21,15 16,10 5,21"/></svg>
          <p>Cargando imágenes…</p>
        </div>
      </div>

    </div>

  </div>
</div>

<!-- ══ LIGHTBOX ══════════════════════════════════════ -->
<div class="lightbox" id="lightbox" onclick="closeLightbox()">
  <div class="lightbox-inner" onclick="event.stopPropagation()">
    <img id="lightboxImg" src="" alt="">
    <button class="lightbox-close" onclick="closeLightbox()">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
    <div class="lightbox-caption" id="lightboxCaption"></div>
  </div>
</div>

<!-- ══ DELETE CONFIRM MODAL ══════════════════════════ -->
<div class="del-modal-backdrop" id="delModal">
  <div class="del-modal">
    <div class="del-modal-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3,6 5,6 21,6"/><path d="M19,6l-1,14a2,2,0,0,1-2,2H8a2,2,0,0,1-2-2L5,6"/><path d="M10,11v6"/><path d="M14,11v6"/></svg>
    </div>
    <h3>¿Eliminar imagen?</h3>
    <p>Se eliminará permanentemente del servidor:<br><strong id="delModalPath"></strong></p>
    <p id="delModalUsage" style="display:none;color:#b45309;background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:8px 12px;font-size:.85rem"></p>
    <div class="del-modal-actions">
      <button class="del-btn cancel" onclick="closeDelModal()">Cancelar</button>
      <button class="del-btn confirm" id="delConfirmBtn" onclick="confirmDelete()">Sí, eliminar</button>
    </div>
  </div>
</div>

<?php $profileApiPath = '../../api/profile.php'; $fotoPrefix = '../../'; require __DIR__ . '/../../includes/profile-modal.php'; ?>

<script src="../../assets/js/auth.js?v=4"></script>
<script>const CSRF_TOKEN = '<?= $csrfToken ?>';</script>
<script src="../../assets/js/imagenes/imagenes.js?v=4"></script>

</body>
</html>
