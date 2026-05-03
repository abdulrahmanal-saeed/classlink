<?php
/**
 * POST /api/auth/login
 *
 * Validates credentials and creates a secure PHP session.
 */

require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/Response.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Method not allowed.', [], 405);
}

$input = json_decode(file_get_contents('php://input'), true);

if (!is_array($input)) {
    $input = $_POST;
}

$email = trim($input['email'] ?? '');
$password = (string) ($input['password'] ?? '');

if ($email === '' || $password === '') {
    json_response(false, 'Email and password are required.', [], 422);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_response(false, 'Please enter a valid email address.', [], 422);
}

[$ok, $user, $message] = auth_attempt($email, $password);

if (!$ok) {
    json_response(false, $message, [], 401);
}

json_response(true, $message, [
    'user' => $user,
    'redirect' => role_home_path($user['role']),
]);
