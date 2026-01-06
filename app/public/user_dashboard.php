<?php
declare(strict_types=1);

require_once __DIR__ . '/../security/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../security/crypto.php';

requireLogin();
requireVerified();

/* Extra safety */
if (($_SESSION['role'] ?? '') !== 'user') {
    http_response_code(403);
    exit('Access denied');
}

$db = getDB();
$stmt = $db->prepare(
    'SELECT id, service, vault_username, password_cipher, iv, tag
     FROM vault_items
     WHERE user_id = :uid
     ORDER BY created_at DESC'
);
$stmt->execute(['uid' => $_SESSION['user_id']]);
$items = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard – EasyVault</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<div class="page-center">

    <!-- Brand -->
    <div class="brand">
        <h1>EasyVault 🔐</h1>
        <p class="brand-sub">
            Your Secure Password Vault • Kurdistan
        </p>
    </div>

    <!-- Vault Card -->
    <div class="card" style="max-width:900px;">

        <div class="card-header">
            <h2>Your Password Vault</h2>
            <p>Encrypted credentials stored securely</p>
        </div>

        <div class="card-body">

            <!-- Action buttons -->
            <div style="display:flex; gap:12px; margin-bottom:15px;">
    <a href="vault_add.php" class="btn btn-primary" style="flex:1;">
        ➕ Add New Credential
    </a>

    <a href="dashboard.php" class="btn btn-outline-success" style="flex:1;">
        🛡️ Security Tools
    </a>
</div>



            <?php if (empty($items)): ?>
                <p style="font-style:italic; color:#64748b;">
                    No credentials stored yet.
                </p>
            <?php else: ?>

                <table>
                    <thead>
                        <tr>
                            <th>Service</th>
                            <th>Username</th>
                            <th>Password</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>

                    <?php foreach ($items as $item): ?>
                        <?php
                            $decryptedPassword = decryptVaultData(
                                $item['password_cipher'],
                                $item['iv'],
                                $item['tag']
                            );
                        ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($item['service']) ?></strong></td>
                            <td><?= htmlspecialchars($item['vault_username']) ?></td>
                            <td style="max-width:220px;">
                                <input
                                    type="password"
                                    value="<?= htmlspecialchars($decryptedPassword) ?>"
                                    readonly
                                    style="width:100%; padding:8px; font-size:0.85rem;"
                                >
                                <button
                                    type="button"
                                    class="secondary"
                                    style="margin-top:6px; width:100%;"
                                    onclick="
                                        const input = this.previousElementSibling;
                                        input.type = input.type === 'password' ? 'text' : 'password';
                                    ">
                                    👁 Show / Hide
                                </button>
                            </td>
                            <td style="white-space:nowrap;">
                                <a href="vault_edit.php?id=<?= (int)$item['id'] ?>">✏️ Edit</a>

                                <form
                                    method="post"
                                    action="vault_delete.php"
                                    style="display:inline"
                                    onsubmit="return confirm('Are you sure you want to delete this credential?');">
                                    <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">
                                    <button type="submit" class="danger" style="margin-left:8px;">
                                        ❌ Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    </tbody>
                </table>

            <?php endif; ?>

        </div>

        <div class="card-footer logout">
            <form method="post" action="logout.php">
                <button type="submit" class="secondary">
                    Logout
                </button>
            </form>
        </div>

    </div>

</div>

</body>
</html>
