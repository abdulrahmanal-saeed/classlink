<?php
/**
 * /owner/communication
 * Communication center for email/WhatsApp templates, logs, and notification overview.
 */

require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../backend/php/shared/CommunicationCenter.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';

$user = require_role('owner_teacher');
$emailTemplates = comm_email_templates();
$whatsappTemplates = comm_whatsapp_templates();
$emailLogs = array_slice(comm_email_logs(), 0, 10);
$demoVars = comm_demo_variables();

ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
  <div>
    <p class="text-muted mb-1">Manage templates, fallback email logs, WhatsApp messages, and actionable notifications.</p>
    <small class="text-muted">Email provider configured: <?= comm_setting('email_provider_configured', '0') === '1' ? 'Yes' : 'No — emails will be logged only' ?></small>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <a class="btn btn-outline-brand" href="/owner/settings/email-templates">Email templates</a>
    <a class="btn btn-outline-brand" href="/owner/settings/whatsapp-templates">WhatsApp templates</a>
    <a class="btn btn-brand" href="/owner/email-logs">Email logs</a>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-4"><div class="status-box h-100"><strong>Email templates</strong><br><span class="display-6"><?= count($emailTemplates) ?></span></div></div>
  <div class="col-md-4"><div class="status-box h-100"><strong>WhatsApp templates</strong><br><span class="display-6"><?= count($whatsappTemplates) ?></span></div></div>
  <div class="col-md-4"><div class="status-box h-100"><strong>Recent email logs</strong><br><span class="display-6"><?= count($emailLogs) ?></span></div></div>
</div>

<div class="foundation-card mb-4">
  <h2 class="h5 fw-bold">WhatsApp quick preview</h2>
  <p class="text-muted">These buttons open WhatsApp with a pre-filled demo message. Replace the phone number in real flows.</p>
  <div class="row g-3">
    <?php foreach (array_slice($whatsappTemplates, 0, 6) as $template): ?>
      <?php $rendered = comm_render_whatsapp($template['template_key'], $demoVars, 'en'); $link = comm_whatsapp_link('971500000000', $rendered['message']); ?>
      <div class="col-md-6">
        <div class="border rounded-4 p-3 h-100">
          <strong><?= htmlspecialchars($template['name'] ?: $template['template_key'], ENT_QUOTES, 'UTF-8') ?></strong>
          <p class="small text-muted mt-2"><?= htmlspecialchars(mb_strimwidth($rendered['message'], 0, 140, '...'), ENT_QUOTES, 'UTF-8') ?></p>
          <a class="btn btn-sm btn-outline-brand" target="_blank" href="<?= htmlspecialchars($link, ENT_QUOTES, 'UTF-8') ?>">Open WhatsApp</a>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<div class="foundation-card">
  <h2 class="h5 fw-bold">Recent email logs</h2>
  <?php if (!$emailLogs): ?>
    <div class="alert alert-light border mb-0">No email logs yet.</div>
  <?php else: ?>
    <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Date</th><th>Recipient</th><th>Template</th><th>Status</th></tr></thead><tbody>
    <?php foreach ($emailLogs as $log): ?>
      <tr><td><?= htmlspecialchars($log['created_at'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($log['recipient_email'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($log['template_key'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td><td><span class="badge text-bg-light border"><?= htmlspecialchars($log['status'], ENT_QUOTES, 'UTF-8') ?></span></td></tr>
    <?php endforeach; ?>
    </tbody></table></div>
  <?php endif; ?>
</div>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Communication Center', $content);
