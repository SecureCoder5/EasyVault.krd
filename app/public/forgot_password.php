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

            // Check user exists & active
            $stmt = $db->prepare(
                'SELECT id FROM users WHERE email = :email AND is_active = 1'
            );
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();

            if (!$user) {
                // Do NOT reveal existence
                $success = 'If the email exists, an OTP has been sent.';
            } else {

                // Generate OTP
                $otp = random_int(100000, 999999);
                $otpHash = password_hash((string)$otp, PASSWORD_DEFAULT);
                $expires = date('Y-m-d H:i:s', time() + 600); // 10 minutes

                // Store OTP
                $stmt = $db->prepare(
                    'UPDATE users
                     SET reset_otp_hash = :hash,
                         reset_otp_expires = :expires
                     WHERE id = :id'
                );
                $stmt->execute([
                    'hash' => $otpHash,
                    'expires' => $expires,
                    'id' => $user['id']
                ]);

                // Send email
                sendMail(
                    $email,
                    'EasyVault Password Reset OTP',
                    "Your EasyVault password reset code is:\n\n$otp\n\nThis code expires in 10 minutes."
                );

                $success = 'OTP sent to your email address.';
            }
        } catch (Throwable $e) {
           
            $error = 'error='

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
