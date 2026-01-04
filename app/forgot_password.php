<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../security/token.php';
require_once __DIR__ . '/../lib/Mailer.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');

    if ($email) {
        $db = getDB();

        $stmt = $db->prepare('SELECT id FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user) {
            $token = generateToken();
            $hash = hashToken($token);

            $db->prepare(
                'INSERT INTO password_resets (user_id, token_hash, expires_at)
                 VALUES (:uid, :hash, DATE_ADD(NOW(), INTERVAL 15 MINUTE))'
            )->execute([
                'uid'  => $user['id'],
                'hash' => $hash,
            ]);

            $link = "http://localhost:8080/reset_password.php?token=$token";

            sendMail(
                $email,
                'Reset your EasyVault password',
                "<p>Click the link below to reset your password:</p>
                 <p><a href='$link'>$link</a></p>
                 <p>This link expires in 15 minutes.</p>"
            );
        }
    }

    // Always generic (anti-enumeration)
    $message = 'If the email exists, a reset link has been sent.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password – EasyVault</title>
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

    <!-- Forgot Password Card -->
    <div class="card auth-card">

        <div class="card-header">
            <h2>Forgot Password</h2>
            <p>Reset access to your account</p>
        </div>

        <div class="card-body">

            <?php if ($message): ?>
                <div class="alert success">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <form method="post">

                <label for="email">Email address</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    required
                    placeholder="you@example.com"
                >

                <button type="submit" class="btn-primary">
                    Send Reset Link
                </button>
            </form>

        </div>

        <div class="card-footer">
            <a href="/login.php">Back to login</a>
        </div>

    </div>

</div>

</body>
</html>
