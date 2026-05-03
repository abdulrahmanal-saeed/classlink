<?php
/**
 * /parent/child/view?id={childUserId}
 * Parent child dashboard. Linked child only.
 */

require_once __DIR__ . '/../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../backend/php/shared/ParentPortal.php';
require_once __DIR__ . '/../../../../../web/components/layout/dashboard_shell.php';

$user = require_role('parent');
$childId = (int) ($_GET['id'] ?? 0);
$data = parent_portal_child_dashboard((int) $user['id'], $childId);
$profile = $data['profile'];

ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
  <div>
    <p class="text-muted mb-1">Child learning dashboard.</p>
    <h2 class="h4 fw-bold mb-0"><?= htmlspecialchars($profile['display_name'] ?? 'Child learner', ENT_QUOTES, 'UTF-8') ?></h2>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <a class="btn btn-brand" href="/parent/book">Book lesson</a>
    <a class="btn btn-outline-brand" href="/parent/child/progress?id=<?= (int) $childId ?>">Progress</a>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-4"><div class="status-box h-100"><strong>Level</strong><br><span class="display-6"><?= htmlspecialchars($profile['current_level'] ?? '-', ENT_QUOTES, 'UTF-8') ?></span></div></div>
  <div class="col-md-4"><div class="status-box h-100"><strong>Remaining credits</strong><br><span class="display-6"><?= htmlspecialchars((string) $data['balance']['remaining'], ENT_QUOTES, 'UTF-8') ?></span></div></div>
  <div class="col-md-4"><div class="status-box h-100"><strong>Streak</strong><br><span class="display-6"><?= (int) $data['streak'] ?></span></div></div>
</div>

<div class="row g-4">
  <div class="col-lg-6"><div class="foundation-card h-100"><h2 class="h5 fw-bold">Upcoming lesson</h2><?php if (!$data['upcoming_lesson']): ?><p class="text-muted">No upcoming lesson.</p><?php else: ?><strong><?= htmlspecialchars($data['upcoming_lesson']['start_at'], ENT_QUOTES, 'UTF-8') ?></strong><br><span class="badge text-bg-light border"><?= htmlspecialchars($data['upcoming_lesson']['status'], ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?></div></div>
  <div class="col-lg-6"><div class="foundation-card h-100"><h2 class="h5 fw-bold">Homework</h2><?php if (!$data['current_homework']): ?><p class="text-muted">No current homework.</p><?php else: ?><strong><?= htmlspecialchars($data['current_homework']['title'], ENT_QUOTES, 'UTF-8') ?></strong><br><small class="text-muted">Due: <?= htmlspecialchars($data['current_homework']['due_at'] ?? '-', ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?></div></div>
  <div class="col-lg-6"><div class="foundation-card h-100"><h2 class="h5 fw-bold">Level/literacy check</h2><?php if (!$data['level_check']): ?><p class="text-muted">No level check result yet.</p><?php else: ?><div>Suggested: <?= htmlspecialchars($data['level_check']['suggested_level'] ?? '-', ENT_QUOTES, 'UTF-8') ?></div><div>Final: <?= htmlspecialchars($data['level_check']['final_level'] ?? '-', ENT_QUOTES, 'UTF-8') ?></div><div class="small text-muted">Recommended first lesson: <?= htmlspecialchars($data['level_check']['recommended_first_lesson'] ?? '-', ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?></div></div>
  <div class="col-lg-6"><div class="foundation-card h-100"><h2 class="h5 fw-bold">Teacher notes</h2><?php if (!$data['session_notes']): ?><p class="text-muted">No teacher notes yet.</p><?php else: foreach ($data['session_notes'] as $note): ?><div class="border rounded-4 p-2 mb-2"><strong><?= htmlspecialchars($note['title'], ENT_QUOTES, 'UTF-8') ?></strong><div><?= nl2br(htmlspecialchars($note['notes'], ENT_QUOTES, 'UTF-8')) ?></div></div><?php endforeach; endif; ?></div></div>
</div>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Child Dashboard', $content);
