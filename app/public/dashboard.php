<?php
declare(strict_types=1);

require_once __DIR__ . '/../security/auth.php';
require_once __DIR__ . '/../lib/hibp_email.php';
require_once __DIR__ . '/../lib/hibp_password.php';
require_once __DIR__ . '/../lib/password_strength.php';

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

/* -------------------------------
   Password Security Checker Logic
-------------------------------- */
$passwordResult = null;
$passwordError  = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['check_password'])) {

    $password = $_POST['password'] ?? '';

    if (strlen($password) < 6) {
        $passwordError = 'Password is too short to analyse.';
    } else {
        try {
            $breached = isPasswordBreached($password);
            $strength = passwordStrength($password);
            $crack    = estimateCrackTime($password);

            $passwordResult = [
                'breached' => $breached,
                'score'    => $strength['score'],
                'label'    => $strength['label'],
                'entropy'  => $crack['entropy'],
                'online'   => $crack['online'],
                'offline'  => $crack['offline'],
            ];
        } catch (Exception $e) {
            $passwordError = 'Password analysis service is unavailable.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Security Tools – EasyVault</title>
    <link rel="stylesheet" href="/assets/style.css">
    <style>
        .card {
            background: #fff;
            border-radius: 10px;
            padding: 22px;
            margin-bottom: 24px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            max-width: 650px;
        }
        .card h3 {
            margin-top: 0;
        }
        .success { color: #1a7f37; }
        .error { color: #b00020; }
        .warning { color: #b45309; }
        ul { margin-top: 10px; }
    </style>
</head>
<body>

<h1>Security Tools</h1>

<nav style="margin-bottom: 25px;">
    <a href="/user_dashboard.php">🔐 My Vault</a> |
    <a href="/dashboard.php">🛡️ Security Tools</a> |
    <a href="/logout.php">🚪 Logout</a>
</nav>

<!-- ===============================
     EMAIL BREACH CHECKER
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
                        (<?= htmlspecialchars($breach['BreachDate'] ?? 'Date not disclosed') ?>)
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- ===============================
     PASSWORD SECURITY CHECKER
================================== -->
<div class="card">
    <h3>🔐 Password Security Checker</h3>
    <p>
        Check whether a password appears in known wordlists and estimate
        how long it would take to crack. Passwords are never stored or logged.
    </p>

    <form method="POST">
        <input
            type="password"
            name="password"
            placeholder="Enter password to test"
            required
        >
        <button type="submit" name="check_password">Check Password</button>
    </form>

    <?php if ($passwordError): ?>
        <p class="error"><?= htmlspecialchars($passwordError) ?></p>
    <?php endif; ?>

    <?php if ($passwordResult): ?>

        <?php if ($passwordResult['breached']): ?>
            <p class="error">⚠️ This password was found in known breaches.</p>
        <?php else: ?>
            <p class="success">✅ This password was not found in known breaches.</p>
        <?php endif; ?>

        <ul>
            <li><strong>Strength:</strong> <?= $passwordResult['label'] ?> (<?= $passwordResult['score'] ?>/100)</li>
            <li><strong>Entropy:</strong> <?= $passwordResult['entropy'] ?> bits</li>
            <li><strong>Online attack:</strong> <?= $passwordResult['online'] ?></li>
            <li><strong>Offline attack:</strong> <?= $passwordResult['offline'] ?></li>
        </ul>

    <?php endif; ?>
</div>

</body>
</html>
