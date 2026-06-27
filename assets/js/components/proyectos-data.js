/* ══════════════════════════════════════════════════
   DATOS DE PROYECTOS (compartidos: inicio + listado + ficha)
   Los usan:
     · stage-carousel.js  → "Proyectos destacados" del inicio (filtra destacado:true)
     · proyectos.js        → grid del listado (soluciones.html)
     · proyecto-detalle.js → ficha de detalle (proyecto.html)

   👉 A FUTURO (panel admin): este arreglo se genera desde la base de datos.
      Para destacar un proyecto en el inicio, basta con marcar  destacado: true.
      El inicio toma de forma automática su imagen, título y descripción.

   ⚠️ CONTENIDO DE EJEMPLO: títulos, textos, sector, servicios, ubicación y año
      son PLACEHOLDERS. Reemplazar con la información real de cada proyecto.
      Se conservan 'cat' y 'badge' (tipo de máquina) reales para que los filtros
      y los contadores del listado sigan funcionando.
   ══════════════════════════════════════════════════ */

const _DESC_EJEMPLO = 'Aquí va una breve descripción del proyecto de ejemplo.';
const _SOL_EJEMPLO  = 'Aquí va la descripción del proyecto. Aquí se describe en qué consistió, qué se diseñó y qué se fabricó. Aquí va la descripción del proyecto de ejemplo.';
const _RES_EJEMPLO  = 'Aquí va información adicional del proyecto de ejemplo. Aquí va información adicional del proyecto de ejemplo.';

window.DEMATIQ_PROJECTS = [
  {
    id: 'proyecto-ejemplo-1', destacado: true,
    cat: 'robotica', badge: 'Robótica',
    title: 'Proyecto ejemplo 1',
    sector: 'Sector de ejemplo',
    servicios: 'Servicio de ejemplo · Servicio de ejemplo',
    ubicacion: 'Ciudad, País',
    anio: '20XX',
    img: '../../assets/images/products/en1.webp', alt: 'Imagen de proyecto de ejemplo',
    stat: '',
    desc: _DESC_EJEMPLO, solucion: _SOL_EJEMPLO, resultados: _RES_EJEMPLO, reto: ''
  },
  {
    id: 'proyecto-ejemplo-2',
    cat: 'torque', badge: 'Torque',
    title: 'Proyecto ejemplo 2',
    sector: 'Sector de ejemplo',
    servicios: 'Servicio de ejemplo · Servicio de ejemplo',
    ubicacion: 'Ciudad, País',
    anio: '20XX',
    img: '../../assets/images/general/t1.webp', alt: 'Imagen de proyecto de ejemplo',
    stat: '',
    desc: _DESC_EJEMPLO, solucion: _SOL_EJEMPLO, resultados: _RES_EJEMPLO, reto: ''
  },
  {
    id: 'proyecto-ejemplo-3', destacado: true,
    cat: 'inspeccion', badge: 'Inspección',
    title: 'Proyecto ejemplo 3',
    sector: 'Sector de ejemplo',
    servicios: 'Servicio de ejemplo · Servicio de ejemplo',
    ubicacion: 'Ciudad, País',
    anio: '20XX',
    img: '../../assets/images/general/inspeccion.webp', alt: 'Imagen de proyecto de ejemplo',
    stat: '',
    desc: _DESC_EJEMPLO, solucion: _SOL_EJEMPLO, resultados: _RES_EJEMPLO, reto: ''
  },
  {
    id: 'proyecto-ejemplo-4',
    cat: 'robotica', badge: 'Robótica',
    title: 'Proyecto ejemplo 4',
    sector: 'Sector de ejemplo',
    servicios: 'Servicio de ejemplo · Servicio de ejemplo',
    ubicacion: 'Ciudad, País',
    anio: '20XX',
    img: '../../assets/images/general/micro.webp', alt: 'Imagen de proyecto de ejemplo',
    stat: '',
    desc: _DESC_EJEMPLO, solucion: _SOL_EJEMPLO, resultados: _RES_EJEMPLO, reto: ''
  },
  {
    id: 'proyecto-ejemplo-5', destacado: true,
    cat: 'robotica', badge: 'Robótica',
    title: 'Proyecto ejemplo 5',
    sector: 'Sector de ejemplo',
    servicios: 'Servicio de ejemplo · Servicio de ejemplo',
    ubicacion: 'Ciudad, País',
    anio: '20XX',
    img: '../../assets/images/general/celdas.webp', alt: 'Imagen de proyecto de ejemplo',
    stat: '',
    desc: _DESC_EJEMPLO, solucion: _SOL_EJEMPLO, resultados: _RES_EJEMPLO, reto: ''
  },
  {
    id: 'proyecto-ejemplo-6', destacado: true,
    cat: 'fugas', badge: 'Probado de fugas',
    title: 'Proyecto ejemplo 6',
    sector: 'Sector de ejemplo',
    servicios: 'Servicio de ejemplo · Servicio de ejemplo',
    ubicacion: 'Ciudad, País',
    anio: '20XX',
    img: '../../assets/images/general/fuga.webp', alt: 'Imagen de proyecto de ejemplo',
    stat: '',
    desc: _DESC_EJEMPLO, solucion: _SOL_EJEMPLO, resultados: _RES_EJEMPLO, reto: ''
  },
  {
    id: 'proyecto-ejemplo-7',
    cat: 'marcado', badge: 'Marcado',
    title: 'Proyecto ejemplo 7',
    sector: 'Sector de ejemplo',
    servicios: 'Servicio de ejemplo · Servicio de ejemplo',
    ubicacion: 'Ciudad, País',
    anio: '20XX',
    img: '../../assets/images/products/maq3.webp', alt: 'Imagen de proyecto de ejemplo',
    stat: '',
    desc: _DESC_EJEMPLO, solucion: _SOL_EJEMPLO, resultados: _RES_EJEMPLO, reto: ''
  },
  {
    id: 'proyecto-ejemplo-8',
    cat: 'limpieza', badge: 'Limpieza',
    title: 'Proyecto ejemplo 8',
    sector: 'Sector de ejemplo',
    servicios: 'Servicio de ejemplo · Servicio de ejemplo',
    ubicacion: 'Ciudad, País',
    anio: '20XX',
    img: '../../assets/images/general/limpieza.webp', alt: 'Imagen de proyecto de ejemplo',
    stat: '',
    desc: _DESC_EJEMPLO, solucion: _SOL_EJEMPLO, resultados: _RES_EJEMPLO, reto: ''
  }
];
