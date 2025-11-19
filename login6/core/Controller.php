<?php
/**
 * Controller.php
 * 
 * Clase Base para Todos los Controladores
 * 
 * Proporciona métodos comunes que todos los controladores pueden usar.
 */

class Controller {
    /**
     * Cargar un modelo
     * 
     * @param string $model El nombre del modelo (ej. "User")
     * @return object La instancia del modelo
     */
    protected function model($model) {
        $modelPath = app_path("app/Models/{$model}.php");
        
        if (file_exists($modelPath)) {
            require_once $modelPath;
            return new $model();
        } else {
            die("El modelo {$model} no existe.");
        }
    }

    /**
     * Cargar una vista
     * 
     * @param string $view El nombre de la vista (ej. "auth/login")
     * @param array $data Datos a pasar a la vista
     */
    protected function view($view, $data = []) {
        $viewPath = app_path("views/{$view}.php");
        
        if (file_exists($viewPath)) {
            // Extraer los datos para que estén disponibles como variables en la vista
            extract($data);
            require_once $viewPath;
        } else {
            die("La vista {$view} no existe.");
        }
    }

    /**
     * Redirigir a otra URL
     * 
     * @param string $path La ruta a la que redirigir (ej. "login", "dashboard")
     */
    protected function redirect($path) {
        $url = asset($path);
        header("Location: {$url}");
        exit();
    }

    /**
     * Devolver una respuesta JSON
     * 
     * @param array $data Los datos a devolver
     * @param int $statusCode El código de estado HTTP
     */
    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }

    /**
     * Verificar si el usuario está autenticado
     * 
     * @return bool
     */
    protected function isAuthenticated() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    /**
     * Obtener el usuario actual de la sesión
     * 
     * @return object|null El objeto del usuario o null si no está autenticado
     */
    protected function getCurrentUser() {
        if ($this->isAuthenticated()) {
            $userModel = $this->model('User');
            return $userModel->findById($_SESSION['user_id']);
        }
        return null;
    }

    /**
     * Requerir autenticación (redirigir al login si no está autenticado)
     */
    protected function requireAuth() {
        if (!$this->isAuthenticated()) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            $this->redirect('login');
        }
    }

    /**
     * Requerir un rol específico
     * 
     * @param string|array $roles El rol o roles permitidos (ej. "admin" o ["admin", "root"])
     */
    protected function requireRole($roles) {
        $this->requireAuth();
        
        $user = $this->getCurrentUser();
        $roles = is_array($roles) ? $roles : [$roles];
        
        if (!in_array($user->role, $roles)) {
            http_response_code(403);
            die("Acceso denegado. No tienes permisos para acceder a esta página.");
        }
    }
}

?>
