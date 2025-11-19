<?php
/**
 * RootController.php
 * 
 * Controlador del Panel de Root
 * 
 * Proporciona acceso a información del sistema y funcionalidades
 * avanzadas solo para usuarios con rol root.
 */

require_once app_path('core/Controller.php');
require_once app_path('app/Models/User.php');
require_once app_path('app/Models/ActivityLog.php');
require_once app_path('app/Models/UserSession.php');
require_once app_path('app/Models/RateLimit.php');

class RootController extends Controller {
    private $userModel;
    private $activityLog;
    private $sessionModel;
    private $rateLimitModel;

    public function __construct() {
        $this->userModel = $this->model('User');
        $this->activityLog = new ActivityLog();
        $this->sessionModel = new UserSession();
        $this->rateLimitModel = new RateLimit();
    }

    /**
     * Verificar que el usuario es root
     */
    private function ensureRoot() {
        if (!$this->isAuthenticated()) {
            $this->redirect('login');
            return false;
        }

        $user = $this->userModel->findById($_SESSION['user_id']);
        if ($user->role !== 'root') {
            $_SESSION['error'] = 'Acceso denegado. Solo usuarios root pueden acceder a esta sección.';
            $this->redirect('dashboard');
            return false;
        }

        return true;
    }

    /**
     * Dashboard del panel de root
     */
    public function dashboard() {
        if (!$this->ensureRoot()) return;

        // Obtener información del sistema
        $systemInfo = $this->getSystemInfo();
        
        // Obtener estadísticas de la base de datos
        $dbStats = $this->getDatabaseStats();
        
        // Obtener actividad reciente
        $recentActivity = $this->activityLog->getRecentLogs(20);
        
        // Obtener usuarios bloqueados
        $blockedIps = $this->rateLimitModel->getBlockedIps();
        
        $this->view('root/dashboard', [
            'systemInfo' => $systemInfo,
            'dbStats' => $dbStats,
            'recentActivity' => $recentActivity,
            'blockedIps' => $blockedIps
        ]);
    }

    /**
     * Obtener información del sistema
     */
    private function getSystemInfo() {
        return [
            'php_version' => phpversion(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Desconocido',
            'server_name' => $_SERVER['SERVER_NAME'] ?? 'Desconocido',
            'server_ip' => $_SERVER['SERVER_ADDR'] ?? 'Desconocido',
            'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Desconocido',
            'max_execution_time' => ini_get('max_execution_time'),
            'memory_limit' => ini_get('memory_limit'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'timezone' => date_default_timezone_get(),
            'current_time' => date('Y-m-d H:i:s'),
            'disk_free_space' => $this->formatBytes(disk_free_space('/')),
            'disk_total_space' => $this->formatBytes(disk_total_space('/')),
        ];
    }

    /**
     * Obtener estadísticas de la base de datos
     */
    private function getDatabaseStats() {
        $pdo = $this->userModel->getPDO();
        
        $stats = [];
        
        // Contar registros en cada tabla
        $tables = ['users', 'activity_logs', 'user_sessions', 'mfa_factors', 'rate_limits', 'waf_rules'];
        foreach ($tables as $table) {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
            $result = $stmt->fetch();
            $stats[$table] = $result->count;
        }
        
        // Tamaño de la base de datos
        $stmt = $pdo->query("SELECT SUM(data_length + index_length) as size FROM information_schema.TABLES WHERE table_schema = DATABASE()");
        $result = $stmt->fetch();
        $stats['database_size'] = $this->formatBytes($result->size ?? 0);
        
        return $stats;
    }

    /**
     * Formatear bytes a formato legible
     */
    private function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Página de configuración del sistema
     */
    public function settings() {
        if (!$this->ensureRoot()) return;

        $this->view('root/settings', []);
    }

    /**
     * Página de logs del sistema
     */
    public function logs() {
        if (!$this->ensureRoot()) return;

        // Obtener todos los logs con paginación
        $page = $_GET['page'] ?? 1;
        $perPage = 50;
        $offset = ($page - 1) * $perPage;
        
        $logs = $this->activityLog->getRecentLogs($perPage, $offset);
        $totalLogs = $this->activityLog->getTotalLogs();
        $totalPages = ceil($totalLogs / $perPage);
        
        $this->view('root/logs', [
            'logs' => $logs,
            'currentPage' => $page,
            'totalPages' => $totalPages
        ]);
    }

    /**
     * Limpiar sesiones expiradas
     */
    public function cleanSessions() {
        if (!$this->ensureRoot()) return;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('root/dashboard');
            return;
        }

        $cleaned = $this->sessionModel->cleanExpiredSessions();
        
        $_SESSION['success'] = "Se limpiaron $cleaned sesiones expiradas.";
        $this->redirect('root/dashboard');
    }

    /**
     * Desbloquear una IP
     */
    public function unblockIp() {
        if (!$this->ensureRoot()) return;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('root/dashboard');
            return;
        }

        $ipAddress = $_POST['ip_address'] ?? '';
        
        if (empty($ipAddress)) {
            $_SESSION['error'] = 'Dirección IP no válida.';
            $this->redirect('root/dashboard');
            return;
        }

        $this->rateLimitModel->unlockIp($ipAddress);
        
        $_SESSION['success'] = "IP $ipAddress desbloqueada correctamente.";
        $this->redirect('root/dashboard');
    }

    /**
     * Información de PHP
     */
    public function phpinfo() {
        if (!$this->ensureRoot()) return;

        // Mostrar phpinfo en un iframe o página separada
        phpinfo();
        exit;
    }

    /**
     * Gestión de Usuarios
     */
    public function gestionUsuarios() {
        if (!$this->ensureRoot()) return;
        
        // Obtener todos los usuarios (incluyendo eliminados)
        $pdo = $this->userModel->getPDO();
        $stmt = $pdo->query("SELECT * FROM users ORDER BY id ASC");
        $users = $stmt->fetchAll(PDO::FETCH_OBJ);
        
        $this->view('root/gestion_usuarios', [
            'users' => $users
        ]);
    }

    /**
     * Gestión del Sistema
     */
    public function gestionSistema() {
        if (!$this->ensureRoot()) return;
        $this->view('root/gestion_sistema', []);
    }

    /**
     * Configuración de Seguridad
     */
    public function seguridad() {
        if (!$this->ensureRoot()) return;
        $this->view('root/seguridad', []);
    }

    /**
     * Estadísticas y Analytics
     */
    public function analytics() {
        if (!$this->ensureRoot()) return;
        $this->view('root/analytics', []);
    }

    /**
     * Configuración Global
     */
    public function configuracion() {
        if (!$this->ensureRoot()) return;
        $this->view('root/configuracion', []);
    }

    /**
     * Administración de Base de Datos
     */
    public function database() {
        if (!$this->ensureRoot()) return;
        $this->view('root/database', []);
    }

    /**
     * Sistema de Emergencia
     */
    public function emergencia() {
        if (!$this->ensureRoot()) return;
        $this->view('root/emergencia', []);
    }

    /**
     * Reglas WAF
     */
    public function wafRules() {
        if (!$this->ensureRoot()) return;
        $this->view('root/waf_rules', []);
    }
}

?>
