<?php
/**
 * SessionController.php
 * 
 * Controlador de Gestión de Sesiones
 * 
 * Permite a los usuarios ver y gestionar sus sesiones activas.
 */

require_once app_path('core/Controller.php');
require_once app_path('app/Models/UserSession.php');
require_once app_path('app/Models/ActivityLog.php');

class SessionController extends Controller {
    private $sessionModel;
    private $activityLog;

    public function __construct() {
        $this->sessionModel = new UserSession();
        $this->activityLog = new ActivityLog();
    }

    /**
     * Mostrar todas las sesiones activas del usuario
     */
    public function index() {
        // Verificar autenticación
        if (!$this->isAuthenticated()) {
            $this->redirect('login');
            return;
        }

        $userId = $_SESSION['user_id'];
        
        // Obtener sesiones activas
        $sessions = $this->sessionModel->getUserActiveSessions($userId);
        
        // Añadir información del dispositivo a cada sesión
        foreach ($sessions as &$session) {
            $session->device_info = $this->sessionModel->getDeviceInfo($session);
            $session->is_current = $this->sessionModel->isCurrentSession($session);
        }
        
        $this->view('sessions/index', [
            'sessions' => $sessions
        ]);
    }

    /**
     * Cerrar una sesión específica
     */
    public function revoke() {
        // Verificar autenticación
        if (!$this->isAuthenticated()) {
            $this->redirect('login');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('sessions');
            return;
        }

        $sessionId = $_POST['session_id'] ?? null;
        
        if (!$sessionId) {
            $_SESSION['error'] = 'ID de sesión no válido.';
            $this->redirect('sessions');
            return;
        }

        // Verificar que la sesión pertenece al usuario
        $session = $this->sessionModel->findById($sessionId);
        if (!$session || $session->user_id != $_SESSION['user_id']) {
            $_SESSION['error'] = 'No tienes permiso para cerrar esta sesión.';
            $this->redirect('sessions');
            return;
        }

        // Desactivar la sesión
        $this->sessionModel->deactivateSession($sessionId);

        // Registrar en activity log
        $this->activityLog->log(
            $_SESSION['user_id'],
            'session_revoked',
            'Sesión cerrada manualmente desde el panel de gestión',
            ['session_id' => $sessionId]
        );

        $_SESSION['success'] = 'Sesión cerrada correctamente.';
        $this->redirect('sessions');
    }

    /**
     * Cerrar todas las demás sesiones excepto la actual
     */
    public function revokeOthers() {
        // Verificar autenticación
        if (!$this->isAuthenticated()) {
            $this->redirect('login');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('sessions');
            return;
        }

        $userId = $_SESSION['user_id'];
        $currentSessionToken = $_SESSION['session_token'] ?? '';

        // Desactivar todas las demás sesiones
        $this->sessionModel->deactivateOtherSessions($userId, $currentSessionToken);

        // Registrar en activity log
        $this->activityLog->log(
            $userId,
            'all_other_sessions_revoked',
            'Todas las demás sesiones cerradas desde el panel de gestión',
            []
        );

        $_SESSION['success'] = 'Todas las demás sesiones han sido cerradas.';
        $this->redirect('sessions');
    }
}

?>
