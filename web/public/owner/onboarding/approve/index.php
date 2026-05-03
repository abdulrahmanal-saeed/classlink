<?php
/**
 * /owner/onboarding/approve?id=...
 *
 * Final Owner approval page. Creates accounts only after explicit approval.
 */

require_once __DIR__ . '/../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../backend/php/shared/ApprovalWorkflow.php';
require_once __DIR__ . '/../../../../../web/components/layout/dashboard_shell.php';

$user = require_role('owner_teacher');
$intakeId = (int) ($_GET['id'] ?? 0);
$message = null;
$error = null;
$result = null;

$intake = onboarding_form_detail($intakeId);
if (!$intake) {
    http_response_code(404);
    $content = '<div class="alert alert-danger">Onboarding submission not found.</div><a class="btn btn-outline-brand" href="/owner/onboarding">Back</a>';
    render_dashboard_shell($user, 'Approve Onboarding Not Found', $content);
    exit;
}

$issues = approval_can_approve($intake);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $result = approval_approve_intake($intakeId, (int) $user['id'], trim($_POST['approval_note'] ?? ''));
        $message = $result['already_created'] ? 'Accounts were already created for this onboarding submission.' : 'Approval completed and accounts were created.';
        $intake = onboarding_form_detail($intakeId);
        $issues = approval_can_approve($intake);
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
    }
}

$payload = json_decode($intake['raw_payload'] ?? '{}', true);
$payload = is_array($payload) ? $payload : [];
$isChild = approval_is_child($intake);

ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
  <div>
    <p class="text-muted mb-1">Final approval creates accounts and login details. Nothing is created before approval.</p>
    <small class="text-muted">Onboarding ID: <?= (int) $intakeId ?></small>
  </div>
  <a class="btn btn-outline-brand" href="/owner/onboarding/view?id=<?= (int) $intakeId ?>">Back to review</a>
</div>

<?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

<?php if ($issues): ?>
  <div class="alert alert-warning">
    <strong>Approval blocked:</strong>
    <ul class="mb-0"><?php foreach ($issues as $issue): ?><li><?= htmlspecialchars($issue, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul>
  </div>
<?php endif; ?>

<div class="row g-4">
  <div class="col-lg-7">
    <div class="status-box mb-4">
      <h2 class="h5 fw-bold">Approval summary</h2>
      <dl class="row mb-0 mt-3">
        <dt class="col-sm-4">Learner</dt><dd class="col-sm-8"><?= htmlspecialchars($intake['learner_name'] ?? $intake['checkout_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>
        <dt class="col-sm-4">Learner type</dt><dd class="col-sm-8"><?= htmlspecialchars($intake['learner_type'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>
        <dt class="col-sm-4">Plan</dt><dd class="col-sm-8"><?= htmlspecialchars($intake['plan_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>
        <dt class="col-sm-4">Payment</dt><dd class="col-sm-8"><?= htmlspecialchars($intake['purchase_status'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>
        <dt class="col-sm-4">Form</dt><dd class="col-sm-8"><?= htmlspecialchars($intake['student_form_status'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>
        <dt class="col-sm-4">Level check</dt><dd class="col-sm-8"><?= htmlspecialchars($intake['level_check_status'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>
        <dt class="col-sm-4">Schedule</dt><dd class="col-sm-8"><?= htmlspecialchars($intake['schedule_status'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>
        <dt class="col-sm-4">Review</dt><dd class="col-sm-8"><?= htmlspecialchars($intake['owner_review_status'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>
      </dl>
    </div>

    <div class="foundation-card">
      <h2 class="h5 fw-bold mb-3">What will be created?</h2>
      <?php if ($isChild): ?>
        <ul class="text-muted mb-0">
          <li>Parent user account.</li>
          <li>Child student user/profile.</li>
          <li>Parent-child link.</li>
          <li>Initial lesson package from purchased plan.</li>
          <li>Login details log for parent and child account.</li>
        </ul>
      <?php else: ?>
        <ul class="text-muted mb-0">
          <li>Adult student user account.</li>
          <li>Student profile.</li>
          <li>Initial lesson package from purchased plan.</li>
          <li>Login details log.</li>
        </ul>
      <?php endif; ?>
    </div>
  </div>
  <div class="col-lg-5">
    <div class="foundation-card h-100">
      <h2 class="h5 fw-bold">Approve and create accounts</h2>
      <p class="text-muted">Teacher approves → Create Student Account → Send/log login details.</p>
      <?php if ($intake['created_student_user_id'] ?? null): ?>
        <div class="alert alert-success">Accounts already created.</div>
        <a class="btn btn-brand" href="/owner/students/view?id=<?= (int) $intake['created_student_user_id'] ?>">Open student</a>
        <?php if (!empty($intake['created_parent_user_id'])): ?><a class="btn btn-outline-brand" href="/owner/parents/view?id=<?= (int) $intake['created_parent_user_id'] ?>">Open parent</a><?php endif; ?>
      <?php else: ?>
        <form method="post">
          <div class="mb-3"><label class="form-label">Approval note</label><textarea class="form-control" name="approval_note" rows="5" placeholder="Optional note for internal records."></textarea></div>
          <button class="btn btn-brand" type="submit" <?= $issues ? 'disabled' : '' ?>>Approve and create accounts</button>
        </form>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Approve Onboarding', $content);
