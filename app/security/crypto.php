<?php
declare(strict_types=1);

/**
 * =========================
 * PASSWORD HANDLING
 * =========================
 */

/**
 * Hash a plaintext password securely.
 */
function hashPassword(string $password): string
{
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify a plaintext password against its hash.
 */
function verifyPassword(string $password, string $hash): bool
{
    return password_verify($password, $hash);
}

/**
 * =========================
 * VAULT ENCRYPTION (AES-256-GCM)
 * =========================
 */

/**
 * Derive a 256-bit encryption key from the master secret.
 */
function deriveEncryptionKey(): string
{
    $masterKey = getenv('APP_KEY');

    if (!$masterKey) {
        throw new RuntimeException('APP_KEY is not set.');
    }

    // Derive a fixed-length 32-byte key
    return hash('sha256', $masterKey, true);
}

/**
 * Encrypt sensitive data.
 */
function encryptSecret(string $plaintext): array
{
    $key = deriveEncryptionKey();
    $iv  = random_bytes(12); // Recommended size for GCM
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
        throw new RuntimeException('Encryption failed.');
    }

    return [
        'ciphertext' => base64_encode($ciphertext),
        'iv'         => base64_encode($iv),
        'tag'        => base64_encode($tag),
    ];
}

/**
 * Decrypt encrypted data.
 */
function decryptSecret(string $ciphertext, string $iv, string $tag): string
{
    $key = deriveEncryptionKey();

    $plaintext = openssl_decrypt(
        base64_decode($ciphertext),
        'aes-256-gcm',
        $key,
        OPENSSL_RAW_DATA,
        base64_decode($iv),
        base64_decode($tag)
    );

    if ($plaintext === false) {
        throw new RuntimeException('Decryption failed.');
    }

    return $plaintext;
}
