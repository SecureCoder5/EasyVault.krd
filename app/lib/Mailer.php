<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../vendor/autoload.php';

function sendMail(string $to, string $subject, string $body): void
{
    $mail = new PHPMailer(true);

    // 🔴 FORCE DEBUG OUTPUT TO RAILWAY LOGS
    $mail->SMTPDebug  = 3;
    $mail->Debugoutput = function ($str, $level) {
        error_log("SMTP DEBUG [$level]: $str");
    };

    try {
        $mail->isSMTP();
        $mail->Host       = getenv('MAIL_HOST');
        $mail->SMTPAuth   = true;
        $mail->Username   = getenv('MAIL_USER');
        $mail->Password   = getenv('MAIL_PASS');
        $mail->Port       = (int) getenv('MAIL_PORT');
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Timeout    = 15;

        $mail->setFrom(
            getenv('MAIL_FROM'),
            getenv('MAIL_FROM_NAME') ?: 'EasyVault'
        );

        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();

        error_log('SMTP SUCCESS: email sent');

    } catch (Exception $e) {
        error_log('SMTP FAILURE: ' . $mail->ErrorInfo);
        throw new RuntimeException('Email failed');
    }
}
