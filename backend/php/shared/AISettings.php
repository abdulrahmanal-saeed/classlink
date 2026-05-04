<?php
/**
 * Phase 26 AI Settings and Security helper.
 * Server-side only. Never expose API keys to browser/frontend.
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/AuditLogger.php';

function ai_setting(string $key, $default = null)
{
    $s = db()->prepare('SELECT setting_value FROM settings WHERE setting_key = :key LIMIT 1');
    $s->execute([':key' => $key]);
    $v = $s->fetchColumn();
    return $v === false ? $default : $v;
}

function ai_bool(string $key, bool $default = false): bool
{
    return ai_setting($key, $default ? '1' : '0') === '1';
}

function ai_encryption_secret(): string
{
    $secret = getenv('AI_SETTINGS_ENCRYPTION_KEY') ?: getenv('APP_KEY') ?: '';
    if (strlen($secret) < 32) {
        throw new RuntimeException('AI encryption key is not configured. Set AI_SETTINGS_ENCRYPTION_KEY in environment.');
    }
    return hash('sha256', $secret, true);
}

function ai_encrypt_key(string $plain): string
{
    $plain = trim($plain);
    if ($plain === '') throw new RuntimeException('API key cannot be empty.');
    $iv = random_bytes(12);
    $tag = '';
    $cipher = openssl_encrypt($plain, 'aes-256-gcm', ai_encryption_secret(), OPENSSL_RAW_DATA, $iv, $tag);
    if ($cipher === false) throw new RuntimeException('Could not encrypt API key.');
    return base64_encode($iv . $tag . $cipher);
}

function ai_decrypt_key(?string $encrypted): ?string
{
    if (!$encrypted) return null;
    $raw = base64_decode($encrypted, true);
    if (!$raw || strlen($raw) < 29) return null;
    $iv = substr($raw, 0, 12);
    $tag = substr($raw, 12, 16);
    $cipher = substr($raw, 28);
    $plain = openssl_decrypt($cipher, 'aes-256-gcm', ai_encryption_secret(), OPENSSL_RAW_DATA, $iv, $tag);
    return $plain === false ? null : $plain;
}

function ai_mask_key(string $key): string
{
    $key = trim($key);
    $last4 = substr($key, -4);
    return '••••••••••••' . $last4;
}

function ai_provider(): string
{
    $provider = ai_setting('ai_provider', 'anthropic');
    return in_array($provider, ['openai','anthropic','gemini','other'], true) ? $provider : 'other';
}

function ai_secret_row(?string $provider = null): ?array
{
    $provider = $provider ?: ai_provider();
    $s = db()->prepare('SELECT * FROM ai_provider_secrets WHERE provider = :provider LIMIT 1');
    $s->execute([':provider' => $provider]);
    $row = $s->fetch();
    return $row ?: null;
}

function ai_save_settings(array $data, array $actor): void
{
    $provider = $data['ai_provider'] ?? 'anthropic';
    if (!in_array($provider, ['openai','anthropic','gemini','other'], true)) $provider = 'other';
    $enabled = !empty($data['ai_features_enabled']) ? '1' : '0';
    $model = trim((string)($data['ai_model_name'] ?? ''));
    $regen = max(0, (int)($data['ai_regenerate_limit_per_tool'] ?? 3));
    $monthly = max(0, (int)($data['ai_monthly_usage_limit'] ?? 0));

    $settings = [
        'ai_features_enabled' => $enabled,
        'ai_provider' => $provider,
        'ai_model_name' => $model,
        'ai_regenerate_limit_per_tool' => (string)$regen,
        'ai_monthly_usage_limit' => (string)$monthly,
        'ai_output_mode' => 'preview_draft_only',
    ];
    foreach ($settings as $key => $value) {
        db()->prepare('UPDATE settings SET setting_value = :value, updated_by_user_id = :user WHERE setting_key = :key')
            ->execute([':value' => $value, ':user' => (int)$actor['id'], ':key' => $key]);
    }

    if (!empty($data['api_key'])) {
        $key = trim((string)$data['api_key']);
        $encrypted = ai_encrypt_key($key);
        db()->prepare('UPDATE ai_provider_secrets SET encrypted_api_key=:encrypted, key_last4=:last4, key_masked=:masked, status="configured", last_error=NULL, updated_by_user_id=:user WHERE provider=:provider')
            ->execute([':encrypted'=>$encrypted, ':last4'=>substr($key,-4), ':masked'=>ai_mask_key($key), ':user'=>(int)$actor['id'], ':provider'=>$provider]);
        db()->prepare('UPDATE settings SET setting_value="configured" WHERE setting_key="ai_connection_status"')->execute();
    }
    audit_log((int)$actor['id'], 'ai_settings_updated', 'settings', 'ai', ['provider'=>$provider, 'enabled'=>$enabled, 'model'=>$model, 'key_updated'=>!empty($data['api_key'])]);
}

function ai_status(): array
{
    $provider = ai_provider();
    $row = ai_secret_row($provider);
    $enabled = ai_bool('ai_features_enabled', false);
    $configured = $row && !empty($row['encrypted_api_key']) && $row['status'] === 'configured';
    return [
        'enabled' => $enabled,
        'provider' => $provider,
        'model' => ai_setting('ai_model_name', ''),
        'configured' => $configured,
        'status' => $configured ? 'configured' : ($row['status'] ?? 'not_configured'),
        'masked_key' => $row['key_masked'] ?? null,
        'last_tested_at' => $row['last_tested_at'] ?? null,
        'last_error' => $row['last_error'] ?? null,
    ];
}

function ai_require_configured(string $toolName, ?array $actor = null, ?string $relatedType = null, ?string $relatedId = null): array
{
    $status = ai_status();
    if (!$status['enabled'] || !$status['configured']) {
        ai_log_usage([
            'tool_name' => $toolName,
            'provider' => $status['provider'],
            'model_name' => $status['model'],
            'related_entity_type' => $relatedType,
            'related_entity_id' => $relatedId,
            'status' => 'blocked_not_configured',
            'error_message' => 'AI is not configured yet. Please add your API key in Owner Settings.',
            'created_by_user_id' => $actor['id'] ?? null,
        ]);
        throw new RuntimeException('AI is not configured yet. Please add your API key in Owner Settings.');
    }
    $row = ai_secret_row($status['provider']);
    $key = ai_decrypt_key($row['encrypted_api_key'] ?? null);
    if (!$key) throw new RuntimeException('AI API key could not be decrypted. Check server encryption key.');
    return ['provider'=>$status['provider'], 'model'=>$status['model'], 'api_key'=>$key];
}

function ai_log_usage(array $data): void
{
    db()->prepare('INSERT INTO ai_usage_logs (tool_name, provider, model_name, related_entity_type, related_entity_id, student_user_id, prompt, response, estimated_input_tokens, estimated_output_tokens, estimated_cost, status, error_message, created_by_user_id) VALUES (:tool,:provider,:model,:rtype,:rid,:student,:prompt,:response,:in_tokens,:out_tokens,:cost,:status,:error,:creator)')
        ->execute([
            ':tool'=>$data['tool_name'] ?? 'unknown', ':provider'=>$data['provider'] ?? null, ':model'=>$data['model_name'] ?? null,
            ':rtype'=>$data['related_entity_type'] ?? null, ':rid'=>$data['related_entity_id'] ?? null, ':student'=>$data['student_user_id'] ?? null,
            ':prompt'=>$data['prompt'] ?? null, ':response'=>$data['response'] ?? null, ':in_tokens'=>$data['estimated_input_tokens'] ?? null,
            ':out_tokens'=>$data['estimated_output_tokens'] ?? null, ':cost'=>$data['estimated_cost'] ?? null, ':status'=>$data['status'] ?? 'success',
            ':error'=>$data['error_message'] ?? null, ':creator'=>$data['created_by_user_id'] ?? null,
        ]);
}

function ai_logs(int $limit = 200): array
{
    $s = db()->prepare('SELECT l.*, u.display_name AS created_by_name, s.display_name AS student_name FROM ai_usage_logs l LEFT JOIN users u ON u.id=l.created_by_user_id LEFT JOIN users s ON s.id=l.student_user_id ORDER BY l.created_at DESC LIMIT :limit');
    $s->bindValue(':limit', $limit, PDO::PARAM_INT);
    $s->execute();
    return $s->fetchAll();
}

function ai_test_connection(array $actor): array
{
    $status = ai_status();
    try {
        $config = ai_require_configured('test_connection', $actor);
        // Lightweight validation only. Actual network call can be added later per provider SDK.
        $ok = strlen($config['api_key']) >= 10 && $config['model'] !== '';
        if (!$ok) throw new RuntimeException('API key or model name looks invalid.');
        db()->prepare('UPDATE ai_provider_secrets SET status="configured", last_tested_at=NOW(), last_error=NULL WHERE provider=:provider')->execute([':provider'=>$status['provider']]);
        db()->prepare('UPDATE settings SET setting_value="configured" WHERE setting_key="ai_connection_status"')->execute();
        ai_log_usage(['tool_name'=>'test_connection','provider'=>$status['provider'],'model_name'=>$status['model'],'status'=>'success','response'=>'Connection settings validated server-side.','created_by_user_id'=>(int)$actor['id']]);
        return ['ok'=>true, 'message'=>'AI connection settings look configured.'];
    } catch (Throwable $e) {
        db()->prepare('UPDATE ai_provider_secrets SET status="connection_failed", last_tested_at=NOW(), last_error=:error WHERE provider=:provider')->execute([':error'=>$e->getMessage(), ':provider'=>$status['provider']]);
        db()->prepare('UPDATE settings SET setting_value="connection_failed" WHERE setting_key="ai_connection_status"')->execute();
        ai_log_usage(['tool_name'=>'test_connection','provider'=>$status['provider'],'model_name'=>$status['model'],'status'=>'failed','error_message'=>$e->getMessage(),'created_by_user_id'=>(int)$actor['id']]);
        return ['ok'=>false, 'message'=>$e->getMessage()];
    }
}
