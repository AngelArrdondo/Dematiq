// Selector de soluciones — compartido por todas las páginas de soluciones.
// Lee título, imagen y descripción desde los data-* de cada opción
// y actualiza el contenido del lado izquierdo.
(function () {
  const options = document.querySelectorAll('.svc-option');
  const titleEl = document.getElementById('svcTitle');
  const imgEl   = document.getElementById('svcImg');
  const descEl  = document.getElementById('svcDesc');
  const content = document.querySelector('.svc-content');
  if (!options.length || !titleEl || !imgEl || !descEl) return;

  function render(opt) {
    imgEl.style.opacity = 0;
    setTimeout(() => {
      titleEl.textContent = opt.dataset.title || '';
      imgEl.src = opt.dataset.img || imgEl.src;
      imgEl.alt = opt.dataset.title || '';
      descEl.textContent = opt.dataset.desc || '';
      imgEl.style.opacity = 1;
      content.classList.remove('svc-fade');
      void content.offsetWidth;
      content.classList.add('svc-fade');
    }, 160);
  }

  options.forEach(opt => {
    opt.addEventListener('click', () => {
      options.forEach(o => o.classList.remove('active'));
      opt.classList.add('active');
      render(opt);
    });
  });

  // Estado inicial = opción activa (o la primera)
  const initial = document.querySelector('.svc-option.active') || options[0];
  if (initial) {
    initial.classList.add('active');
    render(initial);
  }
})();
