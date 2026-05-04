<?php
/**
 * /owner/reviews
 * Central owner reviews overview.
 */

require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../backend/php/shared/OwnerDashboard.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';

$user = require_role('owner_teacher');
$rows = owner_all_reviews();

ob_start();
?>
<p class="text-muted">Review tests and correction status across all students.</p>
<?php if (!$rows): ?>
  <div class="alert alert-light border">No review tests yet.</div>
<?php else: ?>
  <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Review</th><th>Student</th><th>Type</th><th>Status</th><th>Score</th><th>Reviewed</th></tr></thead><tbody>
  <?php foreach ($rows as $row): ?>
    <tr>
      <td><strong><?= htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8') ?></strong><br><small class="text-muted"><?= htmlspecialchars($row['created_at'], ENT_QUOTES, 'UTF-8') ?></small></td>
      <td><?= htmlspecialchars($row['student_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
      <td><?= htmlspecialchars($row['test_type'], ENT_QUOTES, 'UTF-8') ?></td>
      <td><span class="badge text-bg-light border"><?= htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8') ?></span></td>
      <td><?= htmlspecialchars((string) ($row['score'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
      <td><?= htmlspecialchars($row['reviewed_at'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody></table></div>
<?php endif; ?>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Owner Reviews', $content);
