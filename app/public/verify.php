<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../security/token.php';

$message = '';
$error = '';

$token = $_GET['token'] ?? '';

if (!$token) {
    $error = "Invalid verification link.";
} else {

    // Fetch verification record
    $stmt = $pdo->prepare(
        "SELECT ev.id, ev.user_id, ev.token_hash, ev.expires_at, u.is_verified
         FROM email_verifications ev
         JOIN users u ON ev.user_id = u.id
         WHERE ev.expires_at > NOW()"
    );
    $stmt->execute();

    $found = false;

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (verifyToken($token, $row['token_hash'])) {
            $found = true;

            if ($row['is_verified']) {
                $message = "Your account is already verified.";
            } else {
                // Mark user as verified
                $update = $pdo->prepare(
                    "UPDATE users SET is_verified = 1 WHERE id = ?"
                );
                $update->execute([$row['user_id']]);

                // Remove used token
                $delete = $pdo->prepare(
                    "DELETE FROM email_verifications WHERE id = ?"
                );
                $delete->execute([$row['id']]);

                $message = "✅ Email verified successfully. You can now log in.";
            }
            break;
        }
    }

    if (!$found) {
        $error = "Verification link is invalid or expired.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Verification – EasyVault</title>
    <style>
        body { font-family: Arial; background:#f4f4f4; }
        .box {
            width: 420px;
            margin: 100px auto;
            background: #fff;
            padding: 25px;
            border-radius: 6px;
            text-align: center;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .success { color: green; }
        .error { color: red; }
        a { text-decoration: none; color: #007BFF; }
    </style>
</head>
<body>

<div class="box">
    <h2>Email Verification</h2>

    <?php if ($message): ?>
        <p class="success"><?= htmlspecialchars($message) ?></p>
        <p><a href="login.php">Go to Login</a></p>
    <?php endif; ?>

    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
        <p><a href="signup.php">Back to Signup</a></p>
    <?php endif; ?>
</div>

<footer style="text-align:center; margin-top:20px; font-size:12px;">
    ⚠ Educational Project — EasyVault.krd
</footer>

</body>
</html>
