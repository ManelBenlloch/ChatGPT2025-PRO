<?php
/**
 * AdminController.php
 * 
 * Controlador de Administración
 * 
 * Gestiona el panel de administración y las funciones administrativas.
 */

require_once app_path('core/Controller.php');
require_once app_path('app/Middleware/AuthMiddleware.php');

class AdminController extends Controller {
    
    /**
     * Mostrar el dashboard de administración
     */
    public function dashboard() {
        // Requerir rol de admin o root
        AuthMiddleware::requireRole(['admin', 'root']);

        $userModel = $this->model('User');
        
        // Obtener estadísticas
        $stats = [
            'total_users' => $this->getTotalUsers(),
            'active_users' => $this->getActiveUsers(),
            'verified_users' => $this->getVerifiedUsers(),
            'admin_users' => $this->getAdminUsers(),
        ];

        // Obtener usuarios recientes
        $recentUsers = $this->getRecentUsers(10);

        $this->view('admin/dashboard', [
            'stats' => $stats,
            'recentUsers' => $recentUsers
        ]);
    }

    /**
     * Listar todos los usuarios
     */
    public function listUsers() {
        AuthMiddleware::requireRole(['admin', 'root']);

        $userModel = $this->model('User');
        $users = $userModel->all();

        $this->view('admin/users', ['users' => $users]);
    }

    /**
     * Obtener total de usuarios
     */
    private function getTotalUsers() {
        $stmt = $GLOBALS['pdo']->query("SELECT COUNT(*) as total FROM users WHERE deleted_at IS NULL");
        $result = $stmt->fetch();
        return $result->total ?? 0;
    }

    /**
     * Obtener usuarios activos
     */
    private function getActiveUsers() {
        $stmt = $GLOBALS['pdo']->query("SELECT COUNT(*) as total FROM users WHERE is_active = 1 AND deleted_at IS NULL");
        $result = $stmt->fetch();
        return $result->total ?? 0;
    }

    /**
     * Obtener usuarios verificados
     */
    private function getVerifiedUsers() {
        $stmt = $GLOBALS['pdo']->query("SELECT COUNT(*) as total FROM users WHERE email_verified = 1 AND deleted_at IS NULL");
        $result = $stmt->fetch();
        return $result->total ?? 0;
    }

    /**
     * Obtener usuarios administradores
     */
    private function getAdminUsers() {
        $stmt = $GLOBALS['pdo']->query("SELECT COUNT(*) as total FROM users WHERE role IN ('admin', 'root') AND deleted_at IS NULL");
        $result = $stmt->fetch();
        return $result->total ?? 0;
    }

    /**
     * Obtener usuarios recientes
     */
    private function getRecentUsers($limit = 10) {
        $stmt = $GLOBALS['pdo']->prepare("SELECT * FROM users WHERE deleted_at IS NULL ORDER BY created_at DESC LIMIT ?");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    /**
     * Mostrar usuarios eliminados (soft delete)
     */
    public function deletedUsers() {
        AuthMiddleware::requireRole(['admin', 'root']);

        $userModel = $this->model('User');
        $deletedUsers = $userModel->getDeletedUsers();

        $this->view('admin/deleted_users', [
            'deletedUsers' => $deletedUsers
        ]);
    }

    /**
     * Restaurar un usuario eliminado
     */
    public function restoreUser() {
        AuthMiddleware::requireRole(['admin', 'root']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/deleted-users');
            return;
        }

        $userId = $_POST['user_id'] ?? null;

        if (!$userId) {
            $_SESSION['error'] = 'ID de usuario no válido.';
            $this->redirect('admin/deleted-users');
            return;
        }

        $userModel = $this->model('User');
        $userModel->restore($userId);

        // Registrar en activity log
        require_once app_path('app/Models/ActivityLog.php');
        $activityLog = new ActivityLog();
        $activityLog->log(
            $_SESSION['user_id'],
            'user_restored',
            "Usuario restaurado: ID $userId",
            ['restored_user_id' => $userId]
        );

        $_SESSION['success'] = 'Usuario restaurado correctamente.';
        $this->redirect('admin/deleted-users');
    }

    /**
     * Eliminar permanentemente un usuario
     */
    public function permanentDeleteUser() {
        AuthMiddleware::requireRole(['root']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/deleted-users');
            return;
        }

        $userId = $_POST['user_id'] ?? null;

        if (!$userId) {
            $_SESSION['error'] = 'ID de usuario no válido.';
            $this->redirect('admin/deleted-users');
            return;
        }

        $userModel = $this->model('User');
        $userModel->delete($userId);

        // Registrar en activity log
        require_once app_path('app/Models/ActivityLog.php');
        $activityLog = new ActivityLog();
        $activityLog->log(
            $_SESSION['user_id'],
            'user_permanently_deleted',
            "Usuario eliminado permanentemente: ID $userId",
            ['deleted_user_id' => $userId]
        );

        $_SESSION['success'] = 'Usuario eliminado permanentemente.';
        $this->redirect('admin/deleted-users');
    }
}

?>
