<?php
/**
 * ApiController.php
 * 
 * Controlador de API REST
 * 
 * Proporciona endpoints JSON para integración externa y aplicaciones frontend.
 */

require_once app_path('core/Controller.php');
require_once app_path('app/Models/User.php');
require_once app_path('app/Models/ActivityLog.php');

class ApiController extends Controller {
    private $userModel;
    private $activityLog;

    public function __construct() {
        $this->userModel = $this->model('User');
        $this->activityLog = new ActivityLog();
        
        // Configurar headers para API REST
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        
        // Manejar preflight OPTIONS
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }

    /**
     * Respuesta JSON exitosa
     */
    private function jsonSuccess($data = [], $message = 'Success', $statusCode = 200) {
        http_response_code($statusCode);
        echo json_encode([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Respuesta JSON de error
     */
    private function jsonError($message = 'Error', $statusCode = 400, $errors = []) {
        http_response_code($statusCode);
        echo json_encode([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Verificar autenticación por token Bearer
     */
    private function authenticateToken() {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? '';
        
        if (empty($authHeader) || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $this->jsonError('Token de autenticación requerido', 401);
        }
        
        $token = $matches[1];
        
        // Aquí deberías verificar el token JWT o similar
        // Por simplicidad, usamos la sesión PHP
        session_start();
        if (!isset($_SESSION['user_id'])) {
            $this->jsonError('Token inválido o expirado', 401);
        }
        
        return $_SESSION['user_id'];
    }

    /**
     * GET /api/users - Listar todos los usuarios (solo admin/root)
     */
    public function getUsers() {
        $userId = $this->authenticateToken();
        
        // Verificar permisos
        $user = $this->userModel->findById($userId);
        if (!in_array($user->role, ['admin', 'root'])) {
            $this->jsonError('No tienes permisos para esta acción', 403);
        }
        
        $users = $this->userModel->getAllUsers();
        
        // Ocultar información sensible
        foreach ($users as &$u) {
            unset($u->password_hash);
            unset($u->verification_token);
            unset($u->reset_token);
        }
        
        $this->jsonSuccess($users, 'Usuarios obtenidos correctamente');
    }

    /**
     * GET /api/users/:id - Obtener un usuario específico
     */
    public function getUser($id) {
        $userId = $this->authenticateToken();
        
        // Verificar permisos (solo puede ver su propio perfil o ser admin/root)
        $user = $this->userModel->findById($userId);
        if ($userId != $id && !in_array($user->role, ['admin', 'root'])) {
            $this->jsonError('No tienes permisos para esta acción', 403);
        }
        
        $targetUser = $this->userModel->findById($id);
        if (!$targetUser) {
            $this->jsonError('Usuario no encontrado', 404);
        }
        
        // Ocultar información sensible
        unset($targetUser->password_hash);
        unset($targetUser->verification_token);
        unset($targetUser->reset_token);
        
        $this->jsonSuccess($targetUser, 'Usuario obtenido correctamente');
    }

    /**
     * POST /api/users - Crear un nuevo usuario (solo admin/root)
     */
    public function createUser() {
        $userId = $this->authenticateToken();
        
        // Verificar permisos
        $user = $this->userModel->findById($userId);
        if (!in_array($user->role, ['admin', 'root'])) {
            $this->jsonError('No tienes permisos para esta acción', 403);
        }
        
        // Obtener datos JSON
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Validar datos requeridos
        $required = ['fullname', 'username', 'email', 'password'];
        $errors = [];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                $errors[$field] = "El campo $field es requerido";
            }
        }
        
        if (!empty($errors)) {
            $this->jsonError('Datos inválidos', 400, $errors);
        }
        
        // Verificar si el email ya existe
        if ($this->userModel->findByEmail($input['email'])) {
            $this->jsonError('El email ya está registrado', 400);
        }
        
        // PROTECCIÓN: Solo root puede crear usuarios con rol 'root'
        $roleToAssign = $input['role'] ?? 'user';
        if ($roleToAssign === 'root' && $user->role !== 'root') {
            $this->jsonError('Solo el usuario root puede crear otros usuarios root', 403);
        }
        
        // Preparar datos del usuario
        $userData = [
            'fullname' => $input['fullname'],
            'username' => $input['username'],
            'alias' => $input['alias'] ?? $input['username'],
            'email' => $input['email'],
            'password_hash' => password_hash($input['password'], PASSWORD_BCRYPT),
            'email_verified' => 1 // Auto-verificado por admin
        ];
        
        // Asignar rol: puede ser un rol del sistema (role) o un rol personalizado (role_id)
        // Solo uno de los dos debe estar asignado
        if (isset($input['role_id']) && $input['role_id']) {
            // Rol personalizado
            $userData['role_id'] = $input['role_id'];
            $userData['role'] = 'user'; // Default para usuarios con rol personalizado
        } else {
            // Rol del sistema
            $userData['role'] = $roleToAssign;
            $userData['role_id'] = null;
        }
        
        // Crear usuario
        $newUserId = $this->userModel->create($userData);
        
        // Registrar en activity log
        $this->activityLog->log($userId, 'user_created_via_api', "Usuario creado vía API: {$input['email']}", ['new_user_id' => $newUserId]);
        
        $this->jsonSuccess(['id' => $newUserId], 'Usuario creado correctamente', 201);
    }

    /**
     * PUT /api/users/:id - Actualizar un usuario
     */
    public function updateUser($id) {
        $userId = $this->authenticateToken();
        
        // Verificar permisos
        $user = $this->userModel->findById($userId);
        if ($userId != $id && !in_array($user->role, ['admin', 'root'])) {
            $this->jsonError('No tienes permisos para esta acción', 403);
        }
        
        // Obtener usuario a actualizar
        $targetUser = $this->userModel->findById($id);
        if (!$targetUser) {
            $this->jsonError('Usuario no encontrado', 404);
        }
        
        // PROTECCIÓN: Admin no puede modificar usuarios root
        if ($targetUser->role === 'root' && $user->role !== 'root') {
            $this->jsonError('Solo el usuario root puede modificar otros usuarios root', 403);
        }
        
        // Obtener datos JSON
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Campos permitidos para actualizar
        $allowedFields = ['fullname', 'username', 'alias'];
        if (in_array($user->role, ['admin', 'root'])) {
            $allowedFields[] = 'is_active';
            
            // Solo root puede cambiar el rol a 'root'
            if ($user->role === 'root') {
                $allowedFields[] = 'role';
                $allowedFields[] = 'role_id';
            } else {
                // Admin puede cambiar roles excepto asignar 'root'
                if (isset($input['role']) && $input['role'] !== 'root') {
                    $allowedFields[] = 'role';
                }
                if (isset($input['role_id'])) {
                    $allowedFields[] = 'role_id';
                }
            }
        }
        
        $updateData = [];
        foreach ($allowedFields as $field) {
            if (isset($input[$field])) {
                $updateData[$field] = $input[$field];
            }
        }
        
        // VALIDACIÓN: Asegurarse de que solo se asigne role O role_id, no ambos
        if (isset($updateData['role']) && isset($updateData['role_id'])) {
            // Si se proporciona role_id, limpiar role (dejarlo en user por defecto)
            if ($updateData['role_id']) {
                $updateData['role'] = 'user';
            } else {
                // Si role_id es null, limpiar role_id
                $updateData['role_id'] = null;
            }
        }
        
        if (empty($updateData)) {
            $this->jsonError('No hay datos para actualizar', 400);
        }
        
        // Actualizar usuario
        $this->userModel->update($id, $updateData);
        
        // Registrar en activity log
        $this->activityLog->log($userId, 'user_updated_via_api', "Usuario actualizado vía API: ID $id", ['updated_fields' => array_keys($updateData)]);
        
        $this->jsonSuccess([], 'Usuario actualizado correctamente');
    }

    /**
     * DELETE /api/users/:id - Eliminar un usuario (soft delete)
     */
    public function deleteUser($id) {
        $userId = $this->authenticateToken();
        
        // Verificar permisos
        $user = $this->userModel->findById($userId);
        if (!in_array($user->role, ['admin', 'root'])) {
            $this->jsonError('No tienes permisos para esta acción', 403);
        }
        
        // No permitir auto-eliminación
        if ($userId == $id) {
            $this->jsonError('No puedes eliminar tu propia cuenta', 400);
        }
        
        // Obtener usuario a eliminar
        $targetUser = $this->userModel->findById($id);
        if (!$targetUser) {
            $this->jsonError('Usuario no encontrado', 404);
        }
        
        // PROTECCIÓN: Admin no puede eliminar usuarios root
        if ($targetUser->role === 'root' && $user->role !== 'root') {
            $this->jsonError('Solo el usuario root puede eliminar otros usuarios root', 403);
        }
        
        // Soft delete
        $this->userModel->softDelete($id);
        
        // Registrar en activity log
        $this->activityLog->log($userId, 'user_deleted_via_api', "Usuario eliminado vía API: ID $id", ['deleted_user_id' => $id]);
        
        $this->jsonSuccess([], 'Usuario eliminado correctamente');
    }

    /**
     * GET /api/stats - Obtener estadísticas del sistema (solo admin/root)
     */
    public function getStats() {
        $userId = $this->authenticateToken();
        
        // Verificar permisos
        $user = $this->userModel->findById($userId);
        if (!in_array($user->role, ['admin', 'root'])) {
            $this->jsonError('No tienes permisos para esta acción', 403);
        }
        
        $stats = [
            'total_users' => $this->userModel->getTotalUsers(),
            'active_users' => $this->userModel->getActiveUsers(),
            'users_with_2fa' => $this->userModel->getUsersWith2FA(),
            'users_by_role' => $this->userModel->getUsersByRole()
        ];
        
        $this->jsonSuccess($stats, 'Estadísticas obtenidas correctamente');
    }

    /**
     * POST /api/check-email - Verificar si un email está disponible
     */
    public function checkEmail() {
        // Obtener datos JSON
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (empty($input['email'])) {
            $this->jsonError('Email requerido', 400);
        }
        
        $exists = $this->userModel->findByEmail($input['email']) !== null;
        
        $this->jsonSuccess([
            'available' => !$exists,
            'message' => $exists ? 'Email no disponible' : 'Email disponible'
        ]);
    }

    /**
     * POST /api/check-username - Verificar si un username está disponible
     */
    public function checkUsername() {
        // Obtener datos JSON
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (empty($input['username'])) {
            $this->jsonError('Username requerido', 400);
        }
        
        $exists = $this->userModel->findByUsername($input['username']) !== null;
        
        $this->jsonSuccess([
            'available' => !$exists,
            'message' => $exists ? 'Username no disponible' : 'Username disponible'
        ]);
    }
}

?>
