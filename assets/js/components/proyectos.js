/* ══════════════════════════════════════════════════
   PROYECTOS (listado) — render data-driven + filtros
   v36: + contador por categoría en los chips
        + animación de entrada al filtrar (fade + slide up)
   Layout mixto → tarjetas anchas horizontales (img izq)
   intercaladas con filas de tarjetas normales (3 cols)
   Patrón: cada 4 proyectos → 1 ancha + 3 normales
   ══════════════════════════════════════════════════ */

let PROJECTS = window.DEMATIQ_PROJECTS || [];

const ARROW_SVG = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>`;

const esc = s => String(s ?? '').replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;');

// Las rutas que guarda el admin son relativas a la raíz del sitio (ej. "assets/images/general/x.webp").
// Esta página vive en pages/corporativo/, dos niveles abajo, así que hay que anteponer "../../".
const resolveImg = raw => {
  if (!raw) return '';
  if (/^https?:\/\//.test(raw) || raw.startsWith('/') || raw.startsWith('../')) return raw;
  return '../../' + raw;
};

/* ── Cuenta cuántos proyectos hay por categoría ── */
function countFor(filter) {
  if (filter === 'all') return PROJECTS.length;
  return PROJECTS.filter(p => p.cat === filter).length;
}

/* ── Pinta el contador en cada chip: "Robótica (3)" ── */
function paintCounts() {
  document.querySelectorAll('.proj-chip').forEach(btn => {
    const n = countFor(btn.dataset.filter);
    let badge = btn.querySelector('.proj-chip-count');
    if (!badge) {
      badge = document.createElement('span');
      badge.className = 'proj-chip-count';
      btn.appendChild(badge);
    }
    badge.textContent = ` (${n})`;
  });
}

/* ── Tarjeta normal (vertical: img arriba + texto abajo) ── */
function buildCard(p) {
  const a = document.createElement('a');
  a.href = `proyecto.html?id=${encodeURIComponent(p.id)}`;
  a.className = 'proj-card';
  a.dataset.cat = p.cat;

  const anio = p.anio || '';
  const sector = p.sector || '';

  a.innerHTML = `
    <div class="proj-card-media">
      <span class="proj-badge">${esc(p.badge)}</span>
      <img src="${esc(p.img)}" alt="${esc(p.alt)}" loading="lazy">
    </div>
    <div class="proj-card-body">
      <div class="proj-card-meta">
        ${anio ? `<span>${esc(anio)}</span>` : ''}
        ${sector ? `<span>${esc(sector)}</span>` : ''}
      </div>
      <h3 class="proj-card-title">${esc(p.title)}</h3>
      <p class="proj-card-desc">${esc(p.desc)}</p>
      <div class="proj-card-foot">
        <span class="proj-card-arrow">${ARROW_SVG}</span>
        <span class="proj-card-label">Ver proyecto</span>
      </div>
    </div>`;
  return a;
}

/* ── Tarjeta ancha (horizontal: imagen izquierda + texto derecha) ── */
function buildWide(p) {
  const a = document.createElement('a');
  a.href = `proyecto.html?id=${encodeURIComponent(p.id)}`;
  a.className = 'proj-card proj-card--wide';
  a.dataset.cat = p.cat;

  const anio = p.anio || '';
  const sector = p.sector || '';

  a.innerHTML = `
    <div class="proj-card-media">
      <span class="proj-badge">${esc(p.badge)}</span>
      <img src="${esc(p.img)}" alt="${esc(p.alt)}" loading="lazy">
    </div>
    <div class="proj-card-body">
      <div class="proj-card-meta">
        ${anio ? `<span>${esc(anio)}</span>` : ''}
        ${sector ? `<span>${esc(sector)}</span>` : ''}
      </div>
      <h3 class="proj-card-title">${esc(p.title)}</h3>
      <p class="proj-card-desc">${esc(p.desc)}</p>
      <div class="proj-card-foot">
        <span class="proj-card-arrow">${ARROW_SVG}</span>
        <span class="proj-card-label">Ver proyecto completo</span>
      </div>
    </div>`;
  return a;
}

let currentCat = 'all';

function render() {
  const wrap = document.querySelector('.proj-grid');
  if (!wrap) return;

  const items = PROJECTS.filter(p => currentCat === 'all' || p.cat === currentCat);
  wrap.innerHTML = '';

  if (!items.length) {
    wrap.innerHTML = '<p class="proj-empty">No hay proyectos en esta categoría todavía.</p>';
    return;
  }

  // Patrón: la 1ra de cada grupo de 4 es ancha (full width); el resto normales
  items.forEach((p, i) => {
    const card = (i % 4 === 0) ? buildWide(p) : buildCard(p);

    // Animación de entrada escalonada (fade + slide up).
    // Se limpia al terminar para no estorbar al hover.
    card.classList.add('proj-card--enter');
    card.style.animationDelay = (i * 55) + 'ms';
    card.addEventListener('animationend', () => {
      card.classList.remove('proj-card--enter');
      card.style.animationDelay = '';
    }, { once: true });

    wrap.appendChild(card);
  });
}

document.addEventListener('DOMContentLoaded', () => {
  paintCounts();
  render();

  document.querySelectorAll('.proj-chip').forEach(btn => {
    btn.addEventListener('click', () => {
      if (btn.dataset.filter === currentCat) return; // ya está activo
      document.querySelectorAll('.proj-chip').forEach(b => b.classList.remove('is-active'));
      btn.classList.add('is-active');
      currentCat = btn.dataset.filter;
      render();
    });
  });

  // Sincroniza desde la BD (admin/pages/proyectos) si existe, conservando
  // los proyectos de ejemplo cuyo id no esté en la BD.
  (async function () {
    try {
      const db = await fetch('/api/contenido.php?clave=proyectos', { cache: 'no-store' }).then(r => r.json());
      if (!Array.isArray(db) || !db.length) return;
      const byId = Object.fromEntries(PROJECTS.map(p => [p.id, p]));
      PROJECTS = db.map(row => {
        const local = byId[row.id] || {};
        return {
          id:     row.id     || local.id     || '',
          title:  row.title  || local.title  || '',
          cat:    row.cat    || local.cat    || '',
          badge:  row.badge  || local.badge  || '',
          sector: row.sector || local.sector || '',
          anio:   row.anio   || local.anio   || '',
          img:    resolveImg(row.img || local.img || '') || '../../assets/images/general/img1.webp',
          alt:    row.alt    || local.alt    || '',
          desc:   row.desc   || local.desc   || '',
        };
      });
      paintCounts();
      render();
    } catch (e) { /* fallo silencioso — queda el listado de ejemplo */ }
  })();
});
