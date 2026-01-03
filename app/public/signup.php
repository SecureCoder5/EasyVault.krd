<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../security/crypto.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email    = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // ----------------------------
    // Validation
    // ----------------------------
    if ($email === '' || $username === '' || $password === '') {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } else {
        try {
            $pdo = getDB();

            // Check for duplicates
            $stmt = $pdo->prepare(
                'SELECT id FROM users WHERE email = :email OR username = :username'
            );
            $stmt->execute([
                'email'    => $email,
                'username' => $username,
            ]);

            if ($stmt->fetch()) {
                $error = 'Email or username already exists.';
            } else {
                // Create user
                $passwordHash = hashPassword($password);

                $stmt = $pdo->prepare(
                    'INSERT INTO users (email, username, password_hash, role)
                     VALUES (:email, :username, :password_hash, :role)'
                );

                $stmt->execute([
                    'email'         => $email,
                    'username'      => $username,
                    'password_hash' => $passwordHash,
                    'role'          => 'user',
                ]);

                $success = 'Registration successful. You can now log in.';
            }

        } catch (Throwable $e) {
            error_log('Signup error: ' . $e->getMessage());
            $error = 'An unexpected error occurred. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up – EasyVault</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>

<div class="box">
    <h2>Create Account</h2>

    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php if ($success): ?>
        <p class="success"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <form method="post" novalidate>
        <input type="email" name="email" placeholder="Email" required>
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Sign Up</button>
    </form>

    <p>
        Already have an account?
        <a href="/login.php">Login</a>
    </p>
</div>

</body>
</html>
