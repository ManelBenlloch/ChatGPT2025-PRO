<?php
/**
 * Modelo Role
 * 
 * Gestiona los roles del sistema y roles personalizados
 */

require_once app_path('core/Model.php');

class Role extends Model {
    protected $table = 'roles';

    /**
     * Obtener todos los roles
     * 
     * @param bool $includeSystem Incluir roles del sistema
     * @return array
     */
    public function getAllRoles($includeSystem = false) {
        if ($includeSystem) {
            $sql = "SELECT * FROM {$this->table} WHERE is_active = 1 ORDER BY is_system_role DESC, name ASC";
        } else {
            $sql = "SELECT * FROM {$this->table} WHERE is_active = 1 AND is_system_role = 0 ORDER BY name ASC";
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Obtener un rol por ID
     * 
     * @param int $roleId
     * @return object|null
     */
    public function getRoleById($roleId) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $roleId]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Obtener un rol por nombre
     * 
     * @param string $name
     * @return object|null
     */
    public function getRoleByName($name) {
        $sql = "SELECT * FROM {$this->table} WHERE name = :name LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['name' => $name]);
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Crear un nuevo rol personalizado
     * 
     * @param array $data
     * @return int ID del rol creado
     */
    public function createRole($data) {
        // Asegurar que no sea un rol del sistema
        $data['is_system_role'] = 0;
        $data['is_active'] = 1;
        
        return $this->create($data);
    }

    /**
     * Actualizar un rol
     * 
     * @param int $roleId
     * @param array $data
     * @return bool
     */
    public function updateRole($roleId, $data) {
        // System roles cannot be modified
        if ($this->isSystemRole($roleId)) {
            return false;
        }
        
        // Do not allow changing is_system_role or name for any role
        unset($data['is_system_role']);
        unset($data['name']);
        
        return $this->update($roleId, $data);
    }

    /**
     * Eliminar un rol personalizado
     * 
     * @param int $roleId
     * @return bool
     */
    public function deleteRole($roleId) {
        // Verificar que no sea un rol del sistema
        if ($this->isSystemRole($roleId)) {
            return false;
        }
        
        // Verificar que no haya usuarios con este rol
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM users WHERE role_id = ?");
        $stmt->execute([$roleId]);
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        
        if ($result->count > 0) {
            return false; // No se puede eliminar si hay usuarios asignados
        }
        
        return $this->delete($roleId);
    }

    /**
     * Verificar si un rol es del sistema
     * 
     * @param int $roleId
     * @return bool
     */
    public function isSystemRole($roleId) {
        $sql = "SELECT is_system_role FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $roleId]);
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        
        return $result ? (bool)$result->is_system_role : false;
    }

    /**
     * Obtener permisos de un rol
     * 
     * @param int $roleId
     * @return array
     */
    public function getPermissions($roleId) {
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
     * Obtener IDs de permisos de un rol
     * 
     * @param int $roleId
     * @return array
     */
    public function getPermissionIds($roleId) {
        $sql = "SELECT permission_id FROM role_permissions WHERE role_id = :role_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['role_id' => $roleId]);
        
        $ids = [];
        while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
            $ids[] = $row->permission_id;
        }
        
        return $ids;
    }

    /**
     * Asignar permisos a un rol
     * 
     * @param int $roleId
     * @param array $permissionIds
     * @param int $grantedBy ID del usuario que otorga los permisos (opcional)
     * @return bool
     */
    public function assignPermissions($roleId, $permissionIds, $grantedBy = null) {
        // System roles cannot have permissions modified (except via direct DB for initial setup)
        if ($this->isSystemRole($roleId)) {
            return false;
        }
        
        try {
            // Iniciar transacción
            $this->pdo->beginTransaction();
            
            // Eliminar permisos actuales
            $stmt = $this->pdo->prepare("DELETE FROM role_permissions WHERE role_id = ?");
            $stmt->execute([$roleId]);
            
            // Insertar nuevos permisos
            if (!empty($permissionIds)) {
                // Use different query based on whether granted_by is provided
                if ($grantedBy !== null) {
                    $stmt = $this->pdo->prepare("
                        INSERT INTO role_permissions (role_id, permission_id, granted_by) 
                        VALUES (?, ?, ?)
                    ");
                    
                    foreach ($permissionIds as $permissionId) {
                        $stmt->execute([$roleId, $permissionId, $grantedBy]);
                    }
                } else {
                    $stmt = $this->pdo->prepare("
                        INSERT INTO role_permissions (role_id, permission_id) 
                        VALUES (?, ?)
                    ");
                    
                    foreach ($permissionIds as $permissionId) {
                        $stmt->execute([$roleId, $permissionId]);
                    }
                }
            }
            
            // Confirmar transacción
            $this->pdo->commit();
            return true;
            
        } catch (Exception $e) {
            // Revertir transacción en caso de error
            $this->pdo->rollBack();
            return false;
        }
    }

    /**
     * Obtener cantidad de usuarios con un rol específico
     * 
     * @param int $roleId
     * @return int
     */
    public function getUserCount($roleId) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM users WHERE role_id = ?");
        $stmt->execute([$roleId]);
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        
        return $result ? (int)$result->count : 0;
    }

    /**
     * Verificar si un rol tiene un permiso específico
     * 
     * @param int $roleId
     * @param string $permissionName
     * @return bool
     */
    public function hasPermission($roleId, $permissionName) {
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
}
?>
