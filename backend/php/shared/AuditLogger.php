<?php
/**
 * Audit logger helper.
 *
 * Important security-sensitive actions should be recorded so the Owner/Teacher
 * can review what happened later. We hash IP and user agent to reduce exposure
 * of raw personal data while keeping useful traceability.
 */

require_once __DIR__ . '/../config/db.php';

function audit_log(?int $userId, string $action, ?string $entityType = null, ?string $entityId = null, array $metadata = []): void
{
    try {
        $pdo = db();
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'cli';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'cli';

        $statement = $pdo->prepare(
            'INSERT INTO audit_logs (user_id, action, entity_type, entity_id, ip_hash, user_agent_hash, metadata)
             VALUES (:user_id, :action, :entity_type, :entity_id, :ip_hash, :user_agent_hash, :metadata)'
        );

        $statement->execute([
            ':user_id' => $userId,
            ':action' => $action,
            ':entity_type' => $entityType,
            ':entity_id' => $entityId,
            ':ip_hash' => hash('sha256', $ip),
            ':user_agent_hash' => hash('sha256', $userAgent),
            ':metadata' => $metadata ? json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
        ]);
    } catch (Throwable $exception) {
        // Audit logging should not break the user flow, but in production this
        // failure should be sent to a server error log or monitoring service.
    }
}
