<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../security/crypto.php';
require_once __DIR__ . '/../security/token.php';
require_once __DIR__ . '/../lib/Mailer.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email    = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$username || !$password) {
        $error = 'All fields are required';
    } else {
        $db = getDB();

        $stmt = $db->prepare(
            'SELECT id FROM users WHERE email = :email OR username = :username'
        );
        $stmt->execute([
            'email' => $email,
            'username' => $username
        ]);

        if ($stmt->fetch()) {
            $error = 'Account already exists';
        } else {
            $passwordHash = hashPassword($password);

            $stmt = $db->prepare(
                'INSERT INTO users (email, username, password_hash, role, is_verified)
                 VALUES (:email, :username, :password, "user", 0)'
            );
            $stmt->execute([
                'email'    => $email,
                'username' => $username,
                'password' => $passwordHash,
            ]);

            $userId = (int)$db->lastInsertId();

            $token = generateOTP();
            $tokenHash = hashToken($token);

            $db->prepare(
                'INSERT INTO email_verifications (user_id, token_hash, expires_at)
                 VALUES (:uid, :token, DATE_ADD(NOW(), INTERVAL 15 MINUTE))'
            )->execute([
                'uid'   => $userId,
                'token' => $tokenHash,
            ]);

            sendMail(
                $email,
                'Your EasyVault verification code',
                "<p>Your verification code is:</p>
                 <h2>$token</h2>
                 <p>This code expires in 15 minutes.</p>"
            );

            $success = 'Account created successfully. Please verify your email.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Signup – EasyVault</title>
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

    <!-- Signup Card -->
    <div class="card auth-card">

        <div class="card-header">
            <h2>Create Account</h2>
            <p>Start securing your credentials</p>
        </div>

        <div class="card-body">

            <?php if ($error): ?>
                <div class="alert error">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert success">
                    <?= htmlspecialchars($success) ?>
                </div>

                <p style="text-align:center; margin-bottom:10px;">
                    <a href="/verify.php" class="btn-primary" style="display:inline-block; width:auto; padding:10px 20px;">
                        Verify Email
                    </a>
                </p>

                <p style="text-align:center; font-size:0.85rem; color:#64748b;">
                    Enter the 6-digit code sent to your email.
                </p>

            <?php else: ?>

                <form method="post">

                    <label for="email">Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        required
                        placeholder="you@example.com"
                    >

                    <label for="username">Username</label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        required
                        placeholder="yourusername"
                    >

                    <label for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        placeholder="Create a strong password"
                    >

                    <button type="submit" class="btn-primary">
                        Create Account
                    </button>
                </form>

            <?php endif; ?>

        </div>

        <div class="card-footer">
            <a href="/login.php">Back to login</a>
        </div>

    </div>

</div>

</body>
</html>
