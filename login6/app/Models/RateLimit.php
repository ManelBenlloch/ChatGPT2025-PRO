<?php
/**
 * RateLimit.php
 * 
 * Modelo de Rate Limiting
 * 
 * Gestiona el control de intentos de login y otras acciones
 * para prevenir ataques de fuerza bruta.
 */

require_once app_path('core/Model.php');

class RateLimit extends Model {
    protected $table = 'rate_limits';

    /**
     * Verificar si una IP está bloqueada
     * 
     * @param string $ipAddress
     * @param string $action
     * @return bool
     */
    public function isBlocked($ipAddress, $action = 'login_attempt') {
        $stmt = $this->pdo->prepare("
            SELECT * FROM {$this->table} 
            WHERE ip_address = ? 
            AND action = ? 
            AND locked_until IS NOT NULL 
            AND locked_until > NOW()
        ");
        $stmt->execute([$ipAddress, $action]);
        return $stmt->fetch() !== false;
    }

    /**
     * Registrar un intento
     * 
     * @param string $ipAddress
     * @param string $action
     * @return int Número de intentos actuales
     */
    public function recordAttempt($ipAddress, $action = 'login_attempt') {
        // Buscar registro existente
        $stmt = $this->pdo->prepare("
            SELECT * FROM {$this->table} 
            WHERE ip_address = ? AND action = ?
        ");
        $stmt->execute([$ipAddress, $action]);
        $record = $stmt->fetch();

        if ($record) {
            // Si el bloqueo expiró, resetear intentos
            if ($record->locked_until && strtotime($record->locked_until) < time()) {
                $this->resetAttempts($ipAddress, $action);
                return 1;
            }

            // Incrementar intentos
            $newAttempts = $record->attempts + 1;
            $stmt = $this->pdo->prepare("
                UPDATE {$this->table} 
                SET attempts = ?, last_attempt_at = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$newAttempts, $record->id]);

            // Si se superó el límite, bloquear
            if ($newAttempts >= MAX_LOGIN_ATTEMPTS) {
                $this->lockIp($ipAddress, $action);
            }

            return $newAttempts;
        } else {
            // Crear nuevo registro
            $this->create([
                'ip_address' => $ipAddress,
                'action' => $action,
                'attempts' => 1,
                'last_attempt_at' => date('Y-m-d H:i:s')
            ]);
            return 1;
        }
    }

    /**
     * Bloquear una IP
     * 
     * @param string $ipAddress
     * @param string $action
     */
    private function lockIp($ipAddress, $action) {
        $lockedUntil = date('Y-m-d H:i:s', time() + LOCKOUT_TIME);
        $stmt = $this->pdo->prepare("
            UPDATE {$this->table} 
            SET locked_until = ? 
            WHERE ip_address = ? AND action = ?
        ");
        $stmt->execute([$lockedUntil, $ipAddress, $action]);
    }

    /**
     * Resetear intentos
     * 
     * @param string $ipAddress
     * @param string $action
     */
    public function resetAttempts($ipAddress, $action = 'login_attempt') {
        $stmt = $this->pdo->prepare("
            UPDATE {$this->table} 
            SET attempts = 0, locked_until = NULL 
            WHERE ip_address = ? AND action = ?
        ");
        $stmt->execute([$ipAddress, $action]);
    }

    /**
     * Obtener el número de intentos restantes
     * 
     * @param string $ipAddress
     * @param string $action
     * @return int
     */
    public function getRemainingAttempts($ipAddress, $action = 'login_attempt') {
        $stmt = $this->pdo->prepare("
            SELECT attempts FROM {$this->table} 
            WHERE ip_address = ? AND action = ?
        ");
        $stmt->execute([$ipAddress, $action]);
        $record = $stmt->fetch();

        if ($record) {
            return max(0, MAX_LOGIN_ATTEMPTS - $record->attempts);
        }

        return MAX_LOGIN_ATTEMPTS;
    }

    /**
     * Obtener el tiempo restante de bloqueo en segundos
     * 
     * @param string $ipAddress
     * @param string $action
     * @return int
     */
    public function getLockoutTimeRemaining($ipAddress, $action = 'login_attempt') {
        $stmt = $this->pdo->prepare("
            SELECT locked_until FROM {$this->table} 
            WHERE ip_address = ? AND action = ? AND locked_until > NOW()
        ");
        $stmt->execute([$ipAddress, $action]);
        $record = $stmt->fetch();

        if ($record) {
            return max(0, strtotime($record->locked_until) - time());
        }

        return 0;
    }

    /**
     * Obtener todas las IPs bloqueadas actualmente
     * 
     * @return array
     */
    public function getBlockedIps() {
        $stmt = $this->pdo->query("
            SELECT * FROM {$this->table} 
            WHERE locked_until IS NOT NULL 
            AND locked_until > NOW()
            ORDER BY locked_until DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Desbloquear una IP manualmente
     * 
     * @param string $ipAddress
     */
    public function unlockIp($ipAddress) {
        $stmt = $this->pdo->prepare("
            UPDATE {$this->table} 
            SET locked_until = NULL, attempts = 0 
            WHERE ip_address = ?
        ");
        $stmt->execute([$ipAddress]);
    }
}

?>
