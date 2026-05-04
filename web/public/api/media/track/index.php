<?php
/** POST /api/media/track - public attribution event tracking. */
require_once __DIR__ . '/../../../../../backend/php/shared/MediaBuyer.php';

header('Content-Type: application/json; charset=utf-8');
try {
    $input = json_decode(file_get_contents('php://input') ?: '[]', true);
    if (!is_array($input)) $input = $_POST;
    $input['referrer'] = $input['referrer'] ?? ($_SERVER['HTTP_REFERER'] ?? null);
    $id = media_register_event($input);
    echo json_encode(['ok' => true, 'event_id' => $id], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Tracking failed.'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}
