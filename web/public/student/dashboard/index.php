<?php
/**
 * /student/dashboard
 * Phase 10 adult student portal dashboard.
 * Phase 32 improves next actions, empty states, and task clarity.
 */

require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../backend/php/shared/StudentPortal.php';
require_once __DIR__ . '/../../../../backend/php/shared/UXComponents.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';

$user = require_role('student');
$data = student_portal_dashboard((int) $user['id']);
$profile = $data['profile'];

$todayTasks = 0;
if ($data['current_homework']) $todayTasks++;
if ($data['current_scenario']) $todayTasks++;
if ((int)$data['practice_due'] > 0) $todayTasks++;

ob_start();
?>
<?= ux_page_intro('Student learning hub', 'What should you do today?', 'Your dashboard shows lessons, homework, scenarios, materials, feedback, progress, and notifications in one place.', [
  ['label' => 'Book lesson', 'href' => '/student/book', 'primary' => !$data['upcoming_lesson']],
  ['label' => 'Notifications' . ($data['unread_notifications'] ? ' (' . (int) $data['unread_notifications'] . ')' : ''), 'href' => '/student/notifications'],
  ['label' => 'Help', 'href' => '/student/help'],
]) ?>

<?php if ($todayTasks > 0): ?>
  <?= ux_next_step_card('Start with today’s learning tasks', 'Complete the waiting homework, scenario, or flashcards first. Your teacher can give better feedback when practice is submitted on time.', $data['current_homework'] ? '/student/homework' : ($data['current_scenario'] ? '/student/scenarios' : '/student/practice-words'), 'Start today’s task') ?>
<?php else: ?>
  <?= ux_next_step_card('No urgent task right now', 'Check your next lesson, review previous feedback, or practice words to keep progress moving.', '/student/session-notes', 'Review teacher notes', 'outline') ?>
<?php endif; ?>

<?= ux_step_indicator(['Profile', 'Lesson', 'Practice', 'Feedback', 'Progress'], 2) ?>

<div class="row g-3 mb-4">
  <div class="col-md-4"><div class="status-box h-100"><strong>Current level</strong><br><span class="display-6"><?= htmlspecialchars($profile['current_level'] ?? '-', ENT_QUOTES, 'UTF-8') ?></span></div></div>
  <div class="col-md-4"><div class="status-box h-100"><strong>Remaining credits</strong><br><span class="display-6"><?= htmlspecialchars((string) $data['balance']['remaining'], ENT_QUOTES, 'UTF-8') ?></span></div></div>
  <div class="col-md-4"><div class="status-box h-100 streak-celebration"><strong>Streak</strong><br><span class="display-6"><?= (int) $data['streak'] ?></span><span class="text-muted"> days</span></div></div>
</div>

<div class="row g-4">
  <div class="col-lg-6"><div class="foundation-card h-100"><h2 class="h5 fw-bold">Upcoming lesson</h2>
    <?php if (!$data['upcoming_lesson']): ?>
      <?= ux_empty_state('No upcoming lesson yet', 'Book a lesson or wait for the teacher to confirm your next session.', '/student/book', 'Book a lesson') ?>
    <?php else: ?>
      <div class="fw-bold"><?= htmlspecialchars($data['upcoming_lesson']['start_at'], ENT_QUOTES, 'UTF-8') ?></div>
      <div class="text-muted">Status: <?= ux_status_badge($data['upcoming_lesson']['status']) ?></div>
      <?= ux_helper_text('Before the lesson, review feedback and complete any assigned practice.') ?>
      <?php if (!empty($data['upcoming_lesson']['meeting_link'])): ?><a class="btn btn-sm btn-outline-brand mt-2" target="_blank" href="<?= htmlspecialchars($data['upcoming_lesson']['meeting_link'], ENT_QUOTES, 'UTF-8') ?>">Open meeting</a><?php endif; ?>
    <?php endif; ?>
  </div></div>

  <div class="col-lg-6"><div class="foundation-card h-100"><h2 class="h5 fw-bold">Package balance</h2>
    <div class="row g-2 text-center">
      <div class="col-4"><div class="border rounded-4 p-2"><strong><?= htmlspecialchars((string) $data['balance']['total'], ENT_QUOTES, 'UTF-8') ?></strong><br><small>Total</small></div></div>
      <div class="col-4"><div class="border rounded-4 p-2"><strong><?= htmlspecialchars((string) $data['balance']['used'], ENT_QUOTES, 'UTF-8') ?></strong><br><small>Used</small></div></div>
      <div class="col-4"><div class="border rounded-4 p-2"><strong><?= htmlspecialchars((string) $data['balance']['remaining'], ENT_QUOTES, 'UTF-8') ?></strong><br><small>Left</small></div></div>
    </div>
    <?= ux_helper_text('Balance shows how many lesson credits are available after completed, late-cancelled, or no-show sessions.') ?>
    <a class="btn btn-sm btn-outline-brand mt-3" href="/student/balance">View balance</a>
  </div></div>

  <div class="col-lg-6"><div class="foundation-card h-100"><h2 class="h5 fw-bold">Current homework</h2>
    <?php if (!$data['current_homework']): ?>
      <?= ux_empty_state('No homework waiting', 'When your teacher assigns homework, it will appear here with a clear action button.', '/student/help', 'How homework works') ?>
    <?php else: ?>
      <div class="fw-bold"><?= htmlspecialchars($data['current_homework']['title'], ENT_QUOTES, 'UTF-8') ?></div>
      <small class="text-muted">Due: <?= htmlspecialchars($data['current_homework']['due_at'] ?? '-', ENT_QUOTES, 'UTF-8') ?></small><br>
      <a class="btn btn-sm btn-brand mt-2" href="/student/homework">Open homework</a>
    <?php endif; ?>
  </div></div>

  <div class="col-lg-6"><div class="foundation-card h-100"><h2 class="h5 fw-bold">Current scenario</h2>
    <?php if (!$data['current_scenario']): ?>
      <?= ux_empty_state('No speaking scenario waiting', 'Speaking scenarios appear here when your teacher wants you to practice a real-life situation.', '/student/help', 'How scenarios work') ?>
    <?php else: ?>
      <div class="fw-bold"><?= htmlspecialchars($data['current_scenario']['title'], ENT_QUOTES, 'UTF-8') ?></div>
      <small class="text-muted"><?= htmlspecialchars($data['current_scenario']['situation'] ?? '', ENT_QUOTES, 'UTF-8') ?></small><br>
      <a class="btn btn-sm btn-brand mt-2" href="/student/scenarios">Practice scenario</a>
    <?php endif; ?>
  </div></div>

  <div class="col-lg-4"><div class="status-box h-100"><h2 class="h6 fw-bold">Progress summary</h2><div>Completed sessions: <?= (int) $data['progress']['completed_sessions'] ?></div><div>Homework submitted: <?= (int) $data['progress']['submitted_homeworks'] ?></div><div>Scenarios submitted: <?= (int) $data['progress']['submitted_scenarios'] ?></div></div></div>
  <div class="col-lg-4"><div class="status-box h-100"><h2 class="h6 fw-bold">Practice words due</h2><div class="display-6"><?= (int) $data['practice_due'] ?></div><a class="btn btn-sm btn-outline-brand" href="/student/practice-words">Review words</a></div></div>
  <div class="col-lg-4"><div class="status-box h-100"><h2 class="h6 fw-bold">Badges</h2><?php if (!$data['badges']): ?><div class="text-muted">No badges yet. Submit work and keep practicing to earn your first badge.</div><?php else: foreach ($data['badges'] as $badge): ?><span class="badge badge-earned text-bg-light border me-1 mb-1"><?= htmlspecialchars($badge['name_en'], ENT_QUOTES, 'UTF-8') ?></span><?php endforeach; endif; ?></div></div>

  <div class="col-lg-6"><div class="foundation-card h-100"><h2 class="h5 fw-bold">Session notes</h2>
    <?php if (!$data['session_notes']): ?><?= ux_empty_state('No session notes yet', 'Teacher notes will appear after lessons and help you know what to review next.', '/student/help', 'How feedback works') ?><?php else: foreach ($data['session_notes'] as $note): ?><div class="border rounded-4 p-2 mb-2"><strong><?= htmlspecialchars($note['title'], ENT_QUOTES, 'UTF-8') ?></strong><br><small class="text-muted"><?= htmlspecialchars($note['start_at'], ENT_QUOTES, 'UTF-8') ?></small><div><?= nl2br(htmlspecialchars($note['notes'], ENT_QUOTES, 'UTF-8')) ?></div></div><?php endforeach; endif; ?>
    <a class="btn btn-sm btn-outline-brand" href="/student/session-notes">All notes</a>
  </div></div>

  <div class="col-lg-6"><div class="foundation-card h-100"><h2 class="h5 fw-bold">Notifications</h2>
    <?php if (!$data['notifications']): ?><?= ux_empty_state('No notifications yet', 'New homework, corrections, lesson updates, and materials will appear here.', '/student/help', 'How notifications work') ?><?php else: foreach ($data['notifications'] as $notification): ?><div class="border rounded-4 p-2 mb-2"><strong><?= htmlspecialchars($notification['title'], ENT_QUOTES, 'UTF-8') ?></strong><br><small class="text-muted"><?= htmlspecialchars($notification['created_at'], ENT_QUOTES, 'UTF-8') ?></small></div><?php endforeach; endif; ?>
    <a class="btn btn-sm btn-outline-brand" href="/student/notifications">All notifications</a>
  </div></div>
</div>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Student Dashboard', $content);
