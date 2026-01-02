<?php
require_once __DIR__ . '/../config/database.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($email && $username && $password) {
        $hash = password_hash($password, PASSWORD_BCRYPT);

        try {
            $stmt = $pdo->prepare(
                "INSERT INTO users (email, username, password_hash) VALUES (?, ?, ?)"
            );
            $stmt->execute([$email, $username, $hash]);
            $message = "Account created successfully. You can now log in.";
        } catch (PDOException $e) {
            $message = "Email or username already exists.";
        }
    } else {
        $message = "All fields are required.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>EasyVault.krd – Signup</title>
</head>
<body>
<h2>Signup</h2>

<form method="POST">
    <input type="email" name="email" placeholder="Email" required><br><br>
    <input type="text" name="username" placeholder="Username" required><br><br>
    <input type="password" name="password" placeholder="Password" required><br><br>
    <button type="submit">Create Account</button>
</form>

<p><?= htmlspecialchars($message) ?></p>

<a href="login.php">Login</a>
</body>
</html>
