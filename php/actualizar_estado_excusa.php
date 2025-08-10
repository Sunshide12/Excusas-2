<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
include_once './conexion.php';
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/Terceros/dropbox/PHPMailer-master/src/Exception.php';
require_once __DIR__ . '/Terceros/dropbox/PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/Terceros/dropbox/PHPMailer-master/src/SMTP.php';

define('SENDER_EMAIL', 'stebanbusiness@gmail.com'); // tu correo
define('SENDER_PASS',  'jywt gyer gujh qsjl');       // tu app password
$SMTP_HOST = 'smtp.gmail.com';
$SMTP_PORT = 587;
$SEND_FALLBACK_TO_ADMIN = true;
$ADMIN_FALLBACK_EMAIL = SENDER_EMAIL;

function find_teacher_email(PDO $conn, string $profe_full) {
    $profe_full = trim($profe_full);
    if ($profe_full === '') return null;

    $stmt = $conn->prepare("
        SELECT correo_empleado
        FROM empleados
        WHERE UPPER(TRIM(nombre_empleado)) = UPPER(TRIM(:profe_full))
        LIMIT 1
    ");
    $stmt->execute([':profe_full' => $profe_full]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row && !empty($row['correo_empleado'])) {
        return $row['correo_empleado'];
    }

    $parts = preg_split('/\s+/', $profe_full);
    $like1 = '%' . mb_strtoupper($profe_full, 'UTF-8') . '%';
    $like2 = count($parts) ? '%' . mb_strtoupper(end($parts), 'UTF-8') . '%' : $like1;

    $stmt2 = $conn->prepare("
        SELECT correo_empleado
        FROM empleados
        WHERE UPPER(nombre_empleado) LIKE :like1
           OR UPPER(nombre_empleado) LIKE :like2
        LIMIT 1
    ");
    $stmt2->execute([':like1' => $like1, ':like2' => $like2]);
    $row2 = $stmt2->fetch(PDO::FETCH_ASSOC);
    if ($row2 && !empty($row2['correo_empleado'])) {
        return $row2['correo_empleado'];
    }

    return null;
}

if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Director de Unidad') {
    echo json_encode(['success' => false, 'mensaje' => 'No autenticado']);
    exit;
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!isset($data['cambios']) || !is_array($data['cambios'])) {
    echo json_encode(['success' => false, 'mensaje' => 'Datos inválidos']);
    exit;
}

// Nueva línea para recibir la justificación
$justificacion = trim($data['justificacion'] ?? '');

$respuestas = [];

foreach ($data['cambios'] as $cambio) {
    $id_excusa = (int)($cambio['id_excusa'] ?? 0);
    $estado_raw = $cambio['estado'] ?? '';
    $estado_str = strtolower(trim($estado_raw));

    if ($estado_str == '1' || in_array($estado_str, ['aprobar', 'aprobada', 'aprobado'])) {
        $estado_num = 1; // Aprobada
        $estadoTexto = 'APROBADA';
    } elseif ($estado_str == '2' || in_array($estado_str, ['negar', 'denegar', 'denegada', 'negada'])) {
        $estado_num = 2; // Denegada
        $estadoTexto = 'DENEGADA';
    } else {
        $estado_num = 3; // Pendiente
        $estadoTexto = 'PENDIENTE';
    }

    $stmt = $conn->prepare('UPDATE excusas SET estado_excu = :estado WHERE id_excusa = :id_excusa');
    $stmt->bindValue(':estado', $estado_num, PDO::PARAM_INT);
    $stmt->bindValue(':id_excusa', $id_excusa, PDO::PARAM_INT);
    if (!$stmt->execute()) {
        $respuestas[] = ['id_excusa' => $id_excusa, 'actualizacion' => 'fallo'];
        continue;
    }

    $queryInfo = $conn->prepare("
        SELECT 
            e.fecha_falta_excu,
            tex.tipo_excu AS nombre_tipo,
            e.num_doc_estudiante,
            est.nombre_estudiante,
            cae.curso,
            CONCAT_WS(' ', cae.profe_nombre, cae.profe_snombre, cae.profe_apellido, cae.profe_sapellido) AS profe_full
        FROM excusas e
        INNER JOIN estudiantes est 
            ON e.num_doc_estudiante = est.num_doc_estudiante
        INNER JOIN t_v_exc_asig_mat_est cae 
            ON e.id_curs_asig_es = cae.id_curs_asig_es
           AND e.num_doc_estudiante = cae.est_codigo_unico
        INNER JOIN tiposexcusas tex
            ON e.tipo_excu = tex.id_tipo_excu
        WHERE e.id_excusa = :id_excusa
        LIMIT 1
    ");
    $queryInfo->execute([':id_excusa' => $id_excusa]);
    $info = $queryInfo->fetch(PDO::FETCH_ASSOC);

    if (!$info) {
        $respuestas[] = ['id_excusa' => $id_excusa, 'correo' => 'no_info'];
        continue;
    }

    $correo_doc = find_teacher_email($conn, $info['profe_full']);
    if (empty($correo_doc) && $SEND_FALLBACK_TO_ADMIN) {
        $correo_doc = $ADMIN_FALLBACK_EMAIL;
    }

    if (!$correo_doc) {
        $respuestas[] = ['id_excusa' => $id_excusa, 'correo' => 'no_encontrado'];
        continue;
    }

    $subject = "Notificación de Excusa - Estado $estadoTexto";
    
    $mensajeHTML = "
        Hola Docente,<br><br>
        La excusa del estudiante <strong>{$info['nombre_estudiante']}</strong> (ID: {$info['num_doc_estudiante']}) 
        para el curso <strong>{$info['curso']}</strong> ha sido <strong>$estadoTexto</strong> por el Director de Unidad.<br><br>
        <strong>Fecha de la falta:</strong> {$info['fecha_falta_excu']}<br>
        <strong>Tipo de Excusa:</strong> {$info['nombre_tipo']}<br>
        <strong>Estado:</strong> $estadoTexto<br>";

    // Solo añadir la justificación si viene algo
    if (!empty($justificacion)) {
        $mensajeHTML .= "<br><strong>Justificación del Director:</strong> {$justificacion}<br>";
    }

    $mensajeHTML .= "<br>Por favor, revise el sistema para más información.";

    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SENDER_EMAIL;
        $mail->Password = SENDER_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $SMTP_PORT;

        $mail->setFrom(SENDER_EMAIL, 'Cotecnova Notificaciones');
        $mail->addAddress($correo_doc);
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = $subject;
        $mail->Body = $mensajeHTML;

        $mail->send();
        $respuestas[] = ['id_excusa' => $id_excusa, 'correo' => 'enviado', 'estado' => $estadoTexto];
    } catch (Exception $e) {
        $respuestas[] = ['id_excusa' => $id_excusa, 'correo' => 'fallo', 'error' => $mail->ErrorInfo];
    }
}

echo json_encode(['success' => true, 'resultados' => $respuestas]);
