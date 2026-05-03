<?php
/**
 * Ziina API client.
 *
 * Secrets must stay in .env only. This file reads ZIINA_API_TOKEN from the
 * server environment and never hardcodes tokens in GitHub.
 */

require_once __DIR__ . '/../core/env.php';

load_env(dirname(__DIR__, 3) . '/.env');

function ziina_is_configured(): bool
{
    return trim((string) getenv('ZIINA_API_TOKEN')) !== '';
}

function ziina_api_base(): string
{
    return rtrim((string) (getenv('ZIINA_API_BASE') ?: 'https://api-v2.ziina.com/api'), '/');
}

function ziina_request(string $method, string $path, ?array $payload = null): array
{
    $token = trim((string) getenv('ZIINA_API_TOKEN'));

    if ($token === '') {
        throw new RuntimeException('Ziina API token is not configured.');
    }

    $url = ziina_api_base() . '/' . ltrim($path, '/');
    $headers = [
        'Authorization: Bearer ' . $token,
        'Accept: application/json',
        'Content-Type: application/json',
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    if ($payload !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    $raw = curl_exec($ch);
    $error = curl_error($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($raw === false) {
        throw new RuntimeException('Ziina request failed: ' . $error);
    }

    $decoded = json_decode($raw, true);

    if (!is_array($decoded)) {
        throw new RuntimeException('Ziina returned a non-JSON response.');
    }

    if ($status < 200 || $status >= 300) {
        throw new RuntimeException('Ziina API error: HTTP ' . $status . ' - ' . json_encode($decoded));
    }

    return $decoded;
}

function ziina_amount_to_fils(float $amount): int
{
    return (int) round($amount * 100);
}

function ziina_create_payment_intent(array $purchase, string $successUrl, string $cancelUrl): array
{
    $payload = [
        'amount' => ziina_amount_to_fils((float) $purchase['amount']),
        'currency_code' => $purchase['currency'] ?: 'AED',
        'message' => 'Habiba Nabil Arabic Academy - ' . ($purchase['plan_name'] ?? 'Arabic lesson package'),
        'success_url' => $successUrl,
        'cancel_url' => $cancelUrl,
        'test' => filter_var(getenv('ZIINA_TEST_MODE') ?: false, FILTER_VALIDATE_BOOLEAN),
        'metadata' => [
            'checkout_reference' => $purchase['checkout_reference'],
            'purchase_id' => (string) $purchase['id'],
            'email' => $purchase['email'] ?? '',
        ],
    ];

    return ziina_request('POST', '/payment_intent', $payload);
}

function ziina_get_payment_intent(string $intentId): array
{
    return ziina_request('GET', '/payment_intent/' . urlencode($intentId));
}

function ziina_extract_intent_id(array $response): ?string
{
    return $response['id'] ?? $response['data']['id'] ?? null;
}

function ziina_extract_redirect_url(array $response): ?string
{
    return $response['redirect_url'] ?? $response['data']['redirect_url'] ?? null;
}

function ziina_extract_status(array $response): ?string
{
    return $response['status'] ?? $response['data']['status'] ?? null;
}
