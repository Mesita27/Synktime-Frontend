<?php
/**
 * Functions Library
 * SynkTime - Sistema de Control de Asistencia
 * 
 * Colección de funciones de utilidad para el sistema
 */

// Asegurar que la sesión está iniciada
function ensureSession() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

// Verificar que el usuario tiene acceso a la página actual
function checkAccess($required_roles = []) {
    ensureSession();
    
    // Si no hay sesión de usuario, redirigir al login
    if (!isset($_SESSION['user_id'])) {
        redirectToLogin();
        exit();
    }
    
    // Si hay roles requeridos, verificar que el usuario tiene uno de ellos
    if (!empty($required_roles) && !in_array($_SESSION['rol_id'], $required_roles)) {
        setFlashMessage('No tiene permisos para acceder a esta sección.', 'danger');
        header("Location: " . getBasePath() . "index.php");
        exit();
    }
}

// Obtener la ruta base para redirecciones
function getBasePath() {
    $root_path = "";
    if (strpos($_SERVER['PHP_SELF'], 'modules/') !== false) {
        // Estamos en un subdirectorio de módulos (2 niveles)
        $root_path = "../../";
    } elseif (strpos($_SERVER['PHP_SELF'], 'admin/') !== false) {
        // Estamos en el directorio de administración (1 nivel)
        $root_path = "../";
    }
    return $root_path;
}

// Redirigir al login
function redirectToLogin() {
    header("Location: " . getBasePath() . "login.php");
    exit();
}

// Establecer un mensaje flash para mostrar en la siguiente página
function setFlashMessage($message, $type = 'success') {
    ensureSession();
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

// Mostrar mensaje flash si existe
function displayFlashMessage() {
    ensureSession();
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'info';
        
        echo '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">';
        echo $message;
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
        
        // Limpiar el mensaje flash después de mostrarlo
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
    }
}

// Sanear entradas para prevenir inyecciones
function sanitizeInput($input) {
    if (is_array($input)) {
        foreach ($input as $key => $value) {
            $input[$key] = sanitizeInput($value);
        }
    } else {
        $input = trim($input);
        $input = stripslashes($input);
        $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }
    return $input;
}

// Validar entrada de formulario
function validateInput($input, $field_name, $rules = []) {
    $errors = [];
    
    // Validar si el campo es requerido
    if (isset($rules['required']) && $rules['required'] && empty($input)) {
        $errors[] = "El campo $field_name es obligatorio.";
    }
    
    // Si está vacío y no es requerido, no validar más
    if (empty($input) && (!isset($rules['required']) || !$rules['required'])) {
        return $errors;
    }
    
    // Validar longitud mínima
    if (isset($rules['min_length']) && strlen($input) < $rules['min_length']) {
        $errors[] = "El campo $field_name debe tener al menos {$rules['min_length']} caracteres.";
    }
    
    // Validar longitud máxima
    if (isset($rules['max_length']) && strlen($input) > $rules['max_length']) {
        $errors[] = "El campo $field_name debe tener máximo {$rules['max_length']} caracteres.";
    }
    
    // Validar si es email
    if (isset($rules['email']) && $rules['email'] && !filter_var($input, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "El campo $field_name debe ser un email válido.";
    }
    
    // Validar si es número
    if (isset($rules['numeric']) && $rules['numeric'] && !is_numeric($input)) {
        $errors[] = "El campo $field_name debe ser un número.";
    }
    
    // Validar si es fecha
    if (isset($rules['date']) && $rules['date']) {
        $date = date_create_from_format($rules['date_format'] ?? 'Y-m-d', $input);
        if (!$date || date_format($date, $rules['date_format'] ?? 'Y-m-d') != $input) {
            $errors[] = "El campo $field_name debe ser una fecha válida.";
        }
    }
    
    // Validar si es hora
    if (isset($rules['time']) && $rules['time']) {
        $time = date_create_from_format($rules['time_format'] ?? 'H:i', $input);
        if (!$time || date_format($time, $rules['time_format'] ?? 'H:i') != $input) {
            $errors[] = "El campo $field_name debe ser una hora válida.";
        }
    }
    
    return $errors;
}

// Formatear fecha para mostrar
function formatDate($date, $format = 'd/m/Y') {
    return date($format, strtotime($date));
}

// Formatear hora para mostrar
function formatTime($time, $format = 'H:i') {
    return date($format, strtotime($time));
}

// Obtener lista de empresas
function getEmpresas($conn) {
    $sql = "SELECT id_empresa, nombre FROM empresas ORDER BY nombre";
    $result = $conn->query($sql);
    
    $empresas = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $empresas[] = $row;
        }
    }
    
    return $empresas;
}

// Obtener lista de negocios por empresa
function getNegociosByEmpresa($conn, $id_empresa) {
    $sql = "SELECT id_negocio, nombre FROM negocios WHERE id_empresa = ? ORDER BY nombre";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_empresa);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $negocios = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $negocios[] = $row;
        }
    }
    
    return $negocios;
}

// Obtener lista de sedes por negocio
function getSedesByNegocio($conn, $id_negocio) {
    $sql = "SELECT id_sede, nombre FROM sedes WHERE id_negocio = ? ORDER BY nombre";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_negocio);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $sedes = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $sedes[] = $row;
        }
    }
    
    return $sedes;
}

// Obtener lista de trabajadores por sede
function getTrabajadoresBySede($conn, $id_sede) {
    $sql = "SELECT id_trabajador, nombre, apellido, documento, estado FROM trabajadores WHERE id_sede = ? ORDER BY apellido, nombre";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_sede);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $trabajadores = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $trabajadores[] = $row;
        }
    }
    
    return $trabajadores;
}

// Obtener lista de horarios
function getHorarios($conn, $id_sede) {
    $sql = "SELECT id_horario, nombre, hora_entrada, hora_salida, dias_semana FROM horarios WHERE id_sede = ? ORDER BY nombre";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_sede);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $horarios = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $horarios[] = $row;
        }
    }
    
    return $horarios;
}

// Asignar horario a trabajador
function asignarHorario($conn, $id_trabajador, $id_horario) {
    // Primero verificar si ya existe la asignación
    $sql = "SELECT id FROM trabajador_horario WHERE id_trabajador = ? AND id_horario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id_trabajador, $id_horario);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return true; // Ya existe la asignación
    }
    
    // Si no existe, crear la asignación
    $sql = "INSERT INTO trabajador_horario (id_trabajador, id_horario) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id_trabajador, $id_horario);
    
    return $stmt->execute();
}

// Eliminar asignación de horario a trabajador
function eliminarAsignacionHorario($conn, $id_trabajador, $id_horario) {
    $sql = "DELETE FROM trabajador_horario WHERE id_trabajador = ? AND id_horario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id_trabajador, $id_horario);
    
    return $stmt->execute();
}

// Obtener horarios de un trabajador
function getHorariosByTrabajador($conn, $id_trabajador) {
    $sql = "SELECT h.id_horario, h.nombre, h.hora_entrada, h.hora_salida, h.dias_semana 
            FROM horarios h
            JOIN trabajador_horario th ON h.id_horario = th.id_horario
            WHERE th.id_trabajador = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_trabajador);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $horarios = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $horarios[] = $row;
        }
    }
    
    return $horarios;
}

// Registrar asistencia
function registrarAsistencia($conn, $id_trabajador, $tipo = 'entrada') {
    $fecha = date('Y-m-d');
    $hora = date('H:i:s');
    
    // Verificar si ya existe un registro para hoy
    $sql = "SELECT id_asistencia FROM asistencias 
            WHERE id_trabajador = ? AND fecha = ? AND tipo = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $id_trabajador, $fecha, $tipo);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Ya existe un registro, actualizar la hora
        $row = $result->fetch_assoc();
        $id_asistencia = $row['id_asistencia'];
        
        $sql = "UPDATE asistencias SET hora = ? WHERE id_asistencia = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $hora, $id_asistencia);
        
        return $stmt->execute();
    } else {
        // No existe, crear nuevo registro
        $sql = "INSERT INTO asistencias (id_trabajador, fecha, hora, tipo) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isss", $id_trabajador, $fecha, $hora, $tipo);
        
        return $stmt->execute();
    }
}

// Obtener asistencias por trabajador y rango de fechas
function getAsistenciasByTrabajador($conn, $id_trabajador, $fecha_inicio, $fecha_fin) {
    $sql = "SELECT a.id_asistencia, a.fecha, a.hora, a.tipo, 
                   t.nombre, t.apellido, 
                   h.hora_entrada, h.hora_salida 
            FROM asistencias a
            JOIN trabajadores t ON a.id_trabajador = t.id_trabajador
            LEFT JOIN trabajador_horario th ON t.id_trabajador = th.id_trabajador
            LEFT JOIN horarios h ON th.id_horario = h.id_horario
            WHERE a.id_trabajador = ? AND a.fecha BETWEEN ? AND ?
            ORDER BY a.fecha DESC, a.hora DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $id_trabajador, $fecha_inicio, $fecha_fin);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $asistencias = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Calcular estado (puntual, tardanza, ausencia)
            $row['estado'] = calcularEstadoAsistencia($row['hora'], $row['tipo'], $row['hora_entrada'], $row['hora_salida']);
            $asistencias[] = $row;
        }
    }
    
    return $asistencias;
}

// Obtener asistencias por sede y fecha
function getAsistenciasBySede($conn, $id_sede, $fecha) {
    $sql = "SELECT a.id_asistencia, a.fecha, a.hora, a.tipo, 
                   t.id_trabajador, t.nombre, t.apellido, 
                   h.hora_entrada, h.hora_salida 
            FROM asistencias a
            JOIN trabajadores t ON a.id_trabajador = t.id_trabajador
            LEFT JOIN trabajador_horario th ON t.id_trabajador = th.id_trabajador
            LEFT JOIN horarios h ON th.id_horario = h.id_horario
            WHERE t.id_sede = ? AND a.fecha = ?
            ORDER BY t.apellido, t.nombre, a.hora";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $id_sede, $fecha);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $asistencias = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Calcular estado (puntual, tardanza, ausencia)
            $row['estado'] = calcularEstadoAsistencia($row['hora'], $row['tipo'], $row['hora_entrada'], $row['hora_salida']);
            
            // Agrupar por trabajador
            $id_trabajador = $row['id_trabajador'];
            if (!isset($asistencias[$id_trabajador])) {
                $asistencias[$id_trabajador] = [
                    'id_trabajador' => $id_trabajador,
                    'nombre' => $row['nombre'],
                    'apellido' => $row['apellido'],
                    'hora_entrada' => $row['hora_entrada'],
                    'hora_salida' => $row['hora_salida'],
                    'registros' => []
                ];
            }
            
            $asistencias[$id_trabajador]['registros'][] = [
                'hora' => $row['hora'],
                'tipo' => $row['tipo'],
                'estado' => $row['estado']
            ];
        }
    }
    
    return $asistencias;
}

// Calcular estado de asistencia
function calcularEstadoAsistencia($hora_registro, $tipo_registro, $hora_entrada, $hora_salida) {
    if ($tipo_registro == 'entrada') {
        if (empty($hora_entrada)) {
            return 'sin_horario';
        }
        
        $tiempo_registro = strtotime($hora_registro);
        $tiempo_entrada = strtotime($hora_entrada);
        
        $diferencia_minutos = ($tiempo_registro - $tiempo_entrada) / 60;
        
        if ($diferencia_minutos <= 0) {
            return 'puntual';
        } elseif ($diferencia_minutos <= 15) {
            return 'tardanza_leve';
        } else {
            return 'tardanza_grave';
        }
    } elseif ($tipo_registro == 'salida') {
        if (empty($hora_salida)) {
            return 'sin_horario';
        }
        
        $tiempo_registro = strtotime($hora_registro);
        $tiempo_salida = strtotime($hora_salida);
        
        if ($tiempo_registro >= $tiempo_salida) {
            return 'completo';
        } else {
            return 'salida_anticipada';
        }
    }
    
    return 'desconocido';
}

// Obtener estadísticas de asistencia para el dashboard
function getEstadisticasAsistencia($conn, $id_sede, $fecha = null) {
    if ($fecha === null) {
        $fecha = date('Y-m-d');
    }
    
    // Obtener total de trabajadores
    $sql = "SELECT COUNT(*) as total FROM trabajadores WHERE id_sede = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_sede);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $total_trabajadores = $row['total'];
    
    // Contar asistencias del día
    $sql = "SELECT COUNT(DISTINCT a.id_trabajador) as total 
            FROM asistencias a 
            JOIN trabajadores t ON a.id_trabajador = t.id_trabajador 
            WHERE t.id_sede = ? AND a.fecha = ? AND a.tipo = 'entrada'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $id_sede, $fecha);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $total_asistencias = $row['total'];
    
    // Contar tardanzas
    $sql = "SELECT COUNT(DISTINCT a.id_trabajador) as total 
            FROM asistencias a 
            JOIN trabajadores t ON a.id_trabajador = t.id_trabajador 
            JOIN trabajador_horario th ON t.id_trabajador = th.id_trabajador
            JOIN horarios h ON th.id_horario = h.id_horario
            WHERE t.id_sede = ? AND a.fecha = ? AND a.tipo = 'entrada' 
            AND TIME(a.hora) > TIME(h.hora_entrada)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $id_sede, $fecha);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $total_tardanzas = $row['total'];
    
    // Calcular ausencias (trabajadores - asistencias)
    $total_ausencias = $total_trabajadores - $total_asistencias;
    
    // Calcular puntuales (asistencias - tardanzas)
    $total_puntuales = $total_asistencias - $total_tardanzas;
    
    return [
        'total_trabajadores' => $total_trabajadores,
        'total_asistencias' => $total_asistencias,
        'total_tardanzas' => $total_tardanzas,
        'total_ausencias' => $total_ausencias,
        'total_puntuales' => $total_puntuales,
        'porcentaje_asistencia' => $total_trabajadores > 0 ? round(($total_asistencias / $total_trabajadores) * 100, 2) : 0,
        'porcentaje_puntualidad' => $total_asistencias > 0 ? round(($total_puntuales / $total_asistencias) * 100, 2) : 0
    ];
}

// Obtener datos para gráficos de reportes semanales
function getReporteSemanal($conn, $id_sede, $fecha_inicio, $fecha_fin) {
    $sql = "SELECT DATE(a.fecha) as fecha, 
                   COUNT(DISTINCT a.id_trabajador) as total_asistencias,
                   (SELECT COUNT(*) FROM trabajadores WHERE id_sede = ?) as total_trabajadores,
                   COUNT(DISTINCT CASE WHEN TIME(a.hora) <= TIME(h.hora_entrada) THEN a.id_trabajador END) as total_puntuales,
                   COUNT(DISTINCT CASE WHEN TIME(a.hora) > TIME(h.hora_entrada) THEN a.id_trabajador END) as total_tardanzas
            FROM asistencias a
            JOIN trabajadores t ON a.id_trabajador = t.id_trabajador
            LEFT JOIN trabajador_horario th ON t.id_trabajador = th.id_trabajador
            LEFT JOIN horarios h ON th.id_horario = h.id_horario
            WHERE t.id_sede = ? AND a.fecha BETWEEN ? AND ? AND a.tipo = 'entrada'
            GROUP BY DATE(a.fecha)
            ORDER BY DATE(a.fecha)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiss", $id_sede, $id_sede, $fecha_inicio, $fecha_fin);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $reporte = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $row['total_ausencias'] = $row['total_trabajadores'] - $row['total_asistencias'];
            $reporte[] = $row;
        }
    }
    
    return $reporte;
}

// Obtener datos para gráficos de reportes mensuales
function getReporteMensual($conn, $id_sede, $mes, $año) {
    $fecha_inicio = "$año-$mes-01";
    $fecha_fin = date('Y-m-t', strtotime($fecha_inicio));
    
    $sql = "SELECT DATE(a.fecha) as fecha, 
                   COUNT(DISTINCT a.id_trabajador) as total_asistencias,
                   (SELECT COUNT(*) FROM trabajadores WHERE id_sede = ?) as total_trabajadores,
                   COUNT(DISTINCT CASE WHEN TIME(a.hora) <= TIME(h.hora_entrada) THEN a.id_trabajador END) as total_puntuales,
                   COUNT(DISTINCT CASE WHEN TIME(a.hora) > TIME(h.hora_entrada) THEN a.id_trabajador END) as total_tardanzas
            FROM asistencias a
            JOIN trabajadores t ON a.id_trabajador = t.id_trabajador
            LEFT JOIN trabajador_horario th ON t.id_trabajador = th.id_trabajador
            LEFT JOIN horarios h ON th.id_horario = h.id_horario
            WHERE t.id_sede = ? AND a.fecha BETWEEN ? AND ? AND a.tipo = 'entrada'
            GROUP BY DATE(a.fecha)
            ORDER BY DATE(a.fecha)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiss", $id_sede, $id_sede, $fecha_inicio, $fecha_fin);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $reporte = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $row['total_ausencias'] = $row['total_trabajadores'] - $row['total_asistencias'];
            $reporte[] = $row;
        }
    }
    
    // Calcular totales y promedios para el mes
    $total_dias = count($reporte);
    $totales = [
        'total_asistencias' => 0,
        'total_puntuales' => 0,
        'total_tardanzas' => 0,
        'total_ausencias' => 0
    ];
    
    foreach ($reporte as $dia) {
        $totales['total_asistencias'] += $dia['total_asistencias'];
        $totales['total_puntuales'] += $dia['total_puntuales'];
        $totales['total_tardanzas'] += $dia['total_tardanzas'];
        $totales['total_ausencias'] += $dia['total_ausencias'];
    }
    
    $promedios = [];
    if ($total_dias > 0) {
        $promedios = [
            'promedio_asistencias' => round($totales['total_asistencias'] / $total_dias, 2),
            'promedio_puntuales' => round($totales['total_puntuales'] / $total_dias, 2),
            'promedio_tardanzas' => round($totales['total_tardanzas'] / $total_dias, 2),
            'promedio_ausencias' => round($totales['total_ausencias'] / $total_dias, 2)
        ];
    }
    
    return [
        'datos_diarios' => $reporte,
        'totales' => $totales,
        'promedios' => $promedios
    ];
}

// Obtener datos para gráficos de reportes anuales
function getReporteAnual($conn, $id_sede, $año) {
    $sql = "SELECT MONTH(a.fecha) as mes, 
                   COUNT(DISTINCT a.id_trabajador) as total_asistencias,
                   (SELECT COUNT(*) FROM trabajadores WHERE id_sede = ?) as total_trabajadores,
                   COUNT(DISTINCT CASE WHEN TIME(a.hora) <= TIME(h.hora_entrada) THEN a.id_trabajador END) as total_puntuales,
                   COUNT(DISTINCT CASE WHEN TIME(a.hora) > TIME(h.hora_entrada) THEN a.id_trabajador END) as total_tardanzas
            FROM asistencias a
            JOIN trabajadores t ON a.id_trabajador = t.id_trabajador
            LEFT JOIN trabajador_horario th ON t.id_trabajador = th.id_trabajador
            LEFT JOIN horarios h ON th.id_horario = h.id_horario
            WHERE t.id_sede = ? AND YEAR(a.fecha) = ? AND a.tipo = 'entrada'
            GROUP BY MONTH(a.fecha)
            ORDER BY MONTH(a.fecha)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $id_sede, $id_sede, $año);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $reporte = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $row['nombre_mes'] = date('F', mktime(0, 0, 0, $row['mes'], 10));
            $row['total_ausencias'] = $row['total_trabajadores'] - $row['total_asistencias'];
            $reporte[$row['mes']] = $row;
        }
    }
    
    // Completar todos los meses del año
    $reporte_completo = [];
    for ($i = 1; $i <= 12; $i++) {
        if (isset($reporte[$i])) {
            $reporte_completo[$i] = $reporte[$i];
        } else {
            $reporte_completo[$i] = [
                'mes' => $i,
                'nombre_mes' => date('F', mktime(0, 0, 0, $i, 10)),
                'total_asistencias' => 0,
                'total_trabajadores' => 0, // Deberías obtener el total de trabajadores para ese mes
                'total_puntuales' => 0,
                'total_tardanzas' => 0,
                'total_ausencias' => 0
            ];
        }
    }
    
    return $reporte_completo;
}

// Justificar una ausencia
function justificarAsistencia($conn, $id_trabajador, $fecha, $motivo) {
    // Verificar si existe una asistencia para ese día
    $sql = "SELECT id_asistencia FROM asistencias 
            WHERE id_trabajador = ? AND fecha = ? AND tipo = 'entrada'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $id_trabajador, $fecha);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Existe registro, actualizar justificación
        $row = $result->fetch_assoc();
        $id_asistencia = $row['id_asistencia'];
        
        $sql = "UPDATE asistencias SET justificacion = ? WHERE id_asistencia = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $motivo, $id_asistencia);
        
        return $stmt->execute();
    } else {
        // No existe registro, crear uno con justificación
        $sql = "INSERT INTO asistencias (id_trabajador, fecha, tipo, justificacion) 
                VALUES (?, ?, 'entrada', ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iss", $id_trabajador, $fecha, $motivo);
        
        return $stmt->execute();
    }
}

// Obtener estadísticas para el dashboard por trabajador
function getEstadisticasByTrabajador($conn, $id_trabajador, $mes, $año) {
    $fecha_inicio = "$año-$mes-01";
    $fecha_fin = date('Y-m-t', strtotime($fecha_inicio));
    
    // Contar total de días
    $total_dias = date('t', strtotime($fecha_inicio));
    
    // Contar asistencias
    $sql = "SELECT 
                COUNT(DISTINCT fecha) as total_asistencias,
                COUNT(DISTINCT CASE WHEN justificacion IS NOT NULL THEN fecha END) as total_justificadas,
                SUM(CASE 
                    WHEN tipo = 'entrada' AND hora IS NOT NULL AND 
                         EXISTS (SELECT 1 FROM trabajador_horario th 
                                JOIN horarios h ON th.id_horario = h.id_horario 
                                WHERE th.id_trabajador = ? AND TIME(a.hora) > TIME(h.hora_entrada))
                    THEN 1 ELSE 0 END) as total_tardanzas
            FROM asistencias a
            WHERE id_trabajador = ? AND fecha BETWEEN ? AND ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiss", $id_trabajador, $id_trabajador, $fecha_inicio, $fecha_fin);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    // Calcular ausencias (días laborables - asistencias)
    $dias_laborables = getDiasLaborablesMes($conn, $id_trabajador, $mes, $año);
    $total_ausencias = $dias_laborables - $row['total_asistencias'];
    
    return [
        'total_dias' => $total_dias,
        'dias_laborables' => $dias_laborables,
        'total_asistencias' => $row['total_asistencias'],
        'total_tardanzas' => $row['total_tardanzas'],
        'total_justificadas' => $row['total_justificadas'],
        'total_ausencias' => $total_ausencias,
        'porcentaje_asistencia' => $dias_laborables > 0 ? round(($row['total_asistencias'] / $dias_laborables) * 100, 2) : 0,
        'porcentaje_puntualidad' => $row['total_asistencias'] > 0 ? round((($row['total_asistencias'] - $row['total_tardanzas']) / $row['total_asistencias']) * 100, 2) : 0
    ];
}

// Obtener días laborables según horarios asignados
function getDiasLaborablesMes($conn, $id_trabajador, $mes, $año) {
    // Obtener horarios del trabajador
    $sql = "SELECT h.dias_semana 
            FROM trabajador_horario th 
            JOIN horarios h ON th.id_horario = h.id_horario 
            WHERE th.id_trabajador = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_trabajador);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $dias_semana = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // dias_semana almacena los días de la semana en formato: 1,2,3,4,5
            $dias_horario = explode(',', $row['dias_semana']);
            $dias_semana = array_merge($dias_semana, $dias_horario);
        }
    }
    
    // Si no tiene horarios asignados, considerar todos los días de lunes a viernes
    if (empty($dias_semana)) {
        $dias_semana = [1, 2, 3, 4, 5]; // Lunes a viernes por defecto
    } else {
        $dias_semana = array_unique($dias_semana); // Eliminar duplicados
    }
    
    // Contar días laborables en el mes
    $fecha_inicio = "$año-$mes-01";
    $fecha_fin = date('Y-m-t', strtotime($fecha_inicio));
    
    $dias_laborables = 0;
    $fecha_actual = new DateTime($fecha_inicio);
    $fecha_final = new DateTime($fecha_fin);
    
    while ($fecha_actual <= $fecha_final) {
        $dia_semana = $fecha_actual->format('N'); // 1 (lunes) a 7 (domingo)
        if (in_array($dia_semana, $dias_semana)) {
            $dias_laborables++;
        }
        $fecha_actual->modify('+1 day');
    }
    
    return $dias_laborables;
}

// Obtener información del trabajador
function getTrabajador($conn, $id_trabajador) {
    $sql = "SELECT t.*, s.nombre as nombre_sede, n.nombre as nombre_negocio, e.nombre as nombre_empresa
            FROM trabajadores t
            JOIN sedes s ON t.id_sede = s.id_sede
            JOIN negocios n ON s.id_negocio = n.id_negocio
            JOIN empresas e ON n.id_empresa = e.id_empresa
            WHERE t.id_trabajador = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_trabajador);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

// Verificar si un usuario tiene permisos sobre una sede
function checkPermisoSede($conn, $id_usuario, $id_sede) {
    $sql = "SELECT u.id_rol, u.id_empresa, n.id_negocio
            FROM usuarios u
            LEFT JOIN sedes s ON s.id_sede = ?
            LEFT JOIN negocios n ON s.id_negocio = n.id_negocio
            WHERE u.id_usuario = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id_sede, $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Si es administrador general, tiene acceso a todo
        if ($row['id_rol'] == 1) {
            return true;
        }
        
        // Si es administrador de empresa, verificar que la sede pertenece a su empresa
        if ($row['id_rol'] == 2) {
            $sql = "SELECT 1 FROM sedes s
                    JOIN negocios n ON s.id_negocio = n.id_negocio
                    WHERE s.id_sede = ? AND n.id_empresa = ?";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $id_sede, $row['id_empresa']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            return ($result && $result->num_rows > 0);
        }
        
        // Si es supervisor, verificar que la sede está asignada directamente
        if ($row['id_rol'] == 3) {
            $sql = "SELECT 1 FROM usuario_sede 
                    WHERE id_usuario = ? AND id_sede = ?";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $id_usuario, $id_sede);
            $stmt->execute();
            $result = $stmt->get_result();
            
            return ($result && $result->num_rows > 0);
        }
    }
    
    return false;
}

// Obtener sedes asignadas a un usuario
function getSedesByUsuario($conn, $id_usuario) {
    $sql = "SELECT u.id_rol, u.id_empresa FROM usuarios u WHERE u.id_usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    $sedes = [];
    
    // Si es administrador general, tiene acceso a todas las sedes
    if ($row['id_rol'] == 1) {
        $sql = "SELECT s.* FROM sedes s ORDER BY s.nombre";
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            while ($sede = $result->fetch_assoc()) {
                $sedes[] = $sede;
            }
        }
    }
    
    // Si es administrador de empresa, tiene acceso a las sedes de su empresa
    elseif ($row['id_rol'] == 2) {
        $sql = "SELECT s.* FROM sedes s
                JOIN negocios n ON s.id_negocio = n.id_negocio
                WHERE n.id_empresa = ?
                ORDER BY s.nombre";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $row['id_empresa']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            while ($sede = $result->fetch_assoc()) {
                $sedes[] = $sede;
            }
        }
    }
    
    // Si es supervisor, tiene acceso solo a las sedes asignadas
    elseif ($row['id_rol'] == 3) {
        $sql = "SELECT s.* FROM sedes s
                JOIN usuario_sede us ON s.id_sede = us.id_sede
                WHERE us.id_usuario = ?
                ORDER BY s.nombre";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            while ($sede = $result->fetch_assoc()) {
                $sedes[] = $sede;
            }
        }
    }
    
    return $sedes;
}

// Obtener información de la sede
function getSede($conn, $id_sede) {
    $sql = "SELECT s.*, n.nombre as nombre_negocio, e.nombre as nombre_empresa
            FROM sedes s
            JOIN negocios n ON s.id_negocio = n.id_negocio
            JOIN empresas e ON n.id_empresa = e.id_empresa
            WHERE s.id_sede = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_sede);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

// Función para generar contraseña aleatoria
function generateRandomPassword($length = 8) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $password = '';
    $max = strlen($chars) - 1;
    
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, $max)];
    }
    
    return $password;
}

// Enviar correo de notificación
function sendEmail($to, $subject, $message) {
    // Esta es una implementación básica. En producción, deberías usar PHPMailer o similar
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: SynkTime <noreply@synktime.com>" . "\r\n";
    
    return mail($to, $subject, $message, $headers);
}

// Generar token único para restablecer contraseña
function generateResetToken($conn, $id_usuario) {
    $token = bin2hex(random_bytes(32));
    $expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));
    
    $sql = "UPDATE usuarios SET reset_token = ?, reset_expiry = ? WHERE id_usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $token, $expiry, $id_usuario);
    $stmt->execute();
    
    return $token;
}

// Verificar token de restablecimiento
function verifyResetToken($conn, $token) {
    $sql = "SELECT id_usuario FROM usuarios 
            WHERE reset_token = ? AND reset_expiry > NOW()";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['id_usuario'];
    }
    
    return false;
}

// Actualizar contraseña
function updatePassword($conn, $id_usuario, $new_password) {
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    
    $sql = "UPDATE usuarios SET 
            password = ?, 
            reset_token = NULL, 
            reset_expiry = NULL 
            WHERE id_usuario = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $hashed_password, $id_usuario);
    
    return $stmt->execute();
}

// Registrar actividad del usuario
function logActivity($conn, $id_usuario, $accion, $detalle = '') {
    $fecha = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'];
    
    $sql = "INSERT INTO log_actividad (id_usuario, fecha, accion, detalle, ip) 
            VALUES (?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issss", $id_usuario, $fecha, $accion, $detalle, $ip);
    
    return $stmt->execute();
}

// Verificar si es día feriado
function esFeriado($conn, $fecha, $id_empresa) {
    $sql = "SELECT 1 FROM feriados 
            WHERE fecha = ? AND (id_empresa = ? OR id_empresa IS NULL)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $fecha, $id_empresa);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return ($result && $result->num_rows > 0);
}

// Obtener nombre del día de la semana
function getNombreDiaSemana($numero_dia) {
    $dias = [
        1 => 'Lunes',
        2 => 'Martes',
        3 => 'Miércoles',
        4 => 'Jueves',
        5 => 'Viernes',
        6 => 'Sábado',
        7 => 'Domingo'
    ];
    
    return $dias[$numero_dia] ?? '';
}

// Obtener nombre del mes
function getNombreMes($numero_mes) {
    $meses = [
        1 => 'Enero',
        2 => 'Febrero',
        3 => 'Marzo',
        4 => 'Abril',
        5 => 'Mayo',
        6 => 'Junio',
        7 => 'Julio',
        8 => 'Agosto',
        9 => 'Septiembre',
        10 => 'Octubre',
        11 => 'Noviembre',
        12 => 'Diciembre'
    ];
    
    return $meses[$numero_mes] ?? '';
}

// Obtener estados de asistencia para mostrar
function getEstadosAsistencia() {
    return [
        'puntual' => [
            'texto' => 'Puntual',
            'clase' => 'success',
            'icono' => 'check-circle'
        ],
        'tardanza_leve' => [
            'texto' => 'Tardanza Leve',
            'clase' => 'warning',
            'icono' => 'exclamation-triangle'
        ],
        'tardanza_grave' => [
            'texto' => 'Tardanza Grave',
            'clase' => 'danger',
            'icono' => 'times-circle'
        ],
        'completo' => [
            'texto' => 'Completo',
            'clase' => 'success',
            'icono' => 'check-circle'
        ],
        'salida_anticipada' => [
            'texto' => 'Salida Anticipada',
            'clase' => 'warning',
            'icono' => 'exclamation-triangle'
        ],
        'sin_horario' => [
            'texto' => 'Sin Horario Asignado',
            'clase' => 'info',
            'icono' => 'info-circle'
        ],
        'ausencia' => [
            'texto' => 'Ausencia',
            'clase' => 'danger',
            'icono' => 'times-circle'
        ],
        'justificada' => [
            'texto' => 'Justificada',
            'clase' => 'secondary',
            'icono' => 'file-alt'
        ],
        'desconocido' => [
            'texto' => 'Desconocido',
            'clase' => 'dark',
            'icono' => 'question-circle'
        ]
    ];
}