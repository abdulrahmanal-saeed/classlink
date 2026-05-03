<?php
/**
 * Public health endpoint.
 *
 * This endpoint is intentionally simple and public so we can quickly verify
 * that the PHP API layer is reachable before building authentication.
 */

require_once __DIR__ . '/../../../core/Response.php';

json_response(true, 'API is running', [
    'service' => 'Habiba Nabil Arabic Academy API',
    'phase' => '0000 Foundation',
    'timestamp' => gmdate('c'),
]);
