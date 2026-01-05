<?php
declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * Send email using PHPMailer + Gmail SMTP
 *
 * @param string $toEmail  Recipient email
 * @param string $subject  Email subject
 * @param string $htmlBody HTML body
 * @return bool
 */
function sendMail(string $toEmail, string $subject, string $htmlBody): bool
{
    $mail = new PHPMailer(true);

    try {
        // SMTP configuration
        $mail->isSMTP();
        $mail->Host       = $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['MAIL_USERNAME'];
        $mail->Password   = $_ENV['MAIL_PASSWORD'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = (int)($_ENV['MAIL_PORT'] ?? 587);

        // Security & reliability
        $mail->SMTPDebug  = 0;
        $mail->CharSet    = 'UTF-8';
        $mail->Timeout    = 10;

        // Sender
        $mail->setFrom(
            $_ENV['MAIL_FROM'],
            $_ENV['MAIL_FROM_NAME'] ?? 'EasyVault'
        );

        // Recipient
        $mail->addAddress($toEmail);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = strip_tags($htmlBody);

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log('[MAIL ERROR] ' . $mail->ErrorInfo);
        return false;
    }
}
