<?php
declare(strict_types=1);

function isPasswordBreached(string $password): bool
{
    $hash   = strtoupper(sha1($password));
    $prefix = substr($hash, 0, 5);
    $suffix = substr($hash, 5);

    $url = "https://api.pwnedpasswords.com/range/$prefix";

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERAGENT => 'EasyVault.krd',
        CURLOPT_TIMEOUT => 10
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    foreach (explode("\n", $response) as $line) {
        [$hashSuffix] = explode(':', trim($line));
        if ($hashSuffix === $suffix) return true;
    }
    return false;
}
