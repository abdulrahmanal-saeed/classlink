<?php
/**
 * /parent/dashboard
 *
 * Parent dashboard. Parent can only see child learners linked to their account.
 */

require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../backend/php/config/db.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';

$user = require_role('parent');

$statement = db()->prepare(
    'SELECT parent_child_links.*, users.display_name AS child_name, users.email AS child_email,
            student_profiles.current_level, student_profiles.learning_goal, student_profiles.learner_type,
            lesson_packages.package_name, lesson_packages.remaining_credits, lesson_packages.total_credits
     FROM parent_child_links
     LEFT JOIN users ON users.id = parent_child_links.child_user_id
     LEFT JOIN student_profiles ON student_profiles.user_id = parent_child_links.child_user_id
     LEFT JOIN lesson_packages ON lesson_packages.student_user_id = parent_child_links.child_user_id AND lesson_packages.status = "active"
     WHERE parent_child_links.parent_user_id = :parent_id AND parent_child_links.status = "active"
     ORDER BY parent_child_links.created_at DESC'
);
$statement->execute([':parent_id' => (int) $user['id']]);
$children = $statement->fetchAll();

ob_start();
?>
<p class="text-muted">Welcome to the Parent dashboard. You can only see child learners linked to your own account.</p>

<?php if (!$children): ?>
  <div class="alert alert-light border">No child learner is linked to this parent account yet.</div>
<?php else: ?>
  <div class="row g-3">
    <?php foreach ($children as $child): ?>
      <div class="col-md-6">
        <div class="status-box h-100">
          <h2 class="h5 fw-bold"><?= htmlspecialchars($child['child_name'] ?? 'Child learner', ENT_QUOTES, 'UTF-8') ?></h2>
          <div class="small text-muted mb-2"><?= htmlspecialchars($child['child_email'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
          <dl class="row mb-3 small">
            <dt class="col-5">Level</dt><dd class="col-7"><?= htmlspecialchars($child['current_level'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>
            <dt class="col-5">Goal</dt><dd class="col-7"><?= htmlspecialchars($child['learning_goal'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>
            <dt class="col-5">Package</dt><dd class="col-7"><?= htmlspecialchars($child['package_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>
            <dt class="col-5">Credits</dt><dd class="col-7"><?= htmlspecialchars((string) ($child['remaining_credits'] ?? '-'), ENT_QUOTES, 'UTF-8') ?> / <?= htmlspecialchars((string) ($child['total_credits'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></dd>
          </dl>
          <?php if (!empty($child['child_user_id'])): ?>
            <div class="d-flex gap-2 flex-wrap">
              <a class="btn btn-sm btn-outline-brand" href="/parent/child/balance?id=<?= (int) $child['child_user_id'] ?>">View balance</a>
              <a class="btn btn-sm btn-outline-brand" href="/parent/child/lessons?id=<?= (int) $child['child_user_id'] ?>">View lessons</a>
            </div>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Parent Dashboard', $content);
