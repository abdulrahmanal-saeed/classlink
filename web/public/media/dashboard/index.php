<?php
/**
 * /media/dashboard
 * Phase 27 media buyer dashboard.
 * Phase 32 improves what-to-do-next and privacy/status clarity.
 */
require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../backend/php/shared/MediaBuyer.php';
require_once __DIR__ . '/../../../../backend/php/shared/MediaBuyerAgreement.php';
require_once __DIR__ . '/../../../../backend/php/shared/UXComponents.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';

$user = require_role('media_buyer');
$buyer = media_buyer_by_user((int)$user['id']);
if (!$buyer || $buyer['status'] !== 'active') { render_dashboard_shell($user, 'Media Dashboard', '<div class="alert alert-danger">Your media buyer profile is not active.</div>'); exit; }
$summary = media_summary((int)$buyer['id']);
$link = media_tracking_link($buyer, '/pricing', ['utm_source'=>'facebook','utm_medium'=>'paid_social','utm_campaign'=>'arabic_lessons']);
$agreementAccepted = media_agreement_has_valid_acceptance((int)$buyer['id']);
ob_start();
?>
<?= ux_page_intro('Marketing partner dashboard', 'Track your links, orders, and commissions', 'This dashboard only shows your own campaign performance. Private student and parent learning data is hidden for privacy.', [
  ['label' => 'Tracking links', 'href' => '/media/links', 'primary' => true],
  ['label' => 'Commissions', 'href' => '/media/commissions'],
  ['label' => 'Help', 'href' => '/media/help'],
]) ?>

<?php if (!$agreementAccepted): ?>
  <?= ux_next_step_card('Accept your marketing partner agreement', 'You must accept the active agreement before using the full media buyer dashboard.', '/media/agreement', 'Review agreement') ?>
<?php elseif ((int)$summary['clicks'] === 0): ?>
  <?= ux_next_step_card('Start by using your tracking link', 'Copy your tracking link and use it in your ads or posts. Clicks and future attributed orders will appear here.', '/media/links', 'Copy tracking link') ?>
<?php elseif ((int)$summary['paid_orders'] === 0): ?>
  <?= ux_next_step_card('Clicks are coming in — focus on conversion', 'Review your campaign message and make sure the ad sends people to the right pricing or offer page.', '/media/campaigns', 'Review campaigns', 'outline') ?>
<?php else: ?>
  <?= ux_next_step_card('Review commissions and payout status', 'Paid orders can generate commission records. Owner approval is required before payout.', '/media/commissions', 'View commissions') ?>
<?php endif; ?>

<div class="row g-3 mb-4">
  <?php foreach(['clicks'=>'Total clicks','checkout_starts'=>'Checkout starts','paid_orders'=>'Paid orders','revenue'=>'Revenue generated','conversion_rate'=>'Conversion rate %','commission_pending'=>'Pending commission','commission_approved'=>'Approved commission','commission_paid'=>'Paid commission'] as $k=>$label): ?>
  <div class="col-md-3"><div class="status-box h-100"><div class="small text-muted"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></div><div class="h4 fw-bold mb-0"><?= htmlspecialchars((string)$summary[$k], ENT_QUOTES, 'UTF-8') ?></div></div></div>
  <?php endforeach; ?>
</div>

<div class="foundation-card mb-4"><h2 class="h5 fw-bold">Main Tracking Link</h2><p class="text-muted">Use this link in ads or posts. The platform stores attribution when visitors open it.</p><div class="input-group"><input class="form-control ltr-safe" value="<?= htmlspecialchars($link, ENT_QUOTES, 'UTF-8') ?>" readonly><button class="btn btn-outline-brand" onclick="navigator.clipboard.writeText(this.previousElementSibling.value)">Copy</button></div><?= ux_helper_text('Commission is counted only for paid orders. Pending, failed, cancelled, or refunded orders do not count.', '/media/help', 'How commissions work') ?></div>

<div class="foundation-card mb-4">
  <h2 class="h5 fw-bold">Commission status guide</h2>
  <div class="row g-2 small"><div class="col-md-6"><?= ux_status_badge('pending') ?> Waiting for Owner review.</div><div class="col-md-6"><?= ux_status_badge('approved') ?> Approved but not paid yet.</div><div class="col-md-6"><?= ux_status_badge('paid') ?> Paid out.</div><div class="col-md-6"><?= ux_status_badge('reversed') ?> Removed because of refund/chargeback or correction.</div></div>
</div>

<div class="row g-3"><div class="col-md-4"><a class="btn btn-brand w-100" href="/media/orders">View Orders</a></div><div class="col-md-4"><a class="btn btn-outline-brand w-100" href="/media/commissions">View Commissions</a></div><div class="col-md-4"><a class="btn btn-outline-brand w-100" href="/media/links">Tracking Links</a></div></div>
<?php $content=ob_get_clean(); render_dashboard_shell($user, 'Media Buyer Dashboard', $content);