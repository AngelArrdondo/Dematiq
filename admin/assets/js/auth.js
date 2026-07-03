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
    hero: {
      video:  'assets/videos/hero.mp4',
      poster: 'assets/images/general/index.webp',
      badge:  'Bienvenido'
    }
  },
  industrias: [
    { id: 'automotriz',       nombre: 'Automotriz',              imagen: 'assets/images/general/img1.webp', descripcion: 'Colaboramos en proyectos de automatización, control de calidad y desarrollo de maquinaria para procesos de ensamblaje y pruebas.' },
    { id: 'farmaceutica',     nombre: 'Farmacéutica',            imagen: 'assets/images/general/img2.webp', descripcion: 'Desarrollamos sistemas de control ambiental, monitoreo de producción y soluciones de trazabilidad para laboratorios y plantas farmacéuticas.' },
    { id: 'alimenticia',      nombre: 'Alimenticia',             imagen: 'assets/images/general/img3.webp', descripcion: 'Implementamos soluciones de automatización y control de calidad para la producción de alimentos y bebidas.' },
    { id: 'manufactura',      nombre: 'Manufactura',             imagen: 'assets/images/general/img1.webp', descripcion: 'Colaboramos en la optimización de procesos, implementación de sistemas de control y desarrollo de maquinaria especializada.' },
    { id: 'electronica',      nombre: 'Electrónica y Eléctrica', imagen: 'assets/images/general/img2.webp', descripcion: 'Implementamos soluciones de automatización y control de calidad para la producción de dispositivos electrónicos.' },
    { id: 'electrodomesticos',nombre: 'Electrodomésticos',       imagen: 'assets/images/general/img3.webp', descripcion: 'Desarrollamos soluciones de automatización y control de calidad para la producción de electrodomésticos.' },
    { id: 'alimentos',        nombre: 'Alimentos y Bebidas',     imagen: 'assets/images/general/img1.webp', descripcion: 'Soluciones de envasado, etiquetado y control de procesos para líneas de producción de alimentos y bebidas.' },
    { id: 'aeroespacial',     nombre: 'Aeroespacial',            imagen: 'assets/images/general/img2.webp', descripcion: 'Desarrollamos soluciones de automatización y control de calidad para la producción de componentes aeroespaciales.' }
  ],
  servicios: [
    { id: 'plc',           nombre: 'Programación de PLC',                    image: 'assets/images/general/img1.webp', desc: 'Automatización de procesos y líneas de producción mediante lógica de control robusta. Optimizamos secuencias complejas, garantizamos la continuidad operativa y facilitamos diagnósticos rápidos para minimizar los tiempos muertos en planta.' },
    { id: 'hmi',           nombre: 'Programación de HMI, SCADA',             image: 'assets/images/general/img2.webp', desc: 'Desarrollo de interfaces pantalla-operador intuitivas y ergonómicas para el monitoreo en tiempo real, el control seguro de las variables del proceso y la gestión de alarmas. Se complementa con sistemas SCADA que centralizan la supervisión y adquisición de datos de toda la planta.' },
    { id: 'vision',        nombre: 'Sistemas de Visión',                     image: 'assets/images/general/img3.webp', desc: 'Inspección óptica automatizada para control de calidad, guiado de robots y trazabilidad. Detectamos defectos, verificamos ensambles y leemos códigos a alta velocidad, eliminando el error humano en la línea de producción.' },
    { id: 'servo',         nombre: 'Programación de Servomotores',            image: 'assets/images/general/img1.webp', desc: 'Control de movimiento de alta precisión para perfiles de velocidad, aceleración y posicionamiento dinámico. Garantizamos la exactitud requerida en aplicaciones críticas de empaque, corte y manipulación de productos.' },
    { id: 'diagramas',     nombre: 'Diseño de Diagramas Eléctricos',          image: 'assets/images/general/img2.webp', desc: 'Elaboración de planos y esquemas de conexiones detallados bajo normativas internacionales. Aseguramos la correcta interconexión de componentes, facilitando el montaje técnico, el mantenimiento preventivo y futuras expansiones.' },
    { id: 'tableros',      nombre: 'Diseño de Tableros de Control',           image: 'assets/images/general/img3.webp', desc: 'Ingeniería, distribución y ensamble de gabinetes de control y fuerza. Calculamos protecciones y sistemas de gestión térmica que garantizan un entorno seguro, ordenado y con la ventilación adecuada para proteger los componentes.' },
    { id: 'modernizacion', nombre: 'Modernización de Maquinaria',             image: 'assets/images/general/img1.webp', desc: 'Actualización tecnológica de maquinaria obsoleta o descontinuada. Reemplazamos hardware antiguo por sistemas modernos de control, incrementando la productividad, mejorando la seguridad y restituyendo la disponibilidad de refacciones.' },
    { id: 'instalaciones', nombre: 'Instalaciones Eléctricas',                image: 'assets/images/general/img2.webp', desc: 'Montajes eléctricos industriales, canalizaciones pesadas y cableado de fuerza o control. Ejecutamos proyectos bajo estrictas normas de seguridad, garantizando inmunidad al ruido electromagnético e integridad en las señales de datos.' },
    { id: 'ingenieria',    nombre: 'Ingeniería Básica y de Detalle',          image: 'assets/images/general/img3.webp', desc: 'Desarrollo técnico integral de proyectos desde la etapa conceptual hasta la ejecución. Generamos diagramas de flujo, listas de materiales y memorias de cálculo que aseguran la viabilidad financiera y operativa del proyecto.' },
    { id: 'variadores',    nombre: 'Variadores de Frecuencia',                image: 'assets/images/general/img1.webp', desc: 'Control preciso de velocidad y torque en motores eléctricos industriales. Optimizamos los procesos de arranque y paro, reducimos el estrés mecánico de los equipos y logramos un ahorro significativo en el consumo de energía.' }
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
  ],
  ensamble:   { titulo: 'Ensamble', subtitulo: 'Soluciones de Ensamble', tabs: [
    { nombre: 'Máquinas Automáticas',     desc: 'Unión y montaje autónomo de componentes sin intervención humana directa. Optimizan los tiempos de ciclo, garantizan una repetibilidad perfecta y elevan la productividad en líneas de producción masiva.', images: ['assets/images/products/ejem2.webp', 'assets/images/general/img1.webp'] },
    { nombre: 'Máquinas Semiautomáticas', desc: 'Estaciones de trabajo que combinan la flexibilidad de la carga manual del operador con el accionamiento automatizado y preciso de prensas o atornilladores. Maximizan la seguridad y controlan las variables críticas.', images: ['assets/images/products/en1.webp', 'assets/images/general/img1.webp'] },
    { nombre: 'Máquinas Manuales',        desc: 'Dispositivos de sujeción y estaciones ergonómicas operadas por personal técnico. Ofrecen adaptabilidad total para cambios rápidos de modelo, ensamble de alta complejidad o producciones de bajo volumen y prototipos.', images: ['assets/images/general/img3.webp', 'assets/images/general/img1.webp'] }
  ]},
  maqcontrol: { titulo: 'Máquinas de Control de Torque', subtitulo: 'Soluciones de Torque', tabs: [
    { nombre: 'Máquinas de Ángulo',        desc: 'Control y verificación del ángulo de apriete en tornillería crítica. Garantizan el par exacto para cada punto de ensamble, registrando cada resultado para trazabilidad total del proceso.', images: ['assets/images/general/t1.webp', 'assets/images/general/t2.webp'] },
    { nombre: 'Máquinas Número de Vuelta', desc: 'Verificación del número de vueltas aplicadas en operaciones de roscado y apriete. Aseguran la correcta inserción de sujetadores y detectan anomalías en el torque de forma automática.', images: ['assets/images/general/img2.webp'] }
  ]},
  maqprob:    { titulo: 'Máquinas Probadoras de Fuga', subtitulo: 'Soluciones de Probadoras de Fuga', tabs: [
    { nombre: 'Fuga de gas',           desc: 'Detección de fugas mediante presurización con aire o nitrógeno. Ideal para componentes que requieren hermeticidad en condiciones de presión estándar.', images: ['assets/images/general/fuga.webp'] },
    { nombre: 'Fuga de helio',         desc: 'Prueba de hermeticidad de alta sensibilidad usando helio como gas trazador. Detecta fugas mínimas en componentes críticos de la industria automotriz y aeroespacial.', images: ['assets/images/general/img2.webp'] },
    { nombre: 'Fuga de Sniffer helio', desc: 'Localización puntual de fugas mediante rastreo superficial con detector portátil de helio. Permite identificar con precisión el origen de la fuga en piezas complejas.', images: ['assets/images/general/img3.webp'] },
    { nombre: 'Burbuja',               desc: 'Método visual de detección de fugas por inmersión en agua con sobrepresión interna. Solución económica y confiable para piezas de geometría variable.', images: ['assets/images/general/img1.webp'] },
    { nombre: 'Flujo',                 desc: 'Medición del caudal de gas que atraviesa un componente para verificar restricciones, obstrucciones o holguras fuera de tolerancia en orificios y conductos.', images: ['assets/images/general/img2.webp'] },
    { nombre: 'Caída de vacío',        desc: 'Evaluación de hermeticidad mediante la aplicación de vacío y el monitoreo de su estabilidad en el tiempo. Adecuada para piezas sensibles a la presión positiva.', images: ['assets/images/general/img3.webp'] },
    { nombre: 'Caída de presión',      desc: 'Prueba de estanqueidad por monitoreo de la pérdida de presión en cavidades internas. Método versátil para una amplia gama de productos industriales.', images: ['assets/images/general/img1.webp'] }
  ]},
  maqinspe:   { titulo: 'Máquinas de Inspección', subtitulo: 'Soluciones de Maquinas de Inspeccion', tabs: [
    { nombre: 'Inspección 3D',              desc: 'Medición tridimensional de geometrías complejas mediante sensores láser o luz estructurada. Verifica dimensiones, planitud y perfil con tolerancias de micras sin contacto.', images: ['assets/images/general/inspeccion.webp'] },
    { nombre: 'Código de barras 2D / 3D',   desc: 'Lectura y verificación de códigos DataMatrix, QR y de barras en movimiento o en estación. Garantiza la trazabilidad completa de cada pieza a lo largo de la línea.', images: ['assets/images/general/img2.webp'] },
    { nombre: 'Geometría / Textura',        desc: 'Análisis de superficie para detección de defectos de textura, rayones, porosidad y variaciones de forma. Combina visión artificial con algoritmos de procesamiento de imagen.', images: ['assets/images/general/img3.webp'] },
    { nombre: 'Espesor / Área / Altura',    desc: 'Medición precisa de espesores de pared, áreas de sellado y alturas de elementos en piezas ensambladas. Detecta faltantes y variaciones dimensionales fuera de especificación.', images: ['assets/images/general/img1.webp'] },
    { nombre: 'Acabado / Presencia / Marcas', desc: 'Verificación de acabado superficial, presencia de componentes ensamblados y correcta aplicación de marcas o etiquetas. Reduce rechazos en línea y asegura la conformidad visual del producto.', images: ['assets/images/general/img2.webp'] }
  ]},
  maclim:     { titulo: 'Máquinas de Limpieza', subtitulo: 'Soluciones de Maquinas de Limpieza', tabs: [
    { nombre: 'Aire de alta presión',   desc: 'Remoción de partículas, rebabas y contaminantes mediante chorros de aire comprimido a alta presión. Proceso seco y rápido, ideal para piezas mecanizadas antes del ensamble.', images: ['assets/images/general/limpieza.webp'] },
    { nombre: 'Inmersión',              desc: 'Limpieza profunda de piezas por inmersión en soluciones acuosas con agentes desengrasantes. Elimina aceites de corte, emulsiones y residuos adheridos en geometrías complejas.', images: ['assets/images/general/img2.webp'] },
    { nombre: 'Aspersión alta presión', desc: 'Remoción de contaminantes mediante boquillas de alta presión que proyectan solución limpiadora sobre todas las superficies. Combina acción mecánica y química para máxima eficacia.', images: ['assets/images/general/img3.webp'] },
    { nombre: 'Ultrasonido',            desc: 'Limpieza por cavitación ultrasónica que alcanza zonas internas y geometrías inaccesibles. Ideal para componentes de precisión que requieren limpieza sin contacto mecánico.', images: ['assets/images/general/img1.webp'] }
  ]},
  maqmar:     { titulo: 'Máquinas de Marcado', subtitulo: 'Soluciones de Maquinas de Marcado', tabs: [
    { nombre: 'Marcado Láser',      desc: 'Grabado permanente de códigos, logotipos y textos mediante haz láser de alta precisión. Sin consumibles, alta velocidad y contraste duradero en metales, plásticos y otros materiales.', images: ['assets/images/general/micro.webp'] },
    { nombre: 'Micropercusión',     desc: 'Marcado por impacto de aguja en movimiento controlado por CNC. Genera marcas profundas y legibles en todo tipo de metales, resistentes al calor, abrasión y tratamientos superficiales.', images: ['assets/images/general/img1.webp'] },
    { nombre: 'Inyección de tinta', desc: 'Impresión de códigos y datos variables a alta velocidad mediante goteo controlado. Adecuado para superficies porosas y no planas donde otros métodos no son aplicables.', images: ['assets/images/general/img2.webp'] },
    { nombre: 'Troquel',            desc: 'Marcado por presión con matrices intercambiables. Solución económica y robusta para series de producción donde se requiere marca en relieve o bajo relieve en materiales blandos.', images: ['assets/images/general/img3.webp'] }
  ]},
  macrobot:   { titulo: 'Celdas Robóticas', subtitulo: 'Soluciones en Celdas Robóticas', tabs: [
    { nombre: 'Celdas de Paletizado',          desc: 'Automatización del apilado y organización de productos en tarimas mediante robots articulados. Optimizan el flujo al final de línea, reducen lesiones por esfuerzo y aumentan la cadencia de producción.', images: ['assets/images/general/celdas.webp'] },
    { nombre: 'Celdas de Alimentación',        desc: 'Sistemas de suministro automático de piezas a máquinas o líneas de ensamble. Incluyen tolvas vibratorias, bandas indexadas y robots de pick & place para flujo continuo sin intervención humana.', images: ['assets/images/general/img2.webp'] },
    { nombre: 'Celdas con sistema de visión',  desc: 'Robots guiados por cámaras que localizan, orientan y manipulan piezas sin posicionamiento rígido previo. Adaptables a variaciones de forma y posición en tiempo real.', images: ['assets/images/general/img3.webp'] },
    { nombre: 'Celdas de Soldadura',           desc: 'Soldadura MIG, TIG o láser automatizada con trayectorias programadas y repetibilidad milimétrica. Mejoran la calidad del cordón, eliminan exposición operativa y aumentan la productividad.', images: ['assets/images/general/img1.webp'] },
    { nombre: 'Corte láser de pieza metálica', desc: 'Corte de alta precisión en láminas y perfiles metálicos mediante láser de fibra o CO₂. Bordes limpios, mínima zona afectada por calor y versatilidad total en geometrías 2D y 3D.', images: ['assets/images/general/img2.webp'] }
  ]},
  maqindus:   { titulo: 'Maquinados Industriales', subtitulo: 'Soluciones de Manufactura Maquinados Industriales', tabs: [
    { nombre: 'Diseño 2D y 3D de Dispositivos',    desc: 'Modelado tridimensional y desarrollo de planos técnicos para herramentales y mecanismos. Aseguramos la viabilidad funcional, la ergonomía y la precisión geométrica de cada dispositivo antes de iniciar su fabricación.', images: ['assets/images/products/maq.webp', 'assets/images/general/img1.webp'] },
    { nombre: 'Fabricación de Piezas Mecánicas',   desc: 'Manufactura de componentes de precisión mediante torneado, fresado y maquinado CNC en diversos materiales. Garantizamos el cumplimiento estricto de tolerancias dimensionales y los acabados superficiales requeridos.', images: ['assets/images/products/maq2.webp', 'assets/images/general/img1.webp'] },
    { nombre: 'Diseño y Fabricación de Fixture',   desc: 'Creación de dispositivos de sujeción a la medida para optimizar procesos de ensamble, soldadura o inspección. Aseguran la repetibilidad operativa, mantienen la alineación exacta y reducen los tiempos de ciclo.', images: ['assets/images/products/maq3.webp', 'assets/images/general/img1.webp'] }
  ]}
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
      return fetch('/admin/api/contenido.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': _csrfToken() },
        body: JSON.stringify({ clave: section, valor: data })
      }).then(r => r.json()).then(json => {
        if (json.ok) {
          try { const bc = new BroadcastChannel('dematiq-live'); bc.postMessage({ section }); bc.close(); } catch {}
        }
        return json;
      });
    }
    return Promise.resolve({ ok: true });
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

// ── Admin Lightbox ────────────────────────────────────────────────────
(function () {
  const style = document.createElement('style');
  style.textContent = `
    .admin-lb { position:fixed;inset:0;z-index:99999;display:none;align-items:center;justify-content:center; }
    .admin-lb.open { display:flex; }
    .admin-lb-bd { position:absolute;inset:0;background:rgba(0,0,0,.88);backdrop-filter:blur(5px); }
    .admin-lb-frame { position:relative;z-index:1;max-width:92vw;max-height:92vh;display:flex;align-items:center;justify-content:center;animation:alb-in .18s ease; }
    @keyframes alb-in { from{opacity:0;transform:scale(.92)} to{opacity:1;transform:scale(1)} }
    .admin-lb-img { max-width:88vw;max-height:88vh;object-fit:contain;border-radius:10px;box-shadow:0 24px 64px rgba(0,0,0,.7);display:block; }
    .admin-lb-close { position:absolute;top:-14px;right:-14px;width:36px;height:36px;border-radius:50%;background:#fff;border:none;cursor:pointer;font-size:14px;font-weight:700;color:#222;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 10px rgba(0,0,0,.3);transition:background .15s,transform .15s; }
    .admin-lb-close:hover { background:#e53;color:#fff;transform:scale(1.12); }
    .admin-lb-caption { position:absolute;bottom:-34px;left:0;right:0;text-align:center;color:rgba(255,255,255,.7);font-size:.8rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
    .img-thumb img, .slide-img-preview img, img.img-preview { cursor:zoom-in;transition:opacity .15s; }
    .img-thumb:hover img, .slide-img-preview:hover img { opacity:.82; }
    img.img-preview:hover { opacity:.82; }
    .img-thumb { position:relative; }
    .slide-img-preview { position:relative; }
    .alb-zoom-icon { position:absolute;inset:0;display:flex;align-items:center;justify-content:center;pointer-events:none;opacity:0;transition:opacity .18s; }
    .alb-zoom-icon svg { width:36px;height:36px;filter:drop-shadow(0 2px 6px rgba(0,0,0,.5)); }
    .img-thumb:hover .alb-zoom-icon, .slide-img-preview:hover .alb-zoom-icon { opacity:1; }
  `;
  document.head.appendChild(style);

  const lb = document.createElement('div');
  lb.className = 'admin-lb';
  lb.innerHTML = `<div class="admin-lb-bd"></div><div class="admin-lb-frame"><button class="admin-lb-close" aria-label="Cerrar">&#10005;</button><img class="admin-lb-img" src="" alt=""><div class="admin-lb-caption"></div></div>`;
  document.body.appendChild(lb);

  const lbImg     = lb.querySelector('.admin-lb-img');
  const lbCaption = lb.querySelector('.admin-lb-caption');

  function open(src, caption) {
    lbImg.src = src;
    lbCaption.textContent = caption || '';
    lb.classList.add('open');
    document.body.style.overflow = 'hidden';
  }
  function close() { lb.classList.remove('open'); document.body.style.overflow = ''; }

  lb.querySelector('.admin-lb-bd').addEventListener('click', close);
  lb.querySelector('.admin-lb-close').addEventListener('click', close);
  document.addEventListener('keydown', e => { if (e.key === 'Escape') close(); });

  const zoomSVG = `<svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>`;

  function addHint(container) {
    if (!container || container.querySelector('.alb-zoom-icon')) return;
    const hint = document.createElement('div');
    hint.className = 'alb-zoom-icon';
    hint.innerHTML = zoomSVG;
    container.appendChild(hint);
  }

  // Delegated click — handles dynamically rendered images
  document.addEventListener('click', e => {
    const img = e.target.closest('img');
    if (!img || !img.src || img.src.startsWith('data:')) return;

    const inThumb    = img.closest('.img-thumb');
    const inSlide    = img.closest('.slide-img-preview');
    const isGridImg  = img.classList.contains('img-preview');

    if (inThumb || inSlide || isGridImg) {
      e.preventDefault();
      open(img.src, img.alt || img.getAttribute('title') || '');
    }
  });

  // Delegated hover — add zoom hint icon dynamically to containers
  document.addEventListener('mouseover', e => {
    const thumb = e.target.closest('.img-thumb');
    const slide = e.target.closest('.slide-img-preview');
    if (thumb && thumb.querySelector('img')) addHint(thumb);
    if (slide && slide.querySelector('img')) addHint(slide);
  }, { passive: true });

  // imagenes.php grid: add zoom cursor & lightbox via parent wrapper
  document.addEventListener('mouseover', e => {
    const img = e.target.closest('img.img-preview');
    if (img) img.title = img.title || 'Click para ampliar';
  }, { passive: true });
})();

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
    projects: '<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>',
    gear:     '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/>',
    shield:   '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
    image:    '<rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21,15 16,10 5,21"/>'
  },

  init(activePage, bp, rp) {
    const sidebar = document.querySelector('.admin-sidebar');
    if (!sidebar) return;

    const pages = [
      { href: `${bp}dashboard.php`,         icon: 'home',     text: 'Dashboard',  key: 'dashboard' },
      { href: `${bp}pages/inicio.php`,      icon: 'monitor',  text: 'Inicio',     key: 'inicio' },
      { href: `${bp}pages/nosotros.php`,    icon: 'users',    text: 'Nosotros',   key: 'nosotros' },
      { href: `${bp}pages/industrias.php`,  icon: 'layers',   text: 'Industrias', key: 'industrias' },
      { href: `${bp}pages/contacto.php`,    icon: 'phone',    text: 'Contacto',   key: 'contacto' },
      { href: `${bp}pages/partners.php`,    icon: 'star',     text: 'Socios',     key: 'partners' },
      { href: `${bp}pages/servicios.php`,   icon: 'tool',     text: 'Servicios',  key: 'servicios' },
      { href: `${bp}pages/proyectos.php`,   icon: 'projects', text: 'Proyectos',  key: 'proyectos' }
    ];
    const maquinas = [
      { href: `${bp}pages/maquinas.php`,    icon: 'gear',     text: 'Máquinas',   key: 'maquinas' }
    ];
    const tools = [
      { href: `${bp}pages/usuarios.php`,    icon: 'users',    text: 'Usuarios',   key: 'usuarios' },
      { href: `${bp}pages/accesos.php`,     icon: 'shield',   text: 'Accesos',    key: 'accesos' },
      { href: `${bp}pages/imagenes.php`,    icon: 'image',    text: 'Imágenes',   key: 'imagenes' }
    ];

    const link = (n) => {
      const cls = activePage === n.key ? ' class="active"' : '';
      return `<a href="${n.href}"${cls} title="${n.text}">${this.icon(this.ICONS[n.icon])}<span>${n.text}</span></a>`;
    };

    sidebar.innerHTML = `
      <div class="admin-logo">
        <img src="${rp}assets/images/logos/logo1.webp" class="admin-logo-full" alt="DEMATIQ">
        <button class="sidebar-collapse-btn" id="sidebar-collapse" title="Colapsar menú" aria-label="Colapsar menú">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
        </button>
      </div>
      <nav class="admin-nav">
        <div class="admin-nav-section"><span>Páginas</span></div>
        ${pages.map(link).join('')}
        <div class="admin-nav-section" style="margin-top:10px"><span>Máquinas</span></div>
        ${maquinas.map(link).join('')}
        <div class="admin-nav-section" style="margin-top:10px"><span>Herramientas</span></div>
        ${tools.map(link).join('')}
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
