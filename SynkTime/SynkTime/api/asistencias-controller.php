<?php
require_once 'config/database.php';

/**
 * Registra una asistencia verificando si es tardanza
 * 
 * @param int $idEmpleado ID del empleado
 * @param string $fecha Fecha en formato Y-m-d
 * @param string $hora Hora en formato HH:MM
 * @param string $tipo 'ENTRADA' o 'SALIDA'
 * @param string $observacion Observación opcional
 * @param bool $registroManual Indica si el registro es manual
 * @return bool True si se registró correctamente, false si no
 */
function registrarAsistencia($idEmpleado, $fecha, $hora, $tipo, $observacion = null, $registroManual = false) {
    global $conn;
    
    try {
        // Si es entrada, verificar si es tardanza
        $tardanza = 'N';
        if ($tipo === 'ENTRADA') {
            $tardanza = esTardanza($idEmpleado, $hora, $fecha) ? 'S' : 'N';
        }
        
        $stmt = $conn->prepare("
            INSERT INTO ASISTENCIA 
            (ID_EMPLEADO, FECHA, TIPO, HORA, TARDANZA, OBSERVACION, REGISTRO_MANUAL) 
            VALUES 
            (:idEmpleado, :fecha, :tipo, :hora, :tardanza, :observacion, :registroManual)
        ");
        
        $registroManualChar = $registroManual ? 'S' : 'N';
        
        $stmt->bindParam(':idEmpleado', $idEmpleado, PDO::PARAM_INT);
        $stmt->bindParam(':fecha', $fecha, PDO::PARAM_STR);
        $stmt->bindParam(':tipo', $tipo, PDO::PARAM_STR);
        $stmt->bindParam(':hora', $hora, PDO::PARAM_STR);
        $stmt->bindParam(':tardanza', $tardanza, PDO::PARAM_STR);
        $stmt->bindParam(':observacion', $observacion, PDO::PARAM_STR);
        $stmt->bindParam(':registroManual', $registroManualChar, PDO::PARAM_STR);
        
        return $stmt->execute();
        
    } catch (PDOException $e) {
        error_log("Error al registrar asistencia: " . $e->getMessage());
        return false;
    }
}

/**
 * Determina si una llegada es tardía según el horario del empleado
 * 
 * @param int $idEmpleado ID del empleado
 * @param string $hora Hora en formato HH:MM
 * @param string $fecha Fecha en formato Y-m-d
 * @return bool True si es tardanza, false si no
 */
function esTardanza($idEmpleado, $hora, $fecha) {
    $horario = getHorarioEmpleado($idEmpleado, $fecha);
    if (!$horario) return false;
    
    // Convertir a minutos para comparación numérica
    $horaEntradaPartes = explode(':', $horario['HORA_ENTRADA']);
    $minutosHoraEntrada = (int)$horaEntradaPartes[0] * 60 + (int)$horaEntradaPartes[1];
    
    $horaLlegadaPartes = explode(':', $hora);
    $minutosHoraLlegada = (int)$horaLlegadaPartes[0] * 60 + (int)$horaLlegadaPartes[1];
    
    // Considerar tolerancia
    $toleranciaMinutos = (int)$horario['TOLERANCIA'];
    
    return $minutosHoraLlegada > ($minutosHoraEntrada + $toleranciaMinutos);
}

/**
 * Obtiene el horario de un empleado para una fecha específica
 * 
 * @param int $idEmpleado ID del empleado
 * @param string $fecha Fecha en formato Y-m-d
 * @return array|null Datos del horario o null si no se encuentra
 */
function getHorarioEmpleado($idEmpleado, $fecha) {
    global $conn;
    
    try {
        $stmt = $conn->prepare("
            SELECT h.*
            FROM EMPLEADO_HORARIO eh
            JOIN HORARIO h ON eh.ID_HORARIO = h.ID_HORARIO
            WHERE eh.ID_EMPLEADO = :idEmpleado
            AND eh.FECHA_DESDE <= :fecha
            AND (eh.FECHA_HASTA IS NULL OR eh.FECHA_HASTA >= :fecha)
            ORDER BY eh.FECHA_DESDE DESC
            LIMIT 1
        ");
        
        $stmt->bindParam(':idEmpleado', $idEmpleado, PDO::PARAM_INT);
        $stmt->bindParam(':fecha', $fecha, PDO::PARAM_STR);
        $stmt->execute();
        
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $resultado ? $resultado : null;
        
    } catch (PDOException $e) {
        error_log("Error al obtener horario del empleado: " . $e->getMessage());
        return null;
    }
}