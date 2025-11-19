<?php
/**
 * User.php
 * 
 * Modelo de Usuario
 * 
 * Gestiona todas las operaciones relacionadas con los usuarios.
 */

require_once app_path('core/Model.php');

class User extends Model {
    protected $table = 'users';

    /**
     * Obtener la instancia de PDO
     * 
     * @return PDO
     */
    public function getPDO() {
        return $this->pdo;
    }

    /**
     * Buscar un usuario por email
     * 
     * @param string $email
     * @return object|null
     */
    public function findByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE email = ? AND deleted_at IS NULL");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    /**
     * Buscar un usuario por username
     * 
     * @param string $username
     * @return object|null
     */
    public function findByUsername($username) {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE username = ? AND deleted_at IS NULL");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }

    /**
     * Buscar un usuario por alias
     * 
     * @param string $alias
     * @return object|null
     */
    public function findByAlias($alias) {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE alias = ? AND deleted_at IS NULL");
        $stmt->execute([$alias]);
        return $stmt->fetch();
    }

    /**
     * Verificar si un email ya existe
     * 
     * @param string $email
     * @return bool
     */
    public function emailExists($email) {
        return $this->findByEmail($email) !== false;
    }

    /**
     * Verificar si un username ya existe
     * 
     * @param string $username
     * @return bool
     */
    public function usernameExists($username) {
        return $this->findByUsername($username) !== false;
    }

    /**
     * Verificar si un alias ya existe
     * 
     * @param string $alias
     * @return bool
     */
    public function aliasExists($alias) {
        return $this->findByAlias($alias) !== false;
    }

    /**
     * Crear un nuevo usuario
     * 
     * @param array $data Los datos del usuario
     * @return int El ID del usuario creado
     */
    public function createUser($data) {
        // Hash de la contraseña
        if (isset($data['password'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT);
            unset($data['password']);
        }

        // Generar alias de sistema automáticamente si está vacío
        if (empty($data['alias'])) {
            $data['alias'] = $this->generateSystemAlias($data['username'], $data['email']);
        }

        // Generar token de verificación
        $data['verification_token'] = bin2hex(random_bytes(32));
        $data['email_verified'] = 0;
        $data['created_at'] = date('Y-m-d H:i:s');

        return $this->create($data);
    }

    /**
     * Generar alias de sistema automáticamente
     * 
     * Formatos posibles (aleatorios):
     * 1. manel-benlloch-12-11-2025-gmail
     * 2. manel_benlloch_12_11_2025_gmail
     * 3. manel_benlloch_12112025_gmail
     * 4. manelbenlloch_12112025_gmail
     * 
     * @param string $username Alias público (puede contener @)
     * @param string $email Email del usuario
     * @return string Alias de sistema generado (SIN @)
     */
    private function generateSystemAlias($username, $email) {
        // Extraer el nombre del alias (quitar @ si existe)
        $aliasName = str_replace('@', '', $username);
        $aliasName = strtolower($aliasName);
        
        // Extraer dominio del email
        $emailParts = explode('@', $email);
        $domain = isset($emailParts[1]) ? explode('.', $emailParts[1])[0] : 'user';
        
        // Obtener fecha actual
        $day = date('d');
        $month = date('m');
        $year = date('Y');
        
        // Elegir formato aleatorio (1-4)
        $format = rand(1, 4);
        
        switch ($format) {
            case 1:
                // Formato 1: TODO con guiones
                // Ejemplo: manel-benlloch-12-11-2025-gmail
                $systemAlias = $aliasName . '-' . $day . '-' . $month . '-' . $year . '-' . $domain;
                break;
            
            case 2:
                // Formato 2: TODO con guiones bajos
                // Ejemplo: manel_benlloch_12_11_2025_gmail
                $aliasNameFormatted = str_replace('-', '_', $aliasName);
                $systemAlias = $aliasNameFormatted . '_' . $day . '_' . $month . '_' . $year . '_' . $domain;
                break;
            
            case 3:
                // Formato 3: Guiones bajos con fecha compacta
                // Ejemplo: manel_benlloch_12112025_gmail
                $aliasNameFormatted = str_replace('-', '_', $aliasName);
                $systemAlias = $aliasNameFormatted . '_' . $day . $month . $year . '_' . $domain;
                break;
            
            case 4:
                // Formato 4: Sin separadores en el nombre, guión bajo con fecha compacta
                // Ejemplo: manelbenlloch_12112025_gmail
                $aliasNameFormatted = str_replace(['-', '_'], '', $aliasName);
                $systemAlias = $aliasNameFormatted . '_' . $day . $month . $year . '_' . $domain;
                break;
        }
        
        // Verificar que el alias generado sea único, si no, agregar un número aleatorio
        $originalAlias = $systemAlias;
        $counter = 1;
        while ($this->aliasExists($systemAlias)) {
            $systemAlias = $originalAlias . '_' . $counter;
            $counter++;
        }
        
        return $systemAlias;
    }

    /**
     * Verificar la contraseña de un usuario
     * 
     * @param object $user El objeto del usuario
     * @param string $password La contraseña a verificar
     * @return bool
     */
    public function verifyPassword($user, $password) {
        return password_verify($password, $user->password_hash);
    }

    /**
     * Actualizar el último login del usuario
     * 
     * @param int $userId
     */
    public function updateLastLogin($userId) {
        $this->update($userId, ['last_login' => date('Y-m-d H:i:s')]);
    }

    /**
     * Verificar el email de un usuario
     * 
     * @param string $token El token de verificación
     * @return bool
     */
    public function verifyEmail($token) {
        $stmt = $this->pdo->prepare("UPDATE {$this->table} SET email_verified = 1, verification_token = NULL WHERE verification_token = ?");
        return $stmt->execute([$token]);
    }

    /**
     * Generar un token de reseteo de contraseña
     * 
     * @param string $email
     * @return string|null El token generado o null si el email no existe
     */
    public function generateResetToken($email) {
        $user = $this->findByEmail($email);
        if (!$user) {
            return null;
        }

        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $this->update($user->id, [
            'reset_token' => $token,
            'reset_token_expires_at' => $expiresAt
        ]);

        return $token;
    }

    /**
     * Resetear la contraseña de un usuario
     * 
     * @param string $token El token de reseteo
     * @param string $newPassword La nueva contraseña
     * @return bool
     */
    public function resetPassword($token, $newPassword) {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE reset_token = ? AND reset_token_expires_at > NOW()");
        $stmt->execute([$token]);
        $user = $stmt->fetch();

        if (!$user) {
            return false;
        }

        $passwordHash = password_hash($newPassword, PASSWORD_BCRYPT);
        $this->update($user->id, [
            'password_hash' => $passwordHash,
            'reset_token' => null,
            'reset_token_expires_at' => null
        ]);

        return true;
    }

    /**
     * Soft delete de un usuario
     * 
     * @param int $userId
     * @return bool
     */
    public function softDelete($userId) {
        return $this->update($userId, ['deleted_at' => date('Y-m-d H:i:s')]);
    }
}

?>
