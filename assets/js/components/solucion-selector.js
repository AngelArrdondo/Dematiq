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
