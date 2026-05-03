<?php
/**
 * /owner/students/view?id=...
 * Owner student account detail.
 */

require_once __DIR__ . '/../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../backend/php/shared/ApprovalWorkflow.php';
require_once __DIR__ . '/../../../../../web/components/layout/dashboard_shell.php';

$user = require_role('owner_teacher');
$studentId = (int) ($_GET['id'] ?? 0);
$student = approval_student_detail($studentId);

if (!$student) {
    http_response_code(404);
    $content = '<div class="alert alert-danger">Student not found.</div><a class="btn btn-outline-brand" href="/owner/students">Back</a>';
    render_dashboard_shell($user, 'Student Not Found', $content);
    exit;
}

$logs = approval_login_logs_for_user($studentId);

ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
  <div>
    <p class="text-muted mb-1">Student account details.</p>
    <small class="text-muted">User ID: <?= (int) $studentId ?></small>
  </div>
  <a class="btn btn-outline-brand" href="/owner/students">Back to students</a>
</div>

<div class="row g-4">
  <div class="col-lg-7">
    <div class="status-box h-100">
      <h2 class="h5 fw-bold">Student profile</h2>
      <dl class="row mb-0 mt-3">
        <dt class="col-sm-4">Name</dt><dd class="col-sm-8"><?= htmlspecialchars($student['display_name'], ENT_QUOTES, 'UTF-8') ?></dd>
        <dt class="col-sm-4">Email</dt><dd class="col-sm-8"><?= htmlspecialchars($student['email'], ENT_QUOTES, 'UTF-8') ?></dd>
        <dt class="col-sm-4">Learner type</dt><dd class="col-sm-8"><?= htmlspecialchars($student['learner_type'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>
        <dt class="col-sm-4">Current level</dt><dd class="col-sm-8"><?= htmlspecialchars($student['current_level'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>
        <dt class="col-sm-4">Goal</dt><dd class="col-sm-8"><?= htmlspecialchars($student['learning_goal'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>
        <dt class="col-sm-4">Dialect</dt><dd class="col-sm-8"><?= htmlspecialchars($student['preferred_dialect'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>
        <dt class="col-sm-4">Phone</dt><dd class="col-sm-8"><?= htmlspecialchars($student['phone'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>
        <dt class="col-sm-4">Country</dt><dd class="col-sm-8"><?= htmlspecialchars($student['country'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>
        <dt class="col-sm-4">Package</dt><dd class="col-sm-8"><?= htmlspecialchars($student['package_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>
        <dt class="col-sm-4">Credits</dt><dd class="col-sm-8"><?= htmlspecialchars((string) ($student['remaining_credits'] ?? '-'), ENT_QUOTES, 'UTF-8') ?> / <?= htmlspecialchars((string) ($student['total_credits'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></dd>
      </dl>
    </div>
  </div>
  <div class="col-lg-5">
    <div class="foundation-card h-100">
      <h2 class="h5 fw-bold">Login details log</h2>
      <?php if (!$logs): ?>
        <div class="alert alert-light border">No login detail logs found.</div>
      <?php else: ?>
        <?php foreach ($logs as $log): ?>
          <div class="border rounded-4 p-3 mb-3">
            <div class="fw-bold"><?= htmlspecialchars($log['subject'], ENT_QUOTES, 'UTF-8') ?></div>
            <div class="small text-muted mb-2"><?= htmlspecialchars($log['created_at'], ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars($log['recipient'], ENT_QUOTES, 'UTF-8') ?></div>
            <pre class="small mb-2" style="white-space:pre-wrap;"><?= htmlspecialchars($log['message_body'], ENT_QUOTES, 'UTF-8') ?></pre>
            <div class="badge text-bg-warning">Temporary password: <?= htmlspecialchars($log['temporary_password'] ?? '-', ENT_QUOTES, 'UTF-8') ?></div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Student Detail', $content);
