<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// registrar_excusa_estudiante.php
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

// Soporte (en tu flujo lo subes a Dropbox y mandas la URL en 'soporte_excu')
$soporte_excu = $_POST['soporte_excu'] ?? '';
if (empty($soporte_excu)) {
    echo json_encode(['success' => false, 'mensaje' => 'Soporte vacío']);
    exit;
}

try {
    // Insertar excusa
    $fecha_radicado_excu = date('Y-m-d');
    $estado_inicial = 3; // pendiente

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
        ':id_curs_asig_es'   => $id_curs_asig_es,
        ':fecha_falta_excu'  => $fecha_falta_excu,
        ':fecha_radicado_excu' => $fecha_radicado_excu,
        ':soporte_excu'      => $soporte_excu,
        ':descripcion_excu'  => $descripcion_excu,
        ':tipo_excu'         => $tipo_excu,
        ':otro_tipo_excu'    => $otro_tipo_excu,
        ':estado_excu'       => $estado_inicial,
        ':num_doc_estudiante' => $num_doc_estudiante
    ]);

    $id_excusa = $conn->lastInsertId();

    // --- Buscar datos para correo: fecha, tipo, nombre del estudiante, y nombre del profe desde la vista ---
    $sqlDatos = "
        SELECT 
            exc.fecha_falta_excu,
            tex.tipo_excu,
            est.nombre_estudiante,
            CONCAT_WS(' ', cae.profe_nombre, cae.profe_snombre, cae.profe_apellido, cae.profe_sapellido) AS profe_full
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

    $mail_sent = false;
    $mail_error = '';

    if ($datos) {
        $profe_full = trim($datos['profe_full']);

        // 1) Intentar buscar correo del docente por coincidencia exacta en empleados.nombre_empleado
        $correo_doc = null;
        $stmtEmp = $conn->prepare("
            SELECT correo_empleado 
            FROM empleados 
            WHERE UPPER(TRIM(nombre_empleado)) = UPPER(TRIM(:profe_full))
            LIMIT 1
        ");
        $stmtEmp->execute([':profe_full' => $profe_full]);
        $r = $stmtEmp->fetch(PDO::FETCH_ASSOC);
        if ($r && !empty($r['correo_empleado'])) {
            $correo_doc = $r['correo_empleado'];
        } else {
            // 2) Fallback: buscar por LIKE (por si orden de nombres/apellidos difiere)
            $stmtEmp2 = $conn->prepare("
                SELECT correo_empleado 
                FROM empleados
                WHERE UPPER(nombre_empleado) LIKE :like1
                   OR UPPER(nombre_empleado) LIKE :like2
                LIMIT 1
            ");
            $like1 = '%' . mb_strtoupper($profe_full, 'UTF-8') . '%';
            // también probar con solo apellido (si profe_full tiene apellidos)
            $parts = preg_split('/\s+/', $profe_full);
            $like2 = count($parts) ? '%' . mb_strtoupper(end($parts), 'UTF-8') . '%' : $like1;
            $stmtEmp2->execute([':like1' => $like1, ':like2' => $like2]);
            $r2 = $stmtEmp2->fetch(PDO::FETCH_ASSOC);
            if ($r2 && !empty($r2['correo_empleado'])) {
                $correo_doc = $r2['correo_empleado'];
            }
        }

        // Si conseguimos correo, enviamos
        if (!empty($correo_doc)) {
            // paths relativos — ajusta si tu estructura es distinta
            require_once './Terceros/dropbox/PHPMailer-master/src/Exception.php';
            require_once './Terceros/dropbox/PHPMailer-master/src/PHPMailer.php';
            require_once './Terceros/dropbox/PHPMailer-master/src/SMTP.php';



            // Envío de correo
            try {
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'stebanbusiness@gmail.com';          // tu cuenta
                $mail->Password = 'jywt gyer gujh qsjl';             // ¡pon aquí tu app password!
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('stebanbusiness@gmail.com', 'Sistema Excusas Cotecnova');
                $mail->addAddress($correo_doc);

                $mail->isHTML(true);
                $mail->Subject = 'Nueva excusa registrada para su materia';
                $mail->Body = "
                    <p>Hola docente,</p>
                    <p>El estudiante <strong>{$datos['nombre_estudiante']}</strong> ha registrado una excusa.</p>
                    <p><strong>Fecha (falta):</strong> {$datos['fecha_falta_excu']}</p>
                    <p><strong>Tipo:</strong> {$datos['tipo_excu']}</p>
                    <p>Por favor, revise el sistema para más información y soporte.</p>
                ";
                $mail->send();
                $mail_sent = true;
            } catch (Exception $e) {
                $mail_sent = false;
                $mail_error = $mail->ErrorInfo ?? $e->getMessage();
                error_log("PHPMailer error: " . $mail_error);
            }
        } else {
            // no se encontró correo del docente
            error_log("No se encontró correo para el docente: '{$profe_full}' (curso id_curs_asig_es={$id_curs_asig_es})");
        }
    } else {
        error_log("No se localizaron datos de excusa/id_excusa = {$id_excusa} para armar correo.");
    }

    // Preparar mensaje para el front
    if ($mail_sent) {
        echo json_encode(['success' => true, 'mensaje' => 'Excusa registrada y correo enviado al docente.']);
    } else {
        // si no se envió correo, aun así devolvemos success si la inserción fue correcta
        $msg = 'Excusa registrada correctamente.';
        if (!empty($mail_error)) {
            $msg .= ' Pero hubo un error al enviar correo: ' . $mail_error;
        } else {
            $msg .= ' No se envió correo (no se encontró correo del docente).';
        }
        echo json_encode(['success' => true, 'mensaje' => $msg]);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'mensaje' => 'Error al registrar la excusa: ' . $e->getMessage()]);
}
