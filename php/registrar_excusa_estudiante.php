<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
include_once './conexion.php';
session_start();

// Validar sesión de estudiante
if (!isset($_SESSION['estudiante_id'])) {
    echo json_encode(['success' => false, 'mensaje' => 'No autenticado']);
    exit;
}

// Validar datos requeridos
$id_curs_asig_es = $_POST['id_curs_asig_es'] ?? '';
$fecha_falta_excu = $_POST['fecha_falta_excu'] ?? '';
$tipo_excu = $_POST['tipo_excu'] ?? '';
$otro_tipo_excu = $_POST['otro_tipo_excu'] ?? '';
$descripcion_excu = $_POST['descripcion_excu'] ?? '';
$num_doc_estudiante = $_SESSION['estudiante_id'];

if (empty($id_curs_asig_es) || empty($fecha_falta_excu) || empty($tipo_excu) || empty($descripcion_excu)) {
    echo json_encode(['success' => false, 'mensaje' => 'Faltan datos obligatorios']);
    exit;
}

// Manejar archivo de soporte
$soporte_excu = $_POST['soporte_excu'] ?? '';

if (empty($soporte_excu)) {    
    echo json_encode(['success' => false, 'mensaje' => 'Soporte vacío']);
    exit;
}

// Insertar excusa en la base de datos
try {
    $fecha_radicado_excu = date('Y-m-d');
    $estado_inicial = 3; // 3 = pendiente

    $stmt = $conn->prepare("
        INSERT INTO excusas (
            id_curs_asig_es, 
            fecha_falta_excu, 
            fecha_radicado_excu, 
            soporte_excu, 
            descripcion_excu, 
            tipo_excu, 
            otro_tipo_excu, 
            estado_excu, 
            num_doc_estudiante
        ) VALUES (
            :id_curs_asig_es, 
            :fecha_falta_excu,
            :fecha_radicado_excu, 
            :soporte_excu, 
            :descripcion_excu, 
            :tipo_excu, 
            :otro_tipo_excu, 
            :estado_excu, 
            :num_doc_estudiante
        )
    ");

    $stmt->bindParam(':id_curs_asig_es', $id_curs_asig_es);
    $stmt->bindParam(':fecha_falta_excu', $fecha_falta_excu);
    $stmt->bindParam(':fecha_radicado_excu', $fecha_radicado_excu);
    $stmt->bindParam(':soporte_excu', $soporte_excu);
    $stmt->bindParam(':descripcion_excu', $descripcion_excu);
    $stmt->bindParam(':tipo_excu', $tipo_excu);
    $stmt->bindParam(':otro_tipo_excu', $otro_tipo_excu);
    $stmt->bindParam(':estado_excu', $estado_inicial);
    $stmt->bindParam(':num_doc_estudiante', $num_doc_estudiante);
    $stmt->execute();
    echo json_encode(['success' => true, 'mensaje' => 'Excusa registrada correctamente']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'mensaje' => 'Error al registrar la excusa: ' . $e->getMessage()]);
} 