<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
include_once './conexion.php';
session_start();

// Validar sesión de docente/director
if (!isset($_SESSION['rol'])) {
    echo json_encode(['success' => false, 'mensaje' => 'No autenticado']);
    exit;
}

// Validar datos requeridos
$num_doc_estudiante = $_POST['num_doc_estudiante'] ?? '';
$id_curs_asig_es = $_POST['id_curs_asig_es'] ?? '';
$fecha_falta_excu = $_POST['fecha_falta_excu'] ?? '';
$tipo_excu = $_POST['tipo_excu'] ?? '';
$otro_tipo_excu = $_POST['otro_tipo_excu'] ?? '';
$descripcion_excu = $_POST['descripcion_excu'] ?? '';

if (empty($num_doc_estudiante) || empty($id_curs_asig_es) || empty($fecha_falta_excu) || 
    empty($tipo_excu) || empty($descripcion_excu)) {
    echo json_encode(['success' => false, 'mensaje' => 'Todos los campos son obligatorios']);
    exit;
}

// Validar que el estudiante existe
try {
    $stmt = $conn->prepare("SELECT num_doc_estudiante FROM estudiantes WHERE num_doc_estudiante = :num_doc");
    $stmt->bindParam(':num_doc', $num_doc_estudiante);
    $stmt->execute();
    $estudiante = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$estudiante) {
        echo json_encode(['success' => false, 'mensaje' => 'El estudiante no existe']);
        exit;
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'mensaje' => 'Error al validar estudiante: ' . $e->getMessage()]);
    exit;
}

// Procesar archivo de soporte
$soporte_excu = '';
if (isset($_FILES['soporte_excu']) && $_FILES['soporte_excu']['error'] === UPLOAD_ERR_OK) {
    $archivo = $_FILES['soporte_excu'];
    $nombre_archivo = time() . '_' . $archivo['name'];
    $ruta_destino = '../Images/estudiantes/' . $nombre_archivo;
    
    // Crear directorio si no existe
    if (!is_dir('../Images/estudiantes/')) {
        mkdir('../Images/estudiantes/', 0777, true);
    }
    
    if (move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
        $soporte_excu = $nombre_archivo;
    } else {
        echo json_encode(['success' => false, 'mensaje' => 'Error al subir el archivo']);
        exit;
    }
} else {
    echo json_encode(['success' => false, 'mensaje' => 'Debe subir un archivo de soporte']);
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
    echo json_encode(['success' => false, 'mensaje' => 'Error al registrar excusa: ' . $e->getMessage()]);
}
?> 