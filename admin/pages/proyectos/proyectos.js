  AdminSidebar.init('proyectos', '../../', '../../../');

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
  const CATS = [
    { val: 'robotica',   label: 'Robótica' },
    { val: 'torque',     label: 'Torque' },
    { val: 'inspeccion', label: 'Inspección' },
    { val: 'fugas',      label: 'Prueba de fugas' },
    { val: 'marcado',    label: 'Marcado' },
    { val: 'limpieza',   label: 'Limpieza' },
    { val: 'ensamble',   label: 'Ensamble' },
    { val: 'otro',       label: 'Otro' },
  ];

  let projects = (CM.get('proyectos') || []).map(p => ({
    id:         p.id         || '',
    title:      p.title      || p.nombre || '',   /* backward compat */
    cat:        p.cat        || '',
    badge:      p.badge      || '',
    sector:     p.sector     || '',
    servicios:  p.servicios  || '',
    ubicacion:  p.ubicacion  || '',
    anio:       p.anio       || '',
    img:        p.img        || '',
    alt:        p.alt        || '',
    desc:       p.desc       || '',
    destacado:  !!p.destacado,
    href:       p.href       || '',
  }));
  let origJSON = JSON.stringify(projects);
  let dirty    = false;

  /* ─── dirty ──────────────────────────────────────── */
  function markDirty() {
    dirty = true;
    document.getElementById('unsavedNotice').classList.remove('hidden');
    updateBanner();
  }
  function clearDirty() {
    dirty    = false;
    origJSON = JSON.stringify(projects);
    document.getElementById('unsavedNotice').classList.add('hidden');
    hideBlurPrompt();
    updateBanner();
  }
  function checkDirty() {
    JSON.stringify(projects) !== origJSON ? markDirty() : clearDirty();
  }

  /* ─── banner ─────────────────────────────────────── */
  function updateBanner() {
    document.getElementById('statTotal').textContent = projects.length || '0';
    document.getElementById('statDest').textContent  = projects.filter(p => p.destacado).length || '0';
    document.getElementById('statImgs').textContent  = projects.filter(p => p.img).length || '0';
  }

  /* ─── blur prompt ────────────────────────────────── */
  let blurTimer = null, bpAutoTimer = null;
  function onFieldBlur() {
    if (!dirty) return;
    clearTimeout(blurTimer);
    blurTimer = setTimeout(() => { if (dirty) showBlurPrompt(); }, 1400);
  }
  document.addEventListener('focusin', e => {
    if (e.target.matches('input,textarea,select')) clearTimeout(blurTimer);
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
  async function promptSave() { hideBlurPrompt(); await saveProjects(); }

  /* ─── nav modal ──────────────────────────────────── */
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
  async function modalSaveAndGo() { await saveProjects(); if (pendingNavUrl) window.location.href = pendingNavUrl; }
  function modalDiscardAndGo()    { dirty = false; if (pendingNavUrl) window.location.href = pendingNavUrl; }
  function modalCancel()          { pendingNavUrl = null; document.getElementById('navModal').classList.remove('show'); }
  document.addEventListener('keydown', e => {
    if ((e.ctrlKey || e.metaKey) && e.key === 's') { e.preventDefault(); saveProjects(); return; }
    if (e.key === 'Escape') modalCancel();
  });
  window.addEventListener('beforeunload', function(e) {
    if (dirty) { e.preventDefault(); e.returnValue = '¿Salir sin guardar los cambios?'; return e.returnValue; }
  });

  /* ─── preview imagen ─────────────────────────────── */
  function setPreview(i, src) {
    const box = document.getElementById(`imgBox${i}`);
    if (!box) return;
    const img = box.querySelector('img');
    const ph  = box.querySelector('.img-placeholder');
    const bdg = box.querySelector('.img-badge-preview');
    if (!src) {
      if (img) img.style.display = 'none';
      if (ph)  ph.style.display = '';
      return;
    }
    if (img) {
      img.src = '../../../' + src;
      img.style.display = 'block';
      img.onerror = () => { img.style.display = 'none'; if (ph) ph.style.display = ''; };
      if (ph) ph.style.display = 'none';
    }
  }

  function updateBadgePreview(i, val) {
    const box = document.getElementById(`imgBox${i}`);
    if (!box) return;
    let bdg = box.querySelector('.img-badge-preview');
    if (!bdg) { bdg = document.createElement('span'); bdg.className = 'img-badge-preview'; box.appendChild(bdg); }
    bdg.textContent = val || '';
    bdg.style.display = val ? 'block' : 'none';
  }

  /* ─── upload ─────────────────────────────────────── */
  async function uploadImage(i, input) {
    if (!input.files[0]) return;
    const file = input.files[0];
    if (file.size > 5 * 1024 * 1024) { showToast('Máximo 5 MB por imagen', 'error'); input.value = ''; return; }

    const box = document.getElementById(`imgBox${i}`);
    const img = box?.querySelector('img');
    const ph  = box?.querySelector('.img-placeholder');
    if (img) { img.src = URL.createObjectURL(file); img.style.display = 'block'; }
    if (ph)  ph.style.display = 'none';

    const btn = document.getElementById(`imgBtn${i}`);
    if (btn) { btn.disabled = true; btn.textContent = 'Subiendo…'; }

    const fd = new FormData();
    fd.append('image', file);
    fd.append('folder', 'general');
    try {
      const res  = await fetch('../../api/contenido.php', { method: 'POST', headers: { 'X-CSRF-Token': CSRF_TOKEN }, body: fd });
      const json = await res.json();
      if (json.ok) {
        projects[i].img = json.path;
        const pathEl = document.getElementById(`imgPath${i}`);
        if (pathEl) pathEl.value = json.path;
        setPreview(i, json.path);
        markDirty();
        showToast('Imagen subida correctamente');
      } else {
        showToast(json.error || 'Error al subir', 'error');
        setPreview(i, projects[i].img || '');
      }
    } catch {
      showToast('Error de conexión', 'error');
      setPreview(i, projects[i].img || '');
    } finally {
      if (btn) { btn.disabled = false; btn.innerHTML = uploadIcon + ' Subir imagen'; }
      input.value = '';
    }
  }

  /* ─── render ─────────────────────────────────────── */
  const uploadIcon = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="13" height="13"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17,8 12,3 7,8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>`;
  const trashIcon  = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="14" height="14"><polyline points="3,6 5,6 21,6"/><path d="M19,6l-1,14a2,2,0,0,1-2,2H8a2,2,0,0,1-2-2L5,6"/><path d="M10,11v6"/><path d="M14,11v6"/></svg>`;
  const starIcon   = `<svg viewBox="0 0 24 24" fill="currentColor" width="9" height="9"><polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26 12,2"/></svg>`;

  function esc(s)    { return String(s || '').replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
  function escTxt(s) { return String(s || '').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

  function catOptions(selected) {
    return CATS.map(c => `<option value="${c.val}"${c.val === selected ? ' selected' : ''}>${c.label}</option>`).join('');
  }

  function renderProjects() {
    const c = document.getElementById('projects-container');
    c.innerHTML = '';

    if (!projects.length) {
      c.innerHTML = `<div class="proy-empty">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
        <p>No hay proyectos configurados.</p>
        <strong>Usa el botón <em>Agregar proyecto</em> para añadir uno.</strong>
      </div>`;
      updateBanner();
      return;
    }

    projects.forEach((p, i) => {
      const div = document.createElement('div');
      div.className = 'slide-card';
      div.innerHTML = `
        <div class="slide-card-header">
          <div class="slide-badge">
            <span class="slide-num">${i + 1}</span>
            <span class="slide-header-title${p.title ? '' : ' empty'}" id="projTitle${i}">${esc(p.title) || 'Nuevo proyecto'}</span>
            ${p.destacado ? `<span class="slide-dest-badge">${starIcon} Destacado</span>` : ''}
          </div>
          <button class="btn-rm" onclick="removeProject(${i})" title="Eliminar proyecto" style="flex-shrink:0">${trashIcon}</button>
        </div>
        <div class="slide-body">

          <!-- ── IMAGEN ───────────────────────────────── -->
          <div class="slide-img-col">
            <div class="img-preview-box" id="imgBox${i}"
              onclick="document.getElementById('imgFile${i}').click()" title="Clic para cambiar imagen">
              <img src="" alt="" style="display:none">
              <div class="img-placeholder">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21,15 16,10 5,21"/></svg>
                <span>Sin imagen</span>
              </div>
              <div class="img-hover-hint">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17,8 12,3 7,8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                Cambiar imagen
              </div>
              ${p.badge ? `<span class="img-badge-preview">${esc(p.badge)}</span>` : '<span class="img-badge-preview" style="display:none"></span>'}
            </div>
            <button class="img-upload-btn" id="imgBtn${i}" onclick="document.getElementById('imgFile${i}').click()">
              ${uploadIcon} Subir imagen
            </button>
            <input type="file" id="imgFile${i}" accept="image/*" style="display:none" onchange="uploadImage(${i},this)">
            <input type="text" class="img-path-field" id="imgPath${i}" value="${esc(p.img||'')}"
              placeholder="assets/images/general/foto.webp"
              oninput="projects[${i}].img=this.value;setPreview(${i},this.value);checkDirty()"
              onblur="onFieldBlur()">
          </div>

          <!-- ── CAMPOS ────────────────────────────────── -->
          <div class="slide-fields-col">

            <!-- Destacado toggle -->
            <div class="dest-toggle-wrap">
              <div class="dest-toggle-info">
                <strong>Mostrar en inicio</strong>
                <span>Aparece en el carrusel de la página principal</span>
              </div>
              <label class="toggle-switch">
                <input type="checkbox" id="destChk${i}" ${p.destacado ? 'checked' : ''}
                  onchange="projects[${i}].destacado=this.checked;updateHeaderBadge(${i});checkDirty()">
                <span class="toggle-track"></span>
              </label>
            </div>

            <div class="field">
              <div class="field-top"><label>Título del proyecto</label></div>
              <input type="text" class="fi" value="${esc(p.title||'')}" placeholder="Línea de ensamble automotriz"
                oninput="projects[${i}].title=this.value;const el=document.getElementById('projTitle${i}');el.textContent=this.value||'Nuevo proyecto';el.className='slide-header-title'+(this.value?'':' empty');checkDirty()"
                onblur="onFieldBlur()">
            </div>

            <div class="field-row">
              <div class="field">
                <div class="field-top"><label>Categoría</label></div>
                <select class="fi" id="catSel${i}"
                  onchange="projects[${i}].cat=this.value;checkDirty()" onblur="onFieldBlur()">
                  <option value="">— Seleccionar —</option>
                  ${catOptions(p.cat)}
                </select>
              </div>
              <div class="field">
                <div class="field-top"><label>Badge / etiqueta</label></div>
                <input type="text" class="fi" value="${esc(p.badge||'')}" placeholder="Robótica"
                  oninput="projects[${i}].badge=this.value;updateBadgePreview(${i},this.value);checkDirty()"
                  onblur="onFieldBlur()">
                <p class="field-hint">Aparece sobre la imagen</p>
              </div>
            </div>

            <div class="field-row">
              <div class="field">
                <div class="field-top"><label>Sector industrial</label></div>
                <input type="text" class="fi" value="${esc(p.sector||'')}" placeholder="Automotriz"
                  oninput="projects[${i}].sector=this.value;checkDirty()" onblur="onFieldBlur()">
              </div>
              <div class="field-row" style="grid-template-columns:1fr 1fr;gap:10px;">
                <div class="field">
                  <div class="field-top"><label>Año</label></div>
                  <input type="text" class="fi" value="${esc(p.anio||'')}" placeholder="2024"
                    oninput="projects[${i}].anio=this.value;checkDirty()" onblur="onFieldBlur()">
                </div>
                <div class="field">
                  <div class="field-top"><label>Ubicación</label></div>
                  <input type="text" class="fi" value="${esc(p.ubicacion||'')}" placeholder="Monterrey, NL"
                    oninput="projects[${i}].ubicacion=this.value;checkDirty()" onblur="onFieldBlur()">
                </div>
              </div>
            </div>

            <div class="field">
              <div class="field-top"><label>Descripción breve</label></div>
              <textarea class="fi" rows="3" placeholder="Descripción corta que aparece en las tarjetas del listado…"
                oninput="projects[${i}].desc=this.value;checkDirty()"
                onblur="onFieldBlur()">${escTxt(p.desc||'')}</textarea>
              <p class="field-hint">Se muestra en las tarjetas del grid de proyectos</p>
            </div>

            <div class="field-row">
              <div class="field">
                <div class="field-top"><label>ID único</label></div>
                <input type="text" class="fi" value="${esc(p.id||'')}" placeholder="linea-ensamble-01"
                  style="font-family:monospace;font-size:.82rem"
                  oninput="projects[${i}].id=this.value;checkDirty()" onblur="onFieldBlur()">
                <p class="field-hint">Sin espacios — usado en la URL del proyecto</p>
              </div>
              <div class="field">
                <div class="field-top"><label>Servicios</label></div>
                <input type="text" class="fi" value="${esc(p.servicios||'')}" placeholder="PLC · HMI · Visión"
                  oninput="projects[${i}].servicios=this.value;checkDirty()" onblur="onFieldBlur()">
              </div>
            </div>

          </div>
        </div>`;
      c.appendChild(div);
      if (p.img) setPreview(i, p.img);
    });

    updateBanner();
  }

  function updateHeaderBadge(i) {
    const title = document.getElementById(`projTitle${i}`)?.closest('.slide-card-header');
    if (!title) return;
    let badge = title.querySelector('.slide-dest-badge');
    if (projects[i].destacado) {
      if (!badge) {
        badge = document.createElement('span');
        badge.className = 'slide-dest-badge';
        badge.innerHTML = starIcon + ' Destacado';
        title.querySelector('.slide-badge').appendChild(badge);
      }
    } else {
      badge?.remove();
    }
  }

  function addProject() {
    projects.push({ id: '', title: '', cat: '', badge: '', sector: '', servicios: '', ubicacion: '', anio: '', img: '', alt: '', desc: '', destacado: false, href: '' });
    renderProjects();
    markDirty();
    const cards = document.querySelectorAll('.slide-card');
    if (cards.length) cards[cards.length - 1].scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  function removeProject(i) {
    projects.splice(i, 1);
    renderProjects();
    checkDirty();
  }

  function cancelProjects() {
    projects = JSON.parse(origJSON);
    renderProjects();
    clearDirty();
    showToast('Cambios descartados');
  }

  function viewPublic(url) { window.open(url + '?v=' + Date.now(), 'dematiq_public'); }

  async function saveProjects() {
    try {
      const res = await CM.set('proyectos', projects);
      if (res && res.ok) { clearDirty(); showToast('Cambios guardados correctamente'); viewPublic('/pages/corporativo/proyecto.html'); }
      else showToast(res?.error || 'Error al guardar', 'error');
    } catch { showToast('Error de conexión', 'error'); }
  }

  renderProjects();
