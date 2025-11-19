<?php
/**
 * Router.php
 * 
 * Enrutador del Sistema
 * 
 * Gestiona todas las rutas de la aplicación y las dirige al controlador correcto.
 * Soporta rutas dinámicas y parámetros.
 */

class Router {
    private $routes = [];
    private $notFoundCallback;

    /**
     * Registrar una ruta GET
     * 
     * @param string $path La ruta (ej. "/login", "/registro")
     * @param callable $callback La función o método a ejecutar
     */
    public function get($path, $callback) {
        $this->routes['GET'][$path] = $callback;
    }

    /**
     * Registrar una ruta POST
     * 
     * @param string $path La ruta (ej. "/login", "/registro")
     * @param callable $callback La función o método a ejecutar
     */
    public function post($path, $callback) {
        $this->routes['POST'][$path] = $callback;
    }

    /**
     * Registrar una ruta PUT
     * 
     * @param string $path La ruta (ej. "/api/users/:id")
     * @param callable $callback La función o método a ejecutar
     */
    public function put($path, $callback) {
        $this->routes['PUT'][$path] = $callback;
    }

    /**
     * Registrar una ruta DELETE
     * 
     * @param string $path La ruta (ej. "/api/users/:id")
     * @param callable $callback La función o método a ejecutar
     */
    public function delete($path, $callback) {
        $this->routes['DELETE'][$path] = $callback;
    }

    /**
     * Definir el callback para rutas no encontradas (404)
     * 
     * @param callable $callback La función a ejecutar cuando no se encuentra una ruta
     */
    public function notFound($callback) {
        $this->notFoundCallback = $callback;
    }

    /**
     * Resolver la ruta actual y ejecutar el callback correspondiente
     */
    public function resolve() {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];

        // Eliminar la query string de la URI
        $uri = parse_url($uri, PHP_URL_PATH);

        // Eliminar la base de la URL para obtener la ruta relativa
        $base_path = parse_url(APP_BASE_URL, PHP_URL_PATH);
        if ($base_path && strpos($uri, $base_path) === 0) {
            $uri = substr($uri, strlen($base_path));
        }

        // Normalizar la ruta
        $uri = '/' . trim($uri, '/');
        if ($uri !== '/') {
            $uri = rtrim($uri, '/');
        }

        // Buscar la ruta exacta
        if (isset($this->routes[$method][$uri])) {
            return call_user_func($this->routes[$method][$uri]);
        }

        // Buscar rutas con parámetros dinámicos
        foreach ($this->routes[$method] ?? [] as $route => $callback) {
            $pattern = preg_replace('/\{[a-zA-Z0-9_]+\}/', '([a-zA-Z0-9_-]+)', $route);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // Eliminar el primer elemento (la coincidencia completa)
                return call_user_func_array($callback, $matches);
            }
        }

        // Ruta no encontrada
        if ($this->notFoundCallback) {
            return call_user_func($this->notFoundCallback);
        } else {
            http_response_code(404);
            echo "404 - Página no encontrada";
        }
    }
}

?>
