<?php
/**
 * /owner/students/view?id=...
 * Phase 13 full Owner student detail.
 */

require_once __DIR__ . '/../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../backend/php/shared/OwnerDashboard.php';
require_once __DIR__ . '/../../../../../web/components/layout/dashboard_shell.php';

$user = require_role('owner_teacher');
$studentId = (int) ($_GET['id'] ?? 0);
$data = owner_student_detail_full($studentId);

if (!$data) {
    http_response_code(404);
    $content = '<div class="alert alert-danger">Student not found.</div><a class="btn btn-outline-brand" href="/owner/students">Back</a>';
    render_dashboard_shell($user, 'Student Not Found', $content);
    exit;
}

$profile = $data['profile'];
$balance = $data['balance'];

ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
  <div>
    <p class="text-muted mb-1">Complete student profile, learning history, and operational records.</p>
    <h2 class="h4 fw-bold mb-0"><?= htmlspecialchars($profile['display_name'], ENT_QUOTES, 'UTF-8') ?></h2>
    <small class="text-muted">User ID: <?= (int) $studentId ?> · <?= htmlspecialchars($profile['email'], ENT_QUOTES, 'UTF-8') ?></small>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <a class="btn btn-outline-brand" href="/owner/students">Back</a>
    <a class="btn btn-outline-brand" href="/owner/students/credits?id=<?= (int) $studentId ?>">Credits</a>
    <a class="btn btn-brand" href="/owner/bookings">Bookings</a>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-3"><div class="status-box h-100"><strong>Level</strong><br><span class="display-6"><?= htmlspecialchars($profile['current_level'] ?? '-', ENT_QUOTES, 'UTF-8') ?></span></div></div>
  <div class="col-md-3"><div class="status-box h-100"><strong>Remaining credits</strong><br><span class="display-6"><?= htmlspecialchars((string) $balance['remaining'], ENT_QUOTES, 'UTF-8') ?></span></div></div>
  <div class="col-md-3"><div class="status-box h-100"><strong>Completed sessions</strong><br><span class="display-6"><?= (int) $data['progress']['completed_sessions'] ?></span></div></div>
  <div class="col-md-3"><div class="status-box h-100"><strong>Practice words</strong><br><span class="display-6"><?= count($data['practice_words']) ?></span></div></div>
</div>

<div class="row g-4">
  <div class="col-lg-6">
    <div class="foundation-card h-100">
      <h2 class="h5 fw-bold">Profile</h2>
      <dl class="row mb-0 mt-3">
        <dt class="col-sm-4">Name</dt><dd class="col-sm-8"><?= htmlspecialchars($profile['display_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>
        <dt class="col-sm-4">Email</dt><dd class="col-sm-8"><?= htmlspecialchars($profile['email'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>
        <dt class="col-sm-4">Phone</dt><dd class="col-sm-8"><?= htmlspecialchars($profile['phone'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>
        <dt class="col-sm-4">Country</dt><dd class="col-sm-8"><?= htmlspecialchars($profile['country'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>
        <dt class="col-sm-4">Learner type</dt><dd class="col-sm-8"><?= htmlspecialchars($profile['learner_type'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>
        <dt class="col-sm-4">Goal</dt><dd class="col-sm-8"><?= htmlspecialchars($profile['learning_goal'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>
        <dt class="col-sm-4">Dialect</dt><dd class="col-sm-8"><?= htmlspecialchars($profile['preferred_dialect'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>
      </dl>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="foundation-card h-100">
      <h2 class="h5 fw-bold">Package balance</h2>
      <div class="row g-2 text-center">
        <div class="col-4"><div class="border rounded-4 p-2"><strong><?= htmlspecialchars((string) $balance['total'], ENT_QUOTES, 'UTF-8') ?></strong><br><small>Total</small></div></div>
        <div class="col-4"><div class="border rounded-4 p-2"><strong><?= htmlspecialchars((string) $balance['used'], ENT_QUOTES, 'UTF-8') ?></strong><br><small>Used</small></div></div>
        <div class="col-4"><div class="border rounded-4 p-2"><strong><?= htmlspecialchars((string) $balance['remaining'], ENT_QUOTES, 'UTF-8') ?></strong><br><small>Left</small></div></div>
      </div>
      <?php if (!$balance['packages']): ?><div class="alert alert-light border mt-3 mb-0">No package yet.</div><?php else: foreach ($balance['packages'] as $package): ?><div class="border rounded-4 p-2 mt-2"><strong><?= htmlspecialchars($package['package_name'], ENT_QUOTES, 'UTF-8') ?></strong><br><small class="text-muted"><?= htmlspecialchars((string) $package['remaining_credits'], ENT_QUOTES, 'UTF-8') ?> / <?= htmlspecialchars((string) $package['total_credits'], ENT_QUOTES, 'UTF-8') ?> credits</small></div><?php endforeach; endif; ?>
    </div>
  </div>

  <div class="col-12">
    <div class="foundation-card">
      <h2 class="h5 fw-bold">Upcoming / past lessons</h2>
      <?php if (!$data['lessons']): ?><div class="alert alert-light border">No lessons yet.</div><?php else: ?>
        <div class="table-responsive"><table class="table table-sm align-middle"><thead><tr><th>Date</th><th>Status</th><th>Meeting</th><th>Package</th></tr></thead><tbody>
        <?php foreach (array_slice($data['lessons'], 0, 20) as $lesson): ?><tr><td><?= htmlspecialchars($lesson['start_at'], ENT_QUOTES, 'UTF-8') ?> → <?= htmlspecialchars($lesson['end_at'], ENT_QUOTES, 'UTF-8') ?></td><td><span class="badge text-bg-light border"><?= htmlspecialchars($lesson['status'], ENT_QUOTES, 'UTF-8') ?></span></td><td><?= !empty($lesson['meeting_link']) ? '<a target="_blank" href="' . htmlspecialchars($lesson['meeting_link'], ENT_QUOTES, 'UTF-8') . '">Open</a>' : '-' ?></td><td><?= htmlspecialchars($lesson['package_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td></tr><?php endforeach; ?>
        </tbody></table></div>
      <?php endif; ?>
    </div>
  </div>

  <div class="col-lg-6"><div class="foundation-card h-100"><h2 class="h5 fw-bold">Homework history</h2><?php if (!$data['homeworks']): ?><p class="text-muted">No homework yet.</p><?php else: foreach (array_slice($data['homeworks'],0,8) as $hw): ?><div class="border rounded-4 p-2 mb-2"><strong><?= htmlspecialchars($hw['title'], ENT_QUOTES, 'UTF-8') ?></strong><br><small class="text-muted"><?= htmlspecialchars($hw['submission_status'] ?? $hw['status'], ENT_QUOTES, 'UTF-8') ?></small></div><?php endforeach; endif; ?></div></div>
  <div class="col-lg-6"><div class="foundation-card h-100"><h2 class="h5 fw-bold">Scenarios</h2><?php if (!$data['scenarios']): ?><p class="text-muted">No scenarios yet.</p><?php else: foreach (array_slice($data['scenarios'],0,8) as $sc): ?><div class="border rounded-4 p-2 mb-2"><strong><?= htmlspecialchars($sc['title'], ENT_QUOTES, 'UTF-8') ?></strong><br><small class="text-muted"><?= htmlspecialchars($sc['submitted_at'] ? 'submitted' : $sc['status'], ENT_QUOTES, 'UTF-8') ?></small></div><?php endforeach; endif; ?></div></div>
  <div class="col-lg-6"><div class="foundation-card h-100"><h2 class="h5 fw-bold">Reviews</h2><?php if (!$data['reviews']): ?><p class="text-muted">No reviews yet.</p><?php else: foreach (array_slice($data['reviews'],0,8) as $rv): ?><div class="border rounded-4 p-2 mb-2"><strong><?= htmlspecialchars($rv['title'], ENT_QUOTES, 'UTF-8') ?></strong><br><small class="text-muted">Score: <?= htmlspecialchars((string) ($rv['score'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></small></div><?php endforeach; endif; ?></div></div>
  <div class="col-lg-6"><div class="foundation-card h-100"><h2 class="h5 fw-bold">Practice words</h2><?php if (!$data['practice_words']): ?><p class="text-muted">No practice words.</p><?php else: foreach (array_slice($data['practice_words'],0,10) as $word): ?><span class="badge text-bg-light border me-1 mb-1" dir="rtl"><?= htmlspecialchars($word['word_ar'], ENT_QUOTES, 'UTF-8') ?></span><?php endforeach; endif; ?></div></div>

  <div class="col-lg-6"><div class="foundation-card h-100"><h2 class="h5 fw-bold">Common mistakes</h2><div class="alert alert-light border mb-0">No common mistakes module/table yet. This will be expanded in a later phase.</div></div></div>
  <div class="col-lg-6"><div class="foundation-card h-100"><h2 class="h5 fw-bold">AI notes / summaries</h2><div class="alert alert-light border mb-0">No AI summaries generated yet.</div></div></div>

  <div class="col-12"><div class="foundation-card"><h2 class="h5 fw-bold">Session notes</h2><?php if (!$data['session_notes']): ?><p class="text-muted">No session notes yet.</p><?php else: foreach (array_slice($data['session_notes'],0,10) as $note): ?><div class="border rounded-4 p-2 mb-2"><strong><?= htmlspecialchars($note['title'], ENT_QUOTES, 'UTF-8') ?></strong><br><small class="text-muted"><?= htmlspecialchars($note['start_at'], ENT_QUOTES, 'UTF-8') ?></small><div><?= nl2br(htmlspecialchars($note['notes'], ENT_QUOTES, 'UTF-8')) ?></div></div><?php endforeach; endif; ?></div></div>

  <div class="col-12"><div class="foundation-card"><h2 class="h5 fw-bold">Audit history related to student</h2><?php if (!$data['audit']): ?><p class="text-muted">No related audit history.</p><?php else: ?><div class="table-responsive"><table class="table table-sm align-middle"><thead><tr><th>Date</th><th>Action</th><th>Entity</th></tr></thead><tbody><?php foreach ($data['audit'] as $audit): ?><tr><td><?= htmlspecialchars($audit['created_at'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($audit['action'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars(($audit['entity_type'] ?? '-') . ' #' . ($audit['entity_id'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></div></div>
</div>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Student Detail', $content);
