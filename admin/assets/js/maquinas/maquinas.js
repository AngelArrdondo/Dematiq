  AdminSidebar.init('maquinas', '../../', '../../../');

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

  /* ─── definición de páginas ──────────────────────── */
  const PAGES = [
    { key: 'ensamble',   label: 'Ensamble',          url: '/pages/ensamble/ensamble.html' },
    { key: 'maqcontrol', label: 'Control de Torque',  url: '/pages/maquinas/maqcontrol.html' },
    { key: 'maqprob',    label: 'Probadoras de Fuga', url: '/pages/maquinas/maqprob.html' },
    { key: 'maqinspe',   label: 'Inspección',         url: '/pages/maquinas/maqinspe.html' },
    { key: 'maclim',     label: 'Limpieza',           url: '/pages/maquinas/maclim.html' },
    { key: 'maqmar',     label: 'Marcado',            url: '/pages/maquinas/maqmar.html' },
    { key: 'macrobot',   label: 'Celdas Robóticas',   url: '/pages/maquinas/macrobot.html' },
    { key: 'maqindus',   label: 'Manufactura',        url: '/pages/manufactura/maqindus.html' },
  ];

  /* ─── estado ──────────────────────────────────────── */
  let currentKey  = 'ensamble';
  let currentData = { titulo: '', subtitulo: '', tabs: [] };
  let origJSON    = JSON.stringify(currentData);
  let dirty       = false;

  /* ─── chips de selección de página ──────────────── */
  function buildChips() {
    const wrap = document.getElementById('pageChips');
    wrap.innerHTML = '';
    PAGES.forEach(p => {
      const btn = document.createElement('button');
      btn.className = 'page-chip' + (p.key === currentKey ? ' active' : '');
      btn.textContent = p.label;
      btn.dataset.key = p.key;
      btn.addEventListener('click', () => requestSwitch(p.key));
      wrap.appendChild(btn);
    });
  }

  /* ─── dirty state ────────────────────────────────── */
  function markDirty() {
    dirty = true;
    document.getElementById('unsavedNotice').classList.remove('hidden');
    updateBanner();
  }
  function clearDirty() {
    dirty    = false;
    origJSON = JSON.stringify(currentData);
    document.getElementById('unsavedNotice').classList.add('hidden');
    hideBlurPrompt();
    updateBanner();
  }
  function checkDirty() {
    const now = {
      titulo:    document.getElementById('fieldTitulo').value,
      subtitulo: document.getElementById('fieldSubtitulo').value,
      tabs:      currentData.tabs
    };
    JSON.stringify(now) !== origJSON ? markDirty() : clearDirty();
  }

  /* ─── banner ─────────────────────────────────────── */
  function updateBanner() {
    const page = PAGES.find(p => p.key === currentKey);
    document.getElementById('statPage').textContent = page ? page.label : currentKey;
    document.getElementById('statTabs').textContent  = currentData.tabs.length || '0';
    document.getElementById('statImgs').textContent  = currentData.tabs.filter(t => t.images && t.images.some(Boolean)).length || '0';
  }

  /* ─── blur prompt ────────────────────────────────── */
  let blurTimer = null, bpAutoTimer = null;
  function onFieldBlur() {
    if (!dirty) { checkDirty(); }
    if (!dirty) return;
    clearTimeout(blurTimer);
    blurTimer = setTimeout(() => { if (dirty) showBlurPrompt(); }, 1400);
  }
  document.addEventListener('focusin', e => {
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
  async function promptSave() { hideBlurPrompt(); await savePage(); }

  /* ─── switch de página ───────────────────────────── */
  let pendingSwitchKey = null;

  function requestSwitch(newKey) {
    if (newKey === currentKey) return;
    if (dirty) {
      pendingSwitchKey = newKey;
      const page = PAGES.find(p => p.key === currentKey);
      document.getElementById('switchModalDesc').textContent =
        `Tienes cambios sin guardar en "${page ? page.label : currentKey}". Si cambias ahora se perderán.`;
      document.getElementById('switchModal').classList.add('show');
    } else {
      loadPage(newKey);
    }
  }
  async function modalSwitchSaveAndGo() {
    document.getElementById('switchModal').classList.remove('show');
    await savePage();
    if (pendingSwitchKey) { loadPage(pendingSwitchKey); pendingSwitchKey = null; }
  }
  function modalSwitchDiscard() {
    document.getElementById('switchModal').classList.remove('show');
    dirty = false;
    if (pendingSwitchKey) { loadPage(pendingSwitchKey); pendingSwitchKey = null; }
  }
  function modalSwitchCancel() {
    document.getElementById('switchModal').classList.remove('show');
    pendingSwitchKey = null;
  }

  /* ─── nav-away modal (salir del admin) ───────────── */
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
  async function modalSaveAndGo()  { await savePage(); if (pendingNavUrl) window.location.href = pendingNavUrl; }
  function modalDiscardAndGo()     { dirty = false; if (pendingNavUrl) window.location.href = pendingNavUrl; }
  function modalNavCancel()        { pendingNavUrl = null; document.getElementById('navModal').classList.remove('show'); }
  /* ─── lightbox (vista previa grande) ─────────────── */
  function openLightboxImage(src, caption) {
    if (!src) { showToast('Todavía no hay imagen para mostrar', 'error'); return; }
    const modal = document.getElementById('lightboxModal');
    const img   = document.getElementById('lightboxImg');
    img.src = src; img.style.display = 'block';
    document.getElementById('lightboxCaption').textContent = caption || '';
    modal.classList.add('show');
  }
  function closeLightbox() {
    document.getElementById('lightboxModal').classList.remove('show');
  }

  document.addEventListener('keydown', e => {
    if ((e.ctrlKey || e.metaKey) && e.key === 's') { e.preventDefault(); savePage(); return; }
    if (e.key === 'Escape') {
      if (document.getElementById('lightboxModal').classList.contains('show')) { closeLightbox(); return; }
      modalSwitchCancel(); modalNavCancel();
    }
  });
  window.addEventListener('beforeunload', function(e) {
    if (dirty) { e.preventDefault(); e.returnValue = '¿Salir sin guardar?'; return e.returnValue; }
  });

  /* ─── quick-nav scrollspy ─────────────────────────── */
  (function initQuickNav() {
    const pills = Array.from(document.querySelectorAll('.qn-pill'));
    if (!pills.length) return;
    const sections = pills.map(p => document.getElementById(p.dataset.target)).filter(Boolean);
    pills.forEach(p => {
      p.addEventListener('click', () => {
        const target = document.getElementById(p.dataset.target);
        if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      });
    });
    function onScroll() {
      let activeIdx = 0;
      const line = window.scrollY + 140;
      sections.forEach((sec, i) => { if (sec.offsetTop <= line) activeIdx = i; });
      pills.forEach((p, i) => p.classList.toggle('active', i === activeIdx));
    }
    document.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  })();

  /* ─── cargar página ──────────────────────────────── */
  function loadPage(key) {
    currentKey = key;
    const raw  = CM.get(key) || {};
    currentData = {
      titulo:    raw.titulo    || '',
      subtitulo: raw.subtitulo || '',
      tabs:      (raw.tabs || []).map(t => ({
        nombre: t.nombre || '',
        desc:   t.desc   || '',
        images: [ t.images?.[0] || '', t.images?.[1] || '' ]
      }))
    };
    document.getElementById('fieldTitulo').value    = currentData.titulo;
    document.getElementById('fieldSubtitulo').value = currentData.subtitulo;
    origJSON = JSON.stringify(currentData);
    dirty    = false;
    document.getElementById('unsavedNotice').classList.add('hidden');
    hideBlurPrompt();
    const page = PAGES.find(p => p.key === key);
    const btn  = document.getElementById('verPaginaBtn');
    if (btn) btn.href = page?.url || '#';
    buildChips();
    renderTabs();
    updateBanner();
  }

  /* ─── carrusel admin (Imagen 1 / Imagen 2) ───────── */
  function maqCarGoTo(ti, idx) {
    const car   = document.getElementById(`maqCar${ti}`);
    const track = document.getElementById(`maqCarTrack${ti}`);
    if (!car || !track) return;
    car.dataset.idx = idx;
    track.style.transform = `translateX(-${idx * 50}%)`;
    car.querySelectorAll('.ica-dot').forEach((d, k) => d.classList.toggle('active', k === idx));
    const label = car.querySelector('.ica-current');
    if (label) label.textContent = `Imagen ${idx + 1}`;
  }
  function maqCarGo(ti, dir) {
    const car = document.getElementById(`maqCar${ti}`);
    if (!car) return;
    const idx = ((parseInt(car.dataset.idx || '0', 10) + dir) + 2) % 2;
    maqCarGoTo(ti, idx);
  }

  /* ─── preview de imagen ──────────────────────────── */
  function setPreview(ti, slot, src) {
    const box   = document.getElementById(`imgBox${ti}_${slot}`);
    if (!box) return;
    const img   = box.querySelector('img');
    const ph    = box.querySelector('.img-placeholder');
    const zoom  = document.getElementById(`imgZoom${ti}_${slot}`);
    const clr   = document.getElementById(`imgClear${ti}_${slot}`);
    const specs = document.getElementById(`imgAnalysis${ti}_${slot}`);
    if (!src) {
      if (img)  img.style.display  = 'none';
      if (ph)   ph.style.display   = '';
      if (zoom) zoom.style.display = 'none';
      if (clr)  clr.style.display  = 'none';
      if (specs) ImageAnalysis.render(specs, '');
      return;
    }
    if (img) {
      img.src = '../../../' + src;
      img.style.display = 'block';
      img.onload  = () => { if (specs) ImageAnalysis.render(specs, img.src, { minW: MAQ_MIN_W, minH: MAQ_MIN_H, minRatio: MAQ_MIN_RATIO, enforced: true }); };
      img.onerror = () => { img.style.display = 'none'; if (ph) ph.style.display = ''; if (zoom) zoom.style.display = 'none'; if (clr) clr.style.display = 'none'; if (specs) ImageAnalysis.render(specs, ''); };
      if (ph)   ph.style.display   = 'none';
      if (zoom) zoom.style.display = 'flex';
      if (clr)  clr.style.display  = 'flex';
    }
  }

  /* ─── quitar imagen (deja el slot vacío, no borra el archivo del servidor) ── */
  function clearImage(ti, slot) {
    currentData.tabs[ti].images[slot] = '';
    const pathEl = document.getElementById(`imgPath${ti}_${slot}`);
    if (pathEl) pathEl.value = '';
    setPreview(ti, slot, '');
    markDirty();
  }

  /* ─── upload imagen ──────────────────────────────── */
  const uploadIcon = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="11" height="11"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17,8 12,3 7,8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>`;
  const trashIcon  = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="14" height="14"><polyline points="3,6 5,6 21,6"/><path d="M19,6l-1,14a2,2,0,0,1-2,2H8a2,2,0,0,1-2-2L5,6"/><path d="M10,11v6"/><path d="M14,11v6"/></svg>`;

  /* La página pública muestra estas imágenes en un carrusel horizontal de
     hasta 760px de ancho por 440px de alto (object-fit: cover) — se piden
     fotos horizontales y con suficiente resolución. */
  const MAQ_MIN_W     = 900;
  const MAQ_MIN_H     = 440;
  const MAQ_MIN_RATIO = 1.4; /* ancho/alto — por debajo de esto es demasiado vertical/cuadrada */

  function probeImageDimensions(url) {
    return new Promise(resolve => {
      const probe = new Image();
      probe.onload  = () => resolve({ w: probe.naturalWidth, h: probe.naturalHeight });
      probe.onerror = () => resolve({ w: 0, h: 0 });
      probe.src = url;
    });
  }

  async function uploadTabImage(ti, slot, input) {
    if (!input.files[0]) return;
    const file = input.files[0];
    if (file.size > 5 * 1024 * 1024) { showToast('Máximo 5 MB por imagen', 'error'); input.value = ''; return; }
    if (!file.type.startsWith('image/')) { showToast('Solo se permiten imágenes (JPG, PNG, WebP)', 'error'); input.value = ''; return; }

    const localURL = URL.createObjectURL(file);
    const { w, h } = await probeImageDimensions(localURL);
    if (w && h) {
      if (w < MAQ_MIN_W || h < MAQ_MIN_H) {
        showToast(`Imagen no subida: ${w}×${h}px es muy pequeña — se necesita mínimo ${MAQ_MIN_W}×${MAQ_MIN_H}px para que no se vea borrosa`, 'error');
        URL.revokeObjectURL(localURL); input.value = ''; return;
      }
      if (w / h < MAQ_MIN_RATIO) {
        showToast(`Imagen no subida: ${w}×${h}px es demasiado vertical — esta foto se recorta en un carrusel horizontal, usa una imagen horizontal (16:9 o más ancha)`, 'error');
        URL.revokeObjectURL(localURL); input.value = ''; return;
      }
    }
    URL.revokeObjectURL(localURL);

    const box = document.getElementById(`imgBox${ti}_${slot}`);
    const img = box?.querySelector('img');
    const ph  = box?.querySelector('.img-placeholder');
    if (img) { img.src = URL.createObjectURL(file); img.style.display = 'block'; }
    if (ph)  ph.style.display = 'none';

    const btn = document.getElementById(`imgBtn${ti}_${slot}`);
    if (btn) { btn.disabled = true; btn.textContent = 'Subiendo…'; }

    const fd = new FormData();
    fd.append('image', file);
    fd.append('folder', 'general');
    fd.append('oldPath', currentData.tabs[ti].images[slot] || '');
    try {
      const res  = await fetch('../../api/contenido.php', { method: 'POST', headers: { 'X-CSRF-Token': CSRF_TOKEN }, body: fd });
      const json = await res.json();
      if (json.ok) {
        currentData.tabs[ti].images[slot] = json.path;
        const pathEl = document.getElementById(`imgPath${ti}_${slot}`);
        if (pathEl) pathEl.value = json.path;
        setPreview(ti, slot, json.path);
        markDirty();
        showToast('Imagen subida correctamente');
      } else {
        showToast(json.error || 'Error al subir', 'error');
        setPreview(ti, slot, currentData.tabs[ti].images[slot] || '');
      }
    } catch {
      showToast('Error de conexión', 'error');
      setPreview(ti, slot, currentData.tabs[ti].images[slot] || '');
    } finally {
      if (btn) { btn.disabled = false; btn.innerHTML = uploadIcon + ` Subir ${slot === 0 ? '1ª' : '2ª'} imagen`; }
      input.value = '';
    }
  }

  /* ─── slot de imagen (reutilizable) ─────────────── */
  function esc(s)    { return String(s || '').replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
  function escTxt(s) { return String(s || '').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

  function imgSlot(ti, slot, val) {
    const slotLabel = slot === 0 ? '1' : '2';
    const btnLabel  = slot === 0 ? '1ª imagen' : '2ª imagen';
    return `
      <div class="img-slot">
        <div class="img-slot-label"><span class="img-slot-num">${slotLabel}</span> Imagen ${slotLabel}</div>
        <div class="img-preview-box" id="imgBox${ti}_${slot}"
          onclick="document.getElementById('imgFile${ti}_${slot}').click()" title="Clic para cambiar imagen">
          <img src="" alt="" style="display:none">
          <button type="button" class="preview-zoom-btn" style="display:none" id="imgZoom${ti}_${slot}" title="Ver en grande"
            onclick="event.stopPropagation(); openLightboxImage(document.getElementById('imgBox${ti}_${slot}').querySelector('img').src, 'Imagen ${slotLabel}')">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
          </button>
          <span class="spec-badge spec-badge-corner spec-badge-r" tabindex="0" onclick="event.stopPropagation()" data-tip="Foto horizontal, mínimo 1000×560px (16:9 o más ancha) — igual que en Industrias y Servicios. Se recorta tipo &quot;cover&quot; en el carrusel, así que evita que lo importante quede muy cerca de los bordes.">i</span>
          <button type="button" class="img-clear-btn" style="display:none" id="imgClear${ti}_${slot}" title="Quitar imagen"
            onclick="event.stopPropagation(); clearImage(${ti},${slot})">${trashIcon}</button>
          <div class="img-placeholder">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21,15 16,10 5,21"/></svg>
            <span>Sin imagen</span>
          </div>
          <div class="img-hover-hint">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17,8 12,3 7,8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            Cambiar
          </div>
        </div>
        <button class="img-upload-btn" id="imgBtn${ti}_${slot}"
          onclick="document.getElementById('imgFile${ti}_${slot}').click()">
          ${uploadIcon} Subir ${btnLabel}
        </button>
        <input type="file" id="imgFile${ti}_${slot}" accept="image/*" style="display:none"
          onchange="uploadTabImage(${ti},${slot},this)">
        <input type="text" class="img-path-field" id="imgPath${ti}_${slot}" value="${esc(val||'')}"
          placeholder="assets/images/general/img${slotLabel}.webp"
          oninput="currentData.tabs[${ti}].images[${slot}]=this.value;setPreview(${ti},${slot},this.value);checkDirty()"
          onblur="onFieldBlur()">
        <div class="media-analysis" id="imgAnalysis${ti}_${slot}"></div>
      </div>`;
  }

  /* ─── render tabs ────────────────────────────────── */
  function renderTabs() {
    const c = document.getElementById('tabs-container');
    c.innerHTML = '';

    if (!currentData.tabs.length) {
      c.innerHTML = `<div class="maq-empty">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/></svg>
        <p>No hay tabs configurados para esta página.</p>
        <strong>Usa el botón <em>Agregar tab</em> para añadir uno.</strong>
      </div>`;
      updateBanner();
      return;
    }

    currentData.tabs.forEach((tab, ti) => {
      const div = document.createElement('div');
      div.className = 'slide-card';
      div.innerHTML = `
        <div class="slide-card-header">
          <div class="slide-badge">
            <span class="slide-num">${ti + 1}</span>
            <span class="slide-header-title${tab.nombre ? '' : ' empty'}" id="tabTitle${ti}">${esc(tab.nombre) || 'Nuevo tab'}</span>
          </div>
          <button class="btn-rm" onclick="removeTab(${ti})" title="Eliminar tab" style="flex-shrink:0">${trashIcon}</button>
        </div>
        <div class="slide-body">

          <div class="slide-img-col">
            <div class="carousel-preview-label">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21,15 16,10 5,21"/></svg>
              Carrusel de imágenes
            </div>
            <div class="ind-carousel-admin" id="maqCar${ti}" data-idx="0">
              <div class="ica-nav">
                <button type="button" class="ica-arrow" onclick="maqCarGo(${ti},-1)" title="Imagen anterior" aria-label="Imagen anterior">‹</button>
                <div class="ica-nav-label">
                  <span><span class="ica-current">Imagen 1</span> de 2</span>
                  <div class="ica-dots">
                    <span class="ica-dot active" onclick="maqCarGoTo(${ti},0)"></span>
                    <span class="ica-dot" onclick="maqCarGoTo(${ti},1)"></span>
                  </div>
                </div>
                <button type="button" class="ica-arrow" onclick="maqCarGo(${ti},1)" title="Siguiente imagen" aria-label="Siguiente imagen">›</button>
              </div>
              <div class="ica-viewport">
                <div class="ica-track" id="maqCarTrack${ti}">
                  <div class="ica-slide">${imgSlot(ti, 0, tab.images?.[0] || '')}</div>
                  <div class="ica-slide">${imgSlot(ti, 1, tab.images?.[1] || '')}</div>
                </div>
              </div>
            </div>
          </div>

          <div class="slide-fields-col">
            <div class="field">
              <div class="field-top"><label>Nombre del tab</label></div>
              <input type="text" class="fi" value="${esc(tab.nombre||'')}" placeholder="Máquinas de Ángulo"
                oninput="currentData.tabs[${ti}].nombre=this.value;const el=document.getElementById('tabTitle${ti}');el.textContent=this.value||'Nuevo tab';el.className='slide-header-title'+(this.value?'':' empty');checkDirty()"
                onblur="onFieldBlur()">
              <p class="field-hint">Aparece como opción en el panel lateral de la página pública</p>
            </div>
            <div class="field">
              <div class="field-top"><label>Descripción</label></div>
              <textarea class="fi" rows="7" placeholder="Descripción detallada del tipo de máquina…"
                oninput="currentData.tabs[${ti}].desc=this.value;checkDirty()"
                onblur="onFieldBlur()">${escTxt(tab.desc||'')}</textarea>
              <p class="field-hint">Texto visible al seleccionar esta opción en la página pública</p>
            </div>
          </div>

        </div>`;
      c.appendChild(div);
      if (tab.images?.[0]) setPreview(ti, 0, tab.images[0]);
      if (tab.images?.[1]) setPreview(ti, 1, tab.images[1]);
    });

    updateBanner();
  }

  /* ─── acciones ───────────────────────────────────── */
  const MAX_TABS = 10;
  function addTab() {
    if (currentData.tabs.length >= MAX_TABS) { showToast(`Máximo ${MAX_TABS} pestañas permitidas`, 'error'); return; }
    currentData.tabs.push({ nombre: '', desc: '', images: ['', ''] });
    renderTabs();
    markDirty();
    const cards = document.querySelectorAll('.slide-card');
    if (cards.length) cards[cards.length - 1].scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  function removeTab(ti) {
    currentData.tabs.splice(ti, 1);
    renderTabs();
    markDirty();
  }

  function cancelPage() {
    const saved = CM.get(currentKey) || {};
    currentData = {
      titulo:    saved.titulo    || '',
      subtitulo: saved.subtitulo || '',
      tabs:      (saved.tabs || []).map(t => ({
        nombre: t.nombre || '',
        desc:   t.desc   || '',
        images: [ t.images?.[0] || '', t.images?.[1] || '' ]
      }))
    };
    document.getElementById('fieldTitulo').value    = currentData.titulo;
    document.getElementById('fieldSubtitulo').value = currentData.subtitulo;
    origJSON = JSON.stringify(currentData);
    renderTabs();
    clearDirty();
    showToast('Cambios descartados');
  }

  function viewPublic() {
    const page = PAGES.find(p => p.key === currentKey);
    if (page?.url) window.open(page.url + '?v=' + Date.now(), 'dematiq_public');
  }

  async function savePage() {
    currentData.titulo    = document.getElementById('fieldTitulo').value.trim();
    currentData.subtitulo = document.getElementById('fieldSubtitulo').value.trim();
    try {
      const res = await CM.set(currentKey, currentData);
      if (res && res.ok) {
        clearDirty();
        showToast('Cambios guardados correctamente');
        const btn = document.getElementById('mainSaveBtn');
        if (btn) { btn.classList.add('saved'); setTimeout(() => btn.classList.remove('saved'), 900); }
        viewPublic();
      }
      else showToast(res?.error || 'Error al guardar', 'error');
    } catch { showToast('Error de conexión', 'error'); }
  }

  /* ─── iniciar ────────────────────────────────────── */
  buildChips();
  loadPage('ensamble');
