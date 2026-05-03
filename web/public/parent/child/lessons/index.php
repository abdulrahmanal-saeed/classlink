<?php
/**
 * /parent/child/lessons?id={childUserId}
 * Parent sees lessons for linked child only.
 */

require_once __DIR__ . '/../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../backend/php/shared/BookingCalendar.php';
require_once __DIR__ . '/../../../../../web/components/layout/dashboard_shell.php';

$user = require_role('parent');
$childId = (int) ($_GET['id'] ?? 0);
if (!$childId || !booking_parent_can_access_child((int) $user['id'], $childId)) {
    http_response_code(403);
    $content = '<div class="alert alert-danger">You are not allowed to view these lessons.</div><a class="btn btn-outline-brand" href="/parent/dashboard">Back</a>';
    render_dashboard_shell($user, 'Unauthorized Lessons', $content);
    exit;
}

$bookings = booking_list_student($childId);
$nameStatement = db()->prepare('SELECT display_name FROM users WHERE id = :id LIMIT 1');
$nameStatement->execute([':id' => $childId]);
$childName = $nameStatement->fetchColumn() ?: 'Child learner';

ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
  <div>
    <p class="text-muted mb-1">Lessons for <?= htmlspecialchars($childName, ENT_QUOTES, 'UTF-8') ?>.</p>
    <small class="text-muted">Parents can only see linked children.</small>
  </div>
  <a class="btn btn-outline-brand" href="/parent/book">Book another lesson</a>
</div>

<?php if (!$bookings): ?>
  <div class="alert alert-light border">No lessons yet.</div>
<?php else: ?>
  <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Date</th><th>Status</th><th>Meeting</th><th>Package</th></tr></thead><tbody>
  <?php foreach ($bookings as $booking): ?>
    <tr>
      <td><?= htmlspecialchars($booking['start_at'], ENT_QUOTES, 'UTF-8') ?> → <?= htmlspecialchars($booking['end_at'], ENT_QUOTES, 'UTF-8') ?></td>
      <td><span class="badge text-bg-light border"><?= htmlspecialchars($booking['status'], ENT_QUOTES, 'UTF-8') ?></span></td>
      <td><?php if (!empty($booking['meeting_link'])): ?><a href="<?= htmlspecialchars($booking['meeting_link'], ENT_QUOTES, 'UTF-8') ?>" target="_blank">Open</a><?php else: ?>-<?php endif; ?></td>
      <td><?= htmlspecialchars($booking['package_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody></table></div>
<?php endif; ?>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Child Lessons', $content);
