<?php
/**
 * /owner/homework
 * Central owner homework overview.
 */

require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../backend/php/shared/OwnerDashboard.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';

$user = require_role('owner_teacher');
$rows = owner_all_homework();

ob_start();
?>
<p class="text-muted">Manage homework assignments across all students.</p>
<?php if (!$rows): ?>
  <div class="alert alert-light border">No homework records yet.</div>
<?php else: ?>
  <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Homework</th><th>Student</th><th>Status</th><th>Submission</th><th>Due</th></tr></thead><tbody>
  <?php foreach ($rows as $row): ?>
    <tr>
      <td><strong><?= htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8') ?></strong><br><small class="text-muted"><?= htmlspecialchars($row['created_at'], ENT_QUOTES, 'UTF-8') ?></small></td>
      <td><?= htmlspecialchars($row['student_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
      <td><span class="badge text-bg-light border"><?= htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8') ?></span></td>
      <td><?= htmlspecialchars($row['submission_status'] ?? 'not_submitted', ENT_QUOTES, 'UTF-8') ?><br><small class="text-muted"><?= htmlspecialchars($row['submitted_at'] ?? '', ENT_QUOTES, 'UTF-8') ?></small></td>
      <td><?= htmlspecialchars($row['due_at'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody></table></div>
<?php endif; ?>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Owner Homework', $content);
