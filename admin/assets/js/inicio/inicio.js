  AdminSidebar.init('inicio', '../../', '../../../');

  /* user menu */
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

  /* ── Load saved values ───────────────────────── */
  const DEFAULTS = { video: 'assets/videos/hero.mp4', badge: 'Bienvenido' };
  const rawHome  = CM.get('home');
  const hero = (rawHome && rawHome.hero && !Array.isArray(rawHome.hero))
    ? Object.assign({}, DEFAULTS, rawHome.hero)
    : Object.assign({}, DEFAULTS);

  let original = Object.assign({}, hero);
  let dirty = false;
  let originalCta = undefined;
  let originalTitulos = undefined;
  let solData = undefined;
  let solOriginal = undefined;

  /* ── Dirty tracking ──────────────────────────── */
  function getValues() {
    return {
      badge:  document.getElementById('heroBadge').value,
      video:  document.getElementById('heroVideo').value,
    };
  }
  function checkDirty() {
    const cur = getValues();
    const heroChanged = cur.badge !== original.badge
                     || cur.video  !== original.video;
    const solChanged = typeof solOriginal !== 'undefined'
      && JSON.stringify(getSolValues()) !== JSON.stringify(solOriginal);
    const ctaChanged = typeof originalCta !== 'undefined'
      && JSON.stringify(getCtaValues()) !== JSON.stringify(originalCta);
    const titulosChanged = typeof originalTitulos !== 'undefined'
      && JSON.stringify(getTitulosValues()) !== JSON.stringify(originalTitulos);
    heroChanged || solChanged || ctaChanged || titulosChanged ? markDirty() : clearDirty();
  }
  function markDirty() {
    dirty = true;
    document.getElementById('unsavedNotice').classList.remove('hidden');
  }
  function clearDirty() {
    dirty = false;
    document.getElementById('unsavedNotice').classList.add('hidden');
    hideBlurPrompt();
  }

  /* ── Blur-save prompt ────────────────────────── */
  let blurTimer = null;
  let bpAutoTimer = null;

  function onFieldBlur() {
    if (!dirty) return;
    clearTimeout(blurTimer);
    blurTimer = setTimeout(() => { if (dirty) showBlurPrompt(); }, 1400);
  }
  document.addEventListener('focusin', function(e) {
    if (e.target.matches('input,textarea')) clearTimeout(blurTimer);
  });
  function showBlurPrompt() {
    const el = document.getElementById('blurPrompt');
    const bar = document.getElementById('bpBar');
    el.classList.add('show');
    bar.classList.remove('ticking');
    void bar.offsetWidth;
    bar.classList.add('ticking');
    clearTimeout(bpAutoTimer);
    bpAutoTimer = setTimeout(hideBlurPrompt, 6000);
  }
  function hideBlurPrompt() {
    document.getElementById('blurPrompt').classList.remove('show');
    document.getElementById('bpBar').classList.remove('ticking');
    clearTimeout(bpAutoTimer);
  }
  async function promptSave() {
    hideBlurPrompt();
    await saveInicio();
  }

  /* ── Nav-away modal ──────────────────────────── */
  let pendingNavUrl = null;
  function interceptNavLinks() {
    document.querySelectorAll('.admin-sidebar a[href], #logoutLink').forEach(link => {
      link.addEventListener('click', function(e) {
        if (!dirty) return;
        e.preventDefault();
        pendingNavUrl = this.href;
        document.getElementById('navModal').classList.add('show');
      });
    });
  }
  setTimeout(interceptNavLinks, 200);

  /* ── Quick nav: scroll-to + scrollspy ─────────── */
  (function initQuickNav() {
    const pills = Array.from(document.querySelectorAll('.qn-pill'));
    if (!pills.length) return;
    pills.forEach(p => p.addEventListener('click', () => {
      const target = document.getElementById(p.dataset.target);
      if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }));
    const sections = pills.map(p => document.getElementById(p.dataset.target)).filter(Boolean);
    function spy() {
      let activeIdx = 0;
      const refY = 140;
      sections.forEach((sec, i) => { if (sec.getBoundingClientRect().top - refY <= 0) activeIdx = i; });
      pills.forEach((p, i) => p.classList.toggle('active', i === activeIdx));
    }
    document.addEventListener('scroll', spy, { passive: true });
    spy();
  })();

  async function modalSaveAndGo() { await saveInicio(); if (pendingNavUrl) window.location.href = pendingNavUrl; }
  function modalDiscardAndGo()    { dirty = false; if (pendingNavUrl) window.location.href = pendingNavUrl; }
  function modalCancel()          { pendingNavUrl = null; document.getElementById('navModal').classList.remove('show'); }

  /* ── Lightbox (vista previa grande) ──────────── */
  function openLightboxImage(src, caption, iconStyle) {
    if (!src) { showToast('Todavía no hay imagen para mostrar', 'error'); return; }
    const modal = document.getElementById('lightboxModal');
    const img   = document.getElementById('lightboxImg');
    const video = document.getElementById('lightboxVideo');
    video.pause(); video.style.display = 'none'; video.removeAttribute('src');
    img.src = src; img.style.display = 'block';
    document.getElementById('lightboxContent').classList.toggle('icon-mode', !!iconStyle);
    document.getElementById('lightboxCaption').textContent = caption || '';
    modal.classList.add('show');
  }
  function openLightboxVideo(src, caption) {
    if (!src) { showToast('Todavía no hay video para mostrar', 'error'); return; }
    const modal = document.getElementById('lightboxModal');
    const img   = document.getElementById('lightboxImg');
    const video = document.getElementById('lightboxVideo');
    document.getElementById('lightboxContent').classList.remove('icon-mode');
    img.style.display = 'none'; img.src = '';
    video.src = src; video.style.display = 'block';
    video.play().catch(() => {});
    document.getElementById('lightboxCaption').textContent = caption || '';
    modal.classList.add('show');
  }
  function closeLightbox() {
    const modal = document.getElementById('lightboxModal');
    const video = document.getElementById('lightboxVideo');
    modal.classList.remove('show');
    video.pause();
  }

  document.addEventListener('keydown', e => {
    if ((e.ctrlKey || e.metaKey) && e.key === 's') { e.preventDefault(); saveInicio(); return; }
    if (e.key === 'Escape') {
      if (document.getElementById('lightboxModal').classList.contains('show')) { closeLightbox(); return; }
      modalCancel();
    }
  });

  window.addEventListener('beforeunload', function(e) {
    if (dirty) { e.preventDefault(); e.returnValue = '¿Salir sin guardar los cambios?'; return e.returnValue; }
  });

  document.getElementById('heroBadge').value  = hero.badge  || '';
  document.getElementById('heroVideo').value  = hero.video  || '';

  onBadgeChange(hero.badge  || '');
  onVideoChange(hero.video  || '');

  /* ── Badge ───────────────────────────────────── */
  function onBadgeChange(val) {
    const text = val.trim() || 'Bienvenido';
    document.getElementById('badgePreviewText').textContent = text;
    document.getElementById('badgeCount').textContent       = val.length + '/60';
    const s = document.getElementById('statBadge');
    s.textContent = val.trim() || '—';
    s.className   = val.trim() ? 'bsc-val' : 'bsc-val dim';
    document.getElementById('tickBadge')?.classList.toggle('on', val.trim().length > 0);
    checkDirty();
  }

  /* ── Video — status pill + banner ───────────── */
  function onVideoChange(val) {
    const pill        = document.getElementById('videoPill');
    const deviceBadge = document.getElementById('deviceVideoBadge');
    const statVideo   = document.getElementById('statVideo');
    const hasVal      = val.trim().length > 0;
    if (hasVal) {
      pill.className = 'video-status-pill active';
      pill.innerHTML = '<span class="video-status-dot"></span> Video configurado';
      deviceBadge.className = 'video-badge on';
      deviceBadge.innerHTML = '<span class="video-badge-dot"></span> Video activo';
      const short = val.trim().split('/').pop();
      statVideo.textContent = short;
      statVideo.className   = 'bsc-val';
    } else {
      pill.className = 'video-status-pill empty';
      pill.innerHTML = '<span class="video-status-dot"></span> Sin video';
      deviceBadge.className = 'video-badge off';
      deviceBadge.innerHTML = '<span class="video-badge-dot"></span> Sin video';
      statVideo.textContent = '—';
      statVideo.className   = 'bsc-val dim';
    }
    document.getElementById('tickVideo')?.classList.toggle('on', hasVal);
    checkDirty();
  }

  /* ── Video — quality analysis (client-side) ── */
  const VCHECK_ICONS = {
    ok:   '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20,6 9,17 4,12"/></svg>',
    warn: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
    bad:  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',
    info: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
  };

  /* file: File recién elegido (da tamaño/formato exactos al instante).
     Si se omite, se analiza localURL como un video ya guardado en el servidor
     — el tamaño se obtiene con una petición HEAD (MediaSpecs.remoteSize). */
  async function analyzeVideo(localURL, file) {
    const probe = document.createElement('video');
    probe.preload = 'metadata';
    probe.muted = true;
    probe.style.cssText = 'position:fixed;left:-9999px;width:1px;height:1px';
    document.body.appendChild(probe);
    probe.src = localURL;
    const sizeBytes = file ? file.size : await MediaSpecs.remoteSize(localURL);
    const formatSrc = file ? (file.name || file.type) : localURL;
    let done = false;
    const failTimer = setTimeout(() => {
      if (done) return;
      done = true;
      document.getElementById('videoAnalysis').classList.remove('visible');
      probe.remove();
    }, 10000);
    probe.onerror = () => { done = true; clearTimeout(failTimer); probe.remove(); };
    probe.onloadedmetadata = () => {
      if (done) return;
      done = true;
      clearTimeout(failTimer);
      const w      = probe.videoWidth;
      const h      = probe.videoHeight;
      const dur    = probe.duration;
      const sizeMB = sizeBytes ? sizeBytes / 1024 / 1024 : 0;
      const kbps   = (dur > 0 && sizeBytes) ? Math.round(sizeBytes * 8 / dur / 1000) : 0;

      document.getElementById('vStatSize').textContent    = sizeBytes ? sizeMB.toFixed(1) + ' MB' : '—';
      document.getElementById('vStatRes').textContent     = w && h ? w + '×' + h : '—';
      document.getElementById('vStatDur').textContent     = isFinite(dur) ? Math.round(dur) + ' seg' : '—';
      document.getElementById('vStatBitrate').textContent = kbps > 0 ? kbps.toLocaleString() + ' kbps' : '—';

      const checks = [];

      /* Formato */
      const isMp4  = (file && file.type === 'video/mp4') || /\.mp4$/i.test(formatSrc);
      const isWebm = (file && file.type === 'video/webm') || /\.webm$/i.test(formatSrc);
      if (isMp4) {
        checks.push({ t: 'ok',   m: 'Formato MP4 — óptimo para todos los navegadores' });
      } else if (isWebm) {
        checks.push({ t: 'warn', m: 'WebM: sin soporte en Safari. Recomendamos MP4 (H.264)' });
      } else {
        checks.push({ t: 'bad',  m: 'Formato no recomendado — convierte a MP4 (H.264)' });
      }

      /* Tamaño */
      if (sizeBytes) {
        if (sizeMB <= 15)       checks.push({ t: 'ok',   m: 'Peso excelente — carga rápida en web' });
        else if (sizeMB <= 40)  checks.push({ t: 'ok',   m: 'Peso aceptable para un hero de video' });
        else if (sizeMB <= 80)  checks.push({ t: 'warn', m: 'Archivo pesado — considera comprimirlo (HandBrake o FFmpeg)' });
        else                    checks.push({ t: 'bad',  m: 'Muy pesado — puede ralentizar la carga del sitio' });
      }

      /* Resolución */
      if (w && h) {
        if (w <= 1920 && h <= 1080)       checks.push({ t: 'ok',   m: 'Resolución ' + w + '×' + h + ' — ideal para web (≤ 1080p)' });
        else if (w <= 2560 && h <= 1440)  checks.push({ t: 'warn', m: 'Resolución 1440p — considera exportar en 1080p para reducir peso' });
        else                              checks.push({ t: 'bad',  m: '4K (' + w + '×' + h + ') — muy pesado para hero web; exporta en 1080p' });
      }

      /* Duración */
      if (isFinite(dur)) {
        if (dur <= 30)       checks.push({ t: 'ok',   m: 'Duración ideal para loop (' + Math.round(dur) + ' seg ≤ 30 seg)' });
        else if (dur <= 60)  checks.push({ t: 'info', m: 'Duración larga (' + Math.round(dur) + ' seg) — los loops cortos pesan menos' });
        else                 checks.push({ t: 'warn', m: 'Video muy largo — el hero usa loops; se recomienda < 30 seg' });
      }

      /* Bitrate */
      if (kbps > 0) {
        if (kbps <= 5000)        checks.push({ t: 'ok',   m: 'Bitrate eficiente — buena relación calidad/peso' });
        else if (kbps <= 12000)  checks.push({ t: 'warn', m: 'Bitrate alto (' + kbps.toLocaleString() + ' kbps) — puede tardar en cargar' });
        else                     checks.push({ t: 'bad',  m: 'Bitrate muy alto — comprime para web (target 2000–5000 kbps)' });
      }

      document.getElementById('videoChecks').innerHTML = checks
        .map(c => `<div class="vcheck ${c.t}">${VCHECK_ICONS[c.t]}<span>${c.m}</span></div>`)
        .join('');

      document.getElementById('videoAnalysis').classList.add('visible');
      probe.remove();
    };
  }

  /* ── Video — drag & drop ─────────────────── */
  function onVideoDragOver(e) {
    e.preventDefault();
    document.getElementById('videoUploadZone').classList.add('dragging');
    document.getElementById('videoUploadZoneText').textContent = 'Suelta para analizar y subir';
  }
  function onVideoDragLeave() {
    document.getElementById('videoUploadZone').classList.remove('dragging');
    document.getElementById('videoUploadZoneText').textContent = 'Clic o arrastra un video MP4';
  }
  function onVideoDrop(e) {
    e.preventDefault();
    onVideoDragLeave();
    const file = e.dataTransfer.files[0];
    if (file) uploadVideoFile(file);
  }
  function onVideoFileSelect(input) {
    if (!input.files[0]) return;
    uploadVideoFile(input.files[0]);
    input.value = '';
  }

  /* ── Video — validación de calidad antes de subir ── */
  function probeVideoMetadata(localURL) {
    return new Promise((resolve) => {
      const probe = document.createElement('video');
      probe.preload = 'metadata';
      probe.onloadedmetadata = () => resolve({ width: probe.videoWidth, height: probe.videoHeight, duration: probe.duration });
      probe.onerror = () => resolve(null);
      probe.src = localURL;
    });
  }

  /* ── Video — upload con progreso ─────────── */
  async function uploadVideoFile(file) {
    const ALLOWED_TYPES = ['video/mp4','video/webm','video/ogg','video/quicktime','video/x-m4v'];
    const ALLOWED_EXTS  = /\.(mp4|webm|ogv|ogg|mov|m4v)$/i;
    if (!ALLOWED_TYPES.includes(file.type) && !ALLOWED_EXTS.test(file.name)) {
      showToast('Solo se permiten videos MP4, WebM u OGG', 'error'); return;
    }
    const sizeMB = file.size / 1024 / 1024;
    if (file.size > 80 * 1024 * 1024) {
      showToast(`El video pesa ${sizeMB.toFixed(1)} MB — el máximo permitido es 80 MB`, 'error'); return;
    }

    const zone       = document.getElementById('videoUploadZone');
    const zoneTextEl = document.getElementById('videoUploadZoneText');
    const originalZoneText = zoneTextEl.textContent;
    zoneTextEl.textContent = `Analizando video (${sizeMB.toFixed(1)} MB)…`;
    zone.style.pointerEvents = 'none'; zone.style.opacity = '.6';

    const probeURL = URL.createObjectURL(file);
    const meta = await probeVideoMetadata(probeURL);
    URL.revokeObjectURL(probeURL);

    zone.style.pointerEvents = ''; zone.style.opacity = '';
    zoneTextEl.textContent = originalZoneText;

    const w = meta?.width, h = meta?.height, dur = meta?.duration;
    const kbps = meta && dur > 0 ? Math.round(file.size * 8 / dur / 1000) : 0;

    const blockers = [];
    if (w && h && (w > 2560 || h > 1440)) blockers.push(`resolución ${w}×${h} demasiado alta (máximo recomendado: 1920×1080 Full HD)`);
    if (kbps > 12000) blockers.push(`bitrate de ${kbps.toLocaleString()} kbps demasiado alto (máximo recomendado: 5000 kbps)`);

    if (blockers.length) {
      showToast('Video rechazado — ' + blockers.join('; '), 'error');
      return;
    }

    const localURL = URL.createObjectURL(file);

    /* preview local inmediato */
    const player = document.getElementById('videoPlayer');
    player.src = localURL;
    player.style.display = 'block';
    document.getElementById('videoNoPreview').style.display = 'none';
    document.getElementById('videoZoomBtn').style.display = 'flex';
    player.play().catch(() => {});

    /* análisis de calidad */
    analyzeVideo(localURL, file);

    /* bloquear zona de upload */
    zone.style.pointerEvents = 'none'; zone.style.opacity = '.45';

    /* progress bar */
    const progWrap = document.getElementById('videoProg');
    const progBar  = document.getElementById('videoProgBar');
    const progLbl  = document.getElementById('videoProgLabel');
    progWrap.classList.remove('done');
    progWrap.classList.add('visible');
    progBar.style.width = '0%';
    progLbl.textContent = 'Preparando subida…';

    const oldVideoPath = document.getElementById('heroVideo').value;

    const xhr = new XMLHttpRequest();
    xhr.open('POST', '../../api/contenido.php');
    xhr.setRequestHeader('X-CSRF-Token', CSRF_TOKEN);

    xhr.upload.onprogress = (e) => {
      if (!e.lengthComputable) return;
      const pct    = Math.round(e.loaded / e.total * 100);
      const loaded = (e.loaded / 1024 / 1024).toFixed(1);
      const total  = (e.total  / 1024 / 1024).toFixed(1);
      progBar.style.width = pct + '%';
      progLbl.textContent = `Subiendo ${pct}% — ${loaded} / ${total} MB`;
    };

    xhr.onload = () => {
      zone.style.pointerEvents = ''; zone.style.opacity = '';
      let json;
      try { json = JSON.parse(xhr.responseText); } catch { json = {}; }

      if (json.ok) {
        document.getElementById('heroVideo').value = json.path;
        onVideoChange(json.path); /* checkDirty() ya se llama dentro */
        progBar.style.width = '100%';
        progLbl.textContent = '¡Video subido correctamente!';
        progWrap.classList.add('done');
        setTimeout(() => progWrap.classList.remove('visible', 'done'), 3000);
        showToast('Video subido correctamente');
        URL.revokeObjectURL(localURL);
      } else {
        showToast(json.error || 'Error al subir el video', 'error');
        progWrap.classList.remove('visible');
        player.style.display = 'none';
        player.src = '';
        document.getElementById('videoNoPreview').style.display = '';
        document.getElementById('videoAnalysis').classList.remove('visible');
      }
    };

    xhr.onerror = () => {
      zone.style.pointerEvents = ''; zone.style.opacity = '';
      showToast('Error de conexión — intenta de nuevo', 'error');
      progWrap.classList.remove('visible');
    };

    const fd = new FormData();
    fd.append('video', file);
    fd.append('oldPath', oldVideoPath || '');
    xhr.send(fd);
  }

  /* cargar preview si ya hay video configurado */
  if (hero.video) {
    const player = document.getElementById('videoPlayer');
    player.src = '../../../' + hero.video;
    player.oncanplay = () => {
      player.style.display = 'block';
      document.getElementById('videoNoPreview').style.display = 'none';
      document.getElementById('videoZoomBtn').style.display = 'flex';
    };
    analyzeVideo(player.src, null);
  }

  /* ── Cancel / Save ───────────────────────────── */
  function cancelInicio() {
    document.getElementById('heroBadge').value  = original.badge  || '';
    document.getElementById('heroVideo').value  = original.video  || '';
    onBadgeChange(original.badge  || '');
    onVideoChange(original.video  || '');
    const player = document.getElementById('videoPlayer');
    player.pause(); player.src = original.video ? '../../../' + original.video : '';
    if (original.video) {
      player.style.display = 'block';
      document.getElementById('videoNoPreview').style.display = 'none';
      document.getElementById('videoZoomBtn').style.display = 'flex';
      analyzeVideo(player.src, null);
    } else {
      player.style.display = 'none';
      document.getElementById('videoNoPreview').style.display = '';
      document.getElementById('videoZoomBtn').style.display = 'none';
      document.getElementById('videoAnalysis').classList.remove('visible');
    }
    document.getElementById('videoProg').classList.remove('visible', 'done');
    /* reset soluciones */
    if (typeof solOriginal !== 'undefined') {
      solData = JSON.parse(JSON.stringify(solOriginal));
      document.getElementById('solTitulo').value = solData.titulo;
      renderSolGrid('solFeaturedGrid', solData.featured, 'featured');
      renderSolGrid('solMachinesGrid', solData.machines, 'machines');
    }
    /* reset CTA */
    if (typeof originalCta !== 'undefined') {
      document.getElementById('cta1Text').value = originalCta.btn1Text;
      document.getElementById('cta1Href').value = originalCta.btn1Href;
      document.getElementById('cta2Text').value = originalCta.btn2Text;
      document.getElementById('cta2Href').value = originalCta.btn2Href;
      document.getElementById('ctaPreview1').textContent = originalCta.btn1Text;
      document.getElementById('ctaPreview2').textContent = originalCta.btn2Text;
    }
    /* reset títulos */
    if (typeof originalTitulos !== 'undefined') {
      document.getElementById('tituloEmpresas').value  = originalTitulos.empresas;
      document.getElementById('tituloProyectos').value = originalTitulos.proyectos;
    }
    clearDirty();
    showToast('Cambios descartados');
  }

  function viewPublic(url) { window.open(url + '?v=' + Date.now(), 'dematiq_public'); }

  async function saveInicio() {
    const home = CM.get('home') || {};
    const ctaVals = getCtaValues();
    home.hero = {
      badge:  document.getElementById('heroBadge').value.trim(),
      video:  document.getElementById('heroVideo').value.trim(),
      cta: ctaVals,
    };
    if (typeof solData !== 'undefined') {
      home.soluciones = getSolValues();
    }
    home.titulos = getTitulosValues();
    try {
      const res = await CM.set('home', home);
      if (res && res.ok) {
        if (home.soluciones) await pushSolucionTitlesToMaquinas(home.soluciones);
        original = Object.assign({}, home.hero);
        if (typeof solData !== 'undefined') solOriginal = JSON.parse(JSON.stringify(home.soluciones));
        originalCta     = Object.assign({}, ctaVals);
        originalTitulos = Object.assign({}, home.titulos);
        clearDirty();
        showToast('Cambios guardados correctamente');
        const btn = document.getElementById('mainSaveBtn');
        if (btn) { btn.classList.add('saved'); setTimeout(() => btn.classList.remove('saved'), 900); }
        viewPublic('/index.html');
        return true;
      } else {
        showToast(res?.error || 'Error al guardar', 'error');
        return false;
      }
    } catch {
      showToast('Error de conexión', 'error');
      return false;
    }
  }

  /* ════════════════════════════════════════════════
     SOLUCIONES
     ════════════════════════════════════════════════ */
  const SOL_DEFAULTS = {
    titulo: 'Nuestras Soluciones Y Servicios',
    featured: [
      {titulo:'Servicios de ingeniería',imagen:'assets/images/general/servicios.webp',href:'pages/servicios/servicios.html'},
      {titulo:'Manufactura',subtitulo:'Maquinados industriales',descripcion:'Diseño 2D y 3D',imagen:'assets/images/general/manufactura.png',href:'pages/manufactura/maqindus.html'},
      {titulo:'Ensamble',imagen:'assets/images/general/ensamble.png',href:'pages/ensamble/ensamble.html'}
    ],
    machines: [
      {titulo:'Máquinas de control de torque',imagen:'assets/images/general/maquinas de control de torque.webp',href:'pages/maquinas/maqcontrol.html'},
      {titulo:'Máquinas probadoras de fuga',imagen:'assets/images/general/maquinas probadoras de fuga.webp',href:'pages/maquinas/maqprob.html'},
      {titulo:'Máquinas de inspección',imagen:'assets/images/general/maquinas de inspeccion.webp',href:'pages/maquinas/maqinspe.html'},
      {titulo:'Máquinas de limpieza',imagen:'assets/images/general/maquina de limpieza.png',href:'pages/maquinas/maclim.html'},
      {titulo:'Máquinas de marcado',imagen:'assets/images/general/maquinas de marcado.webp',href:'pages/maquinas/maqmar.html'},
      {titulo:'Celdas robóticas',imagen:'assets/images/general/celdas roboticas.webp',href:'pages/maquinas/macrobot.html'}
    ]
  };

  const _rawSol = (CM.get('home') || {}).soluciones;
  solData = {
    titulo:   _rawSol?.titulo   ?? SOL_DEFAULTS.titulo,
    featured: _rawSol?.featured ? JSON.parse(JSON.stringify(_rawSol.featured)) : JSON.parse(JSON.stringify(SOL_DEFAULTS.featured)),
    machines: _rawSol?.machines ? JSON.parse(JSON.stringify(_rawSol.machines)) : JSON.parse(JSON.stringify(SOL_DEFAULTS.machines))
  };
  solOriginal = JSON.parse(JSON.stringify(solData));

  document.getElementById('solTitulo').value = solData.titulo;

  function getSolValues() {
    return {
      titulo:   document.getElementById('solTitulo').value,
      featured: JSON.parse(JSON.stringify(solData.featured)),
      machines: JSON.parse(JSON.stringify(solData.machines))
    };
  }

  /* ── Sincronizar títulos con el "Título principal (h1)" de cada página de Máquinas ── */
  const MAQUINAS_KEY_BY_URL = {
    '/pages/ensamble/ensamble.html': 'ensamble',
    '/pages/maquinas/maqcontrol.html': 'maqcontrol',
    '/pages/maquinas/maqprob.html': 'maqprob',
    '/pages/maquinas/maqinspe.html': 'maqinspe',
    '/pages/maquinas/maclim.html': 'maclim',
    '/pages/maquinas/maqmar.html': 'maqmar',
    '/pages/maquinas/macrobot.html': 'macrobot',
    '/pages/manufactura/maqindus.html': 'maqindus',
  };

  /* Al guardar Inicio, cualquier título de tarjeta que apunte a una página de
     Máquinas se escribe también como el h1 real de esa página — una sola fuente
     de verdad, sin necesidad de sincronizar manualmente. */
  async function pushSolucionTitlesToMaquinas(sol) {
    const writes = [];
    [sol.featured, sol.machines].forEach(list => {
      (list || []).forEach(card => {
        const normHref = '/' + String(card.href || '').replace(/^\/+/, '');
        const key = MAQUINAS_KEY_BY_URL[normHref];
        if (!key) return;
        const pageData = CM.get(key);
        if (pageData && card.titulo && pageData.titulo !== card.titulo) {
          writes.push(CM.set(key, Object.assign({}, pageData, { titulo: card.titulo })));
        }
      });
    });
    if (writes.length) await Promise.all(writes);
  }

  async function syncSolucionesFromMaquinas() {
    let changed = 0;
    [solData.featured, solData.machines].forEach(list => {
      list.forEach(card => {
        const normHref = '/' + String(card.href || '').replace(/^\/+/, '');
        const key = MAQUINAS_KEY_BY_URL[normHref];
        const titulo = key && CM.get(key)?.titulo;
        if (titulo && card.titulo !== titulo) {
          card.titulo = titulo;
          changed++;
        }
      });
    });

    if (changed) {
      renderSolGrid('solFeaturedGrid', solData.featured, 'featured');
      renderSolGrid('solMachinesGrid', solData.machines, 'machines');
      const ok = await saveInicio();
      if (ok) showToast(`${changed} título(s) sincronizado(s) y guardado(s) desde Máquinas`);
    } else {
      showToast('Los títulos ya están sincronizados');
    }
  }

  /* ── CTA Buttons ─────────────────────────────── */
  const _rawCta      = (CM.get('home') || {})?.hero?.cta || {};
  const _rawTitulos  = (CM.get('home') || {})?.titulos   || {};

  let ctaData = {
    btn1Text: _rawCta.btn1Text || 'Cotiza tu proyecto',
    btn1Href: _rawCta.btn1Href || 'pages/corporativo/Contacto.html',
    btn2Text: _rawCta.btn2Text || 'Nuestros servicios',
    btn2Href: _rawCta.btn2Href || '#soluciones',
  };
  originalCta = Object.assign({}, ctaData);

  let titulosData = {
    empresas:  _rawTitulos.empresas  || 'Marcas Asociadas',
    proyectos: _rawTitulos.proyectos || 'Proyectos destacados',
  };
  originalTitulos = Object.assign({}, titulosData);

  document.getElementById('cta1Text').value      = ctaData.btn1Text;
  document.getElementById('cta1Href').value      = ctaData.btn1Href;
  document.getElementById('cta2Text').value      = ctaData.btn2Text;
  document.getElementById('cta2Href').value      = ctaData.btn2Href;
  document.getElementById('tituloEmpresas').value  = titulosData.empresas;
  document.getElementById('tituloProyectos').value = titulosData.proyectos;
  document.getElementById('ctaPreview1').textContent = ctaData.btn1Text;
  document.getElementById('ctaPreview2').textContent = ctaData.btn2Text;
  document.getElementById('badgeLpBtn1').textContent  = ctaData.btn1Text;
  document.getElementById('badgeLpBtn2').textContent  = ctaData.btn2Text;

  function onCtaChange() {
    const t1 = document.getElementById('cta1Text').value || 'Cotiza tu proyecto';
    const t2 = document.getElementById('cta2Text').value || 'Nuestros servicios';
    document.getElementById('ctaPreview1').textContent = t1;
    document.getElementById('ctaPreview2').textContent = t2;
    document.getElementById('badgeLpBtn1').textContent  = t1;
    document.getElementById('badgeLpBtn2').textContent  = t2;
    checkDirty();
  }

  function getCtaValues() {
    return {
      btn1Text: document.getElementById('cta1Text').value.trim(),
      btn1Href: document.getElementById('cta1Href').value.trim(),
      btn2Text: document.getElementById('cta2Text').value.trim(),
      btn2Href: document.getElementById('cta2Href').value.trim(),
    };
  }

  function getTitulosValues() {
    return {
      empresas:  document.getElementById('tituloEmpresas').value.trim(),
      proyectos: document.getElementById('tituloProyectos').value.trim(),
    };
  }

  const escSol = s => String(s ?? '').replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;');

  function renderSolGrid(gridId, cards, prefix) {
    const grid = document.getElementById(gridId);
    if (!grid) return;
    grid.innerHTML = cards.map((card, i) => `
      <div class="sol-card-admin">
        <div class="sol-card-admin-img" onclick="pickSolImage('${prefix}',${i})" title="Cambiar imagen">
          <span class="sol-card-preview-badge">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            Vista previa
          </span>
          <img id="solImg_${prefix}_${i}" src="../../../${escSol(card.imagen)}" alt="${escSol(card.titulo)}"
            onload="ImageAnalysis.render(document.getElementById('solAnalysis_${prefix}_${i}'), this.src, { bgCheck: 'full', enforced: true })"
            onerror="this.style.display='none';ImageAnalysis.render(document.getElementById('solAnalysis_${prefix}_${i}'), '')">
          <button type="button" class="preview-zoom-btn" title="Ver en grande" data-prefix="${prefix}" data-idx="${i}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
          </button>
          <span class="spec-badge spec-badge-corner spec-badge-r" tabindex="0" onclick="event.stopPropagation()" data-tip="Ícono cuadrado en PNG o WebP con fondo transparente, idealmente 200×200px o más — es una silueta simple, no una foto. Se pinta de blanco automáticamente al mostrarse; si no tiene transparencia real se verá como un bloque sólido feo.">i</span>
          <div class="sol-card-admin-hint" id="solHint_${prefix}_${i}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17,8 12,3 7,8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            Cambiar
          </div>
        </div>
        <div class="sol-card-admin-body">
          <input type="text" value="${escSol(card.titulo)}"
            oninput="solData.${prefix}[${i}].titulo=this.value;checkDirty()" onblur="onFieldBlur()">
          <span class="sol-card-img-path" id="solPath_${prefix}_${i}" title="${escSol(card.imagen)}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21,15 16,10 5,21"/></svg>
            <span class="sol-card-img-path-text">${escSol(card.imagen)}</span>
          </span>
          <div class="media-analysis" id="solAnalysis_${prefix}_${i}"></div>
          <span class="sol-card-link-hint">${escSol(card.href)}</span>
        </div>
      </div>`).join('');

    grid.querySelectorAll('.preview-zoom-btn').forEach(btn => {
      btn.onclick = (e) => {
        e.stopPropagation();
        const p = btn.dataset.prefix, idx = Number(btn.dataset.idx);
        const imgEl = document.getElementById(`solImg_${p}_${idx}`);
        openLightboxImage(imgEl ? imgEl.src : '', cards[idx]?.titulo || '', true);
      };
    });
  }

  renderSolGrid('solFeaturedGrid', solData.featured, 'featured');
  renderSolGrid('solMachinesGrid', solData.machines, 'machines');

  /* la imagen se pinta de blanco vía CSS (brightness(0) invert(1)); sin
     transparencia, todo el rectángulo se ve como un bloque blanco sólido */
  function probeImageTransparency(file) {
    return new Promise((resolve) => {
      const img = new Image();
      const url = URL.createObjectURL(file);
      img.onload = () => {
        try {
          const size = 32;
          const c = document.createElement('canvas');
          c.width = size; c.height = size;
          const ctx = c.getContext('2d');
          ctx.drawImage(img, 0, 0, size, size);
          const data = ctx.getImageData(0, 0, size, size).data;
          let hasTransparency = false;
          for (let p = 3; p < data.length; p += 4) {
            if (data[p] < 250) { hasTransparency = true; break; }
          }
          resolve(hasTransparency);
        } catch { resolve(true); }
        URL.revokeObjectURL(url);
      };
      img.onerror = () => { URL.revokeObjectURL(url); resolve(true); };
      img.src = url;
    });
  }

  let _solTarget = null;
  document.getElementById('solFileInput').onchange = async function() {
    if (!_solTarget || !this.files[0]) return;
    const { prefix, i } = _solTarget;
    const file = this.files[0];
    this.value = '';
    const sizeMB = file.size / 1024 / 1024;
    if (!file.type.startsWith('image/')) { showToast('Solo se permiten imágenes (JPG, PNG, WebP)', 'error'); return; }
    if (file.size > 5 * 1024 * 1024) {
      showToast(`La imagen pesa ${sizeMB.toFixed(1)} MB — el máximo permitido es 5 MB`, 'error'); return;
    }
    const hasTransparency = await probeImageTransparency(file);
    if (!hasTransparency) {
      showToast('Imagen rechazada — no tiene fondo transparente y en el sitio se vería como un bloque blanco sólido en vez de un ícono. Usa un PNG o WebP con transparencia.', 'error');
      return;
    }
    const hint = document.getElementById('solHint_' + prefix + '_' + i);
    const hintOriginal = hint ? hint.innerHTML : '';
    if (hint) {
      hint.classList.add('uploading');
      hint.innerHTML = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><circle cx="12" cy="12" r="10"/><polyline points="12,6 12,12 16,14"/></svg><span>Subiendo… ${sizeMB.toFixed(1)} MB</span>`;
    }
    const fd = new FormData();
    fd.append('image', file);
    fd.append('oldPath', solData[prefix][i].imagen || '');
    try {
      const res  = await fetch('../../api/contenido.php', { method:'POST', headers:{'X-CSRF-Token':CSRF_TOKEN}, body:fd });
      const json = await res.json();
      if (json.ok) {
        solData[prefix][i].imagen = json.path;
        const img = document.getElementById('solImg_' + prefix + '_' + i);
        if (img) { img.src = '../../../' + json.path; img.style.display = ''; }
        const pathEl = document.getElementById('solPath_' + prefix + '_' + i);
        if (pathEl) {
          pathEl.title = json.path;
          const textEl = pathEl.querySelector('.sol-card-img-path-text');
          if (textEl) textEl.textContent = json.path;
        }
        checkDirty();
        showToast('Imagen actualizada');
      } else { showToast(json.error || 'Error al subir', 'error'); }
    } catch { showToast('Error de conexión', 'error'); }
    finally {
      if (hint) { hint.classList.remove('uploading'); hint.innerHTML = hintOriginal; }
    }
  };

  function pickSolImage(prefix, i) {
    _solTarget = { prefix, i };
    document.getElementById('solFileInput').click();
  }


