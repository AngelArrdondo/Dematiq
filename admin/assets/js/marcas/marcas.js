  AdminSidebar.init('marcas', '../../', '../../../');

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
  document.addEventListener('keydown', e => {
    if ((e.ctrlKey || e.metaKey) && e.key === 's') { e.preventDefault(); saveMarcas(); }
  });

  /* ── Data ─────────────────────────────────────── */
  let marcas = (CM.get('marcas') || []).map(m => Object.assign({}, m));
  let original = JSON.parse(JSON.stringify(marcas));
  let dirty = false;

  /* ── Dirty state ──────────────────────────────── */
  function markDirty() {
    dirty = true;
    document.getElementById('unsavedNotice').classList.remove('hidden');
  }
  function clearDirty() {
    dirty = false;
    original = JSON.parse(JSON.stringify(marcas));
    document.getElementById('unsavedNotice').classList.add('hidden');
  }
  function checkDirty() {
    JSON.stringify(marcas) !== JSON.stringify(original) ? markDirty() : clearDirty();
  }
  window.addEventListener('beforeunload', function(e) {
    if (dirty) { e.preventDefault(); e.returnValue = '¿Salir sin guardar los cambios?'; return e.returnValue; }
  });

  /* ── Lightbox (vista previa grande) ───────────── */
  function openLightboxImage(src, caption) {
    if (!src) { showToast('Todavía no hay logo para mostrar', 'error'); return; }
    document.getElementById('lightboxImg').src = src;
    document.getElementById('lightboxImg').style.display = 'block';
    document.getElementById('lightboxCaption').textContent = caption || '';
    document.getElementById('lightboxModal').classList.add('show');
  }
  function closeLightbox() {
    document.getElementById('lightboxModal').classList.remove('show');
  }
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape' && document.getElementById('lightboxModal').classList.contains('show')) closeLightbox();
  });

  /* ── Helpers ──────────────────────────────────── */
  const esc = str => String(str || '').replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;');

  const pickIcon  = `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17,8 12,3 7,8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>`;
  const trashIcon = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3,6 5,6 21,6"/><path d="M19,6l-1,14a2,2,0,0,1-2,2H8a2,2,0,0,1-2-2L5,6"/><path d="M10,11v6"/><path d="M14,11v6"/></svg>`;
  const upIcon    = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"/></svg>`;
  const downIcon  = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>`;

  /* ── Preview ──────────────────────────────────── */
  function updatePreview() {
    const track = document.getElementById('marqueePreview');
    const withLogo = marcas.filter(m => m.logo);
    if (!withLogo.length) {
      track.innerHTML = '<span style="color:rgba(255,255,255,.2);font-size:.75rem">Sin logos configurados</span>';
      return;
    }
    track.innerHTML = withLogo.slice(0, 12).map(m =>
      `<div class="marquee-preview-item"><img src="../../../${m.logo}" alt="${esc(m.nombre)}" onerror="this.parentNode.style.display='none'"></div>`
    ).join('');
  }

  /* ── Stats ────────────────────────────────────── */
  function updateStats() {
    document.getElementById('statTotal').textContent   = marcas.length;
    document.getElementById('statConLogo').textContent = marcas.filter(m => m.logo).length;
  }

  /* ── Image preview ────────────────────────────── */
  function setPreview(i, src) {
    const prev = document.getElementById('brandPreview' + i);
    if (!prev) return;
    const hint = prev.querySelector('.brand-img-hint');
    const zoom = prev.querySelector('.preview-zoom-btn');
    const clr  = document.getElementById('brandClear' + i);
    if (zoom) zoom.style.display = src ? 'flex' : 'none';
    if (clr)  clr.style.display  = src ? 'flex' : 'none';
    if (!src) {
      prev.innerHTML = `<div class="img-placeholder"><svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21,15 16,10 5,21"/></svg><span>Sin logo</span></div>`;
      if (hint) prev.appendChild(hint);
      if (zoom) prev.appendChild(zoom);
      return;
    }
    const img = document.createElement('img');
    img.src = src;
    img.onerror = () => {
      prev.innerHTML = `<div class="img-placeholder"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="3"/><line x1="3" y1="3" x2="21" y2="21"/></svg><span>Sin vista previa</span></div>`;
      if (hint) prev.appendChild(hint);
      if (zoom) { zoom.style.display = 'none'; prev.appendChild(zoom); }
    };
    prev.innerHTML = '';
    prev.appendChild(img);
    if (hint) prev.appendChild(hint);
    else {
      const h = document.createElement('div');
      h.className = 'brand-img-hint';
      h.innerHTML = `${pickIcon} Cambiar`;
      prev.appendChild(h);
    }
    if (zoom) prev.appendChild(zoom);
  }

  /* ── Validación de transparencia ──────────────── */
  /* El logo se pinta de blanco vía CSS (brightness(0) invert(1)) en el
     marquee del Inicio; sin transparencia se ve como un bloque blanco sólido. */
  function probeImageTransparency(file) {
    return new Promise((resolve) => {
      if (file.type === 'image/svg+xml') { resolve(true); return; } /* SVG: se asume vectorial/transparente */
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

  /* ── Upload ───────────────────────────────────── */
  let _uploadTarget = null;
  document.getElementById('marcaFileInput').onchange = async function() {
    if (_uploadTarget === null || !this.files[0]) return;
    const i = _uploadTarget;
    const file = this.files[0];
    this.value = '';
    if (file.size > 5 * 1024 * 1024) { showToast('Máximo 5 MB', 'error'); return; }
    if (!file.type.startsWith('image/')) { showToast('Solo se permiten imágenes (PNG, WebP, SVG)', 'error'); return; }

    const hasTransparency = await probeImageTransparency(file);
    if (!hasTransparency) {
      showToast('Logo rechazado — no tiene fondo transparente y se vería como un bloque blanco sólido en el carrusel. Usa un PNG, WebP o SVG con transparencia.', 'error');
      return;
    }

    const localURL = URL.createObjectURL(file);
    setPreview(i, localURL);

    const btn = document.getElementById('pickBtn' + i);
    if (btn) { btn.disabled = true; btn.textContent = 'Subiendo…'; }

    const prog = document.getElementById('prog' + i);
    const progBar = document.getElementById('progBar' + i);
    if (prog) prog.classList.add('visible');
    if (progBar) progBar.style.width = '0%';

    const fd = new FormData();
    fd.append('image', file);
    fd.append('folder', 'partners');
    fd.append('oldPath', marcas[i].logo || '');

    try {
      const xhr = new XMLHttpRequest();
      xhr.open('POST', '../../api/contenido.php');
      xhr.setRequestHeader('X-CSRF-Token', CSRF_TOKEN);
      xhr.upload.onprogress = e => {
        if (e.lengthComputable && progBar) progBar.style.width = Math.round(e.loaded / e.total * 100) + '%';
      };
      xhr.onload = () => {
        let json = {};
        try { json = JSON.parse(xhr.responseText); } catch {}
        if (json.ok) {
          marcas[i].logo = json.path;
          setPreview(i, '../../../' + json.path);
          const pi = document.getElementById('imgPath' + i);
          if (pi) pi.value = json.path;
          updatePreview(); updateStats(); markDirty();
          showToast('Logo subido correctamente');
        } else {
          showToast(json.error || 'Error al subir', 'error');
          setPreview(i, marcas[i].logo ? '../../../' + marcas[i].logo : '');
        }
        if (btn) { btn.disabled = false; btn.innerHTML = pickIcon + ' Seleccionar logo'; }
        if (prog) prog.classList.remove('visible');
        URL.revokeObjectURL(localURL);
      };
      xhr.onerror = () => {
        showToast('Error de conexión', 'error');
        if (btn) { btn.disabled = false; btn.innerHTML = pickIcon + ' Seleccionar logo'; }
        if (prog) prog.classList.remove('visible');
      };
      xhr.send(fd);
    } catch {
      showToast('Error de conexión', 'error');
      if (btn) { btn.disabled = false; btn.innerHTML = pickIcon + ' Seleccionar logo'; }
    }
  };

  function pickLogo(i) {
    _uploadTarget = i;
    document.getElementById('marcaFileInput').click();
  }

  /* ── quitar logo (deja el campo vacío, no borra el archivo del servidor) ── */
  function clearMarcaImage(i) {
    marcas[i].logo = '';
    const pathEl = document.getElementById('imgPath' + i);
    if (pathEl) pathEl.value = '';
    setPreview(i, '');
    updatePreview(); updateStats();
    markDirty();
  }

  /* ── Reorder ──────────────────────────────────── */
  function moveMarca(i, dir) {
    const j = i + dir;
    if (j < 0 || j >= marcas.length) return;
    [marcas[i], marcas[j]] = [marcas[j], marcas[i]];
    renderMarcas();
    checkDirty();
  }

  /* ── Render ───────────────────────────────────── */
  function renderMarcas() {
    const c = document.getElementById('marcasContainer');
    updateStats();
    updatePreview();
    const addBtn = document.getElementById('addMarcaBtn');
    if (addBtn) addBtn.disabled = marcas.length >= MAX_MARCAS;

    if (!marcas.length) {
      c.innerHTML = `<div class="brands-empty">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26 12,2"/></svg>
        <p>No hay marcas. Usa <strong>Agregar marca</strong> para añadir la primera.</p>
      </div>`;
      return;
    }

    c.innerHTML = '';
    marcas.forEach((m, i) => {
      const div = document.createElement('div');
      div.className = 'brand-card';
      div.innerHTML = `
        <div class="brand-card-header">
          <span class="brand-num">${i + 1}</span>
          <span class="brand-header-name${m.nombre ? '' : ' empty'}" id="headerName${i}">${esc(m.nombre) || 'Nueva marca'}</span>
          <div class="brand-header-actions">
            <button class="brand-move-btn" onclick="moveMarca(${i},-1)" title="Subir" ${i === 0 ? 'disabled' : ''}>${upIcon}</button>
            <button class="brand-move-btn" onclick="moveMarca(${i},1)"  title="Bajar" ${i === marcas.length - 1 ? 'disabled' : ''}>${downIcon}</button>
            <button class="btn-rm" onclick="removeMarca(${i})" title="Eliminar">${trashIcon}</button>
          </div>
        </div>
        <div class="brand-body">
          <div class="brand-img-col">
            <div style="position:relative">
              <div class="brand-img-preview" id="brandPreview${i}" onclick="pickLogo(${i})" title="Clic para cambiar logo">
                <div class="brand-img-hint">${pickIcon} Cambiar</div>
              </div>
              <span class="spec-badge spec-badge-corner spec-badge-r" tabindex="0" onclick="event.stopPropagation()" data-tip="Logo en PNG/WebP/SVG con fondo transparente, ideal con al menos 300px de alto para que no se vea pixelado. Se pinta de blanco automáticamente en el carrusel — si no tiene transparencia real se verá como un bloque blanco sólido feo. No se recorta (contain), así que cualquier proporción funciona.">i</span>
              <button type="button" class="img-clear-btn" style="display:none" id="brandClear${i}" title="Quitar logo"
                onclick="event.stopPropagation(); clearMarcaImage(${i})">${trashIcon}</button>
            </div>
            <button type="button" class="btn-admin btn-outline-admin" id="pickBtn${i}"
              onclick="pickLogo(${i})"
              style="width:100%;justify-content:center;gap:6px;font-size:.78rem;padding:7px 10px">
              ${pickIcon} Seleccionar logo
            </button>
            <div class="upload-mini-prog" id="prog${i}">
              <div class="upload-mini-prog-bar" id="progBar${i}" style="width:0%"></div>
            </div>
            <input type="text" class="brand-path-input" id="imgPath${i}" value="${esc(m.logo)}"
              placeholder="assets/images/partners/logo.svg"
              oninput="marcas[${i}].logo=this.value;setPreview(${i},this.value?'../../../'+this.value:'');updatePreview();updateStats();checkDirty()">
          </div>
          <div class="brand-fields-col">
            <div class="form-group">
              <label>Nombre de la marca</label>
              <input type="text" value="${esc(m.nombre)}" placeholder="Ej. Siemens"
                oninput="marcas[${i}].nombre=this.value;const el=document.getElementById('headerName${i}');el.textContent=this.value||'Nueva marca';el.className='brand-header-name'+(this.value?'':' empty');checkDirty()">
            </div>
            <div class="form-group">
              <label>URL del sitio web <span style="font-weight:400;color:var(--text-lt)">(opcional)</span></label>
              <input type="url" value="${esc(m.url || '')}" placeholder="https://www.siemens.com"
                oninput="marcas[${i}].url=this.value;checkDirty()">
              <p class="field-hint">Si se ingresa, el logo será clickeable en el carrusel.</p>
            </div>
          </div>
        </div>`;
      c.appendChild(div);
      setPreview(i, m.logo ? '../../../' + m.logo : '');
      const zoomBtn = document.createElement('button');
      zoomBtn.type = 'button';
      zoomBtn.className = 'preview-zoom-btn';
      zoomBtn.title = 'Ver en grande';
      zoomBtn.style.display = m.logo ? 'flex' : 'none';
      zoomBtn.innerHTML = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>`;
      zoomBtn.onclick = (e) => {
        e.stopPropagation();
        const img = document.getElementById('brandPreview' + i)?.querySelector('img');
        openLightboxImage(img ? img.src : '', m.nombre || `Marca ${i + 1}`);
      };
      document.getElementById('brandPreview' + i).appendChild(zoomBtn);
    });
  }

  /* ── CRUD ─────────────────────────────────────── */
  const MAX_MARCAS = 20;
  function addMarca() {
    if (marcas.length >= MAX_MARCAS) { showToast(`Máximo ${MAX_MARCAS} marcas permitidas`, 'error'); return; }
    marcas.push({ nombre:'', logo:'', url:'' });
    renderMarcas();
    markDirty();
    setTimeout(() => {
      const cards = document.querySelectorAll('.brand-card');
      if (cards.length) cards[cards.length - 1].scrollIntoView({ behavior:'smooth', block:'center' });
    }, 80);
  }

  function removeMarca(i) {
    marcas.splice(i, 1);
    renderMarcas();
    checkDirty();
  }

  function cancelMarcas() {
    marcas = JSON.parse(JSON.stringify(original));
    renderMarcas();
    clearDirty();
    showToast('Cambios descartados');
  }

  async function saveMarcas() {
    try {
      const res = await CM.set('marcas', marcas);
      if (res && res.ok) {
        clearDirty();
        showToast('Cambios guardados correctamente');
        const btn = document.getElementById('mainSaveBtn');
        if (btn) { btn.classList.add('saved'); setTimeout(() => btn.classList.remove('saved'), 900); }
      } else {
        showToast(res?.error || 'Error al guardar', 'error');
      }
    } catch {
      showToast('Error de conexión', 'error');
    }
  }

  /* ── Init ─────────────────────────────────────── */
  renderMarcas();
