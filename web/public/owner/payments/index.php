<?php
/**
 * /owner/payments
 *
 * Owner-only payment review list. The Owner can inspect checkout orders and
 * open each purchase to update its status manually after real verification.
 */

require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../backend/php/config/db.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';

$user = require_role('owner_teacher');

$status = $_GET['status'] ?? '';
$allowedStatuses = ['pending', 'pending_verification', 'paid', 'failed', 'refunded', 'cancelled'];
$params = [];
$where = '';

if (in_array($status, $allowedStatuses, true)) {
    $where = 'WHERE purchases.status = :status';
    $params[':status'] = $status;
}

$statement = db()->prepare(
    "SELECT purchases.*, plans.name_en AS plan_name
     FROM purchases
     LEFT JOIN plans ON plans.id = purchases.plan_id
     {$where}
     ORDER BY purchases.created_at DESC
     LIMIT 150"
);
$statement->execute($params);
$purchases = $statement->fetchAll();

ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
  <div>
    <p class="text-muted mb-1">Review checkout orders and payment statuses.</p>
    <small class="text-muted">Payment is never marked paid automatically. Manual review is required unless future webhook/API verification is added.</small>
  </div>
  <a class="btn btn-outline-brand" href="/owner/audit-log">View audit log</a>
</div>

<div class="d-flex flex-wrap gap-2 mb-4">
  <a class="btn btn-sm <?= $status === '' ? 'btn-brand' : 'btn-outline-brand' ?>" href="/owner/payments">All</a>
  <?php foreach ($allowedStatuses as $filter): ?>
    <a class="btn btn-sm <?= $status === $filter ? 'btn-brand' : 'btn-outline-brand' ?>" href="/owner/payments?status=<?= urlencode($filter) ?>"><?= htmlspecialchars($filter, ENT_QUOTES, 'UTF-8') ?></a>
  <?php endforeach; ?>
</div>

<?php if (!$purchases): ?>
  <div class="alert alert-light border">No checkout orders found.</div>
<?php else: ?>
  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead>
        <tr>
          <th>Reference</th>
          <th>Customer</th>
          <th>Plan</th>
          <th>Amount</th>
          <th>Status</th>
          <th>Created</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($purchases as $purchase): ?>
          <tr>
            <td><code><?= htmlspecialchars($purchase['checkout_reference'] ?? '-', ENT_QUOTES, 'UTF-8') ?></code></td>
            <td>
              <strong><?= htmlspecialchars($purchase['full_name'] ?? 'Unknown', ENT_QUOTES, 'UTF-8') ?></strong><br>
              <small class="text-muted"><?= htmlspecialchars($purchase['email'] ?? '', ENT_QUOTES, 'UTF-8') ?></small>
            </td>
            <td><?= htmlspecialchars($purchase['plan_name'] ?? 'No plan', ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($purchase['currency'], ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars((string) $purchase['amount'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><span class="badge text-bg-light border"><?= htmlspecialchars($purchase['status'], ENT_QUOTES, 'UTF-8') ?></span></td>
            <td class="small text-muted"><?= htmlspecialchars($purchase['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><a class="btn btn-sm btn-outline-brand" href="/owner/payments/view?id=<?= (int) $purchase['id'] ?>">Review</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Owner Payments', $content);
