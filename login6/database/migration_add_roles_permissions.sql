-- ============================================================================
-- MIGRATION: Add Role and Permission System to login6_db
-- ============================================================================
-- Purpose: Upgrade existing login6_db database to support dynamic roles and permissions
-- 
-- INSTRUCTIONS:
-- 1. Backup your database before running this migration
-- 2. This script is idempotent - safe to run multiple times
-- 3. Run this script after the base login6_db schema is already in place
-- 4. For new installations, use database/INSTALAR_LOGIN6_DB.sql instead
--
-- What this migration does:
-- - Adds role_id column to users table (if not exists)
-- - Creates roles, permissions, and role_permissions tables (if not exist)
-- - Seeds 4 system roles: root, admin, personal, user (INTOCABLES - cannot be deleted/renamed)
-- - Seeds comprehensive permissions for all system features
-- - Assigns appropriate permissions to each system role
-- ============================================================================

USE login6_db;

-- ============================================================================
-- STEP 1: Add role_id column to users table if it doesn't exist
-- ============================================================================

-- Check and add role_id column
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
  'SELECT 1', -- Column exists, do nothing
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' INT DEFAULT NULL COMMENT ''ID del rol personalizado (si aplica)'' AFTER role')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add index for role_id if column was just created
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE
      TABLE_SCHEMA = @dbname
      AND TABLE_NAME = @tablename
      AND INDEX_NAME = 'idx_role_id'
  ) > 0,
  'SELECT 1', -- Index exists, do nothing
  CONCAT('ALTER TABLE ', @tablename, ' ADD INDEX idx_role_id (role_id)')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- ============================================================================
-- STEP 2: Create roles table if it doesn't exist
-- ============================================================================

CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL COMMENT 'Nombre interno del rol (identificador único)',
    display_name VARCHAR(100) NOT NULL COMMENT 'Nombre para mostrar en la UI',
    description TEXT COMMENT 'Descripción del rol y sus responsabilidades',
    is_system_role BOOLEAN DEFAULT 0 COMMENT 'Rol del sistema (1) o personalizado (0). Los roles del sistema son INTOCABLES.',
    is_active BOOLEAN DEFAULT 1 COMMENT 'Rol activo (1) o inactivo (0)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT COMMENT 'ID del usuario que creó este rol',
    INDEX idx_name (name),
    INDEX idx_is_system_role (is_system_role),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Roles del sistema (predefinidos) y roles personalizados';

-- ============================================================================
-- STEP 3: Create permissions table if it doesn't exist
-- ============================================================================

CREATE TABLE IF NOT EXISTS permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL COMMENT 'Nombre interno del permiso (identificador único)',
    display_name VARCHAR(150) NOT NULL COMMENT 'Nombre para mostrar en la UI',
    description TEXT COMMENT 'Descripción del permiso y qué permite hacer',
    category VARCHAR(50) COMMENT 'Categoría del permiso (users, posts, system, etc.)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_name (name),
    INDEX idx_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Permisos granulares del sistema';

-- ============================================================================
-- STEP 4: Create role_permissions table if it doesn't exist
-- ============================================================================

CREATE TABLE IF NOT EXISTS role_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL COMMENT 'ID del rol',
    permission_id INT NOT NULL COMMENT 'ID del permiso',
    granted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha en que se otorgó el permiso',
    granted_by INT COMMENT 'ID del usuario que otorgó el permiso',
    UNIQUE KEY unique_role_permission (role_id, permission_id),
    INDEX idx_role_id (role_id),
    INDEX idx_permission_id (permission_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Relación entre roles y permisos';

-- ============================================================================
-- STEP 5: Add foreign keys if they don't exist
-- ============================================================================

-- Add FK from users.role_id to roles.id if not exists
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
    WHERE
      TABLE_SCHEMA = @dbname
      AND TABLE_NAME = 'users'
      AND CONSTRAINT_NAME = 'fk_users_role_id'
  ) > 0,
  'SELECT 1',
  'ALTER TABLE users ADD CONSTRAINT fk_users_role_id FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE SET NULL'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add FK from role_permissions.role_id to roles.id if not exists
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
    WHERE
      TABLE_SCHEMA = @dbname
      AND TABLE_NAME = 'role_permissions'
      AND CONSTRAINT_NAME = 'fk_role_permissions_role_id'
  ) > 0,
  'SELECT 1',
  'ALTER TABLE role_permissions ADD CONSTRAINT fk_role_permissions_role_id FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add FK from role_permissions.permission_id to permissions.id if not exists
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
    WHERE
      TABLE_SCHEMA = @dbname
      AND TABLE_NAME = 'role_permissions'
      AND CONSTRAINT_NAME = 'fk_role_permissions_permission_id'
  ) > 0,
  'SELECT 1',
  'ALTER TABLE role_permissions ADD CONSTRAINT fk_role_permissions_permission_id FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add FK from role_permissions.granted_by to users.id if not exists (optional)
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
    WHERE
      TABLE_SCHEMA = @dbname
      AND TABLE_NAME = 'role_permissions'
      AND CONSTRAINT_NAME = 'fk_role_permissions_granted_by'
  ) > 0,
  'SELECT 1',
  'ALTER TABLE role_permissions ADD CONSTRAINT fk_role_permissions_granted_by FOREIGN KEY (granted_by) REFERENCES users(id) ON DELETE SET NULL'
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- ============================================================================
-- STEP 6: Insert system roles (INTOCABLES) if they don't exist
-- ============================================================================
-- These 4 roles are special: they cannot be deleted, renamed, or deactivated
-- Root can configure their permissions, but root itself always has all permissions by code

INSERT IGNORE INTO roles (name, display_name, description, is_system_role, is_active) VALUES
('user', 'Usuario', 'Usuario estándar del sistema con permisos básicos', 1, 1),
('personal', 'Personal', 'Usuario con permisos de personal (nivel intermedio)', 1, 1),
('admin', 'Administrador', 'Administrador del sistema con permisos elevados', 1, 1),
('root', 'Root', 'Super administrador con acceso total al sistema (todos los permisos)', 1, 1);

-- ============================================================================
-- STEP 7: Insert permissions if they don't exist
-- ============================================================================

-- Categoría: users (Gestión de usuarios)
INSERT IGNORE INTO permissions (name, display_name, description, category) VALUES
('view_users', 'Ver Usuarios', 'Permite ver el listado de usuarios', 'users'),
('create_users', 'Crear Usuarios', 'Permite crear nuevos usuarios', 'users'),
('edit_users', 'Editar Usuarios', 'Permite editar información de usuarios', 'users'),
('delete_users', 'Eliminar Usuarios', 'Permite eliminar usuarios (soft delete)', 'users'),
('manage_user_roles', 'Gestionar Roles de Usuarios', 'Permite asignar y cambiar roles de usuarios', 'users'),
('view_deleted_users', 'Ver Usuarios Eliminados', 'Permite ver usuarios eliminados', 'users'),
('restore_users', 'Restaurar Usuarios', 'Permite restaurar usuarios eliminados', 'users');

-- Categoría: posts (Gestión de publicaciones)
INSERT IGNORE INTO permissions (name, display_name, description, category) VALUES
('view_posts', 'Ver Publicaciones', 'Permite ver publicaciones', 'posts'),
('create_posts', 'Crear Publicaciones', 'Permite crear nuevas publicaciones', 'posts'),
('edit_own_posts', 'Editar Propias Publicaciones', 'Permite editar solo sus propias publicaciones', 'posts'),
('edit_all_posts', 'Editar Todas las Publicaciones', 'Permite editar cualquier publicación', 'posts'),
('delete_own_posts', 'Eliminar Propias Publicaciones', 'Permite eliminar solo sus propias publicaciones', 'posts'),
('delete_all_posts', 'Eliminar Todas las Publicaciones', 'Permite eliminar cualquier publicación', 'posts'),
('publish_posts', 'Publicar/Despublicar Posts', 'Permite cambiar el estado de publicación', 'posts');

-- Categoría: system (Administración del sistema)
INSERT IGNORE INTO permissions (name, display_name, description, category) VALUES
('view_logs', 'Ver Logs', 'Permite ver logs de actividad del sistema', 'system'),
('view_audit_logs', 'Ver Logs de Auditoría', 'Permite ver logs de auditoría detallados', 'system'),
('manage_settings', 'Gestionar Configuración', 'Permite modificar la configuración del sistema', 'system'),
('manage_roles', 'Gestionar Roles', 'Permite crear, editar y eliminar roles personalizados', 'system'),
('manage_permissions', 'Gestionar Permisos', 'Permite crear y asignar permisos', 'system'),
('access_root_panel', 'Acceder Panel Root', 'Permite acceder al panel de administración root', 'system'),
('access_admin_panel', 'Acceder Panel Admin', 'Permite acceder al panel de administración', 'system'),
('view_system_info', 'Ver Información del Sistema', 'Permite ver información técnica del sistema', 'system');

-- Categoría: sessions (Gestión de sesiones)
INSERT IGNORE INTO permissions (name, display_name, description, category) VALUES
('view_sessions', 'Ver Sesiones', 'Permite ver sesiones activas', 'sessions'),
('manage_own_sessions', 'Gestionar Propias Sesiones', 'Permite gestionar solo sus propias sesiones', 'sessions'),
('manage_all_sessions', 'Gestionar Todas las Sesiones', 'Permite gestionar sesiones de cualquier usuario', 'sessions'),
('revoke_sessions', 'Revocar Sesiones', 'Permite cerrar sesiones de otros usuarios', 'sessions');

-- Categoría: security (Seguridad)
INSERT IGNORE INTO permissions (name, display_name, description, category) VALUES
('manage_2fa', 'Gestionar 2FA', 'Permite configurar autenticación de dos factores', 'security'),
('view_security_logs', 'Ver Logs de Seguridad', 'Permite ver logs de seguridad', 'security'),
('manage_ip_blocks', 'Gestionar Bloqueos de IP', 'Permite bloquear y desbloquear IPs', 'security'),
('manage_waf', 'Gestionar WAF', 'Permite configurar el Web Application Firewall', 'security');

-- ============================================================================
-- STEP 8: Assign permissions to system roles (if not already assigned)
-- ============================================================================

-- Permisos para rol 'user' (usuario básico)
INSERT IGNORE INTO role_permissions (role_id, permission_id)
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
INSERT IGNORE INTO role_permissions (role_id, permission_id)
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
INSERT IGNORE INTO role_permissions (role_id, permission_id)
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
-- Nota: El código verifica si el usuario es root y le otorga todos los permisos automáticamente,
-- pero insertamos todos los permisos aquí para mantener consistencia en la base de datos
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 
    (SELECT id FROM roles WHERE name = 'root' LIMIT 1) as role_id,
    id as permission_id
FROM permissions;

-- ============================================================================
-- MIGRATION COMPLETE
-- ============================================================================
-- 
-- Next steps:
-- 1. Verify the migration by checking the tables: roles, permissions, role_permissions
-- 2. Check that users table has the role_id column
-- 3. Access http://localhost/login6/roles as root user to verify it works
-- 4. If you have existing users, they will continue using their users.role column
-- 5. New custom roles can be created and assigned via users.role_id
-- 
-- System roles (INTOCABLES):
-- - root: Always has all permissions (enforced in code)
-- - admin: Can manage users and most system features
-- - personal: Intermediate level permissions
-- - user: Basic user permissions
-- 
-- These 4 system roles CANNOT be:
-- - Deleted (protected in RoleController)
-- - Renamed (is_system_role check prevents modification)
-- - Deactivated (protected)
-- 
-- ============================================================================
