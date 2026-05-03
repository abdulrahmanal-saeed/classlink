<?php
/**
 * /owner/students
 * Owner student accounts list created after approval.
 */

require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../backend/php/shared/ApprovalWorkflow.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';

$user = require_role('owner_teacher');
$students = approval_students();

ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
  <div>
    <p class="text-muted mb-1">Approved student accounts.</p>
    <small class="text-muted">Accounts are created only after Owner approval.</small>
  </div>
  <a class="btn btn-outline-brand" href="/owner/onboarding">Onboarding pipeline</a>
</div>

<?php if (!$students): ?>
  <div class="alert alert-light border">No student accounts created yet.</div>
<?php else: ?>
  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead><tr><th>Student</th><th>Type</th><th>Level</th><th>Goal</th><th>Phone</th><th>Status</th><th>Action</th></tr></thead>
      <tbody>
        <?php foreach ($students as $student): ?>
          <tr>
            <td><strong><?= htmlspecialchars($student['display_name'], ENT_QUOTES, 'UTF-8') ?></strong><br><small class="text-muted"><?= htmlspecialchars($student['email'], ENT_QUOTES, 'UTF-8') ?></small></td>
            <td><span class="badge text-bg-light border"><?= htmlspecialchars($student['learner_type'], ENT_QUOTES, 'UTF-8') ?></span></td>
            <td><?= htmlspecialchars($student['current_level'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($student['learning_goal'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($student['phone'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
            <td><span class="badge text-bg-light border"><?= htmlspecialchars($student['status'], ENT_QUOTES, 'UTF-8') ?></span></td>
            <td><a class="btn btn-sm btn-outline-brand" href="/owner/students/view?id=<?= (int) $student['id'] ?>">View</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Owner Students', $content);
