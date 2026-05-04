<?php
/**
 * /owner/dashboard
 * Phase 13 central Owner/Teacher dashboard.
 * Phase 32 adds UX clarity: status, next action, better empty states, and hierarchy.
 */

require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../backend/php/shared/OwnerDashboard.php';
require_once __DIR__ . '/../../../../backend/php/shared/UXComponents.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';

$user = require_role('owner_teacher');
$stats = owner_dashboard_stats();
$pending = owner_dashboard_pending_items();

function stat_card(string $label, int $value, string $href = '', bool $urgent = false): string
{
    $link = $href ? '<a class="btn btn-sm btn-outline-brand mt-2" href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '">Open</a>' : '';
    $class = $urgent && $value > 0 ? 'status-box h-100 needs-attention' : 'status-box h-100';
    return '<div class="col-md-6 col-xl-3"><div class="' . $class . '"><strong>' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</strong><br><span class="display-6">' . (int) $value . '</span>' . $link . '</div></div>';
}

$urgentCount = (int)$stats['pending_payments'] + (int)$stats['pending_level_checks'] + (int)$stats['reviews_pending_correction'] + (int)$stats['students_low_on_credits'];

ob_start();
?>
<?= ux_page_intro('Owner command center', 'What needs your attention today?', 'Review urgent payments, bookings, submissions, low credits, and corrections from one place. Start with the highlighted cards first.', [
  ['label' => 'Review payments', 'href' => '/owner/payments', 'primary' => $stats['pending_payments'] > 0],
  ['label' => 'Open onboarding', 'href' => '/owner/onboarding'],
  ['label' => 'Help', 'href' => '/owner/help'],
]) ?>

<?php if ($urgentCount > 0): ?>
  <?= ux_next_step_card('Start with urgent pending actions', 'There are items that may block students from moving forward. Review payments, level checks, corrections, or low credit accounts first.', '/owner/payments', 'Review urgent items') ?>
<?php else: ?>
  <?= ux_next_step_card('No urgent blockers right now', 'Use quick actions to create learning work, review upcoming lessons, or check recent activity.', '/owner/calendar', 'Check today’s calendar', 'outline') ?>
<?php endif; ?>

<div class="row g-3 mb-4">
  <?= stat_card('Pending payments', $stats['pending_payments'], '/owner/payments', true) ?>
  <?= stat_card('Pending level checks', $stats['pending_level_checks'], '/owner/level-checks', true) ?>
  <?= stat_card('Reviews pending correction', $stats['reviews_pending_correction'], '/owner/reviews', true) ?>
  <?= stat_card('Students low on credits', $stats['students_low_on_credits'], '/owner/packages', true) ?>
  <?= stat_card('Upcoming lessons today', $stats['upcoming_lessons_today'], '/owner/calendar') ?>
  <?= stat_card('Homework submitted', $stats['homework_submitted'], '/owner/homework') ?>
  <?= stat_card('Scenarios submitted', $stats['scenarios_submitted'], '/owner/scenarios') ?>
  <?= stat_card('AI weekly summaries due', $stats['ai_weekly_summaries_due'], '/owner/students') ?>
  <?= stat_card('Active students', $stats['active_students'], '/owner/students') ?>
  <?= stat_card('Active child learners', $stats['active_child_learners'], '/owner/students') ?>
</div>

<div class="foundation-card mb-4">
  <h2 class="h5 fw-bold">Quick actions</h2>
  <p class="text-muted">Use these when there are no urgent blockers or when you want to prepare learning work.</p>
  <div class="d-flex gap-2 flex-wrap">
    <a class="btn btn-outline-brand" href="/owner/homework/new">Create homework</a>
    <a class="btn btn-outline-brand" href="/owner/scenarios/new">Create scenario</a>
    <a class="btn btn-outline-brand" href="/owner/materials/new">Add material</a>
    <a class="btn btn-outline-brand" href="/owner/bookings">Review bookings</a>
  </div>
</div>

<div class="row g-4">
  <div class="col-lg-6">
    <div class="foundation-card h-100">
      <h2 class="h5 fw-bold mb-3">Pending booking requests</h2>
      <?php if (!$pending['bookings']): ?>
        <?= ux_empty_state('No booking requests', 'When students or parents request lesson times, they will appear here for confirmation.', '/owner/availability', 'Check availability settings') ?>
      <?php else: ?>
        <?php foreach ($pending['bookings'] as $booking): ?><div class="border rounded-4 p-2 mb-2"><strong><?= htmlspecialchars($booking['student_name'] ?? 'Student', ENT_QUOTES, 'UTF-8') ?></strong><br><small class="text-muted"><?= htmlspecialchars($booking['start_at'], ENT_QUOTES, 'UTF-8') ?> · <?= ux_status_badge($booking['status']) ?></small></div><?php endforeach; ?>
        <a class="btn btn-sm btn-outline-brand" href="/owner/bookings">Open bookings</a>
      <?php endif; ?>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="foundation-card h-100">
      <h2 class="h5 fw-bold mb-3">Pending payments</h2>
      <?php if (!$pending['payments']): ?>
        <?= ux_empty_state('No pending payments', 'New checkout orders that need verification will appear here.', '/owner/payments', 'Open payments') ?>
      <?php else: ?>
        <?php foreach ($pending['payments'] as $payment): ?><div class="border rounded-4 p-2 mb-2"><strong><?= htmlspecialchars($payment['checkout_name'] ?? $payment['plan_name'] ?? 'Purchase', ENT_QUOTES, 'UTF-8') ?></strong><br><small class="text-muted"><?= ux_status_badge($payment['status']) ?> · <?= htmlspecialchars((string) $payment['amount'], ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars($payment['currency'], ENT_QUOTES, 'UTF-8') ?></small></div><?php endforeach; ?>
        <a class="btn btn-sm btn-outline-brand" href="/owner/payments">Open payments</a>
      <?php endif; ?>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="foundation-card h-100">
      <h2 class="h5 fw-bold mb-3">Homework needing attention</h2>
      <?php if (!$pending['homework']): ?>
        <?= ux_empty_state('No homework waiting', 'Submitted homework that needs review or correction will appear here.', '/owner/homework', 'Open homework') ?>
      <?php else: ?>
        <?php foreach ($pending['homework'] as $hw): ?><div class="border rounded-4 p-2 mb-2"><strong><?= htmlspecialchars($hw['title'] ?? 'Homework', ENT_QUOTES, 'UTF-8') ?></strong><br><small class="text-muted"><?= htmlspecialchars($hw['student_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?> · <?= ux_status_badge($hw['status']) ?></small></div><?php endforeach; ?>
        <a class="btn btn-sm btn-outline-brand" href="/owner/homework">Open homework</a>
      <?php endif; ?>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="foundation-card h-100">
      <h2 class="h5 fw-bold mb-3">Students low on credits</h2>
      <?php if (!$pending['low_credits']): ?>
        <?= ux_empty_state('No low-credit students', 'When a student is close to running out of lesson credits, they will appear here.', '/owner/packages', 'Open packages') ?>
      <?php else: ?>
        <?php foreach ($pending['low_credits'] as $pkg): ?><div class="border rounded-4 p-2 mb-2"><strong><?= htmlspecialchars($pkg['student_name'] ?? 'Student', ENT_QUOTES, 'UTF-8') ?></strong><br><small class="text-muted"><?= htmlspecialchars($pkg['package_name'], ENT_QUOTES, 'UTF-8') ?> · Remaining: <?= htmlspecialchars((string) $pkg['remaining_credits'], ENT_QUOTES, 'UTF-8') ?></small></div><?php endforeach; ?>
        <a class="btn btn-sm btn-outline-brand" href="/owner/packages">Open packages</a>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Owner/Teacher Dashboard', $content);
