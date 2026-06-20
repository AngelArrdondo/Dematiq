-- Migration 002: Split nombre into name parts + add contact fields
-- Run once on existing installs: mysql -u dematiq_app -p dematiq_db < database/migrations/002_name_split.sql

ALTER TABLE usuarios
  ADD COLUMN primer_nombre    VARCHAR(60)  NOT NULL DEFAULT ''   AFTER nombre,
  ADD COLUMN segundo_nombre   VARCHAR(60)           DEFAULT NULL AFTER primer_nombre,
  ADD COLUMN apellido_paterno VARCHAR(60)  NOT NULL DEFAULT ''   AFTER segundo_nombre,
  ADD COLUMN apellido_materno VARCHAR(60)           DEFAULT NULL AFTER apellido_paterno,
  ADD COLUMN email_contacto   VARCHAR(150)          DEFAULT NULL AFTER apellido_materno,
  ADD COLUMN telefono         VARCHAR(30)           DEFAULT NULL AFTER email_contacto,
  ADD COLUMN foto             VARCHAR(255)          DEFAULT NULL AFTER telefono;
