<?php
// models/Employee.php
class Employee {
    private $conn;
    private $table = 'empleado';
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Obtener todos los empleados con información completa
     */
    public function getAll($filters = []) {
        $query = "SELECT 
                    e.ID_EMPLEADO,
                    e.NOMBRE,
                    e.APELLIDO,
                    e.DNI,
                    e.CORREO,
                    e.TELEFONO,
                    e.FECHA_INGRESO,
                    e.ESTADO,
                    e.ACTIVO,
                    est.NOMBRE as establecimiento,
                    est.ID_ESTABLECIMIENTO,
                    s.NOMBRE as sede,
                    s.ID_SEDE,
                    emp.NOMBRE as empresa,
                    emp.ID_EMPRESA,
                    -- Generar código automático basado en ID
                    CONCAT('EMP', LPAD(e.ID_EMPLEADO, 3, '0')) as codigo
                  FROM " . $this->table . " e
                  LEFT JOIN establecimiento est ON e.ID_ESTABLECIMIENTO = est.ID_ESTABLECIMIENTO
                  LEFT JOIN sede s ON est.ID_SEDE = s.ID_SEDE
                  LEFT JOIN empresa emp ON s.ID_EMPRESA = emp.ID_EMPRESA
                  WHERE 1=1";
        
        $params = [];
        
        // Aplicar filtros
        if (!empty($filters['codigo'])) {
            $query .= " AND CONCAT('EMP', LPAD(e.ID_EMPLEADO, 3, '0')) LIKE :codigo";
            $params[':codigo'] = '%' . $filters['codigo'] . '%';
        }
        
        if (!empty($filters['identificacion'])) {
            $query .= " AND e.DNI LIKE :identificacion";
            $params[':identificacion'] = '%' . $filters['identificacion'] . '%';
        }
        
        if (!empty($filters['nombre'])) {
            $query .= " AND (e.NOMBRE LIKE :nombre OR e.APELLIDO LIKE :apellido OR CONCAT(e.NOMBRE, ' ', e.APELLIDO) LIKE :nombre_completo)";
            $params[':nombre'] = '%' . $filters['nombre'] . '%';
            $params[':apellido'] = '%' . $filters['nombre'] . '%';
            $params[':nombre_completo'] = '%' . $filters['nombre'] . '%';
        }
        
        if (!empty($filters['departamento'])) {
            $query .= " AND est.NOMBRE LIKE :departamento";
            $params[':departamento'] = '%' . $filters['departamento'] . '%';
        }
        
        if (!empty($filters['sede'])) {
            $query .= " AND s.NOMBRE LIKE :sede";
            $params[':sede'] = '%' . $filters['sede'] . '%';
        }
        
        if (!empty($filters['establecimiento'])) {
            $query .= " AND e.ID_ESTABLECIMIENTO = :establecimiento_id";
            $params[':establecimiento_id'] = $filters['establecimiento'];
        }
        
        if (!empty($filters['estado'])) {
            $estado_db = $filters['estado'] === 'Activo' ? 'A' : 'I';
            $query .= " AND e.ESTADO = :estado";
            $params[':estado'] = $estado_db;
        }
        
        if (!empty($filters['activo'])) {
            $activo_db = $filters['activo'] === 'Si' ? 'S' : 'N';
            $query .= " AND e.ACTIVO = :activo";
            $params[':activo'] = $activo_db;
        }
        
        $query .= " ORDER BY e.ID_EMPLEADO ASC";
        
        // Paginación
        if (isset($filters['limit']) && isset($filters['offset'])) {
            $query .= " LIMIT :limit OFFSET :offset";
            $params[':limit'] = (int)$filters['limit'];
            $params[':offset'] = (int)$filters['offset'];
        }
        
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters manualmente para LIMIT/OFFSET
        foreach ($params as $key => $value) {
            if ($key === ':limit' || $key === ':offset') {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value);
            }
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Contar empleados con filtros
     */
    public function count($filters = []) {
        $query = "SELECT COUNT(*) as total
                  FROM " . $this->table . " e
                  LEFT JOIN establecimiento est ON e.ID_ESTABLECIMIENTO = est.ID_ESTABLECIMIENTO
                  LEFT JOIN sede s ON est.ID_SEDE = s.ID_SEDE
                  LEFT JOIN empresa emp ON s.ID_EMPRESA = emp.ID_EMPRESA
                  WHERE 1=1";
        
        $params = [];
        
        // Aplicar los mismos filtros que en getAll()
        if (!empty($filters['codigo'])) {
            $query .= " AND CONCAT('EMP', LPAD(e.ID_EMPLEADO, 3, '0')) LIKE :codigo";
            $params[':codigo'] = '%' . $filters['codigo'] . '%';
        }
        
        if (!empty($filters['identificacion'])) {
            $query .= " AND e.DNI LIKE :identificacion";
            $params[':identificacion'] = '%' . $filters['identificacion'] . '%';
        }
        
        if (!empty($filters['nombre'])) {
            $query .= " AND (e.NOMBRE LIKE :nombre OR e.APELLIDO LIKE :apellido OR CONCAT(e.NOMBRE, ' ', e.APELLIDO) LIKE :nombre_completo)";
            $params[':nombre'] = '%' . $filters['nombre'] . '%';
            $params[':apellido'] = '%' . $filters['nombre'] . '%';
            $params[':nombre_completo'] = '%' . $filters['nombre'] . '%';
        }
        
        if (!empty($filters['departamento'])) {
            $query .= " AND est.NOMBRE LIKE :departamento";
            $params[':departamento'] = '%' . $filters['departamento'] . '%';
        }
        
        if (!empty($filters['sede'])) {
            $query .= " AND s.NOMBRE LIKE :sede";
            $params[':sede'] = '%' . $filters['sede'] . '%';
        }
        
        if (!empty($filters['establecimiento'])) {
            $query .= " AND e.ID_ESTABLECIMIENTO = :establecimiento_id";
            $params[':establecimiento_id'] = $filters['establecimiento'];
        }
        
        if (!empty($filters['estado'])) {
            $estado_db = $filters['estado'] === 'Activo' ? 'A' : 'I';
            $query .= " AND e.ESTADO = :estado";
            $params[':estado'] = $estado_db;
        }
        
        if (!empty($filters['activo'])) {
            $activo_db = $filters['activo'] === 'Si' ? 'S' : 'N';
            $query .= " AND e.ACTIVO = :activo";
            $params[':activo'] = $activo_db;
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        
        $result = $stmt->fetch();
        return $result['total'];
    }
    
    /**
     * Obtener empleado por ID
     */
    public function getById($id) {
        $query = "SELECT 
                    e.*,
                    est.NOMBRE as establecimiento,
                    est.ID_ESTABLECIMIENTO,
                    s.NOMBRE as sede,
                    s.ID_SEDE,
                    emp.NOMBRE as empresa,
                    emp.ID_EMPRESA,
                    CONCAT('EMP', LPAD(e.ID_EMPLEADO, 3, '0')) as codigo
                  FROM " . $this->table . " e
                  LEFT JOIN establecimiento est ON e.ID_ESTABLECIMIENTO = est.ID_ESTABLECIMIENTO
                  LEFT JOIN sede s ON est.ID_SEDE = s.ID_SEDE
                  LEFT JOIN empresa emp ON s.ID_EMPRESA = emp.ID_EMPRESA
                  WHERE e.ID_EMPLEADO = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    /**
     * Crear nuevo empleado
     */
    public function create($data) {
        $query = "INSERT INTO " . $this->table . "
                  (NOMBRE, APELLIDO, DNI, CORREO, TELEFONO, ID_ESTABLECIMIENTO, FECHA_INGRESO, ESTADO, ACTIVO)
                  VALUES 
                  (:nombre, :apellido, :dni, :correo, :telefono, :id_establecimiento, :fecha_ingreso, :estado, :activo)";
        
        $stmt = $this->conn->prepare($query);
        
        $result = $stmt->execute([
            ':nombre' => $data['nombre'],
            ':apellido' => $data['apellido'],
            ':dni' => $data['dni'],
            ':correo' => $data['correo'] ?? null,
            ':telefono' => $data['telefono'] ?? null,
            ':id_establecimiento' => $data['id_establecimiento'],
            ':fecha_ingreso' => $data['fecha_ingreso'] ?? date('Y-m-d'),
            ':estado' => $data['estado'] ?? 'A',
            ':activo' => $data['activo'] ?? 'S'
        ]);
        
        if ($result) {
            return $this->conn->lastInsertId();
        }
        return false;
    }
    
    /**
     * Actualizar empleado
     */
    public function update($id, $data) {
        $query = "UPDATE " . $this->table . " SET
                  NOMBRE = :nombre,
                  APELLIDO = :apellido,
                  DNI = :dni,
                  CORREO = :correo,
                  TELEFONO = :telefono,
                  ID_ESTABLECIMIENTO = :id_establecimiento,
                  FECHA_INGRESO = :fecha_ingreso,
                  ESTADO = :estado,
                  ACTIVO = :activo
                  WHERE ID_EMPLEADO = :id";
        
        $stmt = $this->conn->prepare($query);
        
        return $stmt->execute([
            ':id' => $id,
            ':nombre' => $data['nombre'],
            ':apellido' => $data['apellido'],
            ':dni' => $data['dni'],
            ':correo' => $data['correo'] ?? null,
            ':telefono' => $data['telefono'] ?? null,
            ':id_establecimiento' => $data['id_establecimiento'],
            ':fecha_ingreso' => $data['fecha_ingreso'],
            ':estado' => $data['estado'] ?? 'A',
            ':activo' => $data['activo'] ?? 'S'
        ]);
    }
    
    /**
     * Eliminar empleado (cambiar estado)
     */
    public function delete($id) {
        $query = "UPDATE " . $this->table . " SET ESTADO = 'I', ACTIVO = 'N' WHERE ID_EMPLEADO = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }
    
    /**
     * Verificar si existe DNI
     */
    public function existsDNI($dni, $excludeId = null) {
        $query = "SELECT ID_EMPLEADO FROM " . $this->table . " WHERE DNI = :dni";
        $params = [':dni' => $dni];
        
        if ($excludeId) {
            $query .= " AND ID_EMPLEADO != :exclude_id";
            $params[':exclude_id'] = $excludeId;
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetch() !== false;
    }
    
    /**
     * Verificar si existe correo
     */
    public function existsEmail($email, $excludeId = null) {
        if (empty($email)) return false;
        
        $query = "SELECT ID_EMPLEADO FROM " . $this->table . " WHERE CORREO = :email";
        $params = [':email' => $email];
        
        if ($excludeId) {
            $query .= " AND ID_EMPLEADO != :exclude_id";
            $params[':exclude_id'] = $excludeId;
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetch() !== false;
    }
}
?>