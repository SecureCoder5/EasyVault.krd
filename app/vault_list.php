<?php
declare(strict_types=1);

require_once __DIR__ . '/../security/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../security/crypto.php';

requireLogin();
requireVerified();

$db = getDB();
$stmt = $db->prepare(
    'SELECT id, service, vault_username, password_cipher, iv, tag
     FROM vault_items WHERE user_id = :uid'
);

$stmt->execute(['uid' => $_SESSION['user_id']]);
$items = $stmt->fetchAll();
?>

<ul>
<?php foreach ($items as $item): ?>
    <?php
        $password = decryptVaultData(
            $item['password_cipher'],
            $item['iv'],
            $item['tag']
        );
    ?>
    <li>
        <strong><?= htmlspecialchars($item['service']) ?></strong> —
        <?= htmlspecialchars($item['vault_username']) ?>
        <input type="password" value="<?= htmlspecialchars($password) ?>" readonly>
        <button onclick="this.previousElementSibling.type =
            this.previousElementSibling.type === 'password' ? 'text' : 'password'">
            👁
        </button>
        <form method="post" action="/vault_delete.php" style="display:inline">
            <input type="hidden" name="id" value="<?= $item['id'] ?>">
            <button type="submit">❌</button>
        </form>
    </li>
<?php endforeach; ?>
</ul>
