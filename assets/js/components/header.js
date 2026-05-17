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
      { href: `${base}/index.html`,                        text: 'Inicio',        page: 'index.html'    },
      { href: `${base}/pages/corporativo/nosotros.html`,   text: 'Sobre Nosotros',page: 'nosotros.html' },
      { href: `${base}/pages/corporativo/soluciones.html`, text: 'Proyectos',     page: 'soluciones.html'},
      { href: `${base}/pages/corporativo/industrias.html`, text: 'Industrias',    page: 'industrias.html'},
      { href: `${base}/pages/corporativo/Contacto.html`,   text: 'Contacto',      page: 'Contacto.html' }
    ];

    const navHTML = navItems.map(item => {
      const active = currentPage === item.page ? ' class="active"' : '';
      return `<li><a href="${item.href}"${active}>${item.text}</a></li>`;
    }).join('');

    return `
      <header>
        <div class="logo">
          <img src="${base}/assets/images/logos/LOGO.jpeg" alt="Logo DEMATIQ">
        </div>
        <button class="nav-toggle" aria-label="Abrir menú" aria-expanded="false">
          <span></span><span></span><span></span>
        </button>
        <nav id="main-nav">
          <ul>${navHTML}</ul>
        </nav>
      </header>
    `;
  }

  static init() {
    const existing = document.querySelector('header');
    if (existing) existing.remove();

    document.body.insertAdjacentHTML('afterbegin', this.getHTML());

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
  }
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => HeaderComponent.init());
} else {
  HeaderComponent.init();
}

