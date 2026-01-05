<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../lib/Mailer.php';

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!$email) {
        $error = 'Email is required.';
    } else {
        $db = getDB();

        $stmt = $db->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user) {
            $otp = strval(random_int(100000, 999999));

            $stmt = $db->prepare("
                INSERT INTO password_resets (user_id, otp, expires_at)
                VALUES (:uid, :otp, DATE_ADD(NOW(), INTERVAL 15 MINUTE))
            ");
            $stmt->execute([
                'uid' => $user['id'],
                'otp' => $otp
            ]);

            $html = "
                <h2>EasyVault Password Reset</h2>
                <p>Your one-time password is:</p>
                <h1>$otp</h1>
                <p>This code expires in 15 minutes.</p>
            ";

            sendMail($email, 'Your EasyVault OTP', $html);
        }

        // Prevent user enumeration
        $success = true;
    }
}
?>

<!-- Simple HTML -->
<form method="POST">
    <input type="email" name="email" required placeholder="Your email">
    <button type="submit">Send OTP</button>
</form>

<?php if ($success): ?>
<p>✔ If the email exists, an OTP has been sent.</p>
<?php endif; ?>
