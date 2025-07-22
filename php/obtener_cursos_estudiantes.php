<?php
include_once './conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['num_doc_estudiante'])) {
    $cedula = $_POST['num_doc_estudiante'];

    try {
        $stmt = $conn->prepare("SELECT DISTINCT id_curs_asig_es, curso FROM t_v_exc_asig_mat_est WHERE est_codigo_unico = :cedula");
        $stmt->bindParam(':cedula', $cedula);
        $stmt->execute();

        $cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($cursos) {
            echo json_encode(['success' => true, 'cursos' => $cursos]);
        } else {
            echo json_encode(['success' => false, 'mensaje' => 'No se encontraron cursos']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'mensaje' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'mensaje' => 'Solicitud no válida']);
}
?>
