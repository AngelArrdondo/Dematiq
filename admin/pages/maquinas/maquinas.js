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
  document.addEventListener('keydown', e => {
    if ((e.ctrlKey || e.metaKey) && e.key === 's') { e.preventDefault(); savePage(); return; }
    if (e.key === 'Escape') { modalSwitchCancel(); modalNavCancel(); }
  });
  window.addEventListener('beforeunload', function(e) {
    if (dirty) { e.preventDefault(); e.returnValue = '¿Salir sin guardar?'; return e.returnValue; }
  });

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

  /* ─── preview de imagen ──────────────────────────── */
  function setPreview(ti, slot, src) {
    const box = document.getElementById(`imgBox${ti}_${slot}`);
    if (!box) return;
    const img = box.querySelector('img');
    const ph  = box.querySelector('.img-placeholder');
    if (!src) {
      if (img) img.style.display = 'none';
      if (ph)  ph.style.display  = '';
      return;
    }
    if (img) {
      img.src = '../../../' + src;
      img.style.display = 'block';
      img.onerror = () => { img.style.display = 'none'; if (ph) ph.style.display = ''; };
      if (ph) ph.style.display = 'none';
    }
  }

  /* ─── upload imagen ──────────────────────────────── */
  const uploadIcon = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="11" height="11"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17,8 12,3 7,8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>`;
  const trashIcon  = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="14" height="14"><polyline points="3,6 5,6 21,6"/><path d="M19,6l-1,14a2,2,0,0,1-2,2H8a2,2,0,0,1-2-2L5,6"/><path d="M10,11v6"/><path d="M14,11v6"/></svg>`;

  async function uploadTabImage(ti, slot, input) {
    if (!input.files[0]) return;
    const file = input.files[0];
    if (file.size > 5 * 1024 * 1024) { showToast('Máximo 5 MB por imagen', 'error'); input.value = ''; return; }

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
            <div class="dual-preview">
              ${imgSlot(ti, 0, tab.images?.[0] || '')}
              ${imgSlot(ti, 1, tab.images?.[1] || '')}
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
  function addTab() {
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
      if (res && res.ok) { clearDirty(); showToast('Cambios guardados correctamente'); viewPublic(); }
      else showToast(res?.error || 'Error al guardar', 'error');
    } catch { showToast('Error de conexión', 'error'); }
  }

  /* ─── iniciar ────────────────────────────────────── */
  buildChips();
  loadPage('ensamble');
