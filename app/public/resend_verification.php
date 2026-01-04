<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../lib/Mailer.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

$email = trim($_POST['email'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    exit('Invalid email');
}

$db = getDB();

/* Check user */
$stmt = $db->prepare(
    "SELECT id, is_verified FROM users WHERE email = :email LIMIT 1"
);
$stmt->execute(['email' => $email]);
$user = $stmt->fetch();

if (!$user) {
    exit('User not found');
}

if ((int)$user['is_verified'] === 1) {
    exit('Account already verified');
}

/* Generate new code */
$code = random_int(100000, 999999);

/* Optional: 10-minute expiry */
$expires = date('Y-m-d H:i:s', time() + 600);

/* Update DB */
$stmt = $db->prepare(
    "UPDATE users 
     SET verification_code = :code, verification_expires_at = :exp 
     WHERE id = :id"
);

$stmt->execute([
    'code' => $code,
    'exp'  => $expires,
    'id'   => $user['id']
]);

/* Send email */
sendMail(
    $email,
    'Your EasyVault Verification Code',
    "<p>Your new verification code is:</p><h2>{$code}</h2>"
);

echo 'Verification code resent';
