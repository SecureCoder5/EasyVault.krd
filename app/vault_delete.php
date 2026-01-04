<?php
declare(strict_types=1);

require_once __DIR__ . '/../security/auth.php';
require_once __DIR__ . '/../config/database.php';

requireLogin();
requireVerified();

$id = (int)($_POST['id'] ?? 0);

$db = getDB();
$stmt = $db->prepare(
    'DELETE FROM vault_items WHERE id = :id AND user_id = :uid'
);

$stmt->execute([
    'id'  => $id,
    'uid' => $_SESSION['user_id'],
]);

header('Location: /user_dashboard.php');
exit;
