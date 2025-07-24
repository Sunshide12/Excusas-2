<?php
header('Content-Type: application/json');
include_once './conexion.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['cambios']) || !is_array($data['cambios'])) {
    echo json_encode(['success' => false, 'mensaje' => 'Datos inválidos']);
    exit;
}

try {
    $conn->beginTransaction();

    $stmt = $conn->prepare("UPDATE excusas SET estado_excu = :estado WHERE id_excusa = :id");

    foreach ($data['cambios'] as $cambio) {
        $stmt->bindParam(':estado', $cambio['estado']);
        $stmt->bindParam(':id', $cambio['id_excusa']);
        $stmt->execute();
    }

    $conn->commit();
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    $conn->rollBack();
    echo json_encode(['success' => false, 'mensaje' => 'Error en la BD: ' . $e->getMessage()]);
}
?>
