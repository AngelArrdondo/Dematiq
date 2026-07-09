/* ══════════════════════════════════════════════════
   PROYECTO (detalle) — v33 editorial técnica
   Arma la ficha según ?id= de la URL.
   Datos desde proyectos-data.js (window.DEMATIQ_PROJECTS).

   Esquinas rectas · reglas finas · datos en monoespaciada.
   Secuencia 01/02/03 para reto · solución · resultados.

   ⚠️ FRANJA DE MÉTRICAS desactivada por ahora (números de ejemplo).
      Para reactivarla cuando tengas cifras reales:
      pon  SHOW_METRICS = true  aquí abajo. El bloque ya está listo.
   ══════════════════════════════════════════════════ */

(async function () {
  const SHOW_METRICS = false;   // ← cambia a true cuando las métricas sean reales

  let PROJECTS = window.DEMATIQ_PROJECTS || [];
  const root = document.getElementById('pd-root');
  if (!root) return;

  const esc = s => String(s ?? '').replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;');

  const id = new URLSearchParams(window.location.search).get('id');

  // Sincroniza desde la BD (admin/pages/proyectos) si existe. El admin ya
  // tiene campos para solucion/resultados/metricas — si el proyecto guardado
  // no los trae (aún no editados), se conserva el contenido de ejemplo local.
  try {
    const db = await fetch('/api/contenido.php?clave=proyectos', { cache: 'no-store' }).then(r => r.json());
    if (Array.isArray(db) && db.length) {
      const byId = Object.fromEntries(PROJECTS.map(x => [x.id, x]));
      PROJECTS = db.map(row => {
        const local = byId[row.id] || {};
        return {
          ...local,
          id:         row.id         || local.id         || '',
          title:      row.title      || local.title      || '',
          cat:        row.cat        || local.cat        || '',
          badge:      row.badge      || local.badge      || '',
          sector:     row.sector     || local.sector     || '',
          servicios:  row.servicios  || local.servicios  || '',
          ubicacion:  row.ubicacion  || local.ubicacion  || '',
          anio:       row.anio       || local.anio       || '',
          img:        row.img        || local.img        || '',
          alt:        row.alt        || local.alt        || '',
          solucion:   row.solucion   || local.solucion   || '',
          resultados: row.resultados || local.resultados || '',
          metricas:   (Array.isArray(row.metricas) && row.metricas.length) ? row.metricas : (local.metricas || []),
        };
      });
    }
  } catch (e) { /* fallo silencioso — queda el proyecto de ejemplo local */ }

  const p = PROJECTS.find(x => x.id === id);

  if (!p) {
    root.innerHTML = `
      <div class="pd-missing">
        <h1>Proyecto no encontrado</h1>
        <p>El proyecto que buscas no existe o fue movido.</p>
        <a class="pd-cta-btn" href="soluciones.html">Ver todos los proyectos</a>
      </div>`;
    return;
  }

  document.title = `${p.title} | DEMATIQ`;

  // ── Franja de métricas (opcional) ──
  let metricsHtml = '';
  if (SHOW_METRICS && Array.isArray(p.metricas) && p.metricas.length) {
    const cells = p.metricas.slice(0, 3).map((m, i) => `
      <div class="pd-metric${i === 0 ? ' pd-metric--accent' : ''}">
        <span class="pd-metric-v">${esc(m.v)}</span>
        <span class="pd-metric-l">${esc(m.l)}</span>
      </div>`).join('');
    metricsHtml = `
      <section class="pd-metrics">
        <p class="pd-eyebrow pd-eyebrow--teal">// Resultados en cifras</p>
        <div class="pd-metrics-row">${cells}</div>
      </section>`;
  }

  // ── (Sección de frase destacada eliminada) ──
  const quote = '';

  // ── Sobre el proyecto (split sticky, estilo Apple/Tesla sobre blanco) ──
  //    Texto a la izquierda (baja con scroll) · imagen fija a la derecha (sticky).
  //    Habla solo del proyecto realizado. Datos reales en lista con acentos azules.
  //    👉 p.solucion y p.resultados son CONTENIDO DE EJEMPLO: reemplázalos con la
  //       descripción real de cada proyecto cuando el cliente la envíe.
  const dataRows = [
    { k: 'Tipo de máquina', v: p.badge },
    // sector solo si aporta algo distinto al tipo de máquina (evita repetir)
    { k: 'Sector',    v: (p.sector && p.sector !== p.badge) ? p.sector : '' },
    { k: 'Servicios', v: p.servicios, wide: true },
    { k: 'Ubicación', v: p.ubicacion },
    { k: 'Año',       v: p.anio }
  ].filter(r => r.v).map(r => `
    <div class="pd-about-fact${r.wide ? ' pd-about-fact--wide' : ''}">
      <span class="pd-about-fact-k">${r.k}</span>
      <span class="pd-about-fact-v">${esc(r.v)}</span>
    </div>`).join('');

  const extraParrafo = p.resultados
    ? `<p class="pd-about-text">${esc(p.resultados)}</p>` : '';

  const narrative = p.solucion ? `
    <section class="pd-about">
      <div class="pd-about-inner">
        <div class="pd-about-text-col">
          <span class="pd-about-eyebrow">Sobre el proyecto</span>
          <h2 class="pd-about-title">${esc(p.title)}</h2>
          <p class="pd-about-lead">${esc(p.solucion)}</p>
          ${extraParrafo}
          <div class="pd-about-data">${dataRows}</div>
        </div>
        <div class="pd-about-media-col">
          <div class="pd-about-media-sticky">
            <figure class="pd-about-figure">
              <img src="${esc(p.img)}" alt="${esc(p.alt)}" loading="lazy">
            </figure>
          </div>
        </div>
      </div>
    </section>` : '';

  // ── Otros proyectos (hasta 3) ──
  const related = PROJECTS.filter(x => x.id !== p.id).slice(0, 3).map(r => `
    <a class="pd-rel-card" href="proyecto.html?id=${encodeURIComponent(r.id)}">
      <div class="pd-rel-media">
        <span class="pd-rel-badge">${esc(r.badge)}</span>
        <img src="${esc(r.img)}" alt="${esc(r.alt)}" loading="lazy">
      </div>
      <div class="pd-rel-body"><h3>${esc(r.title)}</h3></div>
    </a>`).join('');

  root.innerHTML = `
    <section class="pd-hero">
      <span class="pd-tick pd-tick--tl" aria-hidden="true"></span>
      <span class="pd-tick pd-tick--tr" aria-hidden="true"></span>
      <span class="pd-tick pd-tick--bl" aria-hidden="true"></span>
      <span class="pd-tick pd-tick--br" aria-hidden="true"></span>
      <img class="pd-hero-bg" src="${esc(p.img)}" alt="${esc(p.alt)}">
      <div class="pd-hero-inner">
        <h1>${esc(p.title)}</h1>
      </div>
    </section>

    <div class="pd-topbar">
      <a class="pd-back" href="soluciones.html">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        Volver a proyectos
      </a>
      <div class="pd-topbar-specs">
        <div class="pd-tb-spec"><span class="pd-tb-k">Tipo de máquina</span><span class="pd-tb-v">${esc(p.badge)}</span></div>
        <div class="pd-tb-spec"><span class="pd-tb-k">Fecha</span><span class="pd-tb-v">${esc(p.anio) || '—'}</span></div>
      </div>
    </div>

    ${metricsHtml}

    ${narrative}

    ${quote}

    <section class="pd-cta">
      <div class="pd-cta-text">
        <h2>¿Tienes un proyecto similar?</h2>
        <p>Cuéntanos tu proceso y diseñamos la solución a tu medida.</p>
      </div>
      <a class="pd-cta-btn" href="Contacto.html">Contáctanos →</a>
    </section>

    ${related ? `
    <section class="pd-related">
      <div class="pd-related-head">
        <span class="pd-mono pd-mono--muted">Otros proyectos</span>
        <span class="pd-related-line"></span>
      </div>
      <div class="pd-related-grid">${related}</div>
    </section>` : ''}
  `;
})();
