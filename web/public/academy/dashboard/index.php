<?php
/**
 * /academy/dashboard
 * Academy Partner dashboard for student briefs.
 */

require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../backend/php/shared/AcademyPortal.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';

$user = require_role('academy_partner');
$briefs = academy_partner_briefs((int) $user['id']);
$counts = ['submitted' => 0, 'under_review' => 0, 'converted_to_student' => 0, 'rejected' => 0];
foreach ($briefs as $brief) {
    if (isset($counts[$brief['status']])) $counts[$brief['status']]++;
}

ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
  <div>
    <p class="text-muted mb-1">Submit and track student briefs sent to Habiba Nabil Arabic Academy.</p>
    <small class="text-muted">You can only see briefs submitted by your academy account.</small>
  </div>
  <a class="btn btn-brand" href="/academy/briefs/new">Submit new brief</a>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-3"><div class="status-box h-100"><strong>Submitted</strong><br><span class="display-6"><?= (int) $counts['submitted'] ?></span></div></div>
  <div class="col-md-3"><div class="status-box h-100"><strong>Under review</strong><br><span class="display-6"><?= (int) $counts['under_review'] ?></span></div></div>
  <div class="col-md-3"><div class="status-box h-100"><strong>Converted</strong><br><span class="display-6"><?= (int) $counts['converted_to_student'] ?></span></div></div>
  <div class="col-md-3"><div class="status-box h-100"><strong>Rejected</strong><br><span class="display-6"><?= (int) $counts['rejected'] ?></span></div></div>
</div>

<div class="foundation-card">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h5 fw-bold mb-0">Latest briefs</h2>
    <a class="btn btn-sm btn-outline-brand" href="/academy/briefs">View all</a>
  </div>
  <?php if (!$briefs): ?>
    <div class="alert alert-light border">No briefs submitted yet.</div>
  <?php else: ?>
    <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Student</th><th>Level</th><th>Goal</th><th>Status</th><th>Action</th></tr></thead><tbody>
    <?php foreach (array_slice($briefs, 0, 10) as $brief): ?>
      <tr>
        <td><strong><?= htmlspecialchars($brief['student_name'] ?: $brief['contact_name'], ENT_QUOTES, 'UTF-8') ?></strong><br><small class="text-muted"><?= htmlspecialchars($brief['created_at'], ENT_QUOTES, 'UTF-8') ?></small></td>
        <td><?= htmlspecialchars($brief['current_arabic_level'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= htmlspecialchars(mb_strimwidth((string) ($brief['goal'] ?: $brief['goals']), 0, 60, '...'), ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= academy_status_badge($brief['status']) ?></td>
        <td><a class="btn btn-sm btn-outline-brand" href="/academy/briefs/view?id=<?= (int) $brief['id'] ?>">Open</a></td>
      </tr>
    <?php endforeach; ?>
    </tbody></table></div>
  <?php endif; ?>
</div>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Academy Partner Dashboard', $content);
