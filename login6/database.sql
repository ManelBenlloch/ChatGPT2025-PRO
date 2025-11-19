-- ============================================================================
-- SQL Schema for login6
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
  `role` enum('user','personal','admin','root') NOT NULL DEFAULT 'user' COMMENT 'Rol del usuario (para roles del sistema)',
  `role_id` int(11) DEFAULT NULL COMMENT 'ID del rol personalizado (si aplica)',
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
  KEY `role_id` (`role_id`),
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
-- Tabla: roles
-- ============================================================================

DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT 'Nombre interno del rol (identificador único)',
  `display_name` varchar(100) NOT NULL COMMENT 'Nombre para mostrar en la UI',
  `description` text DEFAULT NULL COMMENT 'Descripción del rol y sus responsabilidades',
  `is_system_role` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Rol del sistema (1) o personalizado (0). Los roles del sistema son INTOCABLES.',
  `is_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Rol activo (1) o inactivo (0)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Fecha de creación',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Fecha de actualización',
  `created_by` int(11) DEFAULT NULL COMMENT 'ID del usuario que creó este rol',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `is_system_role` (`is_system_role`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Roles del sistema (predefinidos) y roles personalizados';

-- ============================================================================
-- Tabla: permissions
-- ============================================================================

DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT 'Nombre interno del permiso (identificador único)',
  `display_name` varchar(150) NOT NULL COMMENT 'Nombre para mostrar en la UI',
  `description` text DEFAULT NULL COMMENT 'Descripción del permiso y qué permite hacer',
  `category` varchar(50) DEFAULT NULL COMMENT 'Categoría del permiso (users, posts, system, etc.)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Fecha de creación',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Permisos granulares del sistema';

-- ============================================================================
-- Tabla: role_permissions
-- ============================================================================

DROP TABLE IF EXISTS `role_permissions`;
CREATE TABLE `role_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL COMMENT 'ID del rol',
  `permission_id` int(11) NOT NULL COMMENT 'ID del permiso',
  `granted_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Fecha en que se otorgó el permiso',
  `granted_by` int(11) DEFAULT NULL COMMENT 'ID del usuario que otorgó el permiso',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_role_permission` (`role_id`,`permission_id`),
  KEY `role_id` (`role_id`),
  KEY `permission_id` (`permission_id`),
  CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_permissions_ibfk_3` FOREIGN KEY (`granted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Relación entre roles y permisos';

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
-- Add foreign key constraint for users.role_id
-- ============================================================================

ALTER TABLE `users` ADD CONSTRAINT `fk_users_role_id` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL;

-- ============================================================================
-- Insert system roles (INTOCABLES - Cannot be deleted/renamed/deactivated)
-- ============================================================================

INSERT INTO `roles` (`name`, `display_name`, `description`, `is_system_role`, `is_active`) VALUES
('user', 'Usuario', 'Usuario estándar del sistema con permisos básicos', 1, 1),
('personal', 'Personal', 'Usuario con permisos de personal (nivel intermedio)', 1, 1),
('admin', 'Administrador', 'Administrador del sistema con permisos elevados', 1, 1),
('root', 'Root', 'Super administrador con acceso total al sistema (todos los permisos)', 1, 1);

-- ============================================================================
-- Insert permissions
-- ============================================================================

-- Categoría: users (Gestión de usuarios)
INSERT INTO `permissions` (`name`, `display_name`, `description`, `category`) VALUES
('view_users', 'Ver Usuarios', 'Permite ver el listado de usuarios', 'users'),
('create_users', 'Crear Usuarios', 'Permite crear nuevos usuarios', 'users'),
('edit_users', 'Editar Usuarios', 'Permite editar información de usuarios', 'users'),
('delete_users', 'Eliminar Usuarios', 'Permite eliminar usuarios (soft delete)', 'users'),
('manage_user_roles', 'Gestionar Roles de Usuarios', 'Permite asignar y cambiar roles de usuarios', 'users'),
('view_deleted_users', 'Ver Usuarios Eliminados', 'Permite ver usuarios eliminados', 'users'),
('restore_users', 'Restaurar Usuarios', 'Permite restaurar usuarios eliminados', 'users');

-- Categoría: posts (Gestión de publicaciones)
INSERT INTO `permissions` (`name`, `display_name`, `description`, `category`) VALUES
('view_posts', 'Ver Publicaciones', 'Permite ver publicaciones', 'posts'),
('create_posts', 'Crear Publicaciones', 'Permite crear nuevas publicaciones', 'posts'),
('edit_own_posts', 'Editar Propias Publicaciones', 'Permite editar solo sus propias publicaciones', 'posts'),
('edit_all_posts', 'Editar Todas las Publicaciones', 'Permite editar cualquier publicación', 'posts'),
('delete_own_posts', 'Eliminar Propias Publicaciones', 'Permite eliminar solo sus propias publicaciones', 'posts'),
('delete_all_posts', 'Eliminar Todas las Publicaciones', 'Permite eliminar cualquier publicación', 'posts'),
('publish_posts', 'Publicar/Despublicar Posts', 'Permite cambiar el estado de publicación', 'posts');

-- Categoría: system (Administración del sistema)
INSERT INTO `permissions` (`name`, `display_name`, `description`, `category`) VALUES
('view_logs', 'Ver Logs', 'Permite ver logs de actividad del sistema', 'system'),
('view_audit_logs', 'Ver Logs de Auditoría', 'Permite ver logs de auditoría detallados', 'system'),
('manage_settings', 'Gestionar Configuración', 'Permite modificar la configuración del sistema', 'system'),
('manage_roles', 'Gestionar Roles', 'Permite crear, editar y eliminar roles personalizados', 'system'),
('manage_permissions', 'Gestionar Permisos', 'Permite crear y asignar permisos', 'system'),
('access_root_panel', 'Acceder Panel Root', 'Permite acceder al panel de administración root', 'system'),
('access_admin_panel', 'Acceder Panel Admin', 'Permite acceder al panel de administración', 'system'),
('view_system_info', 'Ver Información del Sistema', 'Permite ver información técnica del sistema', 'system');

-- Categoría: sessions (Gestión de sesiones)
INSERT INTO `permissions` (`name`, `display_name`, `description`, `category`) VALUES
('view_sessions', 'Ver Sesiones', 'Permite ver sesiones activas', 'sessions'),
('manage_own_sessions', 'Gestionar Propias Sesiones', 'Permite gestionar solo sus propias sesiones', 'sessions'),
('manage_all_sessions', 'Gestionar Todas las Sesiones', 'Permite gestionar sesiones de cualquier usuario', 'sessions'),
('revoke_sessions', 'Revocar Sesiones', 'Permite cerrar sesiones de otros usuarios', 'sessions');

-- Categoría: security (Seguridad)
INSERT INTO `permissions` (`name`, `display_name`, `description`, `category`) VALUES
('manage_2fa', 'Gestionar 2FA', 'Permite configurar autenticación de dos factores', 'security'),
('view_security_logs', 'Ver Logs de Seguridad', 'Permite ver logs de seguridad', 'security'),
('manage_ip_blocks', 'Gestionar Bloqueos de IP', 'Permite bloquear y desbloquear IPs', 'security'),
('manage_waf', 'Gestionar WAF', 'Permite configurar el Web Application Firewall', 'security');

-- ============================================================================
-- Assign permissions to system roles
-- ============================================================================

-- Permisos para rol 'user' (usuario básico)
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 
    (SELECT id FROM roles WHERE name = 'user' LIMIT 1) as role_id,
    id as permission_id
FROM permissions
WHERE name IN (
    'view_posts',
    'create_posts',
    'edit_own_posts',
    'delete_own_posts',
    'manage_own_sessions',
    'manage_2fa'
);

-- Permisos para rol 'personal' (usuario con permisos intermedios)
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 
    (SELECT id FROM roles WHERE name = 'personal' LIMIT 1) as role_id,
    id as permission_id
FROM permissions
WHERE name IN (
    'view_posts',
    'create_posts',
    'edit_own_posts',
    'delete_own_posts',
    'view_users',
    'manage_own_sessions',
    'manage_2fa'
);

-- Permisos para rol 'admin' (administrador con permisos elevados)
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 
    (SELECT id FROM roles WHERE name = 'admin' LIMIT 1) as role_id,
    id as permission_id
FROM permissions
WHERE name IN (
    -- Users
    'view_users',
    'create_users',
    'edit_users',
    'delete_users',
    'manage_user_roles',
    'view_deleted_users',
    'restore_users',
    -- Posts
    'view_posts',
    'create_posts',
    'edit_all_posts',
    'delete_all_posts',
    'publish_posts',
    -- System
    'view_logs',
    'view_audit_logs',
    'manage_roles',
    'access_admin_panel',
    'view_system_info',
    -- Sessions
    'view_sessions',
    'manage_all_sessions',
    'manage_2fa',
    -- Security
    'view_security_logs'
);

-- Permisos para rol 'root' (super administrador - tiene TODOS los permisos)
-- Nota: El código verifica si el usuario es root y le otorga todos los permisos automáticamente
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 
    (SELECT id FROM roles WHERE name = 'root' LIMIT 1) as role_id,
    id as permission_id
FROM permissions;

-- ============================================================================
-- Insertar usuario root por defecto
-- ============================================================================

INSERT INTO `users` (`fullname`, `username`, `alias`, `email`, `password_hash`, `role`, `is_active`, `email_verified`, `can_manage_users`, `can_delete_users`, `terms_accepted`) VALUES
('Administrador Root', 'root', 'admin', 'admin@login3.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'root', 1, 1, 1, 1, 1);
-- Contraseña por defecto: password

-- ============================================================================
-- Fin del script
-- ============================================================================
