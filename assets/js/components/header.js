// Header Component - Loaded on all pages
class HeaderComponent {
  static getBasePath() {
    const path = window.location.pathname;
    // Handle index.html at root
    if (path === '/' || path.endsWith('/index.html') && !path.includes('/pages/')) {
      return '.';
    }
    
    // Count directory depth
    const parts = path.split('/').filter(p => p.length > 0);
    // Last part is the file, so depth is parts.length - 1
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
    const base = this.getBasePath();
    const currentPage = this.getCurrentPage();
    
    // Define navigation items
    const navItems = [
      { href: `${base}/index.html`, text: 'Inicio', page: 'index.html' },
      { href: `${base}/pages/corporativo/nosotros.html`, text: 'Sobre Nosotros', page: 'nosotros.html' },
      { href: `${base}/pages/corporativo/soluciones.html`, text: 'Proyectos', page: 'soluciones.html' },
      { href: `${base}/pages/corporativo/industrias.html`, text: 'Industrias', page: 'industrias.html' },
      { href: `${base}/pages/corporativo/Contacto.html`, text: 'Contacto', page: 'Contacto.html' }
    ];

    // Generate nav items with active state
    const navHTML = navItems.map(item => {
      const isActive = currentPage === item.page ? ' class="active"' : '';
      return `<li><a href="${item.href}"${isActive}>${item.text}</a></li>`;
    }).join('');

    return `
      <header>
        <div class="logo">
          <img src="${base}/assets/images/logos/LOGO.jpeg" alt="Logo DEMATIQ">
        </div>
        <nav>
          <ul>
            ${navHTML}
          </ul>
        </nav>
      </header>
    `;
  }

  static init() {
    // Remove any existing header first
    const existingHeader = document.querySelector('header');
    if (existingHeader) {
      existingHeader.remove();
    }
    
    // Insert header at the beginning of body
    document.body.insertAdjacentHTML('afterbegin', this.getHTML());
    
    console.log('✅ Header initialized - Current page:', this.getCurrentPage());
  }
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => HeaderComponent.init());
} else {
  HeaderComponent.init();
}
