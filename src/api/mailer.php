<?php
// mailer.php — Función para enviar emails con PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../phpmailer/PHPMailer.php';
require_once __DIR__ . '/../phpmailer/SMTP.php';
require_once __DIR__ . '/../phpmailer/Exception.php';

function enviarEmail($destinatario, $nombreDestinatario, $asunto, $cuerpo) {
    $mail = new PHPMailer(true);

    try {
        // Configuración del servidor
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'rootupapp@gmail.com';
        $mail->Password   = 'evvu atvv viuj fnaz';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        // Remitente
        $mail->setFrom('rootupapp@gmail.com', 'RootUp');

        // Destinatario
        $mail->addAddress($destinatario, $nombreDestinatario);

        // Contenido
        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body    = $cuerpo;

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log('Error enviando email: ' . $mail->ErrorInfo);
        return false;
    }
}
?>