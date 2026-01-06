<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../lib/Mailer.php';

$error = '';
$success = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        try {
            $db = getDB();

            // Find active user (do NOT reveal existence)
            $stmt = $db->prepare(
                'SELECT id FROM users WHERE email = :email AND is_active = 1 LIMIT 1'
            );
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();

            // Always show success message
            $success = 'If the email exists, an OTP has been sent.';

            if ($user) {
                // Generate OTP
                $otp = random_int(100000, 999999);

                // Hash OTP (never store raw)
                $otpHash = password_hash((string)$otp, PASSWORD_DEFAULT);

                // Expiry (10 minutes)
                $expiresAt = date('Y-m-d H:i:s', time() + 600);

                // Invalidate previous reset attempts
                $db->prepare(
                    'UPDATE password_resets
                     SET used = 1
                     WHERE user_id = :uid'
                )->execute(['uid' => $user['id']]);

                // Store new reset request
                $stmt = $db->prepare(
                    'INSERT INTO password_resets
                        (user_id, token_hash, otp, expires_at, used, created_at)
                     VALUES
                        (:uid, :token, :otp, :expires, 0, NOW())'
                );

                $stmt->execute([
                    'uid'     => $user['id'],
                    'token'   => $otpHash,
                    'otp'     => $otp,
                    'expires' => $expiresAt
                ]);

                // Send OTP email
                $emailBody = "
<h2>EasyVault Password Reset</h2>
<p>Your one-time password reset code is:</p>
<h1 style='letter-spacing:3px;'>$otp</h1>
<p>This code expires in <strong>10 minutes</strong>.</p>
<p>If you did not request this, you can ignore this email.</p>
";

if (!sendMail($email, 'EasyVault Password Reset OTP', $emailBody, true)) {
    throw new RuntimeException('Failed to send OTP email');
}
;
            }

        } catch (Throwable $e) {
            error_log('[FORGOT PASSWORD ERROR] ' . $e->getMessage());
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

<div class="page-center">

    <div class="brand">
        <h1>EasyVault.KRD 🔐</h1>
        <p class="brand-sub">Secure Password Vault • Kurdistan</p>
    </div>

    <div class="card auth-card">

        <div class="card-header">
            <h2>Forgot Password</h2>
            <p>We’ll send you a one-time code</p>
        </div>

        <div class="card-body">

            <?php if ($error): ?>
                <div class="alert error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert success">
                    <?= htmlspecialchars($success) ?>
                    <br><br>
                    <a
                        href="/reset_password.php?email=<?= urlencode($email) ?>"
                        class="btn-outline-success"
                    >
                        Continue to Reset Password
                    </a>
                </div>
            <?php endif; ?>

            <form method="post" novalidate>
                <label>Email</label>
                <input
                    type="email"
                    name="email"
                    required
                    placeholder="you@example.com"
                >

                <button type="submit" class="btn-primary">
                    Send OTP
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
