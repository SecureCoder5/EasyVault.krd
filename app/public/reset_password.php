<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp      = $_POST['otp'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!$otp || !$password) {
        $error = 'All fields are required.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } else {
        $db = getDB();

        $stmt = $db->prepare("
            SELECT id, user_id
            FROM password_resets
            WHERE otp = :otp
              AND used = 0
              AND expires_at > NOW()
        ");
        $stmt->execute(['otp' => $otp]);
        $row = $stmt->fetch();

        if (!$row) {
            $error = 'Invalid or expired OTP.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $db->prepare("
                UPDATE users SET password = :pw WHERE id = :uid
            ")->execute([
                'pw'  => $hash,
                'uid' => $row['user_id']
            ]);

            $db->prepare("
                UPDATE password_resets SET used = 1 WHERE id = :id
            ")->execute(['id' => $row['id']]);

            $success = true;
        }
    }
}
?>

<form method="POST">
    <input type="text" name="otp" required placeholder="6-digit OTP">
    <input type="password" name="password" required placeholder="New password">
    <button type="submit">Reset Password</button>
</form>

<?php if ($error): ?>
<p style="color:red"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<?php if ($success): ?>
<p>✔ Password reset successful. <a href="/login.php">Login</a></p>
<?php endif; ?>
