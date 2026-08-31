AdminSidebar.init('navegacion', '../../', '../../../');

/* ── User menu ─────────────────────────────────── */
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

/* ── Enlaces extra junto al login ─────────────────── */
const MAX_EXTRAS = 6;
const trashIcon = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3,6 5,6 21,6"/><path d="M19,6l-1,14a2,2,0,0,1-2,2H8a2,2,0,0,1-2-2L5,6"/><path d="M10,11v6"/><path d="M14,11v6"/></svg>`;

let extras = ((window.__DB_CONTENT && window.__DB_CONTENT.navegacion && window.__DB_CONTENT.navegacion.extras) || [])
  .map(e => ({ texto: e.texto || '', url: e.url || '' }));

function esc(s) {
  return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function renderExtras() {
  const c = document.getElementById('extrasContainer');
  const addBtn = document.getElementById('addExtraBtn');
  if (addBtn) addBtn.disabled = extras.length >= MAX_EXTRAS;

  if (!extras.length) {
    c.innerHTML = `<p style="font-size:.8rem;color:var(--text-lt);margin:0 0 14px">No hay enlaces adicionales.</p>`;
    return;
  }

  c.innerHTML = '';
  extras.forEach((ex, i) => {
    const row = document.createElement('div');
    row.style.cssText = 'display:flex;gap:10px;align-items:flex-end;margin-bottom:12px';
    row.innerHTML = `
      <div class="form-group" style="flex:1;margin-bottom:0">
        <label>Texto</label>
        <input type="text" maxlength="30" placeholder="Ej. WhatsApp" autocomplete="off" value="${esc(ex.texto)}"
          oninput="extras[${i}].texto=this.value">
      </div>
      <div class="form-group" style="flex:2;margin-bottom:0">
        <label>URL de destino</label>
        <input type="url" id="extraUrl${i}" maxlength="255" placeholder="https://..." autocomplete="off" value="${esc(ex.url)}"
          oninput="extras[${i}].url=this.value">
      </div>
      <button type="button" class="btn-rm" title="Eliminar enlace" onclick="removeExtra(${i})" style="margin-bottom:5px">${trashIcon}</button>
    `;
    c.appendChild(row);
  });
}

function addExtra() {
  if (extras.length >= MAX_EXTRAS) { showToast(`Máximo ${MAX_EXTRAS} enlaces permitidos`, 'error'); return; }
  extras.push({ texto: '', url: '' });
  renderExtras();
}

function removeExtra(i) {
  extras.splice(i, 1);
  renderExtras();
}

renderExtras();

/* ── Destinos de los 5 enlaces fijos ──────────────── */
const DESTINO_IDS = ['Inicio', 'Nosotros', 'Proyectos', 'Industrias', 'Contacto'];

function esDestinoValido(v) {
  return v === '' || v.startsWith('/') || /^https?:\/\//i.test(v);
}

/* ── Guardar ────────────────────────────────────── */
async function saveNavegacion() {
  const tiendaUrlInput = document.getElementById('navTiendaUrl');
  const tiendaUrl = tiendaUrlInput.value.trim();
  if (tiendaUrl && !tiendaUrlInput.checkValidity()) {
    showToast('La URL de Tienda no es válida', 'error');
    tiendaUrlInput.focus();
    return;
  }

  const destinos = {};
  for (const id of DESTINO_IDS) {
    const input = document.getElementById('nav' + id + 'Url');
    const v = input.value.trim();
    if (!esDestinoValido(v)) {
      showToast(`La URL de destino de "${id}" debe empezar con "/" o "http(s)://"`, 'error');
      input.focus();
      return;
    }
    destinos[id.toLowerCase() + 'Url'] = v;
  }

  for (let i = 0; i < extras.length; i++) {
    const urlInput = document.getElementById('extraUrl' + i);
    if (extras[i].url.trim() && urlInput && !urlInput.checkValidity()) {
      showToast(`La URL del enlace #${i + 1} no es válida`, 'error');
      urlInput.focus();
      return;
    }
  }

  const valor = {
    inicio:     document.getElementById('navInicio').value.trim(),
    nosotros:   document.getElementById('navNosotros').value.trim(),
    proyectos:  document.getElementById('navProyectos').value.trim(),
    industrias: document.getElementById('navIndustrias').value.trim(),
    contacto:   document.getElementById('navContacto').value.trim(),
    tienda:     document.getElementById('navTienda').value.trim(),
    tiendaUrl:  tiendaUrl,
    ...destinos,
    extras: extras
      .map(ex => ({ texto: ex.texto.trim(), url: ex.url.trim() }))
      .filter(ex => ex.texto && ex.url)
  };
  try {
    const res = await CM.set('navegacion', valor);
    if (res && res.ok) {
      showToast('Cambios guardados correctamente');
    } else {
      showToast(res?.error || 'Error al guardar', 'error');
    }
  } catch {
    showToast('Error de conexión', 'error');
  }
}
