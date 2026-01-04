<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../security/token.php';
require_once __DIR__ . '/../lib/Mailer.php';

$error    = '';
$success  = '';
$verified = false;
$email    = '';

$db = getDB();

/*
|--------------------------------------------------------------------------
| VERIFY CODE
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['code'])) {

    $code = trim($_POST['code']);
    $hash = hashToken($code);

    $stmt = $db->prepare(
        'SELECT ev.id, ev.user_id, u.email
         FROM email_verifications ev
         JOIN users u ON u.id = ev.user_id
         WHERE ev.token_hash = :hash
           AND ev.used = 0
           AND ev.expires_at > NOW()
         LIMIT 1'
    );

    $stmt->execute(['hash' => $hash]);
    $row = $stmt->fetch();

    if ($row) {
        $db->prepare(
            'UPDATE users SET is_verified = 1 WHERE id = :uid'
        )->execute(['uid' => $row['user_id']]);

        $db->prepare(
            'UPDATE email_verifications SET used = 1 WHERE id = :id'
        )->execute(['id' => $row['id']]);

        $verified = true;
    } else {
        $error = 'Invalid or expired verification code.';
    }
}

/*
|--------------------------------------------------------------------------
| RESEND CODE
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resend_email'])) {

    $email = trim($_POST['resend_email']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } else {

        $stmt = $db->prepare(
            'SELECT id, is_verified FROM users WHERE email = :email LIMIT 1'
        );
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if (!$user) {
            $error = 'Account not found.';
        } elseif ((int)$user['is_verified'] === 1) {
            $error = 'This account is already verified.';
        } else {

            // Invalidate old codes
            $db->prepare(
                'UPDATE email_verifications SET used = 1 WHERE user_id = :uid'
            )->execute(['uid' => $user['id']]);

            // Generate new code
            $code  = (string) random_int(100000, 999999);
            $hash  = hashToken($code);
            $exp   = date('Y-m-d H:i:s', time() + 900); // 15 min

            $db->prepare(
                'INSERT INTO email_verifications (user_id, token_hash, expires_at)
                 VALUES (:uid, :hash, :exp)'
            )->execute([
                'uid'  => $user['id'],
                'hash' => $hash,
                'exp'  => $exp
            ]);

            // Send email
            sendMail(
                $email,
                'Your EasyVault Verification Code',
                "<p>Your verification code is:</p><h2>{$code}</h2>"
            );

            $success = 'A new verification code has been sent.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Verification – EasyVault</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>

<div class="page-center">

    <div class="brand">
        <h1>EasyVault 🔐</h1>
        <p class="brand-sub">Secure Password Vault • Kurdistan</p>
    </div>

    <div class="card auth-card">
        <div class="card-header">
            <h2>Email Verification</h2>
            <p>Confirm your email address</p>
        </div>

        <div class="card-body">

            <?php if ($verified): ?>

                <div class="alert success">
                    Your email has been verified successfully.
                </div>

                <p style="text-align:center; margin-top:15px;">
                    <a href="/login.php" class="btn-primary">Proceed to Login</a>
                </p>

            <?php else: ?>

                <?php if ($error): ?>
                    <div class="alert error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <p style="font-size:0.9rem; color:#475569; margin-bottom:15px;">
                    Enter the 6-digit verification code sent to your email.
                </p>

                <!-- VERIFY FORM -->
                <form method="post">
                    <label for="code">Verification Code</label>
                    <input
                        type="text"
                        id="code"
                        name="code"
                        maxlength="6"
                        required
                        placeholder="123456"
                        style="text-align:center; letter-spacing:4px;"
                    >
                    <button type="submit" class="btn-primary">
                        Verify Email
                    </button>
                </form>

                <!-- RESEND FORM -->
                <form method="post" style="margin-top:15px;">
                    <label for="resend_email">Didn’t receive the code?</label>
                    <input
                        type="email"
                        id="resend_email"
                        name="resend_email"
                        required
                        placeholder="your@email.com"
                    >
                    <button type="submit" class="btn-secondary">
                        Resend Verification Code
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
