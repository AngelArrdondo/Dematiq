/**
 * Stage Carousel — Dematiq v14
 */
(function () {
  'use strict';

  const PROJECTS = [
    {
      img: 'assets/images/products/en1.webp',
      nombre: 'Línea de ensamble',
      desc: 'Ensamble de alta precisión con verificación integrada en cada estación.'
    },
    {
      img: 'assets/images/general/fuga.webp',
      nombre: 'Prueba de hermeticidad',
      desc: 'Detección de fugas con sensores de alta sensibilidad y trazabilidad por pieza.'
    },
    {
      img: 'assets/images/general/celdas.webp',
      nombre: 'Celda robótica',
      desc: 'Celda robótica integrada en línea de manufactura flexible.'
    },
    {
      img: 'assets/images/general/inspeccion.webp',
      nombre: 'Inspección por visión',
      desc: 'Inspección automatizada con visión artificial y reporte en tiempo real.'
    },
  ];

  let TOTAL = PROJECTS.length;

  function mod(n, m) { return ((n % m) + m) % m; }

  function buildCard(data) {
    const card = document.createElement('div');
    card.className = 'stage-card';
    if (data.href) card.dataset.href = data.href;
    card.innerHTML = `
      <img class="stage-card__img" src="${data.img}" alt="${data.nombre}" loading="lazy">
      <div class="stage-card__gradient"></div>
      <span class="stage-card__name">${data.nombre}</span>
      <div class="stage-card__info">
        <p class="stage-card__info-nombre">${data.nombre}</p>
        <p class="stage-card__info-desc">${data.desc}</p>
      </div>
      <div class="stage-card__side-overlay">
        <div class="stage-card__side-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <polyline points="12 8 16 12 12 16"></polyline>
            <line x1="8" y1="12" x2="16" y2="12"></line>
          </svg>
        </div>
      </div>`;
    return card;
  }

  function getLayout(track) {
    const tw = track.offsetWidth;
    const isMobile = tw < 560;
    const centerW = isMobile ? tw * 0.84 : Math.min(tw * 0.50, 500);
    const centerH = centerW * (10 / 16);
    const sideScale = isMobile ? 0 : 0.80;
    const sideW = centerW * sideScale;
    const sideH = centerH * sideScale;
    const gap = 16;
    const sideOffset = centerW / 2 + sideW / 2 + gap;

    return {
      '-2': { x: -(sideOffset + sideW + gap), scale: 0.6,      opacity: 0,                z: 0, w: sideW,   h: sideH   },
      '-1': { x: -sideOffset,                 scale: sideScale, opacity: isMobile ? 0 : 1, z: 1, w: sideW,   h: sideH   },
       '0': { x: 0,                            scale: 1,         opacity: 1,                z: 3, w: centerW, h: centerH },
       '1': { x: sideOffset,                   scale: sideScale, opacity: isMobile ? 0 : 1, z: 1, w: sideW,   h: sideH   },
       '2': { x: sideOffset + sideW + gap,     scale: 0.6,      opacity: 0,                z: 0, w: sideW,   h: sideH   },
    };
  }

  function applyPositions(cards, activeIdx, track, animate) {
    const layout = getLayout(track);
    cards.forEach((card, i) => {
      const rel = mod(i - activeIdx, TOTAL);
      let pos;
      if      (rel === 0)         pos =  0;
      else if (rel === 1)         pos =  1;
      else if (rel === 2)         pos =  2;
      else if (rel === TOTAL - 1) pos = -1;
      else if (rel === TOTAL - 2) pos = -2;
      else                        pos =  rel < TOTAL / 2 ? 2 : -2;

      const cfg = layout[String(pos)];
      if (!animate) card.style.transition = 'none';
      card.style.setProperty('--sc-x', cfg.x + 'px');
      card.style.setProperty('--sc-s', cfg.scale);
      card.style.setProperty('--sc-o', cfg.opacity);
      card.style.setProperty('--sc-z', cfg.z);
      card.style.setProperty('--sc-w', cfg.w + 'px');
      card.style.setProperty('--sc-h', cfg.h + 'px');

      card.classList.remove('is-center', 'is-side', 'is-hidden');
      if      (pos === 0)               { card.classList.add('is-center'); card.tabIndex = -1; }
      else if (pos === -1 || pos === 1) { card.classList.add('is-side');   card.tabIndex =  0; }
      else                              { card.classList.add('is-hidden'); card.tabIndex = -1; }

      if (!animate) requestAnimationFrame(() => { card.style.transition = ''; });
    });
  }

  function updateDots(dots, activeIdx) {
    dots.forEach((d, i) => d.classList.toggle('is-active', i === activeIdx));
  }

  function setTrackHeight(track) {
    const tw = track.offsetWidth;
    const isMobile = tw < 560;
    const centerW = isMobile ? tw * 0.84 : Math.min(tw * 0.50, 500);
    const centerH = centerW * (10 / 16);
    track.style.height = Math.round(centerH + 12) + 'px';
  }

  async function loadProjects() {
    // 1) Preferir los DESTACADOS del arreglo compartido DEMATIQ_PROJECTS.
    //    Así, marcar destacado:true en los datos basta para que salga aquí.
    if (Array.isArray(window.DEMATIQ_PROJECTS)) {
      const dest = window.DEMATIQ_PROJECTS.filter(p => p.destacado);
      if (dest.length) {
        return dest.map(p => ({
          img: (p.img || '').replace(/^(\.\.\/)+/, ''),  // index.html está en la raíz del sitio
          nombre: p.title,
          desc: p.desc,
          href: 'pages/corporativo/proyecto.html?id=' + encodeURIComponent(p.id)
        }));
      }
    }
    // 2) Respaldo: API de contenido
    try {
      const data = await fetch('/api/contenido.php?clave=proyectos').then(r => r.json());
      if (Array.isArray(data) && data.length) return data;
    } catch (_) {}
    // 3) Respaldo final: lista interna
    return PROJECTS;
  }

  async function init() {
    const track   = document.getElementById('stageTrack');
    const dotsEl  = document.getElementById('stageDots');
    const btnPrev = document.getElementById('stagePrev');
    const btnNext = document.getElementById('stageNext');
    if (!track) return;

    const projectList = await loadProjects();
    TOTAL = projectList.length;

    const cards = projectList.map(p => { const c = buildCard(p); track.appendChild(c); return c; });
    const dots  = projectList.map((_, i) => {
      const d = document.createElement('button');
      d.className = 'stage-dot';
      d.setAttribute('aria-label', `Proyecto ${i + 1}`);
      dotsEl.appendChild(d);
      return d;
    });

    let activeIdx = 0;
    let busy = false;

    function goTo(idx) {
      if (busy) return;
      busy = true;
      activeIdx = mod(idx, TOTAL);
      applyPositions(cards, activeIdx, track, true);
      updateDots(dots, activeIdx);
      setTimeout(() => { busy = false; }, 560);
    }

    btnPrev.addEventListener('click', () => goTo(activeIdx - 1));
    btnNext.addEventListener('click', () => goTo(activeIdx + 1));
    dots.forEach((d, i) => d.addEventListener('click', () => goTo(i)));

    cards.forEach((card, i) => {
      card.addEventListener('click', () => {
        if (card.classList.contains('is-side')) { goTo(i); return; }
        if (card.classList.contains('is-center') && card.dataset.href) {
          window.location.href = card.dataset.href;
        }
      });
      card.addEventListener('keydown', e => {
        if ((e.key === 'Enter' || e.key === ' ') && card.classList.contains('is-side')) { e.preventDefault(); goTo(i); }
      });
    });

    document.addEventListener('keydown', e => {
      const sec = document.getElementById('proyectos');
      if (!sec) return;
      const r = sec.getBoundingClientRect();
      if (r.top > window.innerHeight || r.bottom < 0) return;
      if (e.key === 'ArrowLeft')  goTo(activeIdx - 1);
      if (e.key === 'ArrowRight') goTo(activeIdx + 1);
    });

    let touchX = null;
    track.addEventListener('touchstart', e => { touchX = e.touches[0].clientX; }, { passive: true });
    track.addEventListener('touchend',   e => {
      if (touchX === null) return;
      const dx = e.changedTouches[0].clientX - touchX;
      if (Math.abs(dx) > 40) goTo(activeIdx + (dx < 0 ? 1 : -1));
      touchX = null;
    }, { passive: true });

    let resizeTimer;
    window.addEventListener('resize', () => {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(() => {
        setTrackHeight(track);
        applyPositions(cards, activeIdx, track, false);
      }, 120);
    });

    // Autoplay — avanza cada 4s, pausa al hover o touch
    const AUTOPLAY_MS = 4000;
    let autoplayTimer = setInterval(() => goTo(activeIdx + 1), AUTOPLAY_MS);

    function resetAutoplay() {
      clearInterval(autoplayTimer);
      autoplayTimer = setInterval(() => goTo(activeIdx + 1), AUTOPLAY_MS);
    }

    // Pausa SOLO cuando el cursor está sobre el card central
    track.addEventListener('mouseover', e => {
      const card = e.target.closest('.stage-card');
      if (card && card.classList.contains('is-center')) clearInterval(autoplayTimer);
    });
    track.addEventListener('mouseout', e => {
      const card = e.target.closest('.stage-card');
      if (card && card.classList.contains('is-center')) resetAutoplay();
    });

    // En táctil, pausa brevemente y reanuda
    track.addEventListener('touchstart',  () => clearInterval(autoplayTimer), { passive: true });
    track.addEventListener('touchend',    () => { resetAutoplay(); }, { passive: true });

    // Resetear timer al navegar manualmente
    const origGoTo = goTo;
    btnPrev.addEventListener('click', resetAutoplay);
    btnNext.addEventListener('click', resetAutoplay);
    dots.forEach(d => d.addEventListener('click', resetAutoplay));

    setTrackHeight(track);
    applyPositions(cards, activeIdx, track, false);
    updateDots(dots, activeIdx);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
