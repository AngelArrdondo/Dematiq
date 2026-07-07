  AdminSidebar.init('industrias', '../../', '../../../');

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

  /* ─── datos ──────────────────────────────────────── */
  let industrias = (CM.get('industrias') || []).map(ind => ({
    id:          ind.id          || '',
    nombre:      ind.nombre      || '',
    descripcion: ind.descripcion || '',
    imagen1:     ind.imagen1 || ind.imagen || '',  /* backward compat con campo único */
    imagen2:     ind.imagen2     || '',
  }));
  let origJSON = JSON.stringify(industrias);
  let dirty    = false;

  /* ─── dirty state ────────────────────────────────── */
  function markDirty() {
    dirty = true;
    document.getElementById('unsavedNotice').classList.remove('hidden');
    updateBanner();
  }
  function clearDirty() {
    dirty    = false;
    origJSON = JSON.stringify(industrias);
    document.getElementById('unsavedNotice').classList.add('hidden');
    hideBlurPrompt();
    updateBanner();
  }
  function checkDirty() {
    JSON.stringify(industrias) !== origJSON ? markDirty() : clearDirty();
  }

  /* ─── banner stats ───────────────────────────────── */
  function updateBanner() {
    document.getElementById('statTotal').textContent = industrias.length || '0';
    document.getElementById('statImgs').textContent  = industrias.filter(ind => ind.imagen1 || ind.imagen2).length || '0';
  }

  /* ─── blur prompt ────────────────────────────────── */
  let blurTimer = null, bpAutoTimer = null;

  function onFieldBlur() {
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
  async function promptSave() { hideBlurPrompt(); await saveIndustrias(); }

  /* ─── nav-away modal ─────────────────────────────── */
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

  async function modalSaveAndGo() { await saveIndustrias(); if (pendingNavUrl) window.location.href = pendingNavUrl; }
  function modalDiscardAndGo()    { dirty = false; if (pendingNavUrl) window.location.href = pendingNavUrl; }
  function modalCancel()          { pendingNavUrl = null; document.getElementById('navModal').classList.remove('show'); }
  document.addEventListener('keydown', e => {
    if ((e.ctrlKey || e.metaKey) && e.key === 's') { e.preventDefault(); saveIndustrias(); return; }
    if (e.key === 'Escape') modalCancel();
  });
  window.addEventListener('beforeunload', function(e) {
    if (dirty) { e.preventDefault(); e.returnValue = '¿Salir sin guardar los cambios?'; return e.returnValue; }
  });

  /* ─── preview imagen ─────────────────────────────── */
  function setPreview(i, slot, src) {
    const box = document.getElementById(`imgPreview${i}_${slot}`);
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
  async function uploadImage(i, slot, input) {
    if (!input.files[0]) return;
    const file = input.files[0];
    if (file.size > 5 * 1024 * 1024) { showToast('Máximo 5 MB por imagen', 'error'); input.value = ''; return; }

    /* preview local inmediato */
    const box = document.getElementById(`imgPreview${i}_${slot}`);
    const img = box?.querySelector('img');
    const ph  = box?.querySelector('.img-placeholder');
    if (img) { img.src = URL.createObjectURL(file); img.style.display = 'block'; }
    if (ph)  ph.style.display = 'none';

    const btn = document.getElementById(`imgBtn${i}_${slot}`);
    if (btn) { btn.disabled = true; btn.textContent = 'Subiendo…'; }

    const fd = new FormData();
    fd.append('image', file);
    fd.append('folder', 'general');
    try {
      const res  = await fetch('../../api/contenido.php', { method: 'POST', headers: { 'X-CSRF-Token': CSRF_TOKEN }, body: fd });
      const json = await res.json();
      if (json.ok) {
        industrias[i][`imagen${slot}`] = json.path;
        const pathEl = document.getElementById(`imgPath${i}_${slot}`);
        if (pathEl) pathEl.value = json.path;
        setPreview(i, slot, json.path);
        markDirty();
        showToast('Imagen subida correctamente');
      } else {
        showToast(json.error || 'Error al subir', 'error');
        setPreview(i, slot, industrias[i][`imagen${slot}`] || '');
      }
    } catch {
      showToast('Error de conexión', 'error');
      setPreview(i, slot, industrias[i][`imagen${slot}`] || '');
    } finally {
      if (btn) {
        btn.disabled = false;
        btn.innerHTML = uploadIcon + ` Subir imagen ${slot}`;
      }
      input.value = '';
    }
  }

  /* ─── render ─────────────────────────────────────── */
  const uploadIcon = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="11" height="11"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17,8 12,3 7,8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>`;
  const trashIcon  = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="14" height="14"><polyline points="3,6 5,6 21,6"/><path d="M19,6l-1,14a2,2,0,0,1-2,2H8a2,2,0,0,1-2-2L5,6"/><path d="M10,11v6"/><path d="M14,11v6"/></svg>`;

  function esc(s)    { return String(s || '').replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
  function escTxt(s) { return String(s || '').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

  function imgSlot(i, slot, val) {
    return `
      <div class="img-slot">
        <div class="img-slot-label"><span class="img-slot-num">${slot}</span> Imagen ${slot}</div>
        <div class="img-preview-box" id="imgPreview${i}_${slot}"
          onclick="document.getElementById('imgFile${i}_${slot}').click()" title="Clic para cambiar imagen">
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
        <button class="img-upload-btn" id="imgBtn${i}_${slot}"
          onclick="document.getElementById('imgFile${i}_${slot}').click()">
          ${uploadIcon} Subir imagen ${slot}
        </button>
        <input type="file" id="imgFile${i}_${slot}" accept="image/*" style="display:none"
          onchange="uploadImage(${i},${slot},this)">
        <input type="text" class="img-path-field" id="imgPath${i}_${slot}" value="${esc(val||'')}"
          placeholder="assets/images/general/foto${slot}.webp"
          oninput="industrias[${i}].imagen${slot}=this.value;setPreview(${i},${slot},this.value);checkDirty()"
          onblur="onFieldBlur()">
      </div>`;
  }

  function renderIndustrias() {
    const c = document.getElementById('industrias-container');
    c.innerHTML = '';

    if (!industrias.length) {
      c.innerHTML = `<div class="ind-empty">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><polygon points="12,2 2,7 12,12 22,7"/><polyline points="2,17 12,22 22,17"/><polyline points="2,12 12,17 22,12"/></svg>
        <p>No hay sectores configurados.</p>
        <strong>Usa el botón <em>Agregar sector</em> para añadir uno.</strong>
      </div>`;
      updateBanner();
      return;
    }

    industrias.forEach((ind, i) => {
      const div = document.createElement('div');
      div.className = 'slide-card';
      div.innerHTML = `
        <div class="slide-card-header">
          <div class="slide-badge">
            <span class="slide-num">${i + 1}</span>
            <span class="slide-header-title${ind.nombre ? '' : ' empty'}" id="indTitle${i}">${esc(ind.nombre) || 'Nuevo sector'}</span>
          </div>
          <button class="btn-rm" onclick="removeIndustria(${i})" title="Eliminar sector" style="flex-shrink:0">${trashIcon}</button>
        </div>
        <div class="slide-body">

          <div class="slide-img-col">
            <div class="carousel-preview-label">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21,15 16,10 5,21"/></svg>
              Imágenes del carrusel
            </div>
            <div class="dual-preview">
              ${imgSlot(i, 1, ind.imagen1)}
              ${imgSlot(i, 2, ind.imagen2)}
            </div>
          </div>

          <div class="slide-fields-col">
            <div class="field">
              <div class="field-top"><label>Nombre del sector</label></div>
              <input type="text" class="fi" value="${esc(ind.nombre||'')}" placeholder="Automotriz"
                oninput="industrias[${i}].nombre=this.value;const el=document.getElementById('indTitle${i}');el.textContent=this.value||'Nuevo sector';el.className='slide-header-title'+(this.value?'':' empty');checkDirty()"
                onblur="onFieldBlur()">
            </div>
            <div class="field">
              <div class="field-top"><label>ID único</label></div>
              <input type="text" class="fi" value="${esc(ind.id||'')}" placeholder="automotriz"
                style="font-family:monospace;font-size:.82rem"
                oninput="industrias[${i}].id=this.value;checkDirty()"
                onblur="onFieldBlur()">
              <p class="field-hint">Sin espacios ni acentos — identifica el panel en la web pública</p>
            </div>
            <div class="field">
              <div class="field-top"><label>Descripción</label></div>
              <textarea class="fi" rows="6"
                oninput="industrias[${i}].descripcion=this.value;checkDirty()"
                onblur="onFieldBlur()">${escTxt(ind.descripcion||'')}</textarea>
            </div>
          </div>

        </div>`;
      c.appendChild(div);
      if (ind.imagen1) setPreview(i, 1, ind.imagen1);
      if (ind.imagen2) setPreview(i, 2, ind.imagen2);
    });

    updateBanner();
  }

  function addIndustria() {
    industrias.push({ id: '', nombre: '', descripcion: '', imagen1: '', imagen2: '' });
    renderIndustrias();
    markDirty();
    const cards = document.querySelectorAll('.slide-card');
    if (cards.length) cards[cards.length - 1].scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  function removeIndustria(i) {
    industrias.splice(i, 1);
    renderIndustrias();
    checkDirty();
  }

  function cancelIndustrias() {
    industrias = JSON.parse(origJSON);
    renderIndustrias();
    clearDirty();
    showToast('Cambios descartados');
  }

  function viewPublic(url) { window.open(url + '?v=' + Date.now(), 'dematiq_public'); }

  async function saveIndustrias() {
    try {
      const res = await CM.set('industrias', industrias);
      if (res && res.ok) { clearDirty(); showToast('Cambios guardados correctamente'); viewPublic('/pages/corporativo/industrias.html'); }
      else showToast(res?.error || 'Error al guardar', 'error');
    } catch { showToast('Error de conexión', 'error'); }
  }

  renderIndustrias();
