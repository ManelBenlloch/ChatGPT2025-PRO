<?php
/**
 * UserSession.php
 * 
 * Modelo de Sesión de Usuario
 * 
 * Gestiona las sesiones activas de los usuarios, permitiendo
 * múltiples sesiones simultáneas y control granular.
 */

require_once app_path('core/Model.php');

class UserSession extends Model {
    protected $table = 'user_sessions';

    /**
     * Crear una nueva sesión
     * 
     * @param int $userId
     * @param string $sessionToken
     * @param int $expiresInSeconds (por defecto 2 horas)
     * @return int ID de la sesión creada
     */
    public function createSession($userId, $sessionToken, $expiresInSeconds = 7200) {
        $expiresAt = date('Y-m-d H:i:s', time() + $expiresInSeconds);
        
        $data = [
            'user_id' => $userId,
            'session_token' => $sessionToken,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'expires_at' => $expiresAt,
            'is_active' => 1
        ];
        
        return $this->create($data);
    }

    /**
     * Obtener una sesión por token
     * 
     * @param string $sessionToken
     * @return object|null
     */
    public function getSessionByToken($sessionToken) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM {$this->table} 
            WHERE session_token = ? 
            AND is_active = 1 
            AND expires_at > NOW()
        ");
        $stmt->execute([$sessionToken]);
        return $stmt->fetch();
    }

    /**
     * Obtener todas las sesiones activas de un usuario
     * 
     * @param int $userId
     * @return array
     */
    public function getUserActiveSessions($userId) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM {$this->table} 
            WHERE user_id = ? 
            AND is_active = 1 
            AND expires_at > NOW()
            ORDER BY created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Renovar una sesión (extender su tiempo de expiración)
     * 
     * @param int $sessionId
     * @param int $expiresInSeconds (por defecto 2 horas)
     * @return bool
     */
    public function renewSession($sessionId, $expiresInSeconds = 7200) {
        $expiresAt = date('Y-m-d H:i:s', time() + $expiresInSeconds);
        return $this->update($sessionId, ['expires_at' => $expiresAt]);
    }

    /**
     * Desactivar una sesión específica
     * 
     * @param int $sessionId
     * @return bool
     */
    public function deactivateSession($sessionId) {
        return $this->update($sessionId, ['is_active' => 0]);
    }

    /**
     * Desactivar todas las sesiones de un usuario excepto la actual
     * 
     * @param int $userId
     * @param string $currentSessionToken
     * @return bool
     */
    public function deactivateOtherSessions($userId, $currentSessionToken) {
        $stmt = $this->pdo->prepare("
            UPDATE {$this->table} 
            SET is_active = 0 
            WHERE user_id = ? 
            AND session_token != ? 
            AND is_active = 1
        ");
        return $stmt->execute([$userId, $currentSessionToken]);
    }

    /**
     * Desactivar todas las sesiones de un usuario
     * 
     * @param int $userId
     * @return bool
     */
    public function deactivateAllSessions($userId) {
        $stmt = $this->pdo->prepare("
            UPDATE {$this->table} 
            SET is_active = 0 
            WHERE user_id = ? 
            AND is_active = 1
        ");
        return $stmt->execute([$userId]);
    }

    /**
     * Limpiar sesiones expiradas
     * 
     * @return int Número de sesiones limpiadas
     */
    public function cleanExpiredSessions() {
        $stmt = $this->pdo->prepare("
            UPDATE {$this->table} 
            SET is_active = 0 
            WHERE expires_at < NOW() 
            AND is_active = 1
        ");
        $stmt->execute();
        return $stmt->rowCount();
    }

    /**
     * Obtener información del dispositivo de una sesión
     * 
     * @param object $session
     * @return array
     */
    public function getDeviceInfo($session) {
        $userAgent = $session->user_agent ?? '';
        
        // Detectar sistema operativo
        $os = 'Desconocido';
        if (preg_match('/windows/i', $userAgent)) $os = 'Windows';
        elseif (preg_match('/macintosh|mac os x/i', $userAgent)) $os = 'macOS';
        elseif (preg_match('/linux/i', $userAgent)) $os = 'Linux';
        elseif (preg_match('/android/i', $userAgent)) $os = 'Android';
        elseif (preg_match('/iphone|ipad|ipod/i', $userAgent)) $os = 'iOS';
        
        // Detectar navegador
        $browser = 'Desconocido';
        if (preg_match('/chrome/i', $userAgent) && !preg_match('/edge/i', $userAgent)) $browser = 'Chrome';
        elseif (preg_match('/firefox/i', $userAgent)) $browser = 'Firefox';
        elseif (preg_match('/safari/i', $userAgent) && !preg_match('/chrome/i', $userAgent)) $browser = 'Safari';
        elseif (preg_match('/edge/i', $userAgent)) $browser = 'Edge';
        elseif (preg_match('/opera|opr/i', $userAgent)) $browser = 'Opera';
        
        // Detectar tipo de dispositivo
        $deviceType = 'Escritorio';
        if (preg_match('/mobile/i', $userAgent)) $deviceType = 'Móvil';
        elseif (preg_match('/tablet|ipad/i', $userAgent)) $deviceType = 'Tablet';
        
        return [
            'os' => $os,
            'browser' => $browser,
            'device_type' => $deviceType,
            'ip' => $session->ip_address ?? 'Desconocida'
        ];
    }

    /**
     * Verificar si una sesión es la actual
     * 
     * @param object $session
     * @return bool
     */
    public function isCurrentSession($session) {
        return isset($_SESSION['session_token']) && $_SESSION['session_token'] === $session->session_token;
    }
}

?>
