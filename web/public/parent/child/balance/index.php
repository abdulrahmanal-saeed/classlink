<?php
/**
 * /parent/child/balance?id={childUserId}
 * Parent can view balance for linked child only.
 */

require_once __DIR__ . '/../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../backend/php/shared/LessonCredits.php';
require_once __DIR__ . '/../../../../../web/components/layout/dashboard_shell.php';

$user = require_role('parent');
$childId = (int) ($_GET['id'] ?? 0);

if (!$childId || !credits_parent_can_access_child((int) $user['id'], $childId)) {
    http_response_code(403);
    $content = '<div class="alert alert-danger">You are not allowed to view this child balance.</div><a class="btn btn-outline-brand" href="/parent/dashboard">Back</a>';
    render_dashboard_shell($user, 'Unauthorized Child Balance', $content);
    exit;
}

$summary = credits_student_summary($childId);
$transactions = credits_transactions_for_student($childId);
$sessions = credits_sessions_for_student($childId);

$nameStatement = db()->prepare('SELECT display_name FROM users WHERE id = :id LIMIT 1');
$nameStatement->execute([':id' => $childId]);
$childName = $nameStatement->fetchColumn() ?: 'Child learner';

ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
  <div>
    <p class="text-muted mb-1">Balance for <?= htmlspecialchars($childName, ENT_QUOTES, 'UTF-8') ?>.</p>
    <small class="text-muted">Parents can only view linked child accounts.</small>
  </div>
  <a class="btn btn-outline-brand" href="/parent/dashboard">Back to parent dashboard</a>
</div>

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
  <h2 class="h5 fw-bold mb-3">Sessions</h2>
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
render_dashboard_shell($user, 'Child Balance', $content);
