<?php
/** /owner/ai/logs - secure AI usage logs. */
require_once __DIR__ . '/../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../backend/php/shared/AISettings.php';
require_once __DIR__ . '/../../../../../web/components/layout/dashboard_shell.php';

$user = require_role('owner_teacher');
$logs = ai_logs(300);
$status = ai_status();

ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
  <div><p class="text-muted mb-1">AI prompts, responses, draft outputs, failures, and blocked calls. API keys are never shown here.</p></div>
  <a class="btn btn-outline-brand" href="/owner/settings/ai">AI Settings</a>
</div>
<?php if(!$status['configured'] || !$status['enabled']): ?><div class="alert alert-warning">AI is not configured yet. Please add your API key in Owner Settings. AI buttons should remain disabled.</div><?php endif; ?>
<div class="foundation-card">
  <h2 class="h5 fw-bold">AI Usage Logs</h2>
  <?php if(!$logs): ?><div class="alert alert-light border mb-0">No AI usage logs yet.</div><?php else: ?>
  <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Date</th><th>Tool</th><th>Provider</th><th>Model</th><th>Related</th><th>Tokens/Cost</th><th>Status</th><th>Error</th></tr></thead><tbody>
  <?php foreach($logs as $log): ?><tr><td><?= htmlspecialchars($log['created_at'], ENT_QUOTES, 'UTF-8') ?></td><td><strong><?= htmlspecialchars($log['tool_name'], ENT_QUOTES, 'UTF-8') ?></strong></td><td><?= htmlspecialchars($log['provider'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td><td class="ltr-safe"><?= htmlspecialchars($log['model_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars(($log['related_entity_type'] ?: '-') . ' #' . ($log['related_entity_id'] ?: '-'), ENT_QUOTES, 'UTF-8') ?><?php if($log['student_name']): ?><br><small><?= htmlspecialchars($log['student_name'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?></td><td class="small">In: <?= (int)($log['estimated_input_tokens'] ?? 0) ?><br>Out: <?= (int)($log['estimated_output_tokens'] ?? 0) ?><br>Cost: <?= htmlspecialchars((string)($log['estimated_cost'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td><td><span class="badge text-bg-light border"><?= htmlspecialchars($log['status'], ENT_QUOTES, 'UTF-8') ?></span></td><td><?= htmlspecialchars(mb_strimwidth((string)($log['error_message'] ?? ''), 0, 120, '...'), ENT_QUOTES, 'UTF-8') ?></td></tr><?php endforeach; ?>
  </tbody></table></div>
  <?php endif; ?>
</div>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'AI Logs', $content);
