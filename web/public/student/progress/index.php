<?php
/**
 * /student/progress
 * Student learning progress dashboard.
 */

require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../backend/php/shared/LearningEngagement.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';

$user = require_role('student');
$progress = engagement_progress((int) $user['id']);

ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
  <div>
    <p class="text-muted mb-1">Your learning progress, streak, activity, and badges.</p>
    <small class="text-muted">Activity is recorded from homework, scenarios, reviews, flashcards, level checks, and sessions.</small>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <a class="btn btn-brand" href="/student/flashcards">Review flashcards</a>
    <a class="btn btn-outline-brand" href="/student/badges">My badges</a>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-3"><div class="status-box h-100"><strong>Streak</strong><br><span class="display-6"><?= (int) $progress['streak'] ?></span><small class="text-muted"> days</small></div></div>
  <div class="col-md-3"><div class="status-box h-100"><strong>Activities</strong><br><span class="display-6"><?= (int) $progress['activity_count'] ?></span></div></div>
  <div class="col-md-3"><div class="status-box h-100"><strong>Due flashcards</strong><br><span class="display-6"><?= (int) $progress['due_flashcards'] ?></span></div></div>
  <div class="col-md-3"><div class="status-box h-100"><strong>Badges</strong><br><span class="display-6"><?= count($progress['badges']) ?></span></div></div>
</div>

<div class="row g-4">
  <div class="col-lg-6">
    <div class="foundation-card h-100">
      <h2 class="h5 fw-bold">Progress summary</h2>
      <div>Completed sessions: <?= (int) $progress['completed_sessions'] ?></div>
      <div>Submitted homework: <?= (int) $progress['summary']['submitted_homeworks'] ?></div>
      <div>Submitted scenarios: <?= (int) $progress['summary']['submitted_scenarios'] ?></div>
      <div>Practice words mastered: <?= (int) $progress['practice_words_mastered'] ?> / <?= (int) $progress['practice_words_total'] ?></div>
      <div>Activities this week: <?= (int) $progress['week_activity_count'] ?></div>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="foundation-card h-100">
      <h2 class="h5 fw-bold">Badges preview</h2>
      <?php if (!$progress['badges']): ?>
        <div class="alert alert-light border mb-0">No badges yet. Complete activities to unlock your first badge.</div>
      <?php else: ?>
        <?php foreach (array_slice($progress['badges'], 0, 8) as $badge): ?>
          <span class="badge text-bg-light border me-1 mb-1"><?= htmlspecialchars(($badge['icon'] ? $badge['icon'] . ' ' : '') . $badge['name_en'], ENT_QUOTES, 'UTF-8') ?></span>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
  <div class="col-12">
    <div class="foundation-card">
      <h2 class="h5 fw-bold">Recent activity</h2>
      <?php if (!$progress['recent_activity']): ?>
        <div class="alert alert-light border mb-0">No activity yet.</div>
      <?php else: ?>
        <div class="table-responsive"><table class="table table-sm align-middle"><thead><tr><th>Date</th><th>Activity</th><th>Source</th></tr></thead><tbody>
        <?php foreach ($progress['recent_activity'] as $activity): ?>
          <tr><td><?= htmlspecialchars($activity['created_at'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($activity['activity_type'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars(($activity['source_type'] ?? '-') . ' #' . ($activity['source_id'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td></tr>
        <?php endforeach; ?>
        </tbody></table></div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'My Progress', $content);
