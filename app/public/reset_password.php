<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../security/token.php';

$error = '';
$success = '';
$showForm = true;

$token = $_GET['token'] ?? '';

if (!$token) {
    $error = "Invalid or missing reset token.";
    $showForm = false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $token       = $_POST['token'] ?? '';
    $password    = $_POST['password'] ?? '';
    $confirmPass = $_POST['confirm_password'] ?? '';

    if (!$token || !$password || !$confirmPass) {
        $error = "All fields are required.";
    } elseif ($password !== $confirmPass) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } else {

        // Fetch all valid (non-expired, unused) reset tokens
        $stmt = $pdo->prepare(
            "SELECT id, user_id, token_hash
             FROM password_resets
             WHERE used = 0 AND expires_at > NOW()"
        );
        $stmt->execute();

        $matched = false;

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (verifyToken($token, $row['token_hash'])) {
                $matched = true;

                // Hash new password
                $newHash = password_hash($password, PASSWORD_DEFAULT);

                // Update user password
                $updateUser = $pdo->prepare(
                    "UPDATE users SET password_hash = ? WHERE id = ?"
                );
                $updateUser->execute([$newHash, $row['user_id']]);

                // Invalidate token
                $invalidate = $pdo->prepare(
                    "UPDATE password_resets SET used = 1 WHERE id = ?"
                );
                $invalidate->execute([$row['id']]);

                $success = "✅ Password updated successfully. You can now log in.";
                $showForm = false;
                break;
            }
        }

        if (!$matched) {
            $error = "Reset link is invalid or expired.";
            $showForm = false;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password – EasyVault</title>
    <style>
        body { font-family: Arial; background:#f4f4f4; }
        .box {
            width: 420px;
            margin: 100px auto;
            background: #fff;
            padding: 25px;
            border-radius: 6px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        input, button {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
        }
        .error { color: red; }
        .success { color: green; }
        a { text-decoration: none; color: #007BFF; }
    </style>
</head>
<body>

<div class="box">
    <h2>Reset Password</h2>

    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php if ($success): ?>
        <p class="success"><?= htmlspecialchars($success) ?></p>
        <p><a href="login.php">Go to Login</a></p>
    <?php endif; ?>

    <?php if ($showForm): ?>
        <form method="POST">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
            <input type="password" name="password" placeholder="New password (min 8 chars)" required>
            <input type="password" name="confirm_password" placeholder="Confirm new password" required>
            <button type="submit">Reset Password</button>
        </form>
    <?php endif; ?>
</div>

<footer style="text-align:center; margin-top:20px; font-size:12px;">
    ⚠ Educational Project — EasyVault.krd
</footer>

</body>
</html>
