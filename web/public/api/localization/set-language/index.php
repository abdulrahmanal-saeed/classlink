<?php
/**
 * POST /api/localization/set-language
 * Saves visitor/user language preference.
 */

require_once __DIR__ . '/../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../backend/php/shared/Localization.php';

header('Content-Type: application/json; charset=utf-8');

try {
    auth_start_session();
    $user = auth_user();
    $input = json_decode(file_get_contents('php://input') ?: '[]', true);
    if (!is_array($input)) $input = $_POST;
    $lang = l10n_set_language($input['lang'] ?? ($_GET['lang'] ?? 'en'), $user);
    echo json_encode(['ok' => true, 'lang' => $lang, 'dir' => l10n_dir($lang)], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
