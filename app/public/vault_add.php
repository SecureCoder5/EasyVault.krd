<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../security/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../security/crypto.php';

/**
 * AUTH & VAULT KEY CHECKS
 */
requireLogin();
requireVerified();

if (
    empty($_SESSION['user_id']) ||
    empty($_SESSION['vault_key']) ||
    strlen($_SESSION['vault_key']) !== 32
) {
    // Vault key missing or invalid → force re-login
    session_destroy();
    header('Location: /login.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $service  = trim($_POST['service'] ?? '');
    $username = trim($_POST['vault_username'] ?? '');
    $password = $_POST['vault_password'] ?? '';

    if (!$service || !$username || !$password) {
        $error = 'All fields are required.';
    } else {
        try {
            // 🔐 Encrypt password using session vault key
            $encrypted = encryptVaultData($password);

            $db = getDB();
            $stmt = $db->prepare(
                'INSERT INTO vault_items
                 (user_id, service, vault_username, password_cipher, iv, tag)
                 VALUES (:uid, :service, :user, :cipher, :iv, :tag)'
            );

            $stmt->execute([
                'uid'     => $_SESSION['user_id'],
                'service' => $service,
                'user'    => $username,
                'cipher'  => $encrypted['ciphertext'],
                'iv'      => $encrypted['iv'],
                'tag'     => $encrypted['tag'],
            ]);

            header('Location: /user_dashboard.php');
            exit;

        } catch (Throwable $e) {
            // Fail safely (no crypto details leaked)
            $error = 'Encryption failed. Please log in again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Credential – EasyVault</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>

<div class="page-center">

    <!-- Brand -->
    <div class="brand">
        <h1>EasyVault 🔐</h1>
        <p class="brand-sub">
            Add a new secure credential • Kurdistan
        </p>
    </div>

    <!-- Add Credential Card -->
    <div class="card auth-card">

        <div class="card-header">
            <h2>Add Credential</h2>
            <p>Store encrypted login details</p>
        </div>

        <div class="card-body">

            <?php if ($error): ?>
                <div class="alert error">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="post">

                <label for="service">Service / Website</label>
                <input
                    type="text"
                    id="service"
                    name="service"
                    required
                    placeholder="e.g. github.com"
                >

                <label for="vault_username">Username</label>
                <input
                    type="text"
                    id="vault_username"
                    name="vault_username"
                    required
                    placeholder="Account username"
                >

                <label for="vault_password">Password</label>
                <input
                    type="password"
                    id="vault_password"
                    name="vault_password"
                    required
                    placeholder="Account password"
                >

                <button type="submit" class="btn-primary">
                    Save Credential
                </button>

            </form>

        </div>

    </div>

</div>

</body>
</html>
