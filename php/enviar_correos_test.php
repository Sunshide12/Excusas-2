<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../php/Terceros/dropbox/vendor/autoload.php'; // o el path donde tengas PHPMailer

$mail = new PHPMailer(true);

try {
    // Configuración del servidor SMTP de Gmail
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;

    // TU CORREO GMAIL
    $mail->Username = 'stebanbusiness@gmail.com';
    $mail->Password = 'iabo xocj omup yifc'; // importante: ver más abajo
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Remitente
    $mail->setFrom('stebanbusiness@gmail.com', 'Sistema Excusas Cotecnova');

    // Destinatario
    $mail->addAddress('stebanmg48@cotecnova.edu.co', 'Docente');

       // Contenido
    $mail->isHTML(true);
    $mail->Subject = 'Nueva excusa registrada para tu materia';
    $mail->Body    = "
        <p>Hola docente,</p>
        <p>Un estudiante ha registrado una excusa para su materia.</p>
        <p><strong>Fecha:</strong> 2025-08-04</p>
        <p><strong>Motivo:</strong> Incapacidad médica</p>
    ";

    $mail->send();
    echo '✅ Correo enviado correctamente';
} catch (Exception $e) {
    echo "❌ Error al enviar correo: {$mail->ErrorInfo}";
}