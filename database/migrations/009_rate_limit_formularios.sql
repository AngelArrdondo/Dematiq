USE dematiq_db;

-- Rate limiting + registro de honeypot para formularios públicos
-- (api/contacto.php, pages/corporativo/forgot-password.php).
-- Run once: mysql -u dematiq_app -p dematiq_db < database/migrations/009_rate_limit_formularios.sql

CREATE TABLE IF NOT EXISTS log_formularios (
  id         INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
  tipo       VARCHAR(30)   NOT NULL,
  ip         VARCHAR(45)   NOT NULL,
  resultado  ENUM('enviado','honeypot','bloqueado')  NOT NULL,
  creado_en  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_ip_tipo (ip, tipo, creado_en)
) ENGINE=InnoDB;
