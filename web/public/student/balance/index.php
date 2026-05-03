<?php
/**
 * /student/balance
 * Student self-service balance page.
 */

require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../backend/php/shared/LessonCredits.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';

$user = require_role('student');
$summary = credits_student_summary((int) $user['id']);
$transactions = credits_transactions_for_student((int) $user['id']);
$sessions = credits_sessions_for_student((int) $user['id']);

ob_start();
?>
<p class="text-muted">Your lesson package balance and credit history.</p>

<div class="row g-3 mb-4">
  <div class="col-md-4"><div class="status-box h-100"><strong>Total credits</strong><br><span class="display-6"><?= htmlspecialchars((string) $summary['total'], ENT_QUOTES, 'UTF-8') ?></span></div></div>
  <div class="col-md-4"><div class="status-box h-100"><strong>Used credits</strong><br><span class="display-6"><?= htmlspecialchars((string) $summary['used'], ENT_QUOTES, 'UTF-8') ?></span></div></div>
  <div class="col-md-4"><div class="status-box h-100"><strong>Remaining credits</strong><br><span class="display-6"><?= htmlspecialchars((string) $summary['remaining'], ENT_QUOTES, 'UTF-8') ?></span></div></div>
</div>

<div class="foundation-card mb-4">
  <h2 class="h5 fw-bold mb-3">Packages</h2>
  <?php if (!$summary['packages']): ?><div class="alert alert-light border">No active lesson package yet.</div><?php else: ?>
  <div class="table-responsive"><table class="table table-sm align-middle"><thead><tr><th>Package</th><th>Total</th><th>Remaining</th><th>Status</th></tr></thead><tbody>
  <?php foreach ($summary['packages'] as $package): ?><tr><td><?= htmlspecialchars($package['package_name'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars((string) $package['total_credits'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars((string) $package['remaining_credits'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($package['status'], ENT_QUOTES, 'UTF-8') ?></td></tr><?php endforeach; ?>
  </tbody></table></div><?php endif; ?>
</div>

<div class="foundation-card mb-4">
  <h2 class="h5 fw-bold mb-3">Upcoming / recent sessions</h2>
  <?php if (!$sessions): ?><div class="alert alert-light border">No sessions yet.</div><?php else: ?>
  <div class="table-responsive"><table class="table table-sm align-middle"><thead><tr><th>Date</th><th>Title</th><th>Status</th></tr></thead><tbody>
  <?php foreach ($sessions as $session): ?><tr><td><?= htmlspecialchars($session['start_at'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($session['title'], ENT_QUOTES, 'UTF-8') ?></td><td><span class="badge text-bg-light border"><?= htmlspecialchars($session['status'], ENT_QUOTES, 'UTF-8') ?></span></td></tr><?php endforeach; ?>
  </tbody></table></div><?php endif; ?>
</div>

<div class="foundation-card">
  <h2 class="h5 fw-bold mb-3">Credit ledger</h2>
  <?php if (!$transactions): ?><div class="alert alert-light border">No credit transactions yet.</div><?php else: ?>
  <div class="table-responsive"><table class="table table-sm align-middle"><thead><tr><th>Date</th><th>Type</th><th>Credits</th><th>Balance</th><th>Reason</th></tr></thead><tbody>
  <?php foreach ($transactions as $tx): ?><tr><td class="small text-muted"><?= htmlspecialchars($tx['created_at'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($tx['transaction_type'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars((string) $tx['credits'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars((string) ($tx['balance_after'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($tx['reason'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td></tr><?php endforeach; ?>
  </tbody></table></div><?php endif; ?>
</div>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'My Balance', $content);
