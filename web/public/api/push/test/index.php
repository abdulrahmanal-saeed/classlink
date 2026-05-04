<?php
/**
 * POST /api/push/test
 * Sends a test push to the currently logged-in user.
 */

require_once __DIR__ . '/../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../backend/php/shared/PushNotifications.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $user = require_login();
    $input = json_decode(file_get_contents('php://input') ?: '[]', true);
    if (!is_array($input)) $input = $_POST;

    $title = trim($input['title'] ?? 'Test push from Habiba Nabil');
    $body = trim($input['body'] ?? 'Push notification setup is working.');
    $actionUrl = trim($input['action_url'] ?? '/owner/notifications');

    $logs = push_send_to_user((int) $user['id'], 'test_push', $title, $body, $actionUrl, ['source' => 'api_push_test'], $user['role'] === 'owner_teacher' ? 'owner' : $user['role']);
    echo json_encode(['ok' => true, 'log_ids' => $logs], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
