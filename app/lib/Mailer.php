<?php

function sendMail(string $to, string $subject, string $html): void
{
    $apiKey = getenv('ELASTIC_EMAIL_API_KEY');

    if (!$apiKey) {
        throw new RuntimeException('Elastic Email API key missing');
    }

    $data = http_build_query([
        'apikey'   => $apiKey,
        'from'     => 'EasyVault <noreply@easyvault.local>',
        'to'       => $to,
        'subject'  => $subject,
        'bodyHtml' => $html,
    ]);

    $ch = curl_init('https://api.elasticemail.com/v2/email/send');

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $data,
    ]);

    $response = curl_exec($ch);
    $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($response === false || $status !== 200) {
        error_log('Elastic Email failed: ' . $response);
        curl_close($ch);
        throw new RuntimeException('Email failed');
    }

    curl_close($ch);
}
