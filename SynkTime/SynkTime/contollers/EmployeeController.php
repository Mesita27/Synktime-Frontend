<?php
// controllers/EmployeeController.php
class EmployeeController {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    /**
     * Obtener todos los empleados con filtros completos
     */
    public function index() {
        try {
            // Obtener parámetros de filtro
            $filters = [
                'codigo' => $_GET['codigo'] ?? '',
                'identificacion' => $_GET['identificacion'] ?? '',
                'nombre' => $_GET['nombre'] ?? '',
                'departamento' => $_GET['departamento'] ?? '',
                'sede' => $_GET['sede'] ?? '',
                'establecimiento' => $_GET['establecimiento'] ?? '',
                'id_sede' => $_GET['id_sede'] ?? '',
                'id_establecimiento' => $_GET['id_establecimiento'] ?? '',
                'id_empresa' => $_GET['id_empresa'] ?? '',
                'estado' => $_GET['estado'] ?? ''
            ];
            
            // Parámetros de paginación
            $page = (int)($_GET['page'] ?? 1);
            $limit = (int)($_GET['limit'] ?? 50);
            $offset = ($page - 1) * $limit;
            
            // Query principal con JOINs
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
                        e.ID_ESTABLECIMIENTO,
                        est.NOMBRE as establecimiento_nombre,
                        est.DIRECCION as establecimiento_direccion,
                        est.ID_SEDE,
                        s.NOMBRE as sede_nombre,
                        s.DIRECCION as sede_direccion,
                        s.ID_EMPRESA,
                        emp.NOMBRE as empresa_nombre,
                        emp.RUC as empresa_ruc,
                        CONCAT('EMP', LPAD(e.ID_EMPLEADO, 3, '0')) as codigo
                      FROM empleado e
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
                $query .= " AND est.NOMBRE LIKE :establecimiento";
                $params[':establecimiento'] = '%' . $filters['establecimiento'] . '%';
            }
            
            // Filtros por ID específicos
            if (!empty($filters['id_empresa'])) {
                $query .= " AND emp.ID_EMPRESA = :id_empresa";
                $params[':id_empresa'] = $filters['id_empresa'];
            }
            
            if (!empty($filters['id_sede'])) {
                $query .= " AND s.ID_SEDE = :id_sede";
                $params[':id_sede'] = $filters['id_sede'];
            }
            
            if (!empty($filters['id_establecimiento'])) {
                $query .= " AND est.ID_ESTABLECIMIENTO = :id_establecimiento";
                $params[':id_establecimiento'] = $filters['id_establecimiento'];
            }
            
            if (!empty($filters['estado'])) {
                $estado_db = $filters['estado'] === 'Activo' ? 'A' : 'I';
                $query .= " AND e.ESTADO = :estado";
                $params[':estado'] = $estado_db;
            }
            
            // Contar total de registros (sin LIMIT)
            $countQuery = str_replace('SELECT e.ID_EMPLEADO, e.NOMBRE, e.APELLIDO, e.DNI, e.CORREO, e.TELEFONO, e.FECHA_INGRESO, e.ESTADO, e.ACTIVO, e.ID_ESTABLECIMIENTO, est.NOMBRE as establecimiento_nombre, est.DIRECCION as establecimiento_direccion, est.ID_SEDE, s.NOMBRE as sede_nombre, s.DIRECCION as sede_direccion, s.ID_EMPRESA, emp.NOMBRE as empresa_nombre, emp.RUC as empresa_ruc, CONCAT(\'EMP\', LPAD(e.ID_EMPLEADO, 3, \'0\')) as codigo', 'SELECT COUNT(*) as total', $query);
            
            $countStmt = $this->conn->prepare($countQuery);
            $countStmt->execute($params);
            $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Agregar ordenamiento y paginación al query principal
            $query .= " ORDER BY e.ID_EMPLEADO ASC LIMIT :limit OFFSET :offset";
            $params[':limit'] = $limit;
            $params[':offset'] = $offset;
            
            $stmt = $this->conn->prepare($query);
            
            // Bind parameters correctamente
            foreach ($params as $key => $value) {
                if ($key === ':limit' || $key === ':offset') {
                    $stmt->bindValue($key, (int)$value, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue($key, $value, PDO::PARAM_STR);
                }
            }
            
            $stmt->execute();
            $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Formatear datos para el frontend
            $formattedEmployees = array_map([$this, 'formatEmployee'], $employees);
            
            $this->jsonResponse([
                'success' => true,
                'data' => $formattedEmployees,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $limit,
                    'total' => (int)$total,
                    'total_pages' => ceil($total / $limit)
                ],
                'filters_applied' => array_filter($filters, function($value) {
                    return $value !== '' && $value !== null;
                })
            ]);
            
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error obteniendo empleados: ' . $e->getMessage(),
                'debug' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]
            ], 500);
        }
    }
    
    /**
     * Obtener empleado por ID
     */
    public function show($id) {
        try {
            if (!is_numeric($id) || $id <= 0) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'ID de empleado inválido'
                ], 400);
                return;
            }
            
            $query = "SELECT 
                        e.*,
                        est.NOMBRE as establecimiento_nombre,
                        est.ID_SEDE,
                        s.NOMBRE as sede_nombre,
                        s.ID_EMPRESA,
                        emp.NOMBRE as empresa_nombre,
                        CONCAT('EMP', LPAD(e.ID_EMPLEADO, 3, '0')) as codigo
                      FROM empleado e
                      LEFT JOIN establecimiento est ON e.ID_ESTABLECIMIENTO = est.ID_ESTABLECIMIENTO
                      LEFT JOIN sede s ON est.ID_SEDE = s.ID_SEDE
                      LEFT JOIN empresa emp ON s.ID_EMPRESA = emp.ID_EMPRESA
                      WHERE e.ID_EMPLEADO = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
            $stmt->execute();
            
            $employee = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$employee) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Empleado no encontrado'
                ], 404);
                return;
            }
            
            $formattedEmployee = $this->formatEmployee($employee);
            
            $this->jsonResponse([
                'success' => true,
                'data' => $formattedEmployee
            ]);
            
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error obteniendo empleado: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Crear nuevo empleado
     */
    public function create() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'JSON inválido: ' . json_last_error_msg()
                ], 400);
                return;
            }
            
            // Validaciones básicas
            $requiredFields = ['nombre', 'apellido', 'dni'];
            foreach ($requiredFields as $field) {
                if (empty($input[$field])) {
                    $this->jsonResponse([
                        'success' => false,
                        'message' => "El campo {$field} es requerido"
                    ], 400);
                    return;
                }
            }
            
            // Verificar DNI único
            if ($this->existsDNI($input['dni'])) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Ya existe un empleado con este DNI'
                ], 400);
                return;
            }
            
            // Verificar email único si se proporciona
            if (!empty($input['email']) && $this->existsEmail($input['email'])) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Ya existe un empleado con este email'
                ], 400);
                return;
            }
            
            // Insertar empleado
            $query = "INSERT INTO empleado 
                      (NOMBRE, APELLIDO, DNI, CORREO, TELEFONO, ID_ESTABLECIMIENTO, FECHA_INGRESO, ESTADO, ACTIVO)
                      VALUES 
                      (:nombre, :apellido, :dni, :correo, :telefono, :id_establecimiento, :fecha_ingreso, :estado, :activo)";
            
            $stmt = $this->conn->prepare($query);
            
            $data = [
                ':nombre' => trim($input['nombre']),
                ':apellido' => trim($input['apellido']),
                ':dni' => trim($input['dni']),
                ':correo' => !empty($input['email']) ? trim($input['email']) : null,
                ':telefono' => !empty($input['telefono']) ? trim($input['telefono']) : null,
                ':id_establecimiento' => !empty($input['id_establecimiento']) ? (int)$input['id_establecimiento'] : null,
                ':fecha_ingreso' => !empty($input['fecha_contratacion']) ? $input['fecha_contratacion'] : date('Y-m-d'),
                ':estado' => isset($input['estado']) && $input['estado'] === 'Inactivo' ? 'I' : 'A',
                ':activo' => isset($input['activo']) && $input['activo'] === 'No' ? 'N' : 'S'
            ];
            
            $result = $stmt->execute($data);
            
            if ($result) {
                $newId = $this->conn->lastInsertId();
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Empleado creado exitosamente',
                    'data' => [
                        'id' => (int)$newId,
                        'codigo' => 'EMP' . str_pad($newId, 3, '0', STR_PAD_LEFT)
                    ]
                ], 201);
            } else {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Error creando empleado'
                ], 500);
            }
            
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error interno: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Actualizar empleado
     */
    public function update($id) {
        try {
            if (!is_numeric($id) || $id <= 0) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'ID de empleado inválido'
                ], 400);
                return;
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'JSON inválido: ' . json_last_error_msg()
                ], 400);
                return;
            }
            
            // Verificar que el empleado existe
            if (!$this->employeeExists($id)) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Empleado no encontrado'
                ], 404);
                return;
            }
            
            // Validaciones
            $requiredFields = ['nombre', 'apellido', 'dni'];
            foreach ($requiredFields as $field) {
                if (empty($input[$field])) {
                    $this->jsonResponse([
                        'success' => false,
                        'message' => "El campo {$field} es requerido"
                    ], 400);
                    return;
                }
            }
            
            // Verificar DNI único (excluyendo el empleado actual)
            if ($this->existsDNI($input['dni'], $id)) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Ya existe otro empleado con este DNI'
                ], 400);
                return;
            }
            
            // Verificar email único (excluyendo el empleado actual)
            if (!empty($input['email']) && $this->existsEmail($input['email'], $id)) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Ya existe otro empleado con este email'
                ], 400);
                return;
            }
            
            // Actualizar empleado
            $query = "UPDATE empleado SET
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
            
            $data = [
                ':id' => (int)$id,
                ':nombre' => trim($input['nombre']),
                ':apellido' => trim($input['apellido']),
                ':dni' => trim($input['dni']),
                ':correo' => !empty($input['email']) ? trim($input['email']) : null,
                ':telefono' => !empty($input['telefono']) ? trim($input['telefono']) : null,
                ':id_establecimiento' => !empty($input['id_establecimiento']) ? (int)$input['id_establecimiento'] : null,
                ':fecha_ingreso' => !empty($input['fecha_contratacion']) ? $input['fecha_contratacion'] : date('Y-m-d'),
                ':estado' => isset($input['estado']) && $input['estado'] === 'Inactivo' ? 'I' : 'A',
                ':activo' => isset($input['activo']) && $input['activo'] === 'No' ? 'N' : 'S'
            ];
            
            $result = $stmt->execute($data);
            
            if ($result) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Empleado actualizado exitosamente'
                ]);
            } else {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Error actualizando empleado'
                ], 500);
            }
            
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error interno: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Eliminar empleado (cambiar estado)
     */
    public function delete($id) {
        try {
            if (!is_numeric($id) || $id <= 0) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'ID de empleado inválido'
                ], 400);
                return;
            }
            
            if (!$this->employeeExists($id)) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Empleado no encontrado'
                ], 404);
                return;
            }
            
            $query = "UPDATE empleado SET ESTADO = 'I', ACTIVO = 'N' WHERE ID_EMPLEADO = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Empleado eliminado exitosamente'
                ]);
            } else {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Error eliminando empleado'
                ], 500);
            }
            
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error interno: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Obtener datos para formularios
     */
    public function getFormData() {
        try {
            // Obtener empresas
            $empresasQuery = "SELECT * FROM empresa WHERE ESTADO = 'A' ORDER BY NOMBRE";
            $empresasStmt = $this->conn->prepare($empresasQuery);
            $empresasStmt->execute();
            $empresas = $empresasStmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Obtener sedes
            $sedesQuery = "SELECT s.*, e.NOMBRE as empresa_nombre 
                          FROM sede s 
                          LEFT JOIN empresa e ON s.ID_EMPRESA = e.ID_EMPRESA 
                          WHERE s.ESTADO = 'A' 
                          ORDER BY e.NOMBRE, s.NOMBRE";
            $sedesStmt = $this->conn->prepare($sedesQuery);
            $sedesStmt->execute();
            $sedes = $sedesStmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Obtener establecimientos
            $establecimientosQuery = "SELECT est.*, s.NOMBRE as sede_nombre, e.NOMBRE as empresa_nombre 
                                     FROM establecimiento est
                                     LEFT JOIN sede s ON est.ID_SEDE = s.ID_SEDE
                                     LEFT JOIN empresa e ON s.ID_EMPRESA = e.ID_EMPRESA
                                     WHERE est.ESTADO = 'A'
                                     ORDER BY e.NOMBRE, s.NOMBRE, est.NOMBRE";
            $establecimientosStmt = $this->conn->prepare($establecimientosQuery);
            $establecimientosStmt->execute();
            $establecimientos = $establecimientosStmt->fetchAll(PDO::FETCH_ASSOC);
            
            $this->jsonResponse([
                'success' => true,
                'data' => [
                    'empresas' => $empresas,
                    'sedes' => $sedes,
                    'establecimientos' => $establecimientos
                ]
            ]);
            
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error obteniendo datos: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Verificar si existe DNI
     */
    private function existsDNI($dni, $excludeId = null) {
        $query = "SELECT ID_EMPLEADO FROM empleado WHERE DNI = :dni";
        $params = [':dni' => $dni];
        
        if ($excludeId !== null) {
            $query .= " AND ID_EMPLEADO != :exclude_id";
            $params[':exclude_id'] = (int)$excludeId;
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }
    
    /**
     * Verificar si existe email
     */
    private function existsEmail($email, $excludeId = null) {
        if (empty($email)) return false;
        
        $query = "SELECT ID_EMPLEADO FROM empleado WHERE CORREO = :email";
        $params = [':email' => $email];
        
        if ($excludeId !== null) {
            $query .= " AND ID_EMPLEADO != :exclude_id";
            $params[':exclude_id'] = (int)$excludeId;
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        
        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }
    
    /**
     * Verificar si existe empleado
     */
    private function employeeExists($id) {
        $query = "SELECT ID_EMPLEADO FROM empleado WHERE ID_EMPLEADO = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }
    
    /**
     * Formatear datos del empleado
     */
    private function formatEmployee($emp) {
        return [
            'id' => (int)$emp['ID_EMPLEADO'],
            'codigo' => $emp['codigo'],
            'identificacion' => $emp['DNI'],
            'tipo_identificacion' => 'CC',
            'nombre' => $emp['NOMBRE'],
            'apellido' => $emp['APELLIDO'],
            'email' => $emp['CORREO'],
            'telefono' => $emp['TELEFONO'],
            'fecha_contratacion' => $emp['FECHA_INGRESO'],
            'estado' => $emp['ESTADO'] === 'A' ? 'Activo' : 'Inactivo',
            'activo' => $emp['ACTIVO'] === 'S' ? 'Si' : 'No',
            'departamento' => $emp['establecimiento_nombre'] ?? 'Sin asignar',
            'sede' => $emp['sede_nombre'] ?? 'Sin asignar',
            'empresa' => $emp['empresa_nombre'] ?? 'Sin asignar',
            'id_establecimiento' => $emp['ID_ESTABLECIMIENTO'] ? (int)$emp['ID_ESTABLECIMIENTO'] : null,
            'id_sede' => isset($emp['ID_SEDE']) && $emp['ID_SEDE'] ? (int)$emp['ID_SEDE'] : null,
            'id_empresa' => isset($emp['ID_EMPRESA']) && $emp['ID_EMPRESA'] ? (int)$emp['ID_EMPRESA'] : null,
            'establecimiento_direccion' => $emp['establecimiento_direccion'] ?? null,
            'sede_direccion' => $emp['sede_direccion'] ?? null,
            'empresa_ruc' => $emp['empresa_ruc'] ?? null
        ];
    }
    
    /**
     * Respuesta JSON estándar
     */
    private function jsonResponse($data, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
}
?>