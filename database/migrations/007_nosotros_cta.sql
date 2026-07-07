USE dematiq_db;

-- Semilla del CTA final de Nosotros con el texto actualmente publicado en
-- pages/corporativo/nosotros.html, para que el panel admin no muestre los
-- campos vacíos la primera vez que se abre la página.
-- Run once: mysql -u dematiq_app -p dematiq_db < database/migrations/007_nosotros_cta.sql

UPDATE contenido
SET valor = JSON_MERGE_PATCH(valor, '{"cta":{
  "titulo":"¿Listo para automatizar tu proceso?",
  "subtitulo":"Cuéntanos tu proyecto y te ayudamos a encontrar la solución a la medida de tu empresa.",
  "btn1Text":"Contáctanos",
  "btn1Href":"Contacto.html",
  "btn2Text":"Ver proyectos",
  "btn2Href":"soluciones.html"
}}')
WHERE clave = 'nosotros';
