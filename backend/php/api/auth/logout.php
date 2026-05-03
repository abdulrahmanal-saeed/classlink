<?php
/**
 * POST /api/auth/logout
 *
 * Destroys the current session and writes a logout audit event.
 */

require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/Response.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Method not allowed.', [], 405);
}

auth_logout();

json_response(true, 'Logged out successfully.', []);
