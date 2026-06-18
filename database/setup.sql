-- ============================================================
--  DEMATIQ — Base de datos de administración
--  Ejecutar como root: mysql -u root -p < database/setup.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS dematiq_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE dematiq_db;

-- ------------------------------------------------------------
--  TABLA: usuarios
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS usuarios (
  id                INT UNSIGNED     AUTO_INCREMENT PRIMARY KEY,
  username          VARCHAR(60)      NOT NULL UNIQUE,
  password_hash     VARCHAR(255)     NOT NULL,
  nombre            VARCHAR(100)     NOT NULL,
  primer_nombre     VARCHAR(60)      NOT NULL DEFAULT '',
  segundo_nombre    VARCHAR(60)               DEFAULT NULL,
  apellido_paterno  VARCHAR(60)      NOT NULL DEFAULT '',
  apellido_materno  VARCHAR(60)               DEFAULT NULL,
  email_contacto    VARCHAR(150)              DEFAULT NULL,
  telefono          VARCHAR(30)               DEFAULT NULL,
  foto              VARCHAR(255)              DEFAULT NULL,
  activo            TINYINT(1)       NOT NULL DEFAULT 1,
  intentos_fallidos SMALLINT         NOT NULL DEFAULT 0,
  bloqueado_hasta   DATETIME                  DEFAULT NULL,
  ultimo_acceso     DATETIME                  DEFAULT NULL,
  creado_en         TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP
                                              ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
--  TABLA: sesiones  (sesiones del lado del servidor)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS sesiones (
  id          INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
  usuario_id  INT UNSIGNED  NOT NULL,
  token       CHAR(64)      NOT NULL UNIQUE,
  ip          VARCHAR(45)   NOT NULL,
  user_agent  VARCHAR(255)  DEFAULT NULL,
  expira_en   DATETIME      NOT NULL,
  creado_en   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  INDEX idx_token   (token),
  INDEX idx_expira  (expira_en)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
--  TABLA: log_accesos  (auditoría completa)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS log_accesos (
  id          INT UNSIGNED                          AUTO_INCREMENT PRIMARY KEY,
  usuario_id  INT UNSIGNED                          DEFAULT NULL,
  username    VARCHAR(60)                           DEFAULT NULL,
  ip          VARCHAR(45)                           NOT NULL,
  user_agent  VARCHAR(255)                          DEFAULT NULL,
  resultado   ENUM('exito','fallo','bloqueado')     NOT NULL,
  creado_en   TIMESTAMP                             NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_ip      (ip),
  INDEX idx_creado  (creado_en),
  INDEX idx_result  (resultado)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
--  USUARIO INICIAL
--  Genera el hash con: php -r "echo password_hash('TU_PASSWORD', PASSWORD_BCRYPT, ['cost'=>12]);"
--  y reemplaza el valor de password_hash antes de ejecutar este script.
-- ------------------------------------------------------------
INSERT INTO usuarios (username, password_hash, nombre)
VALUES (
  'admin',
  'REEMPLAZA_CON_HASH_BCRYPT',
  'Administrador DEMATIQ'
)
ON DUPLICATE KEY UPDATE id = id;

-- ------------------------------------------------------------
--  USUARIO DE APLICACIÓN  (permisos mínimos, nunca usar root)
--  Genera una contraseña segura y reemplaza TU_CONTRASEÑA_BD
--  tanto aquí como en includes/conexion.php antes de ejecutar.
-- ------------------------------------------------------------
CREATE USER IF NOT EXISTS 'dematiq_app'@'localhost'
  IDENTIFIED BY 'TU_CONTRASEÑA_BD';

GRANT SELECT, INSERT, UPDATE, DELETE
  ON dematiq_db.*
  TO 'dematiq_app'@'localhost';

FLUSH PRIVILEGES;
