<?php
/**
 * Application configuration.
 *
 * This file centralizes app-level settings so future phases can reuse the same
 * values without hardcoding them in pages or API endpoints.
 */

return [
    'name' => getenv('APP_NAME') ?: 'Habiba Nabil Arabic Academy',
    'env' => getenv('APP_ENV') ?: 'local',
    'debug' => filter_var(getenv('APP_DEBUG') ?: true, FILTER_VALIDATE_BOOLEAN),
    'url' => getenv('APP_URL') ?: 'http://localhost',
    'timezone' => getenv('APP_TIMEZONE') ?: 'Asia/Dubai',
    'session_name' => getenv('SESSION_NAME') ?: 'hn_academy_session',
];
