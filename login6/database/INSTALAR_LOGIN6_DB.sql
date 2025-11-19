-- ============================================================================
-- BASE DE DATOS: login6_db
-- Sistema de Login con Roles Dinámicos y Permisos Granulares
-- Fecha: 17 de noviembre de 2025
-- ============================================================================

-- Crear la base de datos
CREATE DATABASE IF NOT EXISTS login6_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE login6_db;

-- ============================================================================
-- TABLA: users
-- Usuarios del sistema
-- ============================================================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    fullname VARCHAR(100) NOT NULL,
    alias VARCHAR(100) UNIQUE,
    role ENUM('user', 'personal', 'admin', 'root') DEFAULT 'user',
    role_id INT DEFAULT NULL,
    is_verified BOOLEAN DEFAULT 0,
    is_active BOOLEAN DEFAULT 1,
    is_deleted BOOLEAN DEFAULT 0,
    verification_token VARCHAR(64),
    reset_token VARCHAR(64),
    reset_token_expires DATETIME,
    last_login DATETIME,
    failed_login_attempts INT DEFAULT 0,
    locked_until DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    INDEX idx_email (email),
    INDEX idx_username (username),
    INDEX idx_role (role),
    INDEX idx_role_id (role_id),
    INDEX idx_is_active (is_active),
    INDEX idx_is_deleted (is_deleted)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLA: roles
-- Roles del sistema (por defecto) y roles personalizados
-- ============================================================================
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL,
    display_name VARCHAR(100) NOT NULL,
    description TEXT,
    is_system_role BOOLEAN DEFAULT 0,
    is_active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    INDEX idx_name (name),
    INDEX idx_is_system_role (is_system_role),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLA: permissions
-- Permisos granulares del sistema
-- ============================================================================
CREATE TABLE permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    display_name VARCHAR(150) NOT NULL,
    description TEXT,
    category VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_name (name),
    INDEX idx_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLA: role_permissions
-- Relación entre roles y permisos
-- ============================================================================
CREATE TABLE role_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    permission_id INT NOT NULL,
    granted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    granted_by INT,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    FOREIGN KEY (granted_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_role_permission (role_id, permission_id),
    INDEX idx_role_id (role_id),
    INDEX idx_permission_id (permission_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLA: user_sessions
-- Sesiones activas de usuarios
-- ============================================================================
CREATE TABLE user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_token VARCHAR(64) UNIQUE NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    device_type VARCHAR(50),
    browser VARCHAR(50),
    os VARCHAR(50),
    location VARCHAR(100),
    is_active BOOLEAN DEFAULT 1,
    last_activity DATETIME,
    expires_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_session_token (session_token),
    INDEX idx_is_active (is_active),
    INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLA: activity_logs
-- Registro de actividad del sistema
-- ============================================================================
CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLA: rate_limits
-- Control de intentos de login y rate limiting
-- ============================================================================
CREATE TABLE rate_limits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) UNIQUE NOT NULL,
    attempts INT DEFAULT 0,
    last_attempt DATETIME,
    locked_until DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_ip_address (ip_address),
    INDEX idx_locked_until (locked_until)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLA: mfa_factors
-- Factores de autenticación multifactor (2FA)
-- ============================================================================
CREATE TABLE mfa_factors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    factor_type ENUM('totp', 'email', 'sms') NOT NULL,
    secret VARCHAR(255),
    is_enabled BOOLEAN DEFAULT 0,
    backup_codes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TABLA: allowed_domains
-- Dominios de email permitidos para registro
-- ============================================================================
CREATE TABLE allowed_domains (
    id INT AUTO_INCREMENT PRIMARY KEY,
    domain VARCHAR(100) UNIQUE NOT NULL,
    is_active BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_domain (domain),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- AGREGAR FOREIGN KEY A users.role_id
-- ============================================================================
ALTER TABLE users ADD FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE SET NULL;

-- ============================================================================
-- INSERTAR ROLES DEL SISTEMA (INTOCABLES)
-- ============================================================================
INSERT INTO roles (name, display_name, description, is_system_role, is_active) VALUES
('user', 'Usuario', 'Usuario estándar del sistema con permisos básicos', 1, 1),
('personal', 'Personal', 'Usuario con permisos de personal', 1, 1),
('admin', 'Administrador', 'Administrador del sistema con permisos elevados', 1, 1),
('root', 'Root', 'Super administrador con acceso total al sistema', 1, 1);

-- ============================================================================
-- INSERTAR PERMISOS PREDEFINIDOS
-- ============================================================================

-- Categoría: users
INSERT INTO permissions (name, display_name, description, category) VALUES
('view_users', 'Ver Usuarios', 'Permite ver el listado de usuarios', 'users'),
('create_users', 'Crear Usuarios', 'Permite crear nuevos usuarios', 'users'),
('edit_users', 'Editar Usuarios', 'Permite editar información de usuarios', 'users'),
('delete_users', 'Eliminar Usuarios', 'Permite eliminar usuarios', 'users'),
('manage_user_roles', 'Gestionar Roles de Usuarios', 'Permite asignar y cambiar roles de usuarios', 'users'),
('view_deleted_users', 'Ver Usuarios Eliminados', 'Permite ver usuarios eliminados', 'users'),
('restore_users', 'Restaurar Usuarios', 'Permite restaurar usuarios eliminados', 'users');

-- Categoría: posts
INSERT INTO permissions (name, display_name, description, category) VALUES
('view_posts', 'Ver Publicaciones', 'Permite ver publicaciones', 'posts'),
('create_posts', 'Crear Publicaciones', 'Permite crear nuevas publicaciones', 'posts'),
('edit_own_posts', 'Editar Propias Publicaciones', 'Permite editar solo sus propias publicaciones', 'posts'),
('edit_all_posts', 'Editar Todas las Publicaciones', 'Permite editar cualquier publicación', 'posts'),
('delete_own_posts', 'Eliminar Propias Publicaciones', 'Permite eliminar solo sus propias publicaciones', 'posts'),
('delete_all_posts', 'Eliminar Todas las Publicaciones', 'Permite eliminar cualquier publicación', 'posts'),
('publish_posts', 'Publicar/Despublicar Posts', 'Permite cambiar el estado de publicación', 'posts');

-- Categoría: system
INSERT INTO permissions (name, display_name, description, category) VALUES
('view_logs', 'Ver Logs', 'Permite ver logs del sistema', 'system'),
('manage_settings', 'Gestionar Configuración', 'Permite modificar la configuración del sistema', 'system'),
('manage_roles', 'Gestionar Roles', 'Permite crear, editar y eliminar roles personalizados', 'system'),
('manage_permissions', 'Gestionar Permisos', 'Permite crear y asignar permisos', 'system'),
('access_root_panel', 'Acceder Panel Root', 'Permite acceder al panel de administración root', 'system'),
('access_admin_panel', 'Acceder Panel Admin', 'Permite acceder al panel de administración', 'system'),
('view_system_info', 'Ver Información del Sistema', 'Permite ver información técnica del sistema', 'system');

-- Categoría: sessions
INSERT INTO permissions (name, display_name, description, category) VALUES
('view_sessions', 'Ver Sesiones', 'Permite ver sesiones activas', 'sessions'),
('manage_own_sessions', 'Gestionar Propias Sesiones', 'Permite gestionar solo sus propias sesiones', 'sessions'),
('manage_all_sessions', 'Gestionar Todas las Sesiones', 'Permite gestionar sesiones de cualquier usuario', 'sessions'),
('revoke_sessions', 'Revocar Sesiones', 'Permite cerrar sesiones de otros usuarios', 'sessions');

-- Categoría: security
INSERT INTO permissions (name, display_name, description, category) VALUES
('manage_2fa', 'Gestionar 2FA', 'Permite configurar autenticación de dos factores', 'security'),
('view_security_logs', 'Ver Logs de Seguridad', 'Permite ver logs de seguridad', 'security'),
('manage_ip_blocks', 'Gestionar Bloqueos de IP', 'Permite bloquear y desbloquear IPs', 'security'),
('manage_waf', 'Gestionar WAF', 'Permite configurar el Web Application Firewall', 'security');

-- ============================================================================
-- ASIGNAR PERMISOS A ROLES DEL SISTEMA
-- ============================================================================

-- Permisos para rol 'user' (ID: 1)
INSERT INTO role_permissions (role_id, permission_id) VALUES
(1, (SELECT id FROM permissions WHERE name = 'view_posts')),
(1, (SELECT id FROM permissions WHERE name = 'create_posts')),
(1, (SELECT id FROM permissions WHERE name = 'edit_own_posts')),
(1, (SELECT id FROM permissions WHERE name = 'delete_own_posts')),
(1, (SELECT id FROM permissions WHERE name = 'manage_own_sessions')),
(1, (SELECT id FROM permissions WHERE name = 'manage_2fa'));

-- Permisos para rol 'personal' (ID: 2)
INSERT INTO role_permissions (role_id, permission_id) VALUES
(2, (SELECT id FROM permissions WHERE name = 'view_posts')),
(2, (SELECT id FROM permissions WHERE name = 'create_posts')),
(2, (SELECT id FROM permissions WHERE name = 'edit_own_posts')),
(2, (SELECT id FROM permissions WHERE name = 'delete_own_posts')),
(2, (SELECT id FROM permissions WHERE name = 'view_users')),
(2, (SELECT id FROM permissions WHERE name = 'manage_own_sessions')),
(2, (SELECT id FROM permissions WHERE name = 'manage_2fa'));

-- Permisos para rol 'admin' (ID: 3)
INSERT INTO role_permissions (role_id, permission_id) VALUES
(3, (SELECT id FROM permissions WHERE name = 'view_users')),
(3, (SELECT id FROM permissions WHERE name = 'create_users')),
(3, (SELECT id FROM permissions WHERE name = 'edit_users')),
(3, (SELECT id FROM permissions WHERE name = 'delete_users')),
(3, (SELECT id FROM permissions WHERE name = 'view_posts')),
(3, (SELECT id FROM permissions WHERE name = 'create_posts')),
(3, (SELECT id FROM permissions WHERE name = 'edit_all_posts')),
(3, (SELECT id FROM permissions WHERE name = 'delete_all_posts')),
(3, (SELECT id FROM permissions WHERE name = 'publish_posts')),
(3, (SELECT id FROM permissions WHERE name = 'view_logs')),
(3, (SELECT id FROM permissions WHERE name = 'access_admin_panel')),
(3, (SELECT id FROM permissions WHERE name = 'view_sessions')),
(3, (SELECT id FROM permissions WHERE name = 'manage_all_sessions')),
(3, (SELECT id FROM permissions WHERE name = 'manage_2fa'));

-- Permisos para rol 'root' (ID: 4)
-- Root tiene TODOS los permisos por código, pero los insertamos para referencia
INSERT INTO role_permissions (role_id, permission_id)
SELECT 4, id FROM permissions;

-- ============================================================================
-- INSERTAR DOMINIOS PERMITIDOS POR DEFECTO
-- ============================================================================
INSERT INTO allowed_domains (domain, is_active) VALUES
('gmail.com', 1),
('hotmail.com', 1),
('outlook.com', 1),
('yahoo.com', 1),
('icloud.com', 1);

-- ============================================================================
-- INSERTAR USUARIO ROOT POR DEFECTO
-- ============================================================================
-- Contraseña: Root@2025
-- Hash generado con password_hash('Root@2025', PASSWORD_BCRYPT)
INSERT INTO users (username, email, password, fullname, alias, role, is_verified, is_active) VALUES
('@root', 'root@system.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Root Administrator', 'root-admin-system', 'root', 1, 1);

-- ============================================================================
-- CREAR ÍNDICES ADICIONALES PARA OPTIMIZACIÓN
-- ============================================================================
CREATE INDEX idx_users_email_active ON users(email, is_active);
CREATE INDEX idx_users_role_active ON users(role, is_active);
CREATE INDEX idx_activity_logs_user_action ON activity_logs(user_id, action);
CREATE INDEX idx_user_sessions_user_active ON user_sessions(user_id, is_active);

-- ============================================================================
-- FIN DEL SCRIPT
-- ============================================================================
