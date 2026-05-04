<?php
/**
 * /student/notifications
 * Actionable student notifications. Own data only.
 */

require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../backend/php/shared/CommunicationCenter.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';

$user = require_role('student');

if (isset($_GET['read'])) {
    comm_mark_notification_read((int) $_GET['read'], (int) $user['id']);
    if (!empty($_GET['to'])) {
        header('Location: ' . $_GET['to']);
        exit;
    }
}

$notifications = comm_user_notifications((int) $user['id'], 'student');

ob_start();
?>
<p class="text-muted">Your learning notifications and updates. Action buttons open the correct student page and mark the notification as read.</p>
<?php if (!$notifications): ?>
  <div class="alert alert-light border">No notifications yet.</div>
<?php else: ?>
  <div class="list-group list-group-flush">
    <?php foreach ($notifications as $notification): ?>
      <div class="list-group-item px-0">
        <div class="d-flex justify-content-between gap-3 flex-wrap">
          <div>
            <strong><?= htmlspecialchars($notification['title'], ENT_QUOTES, 'UTF-8') ?></strong>
            <?php if ($notification['status'] !== 'read'): ?><span class="badge text-bg-primary ms-1">Unread</span><?php endif; ?>
          </div>
          <small class="text-muted"><?= htmlspecialchars($notification['created_at'], ENT_QUOTES, 'UTF-8') ?></small>
        </div>
        <?php if (!empty($notification['body'])): ?><div class="text-muted mt-1"><?= nl2br(htmlspecialchars($notification['body'], ENT_QUOTES, 'UTF-8')) ?></div><?php endif; ?>
        <div class="small text-muted mt-1"><?= htmlspecialchars(($notification['related_entity_type'] ?? 'general') . ' #' . ($notification['related_entity_id'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
        <?php if (!empty($notification['action_label']) && !empty($notification['action_url'])): ?>
          <a class="btn btn-sm btn-outline-brand mt-2" href="/student/notifications?read=<?= (int) $notification['id'] ?>&to=<?= urlencode($notification['action_url']) ?>"><?= htmlspecialchars($notification['action_label'], ENT_QUOTES, 'UTF-8') ?></a>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Notifications', $content);
