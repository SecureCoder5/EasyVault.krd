<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../security/crypto.php';

$error = '';
$success = '';

$emailPrefill = trim($_GET['email'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $otp = trim($_POST['otp'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$otp || !$password) {
        $error = 'All fields are required.';
    } else {
        try {
            $db = getDB();

            $stmt = $db->prepare(
                'SELECT id, reset_otp_hash, reset_otp_expires
                 FROM users
                 WHERE email = :email AND is_active = 1'
            );
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();

            if (
                !$user ||
                !$user['reset_otp_hash'] ||
                strtotime($user['reset_otp_expires']) < time() ||
                !password_verify($otp, $user['reset_otp_hash'])
            ) {
                $error = 'Invalid or expired OTP.';
            } else {

                // Update password
                $newHash = hashPassword($password);

                $stmt = $db->prepare(
                    'UPDATE users
                     SET password_hash = :hash,
                         reset_otp_hash = NULL,
                         reset_otp_expires = NULL
                     WHERE id = :id'
                );
                $stmt->execute([
                    'hash' => $newHash,
                    'id' => $user['id']
                ]);

                $success = 'Password updated successfully. You may now log in.';
            }
        } catch (Throwable $e) {
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

<div class="page-center">

    <div class="brand">
        <h1>EasyVault.KRD 🔐</h1>
        <p class="brand-sub">Secure Password Vault • Kurdistan</p>
    </div>

    <div class="card auth-card">

        <div class="card-header">
            <h2>Reset Password</h2>
            <p>Enter OTP and new password</p>
        </div>

        <div class="card-body">

            <?php if ($error): ?>
                <div class="alert error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert success">
                    <?= htmlspecialchars($success) ?>
                    <br><br>
                    <a href="/login.php">Go to Login</a>
                </div>
            <?php endif; ?>

            <form method="post" novalidate>

                <label>Email</label>
                <input
                    type="email"
                    name="email"
                    value="<?= htmlspecialchars($emailPrefill) ?>"
                    required
                >

                <label>OTP Code</label>
                <input
                    type="text"
                    name="otp"
                    required
                    placeholder="6-digit code"
                >

                <label>New Password</label>
                <input
                    type="password"
                    name="password"
                    required
                >

                <button type="submit" class="btn-primary">
                    Reset Password
                </button>

            </form>

        </div>

    </div>

</div>

</body>
</html>
