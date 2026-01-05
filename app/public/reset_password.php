<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../security/crypto.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp      = trim($_POST['otp'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!preg_match('/^[0-9]{6}$/', $otp)) {
        $error = 'Invalid OTP format.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } else {
        try {
            $db = getDB();

            // 1️⃣ Find valid OTP
            $stmt = $db->prepare(
                'SELECT * FROM password_resets
                 WHERE otp = :otp
                   AND used = 0
                   AND expires_at > NOW()
                 LIMIT 1'
            );
            $stmt->execute(['otp' => $otp]);
            $reset = $stmt->fetch();

            if (!$reset) {
                $error = 'Invalid or expired OTP.';
            } else {
                $userId = (int)$reset['user_id'];

                // 2️⃣ Update password
                $stmt = $db->prepare(
                    'UPDATE users SET password_hash = :hash WHERE id = :uid'
                );
                $stmt->execute([
                    'hash' => hashPassword($password),
                    'uid'  => $userId
                ]);

                // 3️⃣ Mark OTP as used
                $stmt = $db->prepare(
                    'UPDATE password_resets SET used = 1 WHERE id = :id'
                );
                $stmt->execute(['id' => $reset['id']]);

                $success = 'Your password has been reset successfully.';
            }
        } catch (Throwable $e) {
            error_log('Reset password error: ' . $e->getMessage());
            $error = 'Something went wrong. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password – EasyVault</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>

<div class="auth-container">
    <div class="auth-card">
        <div class="brand">
            <h1>EasyVault.KRD 🔐</h1>
            <p class="subtitle">Secure Password Vault · Kurdistan</p>
        </div>

        <h2>Reset Password</h2>
        <p class="helper-text">
            Enter the 6-digit code sent to your email and choose a new password.
        </p>

        <?php if ($error): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert success"><?= htmlspecialchars($success) ?></div>
            <a href="/login.php" class="btn-primary" style="margin-top:15px;">
                Go to Login
            </a>
        <?php else: ?>
            <form method="POST" novalidate>
                <label for="otp">OTP Code</label>
                <input
                    type="text"
                    id="otp"
                    name="otp"
                    maxlength="6"
                    pattern="[0-9]{6}"
                    placeholder="123456"
                    required
                >

                <label for="password">New Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    placeholder="At least 8 characters"
                >

                <button type="submit" class="btn-primary">
                    Reset Password
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
