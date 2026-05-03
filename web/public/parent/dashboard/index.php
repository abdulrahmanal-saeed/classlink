<?php
/**
 * /parent/dashboard
 * Phase 11 parent dashboard for linked child learners only.
 */

require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../backend/php/shared/ParentPortal.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';

$user = require_role('parent');
$children = parent_portal_children((int) $user['id']);
$firstChildId = parent_portal_first_child_id((int) $user['id']);
$dashboard = $firstChildId ? parent_portal_child_dashboard((int) $user['id'], $firstChildId) : null;

ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
  <div>
    <p class="text-muted mb-1">Welcome to the Parent dashboard. You can only see child learners linked to your account.</p>
    <small class="text-muted">Manage lessons, progress, balance, and teacher notes.</small>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <a class="btn btn-brand" href="/parent/book">Book lesson</a>
    <a class="btn btn-outline-brand" href="/parent/contact">Contact teacher</a>
  </div>
</div>

<?php if (!$children): ?>
  <div class="alert alert-light border">No child learner is linked to this parent account yet.</div>
<?php else: ?>
  <div class="row g-3 mb-4">
    <?php foreach ($children as $child): ?>
      <div class="col-md-6">
        <div class="status-box h-100">
          <h2 class="h5 fw-bold"><?= htmlspecialchars($child['child_name'] ?? 'Child learner', ENT_QUOTES, 'UTF-8') ?></h2>
          <div class="small text-muted mb-2"><?= htmlspecialchars($child['child_email'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
          <dl class="row mb-3 small">
            <dt class="col-5">Level</dt><dd class="col-7"><?= htmlspecialchars($child['current_level'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>
            <dt class="col-5">Goal</dt><dd class="col-7"><?= htmlspecialchars($child['learning_goal'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>
          </dl>
          <div class="d-flex gap-2 flex-wrap">
            <a class="btn btn-sm btn-outline-brand" href="/parent/child/view?id=<?= (int) $child['child_user_id'] ?>">Child dashboard</a>
            <a class="btn btn-sm btn-outline-brand" href="/parent/child/lessons?id=<?= (int) $child['child_user_id'] ?>">Lessons</a>
            <a class="btn btn-sm btn-outline-brand" href="/parent/child/balance?id=<?= (int) $child['child_user_id'] ?>">Balance</a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <?php if ($dashboard): ?>
    <div class="row g-4">
      <div class="col-lg-6">
        <div class="foundation-card h-100">
          <h2 class="h5 fw-bold">Upcoming lesson</h2>
          <?php if (!$dashboard['upcoming_lesson']): ?>
            <p class="text-muted">No upcoming lesson yet.</p>
          <?php else: ?>
            <div class="fw-bold"><?= htmlspecialchars($dashboard['upcoming_lesson']['start_at'], ENT_QUOTES, 'UTF-8') ?></div>
            <div class="text-muted">Status: <?= htmlspecialchars($dashboard['upcoming_lesson']['status'], ENT_QUOTES, 'UTF-8') ?></div>
            <?php if (!empty($dashboard['upcoming_lesson']['meeting_link'])): ?><a class="btn btn-sm btn-outline-brand mt-2" href="<?= htmlspecialchars($dashboard['upcoming_lesson']['meeting_link'], ENT_QUOTES, 'UTF-8') ?>" target="_blank">Open meeting</a><?php endif; ?>
          <?php endif; ?>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="foundation-card h-100">
          <h2 class="h5 fw-bold">Package balance</h2>
          <div class="row g-2 text-center">
            <div class="col-4"><div class="border rounded-4 p-2"><strong><?= htmlspecialchars((string) $dashboard['balance']['total'], ENT_QUOTES, 'UTF-8') ?></strong><br><small>Total</small></div></div>
            <div class="col-4"><div class="border rounded-4 p-2"><strong><?= htmlspecialchars((string) $dashboard['balance']['used'], ENT_QUOTES, 'UTF-8') ?></strong><br><small>Used</small></div></div>
            <div class="col-4"><div class="border rounded-4 p-2"><strong><?= htmlspecialchars((string) $dashboard['balance']['remaining'], ENT_QUOTES, 'UTF-8') ?></strong><br><small>Left</small></div></div>
          </div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="foundation-card h-100">
          <h2 class="h5 fw-bold">Homework status</h2>
          <?php if (!$dashboard['current_homework']): ?><p class="text-muted">No current homework.</p><?php else: ?><strong><?= htmlspecialchars($dashboard['current_homework']['title'], ENT_QUOTES, 'UTF-8') ?></strong><br><small class="text-muted">Due: <?= htmlspecialchars($dashboard['current_homework']['due_at'] ?? '-', ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="foundation-card h-100">
          <h2 class="h5 fw-bold">Level / literacy result</h2>
          <?php if (!$dashboard['level_check']): ?><p class="text-muted">No reviewed level check yet.</p><?php else: ?><div>Suggested: <?= htmlspecialchars($dashboard['level_check']['suggested_level'] ?? '-', ENT_QUOTES, 'UTF-8') ?></div><div>Final: <?= htmlspecialchars($dashboard['level_check']['final_level'] ?? '-', ENT_QUOTES, 'UTF-8') ?></div><div class="small text-muted">Recommended first lesson: <?= htmlspecialchars($dashboard['level_check']['recommended_first_lesson'] ?? '-', ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
        </div>
      </div>
      <div class="col-lg-4"><div class="status-box h-100"><h2 class="h6 fw-bold">Progress</h2><div>Completed sessions: <?= (int) $dashboard['progress']['completed_sessions'] ?></div><div>Homework submitted: <?= (int) $dashboard['progress']['submitted_homeworks'] ?></div><div>Scenarios submitted: <?= (int) $dashboard['progress']['submitted_scenarios'] ?></div></div></div>
      <div class="col-lg-4"><div class="status-box h-100"><h2 class="h6 fw-bold">Streak</h2><div class="display-6"><?= (int) $dashboard['streak'] ?></div></div></div>
      <div class="col-lg-4"><div class="status-box h-100"><h2 class="h6 fw-bold">Badges</h2><?php if (!$dashboard['badges']): ?><div class="text-muted">No badges yet.</div><?php else: foreach ($dashboard['badges'] as $badge): ?><span class="badge text-bg-light border me-1 mb-1"><?= htmlspecialchars($badge['name_en'], ENT_QUOTES, 'UTF-8') ?></span><?php endforeach; endif; ?></div></div>
      <div class="col-12">
        <div class="foundation-card">
          <h2 class="h5 fw-bold">Recent teacher notes</h2>
          <?php if (!$dashboard['session_notes']): ?><p class="text-muted">No teacher notes yet.</p><?php else: foreach ($dashboard['session_notes'] as $note): ?><div class="border rounded-4 p-2 mb-2"><strong><?= htmlspecialchars($note['title'], ENT_QUOTES, 'UTF-8') ?></strong><br><small class="text-muted"><?= htmlspecialchars($note['start_at'], ENT_QUOTES, 'UTF-8') ?></small><div><?= nl2br(htmlspecialchars($note['notes'], ENT_QUOTES, 'UTF-8')) ?></div></div><?php endforeach; endif; ?>
        </div>
      </div>
    </div>
  <?php endif; ?>
<?php endif; ?>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Parent Dashboard', $content);
