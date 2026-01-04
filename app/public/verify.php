<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../security/token.php';

$error = '';
$verified = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $code = trim($_POST['code'] ?? '');
    $hash = hashToken($code);

    $db = getDB();
    $stmt = $db->prepare(
        'SELECT ev.id, ev.user_id
         FROM email_verifications ev
         WHERE ev.token_hash = :hash
           AND ev.used = 0
           AND ev.expires_at > NOW()'
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

    <!-- Brand -->
    <div class="brand">
        <h1>EasyVault 🔐</h1>
        <p class="brand-sub">
            Secure Password Vault • Kurdistan 
        </p>
    </div>

    <!-- Verification Card -->
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

                <p style="font-size:0.9rem; color:#475569; margin-bottom:15px;">
                    Enter the 6-digit verification code sent to your email address.
                </p>

                <form method="post">

                    <label for="code">Verification Code</label>
                    <input
                        type="text"
                        id="code"
                        name="code"
                        maxlength="6"
                        required
                        placeholder="e.g. 123456"
                        style="text-align:center; letter-spacing:4px;"
                    >

                    <button type="submit" class="btn-primary">
                        Verify Email
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
