<?php
declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * Send an email using configured mail mode
 *
 * @throws RuntimeException on failure
 */
function sendMail(string $to, string $subject, string $htmlBody): void
{
    $mode = $_ENV['MAIL_MODE'] ?? 'dev';

    if ($mode === 'dev') {
        logDevMail($to, $subject, $htmlBody);
        return;
    }

    sendProdMail($to, $subject, $htmlBody);
}

/**
 * The main mail function sending using SMTP
 */
function sendProdMail(string $to, string $subject, string $htmlBody): void
{
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = $_ENV['MAIL_HOST'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['MAIL_USER'];
        $mail->Password   = $_ENV['MAIL_PASS'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = (int) $_ENV['MAIL_PORT'];

        $mail->setFrom(
            $_ENV['MAIL_USER'],
            $_ENV['MAIL_FROM_NAME'] ?? 'EasyVault'
        );

        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;

        $mail->send();
    } catch (Exception $e) {
        throw new RuntimeException('Mail sending failed');
    }
}

/**
 * this part is for development where email will not be sent, this is only used for development phase
 */
function logDevMail(string $to, string $subject, string $htmlBody): void
{
    $logDir = __DIR__ . '/../../storage/logs';
    $logFile = $logDir . '/mail.log';

    if (!is_dir($logDir)) {
        mkdir($logDir, 0750, true);
    }

    $entry = sprintf(
        "[%s]\nTO: %s\nSUBJECT: %s\nBODY:\n%s\n\n--------------------\n",
        date('Y-m-d H:i:s'),
        $to,
        $subject,
        strip_tags($htmlBody)
    );

    file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
}
