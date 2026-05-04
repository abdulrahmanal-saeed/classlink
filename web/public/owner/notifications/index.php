<?php
/**
 * /owner/notifications
 * Central owner notifications overview plus push logs/test.
 */

require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../backend/php/shared/OwnerDashboard.php';
require_once __DIR__ . '/../../../../backend/php/shared/PushNotifications.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';

$user = require_role('owner_teacher');
$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (($_POST['action'] ?? '') === 'test_push') {
            $logs = push_send_to_user((int) $user['id'], 'test_push', 'Test push from Habiba Nabil', 'Firebase push notification setup is working.', '/owner/notifications', ['source' => 'owner_notifications_page'], 'owner');
            $message = 'Test push attempted. Log IDs: ' . implode(', ', $logs);
        }
    } catch (Throwable $e) { $error = $e->getMessage(); }
}

$rows = owner_all_notifications();
$pushLogs = push_logs(100);
$tokens = push_user_tokens((int) $user['id']);

ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
  <div>
    <p class="text-muted mb-1">In-app/email/WhatsApp notifications and Firebase push logs.</p>
    <small class="text-muted">Registered active devices for you: <?= count($tokens) ?></small>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <a class="btn btn-outline-brand" href="/owner/settings/notifications">Push settings</a>
    <form method="post"><input type="hidden" name="action" value="test_push"><button class="btn btn-brand">Send test push</button></form>
  </div>
</div>
<?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

<div class="foundation-card mb-4">
  <h2 class="h5 fw-bold">Push notification logs</h2>
  <?php if (!$pushLogs): ?>
    <div class="alert alert-light border mb-0">No push logs yet.</div>
  <?php else: ?>
    <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Date</th><th>User</th><th>Event</th><th>Title</th><th>Status</th><th>Error</th></tr></thead><tbody>
    <?php foreach ($pushLogs as $log): ?>
      <tr>
        <td><?= htmlspecialchars($log['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= htmlspecialchars($log['display_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
        <td><code><?= htmlspecialchars($log['event_key'], ENT_QUOTES, 'UTF-8') ?></code></td>
        <td><?= htmlspecialchars($log['title'], ENT_QUOTES, 'UTF-8') ?></td>
        <td><span class="badge text-bg-light border"><?= htmlspecialchars($log['status'], ENT_QUOTES, 'UTF-8') ?></span></td>
        <td><?= htmlspecialchars(mb_strimwidth((string) ($log['error_message'] ?? ''), 0, 100, '...'), ENT_QUOTES, 'UTF-8') ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody></table></div>
  <?php endif; ?>
</div>

<div class="foundation-card">
  <h2 class="h5 fw-bold">Internal notification log</h2>
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
</div>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Owner Notifications', $content);
