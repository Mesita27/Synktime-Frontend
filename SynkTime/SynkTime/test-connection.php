<?php
// test_connection.php - ARCHIVO DE PRUEBA
require_once 'config/database.php';

try {
    // Probar la conexión
    $testQuery = "SELECT COUNT(*) as total FROM empleado";
    $stmt = $conn->prepare($testQuery);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "✅ Conexión exitosa<br>";
    echo "📊 Total empleados en BD: " . $result['total'] . "<br>";
    
    // Probar query completa
    $fullQuery = "SELECT 
                    e.ID_EMPLEADO,
                    e.NOMBRE,
                    e.APELLIDO,
                    e.DNI,
                    e.CORREO,
                    CONCAT('EMP', LPAD(e.ID_EMPLEADO, 3, '0')) as codigo
                  FROM empleado e 
                  LIMIT 5";
    
    $stmt2 = $conn->prepare($fullQuery);
    $stmt2->execute();
    $empleados = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<br>📋 Primeros 5 empleados:<br>";
    foreach ($empleados as $emp) {
        echo "- " . $emp['codigo'] . ": " . $emp['NOMBRE'] . " " . $emp['APELLIDO'] . "<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>