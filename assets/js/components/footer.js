// Footer Component - Injected on all pages
class FooterComponent {
  static getBasePath() {
    const path = window.location.pathname;
    if (path === '/' || (path.endsWith('/index.html') && !path.includes('/pages/'))) {
      return '.';
    }
    const parts = path.split('/').filter(p => p.length > 0);
    const depth = parts.length - 1;
    if (depth <= 0) return '.';
    return '../'.repeat(depth).slice(0, -1);
  }

  static getHTML() {
    const base  = this.getBasePath();
    const year  = new Date().getFullYear();

    return `
      <footer id="footer">
        <div class="footer-inner">

          <div class="footer-brand">
            <img src="${base}/assets/images/logos/LOGO.jpeg" alt="DEMATIQ">
            <p>Empresa mexicana especializada en proyectos de Automatización y Ensamble industrial con más de 10 años de experiencia.</p>
          </div>

          <nav class="footer-nav" aria-label="Pie de página">
            <h4>Navegación</h4>
            <ul>
              <li><a href="${base}/index.html">Inicio</a></li>
              <li><a href="${base}/pages/corporativo/nosotros.html">Sobre Nosotros</a></li>
              <li><a href="${base}/pages/corporativo/soluciones.html">Proyectos</a></li>
              <li><a href="${base}/pages/corporativo/industrias.html">Industrias</a></li>
              <li><a href="${base}/pages/corporativo/Contacto.html">Contacto</a></li>
            </ul>
          </nav>

          <div class="footer-contact">
            <h4>Contacto</h4>
            <p>📍 Col. Jardines del Sol<br>Querétaro, Qro. CP.76117</p>
            <p>✉️ <a href="mailto:ventas@dematiq.com.mx">ventas@dematiq.com.mx</a></p>
            <p>📞 <a href="tel:+524427214891">+52 442 721-4891</a></p>
            <div class="footer-social">
              <a href="#" aria-label="Facebook">
                <img src="${base}/assets/images/social/facebook.png" alt="Facebook">
              </a>
              <a href="#" aria-label="Instagram">
                <img src="${base}/assets/images/social/instagram.png" alt="Instagram">
              </a>
              <a href="#" aria-label="LinkedIn">
                <img src="${base}/assets/images/social/linkedin.png" alt="LinkedIn">
              </a>
            </div>
          </div>

        </div>
        <div class="footer-bottom">
          <p>&copy; ${year} DEMATIQ AUTOMATIZACIÓN S. DE R.L. DE C.V. &nbsp;·&nbsp; Querétaro, México</p>
        </div>
      </footer>
    `;
  }

  static init() {
    const existing = document.getElementById('footer');
    if (existing) existing.remove();
    document.body.insertAdjacentHTML('beforeend', this.getHTML());
  }
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => FooterComponent.init());
} else {
  FooterComponent.init();
}
