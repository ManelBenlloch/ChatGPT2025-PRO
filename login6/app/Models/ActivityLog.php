<?php
/**
 * ActivityLog.php
 * 
 * Modelo de Registro de Actividad
 * 
 * Registra todas las acciones importantes de los usuarios
 * para auditoría y seguridad.
 */

require_once app_path('core/Model.php');

class ActivityLog extends Model {
    protected $table = 'activity_logs';

    /**
     * Registrar una actividad
     * 
     * @param int|null $userId ID del usuario (null para acciones anónimas)
     * @param string $action Tipo de acción
     * @param string|null $description Descripción detallada
     * @param array|null $metadata Datos adicionales
     * @return int ID del log creado
     */
    public function log($userId, $action, $description = null, $metadata = null) {
        $data = [
            'user_id' => $userId,
            'action' => $action,
            'description' => $description,
            'ip_address' => $this->getClientIp(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'metadata' => $metadata ? json_encode($metadata) : null,
            'created_at' => date('Y-m-d H:i:s')
        ];

        return $this->create($data);
    }

    /**
     * Registrar login exitoso
     * 
     * @param int $userId
     */
    public function logLogin($userId) {
        $this->log($userId, 'user_login', 'Usuario inició sesión correctamente');
    }

    /**
     * Registrar intento de login fallido
     * 
     * @param string $email
     */
    public function logFailedLogin($email) {
        $this->log(null, 'failed_login', "Intento de login fallido con email: {$email}", [
            'email' => $email
        ]);
    }

    /**
     * Registrar logout
     * 
     * @param int $userId
     */
    public function logLogout($userId) {
        $this->log($userId, 'user_logout', 'Usuario cerró sesión');
    }

    /**
     * Registrar registro de nuevo usuario
     * 
     * @param int $userId
     * @param string $email
     */
    public function logRegistration($userId, $email) {
        $this->log($userId, 'user_registration', "Nuevo usuario registrado: {$email}", [
            'email' => $email
        ]);
    }

    /**
     * Registrar verificación de email
     * 
     * @param int $userId
     */
    public function logEmailVerification($userId) {
        $this->log($userId, 'email_verified', 'Usuario verificó su email');
    }

    /**
     * Registrar solicitud de reseteo de contraseña
     * 
     * @param int $userId
     * @param string $email
     */
    public function logPasswordResetRequest($userId, $email) {
        $this->log($userId, 'password_reset_request', "Solicitud de reseteo de contraseña para: {$email}", [
            'email' => $email
        ]);
    }

    /**
     * Registrar reseteo de contraseña exitoso
     * 
     * @param int $userId
     */
    public function logPasswordReset($userId) {
        $this->log($userId, 'password_reset', 'Usuario reseteó su contraseña correctamente');
    }

    /**
     * Registrar cambio de contraseña
     * 
     * @param int $userId
     */
    public function logPasswordChange($userId) {
        $this->log($userId, 'password_change', 'Usuario cambió su contraseña');
    }

    /**
     * Registrar actualización de perfil
     * 
     * @param int $userId
     * @param array $changes Campos modificados
     */
    public function logProfileUpdate($userId, $changes = []) {
        $this->log($userId, 'profile_update', 'Usuario actualizó su perfil', $changes);
    }

    /**
     * Registrar activación de 2FA
     * 
     * @param int $userId
     */
    public function log2FAEnabled($userId) {
        $this->log($userId, '2fa_enabled', 'Usuario activó la autenticación de dos factores');
    }

    /**
     * Registrar desactivación de 2FA
     * 
     * @param int $userId
     */
    public function log2FADisabled($userId) {
        $this->log($userId, '2fa_disabled', 'Usuario desactivó la autenticación de dos factores');
    }

    /**
     * Obtener los últimos logs de un usuario
     * 
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function getUserLogs($userId, $limit = 50) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM {$this->table} 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }

    /**
     * Obtener los logs más recientes del sistema
     * 
     * @param int $limit Número de logs a obtener
     * @param int $offset Offset para paginación
     * @return array
     */
    public function getRecentLogs($limit = 50, $offset = 0) {
        $stmt = $this->pdo->prepare("
            SELECT al.*, u.fullname, u.email 
            FROM {$this->table} al
            LEFT JOIN users u ON al.user_id = u.id
            ORDER BY al.created_at DESC 
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Obtener el total de logs en el sistema
     * 
     * @return int
     */
    public function getTotalLogs() {
        $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM {$this->table}");
        $result = $stmt->fetch(PDO::FETCH_OBJ);
        return $result->total ?? 0;
    }

    /**
     * Obtener la IP del cliente
     * 
     * @return string
     */
    private function getClientIp() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        }
    }
}

?>
