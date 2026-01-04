<?php
declare(strict_types=1);

require_once __DIR__ . '/../security/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../security/crypto.php';

requireLogin();
requireVerified();

/* Resolve ID (GET for first load, POST for submit) */
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

$db = getDB();

/* Fetch vault item belonging to this user */
$stmt = $db->prepare(
    'SELECT service, vault_username, password_cipher, iv, tag
     FROM vault_items
     WHERE id = :id AND user_id = :uid'
);
$stmt->execute([
    'id'  => $id,
    'uid' => $_SESSION['user_id'],
]);

$item = $stmt->fetch();

if (!$item) {
    http_response_code(404);
    exit('Credential not found');
}

$error = '';

/* Handle update */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $service  = trim($_POST['service'] ?? '');
    $username = trim($_POST['vault_username'] ?? '');
    $password = $_POST['vault_password'] ?? '';

    if (!$service || !$username || !$password) {
        $error = 'All fields are required';
    } else {
        $encrypted = encryptVaultData($password);

        $db->prepare(
            'UPDATE vault_items
             SET service = :service,
                 vault_username = :username,
                 password_cipher = :cipher,
                 iv = :iv,
                 tag = :tag
             WHERE id = :id AND user_id = :uid'
        )->execute([
            'service'  => $service,
            'username' => $username,
            'cipher'   => $encrypted['ciphertext'],
            'iv'       => $encrypted['iv'],
            'tag'      => $encrypted['tag'],
            'id'       => $id,
            'uid'      => $_SESSION['user_id'],
        ]);

        header('Location: /user_dashboard.php');
        exit;
    }
}

/* Decrypt existing password for display */
$decryptedPassword = decryptVaultData(
    $item['password_cipher'],
    $item['iv'],
    $item['tag']
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Credential – EasyVault</title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>

<div class="page-center">

    <div class="card">

        <div class="card-header">
            <h2>Edit Credential</h2>
            <p>Update your stored login details</p>
        </div>

        <div class="card-body">

            <?php if ($error): ?>
                <div class="error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="post">

                <!-- ✅ CRITICAL FIX -->
                <input type="hidden" name="id" value="<?= (int)$id ?>">

                <label>Service</label>
                <input type="text" name="service"
                       value="<?= htmlspecialchars($item['service']) ?>" required>

                <label>Username</label>
                <input type="text" name="vault_username"
                       value="<?= htmlspecialchars($item['vault_username']) ?>" required>

                <label>Password</label>
                <input type="password" name="vault_password"
                       value="<?= htmlspecialchars($decryptedPassword) ?>" required>

                <button type="submit">Update Credential</button>

            </form>

        </div>

        <div class="card-footer">
            <a href="/user_dashboard.php">⬅ Back to dashboard</a>
        </div>

    </div>

</div>

</body>
</html>
