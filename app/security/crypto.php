<?php
declare(strict_types=1);

/**
 * Hash user passwords securely
 */
function hashPassword(string $password): string
{
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify user password
 */
function verifyPassword(string $password, string $hash): bool
{
    return password_verify($password, $hash);
}

/**
 * Encrypt sensitive vault data (AES-256-GCM)
 */
function encryptVaultData(string $plaintext): array
{
    $key = $_ENV['VAULT_KEY'] ?? '';

    if (strlen($key) < 32) {
        throw new RuntimeException('Invalid vault key');
    }

    $iv = random_bytes(12); // GCM standard
    $tag = '';

    $ciphertext = openssl_encrypt(
        $plaintext,
        'aes-256-gcm',
        $key,
        OPENSSL_RAW_DATA,
        $iv,
        $tag
    );

    if ($ciphertext === false) {
        throw new RuntimeException('Encryption failed');
    }

    return [
        'ciphertext' => base64_encode($ciphertext),
        'iv'         => base64_encode($iv),
        'tag'        => base64_encode($tag),
    ];
}

/**
 * Decrypt vault data
 */
function decryptVaultData(string $ciphertext, string $iv, string $tag): string
{
    $key = $_ENV['VAULT_KEY'] ?? '';

    if (strlen($key) < 32) {
        throw new RuntimeException('Invalid vault key');
    }

    $plaintext = openssl_decrypt(
        base64_decode($ciphertext),
        'aes-256-gcm',
        $key,
        OPENSSL_RAW_DATA,
        base64_decode($iv),
        base64_decode($tag)
    );

    if ($plaintext === false) {
        throw new RuntimeException('Decryption failed');
    }

    return $plaintext;
}
