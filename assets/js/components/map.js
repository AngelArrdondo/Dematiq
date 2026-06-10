// Map Component for Contact Page
class MapComponent {
  static init() {
    // Check if map container exists
    const mapContainer = document.getElementById('map');
    if (!mapContainer) return;

    // Coordenadas de DEMATIQ
    const lat = 20.5960;
    const lng = -100.4470;
    const zoom = 15;

    // Initialize map
    const map = L.map('map').setView([lat, lng], zoom);

    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.webp', {
      attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // Add marker
    const marker = L.marker([lat, lng])
      .addTo(map)
      .bindPopup(`
        <b>DEMATIQ</b><br>
        Automatización S. de R.L. de C.V.<br>
        Santa María Magdalena, Querétaro
      `)
      .openPopup();

    // Add circle to show approximate area
    L.circle([lat, lng], {
      color: '#007bff',
      fillColor: '#007bff',
      fillOpacity: 0.2,
      radius: 500
    }).addTo(map);

    // Handle responsive resizing
    window.addEventListener('resize', () => {
      map.invalidateSize();
    });
  }
}

// Initialize map when DOM and Leaflet are ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    // Wait for Leaflet to be loaded
    if (typeof L !== 'undefined') {
      MapComponent.init();
    } else {
      console.error('Leaflet not loaded');
    }
  });
} else {
  if (typeof L !== 'undefined') {
    MapComponent.init();
  }
}
