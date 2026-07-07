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

  const pickIcon  = `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17,8 12,3 7,8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>`;
  const trashIcon = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3,6 5,6 21,6"/><path d="M19,6l-1,14a2,2,0,0,1-2,2H8a2,2,0,0,1-2-2L5,6"/><path d="M10,11v6"/><path d="M14,11v6"/></svg>`;

  function esc(str) { return String(str || '').replace(/"/g, '&quot;'); }

  function setPreview(i, src) {
    const prev = document.getElementById('imgPreview' + i);
    if (!prev) return;
    if (!src) {
      prev.innerHTML = `<div class="img-placeholder"><svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21,15 16,10 5,21"/></svg><span>Sin logo</span></div>`;
      return;
    }
    const img = document.createElement('img');
    img.src = src;
    img.onerror = () => { prev.innerHTML = `<div class="img-placeholder"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="3"/><line x1="3" y1="3" x2="21" y2="21"/></svg><span>No se pudo cargar</span></div>`; };
    prev.innerHTML = '';
    prev.appendChild(img);
  }

  async function uploadImage(i, input) {
    if (!input.files[0]) return;
    const file = input.files[0];
    if (file.size > 5 * 1024 * 1024) { showToast('Máximo 5 MB', 'error'); input.value = ''; return; }
    const localURL = URL.createObjectURL(file);
    setPreview(i, localURL);
    const btn = document.getElementById('imgPickBtn' + i);
    btn.disabled = true; btn.textContent = 'Subiendo…';
    const fd = new FormData();
    fd.append('image', file);
    fd.append('folder', 'partners');
    try {
      const res  = await fetch('../../api/contenido.php', { method: 'POST', headers: { 'X-CSRF-Token': CSRF_TOKEN }, body: fd });
      const json = await res.json();
      if (json.ok) {
        partners[i].logo = json.path;
        const pi = document.getElementById('imgPath' + i);
        if (pi) pi.value = json.path;
        showToast('Logo subido correctamente');
      } else {
        showToast(json.error || 'Error al subir', 'error');
        setPreview(i, partners[i].logo ? '../../../' + partners[i].logo : '');
      }
    } catch { showToast('Error de conexión', 'error'); setPreview(i, partners[i].logo ? '../../../' + partners[i].logo : ''); }
    finally { btn.disabled = false; btn.innerHTML = pickIcon + ' Seleccionar logo'; input.value = ''; }
  }

  function renderPartners() {
    const c = document.getElementById('partners-container');
    const badge = document.getElementById('partner-count');
    if (badge) badge.textContent = partners.length;
    c.innerHTML = '';
    if (partners.length === 0) {
      c.innerHTML = '<p style="text-align:center;padding:28px;color:var(--text-lt);font-size:.88rem">No hay socios. Haz clic en <strong>Agregar socio</strong> para añadir uno.</p>';
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
              oninput="partners[${i}].logo=this.value;setPreview(${i},this.value?'../../../'+this.value:'')"
              placeholder="assets/images/partners/logo.webp">
          </div>
          <div class="slide-fields-col">
            <div class="form-group">
              <label>Nombre</label>
              <input type="text" value="${esc(p.nombre)}"
                oninput="partners[${i}].nombre=this.value;const el=document.getElementById('itemTitle${i}');el.textContent=this.value||'Nuevo socio';el.className='slide-header-title'+(this.value?'':' empty')">
            </div>
            <div class="form-group">
              <label>URL del sitio</label>
              <input type="url" value="${esc(p.url)}" oninput="partners[${i}].url=this.value" placeholder="https://...">
            </div>
          </div>
        </div>`;
      c.appendChild(div);
      setPreview(i, p.logo ? '../../../' + p.logo : '');
    });
  }

  function addPartner()    { partners.push({ nombre: '', logo: '', url: '' }); renderPartners(); }
  function removePartner(i){ partners.splice(i, 1); renderPartners(); }
  async function savePartners() {
    try {
      const res = await CM.set('partners', partners);
      if (res && res.ok) {
        showToast('Cambios guardados correctamente');
      } else {
        showToast(res?.error || 'Error al guardar', 'error');
      }
    } catch {
      showToast('Error de conexión', 'error');
    }
  }

  renderPartners();
