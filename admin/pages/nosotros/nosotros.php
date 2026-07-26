<?php
require_once __DIR__ . '/../../../includes/auth.php';
require_once __DIR__ . '/../../../includes/conexion.php';
require_once __DIR__ . '/../../../includes/contenido.php';
$user      = Auth::require('/pages/corporativo/login.php');
$content   = Contenido::getAll();
$d         = $content['nosotros'] ?? [];
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
  <title>Nosotros | DEMATIQ Admin</title>
  <link rel="stylesheet" href="../../assets/css/admin.css?v=15">
  <link rel="icon" type="image/svg+xml" href="../../../assets/images/logos/favicon-d.svg">
  <link rel="stylesheet" href="../../assets/css/nosotros/nosotros.css?v=3">
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
      <span class="admin-topbar-title">Nosotros</span>
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
    <div class="nos-banner" style="margin-bottom:16px;">
      <div class="nos-banner-mesh"></div>
      <div class="banner-inner">
        <div class="banner-chip"><span class="bdot"></span> Página activa</div>
        <h1 class="banner-title">Sobre Nosotros</h1>
        <p class="banner-desc">Controla el encabezado, la imagen y textos de presentación, la filosofía y el CTA final que aparecen en la página pública.</p>
        <div class="banner-section-cards">
          <div class="bsc">
            <div class="bsc-icon bsci-teal">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
            </div>
            <div class="bsc-info">
              <div class="bsc-label">Etiqueta</div>
              <div class="bsc-val" id="statTag"><?= htmlspecialchars($d['hero']['tag'] ?? 'Conócenos') ?></div>
            </div>
          </div>
          <div class="bsc">
            <div class="bsc-icon bsci-indigo">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16M4 12h8M4 18h12"/></svg>
            </div>
            <div class="bsc-info">
              <div class="bsc-label">Título H1</div>
              <div class="bsc-val" id="statH1"><?= htmlspecialchars($d['hero']['h1'] ?? 'Sobre Nosotros') ?></div>
            </div>
          </div>
          <div class="bsc">
            <div class="bsc-icon bsci-green">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26 12,2"/></svg>
            </div>
            <div class="bsc-info">
              <div class="bsc-label">Secciones</div>
              <div class="bsc-val">Hero · Quiénes · Filosofía · CTA</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ── QUICK NAV ──────────────────────────────────────── -->
    <nav class="quick-nav" id="quickNav" aria-label="Ir a sección">
      <a class="qn-pill" data-target="secHero"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>Hero</a>
      <a class="qn-pill" data-target="secQuienes"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>Quiénes somos</a>
      <a class="qn-pill" data-target="secImagen"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21,15 16,10 5,21"/></svg>Imagen</a>
      <a class="qn-pill" data-target="secFilosofia"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26 12,2"/></svg>Filosofía</a>
      <a class="qn-pill" data-target="secCta"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2L3 14h7l-1 8 10-12h-7l1-8z"/></svg>CTA final</a>
    </nav>

    <!-- ══ UNSAVED NOTICE (arriba, animado) ═══════════ -->
    <div class="unsaved-notice hidden" id="unsavedNotice">
      <div class="un-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
      </div>
      <div class="un-text">
        <strong>Tienes cambios sin guardar</strong>
        <span>Guarda para que se reflejen en la página pública</span>
      </div>
      <button class="un-discard" onclick="cancelNosotros()">No guardar</button>
      <button class="un-save" id="mainSaveBtn" onclick="saveNosotros()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17,21 17,13 7,13 7,21"/><polyline points="7,3 7,8 15,8"/></svg>
        Guardar ahora
      </button>
    </div>

    <!-- ══ CARD 1: HERO ═════════════════════════════════ -->
    <div class="section-card" id="secHero" data-accent="teal" style="animation-delay:.02s">
      <div class="sc-head">
        <div class="sc-icon si-teal">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/>
          </svg>
        </div>
        <div class="sc-head-text">
          <h3>Hero de la página</h3>
          <p>Encabezado principal al entrar a Nosotros</p>
        </div>
      </div>
      <div class="sc-body">
        <div class="hero-split">
          <div>
            <div class="field">
              <div class="field-top">
                <label>Etiqueta (tag)</label>
                <span class="field-cnt" id="cntTag">0</span>
              </div>
              <div class="tag-wrap">
                <span class="tag-hash">#</span>
                <input type="text" id="hero-tag" class="fi tag-fi"
                  value="<?= htmlspecialchars($d['hero']['tag'] ?? '') ?>"
                  placeholder="Conócenos" maxlength="40"
                  oninput="onHeroInput()" onblur="onFieldBlur()">
              </div>
              <p class="field-hint">Aparece encima del título en la página pública</p>
            </div>
            <div class="field">
              <div class="field-top">
                <label>Título principal (H1)</label>
                <span class="field-cnt" id="cntH1">0</span>
              </div>
              <input type="text" id="hero-h1" class="fi"
                value="<?= htmlspecialchars($d['hero']['h1'] ?? '') ?>"
                placeholder="Sobre Nosotros" maxlength="80"
                oninput="onHeroInput()" onblur="onFieldBlur()">
            </div>
            <div class="field">
              <div class="field-top">
                <label>Subtítulo / descripción</label>
                <span class="field-cnt" id="cntSub">0</span>
              </div>
              <textarea id="hero-subtitle" class="fi" rows="3" maxlength="300"
                placeholder="Empresa mexicana especializada en automatización y ensamble industrial…"
                oninput="onHeroInput()" onblur="onFieldBlur()"><?= htmlspecialchars($d['hero']['subtitle'] ?? '') ?></textarea>
            </div>
          </div>
          <!-- live preview sticky -->
          <div>
            <div class="lp-box">
              <div class="lp-hd">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                Preview en vivo
              </div>
              <div class="lp-tag" id="prevTag"><?= htmlspecialchars($d['hero']['tag'] ?? 'Conócenos') ?></div>
              <div class="lp-h1"  id="prevH1"><?= htmlspecialchars($d['hero']['h1'] ?? 'Sobre Nosotros') ?></div>
              <div class="lp-rule"></div>
              <div class="lp-sub" id="prevSub"><?= htmlspecialchars($d['hero']['subtitle'] ?? '') ?></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ══ CARD 2: QUIÉNES SOMOS ════════════════════════ -->
    <div class="section-card" id="secQuienes" data-accent="indigo" style="animation-delay:.07s">
      <div class="sc-head">
        <div class="sc-icon si-indigo">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/>
            <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
          </svg>
        </div>
        <div class="sc-head-text">
          <h3>¿Quiénes Somos?</h3>
          <p>Dos párrafos de presentación de la empresa</p>
        </div>
      </div>
      <div class="sc-body">
        <div class="para-item">
          <div class="para-head">
            <div class="para-badge pb-1">1</div>
            <span>Párrafo principal</span>
            <span class="field-cnt" id="cntP1">0 car.</span>
          </div>
          <textarea id="qs-p1" maxlength="600"
            placeholder="En DEMATIQ somos una empresa especializada en soluciones tecnológicas e industriales…"
            oninput="checkDirty(); cnt('qs-p1','cntP1')" onblur="onFieldBlur()"><?= htmlspecialchars($d['p1'] ?? '') ?></textarea>
        </div>
        <div class="para-item">
          <div class="para-head">
            <div class="para-badge pb-2">2</div>
            <span>Párrafo complementario</span>
            <span class="field-cnt" id="cntP2">0 car.</span>
          </div>
          <textarea id="qs-p2" maxlength="600"
            placeholder="Trabajamos de la mano con nuestros clientes y socios estratégicos…"
            oninput="checkDirty(); cnt('qs-p2','cntP2')" onblur="onFieldBlur()"><?= htmlspecialchars($d['p2'] ?? '') ?></textarea>
        </div>
      </div>
    </div>

    <!-- ══ CARD 2.5: IMAGEN QUIÉNES SOMOS ═══════════════ -->
    <div class="section-card" id="secImagen" data-accent="teal" style="animation-delay:.12s">
      <div class="sc-head">
        <div class="sc-icon si-teal">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21,15 16,10 5,21"/></svg>
        </div>
        <div class="sc-head-text">
          <h3>Imagen — ¿Quiénes Somos?</h3>
          <p>Foto que acompaña el texto de presentación en la página pública</p>
        </div>
      </div>
      <div class="sc-body">
        <div class="poster-layout">
          <div class="poster-frame" id="imgFrame" onclick="document.getElementById('imgFile').click()" title="Clic para seleccionar imagen">
            <div class="poster-no-img" id="imgNoImg" style="display:none">
              <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21,15 16,10 5,21"/></svg>
              <span>Sin imagen</span>
            </div>
            <img id="imgPreview" src="../../../<?= htmlspecialchars($d['quienesImg'] ?? 'assets/images/general/img3.webp') ?>" alt="Quiénes somos">
            <button type="button" class="preview-zoom-btn" id="imgZoomBtn" style="display:none" title="Ver en grande"
              onclick="event.stopPropagation(); openLightboxImage(document.getElementById('imgPreview').src, 'Imagen — ¿Quiénes Somos?')">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
            </button>
            <div class="poster-hover-hint">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17,8 12,3 7,8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
              Cambiar imagen
            </div>
          </div>
          <div class="poster-controls">
            <div class="upload-zone" id="imgUploadZone"
              onclick="document.getElementById('imgFile').click()"
              ondragover="onImgDragOver(event)" ondragleave="onImgDragLeave(event)" ondrop="onImgDrop(event)">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17,8 12,3 7,8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
              <div><strong id="imgUploadZoneText">Clic o arrastra una imagen</strong></div>
              <span>JPG, PNG, WebP · máx 5 MB
                <span class="spec-badge" tabindex="0" onclick="event.stopPropagation()" data-tip="Foto horizontal, mínimo 1000×560px (aprox. 16:9). Si es más vertical que 1.2:1 (ancho/alto) o más pequeña, el sistema la rechaza al subirla. Se recorta tipo &quot;cover&quot; en un panel ancho, así que evita que el sujeto esté muy cerca de los bordes.">i</span>
              </span>
            </div>
            <input type="file" id="imgFile" accept="image/jpeg,image/png,image/webp,image/gif" style="display:none" onchange="uploadQuienesImg(this)">
            <div class="field" style="margin-bottom:0">
              <div class="field-top">
                <label>Ruta manual <span class="field-tick" id="tickImg"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20,6 9,17 4,12"/></svg></span></label>
              </div>
              <input type="text" class="fi" id="quienesImgPath" style="font-family:monospace;font-size:.78rem"
                value="<?= htmlspecialchars($d['quienesImg'] ?? '') ?>"
                oninput="setImgPreview(this.value)" onblur="onFieldBlur()"
                placeholder="assets/images/general/img3.webp">
              <p class="field-hint">Se recorta automáticamente para llenar el espacio (recorte tipo "cover"). Usa una foto <strong>horizontal</strong> de al menos 1000×560px — si es más vertical que 1.2:1 (ancho/alto) o más pequeña, se rechaza al subirla.</p>
              <div class="media-analysis" id="imgAnalysis"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ══ CARD 3: FILOSOFÍA ════════════════════════════ -->
    <div class="section-card" id="secFilosofia" data-accent="green" style="animation-delay:.17s">
      <div class="sc-head">
        <div class="sc-icon si-green">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26 12,2"/>
          </svg>
        </div>
        <div class="sc-head-text">
          <h3>Nuestra Filosofía</h3>
          <p>Misión, visión y valores — aparecen como tarjetas en la página</p>
        </div>
      </div>
      <div class="sc-body">
        <div class="filo-grid">
          <div class="filo-card">
            <div class="fc-accent m"></div>
            <div class="fc-head">
              <div class="fc-icon fci-m">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="4"/></svg>
              </div>
              <div class="fc-head-text"><h4>Misión</h4><p>¿Para qué existimos?</p></div>
            </div>
            <textarea id="mision" maxlength="400"
              placeholder="Brindar soluciones integrales en automatización…"
              oninput="checkDirty(); updateFilo()" onblur="onFieldBlur()"><?= htmlspecialchars($d['mision'] ?? '') ?></textarea>
          </div>
          <div class="filo-card">
            <div class="fc-accent v"></div>
            <div class="fc-head">
              <div class="fc-icon fci-v">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              </div>
              <div class="fc-head-text"><h4>Visión</h4><p>¿A dónde vamos?</p></div>
            </div>
            <textarea id="vision" maxlength="400"
              placeholder="Ser líderes en innovación tecnológica…"
              oninput="checkDirty(); updateFilo()" onblur="onFieldBlur()"><?= htmlspecialchars($d['vision'] ?? '') ?></textarea>
          </div>
          <div class="filo-card">
            <div class="fc-accent val"></div>
            <div class="fc-head">
              <div class="fc-icon fci-val">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9,11 12,14 22,4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
              </div>
              <div class="fc-head-text"><h4>Valores</h4><p>¿Cómo lo hacemos?</p></div>
            </div>
            <textarea id="valores" maxlength="400"
              placeholder="Compromiso, innovación, calidad…"
              oninput="checkDirty(); updateFilo()" onblur="onFieldBlur()"><?= htmlspecialchars($d['valores'] ?? '') ?></textarea>
          </div>
        </div>
        <div class="filo-preview">
          <div class="fp-hd">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            Así se verá en la página
          </div>
          <div class="fp-cards">
            <div class="fpc m"><div class="fpc-lbl">Misión</div><div class="fpc-txt" id="prevMision"><?= htmlspecialchars($d['mision'] ?? '') ?></div></div>
            <div class="fpc v"><div class="fpc-lbl">Visión</div><div class="fpc-txt" id="prevVision"><?= htmlspecialchars($d['vision'] ?? '') ?></div></div>
            <div class="fpc val"><div class="fpc-lbl">Valores</div><div class="fpc-txt" id="prevValores"><?= htmlspecialchars($d['valores'] ?? '') ?></div></div>
          </div>
        </div>
      </div>
    </div>

    <!-- ══ CARD 4: CTA FINAL ════════════════════════════ -->
    <div class="section-card" id="secCta" data-accent="indigo" style="animation-delay:.22s">
      <div class="sc-head">
        <div class="sc-icon si-indigo">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2L3 14h7l-1 8 10-12h-7l1-8z"/></svg>
        </div>
        <div class="sc-head-text">
          <h3>CTA final</h3>
          <p>Título, texto y botones de la llamada a la acción al final de la página</p>
        </div>
      </div>
      <div class="sc-body">
        <div class="field">
          <div class="field-top"><label>Título</label></div>
          <input type="text" id="cta-titulo" class="fi" maxlength="120"
            placeholder="¿Listo para automatizar tu proceso?"
            value="<?= htmlspecialchars($d['cta']['titulo'] ?? '') ?>"
            oninput="onCtaChange()" onblur="onFieldBlur()">
        </div>
        <div class="field">
          <div class="field-top"><label>Subtítulo</label></div>
          <textarea id="cta-subtitulo" class="fi" rows="2" maxlength="200"
            placeholder="Cuéntanos tu proyecto y te ayudamos a encontrar la solución a la medida de tu empresa."
            oninput="onCtaChange()" onblur="onFieldBlur()"><?= htmlspecialchars($d['cta']['subtitulo'] ?? '') ?></textarea>
        </div>
        <div class="cta-pair">
          <div>
            <span class="cta-group-label">Botón 1 — Principal</span>
            <div class="field" style="margin-bottom:10px">
              <div class="field-top"><label>Texto</label></div>
              <input type="text" id="cta-btn1Text" class="fi" placeholder="Contáctanos"
                value="<?= htmlspecialchars($d['cta']['btn1Text'] ?? '') ?>"
                oninput="onCtaChange()" onblur="onFieldBlur()">
            </div>
            <div class="field">
              <div class="field-top"><label>Enlace</label></div>
              <input type="text" id="cta-btn1Href" class="fi" placeholder="Contacto.html"
                value="<?= htmlspecialchars($d['cta']['btn1Href'] ?? '') ?>"
                oninput="onCtaChange()" onblur="onFieldBlur()">
            </div>
          </div>
          <div>
            <span class="cta-group-label">Botón 2 — Secundario</span>
            <div class="field" style="margin-bottom:10px">
              <div class="field-top"><label>Texto</label></div>
              <input type="text" id="cta-btn2Text" class="fi" placeholder="Ver proyectos"
                value="<?= htmlspecialchars($d['cta']['btn2Text'] ?? '') ?>"
                oninput="onCtaChange()" onblur="onFieldBlur()">
            </div>
            <div class="field">
              <div class="field-top"><label>Enlace</label></div>
              <input type="text" id="cta-btn2Href" class="fi" placeholder="soluciones.html"
                value="<?= htmlspecialchars($d['cta']['btn2Href'] ?? '') ?>"
                oninput="onCtaChange()" onblur="onFieldBlur()">
            </div>
          </div>
        </div>
        <div class="cta-preview-bar">
          <div class="cta-preview-btn" id="ctaPreview1"><?= htmlspecialchars($d['cta']['btn1Text'] ?? 'Contáctanos') ?></div>
          <div class="cta-preview-btn ghost" id="ctaPreview2"><?= htmlspecialchars($d['cta']['btn2Text'] ?? 'Ver proyectos') ?></div>
        </div>
      </div>
    </div>

    <!-- ══ SAVE BAR ═════════════════════════════════════ -->
    <div class="save-bar-sticky">
      <div class="save-spacer"></div>
      <a href="/pages/corporativo/nosotros.html" target="_blank" class="btn-admin btn-outline-admin">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
        Ver página
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
    <p class="nm-desc">Tienes cambios sin guardar en la página <strong>Nosotros</strong>. Si sales ahora se perderán.</p>
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
    <video id="lightboxVideo" style="display:none"></video>
    <div class="lightbox-caption" id="lightboxCaption"></div>
  </div>
</div>

<?php $profileApiPath = '../../api/profile.php'; $fotoPrefix = '../../'; require __DIR__ . '/../../includes/profile-modal.php'; ?>

<script src="../../assets/js/auth.js?v=5"></script>
<script>const CSRF_TOKEN = '<?= $csrfToken ?>';</script>
<script src="../../assets/js/nosotros/nosotros.js?v=6"></script>

</body>
</html>
