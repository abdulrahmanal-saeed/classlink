<?php
/** GET /api/mobile/dashboard */
require_once __DIR__ . '/../../../../../backend/php/shared/MobileApi.php';
$user = mobile_require_user();
mobile_json(['ok' => true, 'dashboard' => mobile_dashboard($user)]);
