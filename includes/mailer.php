<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

function sendMail($to, $subject, $body) {
    $mail = new PHPMailer(true);

    try {
$mail->isSMTP();
$mail->Host       = 'smtp.hostinger.com';
$mail->SMTPAuth   = true;
$mail->Username   = 'paw@wakaabout.net';
$mail->Password   = 'Pelu@952';
$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SSL
$mail->Port       = 465;

$mail->setFrom('paw@wakaabout.net', 'WakaAbout Blog');
$mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = $subject;
      $mail->Body    = $body;

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log("Mail error: " . $mail->ErrorInfo);
        return false;
    }
}