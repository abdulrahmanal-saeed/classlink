<?php
/**
 * /parent/child/progress?id={childUserId}
 * Parent child progress page. Linked child only.
 */

require_once __DIR__ . '/../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../backend/php/shared/ParentPortal.php';
require_once __DIR__ . '/../../../../../web/components/layout/dashboard_shell.php';

$user = require_role('parent');
$childId = (int) ($_GET['id'] ?? 0);
$profile = parent_portal_child_profile((int) $user['id'], $childId);
$progress = parent_portal_progress((int) $user['id'], $childId);

ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
  <div><p class="text-muted mb-1">Progress summary for <?= htmlspecialchars($profile['display_name'] ?? 'Child learner', ENT_QUOTES, 'UTF-8') ?>.</p></div>
  <a class="btn btn-outline-brand" href="/parent/child/view?id=<?= (int) $childId ?>">Back to child dashboard</a>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-3"><div class="status-box h-100"><strong>Level</strong><br><span class="display-6"><?= htmlspecialchars($profile['current_level'] ?? '-', ENT_QUOTES, 'UTF-8') ?></span></div></div>
  <div class="col-md-3"><div class="status-box h-100"><strong>Sessions</strong><br><span class="display-6"><?= (int) $progress['summary']['completed_sessions'] ?></span></div></div>
  <div class="col-md-3"><div class="status-box h-100"><strong>Homework</strong><br><span class="display-6"><?= (int) $progress['summary']['submitted_homeworks'] ?></span></div></div>
  <div class="col-md-3"><div class="status-box h-100"><strong>Streak</strong><br><span class="display-6"><?= (int) $progress['streak'] ?></span></div></div>
</div>

<div class="row g-4">
  <div class="col-lg-6">
    <div class="foundation-card h-100">
      <h2 class="h5 fw-bold">Level/literacy check result</h2>
      <?php if (!$progress['level_check']): ?><p class="text-muted">No reviewed level/literacy check yet.</p><?php else: ?>
        <div>Suggested level: <?= htmlspecialchars($progress['level_check']['suggested_level'] ?? '-', ENT_QUOTES, 'UTF-8') ?></div>
        <div>Final level: <?= htmlspecialchars($progress['level_check']['final_level'] ?? '-', ENT_QUOTES, 'UTF-8') ?></div>
        <div class="small text-muted mt-2">Recommended first lesson: <?= htmlspecialchars($progress['level_check']['recommended_first_lesson'] ?? '-', ENT_QUOTES, 'UTF-8') ?></div>
      <?php endif; ?>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="foundation-card h-100">
      <h2 class="h5 fw-bold">Badges</h2>
      <?php if (!$progress['badges']): ?><p class="text-muted">No badges yet.</p><?php else: foreach ($progress['badges'] as $badge): ?><span class="badge text-bg-light border me-1 mb-1"><?= htmlspecialchars($badge['name_en'], ENT_QUOTES, 'UTF-8') ?></span><?php endforeach; endif; ?>
      <div class="mt-3 text-muted">Practice words due: <?= (int) $progress['practice_due'] ?></div>
    </div>
  </div>
</div>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Child Progress', $content);
