/* Barra "Servicios / Ensamble / Torque / Fuga / ..." debajo del H1 en las
   páginas de Servicios, Ensamble y Máquinas. Los nombres de las páginas de
   Máquinas se leen de la clave "maquinasPaginas" — la misma que edita el
   admin en Máquinas > "Renombrar/reordenar" — para que una sola edición se
   refleje aquí en las 9 páginas sin tocar cada archivo a mano. */
(function () {
  const esc = s => String(s ?? '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
  const fingerprint = pages => pages.map(p => p.key + '|' + p.label + '|' + p.url).join('~');

  const FALLBACK_PAGES = [
    { key: 'servicios',  label: 'Servicios',        url: '/pages/servicios/servicios.html' },
    { key: 'maqcontrol', label: 'Torque',           url: '/pages/maquinas/maqcontrol.html' },
    { key: 'ensamble',   label: 'Ensamble',         url: '/pages/ensamble/ensamble.html' },
    { key: 'maqprob',    label: 'Fuga',             url: '/pages/maquinas/maqprob.html' },
    { key: 'maqinspe',   label: 'Inspección',       url: '/pages/maquinas/maqinspe.html' },
    { key: 'maclim',     label: 'Limpieza',         url: '/pages/maquinas/maclim.html' },
    { key: 'maqmar',     label: 'Marcado',          url: '/pages/maquinas/maqmar.html' },
    { key: 'macrobot',   label: 'Celdas robóticas', url: '/pages/maquinas/macrobot.html' },
    { key: 'maqindus',   label: 'Manufactura',      url: '/pages/manufactura/maqindus.html' },
  ];

  function buildPages(saved) {
    const machineDefaults = FALLBACK_PAGES.slice(1);
    if (!Array.isArray(saved) || !saved.length) return FALLBACK_PAGES;
    const byKey = Object.fromEntries(machineDefaults.map(p => [p.key, p]));
    const seen  = new Set();
    const machines = [];
    saved.forEach(s => {
      const base = s && byKey[s.key];
      if (base && !seen.has(s.key)) {
        machines.push({ key: base.key, url: base.url, label: (s.label || base.label).trim() || base.label });
        seen.add(s.key);
      }
    });
    machineDefaults.forEach(p => { if (!seen.has(p.key)) machines.push(p); });
    return [FALLBACK_PAGES[0], ...machines];
  }

  function render(pages) {
    const wrap = document.getElementById('svcQuicknavInner');
    if (!wrap) return;
    const current = location.pathname;
    wrap.innerHTML = pages.map(p =>
      `<a class="svc-chip${p.url === current ? ' is-current' : ''}" href="${esc(p.url)}">${esc(p.label)}</a>`
    ).join('');
  }

  // El HTML de cada página ya trae los chips por defecto (mismo orden/
  // etiquetas que FALLBACK_PAGES), así que no se repinta aquí — solo se
  // vuelve a pintar si lo guardado en el admin difiere de eso, para no
  // sustituir el DOM (y parpadear) en cada carga sin necesidad.
  fetch('/api/contenido.php?clave=maquinasPaginas', { cache: 'no-store' })
    .then(r => r.json())
    .then(saved => {
      const pages = buildPages(saved);
      if (fingerprint(pages) !== fingerprint(FALLBACK_PAGES)) render(pages);
    })
    .catch(() => {});
})();
