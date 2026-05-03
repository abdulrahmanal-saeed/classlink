<?php
/**
 * /owner/payments/view?id=...
 *
 * Owner-only payment detail and manual status update page. Every status change
 * is written to audit log through CheckoutFlow helper.
 */

require_once __DIR__ . '/../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../backend/php/config/db.php';
require_once __DIR__ . '/../../../../../backend/php/shared/CheckoutFlow.php';
require_once __DIR__ . '/../../../../../web/components/layout/dashboard_shell.php';

$user = require_role('owner_teacher');
$purchaseId = (int) ($_GET['id'] ?? 0);
$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        owner_update_purchase_status(
            $purchaseId,
            $_POST['status'] ?? '',
            (int) $user['id'],
            trim($_POST['note'] ?? '')
        );
        $message = 'Payment status updated successfully.';
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
    }
}

$statement = db()->prepare(
    'SELECT purchases.*, plans.name_en AS plan_name, plans.description_en AS plan_description,
            payment_records.provider, payment_records.status AS record_status,
            payment_records.provider_reference, payment_records.notes, payment_records.manual_status_note,
            payment_records.verified_at
     FROM purchases
     LEFT JOIN plans ON plans.id = purchases.plan_id
     LEFT JOIN payment_records ON payment_records.purchase_id = purchases.id
     WHERE purchases.id = :id
     LIMIT 1'
);
$statement->execute([':id' => $purchaseId]);
$purchase = $statement->fetch();

if (!$purchase) {
    http_response_code(404);
    $content = '<div class="alert alert-danger">Payment order not found.</div><a class="btn btn-outline-brand" href="/owner/payments">Back to payments</a>';
    render_dashboard_shell($user, 'Payment Not Found', $content);
    exit;
}

$allowedStatuses = ['pending', 'pending_verification', 'paid', 'failed', 'refunded', 'cancelled'];

ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
  <div>
    <p class="text-muted mb-1">Review checkout and payment details before changing status.</p>
    <small class="text-muted">Manual status updates are recorded in audit log.</small>
  </div>
  <a class="btn btn-outline-brand" href="/owner/payments">Back to payments</a>
</div>

<?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

<div class="row g-4">
  <div class="col-lg-7">
    <div class="status-box h-100">
      <h2 class="h5 fw-bold">Checkout details</h2>
      <dl class="row mb-0 mt-3">
        <dt class="col-sm-4">Reference</dt><dd class="col-sm-8"><code><?= htmlspecialchars($purchase['checkout_reference'] ?? '-', ENT_QUOTES, 'UTF-8') ?></code></dd>
        <dt class="col-sm-4">Customer</dt><dd class="col-sm-8"><?= htmlspecialchars($purchase['full_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>
        <dt class="col-sm-4">Email</dt><dd class="col-sm-8"><?= htmlspecialchars($purchase['email'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>
        <dt class="col-sm-4">WhatsApp</dt><dd class="col-sm-8"><?= htmlspecialchars($purchase['whatsapp'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>
        <dt class="col-sm-4">Plan</dt><dd class="col-sm-8"><?= htmlspecialchars($purchase['plan_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>
        <dt class="col-sm-4">Amount</dt><dd class="col-sm-8"><?= htmlspecialchars($purchase['currency'], ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars((string) $purchase['amount'], ENT_QUOTES, 'UTF-8') ?></dd>
        <dt class="col-sm-4">Purchase status</dt><dd class="col-sm-8"><span class="badge text-bg-light border"><?= htmlspecialchars($purchase['status'], ENT_QUOTES, 'UTF-8') ?></span></dd>
        <dt class="col-sm-4">Payment record</dt><dd class="col-sm-8"><?= htmlspecialchars($purchase['record_status'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>
        <dt class="col-sm-4">Learner type</dt><dd class="col-sm-8"><?= htmlspecialchars($purchase['learner_type'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>
        <dt class="col-sm-4">Goal</dt><dd class="col-sm-8"><?= htmlspecialchars($purchase['main_goal'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>
      </dl>
    </div>
  </div>
  <div class="col-lg-5">
    <div class="foundation-card h-100">
      <h2 class="h5 fw-bold">Manual status update</h2>
      <div class="alert alert-light border small">Only mark as paid after real verification outside the system or a future verified webhook/API.</div>
      <form method="post">
        <div class="mb-3">
          <label class="form-label">New status</label>
          <select class="form-select" name="status" required>
            <?php foreach ($allowedStatuses as $status): ?>
              <option value="<?= $status ?>" <?= $purchase['status'] === $status ? 'selected' : '' ?>><?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Review note</label>
          <textarea class="form-control" name="note" rows="4" placeholder="Example: Verified manually in Ziina dashboard."></textarea>
        </div>
        <button class="btn btn-brand" type="submit">Update status</button>
      </form>
    </div>
  </div>
</div>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Payment Review', $content);
