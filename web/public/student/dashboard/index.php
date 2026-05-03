<?php
/**
 * /student/dashboard
 * Phase 10 adult student portal dashboard.
 */

require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../backend/php/shared/StudentPortal.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';

$user = require_role('student');
$data = student_portal_dashboard((int) $user['id']);
$profile = $data['profile'];

ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
  <div>
    <p class="text-muted mb-1">Welcome back, <?= htmlspecialchars($user['display_name'], ENT_QUOTES, 'UTF-8') ?>.</p>
    <small class="text-muted">Your Arabic learning overview in one place.</small>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <a class="btn btn-brand" href="/student/book">Book lesson</a>
    <a class="btn btn-outline-brand" href="/student/notifications">Notifications <?= $data['unread_notifications'] ? '(' . (int) $data['unread_notifications'] . ')' : '' ?></a>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-4"><div class="status-box h-100"><strong>Current level</strong><br><span class="display-6"><?= htmlspecialchars($profile['current_level'] ?? '-', ENT_QUOTES, 'UTF-8') ?></span></div></div>
  <div class="col-md-4"><div class="status-box h-100"><strong>Remaining credits</strong><br><span class="display-6"><?= htmlspecialchars((string) $data['balance']['remaining'], ENT_QUOTES, 'UTF-8') ?></span></div></div>
  <div class="col-md-4"><div class="status-box h-100"><strong>Streak</strong><br><span class="display-6"><?= (int) $data['streak'] ?></span><span class="text-muted"> days</span></div></div>
</div>

<div class="row g-4">
  <div class="col-lg-6">
    <div class="foundation-card h-100">
      <h2 class="h5 fw-bold">Upcoming lesson</h2>
      <?php if (!$data['upcoming_lesson']): ?>
        <p class="text-muted">No upcoming lesson yet.</p><a class="btn btn-sm btn-outline-brand" href="/student/book">Book a lesson</a>
      <?php else: ?>
        <div class="fw-bold"><?= htmlspecialchars($data['upcoming_lesson']['start_at'], ENT_QUOTES, 'UTF-8') ?></div>
        <div class="text-muted">Status: <?= htmlspecialchars($data['upcoming_lesson']['status'], ENT_QUOTES, 'UTF-8') ?></div>
        <?php if (!empty($data['upcoming_lesson']['meeting_link'])): ?><a class="btn btn-sm btn-outline-brand mt-2" target="_blank" href="<?= htmlspecialchars($data['upcoming_lesson']['meeting_link'], ENT_QUOTES, 'UTF-8') ?>">Open meeting</a><?php endif; ?>
      <?php endif; ?>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="foundation-card h-100">
      <h2 class="h5 fw-bold">Package balance</h2>
      <div class="row g-2 text-center">
        <div class="col-4"><div class="border rounded-4 p-2"><strong><?= htmlspecialchars((string) $data['balance']['total'], ENT_QUOTES, 'UTF-8') ?></strong><br><small>Total</small></div></div>
        <div class="col-4"><div class="border rounded-4 p-2"><strong><?= htmlspecialchars((string) $data['balance']['used'], ENT_QUOTES, 'UTF-8') ?></strong><br><small>Used</small></div></div>
        <div class="col-4"><div class="border rounded-4 p-2"><strong><?= htmlspecialchars((string) $data['balance']['remaining'], ENT_QUOTES, 'UTF-8') ?></strong><br><small>Left</small></div></div>
      </div>
      <a class="btn btn-sm btn-outline-brand mt-3" href="/student/balance">View balance</a>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="foundation-card h-100">
      <h2 class="h5 fw-bold">Current homework</h2>
      <?php if (!$data['current_homework']): ?>
        <p class="text-muted">No homework waiting for you.</p>
      <?php else: ?>
        <div class="fw-bold"><?= htmlspecialchars($data['current_homework']['title'], ENT_QUOTES, 'UTF-8') ?></div>
        <small class="text-muted">Due: <?= htmlspecialchars($data['current_homework']['due_at'] ?? '-', ENT_QUOTES, 'UTF-8') ?></small><br>
        <a class="btn btn-sm btn-outline-brand mt-2" href="/student/homework">Open homework</a>
      <?php endif; ?>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="foundation-card h-100">
      <h2 class="h5 fw-bold">Current scenario</h2>
      <?php if (!$data['current_scenario']): ?>
        <p class="text-muted">No speaking scenario waiting for you.</p>
      <?php else: ?>
        <div class="fw-bold"><?= htmlspecialchars($data['current_scenario']['title'], ENT_QUOTES, 'UTF-8') ?></div>
        <small class="text-muted"><?= htmlspecialchars($data['current_scenario']['situation'] ?? '', ENT_QUOTES, 'UTF-8') ?></small><br>
        <a class="btn btn-sm btn-outline-brand mt-2" href="/student/scenarios">Practice scenario</a>
      <?php endif; ?>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="status-box h-100"><h2 class="h6 fw-bold">Progress summary</h2><div>Completed sessions: <?= (int) $data['progress']['completed_sessions'] ?></div><div>Homework submitted: <?= (int) $data['progress']['submitted_homeworks'] ?></div><div>Scenarios submitted: <?= (int) $data['progress']['submitted_scenarios'] ?></div></div>
  </div>
  <div class="col-lg-4">
    <div class="status-box h-100"><h2 class="h6 fw-bold">Practice words due</h2><div class="display-6"><?= (int) $data['practice_due'] ?></div><a class="btn btn-sm btn-outline-brand" href="/student/practice-words">Review words</a></div>
  </div>
  <div class="col-lg-4">
    <div class="status-box h-100"><h2 class="h6 fw-bold">Badges</h2><?php if (!$data['badges']): ?><div class="text-muted">No badges yet.</div><?php else: foreach ($data['badges'] as $badge): ?><span class="badge text-bg-light border me-1 mb-1"><?= htmlspecialchars($badge['name_en'], ENT_QUOTES, 'UTF-8') ?></span><?php endforeach; endif; ?></div>
  </div>

  <div class="col-lg-6">
    <div class="foundation-card h-100">
      <h2 class="h5 fw-bold">Session notes</h2>
      <?php if (!$data['session_notes']): ?><p class="text-muted">No session notes yet.</p><?php else: foreach ($data['session_notes'] as $note): ?><div class="border rounded-4 p-2 mb-2"><strong><?= htmlspecialchars($note['title'], ENT_QUOTES, 'UTF-8') ?></strong><br><small class="text-muted"><?= htmlspecialchars($note['start_at'], ENT_QUOTES, 'UTF-8') ?></small><div><?= nl2br(htmlspecialchars($note['notes'], ENT_QUOTES, 'UTF-8')) ?></div></div><?php endforeach; endif; ?>
      <a class="btn btn-sm btn-outline-brand" href="/student/session-notes">All notes</a>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="foundation-card h-100">
      <h2 class="h5 fw-bold">Notifications</h2>
      <?php if (!$data['notifications']): ?><p class="text-muted">No notifications yet.</p><?php else: foreach ($data['notifications'] as $notification): ?><div class="border rounded-4 p-2 mb-2"><strong><?= htmlspecialchars($notification['title'], ENT_QUOTES, 'UTF-8') ?></strong><br><small class="text-muted"><?= htmlspecialchars($notification['created_at'], ENT_QUOTES, 'UTF-8') ?></small></div><?php endforeach; endif; ?>
      <a class="btn btn-sm btn-outline-brand" href="/student/notifications">All notifications</a>
    </div>
  </div>
</div>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Student Dashboard', $content);
