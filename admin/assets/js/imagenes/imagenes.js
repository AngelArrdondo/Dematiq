  AdminSidebar.init('imagenes', '../../', '../../../');

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

  /* ─── estado ─────────────────────────────────────── */
  let imagesData    = {};
  let currentFolder = 'general';
  let pendingDelete = null; // { path }

  /* ─── helpers ────────────────────────────────────── */
  function fmtSize(bytes) {
    if (!bytes) return '—';
    if (bytes < 1024)    return bytes + ' B';
    if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / 1048576).toFixed(1) + ' MB';
  }

  function extOf(name) {
    return (name || '').split('.').pop().toLowerCase().slice(0, 4);
  }

  function escH(s) {
    return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  /* ─── banner stats ───────────────────────────────── */
  function updateStats() {
    const total = Object.values(imagesData).reduce((a, arr) => a + (arr?.length || 0), 0);
    const cur   = (imagesData[currentFolder] || []).length;
    document.getElementById('statTotal').textContent  = total;
    document.getElementById('statFolder').textContent = cur + ' imagen' + (cur !== 1 ? 'es' : '');
    ['general','partners','avatares'].forEach(k => {
      const cnt = (imagesData[k] || []).length;
      const el  = document.getElementById('cnt-' + k);
      if (el) el.textContent = cnt;
    });
  }

  /* ─── cambiar carpeta ────────────────────────────── */
  function switchFolder(key, btn) {
    currentFolder = key;
    document.querySelectorAll('.folder-chip').forEach(c => c.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('searchInput').value = '';
    document.getElementById('sortSelect').value  = 'newest';
    updateStats();
    renderGrid();
  }

  /* ─── render grid ────────────────────────────────── */
  function renderGrid() {
    const q    = (document.getElementById('searchInput').value || '').toLowerCase();
    const sort = document.getElementById('sortSelect').value;
    let list   = (imagesData[currentFolder] || []).filter(img => !q || img.name.toLowerCase().includes(q));

    if      (sort === 'oldest') list = [...list].reverse();
    else if (sort === 'az')     list = [...list].sort((a,b) => a.name.localeCompare(b.name));
    else if (sort === 'za')     list = [...list].sort((a,b) => b.name.localeCompare(a.name));
    else if (sort === 'size')   list = [...list].sort((a,b) => (b.size||0) - (a.size||0));

    document.getElementById('gridCount').textContent =
      list.length === (imagesData[currentFolder]||[]).length
        ? `${list.length} imagen${list.length !== 1 ? 'es' : ''}`
        : `${list.length} de ${(imagesData[currentFolder]||[]).length}`;

    const grid = document.getElementById('imgGrid');

    if (!list.length) {
      grid.innerHTML = `<div class="img-empty">
        <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21,15 16,10 5,21"/></svg>
        <p>${q ? 'Sin resultados para "' + escH(q) + '"' : 'Sin imágenes en esta carpeta'}</p>
        ${!q ? '<strong>Arrastra archivos o usa el botón Subir imágenes</strong>' : ''}
      </div>`;
      return;
    }

    grid.innerHTML = list.map(img => {
      const ext  = extOf(img.name);
      const path = img.path || '';
      const safe = path.replace(/'/g, "\\'");
      return `
        <div class="img-card">
          <div class="img-thumb-wrap" onclick="openLightbox('${escH(path)}','${escH(img.name)}')">
            <img class="img-thumb" src="${escH(path)}?t=${Date.now()}"
              alt="${escH(img.name)}" loading="lazy"
              onload="const d=this.closest('.img-card').querySelector('.img-size'); if (d) d.textContent = this.naturalWidth+'×'+this.naturalHeight+'px · '+d.textContent;"
              onerror="this.classList.add('err')">
            <div class="img-overlay">
              <div class="img-overlay-actions">
                <button class="ioa-btn preview" onclick="event.stopPropagation();openLightbox('${escH(path)}','${escH(img.name)}')" title="Ver imagen">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                  Ver
                </button>
                <button class="ioa-btn copy" onclick="event.stopPropagation();copyPath('${escH(path)}')" title="Copiar ruta">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
                  Ruta
                </button>
                <button class="ioa-btn del" onclick="event.stopPropagation();openDelModal('${safe}')" title="Eliminar">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3,6 5,6 21,6"/><path d="M19,6l-1,14a2,2,0,0,1-2,2H8a2,2,0,0,1-2-2L5,6"/></svg>
                </button>
              </div>
            </div>
          </div>
          <div class="img-info">
            <div class="img-name" title="${escH(img.name)}">${escH(img.name)}</div>
            <div class="img-meta">
              <span class="img-size">${fmtSize(img.size)}</span>
              <span class="img-ext">${ext}</span>
            </div>
          </div>
        </div>`;
    }).join('');
  }

  /* ─── cargar imágenes ────────────────────────────── */
  async function loadImages() {
    try {
      const res  = await fetch('../../api/imagenes.php?action=list', { headers: { 'X-CSRF-Token': CSRF_TOKEN } });
      const json = await res.json();
      if (!json.ok) { showToast('Error al cargar imágenes', 'error'); return; }
      imagesData = json.data || {};
      updateStats();
      renderGrid();
    } catch { showToast('Error de conexión', 'error'); }
  }

  /* ─── lightbox ───────────────────────────────────── */
  function openLightbox(path, name) {
    document.getElementById('lightboxImg').src     = path;
    document.getElementById('lightboxCaption').textContent = name;
    document.getElementById('lightbox').classList.add('open');
  }
  function closeLightbox() { document.getElementById('lightbox').classList.remove('open'); }
  document.addEventListener('keydown', e => { if (e.key === 'Escape') { closeLightbox(); closeDelModal(); } });

  /* ─── copiar ruta ────────────────────────────────── */
  async function copyPath(path) {
    try {
      await navigator.clipboard.writeText(path);
      showToast('Ruta copiada al portapapeles');
    } catch {
      showToast('No se pudo copiar', 'error');
    }
  }

  /* ─── modal eliminar ─────────────────────────────── */
  async function openDelModal(path) {
    pendingDelete = path;
    document.getElementById('delModalPath').textContent = path;
    document.getElementById('delModal').classList.add('open');

    const usageEl = document.getElementById('delModalUsage');
    usageEl.style.display = 'none';
    try {
      const res  = await fetch('../../api/imagenes.php?action=check-usage&path=' + encodeURIComponent(path));
      const json = await res.json();
      if (json.ok && json.usedIn && json.usedIn.length) {
        usageEl.textContent = `⚠ Esta imagen sigue en uso en: ${json.usedIn.join(', ')}. Si la borras, esas secciones quedarán con una imagen rota.`;
        usageEl.style.display = 'block';
      }
    } catch { /* si falla la verificación, se deja eliminar sin advertencia */ }
  }
  function closeDelModal() {
    document.getElementById('delModal').classList.remove('open');
    pendingDelete = null;
  }

  async function confirmDelete() {
    if (!pendingDelete) return;
    const path = pendingDelete;
    closeDelModal();

    const fd = new FormData();
    fd.append('action', 'delete');
    fd.append('path', path);
    try {
      const res  = await fetch('../../api/imagenes.php', { method: 'POST', headers: { 'X-CSRF-Token': CSRF_TOKEN }, body: fd });
      const json = await res.json();
      if (json.ok) {
        showToast('Imagen eliminada');
        const arr = imagesData[currentFolder];
        if (arr) {
          const pos = arr.findIndex(img => img.path === path);
          if (pos > -1) arr.splice(pos, 1);
        }
        updateStats();
        renderGrid();
      } else {
        showToast(json.error || 'Error al eliminar', 'error');
      }
    } catch {
      showToast('Error de conexión', 'error');
    }
  }

  /* ─── subir imágenes ─────────────────────────────── */
  async function uploadFiles(files) {
    if (!files || !files.length) return;
    const wrap  = document.getElementById('progressWrap');
    const bar   = document.getElementById('uploadProgressBar');
    const label = document.getElementById('progressLabel');
    wrap.style.display = 'block';

    const folderMap  = { general: 'general', partners: 'partners', avatares: 'avatares' };
    const folderName = folderMap[currentFolder] || 'general';
    let done = 0;
    const total = files.length;

    for (const file of files) {
      if (file.size > 5 * 1024 * 1024) {
        showToast(`${file.name}: máximo 5 MB`, 'error');
        done++; continue;
      }
      label.textContent = `Subiendo ${done + 1} de ${total}: ${file.name}`;
      const fd = new FormData();
      fd.append('image', file);
      fd.append('folder', folderName);
      try {
        const res  = await fetch('../../api/contenido.php', { method: 'POST', headers: { 'X-CSRF-Token': CSRF_TOKEN }, body: fd });
        const json = await res.json();
        if (json.ok) {
          if (!imagesData[currentFolder]) imagesData[currentFolder] = [];
          imagesData[currentFolder].unshift({ name: file.name, path: '/' + json.path.replace(/^\/+/, ''), size: file.size });
        } else {
          showToast(json.error || `Error: ${file.name}`, 'error');
        }
      } catch { showToast('Error de conexión', 'error'); }
      done++;
      bar.style.width = Math.round((done / total) * 100) + '%';
    }

    label.textContent = `${total} imagen${total !== 1 ? 'es' : ''} procesada${total !== 1 ? 's' : ''}`;
    setTimeout(() => { wrap.style.display = 'none'; bar.style.width = '0%'; }, 1200);
    document.getElementById('uploadInput').value = '';
    updateStats();
    renderGrid();
    if (total === done) showToast(total === 1 ? 'Imagen subida correctamente' : `${total} imágenes subidas`);
  }

  /* ─── drag-and-drop ──────────────────────────────── */
  const dz = document.getElementById('dropZone');
  dz.addEventListener('dragover',  e => { e.preventDefault(); dz.classList.add('drag'); });
  dz.addEventListener('dragleave', () => dz.classList.remove('drag'));
  dz.addEventListener('drop', e => {
    e.preventDefault();
    dz.classList.remove('drag');
    uploadFiles(e.dataTransfer.files);
  });

  /* ─── arrancar ───────────────────────────────────── */
  loadImages();
