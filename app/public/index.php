<?php
declare(strict_types=1);
session_start();

/**
 * If user is logged in, redirect to dashboard
 */
if (!empty($_SESSION['user_id'])) {
    header('Location: /dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>EasyVault.krd</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>

<div class="box text-center">
    <h1>EasyVault.krd</h1>
    <p>A secure password vault built with DevSecOps principles.</p>

    <p>
        <a href="/login.php">Login</a>
        &nbsp;|&nbsp;
        <a href="/signup.php">Create Account</a>
    </p>
</div>

</body>
</html>
