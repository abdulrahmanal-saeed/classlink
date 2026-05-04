<?php
/**
 * /academy/dashboard
 * Academy Partner dashboard for student briefs.
 * Phase 32 improves status explanation and next actions.
 */

require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../backend/php/shared/AcademyPortal.php';
require_once __DIR__ . '/../../../../backend/php/shared/UXComponents.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';

$user = require_role('academy_partner');
$briefs = academy_partner_briefs((int) $user['id']);
$counts = ['submitted' => 0, 'under_review' => 0, 'converted_to_student' => 0, 'rejected' => 0];
foreach ($briefs as $brief) { if (isset($counts[$brief['status']])) $counts[$brief['status']]++; }

ob_start();
?>
<?= ux_page_intro('Academy partner portal', 'Submit and track student briefs', 'Send student information to Habiba Nabil Arabic Academy and follow the review status. You only see briefs submitted by your academy account.', [
  ['label' => 'Submit new brief', 'href' => '/academy/briefs/new', 'primary' => true],
  ['label' => 'View all briefs', 'href' => '/academy/briefs'],
  ['label' => 'Help', 'href' => '/academy/help'],
]) ?>

<?= ux_next_step_card('Submit a complete brief for faster review', 'Include age, current Arabic level, learning goal, speaking ability, reading/writing ability, parent contact if child, and schedule notes if known.', '/academy/briefs/new', 'Submit new brief') ?>

<div class="row g-3 mb-4">
  <div class="col-md-3"><div class="status-box h-100"><strong>Submitted</strong><br><span class="display-6"><?= (int) $counts['submitted'] ?></span></div></div>
  <div class="col-md-3"><div class="status-box h-100 <?= $counts['under_review'] ? 'needs-attention' : '' ?>"><strong>Under review</strong><br><span class="display-6"><?= (int) $counts['under_review'] ?></span></div></div>
  <div class="col-md-3"><div class="status-box h-100"><strong>Converted</strong><br><span class="display-6"><?= (int) $counts['converted_to_student'] ?></span></div></div>
  <div class="col-md-3"><div class="status-box h-100"><strong>Rejected</strong><br><span class="display-6"><?= (int) $counts['rejected'] ?></span></div></div>
</div>

<div class="foundation-card mb-4">
  <h2 class="h5 fw-bold">Status guide</h2>
  <div class="row g-2 small">
    <div class="col-md-6"><?= ux_status_badge('submitted') ?> Sent and waiting for review.</div>
    <div class="col-md-6"><?= ux_status_badge('under_review') ?> Owner is checking the brief.</div>
    <div class="col-md-6"><?= ux_status_badge('converted_to_student') ?> Brief became onboarding/student flow.</div>
    <div class="col-md-6"><?= ux_status_badge('rejected') ?> Not accepted or not suitable now.</div>
  </div>
</div>

<div class="foundation-card">
  <div class="d-flex justify-content-between align-items-center mb-3"><h2 class="h5 fw-bold mb-0">Latest briefs</h2><a class="btn btn-sm btn-outline-brand" href="/academy/briefs">View all</a></div>
  <?php if (!$briefs): ?>
    <?= ux_empty_state('No briefs submitted yet', 'Submit the first student brief so the Owner can review it and decide the next step.', '/academy/briefs/new', 'Submit first brief') ?>
  <?php else: ?>
    <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Student</th><th>Level</th><th>Goal</th><th>Status</th><th>Next</th></tr></thead><tbody>
    <?php foreach (array_slice($briefs, 0, 10) as $brief): ?>
      <tr>
        <td><strong><?= htmlspecialchars($brief['student_name'] ?: $brief['contact_name'], ENT_QUOTES, 'UTF-8') ?></strong><br><small class="text-muted"><?= htmlspecialchars($brief['created_at'], ENT_QUOTES, 'UTF-8') ?></small></td>
        <td><?= htmlspecialchars($brief['current_arabic_level'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= htmlspecialchars(mb_strimwidth((string) ($brief['goal'] ?: $brief['goals']), 0, 60, '...'), ENT_QUOTES, 'UTF-8') ?></td>
        <td><?= ux_status_badge($brief['status']) ?></td>
        <td><a class="btn btn-sm btn-outline-brand" href="/academy/briefs/view?id=<?= (int) $brief['id'] ?>">Open</a></td>
      </tr>
    <?php endforeach; ?>
    </tbody></table></div>
  <?php endif; ?>
</div>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Academy Partner Dashboard', $content);
