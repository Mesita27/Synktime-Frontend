<?php
require_once '../config/database.php';

// Verificar si hay un ID de sede
$sedeId = isset($_GET['sede_id']) ? (int)$_GET['sede_id'] : null;

// Verificar que tenemos un ID de sede válido
if (!$sedeId) {
    echo json_encode([
        'success' => false,
        'error' => 'No se proporcionó un ID de sede válido'
    ]);
    exit;
}

try {
    // Obtener establecimientos de la sede
    $stmt = $conn->prepare("
        SELECT ID_ESTABLECIMIENTO, NOMBRE, DIRECCION
        FROM ESTABLECIMIENTO
        WHERE ID_SEDE = :sedeId
        AND ESTADO = 'A'
        ORDER BY NOMBRE
    ");
    
    $stmt->bindParam(':sedeId', $sedeId, PDO::PARAM_INT);
    $stmt->execute();
    
    $establecimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Devolver datos en formato JSON
    echo json_encode([
        'success' => true,
        'establecimientos' => $establecimientos
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}