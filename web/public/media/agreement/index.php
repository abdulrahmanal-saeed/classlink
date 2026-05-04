<?php
require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../backend/php/shared/MediaBuyerAgreement.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';

$user = require_role('media_buyer');
$buyer = media_buyer_by_user((int)$user['id']);
$template = media_agreement_active_template();
$error = null;

if (!$buyer) {
    render_dashboard_shell($user, 'Media Buyer Agreement', '<div class="alert alert-danger">Media buyer profile not found.</div>');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        media_agreement_accept((int)$buyer['id'], $_POST, $user);
        header('Location: /media/dashboard');
        exit;
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

ob_start();
?>
<div class="alert alert-warning">
  This agreement template is not legal advice. It should be reviewed by a lawyer before real use.
</div>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
<?php if (!$template): ?>
  <div class="alert alert-danger">No active agreement template is configured. Please contact the Owner.</div>
<?php else: ?>
  <div class="foundation-card mb-4">
    <h2 class="h4 fw-bold"><?= htmlspecialchars($template['title'], ENT_QUOTES, 'UTF-8') ?></h2>
    <p class="text-muted">Version: <?= htmlspecialchars($template['version'], ENT_QUOTES, 'UTF-8') ?></p>
    <div class="border rounded-4 p-4 bg-light" style="white-space:pre-wrap; max-height:60vh; overflow:auto;"><?= htmlspecialchars($template['content'], ENT_QUOTES, 'UTF-8') ?></div>
  </div>
  <form method="post" class="foundation-card">
    <h3 class="h5 fw-bold">Accept Agreement</h3>
    <div class="mb-3">
      <label class="form-label">Typed legal name</label>
      <input class="form-control" name="typed_name" required placeholder="Type your full legal name">
    </div>
    <div class="mb-3">
      <label class="form-label">Optional signature</label>
      <input class="form-control" name="signature_data" placeholder="Type signature or leave blank">
    </div>
    <div class="form-check border rounded-4 p-3 ps-5 mb-3">
      <input class="form-check-input" type="checkbox" name="confirm_acceptance" value="1" required>
      <label class="form-check-label">I have read and accept this Media Buyer / Marketing Partner Agreement.</label>
    </div>
    <button class="btn btn-brand">Accept and Continue</button>
  </form>
<?php endif; ?>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Media Buyer Agreement', $content);
