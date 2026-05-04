<?php
/** /owner/settings/ai - secure AI provider settings. */
require_once __DIR__ . '/../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../backend/php/shared/AISettings.php';
require_once __DIR__ . '/../../../../../web/components/layout/dashboard_shell.php';

$user = require_role('owner_teacher');
$message = null;
$error = null;
$testResult = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $action = $_POST['action'] ?? 'save';
        if ($action === 'save') {
            ai_save_settings($_POST, $user);
            $message = 'AI settings saved securely.';
        }
        if ($action === 'test') {
            $testResult = ai_test_connection($user);
            $message = $testResult['message'];
        }
    } catch (Throwable $e) { $error = $e->getMessage(); }
}

$status = ai_status();

ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
  <div><p class="text-muted mb-1">AI tools stay disabled until a provider and API key are configured. Keys are never shown in the browser.</p></div>
  <a class="btn btn-outline-brand" href="/owner/ai/logs">AI Logs</a>
</div>
<?php if ($message): ?><div class="alert alert-<?= ($testResult && !$testResult['ok']) ? 'danger' : 'success' ?>"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
<div class="row g-4">
  <div class="col-lg-4"><div class="foundation-card h-100"><h2 class="h5 fw-bold">AI Status</h2><p><strong>Status:</strong> <span class="badge text-bg-light border"><?= htmlspecialchars($status['status'], ENT_QUOTES, 'UTF-8') ?></span></p><p><strong>Provider:</strong> <?= htmlspecialchars($status['provider'], ENT_QUOTES, 'UTF-8') ?></p><p><strong>Model:</strong> <?= htmlspecialchars($status['model'] ?: '-', ENT_QUOTES, 'UTF-8') ?></p><p><strong>Masked key:</strong> <span class="ltr-safe"><?= htmlspecialchars($status['masked_key'] ?: 'Not configured', ENT_QUOTES, 'UTF-8') ?></span></p><p><strong>Last tested:</strong> <?= htmlspecialchars($status['last_tested_at'] ?: '-', ENT_QUOTES, 'UTF-8') ?></p><?php if(!$status['configured']): ?><div class="alert alert-warning">AI is not configured yet. Please add your API key in Owner Settings.</div><?php endif; ?><?php if($status['last_error']): ?><div class="alert alert-danger small"><?= htmlspecialchars($status['last_error'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?></div></div>
  <div class="col-lg-8"><form method="post" class="foundation-card h-100"><input type="hidden" name="action" value="save"><h2 class="h5 fw-bold">Provider Settings</h2><div class="row g-3"><div class="col-md-6"><div class="form-check border rounded-4 p-3 ps-5"><input class="form-check-input" type="checkbox" name="ai_features_enabled" <?= ai_bool('ai_features_enabled', false) ? 'checked' : '' ?>><label class="form-check-label">Enable AI features</label></div></div><div class="col-md-6"><label class="form-label">AI Provider</label><select class="form-select" name="ai_provider"><?php foreach(['openai'=>'OpenAI','anthropic'=>'Anthropic Claude','gemini'=>'Gemini','other'=>'Other'] as $value=>$label): ?><option value="<?= $value ?>" <?= $status['provider']===$value?'selected':'' ?>><?= $label ?></option><?php endforeach; ?></select></div><div class="col-md-6"><label class="form-label">Model name</label><input class="form-control ltr-safe" name="ai_model_name" value="<?= htmlspecialchars((string)ai_setting('ai_model_name',''), ENT_QUOTES, 'UTF-8') ?>" placeholder="claude-3-5-sonnet-latest"></div><div class="col-md-6"><label class="form-label">API key</label><input class="form-control ltr-safe" type="password" name="api_key" autocomplete="new-password" placeholder="Leave blank to keep current key"><small class="text-muted">The full key is never displayed after saving.</small></div><div class="col-md-6"><label class="form-label">Regenerate limit per tool</label><input class="form-control" type="number" name="ai_regenerate_limit_per_tool" value="<?= htmlspecialchars((string)ai_setting('ai_regenerate_limit_per_tool','3'), ENT_QUOTES, 'UTF-8') ?>"></div><div class="col-md-6"><label class="form-label">Monthly usage limit</label><input class="form-control" type="number" name="ai_monthly_usage_limit" value="<?= htmlspecialchars((string)ai_setting('ai_monthly_usage_limit','0'), ENT_QUOTES, 'UTF-8') ?>"><small class="text-muted">0 means no monthly cap enforced yet.</small></div><div class="col-12"><div class="alert alert-info mb-0">AI output mode is fixed to preview/draft only. AI must never auto-publish generated content.</div></div><div class="col-12"><button class="btn btn-brand">Save AI Settings</button></div></div></form></div>
</div>
<form method="post" class="mt-4"><input type="hidden" name="action" value="test"><button class="btn btn-outline-brand">Test AI Connection</button></form>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'AI Settings', $content);
