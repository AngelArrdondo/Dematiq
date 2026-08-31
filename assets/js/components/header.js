// Header Component - Loaded on all pages
class HeaderComponent {
  static getBasePath() {
    const path = window.location.pathname;
    if (path === '/' || (path.endsWith('/index.html') && !path.includes('/pages/'))) {
      return '.';
    }
    // Use /pages/ as anchor so file:// and http:// give the same depth
    const pagesIdx = path.indexOf('/pages/');
    if (pagesIdx !== -1) {
      const parts = path.slice(pagesIdx + 1).split('/').filter(p => p.length > 0);
      const depth = parts.length - 1; // dirs between root and the file
      return depth > 0 ? '../'.repeat(depth).slice(0, -1) : '.';
    }
    const parts = path.split('/').filter(p => p.length > 0);
    const depth = parts.length - 1;
    if (depth <= 0) return '.';
    return '../'.repeat(depth).slice(0, -1);
  }

  static getCurrentPage() {
    const path = window.location.pathname;
    const parts = path.split('/');
    return parts[parts.length - 1] || 'index.html';
  }

  static getHTML() {
    const base        = this.getBasePath();
    const currentPage = this.getCurrentPage();

    const navItems = [
      { id: 'inicio',      href: `${base}/index.html`,                        text: 'Inicio',        page: 'index.html'    },
      { id: 'nosotros',    href: `${base}/pages/corporativo/nosotros.html`,   text: 'Sobre Nosotros',page: 'nosotros.html' },
      { id: 'proyectos',   href: `${base}/pages/corporativo/soluciones.html`, text: 'Proyectos',     page: ['soluciones.html', 'proyecto.html']},
      { id: 'industrias',  href: `${base}/pages/corporativo/industrias.html`, text: 'Industrias',    page: 'industrias.html'},
      { id: 'contacto',    href: `${base}/pages/corporativo/Contacto.html`,   text: 'Contacto',      page: 'Contacto.html' },
      { id: 'tienda',      href: 'https://tienda.dematiq.com.mx/',            text: 'Tienda',        external: true }
    ];

    const navHTML = navItems.map(item => {
      if (item.external) {
        return `<li><a href="${item.href}" target="_blank" rel="noopener" data-nav-id="${item.id}">${item.text}</a></li>`;
      }
      const pages = Array.isArray(item.page) ? item.page : [item.page];
      const active = pages.includes(currentPage) ? ' class="active"' : '';
      return `<li><a href="${item.href}"${active} data-nav-id="${item.id}">${item.text}</a></li>`;
    }).join('');

    return `
      <header>
        <div class="logo">
          <img src="${base}/assets/images/logos/logo1.webp" alt="Logo DEMATIQ">
        </div>
        <nav id="main-nav">
          <ul>${navHTML}</ul>
          <a href="${base}/pages/corporativo/login.php" class="nav-login-link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
            Iniciar sesión
          </a>
        </nav>
        <div class="header-actions">
          <a href="${base}/pages/corporativo/login.php" class="btn-login" aria-label="Iniciar sesión">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
          </a>
          <button class="nav-toggle" aria-label="Abrir menú" aria-expanded="false">
            <span></span><span></span><span></span>
          </button>
        </div>
      </header>
    `;
  }

  static injectFavicon() {
    const base = this.getBasePath();
    document.querySelectorAll('link[rel="icon"], link[rel="shortcut icon"]').forEach(el => el.remove());
    const link = document.createElement('link');
    link.rel = 'icon';
    link.type = 'image/svg+xml';
    link.href = `${base}/assets/images/logos/favicon-d.svg`;
    document.head.appendChild(link);
  }

  static init() {
    const existing = document.querySelector('header');
    if (existing) existing.remove();

    document.body.insertAdjacentHTML('afterbegin', this.getHTML());
    this.injectFavicon();

    // Hamburger toggle
    const toggle = document.querySelector('.nav-toggle');
    const nav    = document.querySelector('#main-nav');

    if (toggle && nav) {
      toggle.addEventListener('click', () => {
        const isOpen = nav.classList.toggle('open');
        toggle.classList.toggle('open', isOpen);
        toggle.setAttribute('aria-expanded', String(isOpen));
      });

      nav.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', () => {
          nav.classList.remove('open');
          toggle.classList.remove('open');
          toggle.setAttribute('aria-expanded', 'false');
        });
      });
    }

    // Mostrar el body YA con header listo — evita el flash sin header
    document.body.style.visibility = 'visible';

    this.applyNavOverrides();
  }

  // Los textos del menú se renderizan primero con sus valores por defecto
  // (para no bloquear el primer pintado) y aquí se sobreescriben si el
  // admin definió textos personalizados en la sección "Navegación".
  static applyNavOverrides() {
    fetch('/api/contenido.php?clave=navegacion', { cache: 'no-store' })
      .then(r => r.json())
      .then(d => {
        if (!d || typeof d !== 'object') return;
        ['inicio', 'nosotros', 'proyectos', 'industrias', 'contacto', 'tienda'].forEach(id => {
          const el = document.querySelector(`#main-nav a[data-nav-id="${id}"]`);
          if (!el) return;
          if (d[id]) el.textContent = d[id];
          const url = d[id + 'Url'];
          if (url) el.href = url;
        });
        this.renderExtraLinks(d.extras);
      })
      .catch(() => {});
  }

  // Enlaces adicionales, configurables desde el admin, que se muestran al
  // final del menú (justo antes del botón de inicio de sesión).
  static renderExtraLinks(extras) {
    if (!Array.isArray(extras) || !extras.length) return;
    const ul = document.querySelector('#main-nav ul');
    if (!ul) return;
    extras.forEach(ex => {
      const texto = (ex && ex.texto || '').trim();
      const url   = (ex && ex.url || '').trim();
      if (!texto || !url) return;
      const li = document.createElement('li');
      const a  = document.createElement('a');
      a.href = url;
      a.target = '_blank';
      a.rel = 'noopener';
      a.textContent = texto;
      li.appendChild(a);
      ul.appendChild(li);
    });
  }
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => HeaderComponent.init());
} else {
  HeaderComponent.init();
}

