<?php
header('Content-Type: application/json');
include 'Db.php';

$db = new Db();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$recogido = isset($_POST['recogido']) ? (int)$_POST['recogido'] : 0;

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

try {
    // Verificar que la inscripción existe
    $checkQuery = "SELECT id FROM inscripcion WHERE id = " . $id;
    $checkResult = $db->query($checkQuery);
    
    if (!$checkResult || $checkResult->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Inscripción no encontrada']);
        exit;
    }
    
    // Actualizar el estado de recogida
    $updateQuery = "UPDATE inscripcion SET recogido = " . $recogido . " WHERE id = " . $id;
    $result = $db->query($updateQuery);
    
    if ($result) {
        echo json_encode([
            'success' => true, 
            'message' => 'Estado actualizado correctamente',
            'id' => $id,
            'recogido' => $recogido
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Error al actualizar: ' . $db->error()
        ]);
    }
    
} catch (Exception $e) {
    error_log("toggle_recogida.php error: " . $e->getMessage(), 3, "../../errors.log");
    echo json_encode([
        'success' => false, 
        'message' => 'Error interno del servidor'
    ]);
}
?>