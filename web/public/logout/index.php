<?php
/**
 * /logout
 *
 * Uses the shared auth helper to destroy the session then redirects safely.
 */

require_once __DIR__ . '/../../../backend/php/core/Auth.php';

auth_logout();

header('Location: /login');
exit;
