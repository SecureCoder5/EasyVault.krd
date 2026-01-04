<?php
declare(strict_types=1);

/**
 * Generate secure random token (for email verification, resets)
 */
function generateToken(int $length = 32): string
{
    return bin2hex(random_bytes($length));
}

/**
 * Hash token before storing in DB
 */
function hashToken(string $token): string
{
    return hash('sha256', $token);
}

/**
 * Verify token against hash
 */
function verifyToken(string $token, string $hash): bool
{
    return hash_equals($hash, hashToken($token));
}

function generateOTP(int $digits = 6): string
{
    return str_pad(
        (string) random_int(0, 10 ** $digits - 1),
        $digits,
        '0',
        STR_PAD_LEFT
    );
}
