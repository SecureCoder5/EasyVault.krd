<?php
declare(strict_types=1);

function hibpCheckEmail(string $email): array
{
    $apiKey = $_ENV['HIBP_API_KEY'] ?? getenv('HIBP_API_KEY');
    if (!$apiKey) {
        throw new RuntimeException('HIBP API key missing');
    }

    $url = 'https://haveibeenpwned.com/api/v3/breachedaccount/' . urlencode($email);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'hibp-api-key: ' . $apiKey,
            'User-Agent: EasyVault.krd'
        ],
        CURLOPT_TIMEOUT => 10
    ]);

    $response = curl_exec($ch);
    $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($status === 404) return [];
    if ($status !== 200) throw new RuntimeException('HIBP error');

    return json_decode($response, true, 512, JSON_THROW_ON_ERROR);
}
