<?php
declare(strict_types=1);

/**
 * ================================
 * CRYPTO UTILITIES – EASYVAULT
 * ================================
 *
 * - Password hashing / verification
 * - Vault encryption / decryption (AES-256-GCM)
 */

/**
 * --------------------------------
 * PASSWORD HASHING (LOGIN SYSTEM)
 * --------------------------------
 */

function hashPassword(string $password): string
{
    return password_hash($password, PASSWORD_ARGON2ID);
}

function verifyPassword(string $password, string $hash): bool
{
    return password_verify($password, $hash);
}

/**
 * --------------------------------
 * VAULT ENCRYPTION (AES-256-GCM)
 * --------------------------------
 */

/**
 * Encrypt sensitive vault data
 *
 * @param string $plaintext
 * @return array{ciphertext: string, iv: string, tag: string}
 */
function encryptVaultData(string $plaintext): array
{
    if (
        empty($_SESSION['vault_key']) ||
        !is_string($_SESSION['vault_key']) ||
        strlen($_SESSION['vault_key']) !== 32
    ) {
        throw new RuntimeException('Invalid vault key');
    }

    $key = $_SESSION['vault_key'];

    // AES-GCM recommended IV size = 12 bytes
    $iv = random_bytes(12);
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
 * Decrypt sensitive vault data
 *
 * @param string $ciphertext
 * @param string $iv
 * @param string $tag
 * @return string
 */
function decryptVaultData(
    string $ciphertext,
    string $iv,
    string $tag
): string {
    if (
        empty($_SESSION['vault_key']) ||
        !is_string($_SESSION['vault_key']) ||
        strlen($_SESSION['vault_key']) !== 32
    ) {
        throw new RuntimeException('Invalid vault key');
    }

    $key = $_SESSION['vault_key'];

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
