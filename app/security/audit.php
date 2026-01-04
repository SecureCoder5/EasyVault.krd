<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

/**
 * Write a security audit log
 */
function auditLog(
    int $actorUserId,
    string $action,
    ?int $targetUserId = null
): void {
    $db = getDB();

    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

    $stmt = $db->prepare(
        'INSERT INTO audit_logs
         (actor_user_id, action, target_user_id, ip_address)
         VALUES (:actor, :action, :target, :ip)'
    );

    $stmt->execute([
        'actor' => $actorUserId,
        'action' => $action,
        'target' => $targetUserId,
        'ip' => $ip,
    ]);
}
