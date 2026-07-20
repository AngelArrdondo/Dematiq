-- ============================================================
--  DEMATIQ — Creación de base de datos + usuario de aplicación
--
--  SOLO para entornos con acceso root a MySQL (local / VPS propio).
--  Ejecutar como root: mysql -u root -p < database/setup-root-only.sql
--
--  En hosting compartido (Hostinger) NO uses este archivo — ahí no
--  hay privilegios para CREATE USER/GRANT. En su lugar, crea la
--  base de datos y el usuario desde hPanel → Bases de datos MySQL,
--  y luego importa database/setup.sql (y los demás .sql) desde
--  phpMyAdmin.
-- ============================================================

CREATE DATABASE IF NOT EXISTS dematiq_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

-- Usuario de aplicación (permisos mínimos, nunca usar root en producción).
-- Genera una contraseña segura y reemplaza TU_CONTRASEÑA_BD aquí y en
-- includes/conexion.php (o en la variable de entorno DB_PASS) antes de
-- ejecutar.
CREATE USER IF NOT EXISTS 'dematiq_app'@'localhost'
  IDENTIFIED BY 'TU_CONTRASEÑA_BD';

GRANT SELECT, INSERT, UPDATE, DELETE
  ON dematiq_db.*
  TO 'dematiq_app'@'localhost';

FLUSH PRIVILEGES;
