<?php
/**
 * Phase 20 Firebase Push Notification helper.
 *
 * Security:
 * - Firebase service account is never committed to GitHub.
 * - Use FIREBASE_SERVICE_ACCOUNT_JSON or GOOGLE_APPLICATION_CREDENTIALS in server env.
 * - FCM HTTP v1 is called server-side only.
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/AuditLogger.php';

function push_setting(string $key, $default = null)
{
    $s = db()->prepare('SELECT setting_value FROM settings WHERE setting_key = :key LIMIT 1');
    $s->execute([':key' => $key]);
    $value = $s->fetchColumn();
    return $value === false ? $default : $value;
}

function push_env(string $key): ?string
{
    $value = getenv($key);
    if ($value) return $value;
    $paths = [__DIR__ . '/../../.env', __DIR__ . '/../../../.env'];
    foreach ($paths as $path) {
        if (!is_file($path)) continue;
        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
            [$envKey, $envValue] = explode('=', $line, 2);
            if (trim($envKey) === $key) return trim($envValue, " \t\n\r\0\x0B\"'");
        }
    }
    return null;
}

function push_owner_event_keys(): array
{
    return [
        'payment_pending_verification' => 'New payment pending verification',
        'student_form_submitted' => 'Student form submitted',
        'level_check_submitted' => 'Level check submitted',
        'homework_submitted' => 'Homework submitted',
        'scenario_submitted' => 'Scenario submitted',
        'review_submitted' => 'Review submitted',
        'testimonial_submitted' => 'Testimonial submitted',
        'academy_brief_submitted' => 'Academy brief submitted',
        'booking_requested' => 'Booking requested',
    ];
}

function push_student_parent_event_keys(): array
{
    return [
        'lesson_confirmed' => 'Lesson confirmed',
        'lesson_reminder' => 'Lesson reminder',
        'homework_published' => 'Homework published',
        'homework_corrected' => 'Homework corrected',
        'scenario_feedback_ready' => 'Scenario feedback ready',
        'low_credits' => 'Low credits',
    ];
}

function push_register_device(int $userId, string $token, string $platform = 'unknown', ?string $label = null, ?string $appVersion = null): int
{
    if (trim($token) === '') throw new RuntimeException('Device token is required.');
    $hash = hash('sha256', $token);
    $platform = in_array($platform, ['web','android','ios','unknown'], true) ? $platform : 'unknown';

    db()->prepare(
        'INSERT INTO push_device_tokens (user_id, token_hash, device_token, platform, device_label, app_version, user_agent, status, last_seen_at)
         VALUES (:user, :hash, :token, :platform, :label, :version, :ua, "active", NOW())
         ON DUPLICATE KEY UPDATE device_token=VALUES(device_token), platform=VALUES(platform), device_label=VALUES(device_label), app_version=VALUES(app_version), user_agent=VALUES(user_agent), status="active", last_seen_at=NOW()'
    )->execute([
        ':user' => $userId,
        ':hash' => $hash,
        ':token' => $token,
        ':platform' => $platform,
        ':label' => $label,
        ':version' => $appVersion,
        ':ua' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
    ]);

    $s = db()->prepare('SELECT id FROM push_device_tokens WHERE user_id = :user AND token_hash = :hash LIMIT 1');
    $s->execute([':user' => $userId, ':hash' => $hash]);
    return (int) $s->fetchColumn();
}

function push_user_tokens(int $userId): array
{
    $s = db()->prepare('SELECT * FROM push_device_tokens WHERE user_id = :user AND status = "active" ORDER BY last_seen_at DESC');
    $s->execute([':user' => $userId]);
    return $s->fetchAll();
}

function push_preference_enabled(int $userId, string $eventKey): bool
{
    $s = db()->prepare('SELECT is_enabled FROM push_notification_preferences WHERE user_id = :user AND event_key = :event LIMIT 1');
    $s->execute([':user' => $userId, ':event' => $eventKey]);
    $value = $s->fetchColumn();
    return $value === false ? true : ((int) $value === 1);
}

function push_set_preference(int $userId, string $eventKey, bool $enabled): void
{
    db()->prepare(
        'INSERT INTO push_notification_preferences (user_id, event_key, is_enabled) VALUES (:user, :event, :enabled)
         ON DUPLICATE KEY UPDATE is_enabled = VALUES(is_enabled)'
    )->execute([':user' => $userId, ':event' => $eventKey, ':enabled' => $enabled ? 1 : 0]);
}

function push_get_preferences(int $userId): array
{
    $s = db()->prepare('SELECT * FROM push_notification_preferences WHERE user_id = :user ORDER BY event_key ASC');
    $s->execute([':user' => $userId]);
    return $s->fetchAll();
}

function push_service_account(): ?array
{
    $jsonEnvName = push_setting('firebase_service_account_json_env', 'FIREBASE_SERVICE_ACCOUNT_JSON');
    $pathEnvName = push_setting('firebase_service_account_path_env', 'GOOGLE_APPLICATION_CREDENTIALS');
    $json = push_env($jsonEnvName);
    if ($json) {
        $decoded = json_decode($json, true);
        if (is_array($decoded)) return $decoded;
    }
    $path = push_env($pathEnvName);
    if ($path && is_file($path)) {
        $decoded = json_decode(file_get_contents($path), true);
        if (is_array($decoded)) return $decoded;
    }
    return null;
}

function push_base64url(string $data): string
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function push_fcm_access_token(): ?string
{
    $sa = push_service_account();
    if (!$sa || empty($sa['client_email']) || empty($sa['private_key']) || empty($sa['token_uri'])) return null;

    $now = time();
    $header = ['alg' => 'RS256', 'typ' => 'JWT'];
    $claim = [
        'iss' => $sa['client_email'],
        'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
        'aud' => $sa['token_uri'],
        'iat' => $now,
        'exp' => $now + 3600,
    ];
    $unsigned = push_base64url(json_encode($header)) . '.' . push_base64url(json_encode($claim));
    $signature = '';
    $ok = openssl_sign($unsigned, $signature, $sa['private_key'], 'sha256WithRSAEncryption');
    if (!$ok) return null;
    $jwt = $unsigned . '.' . push_base64url($signature);

    $ch = curl_init($sa['token_uri']);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ]),
        CURLOPT_HTTPHEADER => ['content-type: application/x-www-form-urlencoded'],
        CURLOPT_TIMEOUT => 30,
    ]);
    $raw = curl_exec($ch);
    $http = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $decoded = json_decode((string) $raw, true);
    return $http < 400 ? ($decoded['access_token'] ?? null) : null;
}

function push_project_id(): ?string
{
    $setting = trim((string) push_setting('firebase_project_id', ''));
    if ($setting !== '') return $setting;
    $sa = push_service_account();
    return $sa['project_id'] ?? null;
}

function push_log(?int $userId, ?int $tokenId, ?string $targetRole, string $eventKey, string $title, string $body, ?string $actionUrl, array $payload, string $status, ?string $error = null, ?string $providerMessageId = null): int
{
    db()->prepare(
        'INSERT INTO push_notification_logs (user_id, device_token_id, target_role, event_key, title, body, action_url, payload_json, status, error_message, provider_message_id, sent_at)
         VALUES (:user, :token, :role, :event, :title, :body, :url, :payload, :status, :error, :message_id, :sent_at)'
    )->execute([
        ':user' => $userId,
        ':token' => $tokenId,
        ':role' => $targetRole,
        ':event' => $eventKey,
        ':title' => $title,
        ':body' => $body,
        ':url' => $actionUrl,
        ':payload' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ':status' => $status,
        ':error' => $error,
        ':message_id' => $providerMessageId,
        ':sent_at' => $status === 'sent' ? date('Y-m-d H:i:s') : null,
    ]);
    return (int) db()->lastInsertId();
}

function push_send_to_token(array $tokenRow, string $eventKey, string $title, string $body, ?string $actionUrl = null, array $data = [], ?string $targetRole = null): int
{
    $userId = (int) $tokenRow['user_id'];
    if (push_setting('push_enabled', '1') !== '1') {
        return push_log($userId, (int) $tokenRow['id'], $targetRole, $eventKey, $title, $body, $actionUrl, $data, 'skipped', 'Push is disabled.');
    }
    if (!push_preference_enabled($userId, $eventKey)) {
        return push_log($userId, (int) $tokenRow['id'], $targetRole, $eventKey, $title, $body, $actionUrl, $data, 'skipped', 'User preference disabled.');
    }

    $projectId = push_project_id();
    $accessToken = push_fcm_access_token();
    if (!$projectId || !$accessToken) {
        return push_log($userId, (int) $tokenRow['id'], $targetRole, $eventKey, $title, $body, $actionUrl, $data, 'failed', 'Firebase credentials or access token missing.');
    }

    $payloadData = array_merge($data, ['event_key' => $eventKey, 'action_url' => $actionUrl ?? '']);
    $message = [
        'message' => [
            'token' => $tokenRow['device_token'],
            'notification' => ['title' => $title, 'body' => $body],
            'data' => array_map('strval', $payloadData),
        ],
    ];

    $ch = curl_init('https://fcm.googleapis.com/v1/projects/' . rawurlencode($projectId) . '/messages:send');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $accessToken, 'Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($message, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        CURLOPT_TIMEOUT => 30,
    ]);
    $raw = curl_exec($ch);
    $error = curl_error($ch);
    $http = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $decoded = json_decode((string) $raw, true);

    if ($raw === false || $error || $http >= 400) {
        $msg = $error ?: ($decoded['error']['message'] ?? ('FCM HTTP ' . $http));
        if (str_contains(strtolower($msg), 'registration-token-not-registered') || str_contains(strtolower($msg), 'not found')) {
            db()->prepare('UPDATE push_device_tokens SET status = "invalid" WHERE id = :id')->execute([':id' => (int) $tokenRow['id']]);
        }
        return push_log($userId, (int) $tokenRow['id'], $targetRole, $eventKey, $title, $body, $actionUrl, $data, 'failed', $msg);
    }

    return push_log($userId, (int) $tokenRow['id'], $targetRole, $eventKey, $title, $body, $actionUrl, $data, 'sent', null, $decoded['name'] ?? null);
}

function push_send_to_user(int $userId, string $eventKey, string $title, string $body, ?string $actionUrl = null, array $data = [], ?string $targetRole = null): array
{
    $tokens = push_user_tokens($userId);
    if (!$tokens) {
        return [push_log($userId, null, $targetRole, $eventKey, $title, $body, $actionUrl, $data, 'skipped', 'No active device tokens.')];
    }
    $logIds = [];
    foreach ($tokens as $token) $logIds[] = push_send_to_token($token, $eventKey, $title, $body, $actionUrl, $data, $targetRole);
    return $logIds;
}

function push_owner_users(): array
{
    return db()->query('SELECT id, display_name, email FROM users WHERE role = "owner_teacher" ORDER BY id ASC')->fetchAll();
}

function push_send_to_owners(string $eventKey, string $title, string $body, ?string $actionUrl = null, array $data = []): array
{
    $logs = [];
    foreach (push_owner_users() as $owner) {
        foreach (push_send_to_user((int) $owner['id'], $eventKey, $title, $body, $actionUrl, $data, 'owner') as $id) $logs[] = $id;
    }
    return $logs;
}

function push_logs(int $limit = 300): array
{
    $s = db()->prepare('SELECT push_notification_logs.*, users.display_name FROM push_notification_logs LEFT JOIN users ON users.id = push_notification_logs.user_id ORDER BY push_notification_logs.created_at DESC LIMIT :limit');
    $s->bindValue(':limit', $limit, PDO::PARAM_INT);
    $s->execute();
    return $s->fetchAll();
}
