// Footer Component - Injected on all pages
class FooterComponent {
  static getBasePath() {
    const path = window.location.pathname;
    if (path === '/' || (path.endsWith('/index.html') && !path.includes('/pages/'))) {
      return '.';
    }
    const pagesIdx = path.indexOf('/pages/');
    if (pagesIdx !== -1) {
      const parts = path.slice(pagesIdx + 1).split('/').filter(p => p.length > 0);
      const depth = parts.length - 1;
      return depth > 0 ? '../'.repeat(depth).slice(0, -1) : '.';
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
            <img src="${base}/assets/images/logos/logo1.webp" alt="DEMATIQ">
            <p>Empresa mexicana especializada en proyectos de Automatización y Ensamble industrial con más de 10 años de experiencia.</p>
          </div>

          <div class="footer-contact">
            <h4>Contacto</h4>
            <a href="https://mail.google.com/mail/?view=cm&fs=1&to=ventas@dematiq.com.mx"
               target="_blank" rel="noopener" class="footer-action-link">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="4" width="20" height="16" rx="2"/><polyline points="2,4 12,13 22,4"/>
              </svg>
              ventas@dematiq.com.mx
            </a>

            <a href="https://wa.me/524427214891?text=Hola%2C%20me%20gustar%C3%ADa%20obtener%20m%C3%A1s%20informaci%C3%B3n%20sobre%20DEMATIQ"
               target="_blank" rel="noopener" class="footer-action-link footer-action-link--wa">
              <svg viewBox="0 0 24 24" fill="currentColor">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                <path d="M12 0C5.373 0 0 5.373 0 12c0 2.124.558 4.122 1.529 5.855L.057 23.886a.5.5 0 00.611.637l6.239-1.637A11.94 11.94 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22a9.94 9.94 0 01-5.094-1.396l-.364-.217-3.773.99 1.006-3.671-.238-.378A9.952 9.952 0 012 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z"/>
              </svg>
              +52 (442) 721-4891
            </a>
          </div>

          <div class="footer-location">
            <h4>Ubicación</h4>
            <p>Col. Jardines del Sol<br>Querétaro, Qro. CP. 76117</p>
          </div>

          <div class="footer-socials">
            <h4>Síguenos</h4>
            <div class="footer-social-icons">
              <a href="#" aria-label="Facebook" class="footer-social-icon">
                <img src="${base}/assets/images/social/facebook.webp" alt="Facebook">
              </a>
              <a href="#" aria-label="Instagram" class="footer-social-icon">
                <img src="${base}/assets/images/social/instagram.webp" alt="Instagram">
              </a>
              <a href="#" aria-label="LinkedIn" class="footer-social-icon">
                <img src="${base}/assets/images/social/linkedin.webp" alt="LinkedIn">
              </a>
              <a href="#" aria-label="YouTube" class="footer-social-icon">
                <img src="${base}/assets/images/social/youtube.webp" alt="YouTube">
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

function footerInit() {
  FooterComponent.init();
  fetch('/api/contenido.php?clave=contacto').then(r => r.json()).then(d => {
    if (!d || typeof d !== 'object') return;
    const footer = document.getElementById('footer');
    if (!footer) return;
    if (d.email) {
      const el = footer.querySelector('.footer-action-link:not(.footer-action-link--wa)');
      if (el) { el.href = 'https://mail.google.com/mail/?view=cm&fs=1&to=' + encodeURIComponent(d.email); el.childNodes[el.childNodes.length - 1].textContent = d.email; }
    }
    if (d.whatsappNum || d.whatsapp) {
      const el = footer.querySelector('.footer-action-link--wa');
      if (el) {
        if (d.whatsappNum) el.href = d.whatsappNum;
        if (d.whatsapp) el.childNodes[el.childNodes.length - 1].textContent = d.whatsapp;
      }
    }
    if (d.direccion) {
      const el = footer.querySelector('.footer-location p');
      if (el) el.innerHTML = d.direccion.replace(/\n/g, '<br>');
    }
    if (d.social && typeof d.social === 'object') {
      ['facebook','instagram','linkedin','youtube'].forEach(net => {
        if (!d.social[net]) return;
        const el = footer.querySelector(`.footer-social-icon[aria-label="${net.charAt(0).toUpperCase()+net.slice(1)}"]`);
        if (el) el.href = d.social[net];
      });
    }
  }).catch(() => {});
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', footerInit);
} else {
  footerInit();
}
