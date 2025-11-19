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
     * Checks permissions in this order:
     * 1. Root users always have all permissions
     * 2. User-specific permission overrides (user_permissions table)
     * 3. Role-based permissions (via role_id or system role)
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
        
        // Si es root, tiene todos los permisos (hardcoded, always true)
        if ($user->role === 'root') {
            return true;
        }
        
        // Check for user-specific permission overrides (ABAC layer)
        $stmt = $this->pdo->prepare("
            SELECT up.is_granted
            FROM user_permissions up
            INNER JOIN permissions p ON up.permission_id = p.id
            WHERE up.user_id = :user_id AND p.name = :permission_name
            LIMIT 1
        ");
        $stmt->execute([
            'user_id' => $userId,
            'permission_name' => $permissionName
        ]);
        $override = $stmt->fetch(PDO::FETCH_OBJ);
        
        // If there's an explicit override, use it (1 = granted, 0 = revoked)
        if ($override !== false) {
            return (bool)$override->is_granted;
        }
        
        // Determine the role ID to check
        $roleId = null;
        
        // Si tiene un rol personalizado (role_id), usar ese
        if ($user->role_id) {
            $roleId = $user->role_id;
        } 
        // Si tiene un rol del sistema (user, personal, admin), obtener su ID
        elseif ($user->role) {
            $stmt = $this->pdo->prepare("SELECT id FROM roles WHERE name = :role_name AND is_system_role = 1 LIMIT 1");
            $stmt->execute(['role_name' => $user->role]);
            $role = $stmt->fetch(PDO::FETCH_OBJ);
            if ($role) {
                $roleId = $role->id;
            }
        }
        
        // If we have a role ID, check role permissions
        if ($roleId) {
            $sql = "
                SELECT COUNT(*) as count
                FROM role_permissions rp
                INNER JOIN permissions p ON rp.permission_id = p.id
                WHERE rp.role_id = :role_id AND p.name = :permission_name
            ";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'role_id' => $roleId,
                'permission_name' => $permissionName
            ]);
            
            $result = $stmt->fetch(PDO::FETCH_OBJ);
            return $result && $result->count > 0;
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
     * Includes both role-based permissions and user-specific overrides
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
        
        $permissions = [];
        $permissionIds = [];
        
        // Get role-based permissions first
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
        
        if ($roleId) {
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
            $permissions = $stmt->fetchAll(PDO::FETCH_OBJ);
            
            // Track permission IDs
            foreach ($permissions as $perm) {
                $permissionIds[$perm->id] = true;
            }
        }
        
        // Apply user-specific permission overrides (ABAC layer)
        $stmt = $this->pdo->prepare("
            SELECT p.*, up.is_granted
            FROM user_permissions up
            INNER JOIN permissions p ON up.permission_id = p.id
            WHERE up.user_id = :user_id
        ");
        $stmt->execute(['user_id' => $userId]);
        $userOverrides = $stmt->fetchAll(PDO::FETCH_OBJ);
        
        foreach ($userOverrides as $override) {
            if ($override->is_granted) {
                // Grant permission (add if not already present)
                if (!isset($permissionIds[$override->id])) {
                    $permissions[] = $override;
                    $permissionIds[$override->id] = true;
                }
            } else {
                // Revoke permission (remove if present)
                if (isset($permissionIds[$override->id])) {
                    $permissions = array_filter($permissions, function($p) use ($override) {
                        return $p->id != $override->id;
                    });
                    unset($permissionIds[$override->id]);
                }
            }
        }
        
        return array_values($permissions);
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
