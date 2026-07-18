  AdminSidebar.init('nosotros', '../../', '../../../');

  /* user menu */
  const userMenuBtn = document.getElementById('userMenuBtn');
  userMenuBtn.addEventListener('click', function(e){
    e.stopPropagation();
    const open = this.classList.toggle('open');
    this.setAttribute('aria-expanded', open);
  });
  userMenuBtn.addEventListener('keydown', function(e){
    if(e.key==='Enter'||e.key===' '){e.preventDefault();this.click();}
    if(e.key==='Escape')this.classList.remove('open');
  });
  document.addEventListener('click', ()=>userMenuBtn.classList.remove('open'));

  /* ─── original snapshot ─────────────────────────── */
  const orig = {
    tag:      document.getElementById('hero-tag').value,
    h1:       document.getElementById('hero-h1').value,
    subtitle: document.getElementById('hero-subtitle').value,
    p1:       document.getElementById('qs-p1').value,
    p2:       document.getElementById('qs-p2').value,
    mision:   document.getElementById('mision').value,
    vision:   document.getElementById('vision').value,
    valores:  document.getElementById('valores').value,
    quienesImg:  document.getElementById('quienesImgPath').value,
    ctaTitulo:    document.getElementById('cta-titulo').value,
    ctaSubtitulo: document.getElementById('cta-subtitulo').value,
    ctaBtn1Text:  document.getElementById('cta-btn1Text').value,
    ctaBtn1Href:  document.getElementById('cta-btn1Href').value,
    ctaBtn2Text:  document.getElementById('cta-btn2Text').value,
    ctaBtn2Href:  document.getElementById('cta-btn2Href').value,
  };
  let dirty = false;

  /* ─── dirty state ────────────────────────────────── */
  function getValues() {
    return {
      tag:      document.getElementById('hero-tag').value,
      h1:       document.getElementById('hero-h1').value,
      subtitle: document.getElementById('hero-subtitle').value,
      p1:       document.getElementById('qs-p1').value,
      p2:       document.getElementById('qs-p2').value,
      mision:   document.getElementById('mision').value,
      vision:   document.getElementById('vision').value,
      valores:  document.getElementById('valores').value,
      quienesImg:  document.getElementById('quienesImgPath').value,
      ctaTitulo:    document.getElementById('cta-titulo').value,
      ctaSubtitulo: document.getElementById('cta-subtitulo').value,
      ctaBtn1Text:  document.getElementById('cta-btn1Text').value,
      ctaBtn1Href:  document.getElementById('cta-btn1Href').value,
      ctaBtn2Text:  document.getElementById('cta-btn2Text').value,
      ctaBtn2Href:  document.getElementById('cta-btn2Href').value,
    };
  }
  function checkDirty() {
    const cur = getValues();
    const changed = Object.keys(orig).some(k => cur[k] !== orig[k]);
    changed ? markDirty() : clearDirty();
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

  /* ─── char counters ──────────────────────────────── */
  function cnt(id, cntId) {
    const el = document.getElementById(id);
    const ct = document.getElementById(cntId);
    if(el && ct) ct.textContent = el.value.length + ' car.';
  }

  /* ─── hero live preview ──────────────────────────── */
  function onHeroInput() {
    checkDirty();
    const tag = document.getElementById('hero-tag').value;
    const h1  = document.getElementById('hero-h1').value;
    const sub = document.getElementById('hero-subtitle').value;
    document.getElementById('prevTag').textContent = tag || 'Conócenos';
    document.getElementById('prevH1').textContent  = h1  || 'Sobre Nosotros';
    document.getElementById('prevSub').textContent = sub;
    document.getElementById('statTag').textContent = tag || '—';
    document.getElementById('statH1').textContent  = h1  || '—';
    cnt('hero-tag','cntTag'); cnt('hero-h1','cntH1'); cnt('hero-subtitle','cntSub');
  }
  function updateFilo() {
    document.getElementById('prevMision').textContent  = document.getElementById('mision').value;
    document.getElementById('prevVision').textContent  = document.getElementById('vision').value;
    document.getElementById('prevValores').textContent = document.getElementById('valores').value;
  }

  /* ─── imagen Quiénes Somos ────────────────────────── */
  const QUIENES_IMG_DEFAULT = 'assets/images/general/img3.webp';

  function setImgPreview(src) {
    const img     = document.getElementById('imgPreview');
    const noImg   = document.getElementById('imgNoImg');
    const zoomBtn = document.getElementById('imgZoomBtn');
    const specs   = document.getElementById('imgSpecs');
    document.getElementById('tickImg')?.classList.toggle('on', !!src);
    /* sin ruta personalizada -> se usa el mismo placeholder por defecto que renderiza el servidor */
    const fullSrc = '../../../' + (src || QUIENES_IMG_DEFAULT);
    img.onload  = () => { img.style.display = 'block'; noImg.style.display = 'none'; if (zoomBtn) zoomBtn.style.display = 'flex'; MediaSpecs.render(specs, fullSrc); };
    img.onerror = () => { img.style.display = 'none';  noImg.style.display = ''; if (zoomBtn) zoomBtn.style.display = 'none'; if (specs) { specs.textContent = ''; specs.classList.add('empty'); } };
    img.src = fullSrc;
    /* si el src no cambió (ej. ya venía renderizado por el servidor), el navegador
       no vuelve a disparar 'load' — forzamos el mismo callback manualmente */
    if (img.complete && img.naturalWidth) img.onload();
    checkDirty();
  }

  function onImgDragOver(e) {
    e.preventDefault();
    document.getElementById('imgUploadZone').classList.add('dragging');
    document.getElementById('imgUploadZoneText').textContent = 'Suelta para subir';
  }
  function onImgDragLeave() {
    document.getElementById('imgUploadZone').classList.remove('dragging');
    document.getElementById('imgUploadZoneText').textContent = 'Clic o arrastra una imagen';
  }
  function onImgDrop(e) {
    e.preventDefault();
    onImgDragLeave();
    const file = e.dataTransfer.files[0];
    if (file) uploadQuienesImgFile(file);
  }
  async function uploadQuienesImg(input) {
    if (!input.files[0]) return;
    await uploadQuienesImgFile(input.files[0]);
    input.value = '';
  }

  /* Dimensiones mínimas recomendadas: en la página pública esta imagen ocupa
     la mitad horizontal de la sección "Quiénes somos" (grid 1fr 1fr, object-fit:
     cover, min-height 420px) — es un panel ancho, no un recuadro vertical,
     por eso pedimos una foto horizontal de al menos 1000×560px. */
  const QUIENES_MIN_W = 1000;
  const QUIENES_MIN_H = 560;
  const QUIENES_MIN_RATIO = 1.2; /* ancho/alto — por debajo de esto es demasiado vertical/cuadrada */

  function probeImageDimensions(url) {
    return new Promise(resolve => {
      const probe = new Image();
      probe.onload  = () => resolve({ w: probe.naturalWidth, h: probe.naturalHeight });
      probe.onerror = () => resolve({ w: 0, h: 0 });
      probe.src = url;
    });
  }

  async function uploadQuienesImgFile(file) {
    const sizeMB = file.size / 1024 / 1024;
    if (file.size > 5 * 1024 * 1024) {
      showToast(`La imagen pesa ${sizeMB.toFixed(1)} MB — el máximo permitido es 5 MB`, 'error'); return;
    }
    if (!file.type.startsWith('image/')) { showToast('Solo se permiten imágenes (JPG, PNG, WebP)', 'error'); return; }

    const localURL = URL.createObjectURL(file);
    const { w, h } = await probeImageDimensions(localURL);
    if (w && h) {
      if (w < QUIENES_MIN_W || h < QUIENES_MIN_H) {
        showToast(`Imagen no subida: ${w}×${h}px es muy pequeña — se necesita mínimo ${QUIENES_MIN_W}×${QUIENES_MIN_H}px (horizontal) para que no se vea borrosa`, 'error');
        URL.revokeObjectURL(localURL);
        return;
      }
      if (w / h < QUIENES_MIN_RATIO) {
        showToast(`Imagen no subida: ${w}×${h}px es demasiado vertical — esta foto se muestra en un panel horizontal, usa una imagen horizontal (apaisada)`, 'error');
        URL.revokeObjectURL(localURL);
        return;
      }
    }

    const oldImgPath = document.getElementById('quienesImgPath').value;
    document.getElementById('quienesImgPath').value = '';
    const img = document.getElementById('imgPreview');
    const zoomBtn = document.getElementById('imgZoomBtn');
    img.src = localURL; img.style.display = 'block';
    if (zoomBtn) zoomBtn.style.display = 'flex';
    document.getElementById('imgNoImg').style.display = 'none';

    const zone = document.getElementById('imgUploadZone');
    const origZoneHTML = zone.innerHTML;
    zone.innerHTML = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="22" height="22"><circle cx="12" cy="12" r="10"/><polyline points="12,6 12,12 16,14"/></svg><div><strong>Subiendo imagen…</strong></div><span>${sizeMB.toFixed(1)} MB · por favor espera</span>`;

    const fd = new FormData();
    fd.append('image', file);
    fd.append('oldPath', oldImgPath || '');
    try {
      const res  = await fetch('../../api/contenido.php', { method: 'POST', headers: { 'X-CSRF-Token': CSRF_TOKEN }, body: fd });
      const json = await res.json();
      if (json.ok) {
        document.getElementById('quienesImgPath').value = json.path;
        setImgPreview(json.path);
        showToast('Imagen subida correctamente');
      } else {
        showToast(json.error || 'Error al subir', 'error');
      }
    } catch {
      showToast('Error de conexión', 'error');
    } finally {
      zone.innerHTML = origZoneHTML;
    }
  }

  /* ─── CTA final ───────────────────────────────────── */
  function onCtaChange() {
    checkDirty();
    document.getElementById('ctaPreview1').textContent = document.getElementById('cta-btn1Text').value || 'Contáctanos';
    document.getElementById('ctaPreview2').textContent = document.getElementById('cta-btn2Text').value || 'Ver proyectos';
  }
  function getCtaValues() {
    return {
      titulo:    document.getElementById('cta-titulo').value.trim(),
      subtitulo: document.getElementById('cta-subtitulo').value.trim(),
      btn1Text:  document.getElementById('cta-btn1Text').value.trim(),
      btn1Href:  document.getElementById('cta-btn1Href').value.trim(),
      btn2Text:  document.getElementById('cta-btn2Text').value.trim(),
      btn2Href:  document.getElementById('cta-btn2Href').value.trim(),
    };
  }

  /* ─── init ───────────────────────────────────────── */
  (function(){
    cnt('hero-tag','cntTag'); cnt('hero-h1','cntH1');
    cnt('hero-subtitle','cntSub');
    cnt('qs-p1','cntP1'); cnt('qs-p2','cntP2');
    document.getElementById('tickImg')?.classList.toggle('on', !!document.getElementById('quienesImgPath').value);
    /* imgPreview siempre trae una imagen renderizada por el servidor (propia o el placeholder por defecto) */
    const zoomBtn = document.getElementById('imgZoomBtn');
    if (zoomBtn) zoomBtn.style.display = 'flex';
    const initImg = document.getElementById('imgPreview');
    const initSpecs = document.getElementById('imgSpecs');
    if (initImg && initSpecs) MediaSpecs.render(initSpecs, initImg.src);
  })();

  /* ─── BLUR-SAVE PROMPT ───────────────────────────── */
  let blurTimer = null;
  let bpAutoTimer = null;

  function onFieldBlur() {
    if (!dirty) return;
    clearTimeout(blurTimer);
    blurTimer = setTimeout(() => {
      if (dirty) showBlurPrompt();
    }, 1400); /* 1.4s — si el usuario salta entre campos no aparece */
  }

  /* cancelar si el usuario foca otro input antes del timeout */
  document.addEventListener('focusin', function(e) {
    if (e.target.matches('input,textarea')) clearTimeout(blurTimer);
  });

  function showBlurPrompt() {
    const el = document.getElementById('blurPrompt');
    const bar = document.getElementById('bpBar');
    el.classList.add('show');
    bar.classList.remove('ticking');
    void bar.offsetWidth; /* reiniciar animación */
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
    await saveNosotros();
  }

  /* ─── CANCEL / SAVE ──────────────────────────────── */
  function cancelNosotros() {
    document.getElementById('hero-tag').value      = orig.tag;
    document.getElementById('hero-h1').value       = orig.h1;
    document.getElementById('hero-subtitle').value = orig.subtitle;
    document.getElementById('qs-p1').value         = orig.p1;
    document.getElementById('qs-p2').value         = orig.p2;
    document.getElementById('mision').value        = orig.mision;
    document.getElementById('vision').value        = orig.vision;
    document.getElementById('valores').value       = orig.valores;
    document.getElementById('quienesImgPath').value = orig.quienesImg;
    document.getElementById('cta-titulo').value     = orig.ctaTitulo;
    document.getElementById('cta-subtitulo').value  = orig.ctaSubtitulo;
    document.getElementById('cta-btn1Text').value   = orig.ctaBtn1Text;
    document.getElementById('cta-btn1Href').value   = orig.ctaBtn1Href;
    document.getElementById('cta-btn2Text').value   = orig.ctaBtn2Text;
    document.getElementById('cta-btn2Href').value   = orig.ctaBtn2Href;
    onHeroInput(); updateFilo();
    setImgPreview(orig.quienesImg);
    onCtaChange();
    cnt('qs-p1','cntP1'); cnt('qs-p2','cntP2');
    clearDirty();
    showToast('Cambios descartados');
  }

  function viewPublic(url) { window.open(url + '?v=' + Date.now(), 'dematiq_public'); }

  async function saveNosotros() {
    try {
      const res = await CM.set('nosotros', {
        hero: {
          tag:      document.getElementById('hero-tag').value.trim(),
          h1:       document.getElementById('hero-h1').value.trim(),
          subtitle: document.getElementById('hero-subtitle').value.trim()
        },
        p1:      document.getElementById('qs-p1').value.trim(),
        p2:      document.getElementById('qs-p2').value.trim(),
        mision:  document.getElementById('mision').value.trim(),
        vision:  document.getElementById('vision').value.trim(),
        valores: document.getElementById('valores').value.trim(),
        quienesImg: document.getElementById('quienesImgPath').value.trim(),
        cta: getCtaValues()
      });
      if (res?.ok) {
        /* actualizar snapshot para que los valores guardados sean el nuevo "original" */
        Object.assign(orig, getValues());
        clearDirty();
        showToast('Cambios guardados correctamente');
        const btn = document.getElementById('mainSaveBtn');
        if (btn) { btn.classList.add('saved'); setTimeout(() => btn.classList.remove('saved'), 900); }
        viewPublic('/pages/corporativo/nosotros.html');
      }
      else showToast(res?.error || 'Error al guardar', 'error');
    } catch { showToast('Error de conexión', 'error'); }
  }

  /* ─── NAVEGACIÓN CON CAMBIOS PENDIENTES ─────────── */
  let pendingNavUrl = null;

  /* interceptar links del sidebar y logout después de que se renderice */
  function interceptNavLinks() {
    const links = document.querySelectorAll(
      '.admin-sidebar a[href], #logoutLink'
    );
    links.forEach(link => {
      link.addEventListener('click', function(e) {
        if (!dirty) return;
        e.preventDefault();
        pendingNavUrl = this.href;
        document.getElementById('navModal').classList.add('show');
      });
    });
  }
  /* El sidebar se construye dinámicamente: esperamos un tick */
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

  /* también con el botón de cerrar del sidebar overlay */
  document.getElementById('sidebar-overlay')?.addEventListener('click', () => {
    if (dirty && pendingNavUrl) return; /* ya hay un pendiente */
  });

  async function modalSaveAndGo() {
    await saveNosotros();
    if (pendingNavUrl) window.location.href = pendingNavUrl;
  }
  function modalDiscardAndGo() {
    dirty = false; /* permitir navegación */
    if (pendingNavUrl) window.location.href = pendingNavUrl;
  }
  function modalCancel() {
    pendingNavUrl = null;
    document.getElementById('navModal').classList.remove('show');
  }

  /* ── Lightbox (vista previa grande) ──────────── */
  function openLightboxImage(src, caption) {
    if (!src) { showToast('Todavía no hay imagen para mostrar', 'error'); return; }
    const modal = document.getElementById('lightboxModal');
    const img   = document.getElementById('lightboxImg');
    const video = document.getElementById('lightboxVideo');
    video.pause(); video.style.display = 'none'; video.removeAttribute('src');
    img.src = src; img.style.display = 'block';
    document.getElementById('lightboxCaption').textContent = caption || '';
    modal.classList.add('show');
  }
  function closeLightbox() {
    const modal = document.getElementById('lightboxModal');
    const video = document.getElementById('lightboxVideo');
    modal.classList.remove('show');
    video.pause();
  }

  /* cerrar modal con Escape / guardar con Ctrl+S */
  document.addEventListener('keydown', e => {
    if ((e.ctrlKey || e.metaKey) && e.key === 's') { e.preventDefault(); saveNosotros(); return; }
    if (e.key === 'Escape') {
      if (document.getElementById('lightboxModal').classList.contains('show')) { closeLightbox(); return; }
      modalCancel();
    }
  });

  /* beforeunload nativo (cierre de pestaña / recargar) */
  window.addEventListener('beforeunload', function(e) {
    if (dirty) {
      e.preventDefault();
      e.returnValue = '¿Salir sin guardar los cambios?';
      return e.returnValue;
    }
  });
