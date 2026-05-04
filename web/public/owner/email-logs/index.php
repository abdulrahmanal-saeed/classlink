<?php
/**
 * /owner/email-logs
 * Fallback email log viewer.
 */

require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../backend/php/shared/CommunicationCenter.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';

$user = require_role('owner_teacher');
$logs = comm_email_logs();

ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
  <div>
    <p class="text-muted mb-1">Fallback email log. If email provider is missing, emails are logged here and the main flow does not fail.</p>
    <small class="text-muted">Provider configured: <?= comm_setting('email_provider_configured', '0') === '1' ? 'Yes' : 'No' ?></small>
  </div>
  <a class="btn btn-outline-brand" href="/owner/communication">Communication Center</a>
</div>
<?php if (!$logs): ?>
  <div class="alert alert-light border">No email logs yet.</div>
<?php else: ?>
  <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Date</th><th>Recipient</th><th>Template</th><th>Subject</th><th>Related</th><th>Status</th></tr></thead><tbody>
  <?php foreach ($logs as $log): ?>
    <tr>
      <td><?= htmlspecialchars($log['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
      <td><?= htmlspecialchars($log['recipient_name'] ?? '', ENT_QUOTES, 'UTF-8') ?><br><small class="text-muted"><?= htmlspecialchars($log['recipient_email'], ENT_QUOTES, 'UTF-8') ?></small></td>
      <td><?= htmlspecialchars($log['template_key'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
      <td><strong><?= htmlspecialchars($log['subject'], ENT_QUOTES, 'UTF-8') ?></strong><details><summary class="small text-muted">Body</summary><pre class="small bg-light border rounded-4 p-2 mt-2" style="white-space:pre-wrap;"><?= htmlspecialchars($log['body'] ?? '', ENT_QUOTES, 'UTF-8') ?></pre></details></td>
      <td><?= htmlspecialchars(($log['related_entity_type'] ?? '-') . ' #' . ($log['related_entity_id'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
      <td><span class="badge text-bg-light border"><?= htmlspecialchars($log['status'], ENT_QUOTES, 'UTF-8') ?></span><?php if (!empty($log['error_message'])): ?><div class="small text-danger"><?= htmlspecialchars($log['error_message'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody></table></div>
<?php endif; ?>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Email Logs', $content);
