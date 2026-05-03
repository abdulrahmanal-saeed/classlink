<?php
/**
 * JSON response helper for PHP APIs.
 *
 * Keeping API responses consistent from the first phase makes Flutter and web
 * integrations easier later because every endpoint returns the same shape.
 */

function json_response(bool $success, string $message, array $data = [], int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');

    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    exit;
}
