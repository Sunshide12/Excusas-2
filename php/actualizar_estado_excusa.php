<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
include_once './conexion.php';
session_start();

// Cargar PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

// Validar sesión
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Director de Unidad') {
    echo json_encode(['success' => false, 'mensaje' => 'No autenticado']);
    exit;
}

// Leer el JSON recibido
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!isset($data['cambios']) || !is_array($data['cambios'])) {
    echo json_encode(['success' => false, 'mensaje' => 'Datos inválidos']);
    exit;
}

$respuestas = [];

foreach ($data['cambios'] as $cambio) {
    $id_excusa = $cambio['id_excusa'];
    $estado = $cambio['estado'];
    $estado_num = ($estado === 'Aprobar') ? 1 : 2;

    // Actualizar estado en la base de datos
    $stmt = $conn->prepare('UPDATE excusas SET estado_excu = :estado WHERE id_excusa = :id_excusa');
    $stmt->bindParam(':estado', $estado_num);
    $stmt->bindParam(':id_excusa', $id_excusa);

    if ($stmt->execute()) {
        // Obtener datos del curso, estudiante y docente
        $query = $conn->prepare('
            SELECT e.*, cae.curso, emp.correo_empleado, est.nombre_estudiante 
            FROM excusas e 
            INNER JOIN t_v_exc_asig_mat_est cae ON e.id_curs_asig_es = cae.id_curs_asig_es 
            INNER JOIN estudiantes est ON e.num_doc_estudiante = est.num_doc_estudiante
            INNER JOIN empleados emp ON cae.id_empleado = emp.id_empleado
            WHERE e.id_excusa = :id_excusa
        ');
        $query->bindParam(':id_excusa', $id_excusa);
        $query->execute();
        $info = $query->fetch(PDO::FETCH_ASSOC);

        if ($info && !empty($info['correo_empleado'])) {
            $to = $info['correo_empleado'];
            $subject = 'Notificación de Excusa - Estado Actualizado';
            $mensaje = "Hola Docente,\n\nLa excusa del estudiante {$info['nombre_estudiante']} (ID: {$info['num_doc_estudiante']}) para el curso {$info['curso']} ha sido ".($estado_num == 1 ? 'APROBADA' : 'DENEGADA')." por el Director de Unidad.\n\nDetalles:\n- Fecha de la falta: {$info['fecha_falta_excu']}\n- Motivo: {$info['descripcion_excu']}\n- Estado: ".($estado_num == 1 ? 'Aprobada' : 'Denegada')."\n\nPor favor, revise el sistema de excusas para más información.";

            // Enviar correo
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'stebanmartinezgutierrez48@gmail.com'; // tu correo Gmail
                $mail->Password = 'otnb nqqw nety edui'; // tu contraseña de aplicación
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                $mail->setFrom('stebanmartinezgutierrez48@gmail.com', 'Cotecnova Notificaciones');
                $mail->addAddress($to);

                $mail->isHTML(false);
                $mail->CharSet = 'UTF-8';
                $mail->Subject = $subject;
                $mail->Body = $mensaje;

                $mail->send();
                $respuestas[] = ['id_excusa' => $id_excusa, 'correo' => 'enviado'];
            } catch (Exception $e) {
                $respuestas[] = [
                    'id_excusa' => $id_excusa,
                    'correo' => 'fallo',
                    'error' => $mail->ErrorInfo
                ];
            }
        } else {
            $respuestas[] = ['id_excusa' => $id_excusa, 'correo' => 'no_encontrado'];
        }
    } else {
        $respuestas[] = ['id_excusa' => $id_excusa, 'actualizacion' => 'fallo'];
    }
}

echo json_encode(['success' => true, 'resultados' => $respuestas]);
