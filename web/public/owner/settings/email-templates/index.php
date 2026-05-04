<?php
/**
 * /owner/settings/email-templates
 * Owner edits email templates and variables.
 */

require_once __DIR__ . '/../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../backend/php/shared/CommunicationCenter.php';
require_once __DIR__ . '/../../../../../web/components/layout/dashboard_shell.php';

$user = require_role('owner_teacher');
$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        comm_update_email_template((int) $user['id'], (int) $_POST['template_id'], $_POST);
        $message = 'Email template updated.';
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
    }
}

$templates = comm_email_templates();
$demoVars = comm_demo_variables();

ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
  <div>
    <p class="text-muted mb-1">Edit email templates. Variables use square brackets like [Name] and [Lesson Date].</p>
    <small class="text-muted">If email provider is not configured, emails are logged only and the main flow does not fail.</small>
  </div>
  <a class="btn btn-outline-brand" href="/owner/communication">Communication Center</a>
</div>
<?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

<?php foreach ($templates as $template): ?>
  <?php $preview = comm_render_email($template['template_key'], $demoVars, 'en'); ?>
  <form method="post" class="foundation-card mb-3">
    <input type="hidden" name="template_id" value="<?= (int) $template['id'] ?>">
    <div class="row g-3">
      <div class="col-md-4"><label class="form-label">Name</label><input class="form-control" name="name" value="<?= htmlspecialchars($template['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></div>
      <div class="col-md-4"><label class="form-label">Key</label><input class="form-control" value="<?= htmlspecialchars($template['template_key'], ENT_QUOTES, 'UTF-8') ?>" disabled></div>
      <div class="col-md-2"><label class="form-label">Order</label><input class="form-control" type="number" name="sort_order" value="<?= (int) $template['sort_order'] ?>"></div>
      <div class="col-md-2"><label class="form-label">Active</label><div class="form-check mt-2"><input class="form-check-input" type="checkbox" name="is_active" <?= ((int) $template['is_active']) === 1 ? 'checked' : '' ?>></div></div>
      <div class="col-md-6"><label class="form-label">Subject EN</label><input class="form-control" name="subject_en" value="<?= htmlspecialchars($template['subject_en'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></div>
      <div class="col-md-6"><label class="form-label">Subject AR</label><input class="form-control" name="subject_ar" value="<?= htmlspecialchars($template['subject_ar'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></div>
      <div class="col-md-6"><label class="form-label">Body EN</label><textarea class="form-control" name="body_en" rows="7"><?= htmlspecialchars($template['body_en'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea></div>
      <div class="col-md-6"><label class="form-label">Body AR</label><textarea class="form-control" name="body_ar" rows="7" dir="rtl"><?= htmlspecialchars($template['body_ar'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea></div>
      <div class="col-12"><label class="form-label">Variables</label><input class="form-control" name="variables" value="<?= htmlspecialchars($template['variables'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></div>
      <div class="col-12"><details><summary class="fw-bold">Preview EN</summary><div class="bg-light border rounded-4 p-3 mt-2"><strong><?= htmlspecialchars($preview['subject'], ENT_QUOTES, 'UTF-8') ?></strong><pre class="mb-0 mt-2" style="white-space:pre-wrap;"><?= htmlspecialchars($preview['body'], ENT_QUOTES, 'UTF-8') ?></pre></div></details></div>
      <div class="col-12"><button class="btn btn-brand" type="submit">Save template</button></div>
    </div>
  </form>
<?php endforeach; ?>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Email Templates', $content);
