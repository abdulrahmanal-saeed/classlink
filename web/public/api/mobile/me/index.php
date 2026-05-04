<?php
/** GET /api/mobile/me */
require_once __DIR__ . '/../../../../../backend/php/shared/MobileApi.php';
$user = mobile_require_user();
mobile_json(['ok' => true, 'user' => $user]);
