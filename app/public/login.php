<?php
session_start();
require_once __DIR__ . '/../config/database.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $login = trim($_POST["login"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($login && $password) {
        $stmt = $pdo->prepare(
            "SELECT id, username, email, password_hash, role
             FROM users
             WHERE email = ? OR username = ?
             LIMIT 1"
        );
        $stmt->execute([$login, $login]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user["password_hash"])) {
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["username"] = $user["username"];
            $_SESSION["role"] = $user["role"];

            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Invalid credentials.";
        }
    } else {
        $error = "All fields required.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>EasyVault.krd – Login</title>
</head>
<body>
<h2>Login</h2>

<form method="POST">
    <input type="text" name="login" placeholder="Email or Username" required><br><br>
    <input type="password" name="password" placeholder="Password" required><br><br>
    <button type="submit">Login</button>
</form>

<p style="color:red"><?= htmlspecialchars($error) ?></p>

<a href="signup.php">Create account</a>
</body>
</html>
