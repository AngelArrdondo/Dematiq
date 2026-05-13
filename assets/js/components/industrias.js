// Industries Component - Manages industry tabs and carousels
class IndustriasComponent {
  static currentOpen = null;

  static toggleInfo(id) {
    const element = document.getElementById(id);
    const allInfos = document.querySelectorAll('.info-industria');
    const allItems = document.querySelectorAll('.lista-industrias li');

    // If clicking the same item, close it
    if (this.currentOpen === id && element.style.display === 'block') {
      element.style.display = 'none';
      allItems.forEach(item => {
        if (item.dataset.industria === id) {
          item.classList.remove('active');
          item.setAttribute('aria-selected', 'false');
        }
      });
      this.currentOpen = null;
      
      // Stop all carousels
      this.stopAllCarousels();
      return;
    }

    // Hide all panels and deactivate all tabs
    allInfos.forEach(info => {
      info.style.display = 'none';
    });
    
    allItems.forEach(item => {
      item.classList.remove('active');
      item.setAttribute('aria-selected', 'false');
    });

    // Show selected panel and activate tab
    element.style.display = 'block';
    allItems.forEach(item => {
      if (item.dataset.industria === id) {
        item.classList.add('active');
        item.setAttribute('aria-selected', 'true');
      }
    });

    this.currentOpen = id;

    // Refresh carousel in the opened panel
    this.refreshCarousel(id);
    
    // Scroll to content on mobile
    if (window.innerWidth < 768) {
      element.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  }

  static refreshCarousel(id) {
    // Find the carousel in the active panel
    const panel = document.getElementById(id);
    if (!panel) return;

    const carousel = panel.querySelector('.carousel');
    if (carousel) {
      const bsCarousel = bootstrap.Carousel.getInstance(carousel);
      if (bsCarousel) {
        bsCarousel.cycle();
      }
    }
  }

  static stopAllCarousels() {
    const carousels = document.querySelectorAll('.carousel');
    carousels.forEach(carousel => {
      const bsCarousel = bootstrap.Carousel.getInstance(carousel);
      if (bsCarousel) {
        bsCarousel.pause();
      }
    });
  }

  static init() {
    // Add keyboard navigation
    const listItems = document.querySelectorAll('.lista-industrias li');
    
    listItems.forEach(item => {
      // Click handler
      item.addEventListener('click', () => {
        const industria = item.dataset.industria;
        if (industria) {
          this.toggleInfo(industria);
        }
      });

      // Keyboard handler
      item.addEventListener('keydown', (e) => {
        const currentIndex = Array.from(listItems).indexOf(item);
        
        switch(e.key) {
          case 'Enter':
          case ' ':
            e.preventDefault();
            item.click();
            break;
          case 'ArrowDown':
            e.preventDefault();
            const next = listItems[currentIndex + 1];
            if (next) next.focus();
            break;
          case 'ArrowUp':
            e.preventDefault();
            const prev = listItems[currentIndex - 1];
            if (prev) prev.focus();
            break;
        }
      });

      // Make items focusable
      item.setAttribute('tabindex', '0');
    });

    console.log('✅ Industrias component initialized');
  }
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => IndustriasComponent.init());
} else {
  IndustriasComponent.init();
}
