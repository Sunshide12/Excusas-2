<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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

// Recolectar datos
$id_curs_asig_es    = $_POST['id_curs_asig_es'] ?? '';
$fecha_falta_excu   = $_POST['fecha_falta_excu'] ?? '';
$tipo_excu          = $_POST['tipo_excu'] ?? '';
$otro_tipo_excu     = $_POST['otro_tipo_excu'] ?? '';
$descripcion_excu   = $_POST['descripcion_excu'] ?? '';
$num_doc_estudiante = $_SESSION['estudiante_id'];

// Validaciones básicas
if (empty($id_curs_asig_es) || empty($fecha_falta_excu) || empty($tipo_excu) || empty($descripcion_excu)) {
    echo json_encode(['success' => false, 'mensaje' => 'Faltan datos obligatorios']);
    exit;
}

// Soporte
$soporte_excu = $_POST['soporte_excu'] ?? '';
if (empty($soporte_excu)) {
    echo json_encode(['success' => false, 'mensaje' => 'Soporte vacío']);
    exit;
}

try {
    // Insertar excusa
    $fecha_radicado_excu = date('Y-m-d');
    $estado_inicial = 3; // Pendiente

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
    $stmt->execute([
        ':id_curs_asig_es'    => $id_curs_asig_es,
        ':fecha_falta_excu'   => $fecha_falta_excu,
        ':fecha_radicado_excu'=> $fecha_radicado_excu,
        ':soporte_excu'       => $soporte_excu,
        ':descripcion_excu'   => $descripcion_excu,
        ':tipo_excu'          => $tipo_excu,
        ':otro_tipo_excu'     => $otro_tipo_excu,
        ':estado_excu'        => $estado_inicial,
        ':num_doc_estudiante' => $num_doc_estudiante
    ]);

    $id_excusa = $conn->lastInsertId();

    // Obtener datos para correo al director de unidad
    $sqlDatos = "
        SELECT 
            exc.fecha_falta_excu,
            tex.tipo_excu,
            est.nombre_estudiante,
            u.nombre_unidad,
            e.nombre_empleado AS nombre_director,
            e.correo_empleado AS correo_director
        FROM excusas AS exc
        INNER JOIN estudiantes AS est 
            ON exc.num_doc_estudiante = est.num_doc_estudiante
        INNER JOIN unidades AS u
            ON est.id_unidad = u.id_unidad
        INNER JOIN empleados AS e
            ON e.id_unidad = u.id_unidad
           AND e.rol_empleado = 2 -- Director de Unidad
        INNER JOIN tiposexcusas AS tex 
            ON exc.tipo_excu = tex.id_tipo_excu
        WHERE exc.id_excusa = :id_excusa
        LIMIT 1
    ";
    $stmtDatos = $conn->prepare($sqlDatos);
    $stmtDatos->execute([':id_excusa' => $id_excusa]);
    $datos = $stmtDatos->fetch(PDO::FETCH_ASSOC);

    $mail_sent = false;
    $mail_error = '';

    if ($datos && !empty($datos['correo_director'])) {
        require_once './Terceros/dropbox/PHPMailer-master/src/Exception.php';
        require_once './Terceros/dropbox/PHPMailer-master/src/PHPMailer.php';
        require_once './Terceros/dropbox/PHPMailer-master/src/SMTP.php';

        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'stebanbusiness@gmail.com';
            $mail->Password = 'jywt gyer gujh qsjl';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('stebanbusiness@gmail.com', 'Sistema Excusas Cotecnova');
            $mail->addAddress($datos['correo_director']);

            $mail->isHTML(true);
            $mail->Subject = 'Nueva excusa registrada - ' . $datos['nombre_unidad'];
            $mail->Body = "
                <p>Hola Director(a) {$datos['nombre_director']},</p>
                <p>El estudiante <strong>{$datos['nombre_estudiante']}</strong> ha registrado una nueva excusa en la unidad <strong>{$datos['nombre_unidad']}</strong>.</p>
                <p><strong>Fecha de la falta:</strong> {$datos['fecha_falta_excu']}</p>
                <p><strong>Tipo de excusa:</strong> {$datos['tipo_excu']}</p>
                <p>Por favor, ingrese al sistema para aprobar o rechazar esta solicitud.</p>
            ";
            $mail->send();
            $mail_sent = true;
        } catch (Exception $e) {
            $mail_error = $mail->ErrorInfo ?? $e->getMessage();
            error_log("PHPMailer error: " . $mail_error);
        }
    } else {
        error_log("No se encontró correo del director para la excusa id={$id_excusa}");
    }

    if ($mail_sent) {
        echo json_encode(['success' => true, 'mensaje' => 'Excusa registrada correctamente.']);
    } else {
        $msg = 'Excusa registrada correctamente.';
        if (!empty($mail_error)) {
            $msg .= ' Pero hubo un error al enviar correo: ' . $mail_error;
        } else {
            $msg .= ' No se envió correo (no se encontró correo del director).';
        }
        echo json_encode(['success' => true, 'mensaje' => $msg]);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'mensaje' => 'Error al registrar la excusa: ' . $e->getMessage()]);
}
 catch (Exception $e) {
    echo json_encode(['success' => false, 'mensaje' => 'Error inesperado: ' . $e->getMessage()]);
}
