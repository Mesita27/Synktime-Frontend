<?php
require_once '../config/database.php';
require_once '../dashboard-controller.php';

// Verificar si hay un ID de establecimiento
$establecimientoId = isset($_GET['establecimiento_id']) ? (int)$_GET['establecimiento_id'] : null;

// Fecha fija para pruebas o usar fecha actual
$fecha = '2025-06-15';

// Verificar que tenemos un ID de establecimiento válido
if (!$establecimientoId) {
    echo json_encode([
        'success' => false,
        'error' => 'No se proporcionó un ID de establecimiento válido'
    ]);
    exit;
}

try {
    // Obtener estadísticas
    $estadisticas = getEstadisticasAsistencia($establecimientoId, $fecha);
    
    // Obtener sede del establecimiento
    $stmt = $conn->prepare("
        SELECT s.ID_SEDE, s.ID_EMPRESA 
        FROM ESTABLECIMIENTO e 
        JOIN SEDE s ON e.ID_SEDE = s.ID_SEDE 
        WHERE e.ID_ESTABLECIMIENTO = :establecimientoId
    ");
    $stmt->bindParam(':establecimientoId', $establecimientoId, PDO::PARAM_INT);
    $stmt->execute();
    $sedeInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$sedeInfo) {
        throw new Exception("No se pudo encontrar la sede del establecimiento");
    }
    
    // Obtener datos para gráficos específicos del establecimiento
    $asistenciasPorHora = getAsistenciasPorHoraEstablecimiento($establecimientoId, $fecha);
    $distribucionAsistencias = getDistribucionAsistenciasEstablecimiento($establecimientoId, $fecha);
    
    // Obtener actividad reciente del establecimiento
    $actividadReciente = getActividadRecienteEstablecimiento($establecimientoId, $fecha);
    
    // Devolver datos en formato JSON
    echo json_encode([
        'success' => true,
        'estadisticas' => $estadisticas,
        'asistenciasPorHora' => $asistenciasPorHora,
        'distribucionAsistencias' => $distribucionAsistencias,
        'actividadReciente' => $actividadReciente
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

/**
 * Obtiene datos para el gráfico de asistencias por hora de un establecimiento específico
 */
function getAsistenciasPorHoraEstablecimiento($establecimientoId, $fecha) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("
            SELECT 
                SUBSTRING(a.HORA, 1, 2) as hora,
                COUNT(*) as cantidad
            FROM ASISTENCIA a
            JOIN EMPLEADO e ON a.ID_EMPLEADO = e.ID_EMPLEADO
            WHERE e.ID_ESTABLECIMIENTO = :establecimientoId
            AND a.FECHA = :fecha
            AND a.TIPO = 'ENTRADA'
            AND e.ACTIVO = 'S'
            GROUP BY SUBSTRING(a.HORA, 1, 2)
            ORDER BY hora
        ");
        
        $stmt->bindParam(':establecimientoId', $establecimientoId, PDO::PARAM_INT);
        $stmt->bindParam(':fecha', $fecha, PDO::PARAM_STR);
        $stmt->execute();
        
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Preparar el formato para el gráfico
        $categories = [];
        $data = [];
        
        foreach ($resultado as $fila) {
            $categories[] = $fila['hora'] . ':00';
            $data[] = (int)$fila['cantidad'];
        }
        
        return [
            'categories' => $categories,
            'data' => $data
        ];
        
    } catch (PDOException $e) {
        error_log("Error al obtener asistencias por hora: " . $e->getMessage());
        return [
            'categories' => [],
            'data' => []
        ];
    }
}

/**
 * Obtiene datos para el gráfico de distribución de asistencias de un establecimiento específico
 */
function getDistribucionAsistenciasEstablecimiento($establecimientoId, $fecha) {
    global $conn;
    
    try {
        // Consulta para obtener el total de empleados, asistencias a tiempo y tardanzas
        $stmt = $conn->prepare("
            SELECT 
                COUNT(DISTINCT e.ID_EMPLEADO) as total_empleados,
                SUM(CASE WHEN a.TARDANZA = 'N' AND a.TIPO = 'ENTRADA' THEN 1 ELSE 0 END) as llegadas_tiempo,
                SUM(CASE WHEN a.TARDANZA = 'S' AND a.TIPO = 'ENTRADA' THEN 1 ELSE 0 END) as llegadas_tarde
            FROM EMPLEADO e
            LEFT JOIN ASISTENCIA a ON e.ID_EMPLEADO = a.ID_EMPLEADO AND a.FECHA = :fecha
            WHERE e.ID_ESTABLECIMIENTO = :establecimientoId
            AND e.ESTADO = 'A'
            AND e.ACTIVO = 'S'
        ");
        
        $stmt->bindParam(':establecimientoId', $establecimientoId, PDO::PARAM_INT);
        $stmt->bindParam(':fecha', $fecha, PDO::PARAM_STR);
        $stmt->execute();
        
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Consulta para calcular las faltas (empleados sin registro de entrada en la fecha)
        $stmt = $conn->prepare("
            SELECT COUNT(e.ID_EMPLEADO) as faltas
            FROM EMPLEADO e
            LEFT JOIN ASISTENCIA a ON e.ID_EMPLEADO = a.ID_EMPLEADO 
                AND a.FECHA = :fecha 
                AND a.TIPO = 'ENTRADA'
            WHERE e.ID_ESTABLECIMIENTO = :establecimientoId 
            AND e.ESTADO = 'A' 
            AND e.ACTIVO = 'S'
            AND a.ID_ASISTENCIA IS NULL
        ");
        
        $stmt->bindParam(':establecimientoId', $establecimientoId, PDO::PARAM_INT);
        $stmt->bindParam(':fecha', $fecha, PDO::PARAM_STR);
        $stmt->execute();
        $faltasResult = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return [
            'series' => [
                (int)$resultado['llegadas_tiempo'],
                (int)$resultado['llegadas_tarde'],
                (int)$faltasResult['faltas']
            ]
        ];
        
    } catch (PDOException $e) {
        error_log("Error al obtener distribución de asistencias: " . $e->getMessage());
        return [
            'series' => [0, 0, 0]
        ];
    }
}

/**
 * Obtiene la actividad reciente de un establecimiento específico
 */
function getActividadRecienteEstablecimiento($establecimientoId, $fecha, $limit = 10) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("
            SELECT 
                a.ID_ASISTENCIA,
                e.ID_EMPLEADO,
                e.NOMBRE,
                e.APELLIDO,
                a.HORA,
                a.TIPO,
                a.TARDANZA,
                a.OBSERVACION,
                s.NOMBRE as SEDE_NOMBRE
            FROM ASISTENCIA a
            JOIN EMPLEADO e ON a.ID_EMPLEADO = e.ID_EMPLEADO
            JOIN ESTABLECIMIENTO est ON e.ID_ESTABLECIMIENTO = est.ID_ESTABLECIMIENTO
            JOIN SEDE s ON est.ID_SEDE = s.ID_SEDE
            WHERE e.ID_ESTABLECIMIENTO = :establecimientoId
            AND a.FECHA = :fecha
            AND e.ACTIVO = 'S'
            ORDER BY a.ID_ASISTENCIA DESC
            LIMIT :limite
        ");
        
        $stmt->bindParam(':establecimientoId', $establecimientoId, PDO::PARAM_INT);
        $stmt->bindParam(':fecha', $fecha, PDO::PARAM_STR);
        $stmt->bindParam(':limite', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        error_log("Error al obtener actividad reciente: " . $e->getMessage());
        return [];
    }
}