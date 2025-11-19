<?php
/**
 * Model.php
 * 
 * Clase Base para Todos los Modelos
 * 
 * Proporciona métodos comunes para interactuar con la base de datos.
 */

class Model {
    protected $pdo;
    protected $table;

    public function __construct() {
        $this->pdo = $GLOBALS['pdo'];
    }

    /**
     * Obtener todos los registros de una tabla
     * 
     * @return array
     */
    public function all() {
        $stmt = $this->pdo->query("SELECT * FROM {$this->table}");
        return $stmt->fetchAll();
    }

    /**
     * Buscar un registro por ID
     * 
     * @param int $id
     * @return object|null
     */
    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Crear un nuevo registro
     * 
     * @param array $data Los datos a insertar
     * @return int El ID del registro insertado
     */
    public function create($data) {
        $keys = array_keys($data);
        $fields = implode(', ', $keys);
        $placeholders = ':' . implode(', :', $keys);

        $sql = "INSERT INTO {$this->table} ({$fields}) VALUES ({$placeholders})";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);

        return $this->pdo->lastInsertId();
    }

    /**
     * Actualizar un registro
     * 
     * @param int $id El ID del registro a actualizar
     * @param array $data Los datos a actualizar
     * @return bool
     */
    public function update($id, $data) {
        $fields = [];
        foreach ($data as $key => $value) {
            $fields[] = "{$key} = :{$key}";
        }
        $fields = implode(', ', $fields);

        $sql = "UPDATE {$this->table} SET {$fields} WHERE id = :id";
        $data['id'] = $id;
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($data);
    }

    /**
     * Eliminar un registro
     * 
     * @param int $id El ID del registro a eliminar
     * @return bool
     */
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Ejecutar una consulta SQL personalizada
     * 
     * @param string $sql La consulta SQL
     * @param array $params Los parámetros de la consulta
     * @return array
     */
    protected function query($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}

?>
