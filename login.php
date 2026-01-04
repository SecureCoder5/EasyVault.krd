<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../security/crypto.php';

$error = '';
$showVerifyLink = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $error = 'Invalid credentials';
    } else {
        $db = getDB();

        $stmt = $db->prepare(
            'SELECT id, password_hash, role, is_verified, is_active
             FROM users WHERE email = :email'
        );
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if (
            !$user ||
            !verifyPassword($password, $user['password_hash'])
        ) {
            $error = 'Invalid credentials';
        } elseif ((int)$user['is_active'] !== 1) {
            $error = 'Account disabled';
        } elseif ((int)$user['is_verified'] !== 1) {
            $error = 'Please verify your email first.';
            $showVerifyLink = true;
        } else {
            session_regenerate_id(true);

            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['user_verified'] = true;

            header('Location: /dashboard.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login – EasyVault</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>

<div class="page-center">

    <!-- Brand / Header -->
    <div class="brand">
        <h1>EasyVault.KRD 🔐</h1>
        <p class="brand-sub">
            Secure Password Vault • Kurdistan 
        </p>
    </div>

    <!-- Login Card -->
    <div class="card auth-card">

        <div class="card-header">
            <h2>Login</h2>
            <p>Access your secure vault</p>
        </div>

        <div class="card-body">

            <?php if ($error): ?>
                <div class="alert error">
                    <?= htmlspecialchars($error) ?>
                    <?php if ($showVerifyLink): ?>
                        <br>
                        <a href="/verify.php">Verify your email</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <form method="post">

                <label for="email">Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    required
                    placeholder="you@example.com"
                >

                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    placeholder="••••••••"
                >

                <button type="submit" class="btn-primary">
                    Login
                </button>
            </form>

        </div>

        <div class="card-footer">
            <a href="/signup.php">Create account</a>
            <span class="divider">|</span>
            <a href="/forgot_password.php">Forgot password?</a>
        </div>

    </div>

</div>

</body>
</html>
