<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../security/crypto.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $login    = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($login === '' || $password === '') {
        $error = 'All fields are required.';
    } else {
        try {
            $pdo = getDB();

            // Use two separate placeholders (PDO requirement)
            $stmt = $pdo->prepare(
                'SELECT id, username, email, password_hash, role
                 FROM users
                 WHERE email = :email OR username = :username
                 LIMIT 1'
            );

            $stmt->execute([
                'email'    => $login,
                'username' => $login,
            ]);

            $user = $stmt->fetch();

            if ($user && verifyPassword($password, $user['password_hash'])) {

                // Prevent session fixation
                session_regenerate_id(true);

                $_SESSION['user_id']  = (int)$user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role']     = $user['role'];

                header('Location: /dashboard.php');
                exit;
            }

            // Generic message to avoid user enumeration
            $error = 'Invalid credentials.';

        } catch (Throwable $e) {
            // Log internally, never expose details
            error_log('Login error: ' . $e->getMessage());
            $error = 'An unexpected error occurred. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login – EasyVault</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>

<div class="box">
    <h2>Login</h2>

    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="post" novalidate>
        <input type="text" name="login" placeholder="Email or Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>

    <p>
        Don’t have an account?
        <a href="/signup.php">Create one</a>
    </p>
</div>

</body>
</html>
