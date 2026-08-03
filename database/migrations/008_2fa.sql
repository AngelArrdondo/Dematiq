USE dematiq_db;

-- Verificación en dos pasos (TOTP) para el login de admin.
-- Run once: mysql -u dematiq_app -p dematiq_db < database/migrations/008_2fa.sql

-- totp_secret_pendiente guarda el secreto generado mientras el usuario
-- todavía no confirma el primer código (evita activar 2FA con un secreto
-- que el usuario nunca llegó a guardar en su app autenticadora).
ALTER TABLE usuarios
  ADD COLUMN totp_secret           VARCHAR(32)  DEFAULT NULL,
  ADD COLUMN totp_secret_pendiente VARCHAR(32)  DEFAULT NULL,
  ADD COLUMN totp_habilitado       TINYINT(1)   NOT NULL DEFAULT 0;

-- Códigos de un solo uso para cuando el usuario pierde acceso a su app
-- autenticadora. Se guardan hasheados, igual que password_hash.
CREATE TABLE IF NOT EXISTS usuario_codigos_recuperacion (
  id           INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
  usuario_id   INT UNSIGNED  NOT NULL,
  codigo_hash  CHAR(64)      NOT NULL,
  usado        TINYINT(1)    NOT NULL DEFAULT 0,
  creado_en    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  INDEX idx_usuario (usuario_id)
) ENGINE=InnoDB;

-- Estado intermedio entre "contraseña correcta" y "sesión creada" cuando
-- el usuario tiene 2FA activo. Vive máximo unos minutos (ver Auth::login).
CREATE TABLE IF NOT EXISTS sesiones_2fa_pendientes (
  id          INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
  usuario_id  INT UNSIGNED  NOT NULL,
  token       CHAR(64)      NOT NULL UNIQUE,
  ip          VARCHAR(45)   NOT NULL,
  user_agent  VARCHAR(255)  DEFAULT NULL,
  recordar    TINYINT(1)    NOT NULL DEFAULT 0,
  intentos    SMALLINT      NOT NULL DEFAULT 0,
  expira_en   DATETIME      NOT NULL,
  creado_en   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  INDEX idx_token   (token),
  INDEX idx_expira  (expira_en)
) ENGINE=InnoDB;
