<?php
/**
 * /student/referrals
 * Student referral link and reward tracking.
 */

require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../backend/php/shared/ReferralSystem.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';

$user = require_role('student');
$code = referral_get_or_create_code((int) $user['id']);
$link = referral_public_link($code['code']);
$referrals = referral_for_user((int) $user['id']);
$enabled = referral_setting('referral_program_enabled', '1') === '1';
$terms = referral_setting('referral_terms_text', 'Rewards are applied manually after the referred student payment is verified.');

ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
  <div>
    <p class="text-muted mb-1">Share your referral link and track reward status.</p>
    <span class="badge <?= $enabled ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= $enabled ? 'Referral program active' : 'Referral program disabled' ?></span>
  </div>
</div>

<div class="foundation-card mb-4">
  <h2 class="h5 fw-bold">Your referral link</h2>
  <p class="text-muted">When someone signs up and payment is verified, your referral becomes reward pending.</p>
  <div class="input-group mb-3">
    <input class="form-control" value="<?= htmlspecialchars($link, ENT_QUOTES, 'UTF-8') ?>" readonly onclick="this.select();document.execCommand('copy');">
    <button class="btn btn-outline-brand" type="button" onclick="navigator.clipboard.writeText('<?= htmlspecialchars($link, ENT_QUOTES, 'UTF-8') ?>')">Copy</button>
  </div>
  <div class="small text-muted">Code: <strong><?= htmlspecialchars($code['code'], ENT_QUOTES, 'UTF-8') ?></strong> · Link visits: <?= (int) $code['landing_count'] ?></div>
</div>

<div class="foundation-card mb-4">
  <h2 class="h5 fw-bold">Terms</h2>
  <p class="mb-0"><?= nl2br(htmlspecialchars($terms, ENT_QUOTES, 'UTF-8')) ?></p>
</div>

<div class="foundation-card">
  <h2 class="h5 fw-bold">My referrals</h2>
  <?php if (!$referrals): ?>
    <div class="alert alert-light border mb-0">No referrals yet.</div>
  <?php else: ?>
    <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Date</th><th>Referred student</th><th>Status</th><th>Reward</th></tr></thead><tbody>
      <?php foreach ($referrals as $referral): ?>
        <tr>
          <td><?= htmlspecialchars($referral['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
          <td><?= htmlspecialchars($referral['referred_user_name'] ?? $referral['referred_name'] ?? $referral['referred_email'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
          <td><span class="badge text-bg-light border"><?= htmlspecialchars($referral['status'], ENT_QUOTES, 'UTF-8') ?></span></td>
          <td><?= htmlspecialchars(($referral['reward_type'] ?? '-') . ' ' . ($referral['reward_value'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody></table></div>
  <?php endif; ?>
</div>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Referrals', $content);
