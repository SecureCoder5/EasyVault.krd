<?php
declare(strict_types=1);

function getDB(): PDO
{
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    $host = $_ENV['DB_HOST'] ?? '';
    $db   = $_ENV['DB_NAME'] ?? '';
    $user = $_ENV['DB_USER'] ?? '';
    $pass = $_ENV['DB_PASS'] ?? '';

    if (!$host || !$db || !$user) {
        throw new RuntimeException('Database configuration missing');
    }

    $dsn = "mysql:host={$host};dbname={$db};charset=utf8mb4";

    try {
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
    } catch (PDOException $e) {
        // This part ensures error messages are not exposed to users
        throw new RuntimeException('Database connection failed');
    }

    return $pdo;
}
