<?php
declare(strict_types=1);

$APP_ROOT = dirname(__DIR__);
require_once $APP_ROOT . '/security/auth.php';

requireAdmin();
requireVerified();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard – EasyVault</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>

<div class="page-center">

    <!-- Brand -->
    <div class="brand">
        <h1>EasyVault 🛡️</h1>
        <p class="brand-sub">
            Administration Panel • Kurdistan 
        </p>
    </div>

    <!-- Admin Overview Card -->
    <div class="card">

        <div class="card-header">
            <h2>Admin Dashboard</h2>
            <p>Security & user management overview</p>
        </div>

        <div class="card-body">

            <div class="section">
                <h3>Admin Capabilities</h3>
                <ul style="margin-top:10px; line-height:1.8;">
                    <li>👥 Manage user accounts (roles & status)</li>
                    <li>📧 Monitor email verification activity</li>
                    <li>🔐 Enforce authentication & account security</li>
                    <li>📊 Review security audit logs</li>
                </ul>
            </div>

            <div class="section">
                <h3>Administration</h3>
                <ul style="margin-top:10px; line-height:1.8;">
                    <li>
                        <a href="/admin_users.php">👥 Manage Users</a>
                    </li>
                    <li>
                        <a href="/admin_audit_logs.php">📊 View Audit Logs</a>
                    </li>
                </ul>
            </div>

        </div>

        <div class="card-footer logout">
            <form method="post" action="/logout.php">
                <button type="submit" class="secondary">
                    Logout
                </button>
            </form>
        </div>

    </div>

</div>

</body>
</html>
