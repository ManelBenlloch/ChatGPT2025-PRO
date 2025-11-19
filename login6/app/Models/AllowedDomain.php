<?php
/**
 * Modelo AllowedDomain
 * 
 * Gestiona los dominios de email permitidos para registro
 */

require_once app_path('core/Model.php');

class AllowedDomain extends Model {
    protected $table = 'allowed_domains';

    /**
     * Verificar si un dominio está permitido
     * 
     * @param string $domain El dominio a verificar (ej. gmail.com)
     * @return bool
     */
    public function isAllowed($domain) {
        $sql = "SELECT * FROM {$this->table} WHERE domain = :domain AND is_active = 1 LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['domain' => $domain]);
        return $stmt->fetch(PDO::FETCH_OBJ) !== false;
    }

    /**
     * Obtener todos los dominios permitidos activos
     * 
     * @return array
     */
    public function getAllActive() {
        $sql = "SELECT * FROM {$this->table} WHERE is_active = 1 ORDER BY domain ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Añadir un nuevo dominio permitido
     * 
     * @param string $domain
     * @return int El ID del dominio creado
     */
    public function addDomain($domain) {
        return $this->create([
            'domain' => $domain,
            'is_active' => 1
        ]);
    }

    /**
     * Desactivar un dominio
     * 
     * @param int $id
     * @return bool
     */
    public function deactivate($id) {
        return $this->update($id, ['is_active' => 0]);
    }

    /**
     * Activar un dominio
     * 
     * @param int $id
     * @return bool
     */
    public function activate($id) {
        return $this->update($id, ['is_active' => 1]);
    }
}
