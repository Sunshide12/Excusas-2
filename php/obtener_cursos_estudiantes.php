<?php
include_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $num_doc = $_POST['num_doc_estudiante'] ?? '';

    try {
        $stmt = $conn->prepare("
            SELECT DISTINCT curso 
            FROM t_v_exc_asig_mat_est 
            WHERE est_codigo_unico = :num_doc
        ");
        $stmt->bindParam(':num_doc', $num_doc);
        $stmt->execute();
        $cursos = $stmt->fetchAll(PDO::FETCH_COLUMN);

        echo json_encode([
            'success' => true,
            'cursos' => $cursos
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'mensaje' => 'Error al obtener cursos: ' . $e->getMessage()
        ]);
    }
}
?>
