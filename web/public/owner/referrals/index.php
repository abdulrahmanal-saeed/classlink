<?php
/** /owner/referrals - referral tracking and manual reward handling. */
require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../backend/php/shared/ReferralSystem.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';

$user = require_role('owner_teacher');
$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $action = $_POST['action'] ?? '';
        if ($action === 'apply') {
            referral_apply_reward((int) $user['id'], (int) $_POST['referral_id'], $_POST);
            $message = 'Reward applied.';
        } elseif ($action === 'reject') {
            referral_reject((int) $user['id'], (int) $_POST['referral_id'], trim($_POST['notes'] ?? ''));
            $message = 'Referral rejected.';
        } elseif ($action === 'qualify') {
            $id = referral_qualify_from_paid_purchase((int) $_POST['purchase_id'], ($_POST['payment_record_id'] ?? '') !== '' ? (int) $_POST['payment_record_id'] : null);
            $message = $id ? 'Referral qualified.' : 'No qualifying referral found.';
        }
    } catch (Throwable $e) { $error = $e->getMessage(); }
}

$rows = referral_all();
$defaultType = referral_setting('referral_reward_type', 'free_session');
$defaultValue = referral_setting('referral_reward_value', '1');

ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
  <div><p class="text-muted mb-1">Track referrals and apply rewards after payment verification.</p><small class="text-muted">Default: <?= htmlspecialchars($defaultType . ' ' . $defaultValue, ENT_QUOTES, 'UTF-8') ?></small></div>
  <a class="btn btn-outline-brand" href="/owner/settings/referrals">Referral settings</a>
</div>
<?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

<form method="post" class="foundation-card mb-4">
  <input type="hidden" name="action" value="qualify">
  <h2 class="h5 fw-bold">Qualify from paid purchase</h2>
  <div class="row g-3 align-items-end">
    <div class="col-md-4"><label class="form-label">Purchase ID</label><input class="form-control" name="purchase_id" required></div>
    <div class="col-md-4"><label class="form-label">Payment ID optional</label><input class="form-control" name="payment_record_id"></div>
    <div class="col-md-4"><button class="btn btn-brand" type="submit">Qualify</button></div>
  </div>
</form>

<div class="foundation-card">
  <h2 class="h5 fw-bold">All referrals</h2>
  <?php if (!$rows): ?><div class="alert alert-light border mb-0">No referrals yet.</div><?php else: ?>
  <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Date</th><th>Referrer</th><th>Referred</th><th>Purchase</th><th>Status</th><th>Actions</th></tr></thead><tbody>
  <?php foreach ($rows as $r): ?>
    <tr>
      <td><?= htmlspecialchars($r['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
      <td><?= htmlspecialchars($r['referrer_name'] ?? ('User #' . $r['referrer_user_id']), ENT_QUOTES, 'UTF-8') ?><br><small><code><?= htmlspecialchars($r['source_referral_code'] ?? $r['referral_code'], ENT_QUOTES, 'UTF-8') ?></code></small></td>
      <td><?= htmlspecialchars($r['referred_user_name'] ?? $r['referred_name'] ?? $r['referred_email'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
      <td>#<?= htmlspecialchars((string) ($r['purchase_id'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
      <td><span class="badge text-bg-light border"><?= htmlspecialchars($r['status'], ENT_QUOTES, 'UTF-8') ?></span></td>
      <td>
        <?php if ($r['status'] !== 'reward_applied' && $r['status'] !== 'rejected'): ?>
          <form method="post" class="d-flex gap-1 flex-wrap mb-1">
            <input type="hidden" name="action" value="apply"><input type="hidden" name="referral_id" value="<?= (int) $r['id'] ?>">
            <select class="form-select form-select-sm" name="reward_type" style="max-width:145px"><option value="free_session">free_session</option><option value="aed_discount">aed_discount</option><option value="both">both</option></select>
            <input class="form-control form-control-sm" name="reward_value" value="<?= htmlspecialchars((string) ($r['reward_value'] ?? $defaultValue), ENT_QUOTES, 'UTF-8') ?>" style="max-width:80px">
            <button class="btn btn-sm btn-success">Apply</button>
          </form>
          <form method="post" class="d-flex gap-1 flex-wrap"><input type="hidden" name="action" value="reject"><input type="hidden" name="referral_id" value="<?= (int) $r['id'] ?>"><input class="form-control form-control-sm" name="notes" placeholder="Reason" style="max-width:150px"><button class="btn btn-sm btn-outline-danger">Reject</button></form>
        <?php else: ?><span class="small text-muted">Closed</span><?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody></table></div>
  <?php endif; ?>
</div>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Referrals', $content);
