<?php
declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../vendor/autoload.php';

function sendMail(string $toEmail, string $subject, string $body, bool $isHtml = false): bool
{
    $mail = new PHPMailer(true);

    try {
        // Validate ENV
        $required = ['MAIL_HOST','MAIL_USERNAME','MAIL_PASSWORD','MAIL_FROM'];
        foreach ($required as $key) {
            if (empty($_ENV[$key])) {
                throw new RuntimeException("Missing ENV: $key");
            }
        }

        // SMTP
        $mail->isSMTP();
        $mail->Host       = $_ENV['MAIL_HOST'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['MAIL_USERNAME'];
        $mail->Password   = $_ENV['MAIL_PASSWORD'];
        $mail->Port       = (int)($_ENV['MAIL_PORT'] ?? 587);

        // Encryption
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

        // Reliability
        $mail->SMTPDebug  = 0;
        $mail->Timeout    = 10;
        $mail->CharSet    = 'UTF-8';

        // Sender
        $mail->setFrom(
            $_ENV['MAIL_FROM'],
            $_ENV['MAIL_FROM_NAME'] ?? 'EasyVault'
        );

        // Recipient
        $mail->addAddress($toEmail);

        // Content
        $mail->isHTML($isHtml);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = strip_tags($body);

        $mail->send();
        return true;

    } catch (Throwable $e) {
        error_log('[MAIL ERROR] ' . $e->getMessage());
        return false;
    }
}
