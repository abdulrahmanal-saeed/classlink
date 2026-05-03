<?php
/**
 * /parent/children
 * Parent linked children list.
 */

require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../backend/php/shared/ParentPortal.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';

$user = require_role('parent');
$children = parent_portal_children((int) $user['id']);

ob_start();
?>
<p class="text-muted">Children linked to your parent account.</p>
<?php if (!$children): ?>
  <div class="alert alert-light border">No child learner is linked yet.</div>
<?php else: ?>
  <div class="row g-3">
    <?php foreach ($children as $child): ?>
      <div class="col-md-6">
        <div class="status-box h-100">
          <h2 class="h5 fw-bold"><?= htmlspecialchars($child['child_name'], ENT_QUOTES, 'UTF-8') ?></h2>
          <div class="small text-muted mb-2"><?= htmlspecialchars($child['child_email'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
          <div>Level: <?= htmlspecialchars($child['current_level'] ?? '-', ENT_QUOTES, 'UTF-8') ?></div>
          <div>Goal: <?= htmlspecialchars($child['learning_goal'] ?? '-', ENT_QUOTES, 'UTF-8') ?></div>
          <div class="d-flex gap-2 flex-wrap mt-3">
            <a class="btn btn-sm btn-brand" href="/parent/child/view?id=<?= (int) $child['child_user_id'] ?>">Open</a>
            <a class="btn btn-sm btn-outline-brand" href="/parent/child/progress?id=<?= (int) $child['child_user_id'] ?>">Progress</a>
            <a class="btn btn-sm btn-outline-brand" href="/parent/child/lessons?id=<?= (int) $child['child_user_id'] ?>">Lessons</a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'My Children', $content);
