<?php
/**
 * /student/reviews
 * Student reviews and tests. Own data only.
 */

require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../backend/php/shared/StudentPortal.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';

$user = require_role('student');
$reviews = student_portal_reviews((int) $user['id']);

ob_start();
?>
<p class="text-muted">Your review tests, scores, and feedback.</p>
<?php if (!$reviews): ?>
  <div class="alert alert-light border">No reviews or tests yet.</div>
<?php else: ?>
  <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Review</th><th>Type</th><th>Status</th><th>Score</th><th>Feedback</th></tr></thead><tbody>
  <?php foreach ($reviews as $review): ?>
    <tr>
      <td><strong><?= htmlspecialchars($review['title'], ENT_QUOTES, 'UTF-8') ?></strong><br><small class="text-muted"><?= htmlspecialchars($review['created_at'], ENT_QUOTES, 'UTF-8') ?></small></td>
      <td><?= htmlspecialchars($review['test_type'], ENT_QUOTES, 'UTF-8') ?></td>
      <td><span class="badge text-bg-light border"><?= htmlspecialchars($review['status'], ENT_QUOTES, 'UTF-8') ?></span></td>
      <td><?= htmlspecialchars((string) ($review['score'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
      <td><?= nl2br(htmlspecialchars($review['feedback'] ?? '-', ENT_QUOTES, 'UTF-8')) ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody></table></div>
<?php endif; ?>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'My Reviews', $content);
