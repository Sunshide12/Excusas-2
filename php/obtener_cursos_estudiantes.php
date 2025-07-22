<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


header('Content-Type: application/json');
require 'conexion.php'; // Asegúrate de que el archivo exista y sea correcto

try {
    if (!isset($_POST['num_doc_estudiante'])) {
        throw new Exception("Falta el número de documento.");
    }

    $num_doc = trim($_POST['num_doc_estudiante']);

    if (empty($num_doc)) {
        throw new Exception("Documento vacío.");
    }

    // Si 't_v_exc_asig_mat_est' tiene 'est_codigo_unico' y no 'num_doc_estudiante'
    $sql = "SELECT DISTINCT curso FROM t_v_exc_asig_mat_est WHERE est_codigo_unico = :num_doc";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':num_doc', $num_doc, PDO::PARAM_STR);
    $stmt->execute();

    $cursos = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode([
        'success' => true,
        'cursos' => $cursos
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
