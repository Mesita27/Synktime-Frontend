<?php
// Parámetros de conexión
$host = 'localhost';
$dbname = 'synktime';
$user = 'root';
$password = '';

// Crear conexión usando MySQLi
$conn = new mysqli($host, $user, $password, $dbname);

// Verificar errores
if ($conn->connect_error) {
    die("Error de conexión a la base de datos: " . $conn->connect_error);
}

// Función auxiliar para consultas que devuelven un solo resultado
function executeQuerySingle($sql, $params = []) {
    global $conn;

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Error en la preparación de la consulta: " . $conn->error);
    }

    if (!empty($params)) {
        $types = str_repeat('s', count($params)); // Solo cadenas para simplificar
        $stmt->bind_param($types, ...array_values($params));
    }

    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc(); // Devuelve solo una fila
}

// Función auxiliar para ejecutar consultas sin retorno
function executeQuery($sql, $params = []) {
    global $conn;

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Error en la preparación de la consulta: " . $conn->error);
    }

    if (!empty($params)) {
        $types = str_repeat('s', count($params)); // Solo cadenas para simplificar
        $stmt->bind_param($types, ...array_values($params));
    }

    return $stmt->execute();
}
?>
