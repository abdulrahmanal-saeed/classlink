<?php
/**
 * /parent/notifications
 * Parent notifications. Own parent account only.
 */

require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../backend/php/shared/ParentPortal.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';

$user = require_role('parent');
$notifications = parent_portal_notifications((int) $user['id']);

ob_start();
?>
<p class="text-muted">Parent notifications and child learning updates.</p>
<?php if (!$notifications): ?>
  <div class="alert alert-light border">No notifications yet.</div>
<?php else: ?>
  <div class="list-group list-group-flush">
    <?php foreach ($notifications as $notification): ?>
      <div class="list-group-item px-0">
        <div class="d-flex justify-content-between gap-3"><strong><?= htmlspecialchars($notification['title'], ENT_QUOTES, 'UTF-8') ?></strong><small class="text-muted"><?= htmlspecialchars($notification['created_at'], ENT_QUOTES, 'UTF-8') ?></small></div>
        <?php if (!empty($notification['body'])): ?><div class="text-muted"><?= nl2br(htmlspecialchars($notification['body'], ENT_QUOTES, 'UTF-8')) ?></div><?php endif; ?>
        <span class="badge text-bg-light border"><?= htmlspecialchars($notification['status'], ENT_QUOTES, 'UTF-8') ?></span>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Parent Notifications', $content);
