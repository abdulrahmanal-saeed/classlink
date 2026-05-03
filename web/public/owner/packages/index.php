<?php
/**
 * /owner/packages
 * Owner package balance overview.
 */

require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../backend/php/shared/LessonCredits.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';

$user = require_role('owner_teacher');
$packages = credits_all_packages();

$totalCredits = 0;
$remainingCredits = 0;
foreach ($packages as $package) {
    $totalCredits += (float) $package['total_credits'];
    if ($package['status'] === 'active') {
        $remainingCredits += (float) $package['remaining_credits'];
    }
}
$usedCredits = max(0, $totalCredits - $remainingCredits);

ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
  <div>
    <p class="text-muted mb-1">Track lesson packages, used credits, and remaining balance.</p>
    <small class="text-muted">Credits are updated by attendance and manual adjustments.</small>
  </div>
  <a class="btn btn-outline-brand" href="/owner/students">Students</a>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-4"><div class="status-box h-100"><strong>Total credits</strong><br><span class="display-6"><?= htmlspecialchars((string) $totalCredits, ENT_QUOTES, 'UTF-8') ?></span></div></div>
  <div class="col-md-4"><div class="status-box h-100"><strong>Used credits</strong><br><span class="display-6"><?= htmlspecialchars((string) $usedCredits, ENT_QUOTES, 'UTF-8') ?></span></div></div>
  <div class="col-md-4"><div class="status-box h-100"><strong>Remaining credits</strong><br><span class="display-6"><?= htmlspecialchars((string) $remainingCredits, ENT_QUOTES, 'UTF-8') ?></span></div></div>
</div>

<?php if (!$packages): ?>
  <div class="alert alert-light border">No lesson packages yet.</div>
<?php else: ?>
  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead><tr><th>Student</th><th>Package</th><th>Total</th><th>Remaining</th><th>Used</th><th>Status</th><th>Action</th></tr></thead>
      <tbody>
        <?php foreach ($packages as $package): ?>
          <?php $used = max(0, (float) $package['total_credits'] - (float) $package['remaining_credits']); ?>
          <tr>
            <td><strong><?= htmlspecialchars($package['student_name'], ENT_QUOTES, 'UTF-8') ?></strong><br><small class="text-muted"><?= htmlspecialchars($package['student_email'], ENT_QUOTES, 'UTF-8') ?></small></td>
            <td><?= htmlspecialchars($package['package_name'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars((string) $package['total_credits'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars((string) $package['remaining_credits'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars((string) $used, ENT_QUOTES, 'UTF-8') ?></td>
            <td><span class="badge text-bg-light border"><?= htmlspecialchars($package['status'], ENT_QUOTES, 'UTF-8') ?></span></td>
            <td><a class="btn btn-sm btn-outline-brand" href="/owner/students/credits?id=<?= (int) $package['student_user_id'] ?>">Credits</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Lesson Packages', $content);
