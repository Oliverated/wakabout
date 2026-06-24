<?php

require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

$mail->SMTPDebug = 2;
$mail->Debugoutput = 'html';
$mail->isSMTP();

try {
$mail->isSMTP();
$mail->Host       = 'smtp.hostinger.com';
$mail->SMTPAuth   = true;
$mail->Username   = 'paw@wakaabout.net';
$mail->Password   = 'Pelu@952';
$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SSL
$mail->Port       = 465;

$mail->setFrom('paw@wakaabout.net', 'WakaAbout Blog');
// $mail->addAddress($to);
    $mail->addAddress('victorlodoliver@gmail.com');

    $mail->isHTML(true);
    $mail->Subject = 'Weekly Newsletter';
    $mail->Body    = '<h1>Hello Subscriber!</h1><p>Welcome to our newsletter.</p>';

    $mail->send();
    echo "Email sent successfully";
} catch (Exception $e) {
    echo "Error: {$mail->ErrorInfo}";
}