<?php
/**
 * Modelo Permission
 * 
 * Gestiona los permisos del sistema
 */

require_once app_path('core/Model.php');

class Permission extends Model {
    protected $table = 'permissions';

    /**
     * Obtener todos los permisos
     * 
     * @return array
     */
    public function getAllPermissions() {
        $sql = "SELECT * FROM {$this->table} ORDER BY category, display_name";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Obtener permisos agrupados por categoría
     * 
     * @return array
     */
    public function getPermissionsByCategory() {
        $permissions = $this->getAllPermissions();
        $grouped = [];
        
        foreach ($permissions as $permission) {
            $category = $permission->category ?? 'other';
            if (!isset($grouped[$category])) {
                $grouped[$category] = [];
            }
            $grouped[$category][] = $permission;
        }
        
        return $grouped;
    }

    /**
     * Obtener permisos de una categoría específica
     * 
     * @param string $category
     * @return array
     */
    public function getByCategory($category) {
        $sql = "SELECT * FROM {$this->table} WHERE category = :category ORDER BY display_name";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['category' => $category]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Obtener un permiso por nombre
     * 
     * @param string $name
     * @return object|null
     */
    public function getByName($name) {
        $sql = "SELECT * FROM {$this->table} WHERE name = :name LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['name' => $name]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Crear un nuevo permiso
     * 
     * @param array $data
     * @return int ID del permiso creado
     */
    public function createPermission($data) {
        return $this->create($data);
    }

    /**
     * Verificar si un usuario tiene un permiso específico
     * 
     * LÓGICA DE PERMISOS:
     * 1. Si el usuario tiene role='root', tiene TODOS los permisos automáticamente
     * 2. Si el usuario tiene role_id (rol personalizado), se buscan permisos en role_permissions
     * 3. Si el usuario tiene un role del sistema (user, personal, admin), se mapea a roles.name
     *    con is_system_role=1 y se buscan sus permisos en role_permissions
     * 
     * @param int $userId
     * @param string $permissionName
     * @return bool
     */
    public function userHasPermission($userId, $permissionName) {
        // Obtener información del usuario
        $stmt = $this->pdo->prepare("SELECT role, role_id FROM users WHERE id = :user_id LIMIT 1");
        $stmt->execute(['user_id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_OBJ);
        
        if (!$user) {
            return false;
        }
        
        // REGLA #1: Si es root, tiene todos los permisos (hardcoded, siempre true)
        if ($user->role === 'root') {
            return true;
        }
        
        // REGLA #2: Si tiene un rol personalizado (role_id), verificar permisos del rol
        if ($user->role_id) {
            $sql = "
                SELECT COUNT(*) as count
                FROM role_permissions rp
                INNER JOIN permissions p ON rp.permission_id = p.id
                WHERE rp.role_id = :role_id AND p.name = :permission_name
            ";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'role_id' => $user->role_id,
                'permission_name' => $permissionName
            ]);
            
            $result = $stmt->fetch(PDO::FETCH_OBJ);
            return $result && $result->count > 0;
        }
        
        // REGLA #3: Si tiene un rol del sistema (user, personal, admin), verificar permisos predefinidos
        if ($user->role) {
            // Obtener el ID del rol del sistema
            $stmt = $this->pdo->prepare("SELECT id FROM roles WHERE name = :role_name AND is_system_role = 1 LIMIT 1");
            $stmt->execute(['role_name' => $user->role]);
            $role = $stmt->fetch(PDO::FETCH_OBJ);
            
            if ($role) {
                $sql = "
                    SELECT COUNT(*) as count
                    FROM role_permissions rp
                    INNER JOIN permissions p ON rp.permission_id = p.id
                    WHERE rp.role_id = :role_id AND p.name = :permission_name
                ";
                
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([
                    'role_id' => $role->id,
                    'permission_name' => $permissionName
                ]);
                
                $result = $stmt->fetch(PDO::FETCH_OBJ);
                return $result && $result->count > 0;
            }
        }
        
        return false;
    }

    /**
     * Verificar si un usuario tiene ALGUNO de los permisos
     * 
     * @param int $userId
     * @param array $permissionNames
     * @return bool
     */
    public function userHasAnyPermission($userId, $permissionNames) {
        foreach ($permissionNames as $permissionName) {
            if ($this->userHasPermission($userId, $permissionName)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Verificar si un usuario tiene TODOS los permisos
     * 
     * @param int $userId
     * @param array $permissionNames
     * @return bool
     */
    public function userHasAllPermissions($userId, $permissionNames) {
        foreach ($permissionNames as $permissionName) {
            if (!$this->userHasPermission($userId, $permissionName)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Obtener todos los permisos de un usuario
     * 
     * @param int $userId
     * @return array
     */
    public function getUserPermissions($userId) {
        // Obtener información del usuario
        $stmt = $this->pdo->prepare("SELECT role, role_id FROM users WHERE id = :user_id LIMIT 1");
        $stmt->execute(['user_id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_OBJ);
        
        if (!$user) {
            return [];
        }
        
        // Si es root, devolver todos los permisos
        if ($user->role === 'root') {
            return $this->getAllPermissions();
        }
        
        $roleId = null;
        
        // Determinar el rol a usar
        if ($user->role_id) {
            $roleId = $user->role_id;
        } elseif ($user->role) {
            // Obtener el ID del rol del sistema
            $stmt = $this->pdo->prepare("SELECT id FROM roles WHERE name = :role_name AND is_system_role = 1 LIMIT 1");
            $stmt->execute(['role_name' => $user->role]);
            $role = $stmt->fetch(PDO::FETCH_OBJ);
            if ($role) {
                $roleId = $role->id;
            }
        }
        
        if (!$roleId) {
            return [];
        }
        
        // Obtener permisos del rol
        $sql = "
            SELECT p.*
            FROM permissions p
            INNER JOIN role_permissions rp ON p.id = rp.permission_id
            WHERE rp.role_id = :role_id
            ORDER BY p.category, p.display_name
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['role_id' => $roleId]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Obtener categorías únicas de permisos
     * 
     * @return array
     */
    public function getCategories() {
        $sql = "SELECT DISTINCT category FROM {$this->table} ORDER BY category";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        
        $categories = [];
        while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
            $categories[] = $row->category;
        }
        
        return $categories;
    }
}
?>
