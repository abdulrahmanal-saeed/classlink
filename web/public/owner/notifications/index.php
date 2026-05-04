<?php
/**
 * /owner/notifications
 * Central owner notifications overview.
 */

require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../backend/php/shared/OwnerDashboard.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';

$user = require_role('owner_teacher');
$rows = owner_all_notifications();

ob_start();
?>
<p class="text-muted">In-app/email/WhatsApp notification log across users.</p>
<?php if (!$rows): ?>
  <div class="alert alert-light border">No notifications yet.</div>
<?php else: ?>
  <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Date</th><th>User</th><th>Title</th><th>Channel</th><th>Status</th></tr></thead><tbody>
  <?php foreach ($rows as $row): ?>
    <tr>
      <td><?= htmlspecialchars($row['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
      <td><?= htmlspecialchars($row['display_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?><br><small class="text-muted"><?= htmlspecialchars($row['email'] ?? '', ENT_QUOTES, 'UTF-8') ?></small></td>
      <td><strong><?= htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8') ?></strong><br><small class="text-muted"><?= htmlspecialchars(mb_strimwidth((string) ($row['body'] ?? ''), 0, 90, '...'), ENT_QUOTES, 'UTF-8') ?></small></td>
      <td><?= htmlspecialchars($row['channel'], ENT_QUOTES, 'UTF-8') ?></td>
      <td><span class="badge text-bg-light border"><?= htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8') ?></span></td>
    </tr>
  <?php endforeach; ?>
  </tbody></table></div>
<?php endif; ?>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Owner Notifications', $content);
