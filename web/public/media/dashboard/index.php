<?php
require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../backend/php/shared/MediaBuyer.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';

$user = require_role('media_buyer');
$buyer = media_buyer_by_user((int)$user['id']);
if (!$buyer || $buyer['status'] !== 'active') { render_dashboard_shell($user, 'Media Dashboard', '<div class="alert alert-danger">Your media buyer profile is not active.</div>'); exit; }
$summary = media_summary((int)$buyer['id']);
$link = media_tracking_link($buyer, '/pricing', ['utm_source'=>'facebook','utm_medium'=>'paid_social','utm_campaign'=>'arabic_lessons']);
ob_start();
?>
<p class="text-muted">Your marketing dashboard only shows your own campaigns, orders, revenue, and commissions. Student learning data is never shown here.</p>
<div class="row g-3 mb-4">
  <?php foreach(['clicks'=>'Total clicks','checkout_starts'=>'Checkout starts','paid_orders'=>'Paid orders','revenue'=>'Revenue generated','conversion_rate'=>'Conversion rate %','commission_pending'=>'Pending commission','commission_approved'=>'Approved commission','commission_paid'=>'Paid commission'] as $k=>$label): ?>
  <div class="col-md-3"><div class="status-box h-100"><div class="small text-muted"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></div><div class="h4 fw-bold mb-0"><?= htmlspecialchars((string)$summary[$k], ENT_QUOTES, 'UTF-8') ?></div></div></div>
  <?php endforeach; ?>
</div>
<div class="foundation-card mb-4"><h2 class="h5 fw-bold">Main Tracking Link</h2><p class="text-muted">Use this link in ads or posts.</p><div class="input-group"><input class="form-control ltr-safe" value="<?= htmlspecialchars($link, ENT_QUOTES, 'UTF-8') ?>" readonly><button class="btn btn-outline-brand" onclick="navigator.clipboard.writeText(this.previousElementSibling.value)">Copy</button></div></div>
<div class="row g-3"><div class="col-md-4"><a class="btn btn-brand w-100" href="/media/orders">View Orders</a></div><div class="col-md-4"><a class="btn btn-outline-brand w-100" href="/media/commissions">View Commissions</a></div><div class="col-md-4"><a class="btn btn-outline-brand w-100" href="/media/links">Tracking Links</a></div></div>
<?php $content=ob_get_clean(); render_dashboard_shell($user, 'Media Buyer Dashboard', $content);