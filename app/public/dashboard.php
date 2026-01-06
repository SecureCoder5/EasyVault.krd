<?php
declare(strict_types=1);

require_once __DIR__ . '/../security/auth.php';
require_once __DIR__ . '/../lib/hibp_email.php';


requireLogin();

if (($_SESSION['role'] ?? '') === 'admin') {
    header('Location: /admin_dashboard.php');
    exit;
}

/* Normal user */
header('Location: /user_dashboard.php');
exit;
