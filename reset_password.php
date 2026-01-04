<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../security/token.php';
require_once __DIR__ . '/../security/crypto.php';

$token = $_GET['token'] ?? '';
$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $token = $_POST['token'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!$token || !$password) {
        $error = 'Invalid reset request.';
    } else {

        $db = getDB();
        $hash = hashToken($token);

        $stmt = $db->prepare(
            'SELECT id, user_id FROM password_resets
             WHERE token_hash = :hash
               AND used = 0
               AND expires_at > NOW()'
        );
        $stmt->execute(['hash' => $hash]);
        $row = $stmt->fetch();

        if (!$row) {
            $error = 'Invalid or expired reset link.';
        } else {
            $db->prepare(
                'UPDATE users SET password_hash = :pw WHERE id = :uid'
            )->execute([
                'pw'  => hashPassword($password),
                'uid' => $row['user_id'],
            ]);

            $db->prepare(
                'UPDATE password_resets SET used = 1 WHERE id = :id'
            )->execute(['id' => $row['id']]);

            $success = true;
        }
    }
}

if (!$token && !$success) {
    $error = 'Invalid reset request.';
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

    <!-- Brand -->
    <div class="brand">
        <h1>EasyVault 🔐</h1>
        <p class="brand-sub">
            Secure Password Vault • Kurdistan 
        </p>
    </div>

    <!-- Reset Password Card -->
    <div class="card auth-card">

        <div class="card-header">
            <h2>Reset Password</h2>
            <p>Choose a new secure password</p>
        </div>

        <div class="card-body">

            <?php if ($success): ?>

                <div class="alert success">
                    Your password has been reset successfully.
                </div>

                <p style="text-align:center; margin-top:15px;">
                    <a href="/login.php" class="btn-primary"
                       style="display:inline-block; width:auto; padding:10px 20px;">
                        Proceed to Login
                    </a>
                </p>

            <?php else: ?>

                <?php if ($error): ?>
                    <div class="alert error">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="post">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                    <label for="password">New Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        placeholder="Enter a strong new password"
                    >

                    <button type="submit" class="btn-primary">
                        Reset Password
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
