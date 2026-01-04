<?php
declare(strict_types=1);

$APP_ROOT = dirname(__DIR__);

require_once $APP_ROOT . '/security/auth.php';
require_once $APP_ROOT . '/config/database.php';

requireAdmin();
requireVerified();

$db = getDB();

$stmt = $db->query(
    'SELECT a.created_at,
            a.action,
            a.ip_address,
            u1.email AS actor,
            u2.email AS target
     FROM audit_logs a
     JOIN users u1 ON a.actor_user_id = u1.id
     LEFT JOIN users u2 ON a.target_user_id = u2.id
     ORDER BY a.created_at DESC
     LIMIT 100'
);

$logs = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Audit Logs – Admin</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>

<h1>Audit Logs 📊</h1>

<p>Recent administrative and security-related actions.</p>

<?php if (empty($logs)): ?>
    <p><em>No audit events recorded yet.</em></p>
<?php else: ?>
<table border="1" cellpadding="8">
    <tr>
        <th>Time</th>
        <th>Action</th>
        <th>Actor</th>
        <th>Target</th>
        <th>IP Address</th>
    </tr>

<?php foreach ($logs as $log): ?>
    <tr>
        <td><?= htmlspecialchars($log['created_at']) ?></td>
        <td><?= htmlspecialchars($log['action']) ?></td>
        <td><?= htmlspecialchars($log['actor']) ?></td>
        <td><?= htmlspecialchars($log['target'] ?? '-') ?></td>
        <td><?= htmlspecialchars($log['ip_address']) ?></td>
    </tr>
<?php endforeach; ?>
</table>
<?php endif; ?>

<br>

<a href="/admin_dashboard.php">⬅ Back to Admin Dashboard</a>

</body>
</html>
