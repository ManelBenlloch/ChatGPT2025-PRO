-- ============================================================================
-- SQL Schema for login3
-- Generated on: 2025-11-10
-- ============================================================================

SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

-- ============================================================================
-- Base de datos: login6_db
-- ============================================================================

CREATE DATABASE IF NOT EXISTS `login6_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `login6_db`;

-- ============================================================================
-- Tabla: users
-- ============================================================================

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fullname` varchar(255) NOT NULL COMMENT 'Nombre completo del usuario',
  `username` varchar(100) NOT NULL COMMENT 'Nombre de usuario único',
  `alias` varchar(50) NOT NULL COMMENT 'Alias único del usuario',
  `email` varchar(255) NOT NULL COMMENT 'Email único del usuario',
  `password_hash` varchar(255) NOT NULL COMMENT 'Hash de la contraseña (bcrypt)',
  `role` enum('user','personal','admin','root') NOT NULL DEFAULT 'user' COMMENT 'Rol del usuario',
  `is_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Usuario activo (1) o desactivado (0)',
  `email_verified` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Email verificado (1) o no (0)',
  `is_2fa_enabled` tinyint(1) NOT NULL DEFAULT '0' COMMENT '2FA activado (1) o no (0)',
  `verification_token` varchar(255) DEFAULT NULL COMMENT 'Token para verificación de email',
  `reset_token` varchar(255) DEFAULT NULL COMMENT 'Token para reseteo de contraseña',
  `reset_token_expires_at` datetime DEFAULT NULL COMMENT 'Fecha de expiración del token de reseteo',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Fecha de creación',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Fecha de última actualización',
  `last_login` timestamp NULL DEFAULT NULL COMMENT 'Fecha del último login',
  `deleted_at` datetime DEFAULT NULL COMMENT 'Fecha de eliminación (soft delete)',
  `require_password_change` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Requerir cambio de contraseña',
  `can_manage_users` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Permiso para gestionar usuarios',
  `can_delete_users` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Permiso para eliminar usuarios',
  `terms_accepted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Términos y condiciones aceptados',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `alias` (`alias`),
  UNIQUE KEY `email` (`email`),
  KEY `role` (`role`),
  KEY `email_verified` (`email_verified`),
  KEY `deleted_at` (`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla principal de usuarios';

-- ============================================================================
-- Tabla: activity_logs
-- ============================================================================

DROP TABLE IF EXISTS `activity_logs`;
CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL COMMENT 'ID del usuario que realizó la acción',
  `action` varchar(100) NOT NULL COMMENT 'Tipo de acción (ej. user_login, password_reset)',
  `description` text DEFAULT NULL COMMENT 'Descripción detallada de la acción',
  `ip_address` varchar(45) DEFAULT NULL COMMENT 'Dirección IP del usuario',
  `user_agent` text DEFAULT NULL COMMENT 'User agent del navegador',
  `metadata` json DEFAULT NULL COMMENT 'Datos adicionales en formato JSON',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Fecha de la acción',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `action` (`action`),
  KEY `created_at` (`created_at`),
  CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Registro de actividad de usuarios';

-- ============================================================================
-- Tabla: user_sessions
-- ============================================================================

DROP TABLE IF EXISTS `user_sessions`;
CREATE TABLE `user_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT 'ID del usuario',
  `session_token` varchar(255) NOT NULL COMMENT 'Token único de la sesión',
  `ip_address` varchar(45) DEFAULT NULL COMMENT 'Dirección IP de la sesión',
  `user_agent` text DEFAULT NULL COMMENT 'User agent del navegador',
  `expires_at` datetime NOT NULL COMMENT 'Fecha de expiración de la sesión',
  `is_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Sesión activa (1) o no (0)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Fecha de creación',
  PRIMARY KEY (`id`),
  UNIQUE KEY `session_token` (`session_token`),
  KEY `user_id` (`user_id`),
  KEY `is_active` (`is_active`),
  KEY `expires_at` (`expires_at`),
  CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Gestión de sesiones de usuarios';

-- ============================================================================
-- Tabla: mfa_factors
-- ============================================================================

DROP TABLE IF EXISTS `mfa_factors`;
CREATE TABLE `mfa_factors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT 'ID del usuario',
  `factor_type` enum('totp','sms','email') NOT NULL COMMENT 'Tipo de factor 2FA',
  `secret` varchar(255) DEFAULT NULL COMMENT 'Secreto para TOTP',
  `phone_number` varchar(20) DEFAULT NULL COMMENT 'Número de teléfono para SMS',
  `is_verified` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Factor verificado (1) o no (0)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Fecha de creación',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `mfa_factors_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Factores de autenticación de dos factores';

-- ============================================================================
-- Tabla: rate_limits
-- ============================================================================

DROP TABLE IF EXISTS `rate_limits`;
CREATE TABLE `rate_limits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL COMMENT 'Dirección IP',
  `action` varchar(100) NOT NULL COMMENT 'Acción limitada (ej. login_attempt)',
  `attempts` int(11) NOT NULL DEFAULT '0' COMMENT 'Número de intentos',
  `last_attempt_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Fecha del último intento',
  `locked_until` datetime DEFAULT NULL COMMENT 'Bloqueado hasta esta fecha',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ip_action` (`ip_address`,`action`),
  KEY `locked_until` (`locked_until`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Control de rate limiting';

-- ============================================================================
-- Tabla: waf_rules
-- ============================================================================

DROP TABLE IF EXISTS `waf_rules`;
CREATE TABLE `waf_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rule_name` varchar(100) NOT NULL COMMENT 'Nombre de la regla',
  `rule_type` enum('ip_block','pattern_block','rate_limit') NOT NULL COMMENT 'Tipo de regla',
  `pattern` varchar(255) DEFAULT NULL COMMENT 'Patrón a bloquear (regex)',
  `is_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Regla activa (1) o no (0)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Fecha de creación',
  PRIMARY KEY (`id`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Reglas del Web Application Firewall';

-- ============================================================================
-- Tabla: allowed_domains
-- ============================================================================

DROP TABLE IF EXISTS `allowed_domains`;
CREATE TABLE `allowed_domains` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `domain` varchar(255) NOT NULL COMMENT 'Dominio permitido (ej. gmail.com)',
  `is_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Dominio activo (1) o no (0)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Fecha de creación',
  PRIMARY KEY (`id`),
  UNIQUE KEY `domain` (`domain`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Dominios de email permitidos para registro';

-- Insertar dominios permitidos por defecto
INSERT INTO `allowed_domains` (`domain`, `is_active`) VALUES
('gmail.com', 1),
('hotmail.com', 1),
('outlook.com', 1),
('yahoo.com', 1),
('manelbenlloch.es', 1);

-- ============================================================================
-- Insertar usuario root por defecto
-- ============================================================================

INSERT INTO `users` (`fullname`, `username`, `alias`, `email`, `password_hash`, `role`, `is_active`, `email_verified`, `can_manage_users`, `can_delete_users`, `terms_accepted`) VALUES
('Administrador Root', 'root', 'admin', 'admin@login3.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'root', 1, 1, 1, 1, 1);
-- Contraseña por defecto: password

-- ============================================================================
-- Fin del script
-- ============================================================================
