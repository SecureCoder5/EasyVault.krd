<?php
declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);


require __DIR__ . '/app/config/database.php';


if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}


if (!empty($_SESSION['user_id'])) {
    header('Location: /app/dashboard.php');
    exit;
}

header('Location: /app/login.php');
exit;
