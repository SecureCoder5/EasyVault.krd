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

    if ($email === '' || $password === '') {
        $error = 'Invalid credentials';
    } else {
        $db = getDB();

        // 🔐 AUTHENTICATION MUST BE ROLE-AGNOSTIC
        $stmt = $db->prepare(
            'SELECT id, password_hash, role, is_verified, is_active
             FROM users
             WHERE email = :email
             LIMIT 1'
        );
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if (
            !$user ||
            !password_verify($password, $user['password_hash'])
        ) {
            $error = 'Invalid credentials';

        } elseif ((int)$user['is_active'] !== 1) {
            $error = 'Account disabled';

        } elseif ((int)$user['is_verified'] !== 1) {
            $error = 'Please verify your email first.';
            $showVerifyLink = true;

        } else {

            /* -------------------------------
               SESSION + VAULT KEY DERIVATION
            -------------------------------- */

            session_regenerate_id(true);

            // Application-level secret (Railway ENV)
            $appKey = $_ENV['APP_KEY'] ?? null;

            if (!$appKey || strlen($appKey) < 16) {
                throw new RuntimeException('APP_KEY missing or too weak');
            }

            /**
             * Derive vault key using:
             * - User password
             * - APP_KEY
             * - PBKDF2 SHA-256 (100k rounds)
             * - 32 bytes output (AES-256)
             */
            $_SESSION['vault_key'] = hash_pbkdf2(
                'sha256',
                $password,
                $appKey,
                100000,
                32,
                true
            );

            // Auth session data
            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['user_verified'] = true;

            // ✅ AUTHORIZATION HAPPENS AFTER LOGIN
            if ($user['role'] === 'admin') {
                header('Location: /admin_dashboard.php');
            } else {
                header('Location: /user_dashboard.php');
            }
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

    <!-- Brand -->
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

            <form method="post" novalidate>

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
            
        </div>

    </div>

</div>

</body>
</html>
