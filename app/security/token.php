<?php
declare(strict_types=1);

/**
 * Generate a cryptographically secure random token.
 * Default: 32 bytes = 64 hex chars.
 */
function generateToken(int $bytes = 32): string
{
    return bin2hex(random_bytes($bytes));
}

/**
 * Hash a token for safe database storage.
 * Uses one-way hashing (bcrypt).
 */
function hashToken(string $token): string
{
    return password_hash($token, PASSWORD_DEFAULT);
}

/**
 * Verify a raw token against its stored hash.
 */
function verifyToken(string $token, string $hash): bool
{
    return password_verify($token, $hash);
}

/**
 * Generate an expiration timestamp (UTC).
 */
function tokenExpiresIn(int $minutes): string
{
    return gmdate('Y-m-d H:i:s', time() + ($minutes * 60));
}
