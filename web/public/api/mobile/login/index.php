<?php
/** POST /api/mobile/login */
require_once __DIR__ . '/../../../../../backend/php/shared/MobileApi.php';
try {
    $input = mobile_input();
    $data = mobile_login($input['email'] ?? '', $input['password'] ?? '', $input['platform'] ?? 'unknown', $input['device_label'] ?? null);
    mobile_json(['ok' => true, 'token' => $data['token'], 'user' => $data['user']]);
} catch (Throwable $e) {
    mobile_json(['ok' => false, 'error' => $e->getMessage()], 400);
}
