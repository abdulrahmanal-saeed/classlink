<?php
/** POST /api/media/attach-order
 * Server-side endpoint to attach media attribution to checkout order.
 * Should be called from checkout/payment backend only, not exposed with private payloads.
 */
require_once __DIR__ . '/../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../backend/php/shared/MediaBuyer.php';

header('Content-Type: application/json; charset=utf-8');
try {
    $user = auth_user();
    if (!$user || $user['role'] !== 'owner_teacher') {
        // Until checkout integration calls this internally, protect manual API access by Owner role.
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'Forbidden']);
        exit;
    }
    $input = json_decode(file_get_contents('php://input') ?: '[]', true);
    if (!is_array($input)) $input = $_POST;
    media_attach_order_attribution($input);
    audit_log((int)$user['id'], 'media_order_attribution_attached', 'checkout_order', (string)($input['checkout_order_id'] ?? ''), []);
    echo json_encode(['ok' => true], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
