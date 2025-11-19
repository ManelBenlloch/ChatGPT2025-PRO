<?php
/**
 * MFAFactor.php
 * 
 * Modelo de Factor de Autenticación de Dos Factores (2FA)
 * 
 * Gestiona los factores de autenticación adicionales para los usuarios,
 * incluyendo TOTP (Google Authenticator), SMS y Email.
 */

require_once app_path('core/Model.php');

class MFAFactor extends Model {
    protected $table = 'mfa_factors';

    /**
     * Obtener todos los factores de un usuario
     * 
     * @param int $userId
     * @return array
     */
    public function getUserFactors($userId) {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Obtener un factor específico de un usuario
     * 
     * @param int $userId
     * @param string $factorType (totp, sms, email)
     * @return object|null
     */
    public function getUserFactor($userId, $factorType) {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE user_id = ? AND factor_type = ?");
        $stmt->execute([$userId, $factorType]);
        return $stmt->fetch();
    }

    /**
     * Crear un nuevo factor TOTP para un usuario
     * 
     * @param int $userId
     * @param string $secret
     * @return int ID del factor creado
     */
    public function createTOTPFactor($userId, $secret) {
        $data = [
            'user_id' => $userId,
            'factor_type' => 'totp',
            'secret' => $secret,
            'is_verified' => 0
        ];
        return $this->create($data);
    }

    /**
     * Crear un nuevo factor SMS para un usuario
     * 
     * @param int $userId
     * @param string $phoneNumber
     * @return int ID del factor creado
     */
    public function createSMSFactor($userId, $phoneNumber) {
        $data = [
            'user_id' => $userId,
            'factor_type' => 'sms',
            'phone_number' => $phoneNumber,
            'is_verified' => 0
        ];
        return $this->create($data);
    }

    /**
     * Crear un nuevo factor Email para un usuario
     * 
     * @param int $userId
     * @return int ID del factor creado
     */
    public function createEmailFactor($userId) {
        $data = [
            'user_id' => $userId,
            'factor_type' => 'email',
            'is_verified' => 0
        ];
        return $this->create($data);
    }

    /**
     * Verificar un factor
     * 
     * @param int $factorId
     * @return bool
     */
    public function verifyFactor($factorId) {
        return $this->update($factorId, ['is_verified' => 1]);
    }

    /**
     * Eliminar un factor
     * 
     * @param int $factorId
     * @return bool
     */
    public function removeFactor($factorId) {
        return $this->delete($factorId);
    }

    /**
     * Verificar si un usuario tiene 2FA habilitado
     * 
     * @param int $userId
     * @return bool
     */
    public function has2FAEnabled($userId) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM {$this->table} WHERE user_id = ? AND is_verified = 1");
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        return $result->count > 0;
    }

    /**
     * Obtener el factor verificado de un usuario
     * 
     * @param int $userId
     * @return object|null
     */
    public function getVerifiedFactor($userId) {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE user_id = ? AND is_verified = 1 LIMIT 1");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }
}

?>
