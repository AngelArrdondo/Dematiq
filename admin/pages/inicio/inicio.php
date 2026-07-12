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
  <title>Inicio | DEMATIQ Admin</title>
  <link rel="stylesheet" href="../../assets/css/admin.css?v=10">
  <link rel="icon" type="image/svg+xml" href="../../../assets/images/logos/favicon-d.svg">
  <link rel="stylesheet" href="../../assets/css/inicio/inicio.css?v=1">
</head>
<body>

<!-- stubs para JS legacy — nunca visibles al usuario -->
<div style="display:none;position:fixed;top:-9999px;left:-9999px;pointer-events:none" aria-hidden="true">
  <span id="deviceBadge">Bienvenido</span>
  <span id="deviceVideoBadge"></span>
  <div  id="deviceNoPoster"></div>
  <img  id="devicePoster" src="" alt="">
</div>

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
      <span class="admin-topbar-title">Inicio</span>
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

    <!-- ── BANNER ─────────────────────────────────────────── -->
    <div class="inicio-banner">
      <div class="banner-mesh"></div>
      <div class="banner-inner">
        <div class="banner-chip">
          <span class="banner-chip-dot"></span>
          Portada activa
        </div>
        <h1 class="banner-title">Hero Principal</h1>
        <p class="banner-desc">Controla el video de fondo, la imagen de respaldo y el texto de bienvenida que ven los visitantes al entrar al sitio.</p>
        <div class="banner-section-cards">
          <div class="bsc">
            <div class="bsc-icon bsci-blue">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
            </div>
            <div>
              <div class="bsc-label">Bienvenida</div>
              <div class="bsc-val" id="statBadge">—</div>
            </div>
          </div>
          <div class="bsc">
            <div class="bsc-icon bsci-teal">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21,15 16,10 5,21"/></svg>
            </div>
            <div>
              <div class="bsc-label">Poster</div>
              <div class="bsc-val" id="statPoster">—</div>
            </div>
          </div>
          <div class="bsc">
            <div class="bsc-icon bsci-violet">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="23,7 16,12 23,17 23,7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
            </div>
            <div>
              <div class="bsc-label">Video</div>
              <div class="bsc-val" id="statVideo">—</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ── QUICK NAV ──────────────────────────────────────── -->
    <nav class="quick-nav" id="quickNav" aria-label="Ir a sección">
      <a class="qn-pill" data-target="secBienvenida"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>Bienvenida</a>
      <a class="qn-pill" data-target="secPoster"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21,15 16,10 5,21"/></svg>Imagen</a>
      <a class="qn-pill" data-target="secVideo"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="23,7 16,12 23,17 23,7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>Video</a>
      <a class="qn-pill" data-target="secSoluciones"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>Soluciones</a>
      <a class="qn-pill" data-target="secCta"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="13,17 18,12 13,7"/><polyline points="6,17 11,12 6,7"/></svg>Botones</a>
      <a class="qn-pill" data-target="secTitulos"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16M4 12h8m-8 6h16"/></svg>Títulos</a>
    </nav>

    <!-- ══ UNSAVED NOTICE ═══════════════════════════════════ -->
    <div class="unsaved-notice hidden" id="unsavedNotice">
      <div class="un-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
      </div>
      <div class="un-text">
        <strong>Tienes cambios sin guardar</strong>
        <span>Guarda para que se reflejen en la página pública</span>
      </div>
      <button class="un-discard" onclick="cancelInicio()">No guardar</button>
      <button class="un-save" id="mainSaveBtn" onclick="saveInicio()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17,21 17,13 7,13 7,21"/><polyline points="7,3 7,8 15,8"/></svg>
        Guardar ahora
      </button>
    </div>

    <!-- ── 1. TEXTO DE BIENVENIDA ─────────────────────────── -->
    <div class="section-card" id="secBienvenida" data-accent="blue" style="animation-delay:.02s">
      <div class="sc-head">
        <div class="sc-icon si-blue">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
        </div>
        <div class="sc-head-text">
          <h3>Texto de bienvenida</h3>
          <p>Aparece sobre el logo en el hero de la portada</p>
        </div>
      </div>
      <div class="sc-body">
        <div class="badge-split">
          <div>
            <div class="field-top">
              <label>Texto de bienvenida <span class="field-tick" id="tickBadge"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20,6 9,17 4,12"/></svg></span></label>
              <span class="field-cnt" id="badgeCount">0/60</span>
            </div>
            <div class="badge-input-wrap">
              <input type="text" id="heroBadge" class="fi" placeholder="Bienvenido" maxlength="60"
                oninput="onBadgeChange(this.value)" onblur="onFieldBlur()">
            </div>
            <p class="field-hint">Aparece encima del logo en el hero · mayúsculas recomendadas</p>
          </div>
          <div>
            <div class="badge-lp">
              <div class="badge-lp-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                Preview en vivo
              </div>
              <div class="badge-lp-frame" id="badgeLpFrame">
                <div class="badge-lp-text" id="badgePreviewText">Bienvenido</div>
                <img src="../../../assets/images/logos/logo1.webp" class="badge-lp-logo" alt="DEMATIQ" onerror="this.style.display='none'">
                <div class="badge-lp-btns">
                  <span class="badge-lp-btn" id="badgeLpBtn1">Cotiza</span>
                  <span class="badge-lp-btn ghost" id="badgeLpBtn2">Servicios</span>
                </div>
              </div>
              <p class="field-hint" style="margin-top:8px">Refleja el badge, el poster y los botones CTA en tiempo real</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ── 2. IMAGEN DE RESPALDO ──────────────────────────── -->
    <div class="section-card" id="secPoster" data-accent="teal" style="animation-delay:.07s">
      <div class="sc-head">
        <div class="sc-icon si-teal">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21,15 16,10 5,21"/></svg>
        </div>
        <div class="sc-head-text">
          <h3>Imagen de respaldo <span style="font-size:.72rem;font-weight:400;color:var(--text-lt)">(poster)</span></h3>
          <p>Se muestra mientras carga el video o si el video no está disponible</p>
        </div>
      </div>
      <div class="sc-body">
        <div class="poster-layout">
          <!-- preview -->
          <div class="poster-frame" id="posterFrame" onclick="document.getElementById('posterFile').click()" title="Clic para seleccionar imagen">
            <div class="poster-no-img" id="posterNoImg">
              <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21,15 16,10 5,21"/></svg>
              <span>Sin imagen</span>
            </div>
            <img id="posterImg" src="" alt="Poster" style="display:none">
            <button type="button" class="preview-zoom-btn" id="posterZoomBtn" style="display:none" title="Ver en grande"
              onclick="event.stopPropagation(); openLightboxImage(document.getElementById('posterImg').src, 'Imagen de respaldo (poster)')">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
            </button>
            <div class="poster-hover-hint">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17,8 12,3 7,8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
              Cambiar imagen
            </div>
          </div>

          <!-- controls -->
          <div class="poster-controls">
            <div class="upload-zone" id="uploadZone"
              onclick="document.getElementById('posterFile').click()"
              ondragover="onDragOver(event)" ondragleave="onDragLeave(event)" ondrop="onDrop(event)">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17,8 12,3 7,8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
              <div><strong id="uploadZoneText">Clic o arrastra una imagen</strong></div>
              <span>JPG, PNG, WebP · máx 5 MB
                <span class="spec-badge" tabindex="0" onclick="event.stopPropagation()" data-tip="Formato horizontal panorámico, mínimo recomendado 1920×1080px (16:9) — entre más ancha, mejor. Se recorta automáticamente tipo &quot;cover&quot; para llenar el hero completo, así que evita fotos verticales, cuadradas o con lo importante muy cerca de los bordes (se puede cortar).">i</span>
              </span>
            </div>
            <input type="file" id="posterFile" accept="image/jpeg,image/png,image/webp,image/gif" style="display:none" onchange="uploadPoster(this)">
            <div class="field" style="margin-bottom:0">
              <div class="field-top">
                <label>Ruta manual <span class="field-tick" id="tickPoster"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20,6 9,17 4,12"/></svg></span></label>
              </div>
              <input type="text" class="fi" id="posterPath" style="font-family:monospace;font-size:.78rem"
                oninput="setPosterPreview(this.value)" onblur="onFieldBlur()"
                placeholder="assets/images/general/index.webp">
              <p class="field-hint">Se recorta automáticamente para llenar el hero (recorte tipo "cover"), sin dimensión mínima obligatoria — pero para que se vea bien sin recortes raros, usa una imagen horizontal panorámica (16:9 o más ancha).</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ── 3. VIDEO ───────────────────────────────────────── -->
    <div class="section-card" id="secVideo" data-accent="violet" style="animation-delay:.12s">
      <div class="sc-head">
        <div class="sc-icon si-violet">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="23,7 16,12 23,17 23,7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
        </div>
        <div class="sc-head-text">
          <h3>Video del hero</h3>
          <p>MP4 en bucle — sube directamente o escribe la ruta manualmente</p>
        </div>
        <div class="video-status-pill empty" id="videoPill">
          <span class="video-status-dot"></span>
          Sin video
        </div>
      </div>
      <div class="sc-body">
        <div class="video-layout">

          <!-- left: preview + analysis -->
          <div>
            <div class="video-preview-frame">
              <div class="video-no-preview" id="videoNoPreview">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><polygon points="23,7 16,12 23,17 23,7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
                <span>Sin video</span>
              </div>
              <video id="videoPlayer" muted loop playsinline style="display:none"
                onerror="this.style.display='none';document.getElementById('videoNoPreview').style.display='';document.getElementById('videoZoomBtn').style.display='none'"></video>
              <button type="button" class="preview-zoom-btn" id="videoZoomBtn" style="display:none" title="Ver en grande"
                onclick="event.stopPropagation(); openLightboxVideo(document.getElementById('videoPlayer').src, 'Video del hero')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
              </button>
            </div>

            <div class="video-analysis" id="videoAnalysis">
              <div class="va-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                Análisis del archivo
              </div>
              <div class="va-grid">
                <div class="va-row"><span class="va-lbl">Tamaño</span><span class="va-val" id="vStatSize">—</span></div>
                <div class="va-row"><span class="va-lbl">Resolución</span><span class="va-val" id="vStatRes">—</span></div>
                <div class="va-row"><span class="va-lbl">Duración</span><span class="va-val" id="vStatDur">—</span></div>
                <div class="va-row"><span class="va-lbl">Bitrate est.</span><span class="va-val" id="vStatBitrate">—</span></div>
              </div>
              <div class="va-checks" id="videoChecks"></div>
            </div>
          </div>

          <!-- right: upload + path -->
          <div style="display:flex;flex-direction:column;gap:10px">
            <div class="upload-zone" id="videoUploadZone"
              onclick="document.getElementById('videoFile').click()"
              ondragover="onVideoDragOver(event)" ondragleave="onVideoDragLeave(event)" ondrop="onVideoDrop(event)">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" width="24" height="24"><polygon points="23,7 16,12 23,17 23,7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
              <div><strong id="videoUploadZoneText">Clic o arrastra un video MP4</strong></div>
              <span>MP4, WebM · máx 80 MB · máx 1080p (Full HD)
                <span class="spec-badge" tabindex="0" onclick="event.stopPropagation()" data-tip="Formato horizontal panorámico (16:9), resolución 1080p (1920×1080) o menor, y corto (≤30 seg) para que el loop no se sienta pesado. Se recorta tipo &quot;cover&quot; y se reproduce en bucle sin sonido — evita videos verticales o con texto/logos cerca de los bordes.">i</span>
              </span>
            </div>
            <input type="file" id="videoFile" accept="video/mp4,video/webm,video/ogg,.mp4,.webm,.ogv"
              style="display:none" onchange="onVideoFileSelect(this)">

            <div class="video-progress-wrap" id="videoProg">
              <div class="video-progress-bar" id="videoProgBar"></div>
              <div class="video-progress-label" id="videoProgLabel">0%</div>
            </div>

            <div class="field" style="margin-bottom:0">
              <div class="field-top">
                <label>Ruta del video <span class="field-tick" id="tickVideo"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20,6 9,17 4,12"/></svg></span></label>
              </div>
              <input type="text" class="fi" id="heroVideo" style="font-family:monospace;font-size:.78rem"
                placeholder="assets/videos/hero.mp4"
                oninput="onVideoChange(this.value)" onblur="onFieldBlur()">
              <p class="field-hint">Se recorta automáticamente para llenar el hero (recorte tipo "cover"), sin dimensión mínima obligatoria — pero para que se vea bien, usa un video horizontal panorámico (16:9), 1080p o menos y de corta duración (≤30 seg) para el loop.</p>
              <p class="field-hint">También puedes subir el archivo directamente a <code style="background:#eef2ff;color:var(--accent);padding:1px 5px;border-radius:3px;font-size:.7rem">assets/videos/</code> en el servidor y escribir la ruta arriba.</p>
            </div>
          </div>

        </div>
      </div>
    </div>

    <!-- ── 4. TARJETAS DE SOLUCIONES ─────────────────────── -->
    <div class="section-card" id="secSoluciones" data-accent="green" style="animation-delay:.17s">
      <div class="sc-head">
        <div class="sc-icon" style="background:linear-gradient(135deg,#065f46,#059669)">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
        </div>
        <div class="sc-head-text">
          <h3>Tarjetas de soluciones</h3>
          <p>Imágenes y títulos de las tarjetas en la sección "Soluciones" del inicio</p>
        </div>
      </div>
      <div class="sc-body">
        <p class="field-hint" style="font-size:.72rem;font-style:normal;margin-bottom:14px">
          El color de la imagen no importa: el sitio la vuelve blanca automáticamente. Usa un <strong>PNG o WebP con fondo transparente</strong> (una silueta tipo ícono, no una foto). La vista previa de cada tarjeta ya aplica ese mismo efecto — si ves un bloque blanco sólido en vez de un ícono, significa que tu imagen no tiene transparencia y necesitas cambiarla.
        </p>
        <div class="field">
          <div class="field-top"><label>Título de la sección</label></div>
          <input type="text" id="solTitulo" class="fi" placeholder="Nuestras Soluciones Y Servicios"
            oninput="checkDirty()" onblur="onFieldBlur()">
        </div>
        <p class="sol-group-label">Fila principal</p>
        <div class="sol-cards-admin-grid" id="solFeaturedGrid"></div>
        <p class="sol-group-label">Máquinas</p>
        <div class="sol-cards-admin-grid" id="solMachinesGrid"></div>
      </div>
    </div>
    <input type="file" id="solFileInput" accept="image/*" style="display:none">

    <!-- ── 5. BOTONES DE ACCIÓN ──────────────────────────── -->
    <div class="section-card" id="secCta" data-accent="orange" style="animation-delay:.22s">
      <div class="sc-head">
        <div class="sc-icon si-orange">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="13,17 18,12 13,7"/><polyline points="6,17 11,12 6,7"/></svg>
        </div>
        <div class="sc-head-text">
          <h3>Botones de acción (CTA)</h3>
          <p>Texto y destino de los dos botones principales del hero</p>
        </div>
      </div>
      <div class="sc-body">
        <div class="cta-pair">
          <div>
            <span class="cta-group-label">Botón 1 — Principal</span>
            <div class="field" style="margin-bottom:10px">
              <div class="field-top"><label>Texto</label></div>
              <input type="text" id="cta1Text" class="fi" placeholder="Cotiza tu proyecto"
                oninput="onCtaChange()" onblur="onFieldBlur()">
            </div>
            <div class="field">
              <div class="field-top"><label>Enlace</label></div>
              <input type="text" id="cta1Href" class="fi" placeholder="pages/corporativo/Contacto.html"
                oninput="onCtaChange()" onblur="onFieldBlur()">
            </div>
          </div>
          <div>
            <span class="cta-group-label">Botón 2 — Secundario</span>
            <div class="field" style="margin-bottom:10px">
              <div class="field-top"><label>Texto</label></div>
              <input type="text" id="cta2Text" class="fi" placeholder="Nuestros servicios"
                oninput="onCtaChange()" onblur="onFieldBlur()">
            </div>
            <div class="field">
              <div class="field-top"><label>Enlace</label></div>
              <input type="text" id="cta2Href" class="fi" placeholder="#soluciones"
                oninput="onCtaChange()" onblur="onFieldBlur()">
            </div>
          </div>
        </div>
        <div class="cta-preview-bar">
          <div class="cta-preview-btn" id="ctaPreview1">Cotiza tu proyecto</div>
          <div class="cta-preview-btn ghost" id="ctaPreview2">Nuestros servicios</div>
        </div>
      </div>
    </div>

    <!-- ── 6. TÍTULOS DE SECCIÓN ─────────────────────────── -->
    <div class="section-card" id="secTitulos" data-accent="indigo" style="animation-delay:.27s">
      <div class="sc-head">
        <div class="sc-icon si-indigo">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16M4 12h8m-8 6h16"/></svg>
        </div>
        <div class="sc-head-text">
          <h3>Títulos de sección</h3>
          <p>Encabezados de "Marcas Asociadas" y "Proyectos destacados" en la portada</p>
        </div>
      </div>
      <div class="sc-body">
        <div class="titles-pair">
          <div class="field">
            <div class="field-top"><label>Sección de Marcas</label></div>
            <input type="text" id="tituloEmpresas" class="fi" placeholder="Marcas Asociadas"
              oninput="checkDirty()" onblur="onFieldBlur()">
          </div>
          <div class="field">
            <div class="field-top"><label>Sección de Proyectos</label></div>
            <input type="text" id="tituloProyectos" class="fi" placeholder="Proyectos destacados"
              oninput="checkDirty()" onblur="onFieldBlur()">
          </div>
        </div>
      </div>
    </div>

    <!-- save bar sticky -->
    <div class="save-bar-sticky" style="justify-content:flex-end">
      <a href="/index.html" target="_blank" class="btn-admin btn-outline-admin">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
        Ver sitio
      </a>
    </div>

  </div>
</div>

<!-- ══ BLUR-SAVE PROMPT ═════════════════════════════════ -->
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

<!-- ══ NAV-AWAY MODAL ═══════════════════════════════════ -->
<div class="nav-modal-backdrop" id="navModal">
  <div class="nav-modal">
    <div class="nm-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
    </div>
    <div class="nm-title">¿Guardar antes de salir?</div>
    <p class="nm-desc">Tienes cambios sin guardar en <strong>Inicio</strong>. Si sales ahora se perderán.</p>
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

<!-- ══ LIGHTBOX (vista previa grande de imágenes/video) ══ -->
<div class="lightbox-backdrop" id="lightboxModal" onclick="if(event.target===this) closeLightbox()">
  <div class="lightbox-content" id="lightboxContent">
    <button type="button" class="lightbox-close" onclick="closeLightbox()" title="Cerrar">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
    <img id="lightboxImg" src="" alt="" style="display:none">
    <video id="lightboxVideo" controls autoplay loop style="display:none"></video>
    <div class="lightbox-caption" id="lightboxCaption"></div>
  </div>
</div>

<?php $profileApiPath = '../../api/profile.php'; $fotoPrefix = '../../'; require __DIR__ . '/../../includes/profile-modal.php'; ?>

<script src="../../assets/js/auth.js?v=2"></script>
<script>const CSRF_TOKEN = '<?= $csrfToken ?>';</script>
<script src="../../assets/js/inicio/inicio.js?v=2"></script>
</body>
</html>
