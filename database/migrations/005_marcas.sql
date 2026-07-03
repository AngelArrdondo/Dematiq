USE dematiq_db;

-- Marcas Asociadas (carrusel en inicio) — separado de los Socios (nosotros.html)
INSERT INTO contenido (clave, valor) VALUES ('marcas', '[
  {"nombre":"Siemens",              "logo":"assets/images/partners/Siemens.svg"},
  {"nombre":"Rockwell Automation",  "logo":"assets/images/partners/Rockwell_Automation.svg"},
  {"nombre":"Keyence",              "logo":"assets/images/partners/Keyence.svg"},
  {"nombre":"Omron",                "logo":"assets/images/partners/Omron.svg"},
  {"nombre":"Delta",                "logo":"assets/images/partners/Delta.svg"},
  {"nombre":"Mitsubishi Electric",  "logo":"assets/images/partners/Mitsubishi_Electric.svg"},
  {"nombre":"Schneider Electric",   "logo":"assets/images/partners/Schneider_Electric.svg"},
  {"nombre":"ABB",                  "logo":"assets/images/partners/ABB.svg"},
  {"nombre":"Yaskawa",              "logo":"assets/images/partners/Yaskawa.svg"},
  {"nombre":"Fanuc",                "logo":"assets/images/partners/Fanuc.svg"},
  {"nombre":"KUKA",                 "logo":"assets/images/partners/KUKA.svg"},
  {"nombre":"EPSON",                "logo":"assets/images/partners/EPSON.svg"},
  {"nombre":"Banner Engineering",   "logo":"assets/images/partners/banner_engg.webp"},
  {"nombre":"IFM",                  "logo":"assets/images/partners/ifm.svg"},
  {"nombre":"Balluff",              "logo":"assets/images/partners/Balluff.svg"},
  {"nombre":"Turck",                "logo":"assets/images/partners/turck.svg"},
  {"nombre":"SICK",                 "logo":"assets/images/partners/SICK.svg"},
  {"nombre":"Pepperl+Fuchs",        "logo":"assets/images/partners/Pepperl+Fuchs.svg"}
]')
ON DUPLICATE KEY UPDATE clave = clave;
