<?php
/** POST /api/mobile/logout */
require_once __DIR__ . '/../../../../../backend/php/shared/MobileApi.php';
mobile_require_user();
mobile_logout();
mobile_json(['ok' => true]);
