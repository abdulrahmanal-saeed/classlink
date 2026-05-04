<?php
/**
 * /api/cron/send-lesson-reminders?token=...
 * Protected cron endpoint.
 */

require_once __DIR__ . '/../../../../../backend/php/shared/CronJobs.php';
header('Content-Type: application/json; charset=utf-8');

try {
    cron_validate_token();
    $result = cron_lesson_reminders_job('cron', null);
    echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(http_response_code() === 200 ? 400 : http_response_code());
    echo json_encode(['ok' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
