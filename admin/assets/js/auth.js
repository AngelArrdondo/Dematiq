'use strict';

// ── Constants ──────────────────────────────────────────────────────────
const CONTENT_KEY = 'dematiq_content';

function _csrfToken() {
  const m = document.cookie.match(/dematiq_csrf=([^;]+)/);
  return m ? m[1] : '';
}

// ── Default content (mirrors the site's current hardcoded values) ──────
const DEFAULT_CONTENT = {
  nosotros: {
    hero: {
      tag:      'Conócenos',
      h1:       'Sobre Nosotros',
      subtitle: 'Empresa mexicana especializada en automatización y ensamble industrial con más de 10 años de experiencia.'
    },
    p1:      'En DEMATIQ somos una empresa especializada en soluciones tecnológicas e industriales enfocadas en optimizar los procesos de manufactura, automatización y desarrollo sostenible.',
    p2:      'Trabajamos de la mano con nuestros clientes y socios estratégicos para ofrecer innovación, eficiencia y calidad en cada uno de nuestros proyectos.',
    mision:  'Brindar soluciones integrales en automatización y desarrollo tecnológico que impulsen la productividad y competitividad de nuestros clientes.',
    vision:  'Ser líderes en innovación tecnológica y un referente en la transformación industrial a nivel nacional e internacional.',
    valores: 'Compromiso, innovación, calidad, trabajo en equipo y responsabilidad social empresarial.'
  },
  contacto: {
    email:         'ventas@dematiq.com.mx',
    whatsapp:      '+52 (442) 721-4891',
    whatsappNum:   '524427214891',
    horario:       'Lun – Vie · 8:30 – 18:00',
    direccion:     'Calle Jardín de la Alabanza 2049, Jardines del Sol, Querétaro, Qro., México',
    mapCoords:     '20.621222,-100.470500',
    social: {
      facebook:  '#',
      instagram: '#',
      linkedin:  '#',
      youtube:   '#'
    }
  },
  partners: [
    { nombre: 'Danfoss',    logo: 'assets/images/partners/Danfoss.webp',  url: 'https://www.danfoss.com/es-mx' },
    { nombre: 'Eaton',      logo: 'assets/images/partners/Eaton.webp',    url: 'https://www.eaton.com/mx/es-mx.html' },
    { nombre: 'Metecno',    logo: 'assets/images/partners/METECNO.webp',  url: 'https://metecnomexico.com' },
    { nombre: 'RPK México', logo: 'assets/images/partners/RPK.webp',      url: 'https://rpk-global.com/es/rpk-mexico' },
    { nombre: 'TT Green',   logo: 'assets/images/partners/TTGREEN.webp',  url: 'https://www.proveedores-greenmetals.com' },
    { nombre: 'Calidra',    logo: 'assets/images/partners/CALIDRA.webp',  url: 'https://www.calidra.com' },
    { nombre: 'Ampacet',    logo: 'assets/images/partners/AMPACET.webp',  url: 'https://www.ampacet.com/spanish' }
  ],
  home: {
    hero: [
      { title: 'Bienvenido a DEMATIQ', subtitle: 'Soluciones inteligentes para la industria moderna',       image: 'assets/images/general/index.webp' },
      { title: 'Innovación',           subtitle: 'Experiencia en proyectos de Automatización y Ensamble',   image: 'assets/images/general/img2.webp' },
      { title: 'Confianza',            subtitle: 'Más de 10 años de Experiencia',                          image: 'assets/images/general/img3.webp' }
    ]
  },
  industrias: [
    { id: 'automotriz',       nombre: 'Automotriz',              descripcion: 'Colaboramos en proyectos de automatización, control de calidad y desarrollo de maquinaria para procesos de ensamblaje y pruebas.' },
    { id: 'farmaceutica',     nombre: 'Farmacéutica',            descripcion: 'Desarrollamos sistemas de control ambiental, monitoreo de producción y soluciones de trazabilidad para laboratorios y plantas farmacéuticas.' },
    { id: 'alimenticia',      nombre: 'Alimenticia',             descripcion: 'Implementamos soluciones de automatización y control de calidad para la producción de alimentos y bebidas.' },
    { id: 'manufactura',      nombre: 'Manufactura',             descripcion: 'Colaboramos en la optimización de procesos, implementación de sistemas de control y desarrollo de maquinaria especializada.' },
    { id: 'electronica',      nombre: 'Electrónica y Eléctrica', descripcion: 'Implementamos soluciones de automatización y control de calidad para la producción de dispositivos electrónicos.' },
    { id: 'electrodomesticos',nombre: 'Electrodomésticos',       descripcion: 'Desarrollamos soluciones de automatización y control de calidad para la producción de electrodomésticos.' },
    { id: 'alimentos',        nombre: 'Alimentos y Bebidas',     descripcion: 'Soluciones de envasado, etiquetado y control de procesos para líneas de producción de alimentos y bebidas.' },
    { id: 'aeroespacial',     nombre: 'Aeroespacial',            descripcion: 'Desarrollamos soluciones de automatización y control de calidad para la producción de componentes aeroespaciales.' }
  ],
  servicios: [
    { id: 'plc',           nombre: 'Programación de PLC',                    image: 'assets/images/general/img1.webp' },
    { id: 'hmi',           nombre: 'Programación de HMI, SCADA',             image: 'assets/images/general/img2.webp' },
    { id: 'vision',        nombre: 'Programación de Sistemas de Visión',      image: 'assets/images/general/img3.webp' },
    { id: 'servo',         nombre: 'Programación de Servomotores',            image: 'assets/images/general/img1.webp' },
    { id: 'diagramas',     nombre: 'Diseño de Diagramas Eléctricos',          image: 'assets/images/general/img2.webp' },
    { id: 'tableros',      nombre: 'Diseño de Tableros de Control',           image: 'assets/images/general/img3.webp' },
    { id: 'modernizacion', nombre: 'Modernización de Maquinaria',             image: 'assets/images/general/img1.webp' },
    { id: 'instalaciones', nombre: 'Servicio de Instalaciones Eléctricas',    image: 'assets/images/general/img2.webp' },
    { id: 'ingenieria',    nombre: 'Ingeniería Básica y de Detalle',          image: 'assets/images/general/img3.webp' },
    { id: 'variadores',    nombre: 'Programación de Variadores de Frecuencia',image: 'assets/images/general/img1.webp' }
  ],
  proyectos: [
    { nombre: 'Proyecto 1',  desc: 'Sistema de ensamble automatizado.',                                                                                                      img: 'assets/images/general/cart.webp',        href: 'pages/ensamble/ensamble.html' },
    { nombre: 'Proyecto 2',  desc: 'Línea de ensamble de alta precisión con verificación integrada en cada estación.',                                                       img: 'assets/images/products/en1.webp',        href: 'pages/ensamble/ensamble.html' },
    { nombre: 'Proyecto 3',  desc: 'Máquinas de control de torque.',                                                                                                         img: 'assets/images/general/t1.webp',          href: 'pages/maquinas/maqcontrol.html' },
    { nombre: 'Proyecto 4',  desc: 'Equipo de prueba de hermeticidad con sensores de alta sensibilidad para detección de fugas en componentes automotrices.',                img: 'assets/images/general/fuga.webp',        href: 'pages/maquinas/maqprob.html' },
    { nombre: 'Proyecto 5',  desc: 'Inspección automatizada con visión artificial.',                                                                                         img: 'assets/images/general/inspeccion.webp',  href: 'pages/maquinas/maqinspe.html' },
    { nombre: 'Proyecto 6',  desc: 'Máquina de lavado industrial para piezas de producción en serie, con secado por aire y control de temperatura del fluido.',              img: 'assets/images/general/limpieza.webp',    href: 'pages/maquinas/maclim.html' },
    { nombre: 'Proyecto 7',  desc: 'Marcado por micropercusión.',                                                                                                            img: 'assets/images/general/micro.webp',       href: 'pages/maquinas/maqmar.html' },
    { nombre: 'Proyecto 8',  desc: 'Celda robótica integrada en línea de manufactura flexible.',                                                                             img: 'assets/images/general/celdas.webp',      href: 'pages/maquinas/macrobot.html' },
    { nombre: 'Proyecto 9',  desc: 'Maquinado CNC de precisión.',                                                                                                           img: 'assets/images/products/maq.webp',        href: 'pages/manufactura/maqindus.html' },
    { nombre: 'Proyecto 10', desc: 'Equipo de manejo y prueba de componentes electrónicos en ambiente controlado.',                                                          img: 'assets/images/general/semi.webp',        href: 'pages/corporativo/soluciones.html' }
  ]
};

// ── Content Manager ────────────────────────────────────────────────────
const CM = {
  _all() {
    try { return JSON.parse(localStorage.getItem(CONTENT_KEY) || '{}'); }
    catch { return {}; }
  },

  get(section) {
    // Use server-injected DB data if available
    if (window.__DB_CONTENT && window.__DB_CONTENT[section] !== undefined) {
      return window.__DB_CONTENT[section];
    }
    const saved = this._all()[section];
    const def   = DEFAULT_CONTENT[section];
    if (!saved) return def;
    if (Array.isArray(def)) return saved;
    return Object.assign({}, def, saved,
      saved.social  ? { social:  Object.assign({}, def.social,  saved.social) }  : {},
      saved.hero    ? { hero:    Object.assign({}, def.hero,    saved.hero)   }  : {}
    );
  },

  set(section, data) {
    // Save to localStorage as cache
    const all = this._all();
    all[section] = data;
    localStorage.setItem(CONTENT_KEY, JSON.stringify(all));
    // Save to DB
    if (window.__DB_CONTENT !== undefined) {
      window.__DB_CONTENT[section] = data;
      fetch('/admin/api/contenido.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': _csrfToken() },
        body: JSON.stringify({ clave: section, valor: data })
      }).catch(() => {});
    }
  },

  exportAll() {
    const blob = new Blob([localStorage.getItem(CONTENT_KEY) || '{}'], { type: 'application/json' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'dematiq-content.json';
    a.click();
  },

  importAll(file) {
    return new Promise((resolve, reject) => {
      const r = new FileReader();
      r.onload = e => {
        try { const d = JSON.parse(e.target.result); localStorage.setItem(CONTENT_KEY, JSON.stringify(d)); resolve(); }
        catch { reject(new Error('JSON inválido')); }
      };
      r.readAsText(file);
    });
  }
};

// ── Toast ──────────────────────────────────────────────────────────────
function showToast(msg, type = 'success') {
  let t = document.getElementById('admin-toast');
  if (!t) { t = document.createElement('div'); t.id = 'admin-toast'; t.className = 'admin-toast'; document.body.appendChild(t); }
  const icon = type === 'success'
    ? '<polyline points="20,6 9,17 4,12"/>'
    : '<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>';
  t.className = `admin-toast ${type}`;
  t.innerHTML = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px">${icon}</svg>${msg}`;
  requestAnimationFrame(() => t.classList.add('show'));
  setTimeout(() => t.classList.remove('show'), 3200);
}

// ── Logout ─────────────────────────────────────────────────────────────
function adminLogout(e, loginPath) {
  if (e) e.preventDefault();
  AdminAuth.logout();
  window.location.href = loginPath || '../../pages/corporativo/login.php';
}

// ── Sidebar component ──────────────────────────────────────────────────
const AdminSidebar = {
  icon(d) {
    return `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">${d}</svg>`;
  },

  ICONS: {
    home:     '<path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9,22 9,12 15,12 15,22"/>',
    monitor:  '<rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/>',
    users:    '<path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>',
    layers:   '<polygon points="12,2 2,7 12,12 22,7"/><polyline points="2,17 12,22 22,17"/><polyline points="2,12 12,17 22,12"/>',
    phone:    '<path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-5.99-5.99 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 15.8 15.8 0 002.81.7A2 2 0 0122 16.92z"/>',
    star:     '<polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26 12,2"/>',
    tool:     '<path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/>',
    eye:      '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>',
    logout:   '<path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16,17 21,12 16,7"/><line x1="21" y1="12" x2="9" y2="12"/>',
    projects: '<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>'
  },

  init(activePage, bp, rp) {
    const sidebar = document.querySelector('.admin-sidebar');
    if (!sidebar) return;

    const nav = [
      { href: `${bp}dashboard.php`,         icon: 'home',     text: 'Dashboard',  key: 'dashboard' },
      { href: `${bp}pages/inicio.php`,      icon: 'monitor',  text: 'Inicio',     key: 'inicio' },
      { href: `${bp}pages/nosotros.php`,    icon: 'users',    text: 'Nosotros',   key: 'nosotros' },
      { href: `${bp}pages/industrias.php`,  icon: 'layers',   text: 'Industrias', key: 'industrias' },
      { href: `${bp}pages/contacto.php`,    icon: 'phone',    text: 'Contacto',   key: 'contacto' },
      { href: `${bp}pages/partners.php`,    icon: 'star',     text: 'Socios',     key: 'partners' },
      { href: `${bp}pages/servicios.php`,   icon: 'tool',     text: 'Servicios',  key: 'servicios' },
      { href: `${bp}pages/proyectos.php`,   icon: 'projects', text: 'Proyectos',  key: 'proyectos' }
    ];

    const navHTML = nav.map(n => {
      const cls = activePage === n.key ? ' class="active"' : '';
      return `<a href="${n.href}"${cls} title="${n.text}">${this.icon(this.ICONS[n.icon])}<span>${n.text}</span></a>`;
    }).join('');

    sidebar.innerHTML = `
      <div class="admin-logo">
        <img src="${rp}assets/images/logos/logo1.webp" class="admin-logo-full" alt="DEMATIQ">
        <button class="sidebar-collapse-btn" id="sidebar-collapse" title="Colapsar menú" aria-label="Colapsar menú">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
        </button>
      </div>
      <nav class="admin-nav">
        <div class="admin-nav-section"><span>Páginas</span></div>
        ${navHTML}
        <div class="admin-nav-section" style="margin-top:10px"><span>Sistema</span></div>
        <a href="${rp}index.html" target="_blank" title="Ver sitio">${this.icon(this.ICONS.eye)}<span>Ver sitio</span></a>
        <a href="${rp}admin/logout.php" title="Cerrar sesión">${this.icon(this.ICONS.logout)}<span>Cerrar sesión</span></a>
      </nav>
      <div class="admin-sidebar-footer"><span>DEMATIQ Admin v1.0</span></div>
    `;

    const toggle     = document.getElementById('sidebar-toggle');
    const overlay    = document.getElementById('sidebar-overlay');
    const collapseBtn = document.getElementById('sidebar-collapse');
    const adminMain  = document.querySelector('.admin-main');
    const COLLAPSE_KEY = 'dematiq_sidebar_collapsed';

    if (toggle && overlay) {
      toggle.addEventListener('click', () => { sidebar.classList.toggle('open'); overlay.classList.toggle('show'); });
      overlay.addEventListener('click', () => { sidebar.classList.remove('open'); overlay.classList.remove('show'); });
    }

    if (localStorage.getItem(COLLAPSE_KEY) === '1') {
      sidebar.classList.add('collapsed');
      if (adminMain) adminMain.classList.add('sidebar-collapsed');
    }

    if (collapseBtn) {
      collapseBtn.addEventListener('click', () => {
        const isCollapsed = sidebar.classList.toggle('collapsed');
        if (adminMain) adminMain.classList.toggle('sidebar-collapsed', isCollapsed);
        localStorage.setItem(COLLAPSE_KEY, isCollapsed ? '1' : '0');
      });
    }
  }
};
