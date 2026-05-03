<?php
/**
 * /owner/level-checks
 *
 * Owner list of adult level checks and child literacy checks.
 */

require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../backend/php/shared/LevelCheck.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';

$user = require_role('owner_teacher');
$attempts = level_latest_attempts();

ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
  <div>
    <p class="text-muted mb-1">Review submitted adult level checks and child literacy checks.</p>
    <small class="text-muted">Owner final review decides the confirmed level.</small>
  </div>
  <a class="btn btn-outline-brand" href="/owner/onboarding">Onboarding pipeline</a>
</div>

<?php if (!$attempts): ?>
  <div class="alert alert-light border">No level checks submitted yet.</div>
<?php else: ?>
  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead>
        <tr>
          <th>Submitted</th>
          <th>Learner</th>
          <th>Type</th>
          <th>Plan</th>
          <th>Auto score</th>
          <th>Suggested level</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($attempts as $attempt): ?>
          <tr>
            <td class="small text-muted"><?= htmlspecialchars($attempt['submitted_at'] ?? $attempt['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
            <td>
              <strong><?= htmlspecialchars($attempt['learner_name'] ?? $attempt['full_name'] ?? 'Learner', ENT_QUOTES, 'UTF-8') ?></strong><br>
              <small class="text-muted"><?= htmlspecialchars($attempt['email'] ?? '', ENT_QUOTES, 'UTF-8') ?></small>
            </td>
            <td><span class="badge text-bg-light border"><?= htmlspecialchars($attempt['attempt_type'], ENT_QUOTES, 'UTF-8') ?></span></td>
            <td><?= htmlspecialchars($attempt['plan_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars((string) ($attempt['auto_score'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>%</td>
            <td><?= htmlspecialchars($attempt['suggested_level'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
            <td><span class="badge text-bg-light border"><?= htmlspecialchars($attempt['status'], ENT_QUOTES, 'UTF-8') ?></span></td>
            <td><a class="btn btn-sm btn-outline-brand" href="/owner/level-checks/view?id=<?= (int) $attempt['id'] ?>">Review</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Owner Level Checks', $content);
