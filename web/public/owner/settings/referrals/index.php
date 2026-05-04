<?php
/** /owner/settings/referrals - referral program settings. */
require_once __DIR__ . '/../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../backend/php/shared/ReferralSystem.php';
require_once __DIR__ . '/../../../../../web/components/layout/dashboard_shell.php';

$user = require_role('owner_teacher');
$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        referral_update_setting((int) $user['id'], 'referral_program_enabled', isset($_POST['referral_program_enabled']) ? '1' : '0');
        referral_update_setting((int) $user['id'], 'referral_reward_type', $_POST['referral_reward_type'] ?? 'free_session');
        referral_update_setting((int) $user['id'], 'referral_reward_value', trim($_POST['referral_reward_value'] ?? '1'));
        referral_update_setting((int) $user['id'], 'referral_terms_text', trim($_POST['referral_terms_text'] ?? ''));
        referral_update_setting((int) $user['id'], 'referral_public_base_url', trim($_POST['referral_public_base_url'] ?? 'https://mshabibanabil.com/?ref='));
        $message = 'Referral settings updated.';
    } catch (Throwable $e) { $error = $e->getMessage(); }
}

$enabled = referral_setting('referral_program_enabled', '1') === '1';
$type = referral_setting('referral_reward_type', 'free_session');
$value = referral_setting('referral_reward_value', '1');
$terms = referral_setting('referral_terms_text', '');
$base = referral_setting('referral_public_base_url', 'https://mshabibanabil.com/?ref=');

ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
  <div><p class="text-muted mb-1">Control referral program reward rules and public link base.</p></div>
  <a class="btn btn-outline-brand" href="/owner/referrals">All referrals</a>
</div>
<?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
<form method="post" class="foundation-card">
  <div class="row g-3">
    <div class="col-md-4"><label class="form-label">Program enabled</label><div class="form-check mt-2"><input class="form-check-input" type="checkbox" name="referral_program_enabled" <?= $enabled ? 'checked' : '' ?>><label class="form-check-label">Enabled</label></div></div>
    <div class="col-md-4"><label class="form-label">Reward type</label><select class="form-select" name="referral_reward_type"><option value="free_session" <?= $type === 'free_session' ? 'selected' : '' ?>>free_session</option><option value="aed_discount" <?= $type === 'aed_discount' ? 'selected' : '' ?>>aed_discount</option><option value="both" <?= $type === 'both' ? 'selected' : '' ?>>both</option></select></div>
    <div class="col-md-4"><label class="form-label">Reward value</label><input class="form-control" name="referral_reward_value" value="<?= htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8') ?>"></div>
    <div class="col-12"><label class="form-label">Public base URL</label><input class="form-control" name="referral_public_base_url" value="<?= htmlspecialchars((string) $base, ENT_QUOTES, 'UTF-8') ?>"><small class="text-muted">Example: https://mshabibanabil.com/?ref=</small></div>
    <div class="col-12"><label class="form-label">Terms text</label><textarea class="form-control" name="referral_terms_text" rows="6"><?= htmlspecialchars((string) $terms, ENT_QUOTES, 'UTF-8') ?></textarea></div>
    <div class="col-12"><button class="btn btn-brand" type="submit">Save settings</button></div>
  </div>
</form>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Referral Settings', $content);
