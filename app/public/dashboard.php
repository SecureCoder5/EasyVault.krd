<?php
declare(strict_types=1);
session_start();

/**
 * Enforce authentication
 */
if (
    empty($_SESSION['user_id']) ||
    empty($_SESSION['username']) ||
    empty($_SESSION['role'])
) {
    header('Location: /login.php');
    exit;
}

$username = (string)$_SESSION['username'];
$role     = (string)$_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard – EasyVault</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>

<div class="box">
    <h2>Welcome, <?= htmlspecialchars($username) ?></h2>

    <?php if ($role === 'admin'): ?>
        <h3>Admin Dashboard</h3>
        <p>Manage users and system settings.</p>
    <?php else: ?>
        <h3>User Dashboard</h3>
        <p>Your password vault will appear here.</p>
    <?php endif; ?>

    <p>
        <a href="/logout.php">Logout</a>
    </p>
</div>

</body>
</html>
