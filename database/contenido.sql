USE dematiq_db;

CREATE TABLE IF NOT EXISTS contenido (
  clave          VARCHAR(50)  PRIMARY KEY,
  valor          JSON         NOT NULL,
  actualizado_en DATETIME     DEFAULT NOW() ON UPDATE NOW()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO contenido (clave, valor) VALUES
('nosotros', '{
  "hero": {
    "tag": "Conócenos",
    "h1": "Sobre Nosotros",
    "subtitle": "Empresa mexicana especializada en automatización y ensamble industrial con más de 10 años de experiencia."
  },
  "p1": "En DEMATIQ somos una empresa especializada en soluciones tecnológicas e industriales enfocadas en optimizar los procesos de manufactura, automatización y desarrollo sostenible.",
  "p2": "Trabajamos de la mano con nuestros clientes y socios estratégicos para ofrecer innovación, eficiencia y calidad en cada uno de nuestros proyectos.",
  "mision": "Brindar soluciones integrales en automatización y desarrollo tecnológico que impulsen la productividad y competitividad de nuestros clientes.",
  "vision": "Ser líderes en innovación tecnológica y un referente en la transformación industrial a nivel nacional e internacional.",
  "valores": "Compromiso, innovación, calidad, trabajo en equipo y responsabilidad social empresarial."
}'),
('contacto', '{
  "email": "ventas@dematiq.com.mx",
  "whatsapp": "+52 (442) 721-4891",
  "whatsappNum": "524427214891",
  "horario": "Lun – Vie · 8:30 – 18:00",
  "direccion": "Calle Jardín de la Alabanza 2049, Jardines del Sol, Querétaro, Qro., México",
  "mapCoords": "20.621222,-100.470500",
  "social": {
    "facebook": "#",
    "instagram": "#",
    "linkedin": "#",
    "youtube": "#"
  }
}'),
('partners', '[
  {"nombre":"Danfoss",    "logo":"assets/images/partners/Danfoss.jpg",  "url":"https://www.danfoss.com/es-mx"},
  {"nombre":"Eaton",      "logo":"assets/images/partners/Eaton.png",    "url":"https://www.eaton.com/mx/es-mx.html"},
  {"nombre":"Metecno",    "logo":"assets/images/partners/METECNO.png",  "url":"https://metecnomexico.com"},
  {"nombre":"RPK México", "logo":"assets/images/partners/RPK.jpg",      "url":"https://rpk-global.com/es/rpk-mexico"},
  {"nombre":"TT Green",   "logo":"assets/images/partners/TTGREEN.png",  "url":"https://www.proveedores-greenmetals.com"},
  {"nombre":"Calidra",    "logo":"assets/images/partners/CALIDRA.png",  "url":"https://www.calidra.com"},
  {"nombre":"Ampacet",    "logo":"assets/images/partners/AMPACET.png",  "url":"https://www.ampacet.com/spanish"}
]'),
('home', '{
  "hero": {
    "video":  "assets/videos/hero.mp4",
    "poster": "assets/images/general/index.webp",
    "badge":  "Bienvenido"
  }
}'),
('industrias', '[
  {"id":"automotriz",        "nombre":"Automotriz",              "descripcion":"Colaboramos en proyectos de automatización, control de calidad y desarrollo de maquinaria para procesos de ensamblaje y pruebas."},
  {"id":"farmaceutica",      "nombre":"Farmacéutica",            "descripcion":"Desarrollamos sistemas de control ambiental, monitoreo de producción y soluciones de trazabilidad para laboratorios y plantas farmacéuticas."},
  {"id":"alimenticia",       "nombre":"Alimenticia",             "descripcion":"Implementamos soluciones de automatización y control de calidad para la producción de alimentos y bebidas."},
  {"id":"manufactura",       "nombre":"Manufactura",             "descripcion":"Colaboramos en la optimización de procesos, implementación de sistemas de control y desarrollo de maquinaria especializada."},
  {"id":"electronica",       "nombre":"Electrónica y Eléctrica", "descripcion":"Implementamos soluciones de automatización y control de calidad para la producción de dispositivos electrónicos."},
  {"id":"electrodomesticos", "nombre":"Electrodomésticos",       "descripcion":"Desarrollamos soluciones de automatización y control de calidad para la producción de electrodomésticos."},
  {"id":"alimentos",         "nombre":"Alimentos y Bebidas",     "descripcion":"Soluciones de envasado, etiquetado y control de procesos para líneas de producción de alimentos y bebidas."},
  {"id":"aeroespacial",      "nombre":"Aeroespacial",            "descripcion":"Desarrollamos soluciones de automatización y control de calidad para la producción de componentes aeroespaciales."}
]'),
('servicios', '[
  {"id":"plc",           "nombre":"Programación de PLC",                     "image":"assets/images/general/img1.png"},
  {"id":"hmi",           "nombre":"Programación de HMI, SCADA",              "image":"assets/images/general/img2.png"},
  {"id":"vision",        "nombre":"Programación de Sistemas de Visión",       "image":"assets/images/general/img3.png"},
  {"id":"servo",         "nombre":"Programación de Servomotores",             "image":"assets/images/general/img1.png"},
  {"id":"diagramas",     "nombre":"Diseño de Diagramas Eléctricos",           "image":"assets/images/general/img2.png"},
  {"id":"tableros",      "nombre":"Diseño de Tableros de Control",            "image":"assets/images/general/img3.png"},
  {"id":"modernizacion", "nombre":"Modernización de Maquinaria",              "image":"assets/images/general/img1.png"},
  {"id":"instalaciones", "nombre":"Servicio de Instalaciones Eléctricas",     "image":"assets/images/general/img2.png"},
  {"id":"ingenieria",    "nombre":"Ingeniería Básica y de Detalle",           "image":"assets/images/general/img3.png"},
  {"id":"variadores",    "nombre":"Programación de Variadores de Frecuencia", "image":"assets/images/general/img1.png"}
]'),
('navegacion', '{
  "inicio": "Inicio",
  "nosotros": "Sobre Nosotros",
  "proyectos": "Proyectos",
  "industrias": "Industrias",
  "contacto": "Contacto",
  "tienda": "Tienda",
  "tiendaUrl": "https://tienda.dematiq.com.mx/"
}')
ON DUPLICATE KEY UPDATE clave = clave;
