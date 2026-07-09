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
    if (!src) {
      prev.innerHTML = `<div class="img-placeholder"><svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21,15 16,10 5,21"/></svg><span>Sin logo</span></div>`;
      return;
    }
    const img = document.createElement('img');
    img.src = src;
    img.alt = (partners[i] && partners[i].nombre) || 'Logo';
    img.onerror = () => { prev.innerHTML = `<div class="img-placeholder"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="3"/><line x1="3" y1="3" x2="21" y2="21"/></svg><span>No se pudo cargar</span></div>`; };
    prev.innerHTML = '';
    prev.appendChild(img);
  }

  /* Los logos se muestran con object-fit:contain sobre fondo blanco/oscuro —
     lo que arruina la vista es un logo con fondo sólido (recuadro visible),
     no una proporción específica. Por eso validamos transparencia, no medidas. */
  function probeImageTransparency(file) {
    return new Promise(resolve => {
      const img = new Image();
      img.onload = () => {
        try {
          const canvas = document.createElement('canvas');
          canvas.width = img.naturalWidth; canvas.height = img.naturalHeight;
          const ctx = canvas.getContext('2d');
          ctx.drawImage(img, 0, 0);
          const data = ctx.getImageData(0, 0, canvas.width, canvas.height).data;
          for (let a = 3; a < data.length; a += 4) if (data[a] < 250) { resolve(true); return; }
          resolve(false);
        } catch { resolve(true); } /* no se pudo leer (CORS/canvas tainted) — no bloquear */
      };
      img.onerror = () => resolve(true);
      img.src = URL.createObjectURL(file);
    });
  }

  async function uploadImage(i, input) {
    if (!input.files[0]) return;
    const file = input.files[0];
    if (file.size > 5 * 1024 * 1024) { showToast('Máximo 5 MB', 'error'); input.value = ''; return; }
    if (!file.type.startsWith('image/')) { showToast('Solo se permiten imágenes', 'error'); input.value = ''; return; }

    if (file.type !== 'image/svg+xml') {
      const hasTransparency = await probeImageTransparency(file);
      if (!hasTransparency) {
        showToast('Logo no subido: la imagen no tiene fondo transparente — usa PNG/WebP con transparencia para que no se vea como un bloque sólido', 'error');
        input.value = ''; return;
      }
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
            <div class="slide-img-preview" id="imgPreview${i}"></div>
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
        showToast('Cambios guardados correctamente — recargando…');
        const btn = document.getElementById('mainSaveBtn');
        if (btn) { btn.classList.add('saved'); setTimeout(() => btn.classList.remove('saved'), 900); }
        setTimeout(() => location.reload(), 1100);
      } else {
        showToast(res?.error || 'Error al guardar', 'error');
      }
    } catch {
      showToast('Error de conexión', 'error');
    }
  }

  renderPartners();
