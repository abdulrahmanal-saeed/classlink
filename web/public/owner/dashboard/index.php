<?php
/**
 * /owner/dashboard
 * Phase 13 central Owner/Teacher dashboard.
 */

require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../backend/php/shared/OwnerDashboard.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';

$user = require_role('owner_teacher');
$stats = owner_dashboard_stats();
$pending = owner_dashboard_pending_items();

function stat_card(string $label, int $value, string $href = ''): string
{
    $link = $href ? '<a class="btn btn-sm btn-outline-brand mt-2" href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '">Open</a>' : '';
    return '<div class="col-md-6 col-xl-3"><div class="status-box h-100"><strong>' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</strong><br><span class="display-6">' . (int) $value . '</span>' . $link . '</div></div>';
}

ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
  <div>
    <p class="text-muted mb-1">Central command center for students, parents, bookings, payments, and learning work.</p>
    <small class="text-muted">Use cards for quick status and tables for pending actions.</small>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <a class="btn btn-brand" href="/owner/bookings">Review bookings</a>
    <a class="btn btn-outline-brand" href="/owner/onboarding">Onboarding</a>
  </div>
</div>

<div class="row g-3 mb-4">
  <?= stat_card('Active students', $stats['active_students'], '/owner/students') ?>
  <?= stat_card('Active child learners', $stats['active_child_learners'], '/owner/students') ?>
  <?= stat_card('Pending payments', $stats['pending_payments'], '/owner/payments') ?>
  <?= stat_card('Pending level checks', $stats['pending_level_checks'], '/owner/level-checks') ?>
  <?= stat_card('Upcoming lessons today', $stats['upcoming_lessons_today'], '/owner/calendar') ?>
  <?= stat_card('Homework submitted', $stats['homework_submitted'], '/owner/homework') ?>
  <?= stat_card('Scenarios submitted', $stats['scenarios_submitted'], '/owner/scenarios') ?>
  <?= stat_card('Reviews pending correction', $stats['reviews_pending_correction'], '/owner/reviews') ?>
  <?= stat_card('Students low on credits', $stats['students_low_on_credits'], '/owner/packages') ?>
  <?= stat_card('AI weekly summaries due', $stats['ai_weekly_summaries_due'], '/owner/students') ?>
</div>

<div class="row g-4">
  <div class="col-lg-6">
    <div class="foundation-card h-100">
      <h2 class="h5 fw-bold mb-3">Pending booking requests</h2>
      <?php if (!$pending['bookings']): ?><div class="alert alert-light border">No booking requests.</div><?php else: ?>
        <?php foreach ($pending['bookings'] as $booking): ?><div class="border rounded-4 p-2 mb-2"><strong><?= htmlspecialchars($booking['student_name'] ?? 'Student', ENT_QUOTES, 'UTF-8') ?></strong><br><small class="text-muted"><?= htmlspecialchars($booking['start_at'], ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars($booking['status'], ENT_QUOTES, 'UTF-8') ?></small></div><?php endforeach; ?>
        <a class="btn btn-sm btn-outline-brand" href="/owner/bookings">Open bookings</a>
      <?php endif; ?>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="foundation-card h-100">
      <h2 class="h5 fw-bold mb-3">Pending payments</h2>
      <?php if (!$pending['payments']): ?><div class="alert alert-light border">No pending payments.</div><?php else: ?>
        <?php foreach ($pending['payments'] as $payment): ?><div class="border rounded-4 p-2 mb-2"><strong><?= htmlspecialchars($payment['checkout_name'] ?? $payment['plan_name'] ?? 'Purchase', ENT_QUOTES, 'UTF-8') ?></strong><br><small class="text-muted"><?= htmlspecialchars($payment['status'], ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars((string) $payment['amount'], ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars($payment['currency'], ENT_QUOTES, 'UTF-8') ?></small></div><?php endforeach; ?>
        <a class="btn btn-sm btn-outline-brand" href="/owner/payments">Open payments</a>
      <?php endif; ?>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="foundation-card h-100">
      <h2 class="h5 fw-bold mb-3">Homework needing attention</h2>
      <?php if (!$pending['homework']): ?><div class="alert alert-light border">No homework waiting.</div><?php else: ?>
        <?php foreach ($pending['homework'] as $hw): ?><div class="border rounded-4 p-2 mb-2"><strong><?= htmlspecialchars($hw['title'] ?? 'Homework', ENT_QUOTES, 'UTF-8') ?></strong><br><small class="text-muted"><?= htmlspecialchars($hw['student_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars($hw['status'], ENT_QUOTES, 'UTF-8') ?></small></div><?php endforeach; ?>
        <a class="btn btn-sm btn-outline-brand" href="/owner/homework">Open homework</a>
      <?php endif; ?>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="foundation-card h-100">
      <h2 class="h5 fw-bold mb-3">Students low on credits</h2>
      <?php if (!$pending['low_credits']): ?><div class="alert alert-light border">No low-credit students.</div><?php else: ?>
        <?php foreach ($pending['low_credits'] as $pkg): ?><div class="border rounded-4 p-2 mb-2"><strong><?= htmlspecialchars($pkg['student_name'] ?? 'Student', ENT_QUOTES, 'UTF-8') ?></strong><br><small class="text-muted"><?= htmlspecialchars($pkg['package_name'], ENT_QUOTES, 'UTF-8') ?> · Remaining: <?= htmlspecialchars((string) $pkg['remaining_credits'], ENT_QUOTES, 'UTF-8') ?></small></div><?php endforeach; ?>
        <a class="btn btn-sm btn-outline-brand" href="/owner/packages">Open packages</a>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Owner/Teacher Dashboard', $content);
