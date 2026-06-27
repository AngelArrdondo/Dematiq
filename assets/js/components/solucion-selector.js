// Selector de soluciones — compartido por todas las páginas de soluciones.
// Actualiza título, descripción y el carrusel de 2 imágenes (con autoplay) desde los data-*.
(function () {
  const options = document.querySelectorAll('.svc-option');
  const titleEl = document.getElementById('svcTitle');
  const descEl  = document.getElementById('svcDesc');
  const img1    = document.getElementById('svcImg');
  const img2    = document.getElementById('svcImg2');
  const content = document.querySelector('.svc-content');
  const carousel = document.querySelector('.carousel2');
  if (!options.length || !titleEl || !descEl) return;

  // ── Lightbox ──
  const lb = document.createElement('div');
  lb.id = 'svc-lightbox';
  lb.innerHTML = `
    <div class="lb-backdrop"></div>
    <div class="lb-frame">
      <button class="lb-close" aria-label="Cerrar">&#10005;</button>
      <img class="lb-img" src="" alt="">
    </div>`;
  lb.style.cssText = 'position:fixed;inset:0;z-index:9999;display:none;align-items:center;justify-content:center;';
  document.body.appendChild(lb);

  const lbBackdrop = lb.querySelector('.lb-backdrop');
  const lbImg      = lb.querySelector('.lb-img');
  const lbClose    = lb.querySelector('.lb-close');

  const lbStyle = document.createElement('style');
  lbStyle.textContent = `
    #svc-lightbox { display:none; }
    #svc-lightbox.open { display:flex; }
    #svc-lightbox .lb-backdrop {
      position:absolute; inset:0;
      background:rgba(0,0,0,.85); backdrop-filter:blur(4px);
    }
    #svc-lightbox .lb-frame {
      position:relative; z-index:1;
      max-width:90vw; max-height:90vh;
      display:flex; align-items:center; justify-content:center;
      animation:lb-in .18s ease;
    }
    @keyframes lb-in { from{opacity:0;transform:scale(.93)} to{opacity:1;transform:scale(1)} }
    #svc-lightbox .lb-img {
      max-width:88vw; max-height:88vh;
      object-fit:contain; border-radius:10px;
      box-shadow:0 20px 60px rgba(0,0,0,.6);
      display:block;
    }
    #svc-lightbox .lb-close {
      position:absolute; top:-14px; right:-14px;
      width:36px; height:36px; border-radius:50%;
      background:#fff; border:none; cursor:pointer;
      font-size:14px; font-weight:700; color:#222;
      display:flex; align-items:center; justify-content:center;
      box-shadow:0 2px 8px rgba(0,0,0,.3);
      transition:background .15s, transform .15s;
    }
    #svc-lightbox .lb-close:hover { background:#f44; color:#fff; transform:scale(1.1); }
    .c2-track img { cursor:zoom-in; transition:opacity .15s; }
    .c2-track img:hover { opacity:.88; }
    .c2-zoom-hint {
      position:absolute; inset:0; display:flex;
      align-items:center; justify-content:center;
      pointer-events:none; opacity:0; transition:opacity .2s;
    }
    .c2-zoom-hint svg { width:42px; height:42px; filter:drop-shadow(0 2px 6px rgba(0,0,0,.5)); }
    .carousel2:hover .c2-zoom-hint { opacity:1; }
  `;
  document.head.appendChild(lbStyle);

  function openLightbox(src, alt) {
    lbImg.src = src;
    lbImg.alt = alt || '';
    lb.classList.add('open');
    document.body.style.overflow = 'hidden';
  }
  function closeLightbox() {
    lb.classList.remove('open');
    document.body.style.overflow = '';
  }

  lbBackdrop.addEventListener('click', closeLightbox);
  lbClose.addEventListener('click', closeLightbox);
  document.addEventListener('keydown', e => { if (e.key === 'Escape') closeLightbox(); });

  // Añadir hint de zoom al carrusel y abrir lightbox al hacer click en imágenes
  if (carousel) {
    const hint = document.createElement('div');
    hint.className = 'c2-zoom-hint';
    hint.innerHTML = `<svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
      <line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/>
    </svg>`;
    carousel.style.position = 'relative';
    carousel.appendChild(hint);

    carousel.querySelector('.c2-track').addEventListener('click', e => {
      const target = e.target.closest('img');
      if (target && target.src && target.style.visibility !== 'hidden') {
        openLightbox(target.src, target.alt);
      }
    });
  }

  // ── Carrusel con autoplay ──
  let idx = 0;
  let timer = null;
  const DELAY = 3000;

  function slideTo(i) {
    if (!carousel) return;
    const track = carousel.querySelector('.c2-track');
    const count = track ? track.children.length : 0;
    if (!count) return;
    idx = (i + count) % count;
    carousel.dataset.idx = idx;
    track.style.transform = 'translateX(-' + (idx * 100) + '%)';
    carousel.querySelectorAll('.c2-dots span').forEach((d, k) => d.classList.toggle('active', k === idx));
  }
  function stopAuto() { if (timer) { clearInterval(timer); timer = null; } }
  function startAuto() {
    stopAuto();
    if (!carousel) return;
    const track = carousel.querySelector('.c2-track');
    if (track && track.children.length > 1) {
      timer = setInterval(() => slideTo(idx + 1), DELAY);
    }
  }
  if (carousel) {
    carousel.querySelectorAll('.c2-dots span').forEach((d, k) =>
      d.addEventListener('click', () => { slideTo(k); startAuto(); }));
    carousel.addEventListener('mouseenter', stopAuto);
    carousel.addEventListener('mouseleave', startAuto);
  }

  function safeImg(el, src, alt) {
    if (!el) return;
    el.alt = alt || '';
    el.onerror = function() { this.style.visibility = 'hidden'; this.onerror = null; };
    el.onload  = function() { this.style.visibility = ''; this.onerror = null; };
    if (src) el.src = src; else el.style.visibility = 'hidden';
  }

  function render(opt) {
    if (content) content.style.opacity = 0;
    setTimeout(() => {
      titleEl.textContent = opt.dataset.title || '';
      descEl.textContent  = opt.dataset.desc || '';
      safeImg(img1, opt.dataset.img || '', opt.dataset.title);
      safeImg(img2, opt.dataset.img2 || opt.dataset.img || '', opt.dataset.title);
      slideTo(0);
      if (content) {
        content.style.opacity = 1;
        content.classList.remove('svc-fade');
        void content.offsetWidth;
        content.classList.add('svc-fade');
      }
    }, 160);
  }

  options.forEach(opt => {
    opt.addEventListener('click', () => {
      options.forEach(o => o.classList.remove('active'));
      opt.classList.add('active');
      render(opt);
    });
  });

  const initial = document.querySelector('.svc-option.active') || options[0];
  if (initial) { initial.classList.add('active'); render(initial); }
  startAuto();
})();
