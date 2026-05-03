<?php
/**
 * GET /api/auth/me
 *
 * Returns the current authenticated user from the secure PHP session.
 */

require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/Response.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(false, 'Method not allowed.', [], 405);
}

$user = auth_user();

if (!$user) {
    json_response(false, 'Unauthenticated.', [], 401);
}

json_response(true, 'Authenticated user loaded.', [
    'user' => $user,
    'home' => role_home_path($user['role']),
]);
