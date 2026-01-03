<?php
declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../vendor/autoload.php';

class Mailer
{
    /**
     * Send an email.
     * In development, emails can be logged instead of sent.
     */
    public static function send(string $to, string $subject, string $body): bool
    {
        $host = getenv('MAIL_HOST');
        $user = getenv('MAIL_USER');
        $pass = getenv('MAIL_PASS');
        $port = getenv('MAIL_PORT');
        $mode = getenv('MAIL_MODE') ?: 'dev'; // dev | prod

        // Dev mode: log emails instead of sending
        if ($mode === 'dev') {
            error_log("MAIL DEV MODE\nTo: {$to}\nSubject: {$subject}\nBody:\n{$body}");
            return true;
        }

        if (!$host || !$user || !$pass || !$port) {
            error_log('Mail configuration incomplete.');
            return false;
        }

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = $host;
            $mail->SMTPAuth   = true;
            $mail->Username   = $user;
            $mail->Password   = $pass;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = (int)$port;

            $mail->setFrom($user, 'EasyVault.krd');
            $mail->addAddress($to);

            $mail->isHTML(true);
            $mail->Sub
