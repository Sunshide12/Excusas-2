<?php
require_once '../../php/conexion.php';
require_once '../../php/Terceros/dropbox/PHPMailer-master/src/Exception.php';
require_once '../../php/Terceros/dropbox/PHPMailer-master/src/PHPMailer.php';
require_once '../../php/Terceros/dropbox/PHPMailer-master/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

header('Content-Type: application/json');

try {
    // Datos desde JS
    $id_curso   = $_POST['id_curs_asig_es'];
    $fecha      = $_POST['fecha_falta_excu'];
    $tipo       = $_POST['tipo_excu'];
    $otroTipo   = $_POST['otro_tipo_excu'] ?? '';
    $motivo     = $_POST['descripcion_excu'];
    $soporte    = $_POST['soporte_excu'];

    $num_doc_estudiante = $_SESSION['num_doc_estudiante']; // debe estar en sesión

    // 1) Insertar en la BD
    $stmt = $conn->prepare("
        INSERT INTO excusas (
            id_curs_asig_es, fecha_falta_excu, tipo_excu, otro_tipo_excu, 
            descripcion_excu, soporte_excu, num_doc_estudiante, estado_excu
        )
        VALUES (:id_curso, :fecha, :tipo, :otroTipo, :motivo, :soporte, :doc, 3)
    ");
    $stmt->execute([
        ':id_curso' => $id_curso,
        ':fecha' => $fecha,
        ':tipo' => $tipo,
        ':otroTipo' => $otroTipo,
        ':motivo' => $motivo,
        ':soporte' => $soporte,
        ':doc' => $num_doc_estudiante
    ]);

    $id_excusa = $conn->lastInsertId();

    // 2) Traer datos para el correo
    $sqlDatos = "
        SELECT 
            exc.fecha_falta_excu,
            tex.tipo_excu,
            est.nombre_estudiante,
            cae.correo_empleado
        FROM excusas AS exc
        INNER JOIN estudiantes AS est 
            ON exc.num_doc_estudiante = est.num_doc_estudiante
        INNER JOIN tiposexcusas AS tex 
            ON exc.tipo_excu = tex.id_tipo_excu
        INNER JOIN t_v_exc_asig_mat_est AS cae
            ON exc.id_curs_asig_es = cae.id_curs_asig_es
           AND exc.num_doc_estudiante = cae.est_codigo_unico
        WHERE exc.id_excusa = :id_excusa
        LIMIT 1
    ";
    $stmtDatos = $conn->prepare($sqlDatos);
    $stmtDatos->execute([':id_excusa' => $id_excusa]);
    $datos = $stmtDatos->fetch(PDO::FETCH_ASSOC);

    if ($datos && !empty($datos['correo_empleado'])) {
        // 3) Enviar correo
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'stebanbusiness@gmail.com';
            $mail->Password = 'iabo xocj omup yifc'; // clave de aplicación
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('stebanbusiness@gmail.com', 'Sistema Excusas Cotecnova');
            $mail->addAddress($datos['correo_empleado']); // correo del docente

            $mail->isHTML(true);
            $mail->Subject = 'Nueva excusa registrada para su materia';
            $mail->Body = "
                <p>Hola docente,</p>
                <p>El estudiante <strong>{$datos['nombre_estudiante']}</strong> ha registrado una excusa.</p>
                <p><strong>Fecha:</strong> {$datos['fecha_falta_excu']}</p>
                <p><strong>Motivo:</strong> {$datos['tipo_excu']}</p>
            ";
            $mail->send();
        } catch (Exception $e) {
            // No interrumpir flujo si el correo falla
            error_log("Error al enviar correo: {$mail->ErrorInfo}");
        }
    }

    echo json_encode(['success' => true, 'mensaje' => 'Excusa registrada y notificada.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'mensaje' => $e->getMessage()]);
}
