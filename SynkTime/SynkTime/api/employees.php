<?php
ini_set('display_errors', 0); // Apaga los errores visibles
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);

require_once __DIR__ . '/../config/database.php'; // Usa __DIR__ para evitar errores de ruta

// Función para log de debug
function debugLog($message, $data = null) {
    error_log("[EMPLOYEES API] " . $message . ($data ? " - Data: " . json_encode($data) : ""));
}

// Función de respuesta JSON
function jsonResponse($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    debugLog("API Called", $_SERVER['REQUEST_METHOD'] . " " . $_SERVER['REQUEST_URI']);
    
    $method = $_SERVER['REQUEST_METHOD'];
    $request_uri = $_SERVER['REQUEST_URI'];
    
    // Parsear URL para obtener acción
    $path_parts = explode('/', trim(parse_url($request_uri, PHP_URL_PATH), '/'));
    $action = null;
    
    // Buscar employees.php en la ruta y obtener el siguiente elemento
    for ($i = 0; $i < count($path_parts); $i++) {
        if ($path_parts[$i] === 'employees.php' && isset($path_parts[$i + 1])) {
            $action = $path_parts[$i + 1];
            break;
        }
    }
    
    debugLog("Action detected", $action);
    
    switch ($method) {
        case 'GET':
            if (is_numeric($action)) {
                // Obtener empleado específico
                getEmployee($conn, $action);
            } elseif ($action === 'form-data') {
                // Obtener datos para formularios
                getFormData($conn);
            } else {
                // Obtener todos los empleados
                getEmployees($conn);
            }
            break;
            
        case 'POST':
            createEmployee($conn);
            break;
            
        case 'PUT':
            if (is_numeric($action)) {
                updateEmployee($conn, $action);
            } else {
                jsonResponse(['success' => false, 'message' => 'ID requerido para actualizar'], 400);
            }
            break;
            
        case 'DELETE':
            if (is_numeric($action)) {
                deleteEmployee($conn, $action);
            } else {
                jsonResponse(['success' => false, 'message' => 'ID requerido para eliminar'], 400);
            }
            break;
            
        default:
            jsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
            break;
    }
    
} catch (Exception $e) {
    debugLog("ERROR", $e->getMessage());
    jsonResponse([
        'success' => false,
        'message' => 'Error interno del servidor',
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ], 500);
}

/**
 * Obtener todos los empleados
 */
function getEmployees($conn) {
    try {
        debugLog("Getting employees with filters", $_GET);
        
        // Obtener filtros
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
        
        // Paginación
        $page = (int)($_GET['page'] ?? 1);
        $limit = (int)($_GET['limit'] ?? 50);
        $offset = ($page - 1) * $limit;
        
        // Query base - SIMPLIFICADO PARA DEBUG
        $baseQuery = "SELECT 
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
                        CONCAT('EMP', LPAD(e.ID_EMPLEADO, 3, '0')) as codigo,
                        COALESCE(est.NOMBRE, 'Sin asignar') as establecimiento_nombre,
                        COALESCE(s.NOMBRE, 'Sin asignar') as sede_nombre,
                        COALESCE(emp.NOMBRE, 'Sin asignar') as empresa_nombre,
                        est.ID_SEDE,
                        s.ID_EMPRESA
                      FROM empleado e
                      LEFT JOIN establecimiento est ON e.ID_ESTABLECIMIENTO = est.ID_ESTABLECIMIENTO
                      LEFT JOIN sede s ON est.ID_SEDE = s.ID_SEDE
                      LEFT JOIN empresa emp ON s.ID_EMPRESA = emp.ID_EMPRESA";
        
        $whereConditions = [];
        $params = [];
        
        // Aplicar filtros
        if (!empty($filters['codigo'])) {
            $whereConditions[] = "CONCAT('EMP', LPAD(e.ID_EMPLEADO, 3, '0')) LIKE :codigo";
            $params[':codigo'] = '%' . $filters['codigo'] . '%';
        }
        
        if (!empty($filters['identificacion'])) {
            $whereConditions[] = "e.DNI LIKE :identificacion";
            $params[':identificacion'] = '%' . $filters['identificacion'] . '%';
        }
        
        if (!empty($filters['nombre'])) {
            $whereConditions[] = "(e.NOMBRE LIKE :nombre OR e.APELLIDO LIKE :apellido OR CONCAT(e.NOMBRE, ' ', e.APELLIDO) LIKE :nombre_completo)";
            $params[':nombre'] = '%' . $filters['nombre'] . '%';
            $params[':apellido'] = '%' . $filters['nombre'] . '%';
            $params[':nombre_completo'] = '%' . $filters['nombre'] . '%';
        }
        
        if (!empty($filters['departamento'])) {
            $whereConditions[] = "est.NOMBRE LIKE :departamento";
            $params[':departamento'] = '%' . $filters['departamento'] . '%';
        }
        
        if (!empty($filters['sede'])) {
            $whereConditions[] = "s.NOMBRE LIKE :sede";
            $params[':sede'] = '%' . $filters['sede'] . '%';
        }
        
        if (!empty($filters['establecimiento'])) {
            $whereConditions[] = "est.NOMBRE LIKE :establecimiento";
            $params[':establecimiento'] = '%' . $filters['establecimiento'] . '%';
        }
        
        if (!empty($filters['id_empresa'])) {
            $whereConditions[] = "emp.ID_EMPRESA = :id_empresa";
            $params[':id_empresa'] = (int)$filters['id_empresa'];
        }
        
        if (!empty($filters['id_sede'])) {
            $whereConditions[] = "s.ID_SEDE = :id_sede";
            $params[':id_sede'] = (int)$filters['id_sede'];
        }
        
        if (!empty($filters['id_establecimiento'])) {
            $whereConditions[] = "est.ID_ESTABLECIMIENTO = :id_establecimiento";
            $params[':id_establecimiento'] = (int)$filters['id_establecimiento'];
        }
        
        if (!empty($filters['estado'])) {
            $whereConditions[] = "e.ESTADO = :estado";
            $params[':estado'] = $filters['estado'] === 'Activo' ? 'A' : 'I';
        }
        
        // Construir query completa
        $query = $baseQuery;
        if (!empty($whereConditions)) {
            $query .= " WHERE " . implode(" AND ", $whereConditions);
        }
        
        // Contar total
        $countQuery = "SELECT COUNT(*) as total FROM (" . $query . ") as count_table";
        $countStmt = $conn->prepare($countQuery);
        $countStmt->execute($params);
        $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Agregar orden y paginación
        $query .= " ORDER BY e.ID_EMPLEADO ASC LIMIT :limit OFFSET :offset";
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;
        
        debugLog("Final query", $query);
        debugLog("Parameters", $params);
        
        $stmt = $conn->prepare($query);
        
        // Ejecutar con bind correcto
        foreach ($params as $key => $value) {
            if ($key === ':limit' || $key === ':offset' || $key === ':id_empresa' || $key === ':id_sede' || $key === ':id_establecimiento') {
                $stmt->bindValue($key, (int)$value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value, PDO::PARAM_STR);
            }
        }
        
        $stmt->execute();
        $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        debugLog("Employees found", count($employees));
        
        // Formatear datos
        $formattedEmployees = array_map(function($emp) {
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
                'departamento' => $emp['establecimiento_nombre'],
                'sede' => $emp['sede_nombre'],
                'empresa' => $emp['empresa_nombre'],
                'id_establecimiento' => $emp['ID_ESTABLECIMIENTO'] ? (int)$emp['ID_ESTABLECIMIENTO'] : null,
                'id_sede' => $emp['ID_SEDE'] ? (int)$emp['ID_SEDE'] : null,
                'id_empresa' => $emp['ID_EMPRESA'] ? (int)$emp['ID_EMPRESA'] : null
            ];
        }, $employees);
        
        jsonResponse([
            'success' => true,
            'data' => $formattedEmployees,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total' => (int)$total,
                'total_pages' => ceil($total / $limit)
            ],
            'filters_applied' => array_filter($filters),
            'debug' => [
                'query_executed' => true,
                'total_found' => (int)$total,
                'page_results' => count($formattedEmployees)
            ]
        ]);
        
    } catch (Exception $e) {
        debugLog("Error in getEmployees", $e->getMessage());
        jsonResponse([
            'success' => false,
            'message' => 'Error obteniendo empleados: ' . $e->getMessage(),
            'debug' => [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]
        ], 500);
    }
}

/**
 * Obtener empleado específico
 */
function getEmployee($conn, $id) {
    try {
        $query = "SELECT 
                    e.*,
                    CONCAT('EMP', LPAD(e.ID_EMPLEADO, 3, '0')) as codigo,
                    COALESCE(est.NOMBRE, 'Sin asignar') as establecimiento_nombre,
                    COALESCE(s.NOMBRE, 'Sin asignar') as sede_nombre,
                    COALESCE(emp.NOMBRE, 'Sin asignar') as empresa_nombre,
                    est.ID_SEDE,
                    s.ID_EMPRESA
                  FROM empleado e
                  LEFT JOIN establecimiento est ON e.ID_ESTABLECIMIENTO = est.ID_ESTABLECIMIENTO
                  LEFT JOIN sede s ON est.ID_SEDE = s.ID_SEDE
                  LEFT JOIN empresa emp ON s.ID_EMPRESA = emp.ID_EMPRESA
                  WHERE e.ID_EMPLEADO = :id";
        
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $stmt->execute();
        
        $employee = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$employee) {
            jsonResponse(['success' => false, 'message' => 'Empleado no encontrado'], 404);
            return;
        }
        
        $formattedEmployee = [
            'id' => (int)$employee['ID_EMPLEADO'],
            'codigo' => $employee['codigo'],
            'identificacion' => $employee['DNI'],
            'tipo_identificacion' => 'CC',
            'nombre' => $employee['NOMBRE'],
            'apellido' => $employee['APELLIDO'],
            'email' => $employee['CORREO'],
            'telefono' => $employee['TELEFONO'],
            'fecha_contratacion' => $employee['FECHA_INGRESO'],
            'estado' => $employee['ESTADO'] === 'A' ? 'Activo' : 'Inactivo',
            'activo' => $employee['ACTIVO'] === 'S' ? 'Si' : 'No',
            'departamento' => $employee['establecimiento_nombre'],
            'sede' => $employee['sede_nombre'],
            'empresa' => $employee['empresa_nombre'],
            'id_establecimiento' => $employee['ID_ESTABLECIMIENTO'] ? (int)$employee['ID_ESTABLECIMIENTO'] : null,
            'id_sede' => $employee['ID_SEDE'] ? (int)$employee['ID_SEDE'] : null,
            'id_empresa' => $employee['ID_EMPRESA'] ? (int)$employee['ID_EMPRESA'] : null
        ];
        
        jsonResponse(['success' => true, 'data' => $formattedEmployee]);
        
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
    }
}

/**
 * Obtener datos para formularios
 */
function getFormData($conn) {
    try {
        // Empresas
        $empresasQuery = "SELECT * FROM empresa WHERE ESTADO = 'A' ORDER BY NOMBRE";
        $empresasStmt = $conn->prepare($empresasQuery);
        $empresasStmt->execute();
        $empresas = $empresasStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Sedes
        $sedesQuery = "SELECT s.*, e.NOMBRE as empresa_nombre 
                      FROM sede s 
                      LEFT JOIN empresa e ON s.ID_EMPRESA = e.ID_EMPRESA 
                      WHERE s.ESTADO = 'A' 
                      ORDER BY e.NOMBRE, s.NOMBRE";
        $sedesStmt = $conn->prepare($sedesQuery);
        $sedesStmt->execute();
        $sedes = $sedesStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Establecimientos
        $establecimientosQuery = "SELECT est.*, s.NOMBRE as sede_nombre, e.NOMBRE as empresa_nombre 
                                 FROM establecimiento est
                                 LEFT JOIN sede s ON est.ID_SEDE = s.ID_SEDE
                                 LEFT JOIN empresa e ON s.ID_EMPRESA = e.ID_EMPRESA
                                 WHERE est.ESTADO = 'A'
                                 ORDER BY e.NOMBRE, s.NOMBRE, est.NOMBRE";
        $establecimientosStmt = $conn->prepare($establecimientosQuery);
        $establecimientosStmt->execute();
        $establecimientos = $establecimientosStmt->fetchAll(PDO::FETCH_ASSOC);
        
        jsonResponse([
            'success' => true,
            'data' => [
                'empresas' => $empresas,
                'sedes' => $sedes,
                'establecimientos' => $establecimientos
            ]
        ]);
        
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
    }
}

/**
 * Crear empleado
 */
function createEmployee($conn) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            jsonResponse(['success' => false, 'message' => 'JSON inválido'], 400);
            return;
        }
        
        // Validaciones
        if (empty($input['nombre']) || empty($input['apellido']) || empty($input['dni'])) {
            jsonResponse(['success' => false, 'message' => 'Nombre, apellido y DNI son requeridos'], 400);
            return;
        }
        
        // Verificar DNI único
        $checkQuery = "SELECT ID_EMPLEADO FROM empleado WHERE DNI = :dni";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bindValue(':dni', $input['dni']);
        $checkStmt->execute();
        
        if ($checkStmt->fetch()) {
            jsonResponse(['success' => false, 'message' => 'Ya existe un empleado con este DNI'], 400);
            return;
        }
        
        // Insertar
        $insertQuery = "INSERT INTO empleado 
                       (NOMBRE, APELLIDO, DNI, CORREO, TELEFONO, ID_ESTABLECIMIENTO, FECHA_INGRESO, ESTADO, ACTIVO)
                       VALUES 
                       (:nombre, :apellido, :dni, :correo, :telefono, :id_establecimiento, :fecha_ingreso, :estado, :activo)";
        
        $insertStmt = $conn->prepare($insertQuery);
        $result = $insertStmt->execute([
            ':nombre' => trim($input['nombre']),
            ':apellido' => trim($input['apellido']),
            ':dni' => trim($input['dni']),
            ':correo' => !empty($input['email']) ? trim($input['email']) : null,
            ':telefono' => !empty($input['telefono']) ? trim($input['telefono']) : null,
            ':id_establecimiento' => !empty($input['id_establecimiento']) ? (int)$input['id_establecimiento'] : null,
            ':fecha_ingreso' => !empty($input['fecha_contratacion']) ? $input['fecha_contratacion'] : date('Y-m-d'),
            ':estado' => ($input['estado'] ?? 'Activo') === 'Inactivo' ? 'I' : 'A',
            ':activo' => ($input['activo'] ?? 'Si') === 'No' ? 'N' : 'S'
        ]);
        
        if ($result) {
            $newId = $conn->lastInsertId();
            jsonResponse([
                'success' => true,
                'message' => 'Empleado creado exitosamente',
                'data' => ['id' => (int)$newId]
            ], 201);
        } else {
            jsonResponse(['success' => false, 'message' => 'Error creando empleado'], 500);
        }
        
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
    }
}

/**
 * Actualizar empleado
 */
function updateEmployee($conn, $id) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            jsonResponse(['success' => false, 'message' => 'JSON inválido'], 400);
            return;
        }
        
        // Verificar que existe
        $checkQuery = "SELECT ID_EMPLEADO FROM empleado WHERE ID_EMPLEADO = :id";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $checkStmt->execute();
        
        if (!$checkStmt->fetch()) {
            jsonResponse(['success' => false, 'message' => 'Empleado no encontrado'], 404);
            return;
        }
        
        // Verificar DNI único
        $checkDniQuery = "SELECT ID_EMPLEADO FROM empleado WHERE DNI = :dni AND ID_EMPLEADO != :id";
        $checkDniStmt = $conn->prepare($checkDniQuery);
        $checkDniStmt->execute([':dni' => $input['dni'], ':id' => (int)$id]);
        
        if ($checkDniStmt->fetch()) {
            jsonResponse(['success' => false, 'message' => 'Ya existe otro empleado con este DNI'], 400);
            return;
        }
        
        // Actualizar
        $updateQuery = "UPDATE empleado SET
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
        
        $updateStmt = $conn->prepare($updateQuery);
        $result = $updateStmt->execute([
            ':id' => (int)$id,
            ':nombre' => trim($input['nombre']),
            ':apellido' => trim($input['apellido']),
            ':dni' => trim($input['dni']),
            ':correo' => !empty($input['email']) ? trim($input['email']) : null,
            ':telefono' => !empty($input['telefono']) ? trim($input['telefono']) : null,
            ':id_establecimiento' => !empty($input['id_establecimiento']) ? (int)$input['id_establecimiento'] : null,
            ':fecha_ingreso' => $input['fecha_contratacion'],
            ':estado' => $input['estado'] === 'Inactivo' ? 'I' : 'A',
            ':activo' => $input['activo'] === 'No' ? 'N' : 'S'
        ]);
        
        if ($result) {
            jsonResponse(['success' => true, 'message' => 'Empleado actualizado exitosamente']);
        } else {
            jsonResponse(['success' => false, 'message' => 'Error actualizando empleado'], 500);
        }
        
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
    }
}

/**
 * Eliminar empleado
 */
function deleteEmployee($conn, $id) {
    try {
        $query = "UPDATE empleado SET ESTADO = 'I', ACTIVO = 'N' WHERE ID_EMPLEADO = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            jsonResponse(['success' => true, 'message' => 'Empleado eliminado exitosamente']);
        } else {
            jsonResponse(['success' => false, 'message' => 'Error eliminando empleado'], 500);
        }
        
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
    }
}
?>