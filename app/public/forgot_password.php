<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../lib/Mailer.php';

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        try {
            $db = getDB();

            // 1️⃣ Find user
            $stmt = $db->prepare('SELECT id FROM users WHERE email = :email AND is_active = 1');
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();

            if (!$user) {
                $error = 'If this email exists, an OTP will be sent.';
            } else {
                $userId = (int)$user['id'];

                // 2️⃣ Invalidate old OTPs
                $db->prepare(
                    'UPDATE password_resets SET used = 1 WHERE user_id = :uid'
                )->execute(['uid' => $userId]);

                // 3️⃣ Generate secure 6-digit OTP
                $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

                // 4️⃣ Expiry (15 minutes)
                $expiresAt = (new DateTime('+15 minutes'))->format('Y-m-d H:i:s');

                // 5️⃣ Store OTP
                $stmt = $db->prepare(
                    'INSERT INTO password_resets (user_id, otp, expires_at, used)
                     VALUES (:uid, :otp, :expires, 0)'
                );

                $stmt->execute([
                    'uid'     => $userId,
                    'otp'     => $otp,
                    'expires' => $expiresAt
                ]);

                // 6️⃣ Send email
                sendMail(
                    $email,
                    'EasyVault – Password Reset OTP',
                    "
                    <p>You requested a password reset.</p>
                    <p><strong>Your OTP code:</strong></p>
                    <h2 style='letter-spacing:2px;'>$otp</h2>
                    <p>This code expires in <strong>15 minutes</strong>.</p>
                    <p>If you didn’t request this, ignore this email.</p>
                    "
                );

                $success = 'OTP has been sent to your email.';
            }
        } catch (Throwable $e) {
            error_log('Forgot password error: ' . $e->getMessage());
            $error = 'Something went wrong. Please try again later.';
        }
    }
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

<div class="auth-container">
    <div class="auth-card">
        <div class="brand">
            <h1>EasyVault.KRD 🔐</h1>
            <p class="subtitle">Secure Password Vault · Kurdistan</p>
        </div>

        <h2>Forgot Password</h2>
        <p class="helper-text">
            Enter your email address and we’ll send you a one-time code.
        </p>

        <?php if ($error): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert success"><?= htmlspecialchars($success) ?></div>
            <a href="/reset_password.php" class="btn-primary" style="margin-top:15px;">
                Enter OTP
            </a>
        <?php else: ?>
            <form method="POST" novalidate>
                <label for="email">Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    required
                    placeholder="you@example.com"
                >

                <button type="submit" class="btn-primary">
                    Send OTP
                </button>
            </form>
        <?php endif; ?>

        <div class="auth-footer">
            <a href="/login.php">Back to login</a>
        </div>
    </div>
</div>

</body>
</html>
