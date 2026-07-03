-- Migration 003: Cambiar home.hero de array de slides a objeto de video hero
-- Run once: mysql -u dematiq_app -p dematiq_db < database/migrations/003_home_hero_video.sql

UPDATE contenido
SET valor = JSON_SET(valor, '$.hero', JSON_OBJECT(
  'video',  'assets/videos/hero.mp4',
  'poster', 'assets/images/general/index.webp',
  'badge',  'Bienvenido'
))
WHERE clave = 'home';
