<?php
/**
 * RoleController
 * 
 * Controlador para gestión de roles personalizados
 */

require_once app_path('core/Controller.php');
require_once app_path('app/Models/Role.php');
require_once app_path('app/Models/Permission.php');
require_once app_path('app/Models/ActivityLog.php');
require_once app_path('app/Middleware/PermissionMiddleware.php');

class RoleController extends Controller {
    private $roleModel;
    private $permissionModel;
    private $activityLog;
    
    public function __construct() {
        $this->roleModel = new Role();
        $this->permissionModel = new Permission();
        $this->activityLog = new ActivityLog();
    }
    
    /**
     * Listar todos los roles
     */
    public function index() {
        // Verificar permiso
        PermissionMiddleware::requirePermission('manage_roles');
        
        // Obtener todos los roles (incluyendo los del sistema para visualización)
        $roles = $this->roleModel->getAllRoles(true);
        
        // Obtener cantidad de usuarios por rol
        foreach ($roles as $role) {
            $role->user_count = $this->roleModel->getUserCount($role->id);
        }
        
        $this->view('roles/index', [
            'roles' => $roles
        ]);
    }
    
    /**
     * Mostrar formulario de creación de rol
     */
    public function create() {
        // Verificar permiso
        PermissionMiddleware::requirePermission('manage_roles');
        
        $this->view('roles/create', []);
    }
    
    /**
     * Guardar nuevo rol
     */
    public function store() {
        // Verificar permiso
        PermissionMiddleware::requirePermission('manage_roles');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . asset('roles'));
            exit();
        }
        
        // Validar datos
        $name = trim($_POST['name'] ?? '');
        $display_name = trim($_POST['display_name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        
        if (empty($name) || empty($display_name)) {
            $_SESSION['error'] = 'El nombre y nombre para mostrar son obligatorios.';
            header('Location: ' . asset('roles/create'));
            exit();
        }
        
        // Verificar que el nombre no exista
        if ($this->roleModel->getRoleByName($name)) {
            $_SESSION['error'] = 'Ya existe un rol con ese nombre.';
            header('Location: ' . asset('roles/create'));
            exit();
        }
        
        // Crear el rol
        $roleData = [
            'name' => $name,
            'display_name' => $display_name,
            'description' => $description
        ];
        
        // Add created_by if user_id is available
        if (isset($_SESSION['user_id'])) {
            $roleData['created_by'] = $_SESSION['user_id'];
        }
        
        $roleId = $this->roleModel->createRole($roleData);
        
        if ($roleId) {
            // Registrar actividad
            $this->activityLog->log(
                $_SESSION['user_id'],
                'role_created',
                "Rol creado: {$display_name} (ID: {$roleId})"
            );
            
            $_SESSION['success'] = 'Rol creado exitosamente.';
            header('Location: ' . asset('roles/' . $roleId . '/permissions'));
        } else {
            $_SESSION['error'] = 'Error al crear el rol.';
            header('Location: ' . asset('roles/create'));
        }
        
        exit();
    }
    
    /**
     * Mostrar formulario de edición de rol
     */
    public function edit($roleId) {
        // Verificar permiso
        PermissionMiddleware::requirePermission('manage_roles');
        
        $role = $this->roleModel->getRoleById($roleId);
        
        if (!$role) {
            $_SESSION['error'] = 'Rol no encontrado.';
            header('Location: ' . asset('roles'));
            exit();
        }
        
        // Verificar que no sea un rol del sistema
        if ($role->is_system_role) {
            $_SESSION['error'] = 'No se pueden editar los roles del sistema.';
            header('Location: ' . asset('roles'));
            exit();
        }
        
        $this->view('roles/edit', [
            'role' => $role
        ]);
    }
    
    /**
     * Actualizar rol
     */
    public function update($roleId) {
        // Verificar permiso
        PermissionMiddleware::requirePermission('manage_roles');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . asset('roles'));
            exit();
        }
        
        $role = $this->roleModel->getRoleById($roleId);
        
        if (!$role || $role->is_system_role) {
            $_SESSION['error'] = 'No se puede actualizar este rol.';
            header('Location: ' . asset('roles'));
            exit();
        }
        
        // Validar datos
        $display_name = trim($_POST['display_name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        if (empty($display_name)) {
            $_SESSION['error'] = 'El nombre para mostrar es obligatorio.';
            header('Location: ' . asset('roles/' . $roleId . '/edit'));
            exit();
        }
        
        // Actualizar el rol
        $updated = $this->roleModel->updateRole($roleId, [
            'display_name' => $display_name,
            'description' => $description,
            'is_active' => $is_active
        ]);
        
        if ($updated) {
            // Registrar actividad
            $this->activityLog->log(
                $_SESSION['user_id'],
                'role_updated',
                "Rol actualizado: {$display_name} (ID: {$roleId})"
            );
            
            $_SESSION['success'] = 'Rol actualizado exitosamente.';
        } else {
            $_SESSION['error'] = 'Error al actualizar el rol.';
        }
        
        header('Location: ' . asset('roles'));
        exit();
    }
    
    /**
     * Eliminar rol
     */
    public function delete($roleId) {
        // Verificar permiso
        PermissionMiddleware::requirePermission('manage_roles');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . asset('roles'));
            exit();
        }
        
        $role = $this->roleModel->getRoleById($roleId);
        
        if (!$role) {
            $_SESSION['error'] = 'Rol no encontrado.';
            header('Location: ' . asset('roles'));
            exit();
        }
        
        if ($role->is_system_role) {
            $_SESSION['error'] = 'No se pueden eliminar los roles del sistema.';
            header('Location: ' . asset('roles'));
            exit();
        }
        
        // Verificar que no haya usuarios con este rol
        if ($this->roleModel->getUserCount($roleId) > 0) {
            $_SESSION['error'] = 'No se puede eliminar el rol porque hay usuarios asignados a él.';
            header('Location: ' . asset('roles'));
            exit();
        }
        
        // Eliminar el rol
        $deleted = $this->roleModel->deleteRole($roleId);
        
        if ($deleted) {
            // Registrar actividad
            $this->activityLog->log(
                $_SESSION['user_id'],
                'role_deleted',
                "Rol eliminado: {$role->display_name} (ID: {$roleId})"
            );
            
            $_SESSION['success'] = 'Rol eliminado exitosamente.';
        } else {
            $_SESSION['error'] = 'Error al eliminar el rol.';
        }
        
        header('Location: ' . asset('roles'));
        exit();
    }
    
    /**
     * Gestionar permisos de un rol
     * Permite ver permisos de roles del sistema (solo lectura) y editar permisos de roles personalizados
     */
    public function managePermissions($roleId) {
        // Verificar permiso
        PermissionMiddleware::requirePermission('manage_roles');
        
        $role = $this->roleModel->getRoleById($roleId);
        
        if (!$role) {
            $_SESSION['error'] = 'Rol no encontrado.';
            header('Location: ' . asset('roles'));
            exit();
        }
        
        // Allow viewing system roles in read-only mode
        // (The view will handle showing it as read-only based on is_system_role)
        
        // Obtener todos los permisos agrupados por categoría
        $permissionsByCategory = $this->permissionModel->getPermissionsByCategory();
        
        // Obtener permisos actuales del rol
        $rolePermissionIds = $this->roleModel->getPermissionIds($roleId);
        
        $this->view('roles/permissions', [
            'role' => $role,
            'permissionsByCategory' => $permissionsByCategory,
            'rolePermissionIds' => $rolePermissionIds
        ]);
    }
            'role' => $role,
            'permissionsByCategory' => $permissionsByCategory,
            'rolePermissionIds' => $rolePermissionIds
        ]);
    }
    
    /**
     * Guardar permisos de un rol
     */
    public function savePermissions($roleId) {
        // Verificar permiso
        PermissionMiddleware::requirePermission('manage_roles');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . asset('roles'));
            exit();
        }
        
        $role = $this->roleModel->getRoleById($roleId);
        
        if (!$role || $role->is_system_role) {
            $_SESSION['error'] = 'No se pueden modificar los permisos de este rol.';
            header('Location: ' . asset('roles'));
            exit();
        }
        
        // Obtener permisos seleccionados
        $permissionIds = $_POST['permissions'] ?? [];
        
        // Asignar permisos
        $assigned = $this->roleModel->assignPermissions($roleId, $permissionIds, $_SESSION['user_id']);
        
        if ($assigned) {
            // Registrar actividad
            $this->activityLog->log(
                $_SESSION['user_id'],
                'role_permissions_updated',
                "Permisos actualizados para rol: {$role->display_name} (ID: {$roleId})"
            );
            
            $_SESSION['success'] = 'Permisos actualizados exitosamente.';
        } else {
            $_SESSION['error'] = 'Error al actualizar los permisos.';
        }
        
        header('Location: ' . asset('roles'));
        exit();
    }
}
?>
