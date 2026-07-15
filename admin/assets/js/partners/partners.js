  AdminSidebar.init('partners', '../../', '../../../');

  /* ── User menu dropdown ─────────────────────────── */
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

  let partners = (CM.get('partners') || []).map(p => Object.assign({}, p));
  let origJSON = JSON.stringify(partners);
  let dirty    = false;

  const pickIcon  = `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17,8 12,3 7,8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>`;
  const trashIcon = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3,6 5,6 21,6"/><path d="M19,6l-1,14a2,2,0,0,1-2,2H8a2,2,0,0,1-2-2L5,6"/><path d="M10,11v6"/><path d="M14,11v6"/></svg>`;

  function esc(str) { return String(str || '').replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

  /* ─── dirty state ─────────────────────────────────── */
  function markDirty() {
    dirty = true;
    document.getElementById('unsavedNotice').classList.remove('hidden');
  }
  function clearDirty() {
    dirty    = false;
    origJSON = JSON.stringify(partners);
    document.getElementById('unsavedNotice').classList.add('hidden');
  }
  function checkDirty() {
    JSON.stringify(partners) !== origJSON ? markDirty() : clearDirty();
  }
  window.addEventListener('beforeunload', function(e) {
    if (dirty) { e.preventDefault(); e.returnValue = '¿Salir sin guardar los cambios?'; return e.returnValue; }
  });

  function updateBanner() {
    const t = document.getElementById('statTotal');
    const l = document.getElementById('statLogos');
    if (t) t.textContent = partners.length || '0';
    if (l) l.textContent = partners.filter(p => p.logo).length || '0';
  }

  /* El zoom/lightbox de este logo lo maneja el sistema global de
     admin/assets/js/auth.js (delegado sobre .slide-img-preview) — no hace
     falta ninguno propio aquí. */
  function setPreview(i, src) {
    const prev = document.getElementById('imgPreview' + i);
    if (!prev) return;
    const clr = document.getElementById('imgClear' + i);
    if (clr) clr.style.display = src ? 'flex' : 'none';
    if (!src) {
      prev.innerHTML = `<div class="img-placeholder"><svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21,15 16,10 5,21"/></svg><span>Sin logo</span></div>`;
      return;
    }
    const img = document.createElement('img');
    img.src = src;
    img.alt = (partners[i] && partners[i].nombre) || 'Logo';
    img.onerror = () => { prev.innerHTML = `<div class="img-placeholder"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="3"/><line x1="3" y1="3" x2="21" y2="21"/></svg><span>No se pudo cargar</span></div>`; if (clr) clr.style.display = 'none'; };
    prev.innerHTML = '';
    prev.appendChild(img);
  }

  /* ── quitar logo (deja el campo vacío, no borra el archivo del servidor) ── */
  function clearPartnerImage(i) {
    partners[i].logo = '';
    const pathEl = document.getElementById('imgPath' + i);
    if (pathEl) pathEl.value = '';
    setPreview(i, '');
    markDirty();
  }

  /* El marquee de "Empresas Asociadas" (Nosotros) tiene fondo BLANCO y pinta
     el logo en escala de grises (filter:grayscale, ver home.css .marquee-item
     img). Un logo con fondo transparente o blanco se funde con el carrusel;
     un fondo de color sólido se vería como un bloque de color. */
  function probeImageBackground(file) {
    return new Promise(resolve => {
      if (file.type === 'image/svg+xml') { resolve('ok'); return; } /* SVG: se asume vectorial/transparente */
      const img = new Image();
      img.onload = () => {
        try {
          const size = 32;
          const canvas = document.createElement('canvas');
          canvas.width = size; canvas.height = size;
          const ctx = canvas.getContext('2d');
          ctx.drawImage(img, 0, 0, size, size);
          const data = ctx.getImageData(0, 0, size, size).data;
          // Revisa las 4 esquinas: si alguna es transparente o blanca, el fondo funde bien con el carrusel.
          const corners = [[0, 0], [size - 1, 0], [0, size - 1], [size - 1, size - 1]];
          let ok = false;
          for (const [x, y] of corners) {
            const p = (y * size + x) * 4;
            const [r, g, b, a] = [data[p], data[p + 1], data[p + 2], data[p + 3]];
            if (a < 250 || (r > 235 && g > 235 && b > 235)) { ok = true; break; }
          }
          resolve(ok ? 'ok' : 'solid-color');
        } catch { resolve('ok'); } /* no se pudo leer (CORS/canvas tainted) — no bloquear */
      };
      img.onerror = () => resolve('ok');
      img.src = URL.createObjectURL(file);
    });
  }

  async function uploadImage(i, input) {
    if (!input.files[0]) return;
    const file = input.files[0];
    if (file.size > 5 * 1024 * 1024) { showToast('Máximo 5 MB', 'error'); input.value = ''; return; }
    if (!file.type.startsWith('image/')) { showToast('Solo se permiten imágenes', 'error'); input.value = ''; return; }

    const bg = await probeImageBackground(file);
    if (bg === 'solid-color') {
      showToast('Logo no subido: tiene un fondo de color sólido y se vería como un bloque de color en el carrusel (fondo blanco). Usa un PNG, WebP o SVG con fondo transparente o blanco.', 'error');
      input.value = ''; return;
    }

    const localURL = URL.createObjectURL(file);
    setPreview(i, localURL);
    const btn = document.getElementById('imgPickBtn' + i);
    btn.disabled = true; btn.textContent = 'Subiendo…';
    const fd = new FormData();
    fd.append('image', file);
    fd.append('folder', 'partners');
    fd.append('oldPath', partners[i].logo || '');
    try {
      const res  = await fetch('../../api/contenido.php', { method: 'POST', headers: { 'X-CSRF-Token': CSRF_TOKEN }, body: fd });
      const json = await res.json();
      if (json.ok) {
        partners[i].logo = json.path;
        const pi = document.getElementById('imgPath' + i);
        if (pi) pi.value = json.path;
        markDirty();
        showToast('Logo subido correctamente');
      } else {
        showToast(json.error || 'Error al subir', 'error');
        setPreview(i, partners[i].logo ? '../../../' + partners[i].logo : '');
      }
    } catch { showToast('Error de conexión', 'error'); setPreview(i, partners[i].logo ? '../../../' + partners[i].logo : ''); }
    finally { btn.disabled = false; btn.innerHTML = pickIcon + ' Seleccionar logo'; input.value = ''; }
  }

  const MAX_PARTNERS = 12;
  function renderPartners() {
    const c = document.getElementById('partners-container');
    const badge = document.getElementById('partner-count');
    if (badge) badge.textContent = partners.length;
    const addBtn = document.getElementById('addPartnerBtn');
    if (addBtn) addBtn.disabled = partners.length >= MAX_PARTNERS;
    c.innerHTML = '';
    if (partners.length === 0) {
      c.innerHTML = '<p style="text-align:center;padding:28px;color:var(--text-lt);font-size:.88rem">No hay socios. Haz clic en <strong>Agregar socio</strong> para añadir uno.</p>';
      updateBanner();
      return;
    }
    partners.forEach((p, i) => {
      const div = document.createElement('div');
      div.className = 'slide-card';
      div.innerHTML = `
        <div class="slide-card-header">
          <div class="slide-badge">
            <span class="slide-num">${i + 1}</span>
            <span class="slide-header-title${p.nombre ? '' : ' empty'}" id="itemTitle${i}">${esc(p.nombre) || 'Nuevo socio'}</span>
          </div>
          <button class="btn-rm" onclick="removePartner(${i})" title="Eliminar" style="flex-shrink:0">${trashIcon}</button>
        </div>
        <div class="slide-body">
          <div class="slide-img-col">
            <div style="position:relative">
              <div class="slide-img-preview" id="imgPreview${i}"></div>
              <span class="spec-badge spec-badge-corner spec-badge-r" tabindex="0" data-tip="Logo en PNG/WebP/SVG con fondo transparente o blanco, cualquier proporción funciona porque no se recorta (contain) — solo se ajusta dentro del recuadro. El carrusel tiene fondo blanco; un fondo de color sólido se verá como un recuadro de color detrás del logo.">i</span>
              <button type="button" class="img-clear-btn" style="display:none" id="imgClear${i}" title="Quitar logo"
                onclick="event.stopPropagation(); clearPartnerImage(${i})">${trashIcon}</button>
            </div>
            <button type="button" class="btn-admin btn-outline-admin" id="imgPickBtn${i}"
              onclick="document.getElementById('imgFile${i}').click()"
              style="width:100%;justify-content:center;gap:6px;font-size:.8rem;padding:7px 10px">
              ${pickIcon} Seleccionar logo
            </button>
            <input type="file" id="imgFile${i}" accept="image/*" style="display:none" onchange="uploadImage(${i},this)">
            <input type="text" class="slide-path-input" id="imgPath${i}" value="${esc(p.logo)}"
              oninput="partners[${i}].logo=this.value;setPreview(${i},this.value?'../../../'+this.value:'');checkDirty()"
              placeholder="assets/images/partners/logo.webp">
          </div>
          <div class="slide-fields-col">
            <div class="form-group">
              <label>Nombre</label>
              <input type="text" value="${esc(p.nombre)}"
                oninput="partners[${i}].nombre=this.value;const el=document.getElementById('itemTitle${i}');el.textContent=this.value||'Nuevo socio';el.className='slide-header-title'+(this.value?'':' empty');checkDirty()">
            </div>
            <div class="form-group">
              <label>URL del sitio</label>
              <input type="url" value="${esc(p.url)}" oninput="partners[${i}].url=this.value;checkDirty()" placeholder="https://...">
            </div>
          </div>
        </div>`;
      c.appendChild(div);
      setPreview(i, p.logo ? '../../../' + p.logo : '');
    });
    updateBanner();
  }

  function addPartner()    {
    if (partners.length >= MAX_PARTNERS) { showToast(`Máximo ${MAX_PARTNERS} socios permitidos`, 'error'); return; }
    partners.push({ nombre: '', logo: '', url: '' }); renderPartners(); markDirty();
  }
  function removePartner(i){ partners.splice(i, 1); renderPartners(); checkDirty(); }
  function cancelPartners() {
    partners = JSON.parse(origJSON);
    renderPartners();
    clearDirty();
    showToast('Cambios descartados');
  }
  async function savePartners() {
    try {
      const res = await CM.set('partners', partners);
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

  renderPartners();
