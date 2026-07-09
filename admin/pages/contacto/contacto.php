<?php
require_once __DIR__ . '/../../../includes/auth.php';
require_once __DIR__ . '/../../../includes/conexion.php';
require_once __DIR__ . '/../../../includes/contenido.php';
$user      = Auth::require('/pages/corporativo/login.php');
$content   = Contenido::getAll();
$d         = $content['contacto'] ?? [];
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
  <title>Contacto | DEMATIQ Admin</title>
  <link rel="stylesheet" href="../../assets/css/admin.css?v=7">
  <link rel="icon" type="image/svg+xml" href="../../../assets/images/logos/favicon-d.svg">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
  <link rel="stylesheet" href="../../assets/css/contacto/contacto.css?v=1">
</head>
<body>

<script>window.__DB_CONTENT = <?= json_encode($content, JSON_UNESCAPED_UNICODE) ?>;</script>

<div id="sidebar-overlay" class="sidebar-overlay"></div>
<aside class="admin-sidebar"></aside>

<div class="admin-main">

  <header class="admin-topbar">
    <div style="display:flex;align-items:center;gap:12px">
      <button id="sidebar-toggle" class="mobile-menu-toggle" aria-label="Abrir menú" aria-expanded="false" aria-controls="admin-sidebar">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
        </svg>
      </button>
      <span class="admin-topbar-title">Contacto</span>
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
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="user-menu-chevron" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
      <div class="user-dropdown" id="userDropdown" role="menu">
        <button class="user-dropdown-item" role="menuitem" onclick="openProfileModal()">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          Mi Perfil
        </button>
        <div class="user-dropdown-sep"></div>
        <a class="user-dropdown-item danger" role="menuitem" href="../../logout.php">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16,17 21,12 16,7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
          Cerrar sesión
        </a>
      </div>
    </div>
  </header>

  <div class="admin-content">

    <!-- ══ BANNER ══════════════════════════════════════ -->
    <div class="ct-banner" style="margin-bottom:16px;">
      <div class="ct-banner-mesh"></div>
      <div class="banner-inner">
        <div class="banner-chip"><span class="bdot"></span> Página activa</div>
        <h1 class="banner-title">Información de Contacto</h1>
        <p class="banner-desc">Edita los datos de contacto, horario, ubicación y redes sociales que se muestran en el sitio.</p>
        <div class="banner-section-cards">
          <div class="bsc">
            <div class="bsc-icon bsci-navy">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
            </div>
            <div class="bsc-info">
              <div class="bsc-label">Redes activas</div>
              <div class="bsc-val" id="statSocials">0</div>
            </div>
          </div>
          <div class="bsc">
            <div class="bsc-icon bsci-teal">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12,6 12,12 16,14"/></svg>
            </div>
            <div class="bsc-info">
              <div class="bsc-label">Turnos de horario</div>
              <div class="bsc-val" id="statSlots">0</div>
            </div>
          </div>
          <div class="bsc">
            <div class="bsc-icon bsci-green">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
            </div>
            <div class="bsc-info">
              <div class="bsc-label">Ubicación</div>
              <div class="bsc-val" id="statLoc">Sin fijar</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ══ QUICK NAV ═══════════════════════════════════ -->
    <nav class="quick-nav" id="quickNav" aria-label="Ir a sección">
      <a class="qn-pill" data-target="ctEmail"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><polyline points="2,4 12,13 22,4"/></svg>Email</a>
      <a class="qn-pill" data-target="ctWhatsapp"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z"/></svg>WhatsApp</a>
      <a class="qn-pill" data-target="ctHorario"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12,6 12,12 16,14"/></svg>Horario</a>
      <a class="qn-pill" data-target="ctFestivos"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>Festivos</a>
      <a class="qn-pill" data-target="ctDireccion"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9,22 9,12 15,12 15,22"/></svg>Dirección</a>
      <a class="qn-pill" data-target="ctMapa"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>Mapa</a>
      <a class="qn-pill" data-target="ctSocial"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>Redes</a>
    </nav>

    <!-- ── Email ─────────────────────────────── -->
    <div class="ct-card" id="ctEmail" data-accent="navy" style="animation-delay:.02s">
      <div class="ct-card-head">
        <div class="ct-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><polyline points="2,4 12,13 22,4"/></svg>
        </div>
        <div class="ct-card-head-text">
          <h3>Correo electrónico</h3>
          <p>Dirección de email principal de contacto</p>
        </div>
      </div>
      <div class="ct-body">
        <div class="ct-field">
          <label class="ct-label">Email</label>
          <input type="email" id="email" class="ct-input"
            value="<?= htmlspecialchars($d['email'] ?? '') ?>"
            placeholder="ventas@dematiq.com.mx">
        </div>
      </div>
    </div>

    <!-- ── WhatsApp ───────────────────────────── -->
    <div class="ct-card" id="ctWhatsapp" data-accent="green" style="animation-delay:.05s">
      <div class="ct-card-head">
        <div class="ct-icon green">
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.124.558 4.122 1.529 5.855L.057 23.886a.5.5 0 00.611.637l6.239-1.637A11.94 11.94 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22a9.94 9.94 0 01-5.094-1.396l-.364-.217-3.773.99 1.006-3.671-.238-.378A9.952 9.952 0 012 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z"/></svg>
        </div>
        <div class="ct-card-head-text">
          <h3>WhatsApp</h3>
          <p>Selecciona el país y escribe el número local</p>
        </div>
      </div>
      <div class="ct-body">
        <label class="ct-label">País y número</label>
        <div class="wa-builder">
          <select id="wa-country" class="wa-country-sel" onchange="updateWA()">
            <option value="52"  data-fmt="mx">🇲🇽 +52 México</option>
            <option value="1"   data-fmt="us">🇺🇸 +1 EE.UU / Canadá</option>
            <option value="34"  data-fmt="plain">🇪🇸 +34 España</option>
            <option value="55"  data-fmt="plain">🇧🇷 +55 Brasil</option>
            <option value="54"  data-fmt="plain">🇦🇷 +54 Argentina</option>
            <option value="57"  data-fmt="plain">🇨🇴 +57 Colombia</option>
            <option value="56"  data-fmt="plain">🇨🇱 +56 Chile</option>
            <option value="51"  data-fmt="plain">🇵🇪 +51 Perú</option>
            <option value="49"  data-fmt="plain">🇩🇪 +49 Alemania</option>
            <option value="33"  data-fmt="plain">🇫🇷 +33 Francia</option>
            <option value="44"  data-fmt="plain">🇬🇧 +44 Reino Unido</option>
            <option value="39"  data-fmt="plain">🇮🇹 +39 Italia</option>
            <option value="81"  data-fmt="plain">🇯🇵 +81 Japón</option>
            <option value="86"  data-fmt="plain">🇨🇳 +86 China</option>
            <option value="82"  data-fmt="plain">🇰🇷 +82 Corea del Sur</option>
          </select>
          <input type="tel" id="wa-local" class="wa-number-inp"
            placeholder="4427214891" oninput="updateWA()">
        </div>
        <div class="wa-chips">
          <div class="wa-chip">
            <div class="wa-chip-label">Texto visible en el sitio</div>
            <div class="wa-chip-val" id="wa-display-val">—</div>
          </div>
          <div class="wa-chip green">
            <div class="wa-chip-label">Número de enlace (wa.me/…)</div>
            <div class="wa-chip-val" id="wa-link-val">—</div>
          </div>
        </div>
      </div>
    </div>

    <!-- ── Horario ────────────────────────────── -->
    <div class="ct-card" id="ctHorario" data-accent="teal" style="animation-delay:.09s">
      <div class="ct-card-head">
        <div class="ct-icon teal">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12,6 12,12 16,14"/></svg>
        </div>
        <div class="ct-card-head-text">
          <h3>Horario de atención</h3>
          <p>Activa los días y ajusta la hora de apertura y cierre</p>
        </div>
      </div>
      <div class="ct-body">
        <div id="sched-slots"></div>
        <button type="button" class="sched-add" onclick="addSlot()">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Agregar otro turno
        </button>
        <div class="sched-preview">
          <span class="sched-preview-label">Vista previa</span>
          <span class="sched-preview-val" id="sched-preview-val">—</span>
        </div>
      </div>
    </div>

    <!-- ── Días festivos ──────────────────────── -->
    <div class="ct-card" id="ctFestivos" data-accent="teal" style="animation-delay:.11s">
      <div class="ct-card-head">
        <div class="ct-icon teal">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        </div>
        <div class="ct-card-head-text">
          <h3>Días festivos</h3>
          <p>Fechas específicas en que no hay atención, además del horario semanal</p>
        </div>
      </div>
      <div class="ct-body">
        <div id="festivos-list"></div>
        <button type="button" class="social-add-btn" onclick="addFestivo()">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Agregar fecha
        </button>
      </div>
    </div>

    <!-- ── Dirección ──────────────────────────── -->
    <div class="ct-card" id="ctDireccion" data-accent="navy" style="animation-delay:.13s">
      <div class="ct-card-head">
        <div class="ct-icon navy">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9,22 9,12 15,12 15,22"/></svg>
        </div>
        <div class="ct-card-head-text">
          <h3>Dirección</h3>
          <p>Se llena automáticamente al elegir en el mapa</p>
        </div>
      </div>
      <div class="ct-body">
        <div class="ct-field">
          <label class="ct-label">Dirección completa</label>
          <div style="position:relative">
            <textarea id="direccion" class="ct-input" style="padding-right:42px" placeholder="Haz clic en el mapa o usa tu ubicación…"><?= htmlspecialchars($d['direccion'] ?? '') ?></textarea>
            <span id="dir-auto-badge" title="Se llenó desde el mapa" style="
              display:none; position:absolute; top:10px; right:10px;
              background:var(--brand-xlight); border:1px solid #c8d8f0;
              border-radius:6px; padding:2px 7px; font-size:.65rem;
              font-weight:700; letter-spacing:.6px; text-transform:uppercase;
              color:var(--brand-mid); pointer-events:none; white-space:nowrap;">Auto</span>
          </div>
          <p style="font-size:.72rem;color:var(--text-lt);margin:7px 0 0;display:flex;align-items:center;gap:5px">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            Haz clic en el mapa de abajo y la dirección se llenará sola. También puedes editarla manualmente.
          </p>
        </div>
      </div>
    </div>

    <!-- ── Mapa ───────────────────────────────── -->
    <div class="ct-card" id="ctMapa" data-accent="green" style="animation-delay:.17s">
      <div class="ct-card-head">
        <div class="ct-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
        </div>
        <div class="ct-card-head-text">
          <h3>Ubicación en el mapa</h3>
          <p>Haz clic en el mapa o busca por dirección</p>
        </div>
      </div>
      <div class="ct-body">
        <div class="map-search">
          <input type="text" id="map-search-input" class="map-search-inp"
            placeholder="Buscar empresa, calle o colonia…"
            onkeydown="if(event.key==='Enter')geocodeSearch()">
          <button type="button" class="map-btn" onclick="geocodeSearch()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            Buscar
          </button>
          <button type="button" class="map-btn primary" onclick="getMyLocation()" id="btn-my-loc" title="Usar mi ubicación actual">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3"/><path d="M12 8a4 4 0 100 8 4 4 0 000-8z" opacity=".3"/></svg>
            Mi ubicación
          </button>
        </div>

        <div id="leaflet-map"></div>

        <div class="map-coords-row">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          <input type="text" id="map-coords" placeholder="Haz clic en el mapa para fijar coordenadas" readonly>
        </div>
        <p class="map-tip">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          Haz clic en el mapa o arrastra el pin — la dirección se llenará automáticamente
        </p>
      </div>
    </div>

    <!-- ── Redes sociales ─────────────────────── -->
    <div class="ct-card" id="ctSocial" data-accent="indigo" style="animation-delay:.21s">
      <div class="ct-card-head">
        <div class="ct-icon social">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
        </div>
        <div class="ct-card-head-text">
          <h3>Redes sociales</h3>
          <p>URLs completas de cada perfil</p>
        </div>
      </div>
      <div class="ct-body">
        <div class="social-grid" id="social-grid"></div>
        <div class="social-picker-wrap" id="social-picker-wrap">
          <button type="button" class="social-add-btn" onclick="toggleSocialPicker(event)">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Agregar red social
          </button>
          <div class="social-picker" id="social-picker">
            <div class="social-picker-title">Elige una red social</div>
            <div class="social-picker-grid" id="social-picker-grid"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- ── Save bar ───────────────────────────── -->
    <div class="ct-save-bar">
      <a href="/pages/corporativo/Contacto.html" target="_blank" class="ct-btn-view">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
        Ver página
      </a>
      <button class="ct-btn-save" id="mainSaveBtn" onclick="saveContacto()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17,21 17,13 7,13 7,21"/><polyline points="7,3 7,8 15,8"/></svg>
        Guardar cambios
      </button>
    </div>

  </div>
</div>

<?php $profileApiPath = '../../api/profile.php'; $fotoPrefix = '../../'; require __DIR__ . '/../../includes/profile-modal.php'; ?>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="../../assets/js/auth.js?v=2"></script>
<script>
const CSRF_TOKEN = '<?= $csrfToken ?>';
const _D = <?= json_encode($d, JSON_UNESCAPED_UNICODE) ?>;
</script>
<script src="../../assets/js/contacto/contacto.js?v=1"></script>

</body>
</html>
