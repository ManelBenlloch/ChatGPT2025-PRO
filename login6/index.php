<?php
/**
 * index.php
 * 
 * Punto de Entrada Único de la Aplicación
 * 
 * Este es el ÚNICO archivo PHP que debe ser accesible directamente desde el navegador.
 * Todas las peticiones pasan por aquí y son dirigidas al controlador correcto por el Router.
 */

// Cargar el script de portabilidad
require_once __DIR__ . '/app_boot.php';

// Cargar la configuración
require_once app_path('config/config.php');

// Cargar el Router
require_once app_path('core/Router.php');

// Cargar Middleware
require_once app_path('app/Middleware/WAFMiddleware.php');
require_once app_path('app/Middleware/AuthMiddleware.php');

// Ejecutar WAF
WAFMiddleware::check();

// Crear una instancia del Router
$router = new Router();

// ============================================================================
// DEFINIR RUTAS
// ============================================================================

// Ruta raíz - Mostrar el login directamente
$router->get('/', function() {
    require_once app_path('app/Controllers/AuthController.php');
    $controller = new AuthController();
    $controller->showLogin();
});

// Rutas de Autenticación
$router->get('/login', function() {
    require_once app_path('app/Controllers/AuthController.php');
    $controller = new AuthController();
    $controller->showLogin();
});

$router->post('/login', function() {
    require_once app_path('app/Controllers/AuthController.php');
    $controller = new AuthController();
    $controller->handleLogin();
});

$router->get('/register', function() {
    require_once app_path('app/Controllers/AuthController.php');
    $controller = new AuthController();
    $controller->showRegister();
});

$router->post('/register', function() {
    require_once app_path('app/Controllers/AuthController.php');
    $controller = new AuthController();
    $controller->handleRegister();
});

$router->get('/logout', function() {
    require_once app_path('app/Controllers/AuthController.php');
    $controller = new AuthController();
    $controller->logout();
});

$router->get('/verify-email', function() {
    require_once app_path('app/Controllers/AuthController.php');
    $controller = new AuthController();
    $controller->verifyEmail();
});

$router->get('/reset-password', function() {
    require_once app_path('app/Controllers/AuthController.php');
    $controller = new AuthController();
    $controller->showResetPassword();
});

$router->post('/reset-password', function() {
    require_once app_path('app/Controllers/AuthController.php');
    $controller = new AuthController();
    $controller->handleResetPasswordRequest();
});

// Rutas de Administración
$router->get('/admin/dashboard', function() {
    require_once app_path('app/Controllers/AdminController.php');
    $controller = new AdminController();
    $controller->dashboard();
});

$router->get('/admin/users', function() {
    require_once app_path('app/Controllers/AdminController.php');
    $controller = new AdminController();
    $controller->listUsers();
});

$router->get('/admin/deleted-users', function() {
    require_once app_path('app/Controllers/AdminController.php');
    $controller = new AdminController();
    $controller->deletedUsers();
});

$router->post('/admin/restore-user', function() {
    require_once app_path('app/Controllers/AdminController.php');
    $controller = new AdminController();
    $controller->restoreUser();
});

$router->post('/admin/permanent-delete-user', function() {
    require_once app_path('app/Controllers/AdminController.php');
    $controller = new AdminController();
    $controller->permanentDeleteUser();
});

// Rutas de 2FA
$router->get('/2fa/setup', function() {
    require_once app_path('app/Controllers/TwoFactorController.php');
    $controller = new TwoFactorController();
    $controller->setup();
});

$router->post('/2fa/verify', function() {
    require_once app_path('app/Controllers/TwoFactorController.php');
    $controller = new TwoFactorController();
    $controller->verify();
});

$router->get('/2fa/challenge', function() {
    require_once app_path('app/Controllers/TwoFactorController.php');
    $controller = new TwoFactorController();
    $controller->challenge();
});

$router->post('/2fa/validate', function() {
    require_once app_path('app/Controllers/TwoFactorController.php');
    $controller = new TwoFactorController();
    $controller->validateChallenge();
});

$router->post('/2fa/disable', function() {
    require_once app_path('app/Controllers/TwoFactorController.php');
    $controller = new TwoFactorController();
    $controller->disable();
});

// Rutas de Sesiones
$router->get('/sessions', function() {
    require_once app_path('app/Controllers/SessionController.php');
    $controller = new SessionController();
    $controller->index();
});

$router->post('/sessions/revoke', function() {
    require_once app_path('app/Controllers/SessionController.php');
    $controller = new SessionController();
    $controller->revoke();
});

$router->post('/sessions/revoke-others', function() {
    require_once app_path('app/Controllers/SessionController.php');
    $controller = new SessionController();
    $controller->revokeOthers();
});

// Rutas de Root
$router->get('/root/dashboard', function() {
    require_once app_path('app/Controllers/RootController.php');
    $controller = new RootController();
    $controller->dashboard();
});

$router->get('/root/settings', function() {
    require_once app_path('app/Controllers/RootController.php');
    $controller = new RootController();
    $controller->settings();
});

$router->get('/root/logs', function() {
    require_once app_path('app/Controllers/RootController.php');
    $controller = new RootController();
    $controller->logs();
});

$router->post('/root/clean-sessions', function() {
    require_once app_path('app/Controllers/RootController.php');
    $controller = new RootController();
    $controller->cleanSessions();
});

$router->post('/root/unblock-ip', function() {
    require_once app_path('app/Controllers/RootController.php');
    $controller = new RootController();
    $controller->unblockIp();
});

$router->get('/root/phpinfo', function() {
    require_once app_path('app/Controllers/RootController.php');
    $controller = new RootController();
    $controller->phpinfo();
});

$router->get('/root/gestion-usuarios', function() {
    require_once app_path('app/Controllers/RootController.php');
    $controller = new RootController();
    $controller->gestionUsuarios();
});

$router->get('/root/gestion-sistema', function() {
    require_once app_path('app/Controllers/RootController.php');
    $controller = new RootController();
    $controller->gestionSistema();
});

$router->get('/root/seguridad', function() {
    require_once app_path('app/Controllers/RootController.php');
    $controller = new RootController();
    $controller->seguridad();
});

$router->get('/root/analytics', function() {
    require_once app_path('app/Controllers/RootController.php');
    $controller = new RootController();
    $controller->analytics();
});

$router->get('/root/configuracion', function() {
    require_once app_path('app/Controllers/RootController.php');
    $controller = new RootController();
    $controller->configuracion();
});

$router->get('/root/database', function() {
    require_once app_path('app/Controllers/RootController.php');
    $controller = new RootController();
    $controller->database();
});

$router->get('/root/emergencia', function() {
    require_once app_path('app/Controllers/RootController.php');
    $controller = new RootController();
    $controller->emergencia();
});

$router->get('/root/waf-rules', function() {
    require_once app_path('app/Controllers/RootController.php');
    $controller = new RootController();
    $controller->wafRules();
});

// ============================================================================
// RUTAS DE GESTIÓN DE ROLES Y PERMISOS
// ============================================================================

// Listar roles
$router->get('/roles', function() {
    require_once app_path('app/Controllers/RoleController.php');
    $controller = new RoleController();
    $controller->index();
});

// Crear rol
$router->get('/roles/create', function() {
    require_once app_path('app/Controllers/RoleController.php');
    $controller = new RoleController();
    $controller->create();
});

$router->post('/roles/store', function() {
    require_once app_path('app/Controllers/RoleController.php');
    $controller = new RoleController();
    $controller->store();
});

// Editar rol
$router->get('/roles/:id/edit', function($id) {
    require_once app_path('app/Controllers/RoleController.php');
    $controller = new RoleController();
    $controller->edit($id);
});

$router->post('/roles/:id/update', function($id) {
    require_once app_path('app/Controllers/RoleController.php');
    $controller = new RoleController();
    $controller->update($id);
});

// Eliminar rol
$router->post('/roles/:id/delete', function($id) {
    require_once app_path('app/Controllers/RoleController.php');
    $controller = new RoleController();
    $controller->delete($id);
});

// Gestionar permisos de rol
$router->get('/roles/:id/permissions', function($id) {
    require_once app_path('app/Controllers/RoleController.php');
    $controller = new RoleController();
    $controller->managePermissions($id);
});

$router->post('/roles/:id/save-permissions', function($id) {
    require_once app_path('app/Controllers/RoleController.php');
    $controller = new RoleController();
    $controller->savePermissions($id);
});

// Rutas de API REST
$router->get('/api/users', function() {
    require_once app_path('app/Controllers/ApiController.php');
    $controller = new ApiController();
    $controller->getUsers();
});

$router->get('/api/users/:id', function($id) {
    require_once app_path('app/Controllers/ApiController.php');
    $controller = new ApiController();
    $controller->getUser($id);
});

$router->post('/api/users', function() {
    require_once app_path('app/Controllers/ApiController.php');
    $controller = new ApiController();
    $controller->createUser();
});

$router->put('/api/users/:id', function($id) {
    require_once app_path('app/Controllers/ApiController.php');
    $controller = new ApiController();
    $controller->updateUser($id);
});

$router->delete('/api/users/:id', function($id) {
    require_once app_path('app/Controllers/ApiController.php');
    $controller = new ApiController();
    $controller->deleteUser($id);
});

$router->get('/api/stats', function() {
    require_once app_path('app/Controllers/ApiController.php');
    $controller = new ApiController();
    $controller->getStats();
});

$router->post('/api/check-email', function() {
    require_once app_path('app/Controllers/ApiController.php');
    $controller = new ApiController();
    $controller->checkEmail();
});

$router->post('/api/check-username', function() {
    require_once app_path('app/Controllers/ApiController.php');
    $controller = new ApiController();
    $controller->checkUsername();
});

// Ruta de Dashboard de Personal
$router->get('/personal/dashboard', function() {
    AuthMiddleware::requireRole('personal');
    echo "<!DOCTYPE html>";
    echo "<html lang='es'>";
    echo "<head>";
    echo "<meta charset='UTF-8'>";
    echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
    echo "<title>Dashboard Personal - " . APP_NAME . "</title>";
    echo "<link rel='stylesheet' href='" . asset('assets/css/style.css') . "'>";
    echo "</head>";
    echo "<body>";
    echo "<div style='max-width: 1200px; margin: 40px auto; padding: 20px;'>";
    echo "<h1>Dashboard de Personal</h1>";
    echo "<p>Bienvenido, " . htmlspecialchars($_SESSION['user_fullname']) . "!</p>";
    echo "<p>Tu rol es: <strong>" . htmlspecialchars($_SESSION['user_role']) . "</strong></p>";
    echo "<a href='" . asset('logout') . "' class='btn btn-secondary'>Cerrar Sesión</a>";
    echo "</div>";
    echo "</body>";
    echo "</html>";
});

// Ruta de Dashboard (ejemplo)
$router->get('/dashboard', function() {
    // Verificar autenticación
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . asset('login'));
        exit();
    }
    
    echo "<!DOCTYPE html>";
    echo "<html lang='es'>";
    echo "<head>";
    echo "<meta charset='UTF-8'>";
    echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
    echo "<title>Dashboard - " . APP_NAME . "</title>";
    echo "<link rel='stylesheet' href='" . asset('assets/css/style.css') . "'>";
    echo "</head>";
    echo "<body>";
    echo "<div style='max-width: 1200px; margin: 40px auto; padding: 20px;'>";
    echo "<h1>Bienvenido, " . htmlspecialchars($_SESSION['user_fullname']) . "!</h1>";
    echo "<p>Has iniciado sesión correctamente.</p>";
    echo "<p>Tu rol es: <strong>" . htmlspecialchars($_SESSION['user_role']) . "</strong></p>";
    echo "<a href='" . asset('logout') . "' class='btn btn-secondary'>Cerrar Sesión</a>";
    echo "</div>";
    echo "</body>";
    echo "</html>";
});

// Ruta 404 - Página no encontrada
$router->notFound(function() {
    http_response_code(404);
    echo "<!DOCTYPE html>";
    echo "<html lang='es'>";
    echo "<head>";
    echo "<meta charset='UTF-8'>";
    echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
    echo "<title>404 - Página no encontrada</title>";
    echo "<link rel='stylesheet' href='" . asset('assets/css/style.css') . "'>";
    echo "</head>";
    echo "<body>";
    echo "<div style='text-align: center; margin-top: 100px;'>";
    echo "<h1 style='font-size: 4rem; color: #4f46e5;'>404</h1>";
    echo "<p style='font-size: 1.5rem; color: #6b7280;'>Página no encontrada</p>";
    echo "<a href='" . asset('login') . "' class='btn btn-primary' style='display: inline-block; margin-top: 20px;'>Volver al inicio</a>";
    echo "</div>";
    echo "</body>";
    echo "</html>";
});

// ============================================================================
// RESOLVER LA RUTA ACTUAL
// ============================================================================

$router->resolve();

?>
