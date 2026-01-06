<?php
declare(strict_types=1);

require_once __DIR__ . '/../security/auth.php';
require_once __DIR__ . '/../lib/hibp_email.php';

requireLogin();

/* Redirect admins */
if (($_SESSION['role'] ?? '') === 'admin') {
    header('Location: /admin_dashboard.php');
    exit;
}

/* -------------------------------
   Email Breach Checker Logic
-------------------------------- */
$emailResult = null;
$emailError  = null;


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['check_email'])) {

    // Simple rate limit (10 seconds)
    $_SESSION['email_check_last'] ??= 0;
    if (time() - $_SESSION['email_check_last'] < 10) {
        $emailError = 'Please wait a few seconds before checking again.';
    } else {
        $_SESSION['email_check_last'] = time();

        $email = trim($_POST['email'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emailError = 'Invalid email address.';
        } else {
            try {
                $emailResult = hibpCheckEmail($email);
            } catch (Exception $e) {
                $emailError = 'Breach check service is currently unavailable.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard – EasyVault</title>
    <link rel="stylesheet" href="/assets/style.css">
    <style>
        .card {
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
            max-width: 600px;
        }
        .card h3 {
            margin-top: 0;
        }
        .success {
            color: #1a7f37;
        }
        .error {
            color: #b00020;
        }
        .warning {
            color: #b45309;
        }
    </style>
</head>
<body>

<h1>Welcome to EasyVault</h1>
    <nav style="margin-bottom: 20px;">
    <a href="/user_dashboard.php">🔐 My Vault</a> |
    <a href="/add_vault.php">➕ Add Password</a> |
    <a href="/dashboard.php">🛡️ Security Tools</a> |
    <a href="/logout.php">🚪 Logout</a>
</nav>


<!-- ===============================
     EMAIL BREACH CHECKER CARD
================================== -->
<div class="card">
    <h3>🔍 Email Breach Checker</h3>
    <p>
        Check if an email address has appeared in known data breaches.
        This tool does not store or log the email address.
    </p>

    <form method="POST">
        <input
            type="email"
            name="email"
            placeholder="you@example.com"
            required
            style="width: 100%; padding: 8px; margin-bottom: 10px;"
        >
        <button type="submit" name="check_email">Check Email</button>
    </form>

    <?php if ($emailError): ?>
        <p class="error"><?= htmlspecialchars($emailError) ?></p>
    <?php endif; ?>

    <?php if (is_array($emailResult)): ?>
        <?php if (empty($emailResult)): ?>
            <p class="success">✅ No breaches found for this email.</p>
        <?php else: ?>
            <p class="warning">
                ⚠️ This email appeared in <?= count($emailResult) ?> known breach(es):
            </p>
            <ul>
                <?php foreach ($emailResult as $breach): ?>
                    <li>
    <strong><?= htmlspecialchars($breach['Name']) ?></strong>
    <?php if (!empty($breach['BreachDate'])): ?>
        (<?= htmlspecialchars($breach['BreachDate']) ?>)
    <?php else: ?>
        (Date not disclosed)
    <?php endif; ?>
</li>

                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- You can add more cards here later -->
<!-- Vault summary, security score, recent activity, etc. -->

</body>
</html>
