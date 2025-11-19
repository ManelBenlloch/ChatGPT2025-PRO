<?php
/**
 * AuthMiddleware.php
 * 
 * Middleware de Autenticación
 * 
 * Verifica que el usuario esté autenticado antes de acceder a rutas protegidas.
 */

class AuthMiddleware {
    /**
     * Verificar autenticación
     * 
     * @return bool
     */
    public static function check() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    /**
     * Requerir autenticación (redirigir si no está autenticado)
     */
    public static function require() {
        if (!self::check()) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            header('Location: ' . asset('login'));
            exit();
        }
    }

    /**
     * Requerir un rol específico
     * 
     * @param string|array $roles El rol o roles permitidos
     */
    public static function requireRole($roles) {
        self::require();
        
        $roles = is_array($roles) ? $roles : [$roles];
        $userRole = $_SESSION['user_role'] ?? 'user';
        
        if (!in_array($userRole, $roles)) {
            http_response_code(403);
            die("Acceso denegado. No tienes permisos para acceder a esta página.");
        }
    }

    /**
     * Verificar si el usuario tiene un rol específico
     * 
     * @param string $role
     * @return bool
     */
    public static function hasRole($role) {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
    }

    /**
     * Verificar si el usuario es administrador (admin o root)
     * 
     * @return bool
     */
    public static function isAdmin() {
        return isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'root']);
    }

    /**
     * Verificar si el usuario es root
     * 
     * @return bool
     */
    public static function isRoot() {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'root';
    }
}

?>
