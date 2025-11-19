-- ============================================================================
-- RBAC Schema Update for login6_db
-- This script updates an existing login6_db to add/update RBAC tables and data
-- Compatible with the actual database structure from phpMyAdmin export
-- Date: 2025-11-19
-- ============================================================================

USE login6_db;

-- ============================================================================
-- ENSURE ROLES TABLE EXISTS WITH CORRECT STRUCTURE
-- ============================================================================
CREATE TABLE IF NOT EXISTS `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT 'Nombre interno del rol (ej. admin, manager)',
  `display_name` varchar(100) NOT NULL COMMENT 'Nombre legible del rol',
  `description` text DEFAULT NULL COMMENT 'Descripción del rol',
  `is_system_role` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1 si es uno de los roles del sistema (root, admin, personal, user)',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `idx_is_system_role` (`is_system_role`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Roles de usuarios';

-- ============================================================================
-- ENSURE PERMISSIONS TABLE EXISTS WITH CORRECT STRUCTURE  
-- ============================================================================
CREATE TABLE IF NOT EXISTS `permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT 'Nombre interno del permiso (ej. manage_users)',
  `display_name` varchar(150) NOT NULL COMMENT 'Nombre legible del permiso',
  `description` text DEFAULT NULL COMMENT 'Descripción del permiso',
  `category` varchar(100) DEFAULT NULL COMMENT 'Categoría del permiso (ej. users, roles, system)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `idx_name` (`name`),
  KEY `idx_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Permisos del sistema';

-- ============================================================================
-- ENSURE ROLE_PERMISSIONS TABLE EXISTS WITH CORRECT STRUCTURE
-- ============================================================================
CREATE TABLE IF NOT EXISTS `role_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `granted_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_permission_unique` (`role_id`,`permission_id`),
  KEY `permission_id` (`permission_id`),
  KEY `idx_role_id` (`role_id`),
  KEY `idx_permission_id` (`permission_id`),
  CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Permisos asignados a roles';

-- ============================================================================
-- ENSURE user_permissions TABLE EXISTS (for ABAC-style user overrides)
-- ============================================================================
CREATE TABLE IF NOT EXISTS `user_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `is_granted` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1 = concede, 0 = revoca respecto al rol',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_permission_unique` (`user_id`,`permission_id`),
  KEY `user_id` (`user_id`),
  KEY `permission_id` (`permission_id`),
  CONSTRAINT `user_permissions_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_permissions_perm_fk` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Permisos específicos de usuario (overrides)';

-- ============================================================================
-- ENSURE users.role_id COLUMN EXISTS
-- ============================================================================
-- Check if role_id column exists, if not add it
SET @dbname = DATABASE();
SET @tablename = 'users';
SET @columnname = 'role_id';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      TABLE_SCHEMA = @dbname
      AND TABLE_NAME = @tablename
      AND COLUMN_NAME = @columnname
  ) > 0,
  'SELECT 1',
  'ALTER TABLE users ADD COLUMN role_id INT(11) DEFAULT NULL AFTER role, ADD INDEX idx_role_id (role_id)'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- ============================================================================
-- INSERT SYSTEM ROLES (use INSERT IGNORE to avoid duplicates)
-- ============================================================================
INSERT IGNORE INTO `roles` (`id`, `name`, `display_name`, `description`, `is_system_role`, `is_active`) VALUES
(1, 'user', 'Usuario estándar', 'Usuario final con permisos limitados.', 1, 1),
(2, 'personal', 'Personal interno', 'Personal con permisos intermedios.', 1, 1),
(3, 'admin', 'Administrador', 'Administra usuarios y configuraciones permitidas por root.', 1, 1),
(4, 'root', 'Superadministrador (root)', 'Rol máximo del sistema, acceso total.', 1, 1);

-- ============================================================================
-- INSERT COMPREHENSIVE PERMISSIONS
-- ============================================================================

-- Category: users
INSERT IGNORE INTO `permissions` (`name`, `display_name`, `description`, `category`) VALUES
('view_users', 'Ver Usuarios', 'Permite ver el listado de usuarios', 'users'),
('create_users', 'Crear Usuarios', 'Permite crear nuevos usuarios', 'users'),
('edit_users', 'Editar Usuarios', 'Permite editar información de usuarios', 'users'),
('delete_users', 'Eliminar Usuarios', 'Permite eliminar usuarios', 'users'),
('manage_user_roles', 'Gestionar Roles de Usuarios', 'Permite asignar y cambiar roles de usuarios', 'users'),
('view_deleted_users', 'Ver Usuarios Eliminados', 'Permite ver usuarios eliminados', 'users'),
('restore_users', 'Restaurar Usuarios', 'Permite restaurar usuarios eliminados', 'users');

-- Category: roles (system/RBAC management)
INSERT IGNORE INTO `permissions` (`name`, `display_name`, `description`, `category`) VALUES
('manage_roles', 'Gestionar Roles', 'Permite crear, editar y eliminar roles personalizados', 'roles'),
('manage_permissions', 'Gestionar Permisos', 'Permite crear y asignar permisos', 'roles'),
('view_roles', 'Ver Roles', 'Permite ver el listado de roles', 'roles');

-- Category: posts (example content management)
INSERT IGNORE INTO `permissions` (`name`, `display_name`, `description`, `category`) VALUES
('view_posts', 'Ver Publicaciones', 'Permite ver publicaciones', 'posts'),
('create_posts', 'Crear Publicaciones', 'Permite crear nuevas publicaciones', 'posts'),
('edit_own_posts', 'Editar Propias Publicaciones', 'Permite editar solo sus propias publicaciones', 'posts'),
('edit_all_posts', 'Editar Todas las Publicaciones', 'Permite editar cualquier publicación', 'posts'),
('delete_own_posts', 'Eliminar Propias Publicaciones', 'Permite eliminar solo sus propias publicaciones', 'posts'),
('delete_all_posts', 'Eliminar Todas las Publicaciones', 'Permite eliminar cualquier publicación', 'posts'),
('publish_posts', 'Publicar/Despublicar Posts', 'Permite cambiar el estado de publicación', 'posts');

-- Category: system
INSERT IGNORE INTO `permissions` (`name`, `display_name`, `description`, `category`) VALUES
('view_logs', 'Ver Logs', 'Permite ver logs del sistema', 'system'),
('view_audit_logs', 'Ver Logs de Auditoría', 'Permite ver el historial de actividad de usuarios', 'system'),
('manage_settings', 'Gestionar Configuración', 'Permite modificar la configuración del sistema', 'system'),
('access_root_panel', 'Acceder Panel Root', 'Permite acceder al panel de administración root', 'system'),
('access_admin_panel', 'Acceder Panel Admin', 'Permite acceder al panel de administración', 'system'),
('view_system_info', 'Ver Información del Sistema', 'Permite ver información técnica del sistema', 'system'),
('view_dashboard', 'Ver Dashboard', 'Acceder al panel principal', 'system');

-- Category: sessions
INSERT IGNORE INTO `permissions` (`name`, `display_name`, `description`, `category`) VALUES
('view_sessions', 'Ver Sesiones', 'Permite ver sesiones activas', 'sessions'),
('manage_own_sessions', 'Gestionar Propias Sesiones', 'Permite gestionar solo sus propias sesiones', 'sessions'),
('manage_all_sessions', 'Gestionar Todas las Sesiones', 'Permite gestionar sesiones de cualquier usuario', 'sessions'),
('revoke_sessions', 'Revocar Sesiones', 'Permite cerrar sesiones de otros usuarios', 'sessions');

-- Category: security
INSERT IGNORE INTO `permissions` (`name`, `display_name`, `description`, `category`) VALUES
('manage_2fa', 'Gestionar 2FA', 'Permite configurar autenticación de dos factores', 'security'),
('view_security_logs', 'Ver Logs de Seguridad', 'Permite ver logs de seguridad', 'security'),
('manage_ip_blocks', 'Gestionar Bloqueos de IP', 'Permite bloquear y desbloquear IPs', 'security'),
('manage_waf', 'Gestionar WAF', 'Permite configurar el Web Application Firewall', 'security');

-- ============================================================================
-- ASSIGN PERMISSIONS TO SYSTEM ROLES
-- ============================================================================

-- Clear existing permissions for system roles (to ensure clean state)
-- We only clear system roles to preserve any custom role configurations
DELETE FROM role_permissions WHERE role_id IN (1, 2, 3, 4);

-- Permissions for 'user' role (ID: 1) - Basic user permissions
INSERT INTO role_permissions (role_id, permission_id) 
SELECT 1, id FROM permissions WHERE name IN (
    'view_posts',
    'create_posts', 
    'edit_own_posts',
    'delete_own_posts',
    'manage_own_sessions',
    'manage_2fa',
    'view_dashboard'
);

-- Permissions for 'personal' role (ID: 2) - Intermediate permissions
INSERT INTO role_permissions (role_id, permission_id)
SELECT 2, id FROM permissions WHERE name IN (
    'view_posts',
    'create_posts',
    'edit_own_posts',
    'delete_own_posts',
    'view_users',
    'manage_own_sessions',
    'manage_2fa',
    'view_dashboard',
    'view_sessions'
);

-- Permissions for 'admin' role (ID: 3) - Administrative permissions
INSERT INTO role_permissions (role_id, permission_id)
SELECT 3, id FROM permissions WHERE name IN (
    'view_users',
    'create_users',
    'edit_users',
    'delete_users',
    'manage_user_roles',
    'view_posts',
    'create_posts',
    'edit_all_posts',
    'delete_all_posts',
    'publish_posts',
    'view_logs',
    'view_audit_logs',
    'access_admin_panel',
    'view_sessions',
    'manage_all_sessions',
    'manage_2fa',
    'view_dashboard',
    'manage_roles',
    'view_roles',
    'revoke_sessions'
);

-- Permissions for 'root' role (ID: 4) - ALL permissions
-- Root gets all permissions by code, but we insert them for reference
INSERT INTO role_permissions (role_id, permission_id)
SELECT 4, id FROM permissions;

-- ============================================================================
-- INFORMATION MESSAGE
-- ============================================================================
SELECT 'RBAC Schema Update Complete!' as Status,
       (SELECT COUNT(*) FROM roles) as Total_Roles,
       (SELECT COUNT(*) FROM permissions) as Total_Permissions,
       (SELECT COUNT(*) FROM role_permissions) as Total_Role_Permissions;

-- ============================================================================
-- END OF SCRIPT
-- ============================================================================
