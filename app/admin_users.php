<?php
declare(strict_types=1);

/* Bootstrap */
$APP_ROOT = dirname(__DIR__);

require_once $APP_ROOT . '/security/auth.php';
require_once $APP_ROOT . '/config/database.php';

requireAdmin();
requireVerified();

/* Fetch users */
$db = getDB();
$stmt = $db->query(
    'SELECT id, email, username, role, is_verified, is_active
     FROM users
     ORDER BY created_at DESC'
);

$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Management – EasyVault</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>

<div class="page-center">

    <!-- Brand -->
    <div class="brand">
        <h1>EasyVault 🛡️</h1>
        <p class="brand-sub">
            User Management • Kurdistan 
        </p>
    </div>

    <!-- User Management Card -->
    <div class="card" style="max-width:1100px;">

        <div class="card-header">
            <h2>User Management</h2>
            <p>Manage user roles, verification status, and access</p>
        </div>

        <div class="card-body">

            <table>
                <thead>
                    <tr>
                        <th>Email</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Verified</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>

                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= htmlspecialchars($u['username']) ?></td>
                        <td>
                            <strong><?= htmlspecialchars($u['role']) ?></strong>
                        </td>
                        <td>
                            <?= $u['is_verified'] ? '✅ Verified' : '❌ Unverified' ?>
                        </td>
                        <td>
                            <?= $u['is_active'] ? 'Active' : 'Disabled' ?>
                        </td>
                        <td style="white-space:nowrap;">

                        <?php if ($u['id'] !== $_SESSION['user_id']): ?>

                            <!-- ROLE MANAGEMENT -->
                            <?php if ($u['role'] === 'admin'): ?>
                                <form method="post"
                                      action="/admin_user_update.php"
                                      style="display:inline"
                                      onsubmit="return confirm('Demote this admin to a normal user?');">
                                    <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                                    <input type="hidden" name="action" value="demote">
                                    <button type="submit" class="secondary">
                                        Demote
                                    </button>
                                </form>
                            <?php else: ?>
                                <form method="post"
                                      action="/admin_user_update.php"
                                      style="display:inline"
                                      onsubmit="return confirm('Promote this user to admin?');">
                                    <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                                    <input type="hidden" name="action" value="promote">
                                    <button type="submit" class="btn-primary" style="width:auto;">
                                        Promote
                                    </button>
                                </form>
                            <?php endif; ?>

                            <!-- ACCOUNT STATUS -->
                            <?php if ($u['is_active']): ?>
                                <form method="post"
                                      action="/admin_user_update.php"
                                      style="display:inline; margin-left:6px;"
                                      onsubmit="return confirm('Disable this account?');">
                                    <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                                    <input type="hidden" name="action" value="disable">
                                    <button type="submit" class="danger">
                                        Disable
                                    </button>
                                </form>
                            <?php else: ?>
                                <form method="post"
                                      action="/admin_user_update.php"
                                      style="display:inline; margin-left:6px;"
                                      onsubmit="return confirm('Enable this account?');">
                                    <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                                    <input type="hidden" name="action" value="enable">
                                    <button type="submit" class="secondary">
                                        Enable
                                    </button>
                                </form>
                            <?php endif; ?>

                        <?php else: ?>
                            <em>Current admin</em>
                        <?php endif; ?>

                        </td>
                    </tr>
                <?php endforeach; ?>

                </tbody>
            </table>

        </div>

        <div class="card-footer">
            <a href="/admin_dashboard.php">⬅ Back to Admin Dashboard</a>
        </div>

    </div>

</div>

</body>
</html>
