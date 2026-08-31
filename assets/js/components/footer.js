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

          <div class="footer-socials" id="footer-socials-block" style="display:none;">
            <h4>Síguenos</h4>
            <div class="footer-social-icons" id="footer-social-icons"></div>
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

const FOOTER_SOCIAL_NETWORKS = [
  { id:'facebook',  name:'Facebook',
    icon:`<svg viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>` },
  { id:'instagram', name:'Instagram',
    icon:`<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>` },
  { id:'linkedin',  name:'LinkedIn',
    icon:`<svg viewBox="0 0 24 24" fill="currentColor"><path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/></svg>` },
  { id:'youtube',   name:'YouTube',
    icon:`<svg viewBox="0 0 24 24" fill="currentColor"><path d="M22.54 6.42a2.78 2.78 0 00-1.95-1.96C18.88 4 12 4 12 4s-6.88 0-8.59.46a2.78 2.78 0 00-1.95 1.96A29 29 0 001 12a29 29 0 00.46 5.58A2.78 2.78 0 003.41 19.54C5.12 20 12 20 12 20s6.88 0 8.59-.46a2.78 2.78 0 001.95-1.96A29 29 0 0023 12a29 29 0 00-.46-5.58z"/><polygon points="9.75,15.02 15.5,12 9.75,8.98 9.75,15.02" fill="#fff"/></svg>` },
  { id:'twitter',   name:'X / Twitter',
    icon:`<svg viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.743l7.737-8.835L1.254 2.25H8.08l4.259 5.63L18.244 2.25zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>` },
  { id:'tiktok',    name:'TikTok',
    icon:`<svg viewBox="0 0 24 24" fill="currentColor"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.34 6.34 0 106.34 6.34V8.69a8.18 8.18 0 004.78 1.52V6.76a4.85 4.85 0 01-1.01-.07z"/></svg>` },
  { id:'pinterest', name:'Pinterest',
    icon:`<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.373 0 0 5.373 0 12c0 5.084 3.163 9.426 7.627 11.174-.105-.949-.2-2.405.042-3.441.218-.937 1.407-5.965 1.407-5.965s-.359-.719-.359-1.782c0-1.668.967-2.914 2.171-2.914 1.023 0 1.518.769 1.518 1.69 0 1.029-.655 2.568-.994 3.995-.283 1.194.599 2.169 1.777 2.169 2.133 0 3.772-2.249 3.772-5.495 0-2.873-2.064-4.882-5.012-4.882-3.414 0-5.418 2.561-5.418 5.207 0 1.031.397 2.138.893 2.738.099.12.113.225.083.345l-.333 1.36c-.053.22-.174.267-.402.161-1.499-.698-2.436-2.889-2.436-4.649 0-3.785 2.75-7.262 7.929-7.262 4.163 0 7.398 2.967 7.398 6.931 0 4.136-2.607 7.464-6.227 7.464-1.216 0-2.359-.632-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146C9.57 23.812 10.763 24 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0z"/></svg>` },
  { id:'threads',   name:'Threads',
    icon:`<svg viewBox="0 0 192 192" fill="currentColor"><path d="M141.537 88.988a66.667 66.667 0 00-2.518-1.143c-1.482-27.307-16.403-42.94-41.457-43.1h-.34c-14.986 0-27.449 6.396-35.12 18.036l13.779 9.452c5.73-8.695 14.724-10.548 21.348-10.548h.231c8.248.053 14.474 2.452 18.502 7.13 2.932 3.405 4.893 8.11 5.864 14.05-7.314-1.243-15.224-1.626-23.68-1.14-23.82 1.371-39.134 15.264-38.105 34.568.522 9.792 5.4 18.216 13.735 23.719 7.047 4.652 16.124 6.927 25.557 6.412 12.458-.683 22.231-5.436 29.049-14.127 5.178-6.6 8.453-15.153 9.899-25.93 5.937 3.583 10.337 8.298 12.767 13.966 4.132 9.635 4.373 25.468-8.546 38.318-11.319 11.255-24.94 16.12-45.508 16.274-22.739-.169-39.951-7.418-51.15-21.551C27.36 139.343 21.96 120.712 21.768 96c.192-24.712 5.592-43.343 16.033-55.369C48.999 26.418 66.211 19.169 88.95 19c21.92.17 38.978 7.452 50.7 21.645 5.765 6.974 10.11 15.808 12.964 26.07l16.239-4.285c-3.5-12.93-9.044-24.116-16.609-33.333C136.637 11.816 115.498 2.295 89.04 2.1h-.1C62.578 2.295 41.205 11.848 26.891 29.2 14.3 44.438 7.694 65.796 7.5 95.976v.05c.194 30.18 6.8 51.538 19.391 66.774C41.205 180.152 62.578 189.705 89 189.9h.1c23.433-.16 39.912-6.53 53.5-20.045 17.679-17.579 17.154-39.663 11.337-53.12-4.216-9.822-12.208-17.799-24.4-23.747zM96.165 138.3c-10.39.583-21.208-4.074-21.727-14.053-.39-7.348 5.241-15.53 22.308-16.535 1.953-.112 3.868-.168 5.745-.168 6.097 0 11.808.606 17.05 1.778-1.939 24.184-12.834 28.394-23.376 28.978z"/></svg>` },
  { id:'snapchat',  name:'Snapchat',
    icon:`<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12.166.001C10.009.001 4.5.56 4.5 6.899V9H3l-.5 2.5h2C4.18 13.274 3.5 14.5 2 15c0 0 .5 2 4 2 0 0 .9 2 6 2s6-2 6-2c3.5 0 4-2 4-2-1.5-.5-2.18-1.726-2.5-3.5h2L21 9h-1.5V6.899C19.5.56 14.323.001 12.166.001z"/></svg>` },
];

function registrarVisita() {
  // Sitio multipágina: cada clic a otra sección es una carga de página nueva, así
  // que sin este candado cada navegación interna sumaba una "visita" (el contador
  // debe medir visitantes por día, no cargas de página). Un candado por día en
  // localStorage evita contar de nuevo hasta el día siguiente.
  try {
    // Fecha LOCAL del visitante, no UTC: toISOString() da la fecha UTC, que
    // se adelanta a la de México (UTC-6) desde las 6pm hora local en adelante,
    // haciendo que este candado deje de coincidir con el día que registra el
    // servidor (api/visita.php usa America/Mexico_City) y dispare un conteo
    // duplicado cada noche — el mismo bug de sobreconteo que este candado
    // existe para evitar.
    const d = new Date();
    const hoy = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
    if (localStorage.getItem('dtq_visita') === hoy) return;
    localStorage.setItem('dtq_visita', hoy);
  } catch {}
  fetch('/api/visita.php', { method: 'POST' }).catch(() => {});
}

function footerInit() {
  FooterComponent.init();
  registrarVisita();
  fetch('/api/contenido.php?clave=contacto', { cache: 'no-store' }).then(r => r.json()).then(d => {
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
        if (d.whatsappNum) {
          const msg = 'Hola, me gustaría obtener más información sobre DEMATIQ';
          el.href = 'https://wa.me/' + d.whatsappNum + '?text=' + encodeURIComponent(msg);
        }
        if (d.whatsapp) el.childNodes[el.childNodes.length - 1].textContent = d.whatsapp;
      }
    }
    if (d.direccion) {
      const el = footer.querySelector('.footer-location p');
      if (el) el.innerHTML = d.direccion.replace(/\n/g, '<br>');
    }
    if (d.social && typeof d.social === 'object') {
      const grid = footer.querySelector('#footer-social-icons');
      const block = footer.querySelector('#footer-socials-block');
      if (grid) {
        const html = FOOTER_SOCIAL_NETWORKS
          .filter(net => d.social[net.id])
          .map(net => `
            <a href="${d.social[net.id]}" target="_blank" rel="noopener noreferrer" aria-label="${net.name}" class="footer-social-icon">
              ${net.icon}
            </a>
          `).join('');
        grid.innerHTML = html;
        if (block) block.style.display = html ? '' : 'none';
      }
    }
  }).catch(() => {});
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', footerInit);
} else {
  footerInit();
}
