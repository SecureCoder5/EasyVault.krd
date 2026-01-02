<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>EasyVault.krd – Dashboard</title>
</head>
<body>

<h2>Welcome, <?= htmlspecialchars($_SESSION["username"]) ?></h2>

<?php if ($_SESSION["role"] === "admin"): ?>
    <h3>Admin Dashboard</h3>
    <p>Manage users and system settings.</p>
<?php else: ?>
    <h3>User Dashboard</h3>
    <p>Access your password vault.</p>
<?php endif; ?>

<a href="logout.php">Logout</a>

</body>
</html>
