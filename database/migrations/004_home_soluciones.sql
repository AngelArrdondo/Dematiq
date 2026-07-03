USE dematiq_db;

-- Agrega el bloque soluciones a la entrada home existente
UPDATE contenido
SET valor = JSON_MERGE_PATCH(valor, '{"soluciones":{
  "titulo":"Nuestras Soluciones Y Servicios",
  "featured":[
    {"titulo":"Servicios de ingeniería","imagen":"assets/images/general/servicios.webp","href":"pages/servicios/servicios.html"},
    {"titulo":"Manufactura","subtitulo":"Maquinados industriales","descripcion":"Diseño 2D y 3D","imagen":"assets/images/general/manufactura.png","href":"pages/manufactura/maqindus.html"},
    {"titulo":"Ensamble","imagen":"assets/images/general/ensamble.png","href":"pages/ensamble/ensamble.html"}
  ],
  "machines":[
    {"titulo":"Máquinas de control de torque","imagen":"assets/images/general/maquinas de control de torque.webp","href":"pages/maquinas/maqcontrol.html"},
    {"titulo":"Máquinas probadoras de fuga","imagen":"assets/images/general/maquinas probadoras de fuga.webp","href":"pages/maquinas/maqprob.html"},
    {"titulo":"Máquinas de inspección","imagen":"assets/images/general/maquinas de inspeccion.webp","href":"pages/maquinas/maqinspe.html"},
    {"titulo":"Máquinas de limpieza","imagen":"assets/images/general/maquina de limpieza.png","href":"pages/maquinas/maclim.html"},
    {"titulo":"Máquinas de marcado","imagen":"assets/images/general/maquinas de marcado.webp","href":"pages/maquinas/maqmar.html"},
    {"titulo":"Celdas robóticas","imagen":"assets/images/general/celdas roboticas.webp","href":"pages/maquinas/macrobot.html"}
  ]
}}')
WHERE clave = 'home';
