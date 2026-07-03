// Datos de Industrias — intro ("qué es"), Áreas de planta y Temas técnicos por sector.
// Cada área/tema tiene { n: nombre, d: descripción }. La descripción se revela al
// pasar el mouse o dar clic sobre cada subtema (ver industrias.html).
(function () {
  // Alimenticia y Bebidas comparte contenido entre "alimenticia" y "alimentos".
  const alimentosAreas = [
    { n: "Procesamiento y Preparación", d: "Sistemas automatizados para el transporte, mezcla y dosificación de ingredientes con control de flujo constante." },
    { n: "Líneas de Llenado y Tapado", d: "Integración de estaciones para embotellado y envasado a alta velocidad, minimizando el derrame y desperdicio de producto." },
    { n: "Zona de Etiquetado y Codificado", d: "Automatización en la aplicación precisa de etiquetas y marcaje de lotes a velocidades de producción masiva." },
    { n: "Final de Línea (Encajado)", d: "Celdas robotizadas para la formación de cajas y el posicionamiento de producto terminado." },
    { n: "Paletizado Automatizado", d: "Sistemas de manipulación robótica para la estiba y flejado de tarimas listas para su despacho." }
  ];
  const alimentosTemas = [
    { n: "Robótica Grado Alimenticio", d: "Integración de robots con recubrimientos epóxicos y lubricantes grado H1, aptos para contacto directo o zonas de salpicadura." },
    { n: "Visión Artificial", d: "Inspección automática de niveles de llenado, verificación de cierre de tapas y detección de cuerpos extraños." },
    { n: "Control y Automatización (PLC/HMI)", d: "Gestión de secuencias complejas de línea para la sincronización entre transportadores, llenadoras y empacadoras." },
    { n: "Seguridad Industrial (Safety)", d: "Implementación de dispositivos de seguridad aptos para ambientes de lavado a presión (Washdown)." },
    { n: "Comunicaciones Industriales", d: "Arquitectura de red para la integración de sensores de flujo, temperatura y presión hacia el sistema central de control." },
    { n: "Electroneumática", d: "Diseño de circuitos neumáticos para actuadores de alta velocidad, garantizando la eficiencia en el consumo de aire comprimido." }
  ];
  const alimentosIntro = "Este sector requiere soluciones que prioricen la inocuidad, la alta velocidad de procesamiento y la reducción de tiempos de parada. Nuestras automatizaciones están diseñadas para operar en ambientes exigentes, cumpliendo con las regulaciones de seguridad alimentaria sin comprometer el rendimiento de la línea.";

  window.DEMATIQ_INDUSTRIAS = [
    {
      id: "automotriz",
      nombre: "Automotriz",
      intro: "La industria automotriz representa uno de los entornos de producción más exigentes a nivel global, donde la precisión, la trazabilidad y la eficiencia de los ciclos de trabajo son críticos para mantener la competitividad. Nuestras soluciones de automatización están diseñadas para optimizar la cadena de suministro y el ensamblaje, garantizando altos estándares de calidad bajo esquemas de producción flexible.",
      areas: [
        { n: "Línea de Estampado y Prensa", d: "Automatización en la carga y descarga de prensas, transferencia de piezas entre estaciones y manejo de lámina con sistemas de vacío." },
        { n: "Línea de Carrocería (Body Shop)", d: "Celdas de soldadura robotizada (punteo y soldadura láser), manipulación de chasis y sistemas de ensamblaje de componentes estructurales." },
        { n: "Línea de Pintura y Acabado", d: "Automatización en procesos de sellado, aplicación de pintura mediante robots de alta precisión y control de atmósferas controladas." },
        { n: "Ensamblaje Final", d: "Estaciones de ensamble automatizado de motores, transmisiones, tableros y componentes electrónicos, incluyendo sistemas de apretado controlado (torquimetría)." },
        { n: "Área de Logística y Almacén", d: "Sistemas de transporte automatizado (conveyors), gestión de tarimas y soluciones de trazabilidad mediante lectura de códigos (QR/RFID)." }
      ],
      temas: [
        { n: "Robótica Industrial", d: "Programación, integración y optimización de trayectorias en robots de diversas marcas (ABB, Fanuc, KUKA, Yaskawa)." },
        { n: "Control y Automatización (PLC/HMI)", d: "Desarrollo de lógica de control, programación de PLCs para la gestión de procesos, y diseño de interfaces hombre-máquina (HMI) intuitivas para el operador." },
        { n: "Visión Artificial", d: "Implementación de sistemas de cámaras para inspección de calidad, verificación de presencia de piezas, guiado de robots y lectura de códigos de identificación." },
        { n: "Seguridad Industrial (Safety)", d: "Diseño de sistemas de seguridad basados en normas internacionales (ISO 13849), incluyendo cortinas de luz, escáneres láser y paro de emergencia, garantizando la integridad del personal." },
        { n: "Comunicaciones Industriales", d: "Integración de redes de campo (Profinet, EtherCAT, Ethernet/IP) para asegurar que todos los dispositivos de la planta se comuniquen eficientemente y envíen datos al sistema central." },
        { n: "Electroneumática y Actuación", d: "Diseño y montaje de sistemas de potencia neumática, cilindros de alta velocidad y actuadores eléctricos para el movimiento de mecanismos." }
      ]
    },
    {
      id: "farmaceutica",
      nombre: "Farmacéutica",
      intro: "La industria farmacéutica demanda un nivel de precisión y esterilidad inigualable, donde el cumplimiento normativo y la integridad del producto son los ejes rectores de la producción. Nuestras soluciones garantizan procesos automatizados que eliminan el error humano, manteniendo los más altos estándares de higiene y validación requeridos por el sector salud.",
      areas: [
        { n: "Área de Pesaje y Dosificación", d: "Automatización de básculas de alta precisión y sistemas de dosificación automática para la mezcla exacta de compuestos químicos." },
        { n: "Líneas de Llenado y Sellado", d: "Celdas robotizadas para el manejo de viales, ampollas y blísteres en ambientes controlados (salas blancas)." },
        { n: "Zona de Empacado y Serialización", d: "Automatización del encajado final, etiquetado inteligente y sistemas de impresión para la trazabilidad unitaria." },
        { n: "Área de Inspección y Calidad", d: "Estaciones automatizadas para la verificación visual de integridad de sellos, niveles de llenado y presencia de producto." },
        { n: "Sistema de Gestión de Lotes", d: "Integración de estaciones para la codificación y rastreo individual de medicamentos para el cumplimiento de normativas." }
      ],
      temas: [
        { n: "Validación GAMP 5", d: "Documentación técnica integral para procesos de Calificación de Diseño (DQ), Instalación (IQ) y Operación (OQ)." },
        { n: "Diseño Sanitario", d: "Implementación de acero inoxidable AISI 316L y acabados grado farmacéutico, resistentes a protocolos de limpieza CIP/SIP." },
        { n: "Visión Artificial (OCR/OCV)", d: "Lectura e inspección de datos variables (lotes, fechas de caducidad) para asegurar la trazabilidad unitaria." },
        { n: "Control de Movimiento", d: "Sincronización precisa de ejes en líneas de empaque de alta cadencia." },
        { n: "Seguridad Industrial (Safety)", d: "Sistemas de seguridad adaptados para entornos críticos, asegurando la hermeticidad de las celdas de trabajo." },
        { n: "Comunicaciones Industriales", d: "Integración de sistemas de control bajo protocolos robustos para monitoreo remoto y registro de datos." }
      ]
    },
    {
      id: "alimenticia",
      nombre: "Alimenticia",
      intro: alimentosIntro,
      areas: alimentosAreas,
      temas: alimentosTemas
    },
    {
      id: "manufactura",
      nombre: "Manufactura",
      intro: "Este sector se caracteriza por procesos productivos diversos y versátiles, donde la capacidad de adaptación y la optimización de recursos son fundamentales. Nuestras soluciones de automatización están diseñadas para incrementar la flexibilidad de las líneas de ensamble, reduciendo los tiempos de ciclo y permitiendo una transición eficiente entre distintos modelos de productos.",
      areas: [
        { n: "Celdas de Maquinado", d: "Automatización en la carga y descarga de centros de mecanizado CNC (tornos y fresadoras), incrementando el aprovechamiento de la maquinaria." },
        { n: "Líneas de Ensamble Flexible", d: "Estaciones de trabajo modulares para el ensamblaje, transferencia y transporte de piezas mediante sistemas paletizados." },
        { n: "Procesos de Acabado", d: "Automatización de procesos de pintura, recubrimiento o pulido superficial mediante brazos robóticos de alta precisión." },
        { n: "Estaciones de Ensamble de Subcomponentes", d: "Sistemas automatizados para el montaje de piezas pequeñas y subconjuntos mecánicos." },
        { n: "Área de Empacado Industrial", d: "Celdas robotizadas para el encajado, etiquetado y preparación de producto final para su distribución." }
      ],
      temas: [
        { n: "Robótica Industrial", d: "Programación, integración y optimización de trayectorias en robots articulados de diversas marcas líderes." },
        { n: "Control y Automatización (PLC/HMI)", d: "Desarrollo de lógica de control, programación de PLCs para la gestión de procesos y diseño de interfaces hombre-máquina intuitivas." },
        { n: "Visión Artificial", d: "Implementación de sistemas de cámaras para inspección de calidad, verificación de presencia de piezas y guiado de robots." },
        { n: "Seguridad Industrial (Safety)", d: "Diseño de sistemas de seguridad basados en normas internacionales (ISO 13849), garantizando la integridad del personal." },
        { n: "Comunicaciones Industriales", d: "Integración de redes de campo para asegurar que todos los dispositivos de la planta se comuniquen eficientemente y envíen datos al sistema central." },
        { n: "Electroneumática", d: "Diseño y montaje de sistemas de potencia neumática, cilindros de alta velocidad y actuadores eléctricos." }
      ]
    },
    {
      id: "electronica",
      nombre: "Electrónica y Eléctrica",
      intro: "Este sector exige una precisión absoluta y un manejo cuidadoso de componentes altamente sensibles. Nuestras soluciones están orientadas a la automatización de procesos de ensamble micrométrico y pruebas funcionales, garantizando la calidad eléctrica y estructural en cada componente producido, bajo estándares de protección contra descargas.",
      areas: [
        { n: "Línea de Ensamble de PCBs", d: "Inserción automática de componentes electrónicos y estaciones de soldadura selectiva de alta precisión." },
        { n: "Estaciones de Pruebas Funcionales", d: "Implementación de sistemas automáticos (ICT/FCT) para la validación eléctrica completa de cada unidad producida." },
        { n: "Área de Manipulación Delicada", d: "Sistemas de sujeción mediante vacío suave para el manejo de componentes frágiles en entornos controlados." },
        { n: "Zona de Ensamble de Carcasas", d: "Integración de componentes electrónicos en sus envolventes mediante procesos automatizados de fijación." },
        { n: "Final de Línea y Empacado", d: "Sistemas para la verificación final, etiquetado y embalaje de dispositivos electrónicos." }
      ],
      temas: [
        { n: "Control de ESD (Descarga Electrostática)", d: "Diseño integral de celdas automatizadas con materiales disipativos y sistemas de tierra certificados para la protección de circuitos." },
        { n: "Visión Artificial de Alta Resolución", d: "Inspección microscópica para detección automática de fallas en soldaduras y alineación de componentes." },
        { n: "Control de Torque de Precisión", d: "Atornillado electrónico con retroalimentación en tiempo real para el ensamble de carcasas con tolerancias micrométricas." },
        { n: "Robótica Industrial", d: "Integración de robots de precisión para tareas de ensamble y manipulación en espacios reducidos." },
        { n: "Control y Automatización (PLC/HMI)", d: "Gestión de secuencias lógicas para la sincronización de procesos de ensamble y prueba eléctrica." },
        { n: "Comunicaciones Industriales", d: "Integración de redes para el monitoreo en tiempo real de los parámetros eléctricos durante el ensamble." }
      ]
    },
    {
      id: "electrodomesticos",
      nombre: "Electrodomésticos",
      intro: "Esta industria se centra en la producción masiva de componentes electromecánicos, donde la estandarización, la repetibilidad y el control de calidad final determinan el éxito comercial. Nuestras automatizaciones enfocan su valor en la integración sincronizada de motores, carcasas y sistemas electrónicos, asegurando un ensamble perfecto.",
      areas: [
        { n: "Líneas de Ensamble de Motores", d: "Procesos automatizados de bobinado, montaje y ensamblaje de componentes electromecánicos internos." },
        { n: "Línea de Montaje de Estructuras", d: "Integración de carcasas, paneles y componentes plásticos mediante sistemas de transferencia sincronizados." },
        { n: "Estaciones de Final de Línea (EOL Test)", d: "Pruebas automatizadas de hermeticidad (leak test), funcionamiento eléctrico y validación acústica." },
        { n: "Zona de Fijación y Ensamble", d: "Sistemas automatizados de atornillado, remachado y ensamble de piezas estructurales." },
        { n: "Área de Logística y Trazabilidad", d: "Seguimiento integral de variantes de modelos mediante sistemas de lectura RFID/QR durante todo el proceso." }
      ],
      temas: [
        { n: "Sistemas Poka-Yoke", d: "Dispositivos de detección física y lógica integrados para eliminar la posibilidad de error humano en el proceso de ensamble." },
        { n: "Automatización de Fijación", d: "Sistemas de atornillado automático con trazabilidad de torque aplicada para asegurar la integridad mecánica del producto." },
        { n: "Visión Artificial", d: "Verificación automatizada de la presencia de piezas, alineación de componentes y calidad visual del ensamble." },
        { n: "Control y Automatización (PLC/HMI)", d: "Desarrollo de lógica de control para la gestión de líneas de ensamble síncronas y asíncronas." },
        { n: "Seguridad Industrial (Safety)", d: "Diseño de esquemas de seguridad industrial adaptados para proteger al personal en líneas de ensamble de alto flujo." },
        { n: "Electroneumática", d: "Implementación de actuadores para el movimiento, sujeción y ensamble de componentes en la línea de producción." }
      ]
    },
    {
      id: "alimentos",
      nombre: "Alimentos y Bebidas",
      intro: alimentosIntro,
      areas: alimentosAreas,
      temas: alimentosTemas
    },
    {
      id: "aeroespacial",
      nombre: "Aeroespacial",
      intro: "La industria aeroespacial es el paradigma de la excelencia, donde la trazabilidad absoluta y la precisión dimensional definen la calidad. Desarrollamos soluciones de automatización que cumplen con las tolerancias más estrictas, garantizando que cada componente estructural cumpla con los estándares de seguridad crítica del sector.",
      areas: [
        { n: "Estructuras (Fuselaje/Alas)", d: "Celdas robóticas para el remachado automático, barrenado de precisión y sujeción de componentes de grandes dimensiones." },
        { n: "Manufactura de Compuestos", d: "Automatización en el corte y posicionamiento de fibras de carbono o materiales avanzados mediante sistemas guiados por visión." },
        { n: "Línea de Ensamble de Aviónica", d: "Estaciones de ensamble para componentes electrónicos críticos con control de torque y trazabilidad." },
        { n: "Área de Metrología", d: "Sistemas de inspección automatizada por láser para la verificación dimensional de subensambles." },
        { n: "Logística de Partes Críticas", d: "Sistemas de gestión de materiales con trazabilidad total mediante tecnologías de identificación avanzada." }
      ],
      temas: [
        { n: "Robótica Industrial", d: "Programación de robots de alta repetibilidad para operaciones de mecanizado, taladrado y ensamble estructural." },
        { n: "Trazabilidad Total", d: "Registro inalterable de cada parámetro de proceso, incluyendo par de apriete, operador/robot y fecha de ejecución." },
        { n: "Visión Artificial", d: "Sistemas de metrología automatizada para el guiado de robots y la validación dimensional con tolerancias micrométricas." },
        { n: "Seguridad Industrial (Safety)", d: "Implementación de sistemas de seguridad de alto nivel (SIL-3) para la protección del personal y la integridad del componente." },
        { n: "Comunicaciones Industriales", d: "Integración de redes de alta velocidad para el intercambio de datos masivos durante los procesos de inspección." },
        { n: "Electroneumática", d: "Sistemas de actuación eléctrica y neumática de alta precisión para el control de fuerzas y movimientos en procesos de ensamble crítico." }
      ]
    }
  ];
})();
