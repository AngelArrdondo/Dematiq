AdminSidebar.init('contacto', '../../', '../../../');

const esc = s => String(s ?? '').replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;');

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

/* ══ FICHA DE LA EMPRESA (PDF / Word / PowerPoint) ═══════════════════ */
// _D.presentacionPdf puede ser: undefined (nunca configurado, usar el PDF
// original de respaldo), '' (el admin lo eliminó explícitamente) o una ruta.
let fichaPdfPath = (_D.presentacionPdf !== undefined) ? _D.presentacionPdf : 'assets/docs/DEMATIQ.pdf';

function actualizarUIFicha() {
  const sinDoc  = document.getElementById('ficha-sin-documento');
  const conDoc  = document.getElementById('ficha-con-documento');
  const delBtn  = document.getElementById('ficha-delete-btn');
  const box     = document.getElementById('ficha-preview-box');
  const hayDoc  = fichaPdfPath !== '';
  sinDoc.style.display = hayDoc ? 'none' : '';
  conDoc.style.display = hayDoc ? '' : 'none';
  delBtn.style.display = hayDoc ? '' : 'none';
  if (!hayDoc) { box.innerHTML = ''; return; }

  const url  = '../../../' + fichaPdfPath;
  const name = fichaPdfPath.split('/').pop();
  const ext  = (fichaPdfPath.split('.').pop() || '').toUpperCase();

  if (ext === 'PDF') {
    box.innerHTML = `<iframe id="ficha-preview-iframe" class="ficha-preview-iframe" src="${esc(url)}"></iframe>`;
  } else {
    box.innerHTML = `
      <div class="ficha-file-card" id="ficha-file-card">
        <div class="ficha-file-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14,2 14,8 20,8"/></svg>
        </div>
        <div class="ficha-file-info">
          <strong id="ficha-file-name">${esc(name)}</strong>
          <span id="ficha-file-ext" class="ficha-file-ext">${esc(ext)}</span>
        </div>
        <p class="ficha-file-note">Vista previa no disponible para este formato</p>
      </div>`;
  }
}

/* ── Visor de documento completo (lightbox) ──────── */
function verFichaCompleta() {
  if (!fichaPdfPath) { showToast('Todavía no hay documento para mostrar', 'error'); return; }
  const url = '../../../' + fichaPdfPath;
  const ext = (fichaPdfPath.split('.').pop() || '').toLowerCase();
  if (ext === 'pdf') {
    document.getElementById('fichaLightboxIframe').src = url;
    document.getElementById('fichaLightbox').classList.add('show');
  } else {
    window.open(url, '_blank');
  }
}
function closeFichaLightbox() {
  document.getElementById('fichaLightbox').classList.remove('show');
  document.getElementById('fichaLightboxIframe').src = '';
}
document.addEventListener('keydown', e => {
  if (e.key === 'Escape' && document.getElementById('fichaLightbox').classList.contains('show')) {
    closeFichaLightbox();
  }
});

async function uploadFichaPdf(input) {
  if (!input.files[0]) return;
  if (input.files[0].size > 15 * 1024 * 1024) { showToast('Máximo 15 MB', 'error'); input.value = ''; return; }
  const fd = new FormData();
  fd.append('documento', input.files[0]);
  fd.append('oldPath', fichaPdfPath);
  fd.append('_csrf', CSRF_TOKEN);
  try {
    const res  = await fetch('../../api/contenido.php', { method: 'POST', headers: { 'X-CSRF-Token': CSRF_TOKEN }, body: fd });
    const json = await res.json();
    if (json.ok) {
      fichaPdfPath = json.path;
      actualizarUIFicha();
      showToast('Documento actualizado — recuerda guardar cambios');
    } else { showToast(json.error || 'Error al subir el documento', 'error'); }
  } catch { showToast('Error de conexión', 'error'); }
  finally { input.value = ''; }
}

async function eliminarFichaDocumento() {
  if (!confirm('¿Eliminar el documento actual? El botón "Presentación" dejará de mostrarse en el sitio hasta que subas uno nuevo.')) return;
  const fd = new FormData();
  fd.append('action', 'eliminar_documento');
  fd.append('path', fichaPdfPath);
  fd.append('_csrf', CSRF_TOKEN);
  try {
    const res  = await fetch('../../api/contenido.php', { method: 'POST', headers: { 'X-CSRF-Token': CSRF_TOKEN }, body: fd });
    const json = await res.json();
    if (json.ok) {
      fichaPdfPath = '';
      actualizarUIFicha();
      showToast('Documento eliminado — recuerda guardar cambios');
    } else { showToast(json.error || 'Error al eliminar', 'error'); }
  } catch { showToast('Error de conexión', 'error'); }
}

/* ══ WHATSAPP ════════════════════════════════════ */
function fmtPhone(code, local) {
  const d = local.replace(/\D/g,'');
  if (code === '52' && d.length === 10)
    return `+52 (${d.slice(0,3)}) ${d.slice(3,6)}-${d.slice(6)}`;
  if (code === '1' && d.length === 10)
    return `+1 (${d.slice(0,3)}) ${d.slice(3,6)}-${d.slice(6)}`;
  return `+${code} ${local.trim()}`;
}
function buildLink(code, local) {
  const d = local.replace(/\D/g,'');
  const c = (code !== '52' && code !== '1' && d.startsWith('0')) ? d.slice(1) : d;
  return code + c;
}
function updateWA() {
  const code = document.getElementById('wa-country').value;
  const loc  = document.getElementById('wa-local').value.trim();
  if (!loc) {
    document.getElementById('wa-display-val').textContent = '—';
    document.getElementById('wa-link-val').textContent    = '—';
    return;
  }
  document.getElementById('wa-display-val').textContent = fmtPhone(code, loc);
  document.getElementById('wa-link-val').textContent    = buildLink(code, loc);
}
function initWA() {
  const raw = _D.whatsappNum || '';
  if (!raw) return;
  const codes = ['52','1','34','55','54','57','56','51','49','33','44','39','81','86','82'];
  let matched = '', local = raw;
  for (const c of codes) {
    if (raw.startsWith(c)) { matched = c; local = raw.slice(c.length); break; }
  }
  if (matched) document.getElementById('wa-country').value = matched;
  document.getElementById('wa-local').value = local;
  updateWA();
}

/* ══ SCHEDULE ════════════════════════════════════ */
const DAYS = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];
let slots = [];

function renderSlots() {
  const c = document.getElementById('sched-slots');
  c.innerHTML = '';
  slots.forEach((slot, i) => {
    const wrap = document.createElement('div');
    wrap.className = 'sched-slot';

    const top = document.createElement('div');
    top.className = 'sched-slot-top';

    const lbl = document.createElement('span');
    lbl.className = 'sched-slot-label';
    lbl.textContent = slots.length > 1 ? `Turno ${i+1}` : 'Días';
    top.appendChild(lbl);

    const days = document.createElement('div');
    days.className = 'sched-days';
    DAYS.forEach((d, j) => {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'sched-day' + (slot.days.includes(j) ? ' on' : '');
      btn.textContent = d;
      btn.addEventListener('click', () => {
        const pos = slots[i].days.indexOf(j);
        if (pos >= 0) slots[i].days.splice(pos, 1);
        else { slots[i].days.push(j); slots[i].days.sort((a,b)=>a-b); }
        renderSlots();
      });
      days.appendChild(btn);
    });
    top.appendChild(days);

    if (slots.length > 1) {
      const rm = document.createElement('button');
      rm.className = 'sched-rm'; rm.type = 'button';
      rm.title = 'Eliminar turno';
      rm.innerHTML = `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>`;
      rm.addEventListener('click', () => { slots.splice(i,1); renderSlots(); });
      top.appendChild(rm);
    }

    wrap.appendChild(top);

    const timeRow = document.createElement('div');
    timeRow.className = 'sched-time-row';

    const lblA = document.createElement('span'); lblA.className='sched-time-label'; lblA.textContent='Abre:';
    const inA  = document.createElement('input'); inA.type='time'; inA.className='sched-time-inp'; inA.value=slot.from;
    inA.addEventListener('change', () => { slots[i].from = inA.value; updateSchedPreview(); });

    const sep  = document.createElement('span'); sep.className='sched-time-sep'; sep.textContent='—';

    const lblC = document.createElement('span'); lblC.className='sched-time-label'; lblC.textContent='Cierra:';
    const inC  = document.createElement('input'); inC.type='time'; inC.className='sched-time-inp'; inC.value=slot.to;
    inC.addEventListener('change', () => { slots[i].to = inC.value; updateSchedPreview(); });

    [lblA, inA, sep, lblC, inC].forEach(el => timeRow.appendChild(el));
    wrap.appendChild(timeRow);
    c.appendChild(wrap);
  });
  updateSchedPreview();
}

function addSlot() {
  slots.push({ days:[1,2,3,4,5], from:'08:00', to:'18:00' });
  renderSlots();
}

function fmtDays(days) {
  if (!days.length) return '';
  let consec = true;
  for (let i=1;i<days.length;i++) if (days[i]!==days[i-1]+1){consec=false;break;}
  if (consec && days.length > 2) return `${DAYS[days[0]]} – ${DAYS[days[days.length-1]]}`;
  if (days.length === 1) return DAYS[days[0]];
  return days.map(d=>DAYS[d]).join(', ');
}
function fmtT(t) {
  if (!t) return '';
  const [h,m] = t.split(':');
  return m==='00' ? `${parseInt(h)}:00` : `${parseInt(h)}:${m}`;
}
function updateSchedPreview() {
  const parts = slots
    .filter(s => s.days.length && s.from && s.to)
    .map(s => `${fmtDays(s.days)} · ${fmtT(s.from)} – ${fmtT(s.to)}`);
  document.getElementById('sched-preview-val').textContent = parts.join(' | ') || '—';
}
function getHorario() {
  return slots
    .filter(s=>s.days.length&&s.from&&s.to)
    .map(s=>`${fmtDays(s.days)} · ${fmtT(s.from)} – ${fmtT(s.to)}`)
    .join(' | ');
}

function parseHorario(str) {
  if (!str) return null;
  const results = [];
  for (const part of str.split(' | ')) {
    const di = part.indexOf(' · ');
    if (di < 0) continue;
    const dayPart = part.slice(0, di).trim();
    const timePart = part.slice(di+3).trim();
    const tm = timePart.match(/^(\d+:\d+)\s*–\s*(\d+:\d+)$/);
    if (!tm) continue;
    const pad = t => { const[h,m]=t.split(':'); return `${h.padStart(2,'0')}:${m.padStart(2,'0')}`; };
    let dayIdxs = [];
    if (dayPart.includes('–')) {
      const [f,t2] = dayPart.split('–').map(s=>s.trim());
      const fi=DAYS.indexOf(f), ti=DAYS.indexOf(t2);
      if (fi>=0&&ti>=0) for(let d=fi;d<=ti;d++) dayIdxs.push(d);
    } else {
      dayPart.split(',').forEach(d => { const i=DAYS.indexOf(d.trim()); if(i>=0) dayIdxs.push(i); });
    }
    if (dayIdxs.length) results.push({ days:dayIdxs, from:pad(tm[1]), to:pad(tm[2]) });
  }
  return results.length ? results : null;
}

function initSchedule() {
  const p = parseHorario(_D.horario || '');
  slots = p || [{ days:[1,2,3,4,5], from:'08:00', to:'18:00' }];
  renderSlots();
}

/* ══ DÍAS FESTIVOS ═══════════════════════════════ */
const MAX_FESTIVOS = 30;
const MESES = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
let festivos = [];

function renderFestivos() {
  const c = document.getElementById('festivos-list');
  c.innerHTML = '';
  if (!festivos.length) {
    c.innerHTML = '<p class="festivos-empty">Sin fechas agregadas — se atiende según el horario semanal todos los días.</p>';
    return;
  }
  festivos.forEach((f, i) => {
    const diaOpts = ['<option value="">Día</option>']
      .concat(Array.from({length:31}, (_,d) => `<option value="${d+1}"${Number(f.dia)===d+1?' selected':''}>${d+1}</option>`))
      .join('');
    const mesOpts = ['<option value="">Mes</option>']
      .concat(MESES.map((m,idx) => `<option value="${idx+1}"${Number(f.mes)===idx+1?' selected':''}>${m.charAt(0).toUpperCase()+m.slice(1)}</option>`))
      .join('');
    const row = document.createElement('div');
    row.className = 'social-field';
    row.innerHTML = `
      <div class="social-field-head">
        <span class="social-name">Fecha ${i + 1}</span>
        <button type="button" class="social-rm" title="Quitar">
          <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>
      <div class="festivo-row-inputs">
        <select class="festivo-dia">${diaOpts}</select>
        <select class="festivo-mes">${mesOpts}</select>
        <input type="text" class="festivo-motivo" placeholder="Motivo (ej. Navidad)" value="${esc(f.motivo)}">
      </div>`;
    row.querySelector('.festivo-dia').addEventListener('change', e => { festivos[i].dia = e.target.value; });
    row.querySelector('.festivo-mes').addEventListener('change', e => { festivos[i].mes = e.target.value; });
    row.querySelector('.festivo-motivo').addEventListener('input', e => { festivos[i].motivo = e.target.value; });
    row.querySelector('.social-rm').addEventListener('click', () => { festivos.splice(i, 1); renderFestivos(); });
    c.appendChild(row);
  });
}

function addFestivo() {
  if (festivos.length >= MAX_FESTIVOS) { showToast(`Máximo ${MAX_FESTIVOS} fechas permitidas`, 'error'); return; }
  festivos.push({ dia: '', mes: '', motivo: '' });
  renderFestivos();
}

function initFestivos() {
  festivos = Array.isArray(_D.diasFestivos)
    ? _D.diasFestivos.map(f => ({ dia: f.dia || '', mes: f.mes || '', motivo: f.motivo || '' }))
    : [];
  renderFestivos();
}

function getFestivos() {
  return festivos
    .filter(f => f.dia && f.mes)
    .map(f => ({ dia: Number(f.dia), mes: Number(f.mes), motivo: (f.motivo || '').trim() }));
}

/* ══ MAP ═════════════════════════════════════════ */
let map, marker;
const DEF_LAT = 20.621222, DEF_LNG = -100.470500;

function initMap() {
  const raw = (_D.mapCoords||'').trim();
  let lat = DEF_LAT, lng = DEF_LNG;
  if (raw) {
    const p = raw.split(',');
    if (p.length===2) { lat=parseFloat(p[0])||DEF_LAT; lng=parseFloat(p[1])||DEF_LNG; }
  }
  map = L.map('leaflet-map').setView([lat,lng], raw?16:13);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{
    attribution:'© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>', maxZoom:19
  }).addTo(map);
  if (raw) {
    placeMarker(lat, lng);
    document.getElementById('map-coords').value = `${lat},${lng}`;
    /* On load, dirección already comes from DB — don't overwrite */
  }
  map.on('click', e => { placeMarker(e.latlng.lat, e.latlng.lng); setCoords(e.latlng.lat, e.latlng.lng); });
}

function placeMarker(lat,lng) {
  if (marker) marker.setLatLng([lat,lng]);
  else {
    marker = L.marker([lat,lng],{draggable:true}).addTo(map);
    marker.on('dragend', e => { const p=e.target.getLatLng(); setCoords(p.lat,p.lng); });
  }
}
function setCoords(lat, lng) {
  document.getElementById('map-coords').value = `${lat.toFixed(6)},${lng.toFixed(6)}`;
  reverseGeocode(lat, lng);
}

async function reverseGeocode(lat, lng) {
  try {
    const res  = await fetch(
      `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&accept-language=es`,
      { headers: { 'Accept-Language': 'es' } }
    );
    const json = await res.json();
    if (!json || !json.display_name) return;

    /* Build a cleaner address from the structured parts */
    const a = json.address || {};
    const parts = [
      a.road || a.pedestrian || a.footway || '',
      a.house_number ? `#${a.house_number}` : '',
      a.suburb || a.neighbourhood || a.quarter || '',
      a.city || a.town || a.village || a.municipality || '',
      a.state || '',
      a.country || ''
    ].filter(Boolean);
    const addr = parts.length >= 3 ? parts.join(', ') : json.display_name;

    const el = document.getElementById('direccion');
    el.value = addr;
    document.getElementById('dir-auto-badge').style.display = 'inline-block';
  } catch { /* silent — coords still saved */ }
}

async function geocode(q, fillAddress = false) {
  try {
    const res  = await fetch(
      `https://nominatim.openstreetmap.org/search?format=json&limit=1&q=${encodeURIComponent(q)}`,
      { headers: { 'Accept-Language': 'es' } }
    );
    const json = await res.json();
    if (!json.length) { showToast('No se encontró la dirección','error'); return; }
    const la = parseFloat(json[0].lat), lo = parseFloat(json[0].lon);
    map.setView([la, lo], 17);
    placeMarker(la, lo);
    document.getElementById('map-coords').value = `${la.toFixed(6)},${lo.toFixed(6)}`;
    if (fillAddress) reverseGeocode(la, lo);
    showToast('Ubicación encontrada');
  } catch { showToast('Error al buscar','error'); }
}

function geocodeSearch() {
  const q = document.getElementById('map-search-input').value.trim();
  if (q) geocode(q, true);
}

function getMyLocation() {
  if (!navigator.geolocation) { showToast('Tu navegador no soporta geolocalización','error'); return; }
  const btn = document.getElementById('btn-my-loc');
  btn.disabled = true;
  btn.style.opacity = '.6';
  navigator.geolocation.getCurrentPosition(
    pos => {
      const la = pos.coords.latitude, lo = pos.coords.longitude;
      map.setView([la, lo], 17);
      placeMarker(la, lo);
      setCoords(la, lo);
      showToast('Ubicación obtenida');
      btn.disabled = false; btn.style.opacity = '1';
    },
    err => {
      const msgs = { 1:'Permiso denegado', 2:'Posición no disponible', 3:'Tiempo agotado' };
      showToast(msgs[err.code] || 'Error de geolocalización','error');
      btn.disabled = false; btn.style.opacity = '1';
    },
    { enableHighAccuracy: true, timeout: 10000 }
  );
}

/* ══ SAVE ════════════════════════════════════════ */
async function saveContacto() {
  const code = document.getElementById('wa-country').value;
  const loc  = document.getElementById('wa-local').value.trim();
  try {
    const res = await CM.set('contacto', {
      email:       document.getElementById('email').value.trim(),
      whatsapp:    loc ? fmtPhone(code,loc) : '',
      whatsappNum: loc ? buildLink(code,loc) : '',
      horario:     getHorario(),
      diasFestivos: getFestivos(),
      direccion:   document.getElementById('direccion').value.trim(),
      mapCoords:   document.getElementById('map-coords').value.trim(),
      presentacionPdf: fichaPdfPath,
      social: getSocials()
    });
    if (res && res.ok) {
      showToast('Cambios guardados correctamente');
      const btn = document.getElementById('mainSaveBtn');
      if (btn) { btn.classList.add('saved'); setTimeout(() => btn.classList.remove('saved'), 900); }
    }
    else showToast(res?.error||'Error al guardar','error');
  } catch { showToast('Error de conexión','error'); }
}

/* ══ SOCIAL NETWORKS ════════════════════════════ */
const NETWORKS = [
  { id:'facebook',  name:'Facebook',   cls:'fb', placeholder:'https://facebook.com/dematiq',
    icon:`<svg viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>` },
  { id:'instagram', name:'Instagram',  cls:'ig', placeholder:'https://instagram.com/dematiq',
    icon:`<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>` },
  { id:'linkedin',  name:'LinkedIn',   cls:'li', placeholder:'https://linkedin.com/company/dematiq',
    icon:`<svg viewBox="0 0 24 24" fill="currentColor"><path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/></svg>` },
  { id:'youtube',   name:'YouTube',    cls:'yt', placeholder:'https://youtube.com/@dematiq',
    icon:`<svg viewBox="0 0 24 24" fill="currentColor"><path d="M22.54 6.42a2.78 2.78 0 00-1.95-1.96C18.88 4 12 4 12 4s-6.88 0-8.59.46a2.78 2.78 0 00-1.95 1.96A29 29 0 001 12a29 29 0 00.46 5.58A2.78 2.78 0 003.41 19.54C5.12 20 12 20 12 20s6.88 0 8.59-.46a2.78 2.78 0 001.95-1.96A29 29 0 0023 12a29 29 0 00-.46-5.58z"/><polygon points="9.75,15.02 15.5,12 9.75,8.98 9.75,15.02" fill="#fff"/></svg>` },
  { id:'twitter',   name:'X / Twitter',cls:'tw', placeholder:'https://x.com/dematiq',
    icon:`<svg viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.743l7.737-8.835L1.254 2.25H8.08l4.259 5.63L18.244 2.25zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>` },
  { id:'tiktok',    name:'TikTok',     cls:'tt', placeholder:'https://tiktok.com/@dematiq',
    icon:`<svg viewBox="0 0 24 24" fill="currentColor"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.34 6.34 0 106.34 6.34V8.69a8.18 8.18 0 004.78 1.52V6.76a4.85 4.85 0 01-1.01-.07z"/></svg>` },
  { id:'pinterest', name:'Pinterest',  cls:'pt', placeholder:'https://pinterest.com/dematiq',
    icon:`<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.373 0 0 5.373 0 12c0 5.084 3.163 9.426 7.627 11.174-.105-.949-.2-2.405.042-3.441.218-.937 1.407-5.965 1.407-5.965s-.359-.719-.359-1.782c0-1.668.967-2.914 2.171-2.914 1.023 0 1.518.769 1.518 1.69 0 1.029-.655 2.568-.994 3.995-.283 1.194.599 2.169 1.777 2.169 2.133 0 3.772-2.249 3.772-5.495 0-2.873-2.064-4.882-5.012-4.882-3.414 0-5.418 2.561-5.418 5.207 0 1.031.397 2.138.893 2.738.099.12.113.225.083.345l-.333 1.36c-.053.22-.174.267-.402.161-1.499-.698-2.436-2.889-2.436-4.649 0-3.785 2.75-7.262 7.929-7.262 4.163 0 7.398 2.967 7.398 6.931 0 4.136-2.607 7.464-6.227 7.464-1.216 0-2.359-.632-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146C9.57 23.812 10.763 24 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0z"/></svg>` },
  { id:'threads',   name:'Threads',    cls:'th', placeholder:'https://threads.net/@dematiq',
    icon:`<svg viewBox="0 0 192 192" fill="currentColor"><path d="M141.537 88.988a66.667 66.667 0 00-2.518-1.143c-1.482-27.307-16.403-42.94-41.457-43.1h-.34c-14.986 0-27.449 6.396-35.12 18.036l13.779 9.452c5.73-8.695 14.724-10.548 21.348-10.548h.231c8.248.053 14.474 2.452 18.502 7.13 2.932 3.405 4.893 8.11 5.864 14.05-7.314-1.243-15.224-1.626-23.68-1.14-23.82 1.371-39.134 15.264-38.105 34.568.522 9.792 5.4 18.216 13.735 23.719 7.047 4.652 16.124 6.927 25.557 6.412 12.458-.683 22.231-5.436 29.049-14.127 5.178-6.6 8.453-15.153 9.899-25.93 5.937 3.583 10.337 8.298 12.767 13.966 4.132 9.635 4.373 25.468-8.546 38.318-11.319 11.255-24.94 16.12-45.508 16.274-22.739-.169-39.951-7.418-51.15-21.551C27.36 139.343 21.96 120.712 21.768 96c.192-24.712 5.592-43.343 16.033-55.369C48.999 26.418 66.211 19.169 88.95 19c21.92.17 38.978 7.452 50.7 21.645 5.765 6.974 10.11 15.808 12.964 26.07l16.239-4.285c-3.5-12.93-9.044-24.116-16.609-33.333C136.637 11.816 115.498 2.295 89.04 2.1h-.1C62.578 2.295 41.205 11.848 26.891 29.2 14.3 44.438 7.694 65.796 7.5 95.976v.05c.194 30.18 6.8 51.538 19.391 66.774C41.205 180.152 62.578 189.705 89 189.9h.1c23.433-.16 39.912-6.53 53.5-20.045 17.679-17.579 17.154-39.663 11.337-53.12-4.216-9.822-12.208-17.799-24.4-23.747zM96.165 138.3c-10.39.583-21.208-4.074-21.727-14.053-.39-7.348 5.241-15.53 22.308-16.535 1.953-.112 3.868-.168 5.745-.168 6.097 0 11.808.606 17.05 1.778-1.939 24.184-12.834 28.394-23.376 28.978z"/></svg>` },
  { id:'snapchat',  name:'Snapchat',   cls:'sc', placeholder:'https://snapchat.com/add/dematiq',
    icon:`<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12.166.001C10.009.001 4.5.56 4.5 6.899V9H3l-.5 2.5h2C4.18 13.274 3.5 14.5 2 15c0 0 .5 2 4 2 0 0 .9 2 6 2s6-2 6-2c3.5 0 4-2 4-2-1.5-.5-2.18-1.726-2.5-3.5h2L21 9h-1.5V6.899C19.5.56 14.323.001 12.166.001z"/></svg>` },
];

let socialItems = [];

function getNetwork(id) { return NETWORKS.find(n => n.id === id); }

function renderSocialGrid() {
  const grid = document.getElementById('social-grid');
  grid.innerHTML = '';
  socialItems.forEach((item, idx) => {
    const net = getNetwork(item.id);
    if (!net) return;
    const field = document.createElement('div');
    field.className = 'social-field';
    field.innerHTML = `
      <div class="social-field-head">
        <div class="social-ico ${net.cls}">${net.icon}</div>
        <span class="social-name">${net.name}</span>
        <button type="button" class="social-rm" title="Quitar">
          <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>
      <input type="url" data-social-id="${net.id}" value="${esc(item.url)}" placeholder="${net.placeholder}">
    `;
    field.querySelector('.social-rm').addEventListener('click', () => removeSocial(idx));
    grid.appendChild(field);
  });
  updatePickerState();
}

function removeSocial(idx) {
  socialItems.splice(idx, 1);
  renderSocialGrid();
}

function toggleSocialPicker(e) {
  e.stopPropagation();
  document.getElementById('social-picker').classList.toggle('open');
}

function addSocial(id) {
  if (socialItems.find(i => i.id === id)) return;
  socialItems.push({ id, url: '' });
  document.getElementById('social-picker').classList.remove('open');
  renderSocialGrid();
}

function renderPickerGrid() {
  const pg = document.getElementById('social-picker-grid');
  pg.innerHTML = '';
  NETWORKS.forEach(net => {
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'social-picker-item';
    btn.dataset.id = net.id;
    btn.onclick = () => addSocial(net.id);
    btn.innerHTML = `<div class="social-ico ${net.cls}">${net.icon}</div><span>${net.name}</span>`;
    pg.appendChild(btn);
  });
}

function updatePickerState() {
  const added = socialItems.map(i => i.id);
  document.querySelectorAll('.social-picker-item').forEach(btn => {
    btn.classList.toggle('added', added.includes(btn.dataset.id));
  });
}

function initSocials() {
  const s = _D.social || {};
  const keyMap = ['facebook','instagram','linkedin','youtube','twitter','tiktok','pinterest','threads','snapchat'];
  socialItems = keyMap.filter(k => s[k]).map(k => ({ id: k, url: s[k] }));
  renderPickerGrid();
  renderSocialGrid();
}

function getSocials() {
  const result = {};
  document.querySelectorAll('#social-grid input[data-social-id]').forEach(inp => {
    result[inp.dataset.socialId] = inp.value.trim();
  });
  return result;
}

document.addEventListener('click', () => {
  document.getElementById('social-picker').classList.remove('open');
});

/* ── Quick nav: scroll-to + scrollspy ──────────── */
(function initQuickNav() {
  const pills = Array.from(document.querySelectorAll('.qn-pill'));
  if (!pills.length) return;
  pills.forEach(p => p.addEventListener('click', () => {
    const target = document.getElementById(p.dataset.target);
    if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }));
  const sections = pills.map(p => document.getElementById(p.dataset.target)).filter(Boolean);
  function spy() {
    let activeIdx = 0;
    const refY = 140;
    sections.forEach((sec, i) => { if (sec.getBoundingClientRect().top - refY <= 0) activeIdx = i; });
    pills.forEach((p, i) => p.classList.toggle('active', i === activeIdx));
  }
  document.addEventListener('scroll', spy, { passive: true });
  spy();
})();

/* ── Banner stats ──────────────────────────────── */
function updateBannerStats() {
  const statSocials = document.getElementById('statSocials');
  const statSlots    = document.getElementById('statSlots');
  const statLoc      = document.getElementById('statLoc');
  if (statSocials) statSocials.textContent = socialItems.filter(i => i.url).length || '0';
  if (statSlots)   statSlots.textContent   = slots.length || '0';
  if (statLoc)     statLoc.textContent     = (_D.mapCoords || '').trim() ? 'Fijada' : 'Sin fijar';
}

/* ── Init ────────────────────────────────────── */
initWA();
initSchedule();
initFestivos();
initSocials();
updateBannerStats();
document.addEventListener('DOMContentLoaded', initMap);
