<?php
/**
 * POST /api/push/register-device
 * Registers FCM device token for the currently logged-in user.
 */

require_once __DIR__ . '/../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../backend/php/shared/PushNotifications.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $user = require_login();
    $input = json_decode(file_get_contents('php://input') ?: '[]', true);
    if (!is_array($input)) $input = $_POST;

    $token = trim($input['device_token'] ?? $input['token'] ?? '');
    $platform = trim($input['platform'] ?? 'unknown');
    $label = trim($input['device_label'] ?? '');
    $appVersion = trim($input['app_version'] ?? '');

    $id = push_register_device((int) $user['id'], $token, $platform, $label ?: null, $appVersion ?: null);
    echo json_encode(['ok' => true, 'device_token_id' => $id], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
