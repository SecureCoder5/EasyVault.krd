<?php
declare(strict_types=1);

/* Bootstrap (DO NOT HARDCODE PATHS) */
$APP_ROOT = dirname(__DIR__);

require_once $APP_ROOT . '/security/auth.php';
require_once $APP_ROOT . '/security/audit.php';
require_once $APP_ROOT . '/config/database.php';

requireAdmin();
requireVerified();

/* Request validation */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

$userId = (int)($_POST['user_id'] ?? 0);
$action = $_POST['action'] ?? '';

if ($userId <= 0 || !$action) {
    http_response_code(400);
    exit('Invalid request');
}

/* Prevent self modification */
if ($userId === (int)$_SESSION['user_id']) {
    http_response_code(403);
    exit('You cannot modify your own account');
}

$db = getDB();

/* admin actions */
switch ($action) {

    case 'promote':
        $db->prepare(
            "UPDATE users SET role = 'admin' WHERE id = :id"
        )->execute(['id' => $userId]);

        auditLog(
            (int)$_SESSION['user_id'],
            'PROMOTE_USER_TO_ADMIN',
            $userId
        );
        break;

    case 'demote':
        $db->prepare(
            "UPDATE users SET role = 'user' WHERE id = :id"
        )->execute(['id' => $userId]);

        auditLog(
            (int)$_SESSION['user_id'],
            'DEMOTE_ADMIN_TO_USER',
            $userId
        );
        break;

    case 'disable':
        $db->prepare(
            "UPDATE users SET is_active = 0 WHERE id = :id"
        )->execute(['id' => $userId]);

        auditLog(
            (int)$_SESSION['user_id'],
            'DISABLE_USER_ACCOUNT',
            $userId
        );
        break;

    case 'enable':
        $db->prepare(
            "UPDATE users SET is_active = 1 WHERE id = :id"
        )->execute(['id' => $userId]);

        auditLog(
            (int)$_SESSION['user_id'],
            'ENABLE_USER_ACCOUNT',
            $userId
        );
        break;

    default:
        http_response_code(400);
        exit('Unknown action');
}

/* Redirect back to admin users page */
header('Location: /admin_users.php');
exit;
