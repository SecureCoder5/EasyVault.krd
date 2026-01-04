<?php
declare(strict_types=1);

function getDB(): PDO
{
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    // 1️⃣ Preferred: individual DB_* variables
    $host = getenv('DB_HOST');
    $port = getenv('DB_PORT') ?: '3306';
    $db   = getenv('DB_NAME');
    $user = getenv('DB_USER');
    $pass = getenv('DB_PASS');

    // 2️⃣ Fallback: DATABASE_URL (Railway-safe)
    if ((!$host || !$db || !$user || !$pass) && getenv('DATABASE_URL')) {
        $url = parse_url(getenv('DATABASE_URL'));

        $host = $url['host'] ?? null;
        $port = $url['port'] ?? '3306';
        $db   = ltrim($url['path'] ?? '', '/');
        $user = $url['user'] ?? null;
        $pass = $url['pass'] ?? null;
    }

    if (!$host || !$db || !$user || !$pass) {
        throw new RuntimeException(
            'Database configuration missing (env vars not injected into app service)'
        );
    }

    $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";

    $pdo = new PDO(
        $dsn,
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );

    return $pdo;
}
