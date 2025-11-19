<?php
/**
 * PermissionMiddleware
 * 
 * Middleware para verificar permisos de usuarios
 */

class PermissionMiddleware {
    
    /**
     * Verificar si el usuario tiene un permiso específico
     * 
     * @param string $permissionName
     * @param string $redirectUrl URL de redirección si no tiene permiso
     */
    public static function requirePermission($permissionName, $redirectUrl = null) {
        // Verificar si el usuario está autenticado
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . asset('login'));
            exit();
        }
        
        // Cargar el modelo de permisos
        require_once app_path('app/Models/Permission.php');
        $permissionModel = new Permission();
        
        // Verificar si el usuario tiene el permiso
        if (!$permissionModel->userHasPermission($_SESSION['user_id'], $permissionName)) {
            // Si no tiene permiso, mostrar error 403 o redirigir
            if ($redirectUrl) {
                $_SESSION['error'] = 'No tienes permisos para acceder a esta sección.';
                header('Location: ' . asset($redirectUrl));
                exit();
            } else {
                self::show403();
            }
        }
    }
    
    /**
     * Verificar si el usuario tiene ALGUNO de los permisos
     * 
     * @param array $permissionNames
     * @param string $redirectUrl URL de redirección si no tiene permiso
     */
    public static function requireAnyPermission($permissionNames, $redirectUrl = null) {
        // Verificar si el usuario está autenticado
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . asset('login'));
            exit();
        }
        
        // Cargar el modelo de permisos
        require_once app_path('app/Models/Permission.php');
        $permissionModel = new Permission();
        
        // Verificar si el usuario tiene alguno de los permisos
        if (!$permissionModel->userHasAnyPermission($_SESSION['user_id'], $permissionNames)) {
            // Si no tiene ningún permiso, mostrar error 403 o redirigir
            if ($redirectUrl) {
                $_SESSION['error'] = 'No tienes permisos para acceder a esta sección.';
                header('Location: ' . asset($redirectUrl));
                exit();
            } else {
                self::show403();
            }
        }
    }
    
    /**
     * Verificar si el usuario tiene TODOS los permisos
     * 
     * @param array $permissionNames
     * @param string $redirectUrl URL de redirección si no tiene permiso
     */
    public static function requireAllPermissions($permissionNames, $redirectUrl = null) {
        // Verificar si el usuario está autenticado
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . asset('login'));
            exit();
        }
        
        // Cargar el modelo de permisos
        require_once app_path('app/Models/Permission.php');
        $permissionModel = new Permission();
        
        // Verificar si el usuario tiene todos los permisos
        if (!$permissionModel->userHasAllPermissions($_SESSION['user_id'], $permissionNames)) {
            // Si no tiene todos los permisos, mostrar error 403 o redirigir
            if ($redirectUrl) {
                $_SESSION['error'] = 'No tienes permisos para acceder a esta sección.';
                header('Location: ' . asset($redirectUrl));
                exit();
            } else {
                self::show403();
            }
        }
    }
    
    /**
     * Verificar si el usuario tiene un permiso (sin detener la ejecución)
     * 
     * @param string $permissionName
     * @return bool
     */
    public static function hasPermission($permissionName) {
        // Verificar si el usuario está autenticado
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        
        // Cargar el modelo de permisos
        require_once app_path('app/Models/Permission.php');
        $permissionModel = new Permission();
        
        return $permissionModel->userHasPermission($_SESSION['user_id'], $permissionName);
    }
    
    /**
     * Mostrar página de error 403
     */
    private static function show403() {
        http_response_code(403);
        echo "<!DOCTYPE html>";
        echo "<html lang='es'>";
        echo "<head>";
        echo "<meta charset='UTF-8'>";
        echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
        echo "<title>403 - Acceso Denegado</title>";
        echo "<link rel='stylesheet' href='" . asset('assets/css/style.css') . "'>";
        echo "<style>";
        echo "body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); margin: 0; padding: 0; display: flex; justify-content: center; align-items: center; min-height: 100vh; }";
        echo ".error-container { background: white; border-radius: 20px; padding: 60px 40px; text-align: center; box-shadow: 0 20px 60px rgba(0,0,0,0.3); max-width: 500px; }";
        echo ".error-code { font-size: 120px; font-weight: bold; color: #667eea; margin: 0; line-height: 1; }";
        echo ".error-title { font-size: 32px; color: #333; margin: 20px 0 10px; }";
        echo ".error-message { font-size: 18px; color: #666; margin: 20px 0 40px; line-height: 1.6; }";
        echo ".btn { display: inline-block; padding: 15px 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-decoration: none; border-radius: 50px; font-weight: 600; transition: transform 0.3s, box-shadow 0.3s; }";
        echo ".btn:hover { transform: translateY(-2px); box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4); }";
        echo "</style>";
        echo "</head>";
        echo "<body>";
        echo "<div class='error-container'>";
        echo "<div class='error-code'>403</div>";
        echo "<h1 class='error-title'>Acceso Denegado</h1>";
        echo "<p class='error-message'>No tienes permisos suficientes para acceder a esta sección del sistema.</p>";
        echo "<a href='" . asset('dashboard') . "' class='btn'>Volver al Dashboard</a>";
        echo "</div>";
        echo "</body>";
        echo "</html>";
        exit();
    }
}
?>
