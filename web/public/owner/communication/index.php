<?php
/**
 * /owner/communication
 * Communication center for email/WhatsApp templates, logs, and notification overview.
 */

require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../backend/php/shared/CommunicationCenter.php';
require_once __DIR__ . '/../../../../backend/php/shared/LearningAssignments.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';

$user = require_role('owner_teacher');
$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $action = $_POST['action'] ?? '';
        if ($action === 'log_email') {
            comm_log_email(
                (int) $user['id'],
                null,
                trim($_POST['recipient_email'] ?? 'test@example.com'),
                trim($_POST['recipient_name'] ?? 'Student Name'),
                $_POST['template_key'] ?? 'payment_confirmation',
                comm_demo_variables(),
                'en',
                'general',
                'demo'
            );
            $message = 'Email logged successfully. If no email provider is configured, this is expected fallback behavior.';
        }
        if ($action === 'create_notification') {
            $targetUserId = (int) ($_POST['target_user_id'] ?? 0);
            $targetRole = $_POST['target_role'] ?? 'student';
            $entityType = $_POST['related_entity_type'] ?? 'homework';
            $entityId = trim($_POST['related_entity_id'] ?? '1');
            $childId = ($_POST['child_id'] ?? '') !== '' ? (int) $_POST['child_id'] : null;
            $route = comm_action_route($targetRole, $entityType, $entityId, $childId, $_POST['mode'] ?? 'open');
            comm_create_notification([
                'user_id' => $targetUserId,
                'title' => trim($_POST['title'] ?? 'New update'),
                'message' => trim($_POST['body'] ?? 'You have a new actionable notification.'),
                'type' => $entityType . '_update',
                'target_role' => $targetRole,
                'related_entity_type' => $entityType,
                'related_entity_id' => $entityId,
                'action_label' => $route['label'],
                'action_url' => $route['url'],
            ]);
            $message = 'Actionable notification created.';
        }
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
    }
}

$emailTemplates = comm_email_templates();
$whatsappTemplates = comm_whatsapp_templates();
$emailLogs = array_slice(comm_email_logs(), 0, 10);
$demoVars = comm_demo_variables();
$students = learning_students_for_select();

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
<?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

<div class="row g-3 mb-4">
  <div class="col-md-4"><div class="status-box h-100"><strong>Email templates</strong><br><span class="display-6"><?= count($emailTemplates) ?></span></div></div>
  <div class="col-md-4"><div class="status-box h-100"><strong>WhatsApp templates</strong><br><span class="display-6"><?= count($whatsappTemplates) ?></span></div></div>
  <div class="col-md-4"><div class="status-box h-100"><strong>Recent email logs</strong><br><span class="display-6"><?= count($emailLogs) ?></span></div></div>
</div>

<div class="row g-4 mb-4">
  <div class="col-lg-6">
    <form method="post" class="foundation-card h-100">
      <input type="hidden" name="action" value="log_email">
      <h2 class="h5 fw-bold">Test email fallback log</h2>
      <p class="text-muted">Creates an email log using a selected template. This will not fail if no provider is configured.</p>
      <div class="mb-2"><label class="form-label">Template</label><select class="form-select" name="template_key"><?php foreach ($emailTemplates as $template): ?><option value="<?= htmlspecialchars($template['template_key'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($template['name'] ?: $template['template_key'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></div>
      <div class="mb-2"><label class="form-label">Recipient email</label><input class="form-control" name="recipient_email" value="test@example.com"></div>
      <div class="mb-3"><label class="form-label">Recipient name</label><input class="form-control" name="recipient_name" value="Student Name"></div>
      <button class="btn btn-outline-brand" type="submit">Create email log</button>
    </form>
  </div>
  <div class="col-lg-6">
    <form method="post" class="foundation-card h-100">
      <input type="hidden" name="action" value="create_notification">
      <h2 class="h5 fw-bold">Create actionable notification</h2>
      <p class="text-muted">Every homework/scenario/review/material/booking/payment/badge notification must have a useful action.</p>
      <div class="row g-2">
        <div class="col-md-6"><label class="form-label">Target user</label><select class="form-select" name="target_user_id"><?php foreach ($students as $student): ?><option value="<?= (int) $student['id'] ?>"><?= htmlspecialchars($student['display_name'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></div>
        <div class="col-md-6"><label class="form-label">Target role</label><select class="form-select" name="target_role"><option value="student">student</option><option value="parent">parent</option><option value="owner">owner</option></select></div>
        <div class="col-md-6"><label class="form-label">Entity type</label><select class="form-select" name="related_entity_type"><option value="homework">homework</option><option value="scenario">scenario</option><option value="review">review</option><option value="material">material</option><option value="booking">booking</option><option value="payment">payment</option><option value="badge">badge</option></select></div>
        <div class="col-md-3"><label class="form-label">Entity ID</label><input class="form-control" name="related_entity_id" value="1"></div>
        <div class="col-md-3"><label class="form-label">Mode</label><select class="form-select" name="mode"><option value="open">open</option><option value="result">result</option></select></div>
        <div class="col-md-12"><label class="form-label">Child ID for parent route optional</label><input class="form-control" name="child_id" placeholder="Only if target role is parent"></div>
        <div class="col-12"><label class="form-label">Title</label><input class="form-control" name="title" value="New learning update"></div>
        <div class="col-12"><label class="form-label">Message</label><textarea class="form-control" name="body" rows="2">You have a new update. Please open it from the button.</textarea></div>
        <div class="col-12"><button class="btn btn-brand" type="submit">Create notification</button></div>
      </div>
    </form>
  </div>
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
