<?php
/**
 * Phase 34 Security hardening helpers.
 *
 * These helpers are intentionally small and dependency-free so they can be used
 * across pages and API routes without changing business logic.
 */

function security_is_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
}

function security_env_bool(string $key, bool $default = false): bool
{
    $value = getenv($key);
    if ($value === false || $value === '') return $default;
    return in_array(strtolower(trim((string)$value)), ['1', 'true', 'yes', 'on'], true);
}

function security_headers(bool $publicPage = false): void
{
    if (headers_sent()) return;
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()');
    if (security_is_https()) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
    }
    if (!$publicPage) {
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
    }
}

function security_safe_error(string $fallback = 'Something went wrong. Please try again.'): string
{
    return getenv('APP_ENV') === 'production' ? $fallback : $fallback;
}

function security_hash_value(?string $value): ?string
{
    if ($value === null || $value === '') return null;
    $pepper = getenv('APP_KEY') ?: getenv('SECURITY_HASH_PEPPER') ?: 'hn-academy-local-pepper';
    return hash('sha256', trim($value) . '|' . $pepper);
}

function security_client_fingerprint(): string
{
    $ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    return security_hash_value($ip . '|' . $ua) ?: 'unknown';
}

function security_csrf_token(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf_token'];
}

function security_csrf_input(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(security_csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

function security_verify_csrf(?string $token): bool
{
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    return is_string($token) && !empty($_SESSION['_csrf_token']) && hash_equals($_SESSION['_csrf_token'], $token);
}

function security_rate_limit_key(string $scope, string $identity): string
{
    return preg_replace('/[^a-zA-Z0-9_\-]/', '_', $scope) . '_' . hash('sha256', $identity);
}

function security_rate_limit_file(string $key): string
{
    $dir = sys_get_temp_dir() . '/hn_security_rate_limits';
    if (!is_dir($dir)) @mkdir($dir, 0700, true);
    return $dir . '/' . $key . '.json';
}

function security_rate_limit_check(string $scope, string $identity, int $maxAttempts = 8, int $windowSeconds = 900): bool
{
    $key = security_rate_limit_key($scope, $identity);
    $file = security_rate_limit_file($key);
    if (!is_file($file)) return true;
    $data = json_decode((string)@file_get_contents($file), true) ?: [];
    $now = time();
    $attempts = array_values(array_filter($data['attempts'] ?? [], fn($t) => is_int($t) && ($now - $t) <= $windowSeconds));
    return count($attempts) < $maxAttempts;
}

function security_rate_limit_record(string $scope, string $identity, int $windowSeconds = 900): void
{
    $key = security_rate_limit_key($scope, $identity);
    $file = security_rate_limit_file($key);
    $now = time();
    $data = is_file($file) ? (json_decode((string)@file_get_contents($file), true) ?: []) : [];
    $attempts = array_values(array_filter($data['attempts'] ?? [], fn($t) => is_int($t) && ($now - $t) <= $windowSeconds));
    $attempts[] = $now;
    @file_put_contents($file, json_encode(['attempts' => $attempts]), LOCK_EX);
}

function security_rate_limit_clear(string $scope, string $identity): void
{
    $file = security_rate_limit_file(security_rate_limit_key($scope, $identity));
    if (is_file($file)) @unlink($file);
}

function security_require_post(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        http_response_code(405);
        exit('Method not allowed.');
    }
}

function security_require_json(): array
{
    $raw = file_get_contents('php://input') ?: '';
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        http_response_code(400);
        exit(json_encode(['error' => 'Invalid JSON.']));
    }
    return $data;
}
