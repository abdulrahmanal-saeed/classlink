<?php
/** /api/cron/send-homework-reminders?token=... */
require_once __DIR__ . '/../../../../../backend/php/shared/CronJobs.php';
header('Content-Type: application/json; charset=utf-8');
try { cron_validate_token(); echo json_encode(cron_homework_reminders_job('cron', null), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); }
catch (Throwable $e) { http_response_code(http_response_code() === 200 ? 400 : http_response_code()); echo json_encode(['ok'=>false,'error'=>$e->getMessage()], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); }
