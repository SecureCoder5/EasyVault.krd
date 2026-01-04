<?php

function sendMail(string $to, string $subject, string $html): void
{
    $apiKey = getenv('MAILGUN_API_KEY');
    $domain = getenv('MAILGUN_DOMAIN');

    if (!$apiKey || !$domain) {
        throw new RuntimeException('Mailgun config missing');
    }

    $url = "https://api.mailgun.net/v3/{$domain}/messages";

    $postData = [
        'from'    => "EasyVault.krd <postmaster@{$domain}>",
        'to'      => $to,
        'subject' => $subject,
        'html'    => $html,
    ];

    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_HTTPAUTH       => CURLAUTH_BASIC,
        CURLOPT_USERPWD        => "api:{$apiKey}",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $postData,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($response === false || $httpCode >= 400) {
        error_log("Mailgun error ({$httpCode}): " . curl_error($ch));
        curl_close($ch);
        throw new RuntimeException('Email failed');
    }

    curl_close($ch);
}
