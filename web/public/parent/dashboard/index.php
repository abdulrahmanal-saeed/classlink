<?php
/**
 * /parent/dashboard
 * Phase 11 parent dashboard for linked child learners only.
 * Phase 32 improves next actions and clearer empty states.
 */

require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../backend/php/shared/ParentPortal.php';
require_once __DIR__ . '/../../../../backend/php/shared/UXComponents.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';

$user = require_role('parent');
$children = parent_portal_children((int) $user['id']);
$firstChildId = parent_portal_first_child_id((int) $user['id']);
$dashboard = $firstChildId ? parent_portal_child_dashboard((int) $user['id'], $firstChildId) : null;

ob_start();
?>
<?= ux_page_intro('Parent portal', 'Follow your child’s Arabic progress', 'See upcoming lessons, homework status, teacher notes, package balance, and next actions for linked child learners only.', [
  ['label' => 'Book lesson', 'href' => '/parent/book', 'primary' => true],
  ['label' => 'Contact teacher', 'href' => '/parent/contact'],
  ['label' => 'Help', 'href' => '/parent/help'],
]) ?>

<?php if (!$children): ?>
  <?= ux_empty_state('No child learner linked yet', 'When the Owner links a child learner to this parent account, the child dashboard, lessons, homework, and balance will appear here.', '/parent/contact', 'Contact teacher') ?>
<?php else: ?>
  <?= ux_next_step_card('Start with the child overview', 'Open the child dashboard to see lessons, balance, homework, notes, and progress in more detail.', '/parent/children', 'View children', 'outline') ?>
  <div class="row g-3 mb-4">
    <?php foreach ($children as $child): ?>
      <div class="col-md-6">
        <div class="status-box h-100">
          <h2 class="h5 fw-bold"><?= htmlspecialchars($child['child_name'] ?? 'Child learner', ENT_QUOTES, 'UTF-8') ?></h2>
          <div class="small text-muted mb-2"><?= htmlspecialchars($child['child_email'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
          <dl class="row mb-3 small"><dt class="col-5">Level</dt><dd class="col-7"><?= htmlspecialchars($child['current_level'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd><dt class="col-5">Goal</dt><dd class="col-7"><?= htmlspecialchars($child['learning_goal'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd></dl>
          <div class="d-flex gap-2 flex-wrap"><a class="btn btn-sm btn-outline-brand" href="/parent/child/view?id=<?= (int) $child['child_user_id'] ?>">Child dashboard</a><a class="btn btn-sm btn-outline-brand" href="/parent/child/lessons?id=<?= (int) $child['child_user_id'] ?>">Lessons</a><a class="btn btn-sm btn-outline-brand" href="/parent/child/balance?id=<?= (int) $child['child_user_id'] ?>">Balance</a></div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <?php if ($dashboard): ?>
    <div class="row g-4">
      <div class="col-lg-6"><div class="foundation-card h-100"><h2 class="h5 fw-bold">Upcoming lesson</h2>
        <?php if (!$dashboard['upcoming_lesson']): ?><?= ux_empty_state('No upcoming lesson yet', 'Book a lesson or wait for the teacher to confirm the next child session.', '/parent/book', 'Book lesson') ?><?php else: ?>
          <div class="fw-bold"><?= htmlspecialchars($dashboard['upcoming_lesson']['start_at'], ENT_QUOTES, 'UTF-8') ?></div><div class="text-muted">Status: <?= ux_status_badge($dashboard['upcoming_lesson']['status']) ?></div><?= ux_helper_text('Before the lesson, check homework status and recent teacher notes.') ?><?php if (!empty($dashboard['upcoming_lesson']['meeting_link'])): ?><a class="btn btn-sm btn-outline-brand mt-2" href="<?= htmlspecialchars($dashboard['upcoming_lesson']['meeting_link'], ENT_QUOTES, 'UTF-8') ?>" target="_blank">Open meeting</a><?php endif; ?>
        <?php endif; ?>
      </div></div>
      <div class="col-lg-6"><div class="foundation-card h-100"><h2 class="h5 fw-bold">Package balance</h2>
        <div class="row g-2 text-center"><div class="col-4"><div class="border rounded-4 p-2"><strong><?= htmlspecialchars((string) $dashboard['balance']['total'], ENT_QUOTES, 'UTF-8') ?></strong><br><small>Total</small></div></div><div class="col-4"><div class="border rounded-4 p-2"><strong><?= htmlspecialchars((string) $dashboard['balance']['used'], ENT_QUOTES, 'UTF-8') ?></strong><br><small>Used</small></div></div><div class="col-4"><div class="border rounded-4 p-2"><strong><?= htmlspecialchars((string) $dashboard['balance']['remaining'], ENT_QUOTES, 'UTF-8') ?></strong><br><small>Left</small></div></div></div>
        <?= ux_helper_text('Lesson credits help you track remaining paid sessions for your child.') ?>
      </div></div>
      <div class="col-lg-6"><div class="foundation-card h-100"><h2 class="h5 fw-bold">Homework status</h2><?php if (!$dashboard['current_homework']): ?><?= ux_empty_state('No current homework', 'When the teacher assigns homework, you can check its status here.', '/parent/help', 'How homework works') ?><?php else: ?><strong><?= htmlspecialchars($dashboard['current_homework']['title'], ENT_QUOTES, 'UTF-8') ?></strong><br><small class="text-muted">Due: <?= htmlspecialchars($dashboard['current_homework']['due_at'] ?? '-', ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?></div></div>
      <div class="col-lg-6"><div class="foundation-card h-100"><h2 class="h5 fw-bold">Level / literacy result</h2><?php if (!$dashboard['level_check']): ?><?= ux_empty_state('No reviewed level check yet', 'After the child literacy or level check is reviewed, the result and recommended first lesson will appear here.', '/parent/help', 'How progress works') ?><?php else: ?><div>Suggested: <?= htmlspecialchars($dashboard['level_check']['suggested_level'] ?? '-', ENT_QUOTES, 'UTF-8') ?></div><div>Final: <?= htmlspecialchars($dashboard['level_check']['final_level'] ?? '-', ENT_QUOTES, 'UTF-8') ?></div><div class="small text-muted">Recommended first lesson: <?= htmlspecialchars($dashboard['level_check']['recommended_first_lesson'] ?? '-', ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?></div></div>
      <div class="col-lg-4"><div class="status-box h-100"><h2 class="h6 fw-bold">Progress</h2><div>Completed sessions: <?= (int) $dashboard['progress']['completed_sessions'] ?></div><div>Homework submitted: <?= (int) $dashboard['progress']['submitted_homeworks'] ?></div><div>Scenarios submitted: <?= (int) $dashboard['progress']['submitted_scenarios'] ?></div></div></div>
      <div class="col-lg-4"><div class="status-box h-100 streak-celebration"><h2 class="h6 fw-bold">Streak</h2><div class="display-6"><?= (int) $dashboard['streak'] ?></div></div></div>
      <div class="col-lg-4"><div class="status-box h-100"><h2 class="h6 fw-bold">Badges</h2><?php if (!$dashboard['badges']): ?><div class="text-muted">No badges yet.</div><?php else: foreach ($dashboard['badges'] as $badge): ?><span class="badge badge-earned text-bg-light border me-1 mb-1"><?= htmlspecialchars($badge['name_en'], ENT_QUOTES, 'UTF-8') ?></span><?php endforeach; endif; ?></div></div>
      <div class="col-12"><div class="foundation-card"><h2 class="h5 fw-bold">Recent teacher notes</h2><?php if (!$dashboard['session_notes']): ?><?= ux_empty_state('No teacher notes yet', 'Teacher notes will appear after lessons and help you support your child at home.', '/parent/help', 'How to support at home') ?><?php else: foreach ($dashboard['session_notes'] as $note): ?><div class="border rounded-4 p-2 mb-2"><strong><?= htmlspecialchars($note['title'], ENT_QUOTES, 'UTF-8') ?></strong><br><small class="text-muted"><?= htmlspecialchars($note['start_at'], ENT_QUOTES, 'UTF-8') ?></small><div><?= nl2br(htmlspecialchars($note['notes'], ENT_QUOTES, 'UTF-8')) ?></div></div><?php endforeach; endif; ?></div></div>
    </div>
  <?php endif; ?>
<?php endif; ?>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Parent Dashboard', $content);
