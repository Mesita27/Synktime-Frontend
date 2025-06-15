<?php
require_once '../auth/session.php';
require_once '../config/database.php';

// Verificar autenticación
requireAuth();

// Establecer encabezados para JSON
header('Content-Type: application/json');

// Fecha fija para pruebas - puedes cambiarla a date('Y-m-d') para fecha actual
$fecha = '2025-06-15';

// Verificar que se recibió el ID de empresa
if (!isset($_GET['empresa_id'])) {
    echo json_encode([
        'success' => false,
        'error' => 'ID de empresa no proporcionado'
    ]);
    exit;
}

$empresaId = (int)$_GET['empresa_id'];

// Verificar que el ID de empresa sea válido
if ($empresaId <= 0) {
    echo json_encode([
        'success' => false,
        'error' => 'ID de empresa inválido'
    ]);
    exit;
}

try {
    // Usamos la conexión global definida en config/database.php
    global $conn;
    
    // Consulta para obtener las sedes con más información
    $stmt = $conn->prepare("
        SELECT 
            s.ID_SEDE, 
            s.NOMBRE, 
            s.DIRECCION,
            COUNT(DISTINCT e.ID_ESTABLECIMIENTO) AS num_establecimientos,
            COUNT(DISTINCT em.ID_EMPLEADO) AS num_empleados
        FROM SEDE s
        LEFT JOIN ESTABLECIMIENTO e ON s.ID_SEDE = e.ID_SEDE AND e.ESTADO = 'A'
        LEFT JOIN EMPLEADO em ON e.ID_ESTABLECIMIENTO = em.ID_ESTABLECIMIENTO AND em.ESTADO = 'A' AND em.ACTIVO = 'S'
        WHERE s.ID_EMPRESA = :empresaId 
        AND s.ESTADO = 'A'
        GROUP BY s.ID_SEDE, s.NOMBRE, s.DIRECCION
        ORDER BY s.NOMBRE
    ");
    
    $stmt->bindParam(':empresaId', $empresaId, PDO::PARAM_INT);
    $stmt->execute();
    
    $sedes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Si se solicitó un detalle con estadísticas
    if (isset($_GET['incluir_estadisticas']) && $_GET['incluir_estadisticas'] == '1') {
        // Para cada sede, obtenemos estadísticas de asistencias
        foreach ($sedes as &$sede) {
            // Consulta para obtener estadísticas básicas de la sede
            $stmtStats = $conn->prepare("
                SELECT 
                    COUNT(DISTINCT em.ID_EMPLEADO) as total_empleados,
                    SUM(CASE WHEN a.TARDANZA = 'N' AND a.TIPO = 'ENTRADA' THEN 1 ELSE 0 END) as llegadas_tiempo,
                    SUM(CASE WHEN a.TARDANZA = 'S' AND a.TIPO = 'ENTRADA' THEN 1 ELSE 0 END) as llegadas_tarde
                FROM EMPLEADO em
                JOIN ESTABLECIMIENTO est ON em.ID_ESTABLECIMIENTO = est.ID_ESTABLECIMIENTO
                LEFT JOIN ASISTENCIA a ON em.ID_EMPLEADO = a.ID_EMPLEADO AND a.FECHA = :fecha
                WHERE est.ID_SEDE = :sedeId 
                AND em.ESTADO = 'A' 
                AND em.ACTIVO = 'S'
            ");
            
            $stmtStats->bindParam(':sedeId', $sede['ID_SEDE'], PDO::PARAM_INT);
            $stmtStats->bindParam(':fecha', $fecha, PDO::PARAM_STR);
            $stmtStats->execute();
            
            $estadisticas = $stmtStats->fetch(PDO::FETCH_ASSOC);
            
            // Calcular faltas
            $stmtFaltas = $conn->prepare("
                SELECT COUNT(em.ID_EMPLEADO) as faltas
                FROM EMPLEADO em
                JOIN ESTABLECIMIENTO est ON em.ID_ESTABLECIMIENTO = est.ID_ESTABLECIMIENTO
                LEFT JOIN ASISTENCIA a ON em.ID_EMPLEADO = a.ID_EMPLEADO AND a.FECHA = :fecha AND a.TIPO = 'ENTRADA'
                WHERE est.ID_SEDE = :sedeId 
                AND em.ESTADO = 'A' 
                AND em.ACTIVO = 'S'
                AND a.ID_ASISTENCIA IS NULL
            ");
            
            $stmtFaltas->bindParam(':sedeId', $sede['ID_SEDE'], PDO::PARAM_INT);
            $stmtFaltas->bindParam(':fecha', $fecha, PDO::PARAM_STR);
            $stmtFaltas->execute();
            
            $faltasData = $stmtFaltas->fetch(PDO::FETCH_ASSOC);
            
            // Añadir estadísticas a la sede
            $sede['estadisticas'] = [
                'total_empleados' => (int)$estadisticas['total_empleados'],
                'llegadas_tiempo' => (int)$estadisticas['llegadas_tiempo'],
                'llegadas_tarde' => (int)$estadisticas['llegadas_tarde'],
                'faltas' => (int)$faltasData['faltas']
            ];
        }
    }
    
    // Registrar en el log la consulta de sedes (opcional)
    if (function_exists('logUserActivity')) {
        logUserActivity('CONSULTA', 'Consulta de sedes de empresa ID: ' . $empresaId);
    }
    
    echo json_encode([
        'success' => true, 
        'sedes' => $sedes,
        'fecha_consulta' => $fecha,
        'timestamp' => date('Y-m-d H:i:s'),
        'usuario' => $_SESSION['username'] ?? 'Mesita27'
    ]);
    
} catch (PDOException $e) {
    error_log("Error obteniendo sedes: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'error' => 'Error al obtener sedes',
        'debug' => ENVIRONMENT === 'development' ? $e->getMessage() : null
    ]);
}
?>