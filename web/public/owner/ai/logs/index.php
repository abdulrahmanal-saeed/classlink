<?php
/**
 * /owner/ai/logs
 * AI usage log with prompts/responses/cost tracking.
 */

require_once __DIR__ . '/../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../backend/php/shared/AITools.php';
require_once __DIR__ . '/../../../../../web/components/layout/dashboard_shell.php';

$user = require_role('owner_teacher');
$logs = ai_usage_logs();
$tools = ai_tool_labels();

ob_start();
?>
<p class="text-muted">AI usage logs store prompt, response, tool, related entity, tokens, estimated cost, and status.</p>
<?php if (!$logs): ?>
  <div class="alert alert-light border">No AI logs yet.</div>
<?php else: ?>
  <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Date</th><th>User</th><th>Tool</th><th>Related</th><th>Tokens</th><th>Cost</th><th>Status</th></tr></thead><tbody>
  <?php foreach ($logs as $log): ?>
    <tr>
      <td><?= htmlspecialchars($log['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
      <td><?= htmlspecialchars($log['display_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
      <td><strong><?= htmlspecialchars($tools[$log['tool_name']] ?? $log['tool_name'], ENT_QUOTES, 'UTF-8') ?></strong><br><small class="text-muted"><?= htmlspecialchars($log['model_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?></small></td>
      <td><?= htmlspecialchars(($log['related_type'] ?? '-') . ' #' . ($log['related_id'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
      <td><?= htmlspecialchars((string) ($log['estimated_tokens'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
      <td><?= htmlspecialchars((string) ($log['estimated_cost'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
      <td><span class="badge text-bg-light border"><?= htmlspecialchars($log['status'], ENT_QUOTES, 'UTF-8') ?></span><?php if (!empty($log['error_message'])): ?><div class="small text-danger"><?= htmlspecialchars($log['error_message'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody></table></div>
<?php endif; ?>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'AI Usage Logs', $content);
