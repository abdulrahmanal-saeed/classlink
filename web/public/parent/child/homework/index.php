<?php
/**
 * /parent/child/homework?id={childUserId}
 * Parent child homework page. Linked child only.
 */

require_once __DIR__ . '/../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../backend/php/shared/ParentPortal.php';
require_once __DIR__ . '/../../../../../web/components/layout/dashboard_shell.php';

$user = require_role('parent');
$childId = (int) ($_GET['id'] ?? 0);
$profile = parent_portal_child_profile((int) $user['id'], $childId);
$homeworks = parent_portal_homeworks((int) $user['id'], $childId);

ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
  <div><p class="text-muted mb-1">Homework for <?= htmlspecialchars($profile['display_name'] ?? 'Child learner', ENT_QUOTES, 'UTF-8') ?>.</p></div>
  <a class="btn btn-outline-brand" href="/parent/child/view?id=<?= (int) $childId ?>">Back to child dashboard</a>
</div>

<?php if (!$homeworks): ?>
  <div class="alert alert-light border">No homework yet.</div>
<?php else: ?>
  <div class="row g-3">
    <?php foreach ($homeworks as $hw): ?>
      <div class="col-md-6"><div class="status-box h-100"><h2 class="h5 fw-bold"><?= htmlspecialchars($hw['title'], ENT_QUOTES, 'UTF-8') ?></h2><div class="small text-muted mb-2">Due: <?= htmlspecialchars($hw['due_at'] ?? '-', ENT_QUOTES, 'UTF-8') ?></div><span class="badge text-bg-light border"><?= htmlspecialchars($hw['submission_status'] ?? 'not_submitted', ENT_QUOTES, 'UTF-8') ?></span><?php if (!empty($hw['feedback'])): ?><div class="alert alert-info mt-3 mb-0"><?= nl2br(htmlspecialchars($hw['feedback'], ENT_QUOTES, 'UTF-8')) ?></div><?php endif; ?></div></div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Child Homework', $content);
