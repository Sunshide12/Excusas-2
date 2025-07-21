<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
include_once './conexion.php'; 

function validarEstudiante($documento, $contrasena) {
    global $conn;
    try {
        $stmt = $conn->prepare("
            SELECT * FROM estudiantes 
            WHERE num_doc_estudiante = :documento AND contra_estudiante = :contrasena
        ");
        $stmt->bindParam(':documento', $documento);
        $stmt->bindParam(':contrasena', $contrasena);
        $stmt->execute();
        $estudiante = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($estudiante) {
            return [
                'success' => true,
                'estudiante' => $estudiante
            ];
        }

        return [
            'success' => false,
            'mensaje' => 'Usuario o contraseña incorrectos'
        ];
    } catch(PDOException $e) {
        return [
            'success' => false,
            'mensaje' => 'Error en la validación: ' . $e->getMessage()
        ];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $documento = $_POST['documentoIdentidad'] ?? '';
    $contrasena = $_POST['contrasena'] ?? '';

    if (empty($documento) || empty($contrasena)) {
        echo json_encode([
            'success' => false,
            'mensaje' => 'Por favor, complete todos los campos'
        ]);
        exit;
    }

    $resultado = validarEstudiante($documento, $contrasena);

    if ($resultado['success']) {
        session_start();
        $_SESSION['estudiante_id'] = $resultado['estudiante']['num_doc_estudiante'];
        $_SESSION['documento'] = $resultado['estudiante']['num_doc_estudiante'];
        $_SESSION['rol'] = 'Estudiante';
    }

    echo json_encode($resultado);
    exit;
}
