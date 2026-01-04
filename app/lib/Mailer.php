<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../vendor/autoload.php';

function sendMail(string $to, string $subject, string $body): void
{
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();

        // Gmail SMTPS (THIS IS THE KEY FIX)
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = getenv('MAIL_USER');
        $mail->Password   = getenv('MAIL_PASS');
        $mail->Port       = 465;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;

        // Required on Railway to avoid TLS handshake issues
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true,
            ]
        ];

        $mail->setFrom(
            getenv('MAIL_FROM'),
            getenv('MAIL_FROM_NAME') ?: 'EasyVault'
        );

        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        if (!$mail->send()) {
            throw new RuntimeException($mail->ErrorInfo);
        }

    } catch (Exception $e) {
        error_log('MAIL ERROR: ' . $e->getMessage());
        throw new RuntimeException('Email failed');
    }
}
