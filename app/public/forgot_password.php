<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../security/token.php';
require_once __DIR__ . '/../lib/Mailer.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');

    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {

        // Always respond generically (prevents user enumeration)
        $message = "If the email exists, a reset link has been sent.";

        // Check user
        $stmt = $pdo->prepare(
            "SELECT id, is_verified FROM users WHERE email = ? LIMIT 1"
        );
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Only proceed if user exists AND email verified
        if ($user && $user['is_verified']) {

            // Generate secure token
            $token     = generateToken();
            $tokenHash = hashToken($token);
            $expires   = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Store token
            $stmt = $pdo->prepare(
                "INSERT INTO password_resets (user_id, token_hash, expires_at)
                 VALUES (?, ?, ?)"
            );
            $stmt->execute([$user['id'], $tokenHash, $expires]);

            // Reset link
            $resetLink = "http://localhost:8080/reset_password.php?token=$token";

            // Send email (REAL PHPMailer)
            Mailer::send(
                $email,
                "EasyVault Password Reset",
                "
                <h3>Password Reset Request</h3>
                <p>You requested to reset your password.</p>
                <p>
                    <a href='$resetLink'>$resetLink</a>
                </p>
                <p>This link expires in 1 hour.</p>
                <p>If you did not request this, ignore this email.</p>
                "
            );
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password – EasyVault</title>
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
    <h2>Forgot Password</h2>

    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php if ($message): ?>
        <p class="success"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="POST">
        <input type="email" name="email" placeholder="Your email address" required>
        <button type="submit">Send Reset Link</button>
    </form>

    <p style="margin-top:10px;">
        <a href="login.php">Back to Login</a>
    </p>
</div>

<footer style="text-align:center; margin-top:20px; font-size:12px;">
    ⚠ Educational Project — EasyVault.krd
</footer>

</body>
</html>
