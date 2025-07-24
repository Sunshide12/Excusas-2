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
        // Validar que el estado sea 1 (Aprobado) o 2 (Denegado)
        if (!in_array($cambio['estado'], ['1', '2'], true)) {
            throw new Exception("Estado inválido para la excusa ID {$cambio['id_excusa']}");
        }

        $stmt->bindParam(':estado', $cambio['estado']);
        $stmt->bindParam(':id', $cambio['id_excusa']);
        $stmt->execute();
    }

    $conn->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['success' => false, 'mensaje' => 'Error: ' . $e->getMessage()]);
}
?>
