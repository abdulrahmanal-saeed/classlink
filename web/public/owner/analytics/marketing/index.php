<?php
/** /owner/analytics/marketing */
require_once __DIR__ . '/../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../backend/php/shared/Analytics.php';
require_once __DIR__ . '/../../../../../web/components/layout/dashboard_shell.php';
$user = require_role('owner_teacher');
$funnel = analytics_funnel(30);
$visitors = analytics_unique_visitors(30);
$pageViews = analytics_count_events('page_view', null, 30);
$pricing = analytics_count_events('pricing_view', null, 30);
$checkoutStart = analytics_count_events('checkout_start', null, 30);
$checkoutSubmit = analytics_count_events('checkout_submit', null, 30);
$checkoutRate = $checkoutStart > 0 ? round(($checkoutSubmit / $checkoutStart) * 100, 1) : 0;
ob_start();
?>
<p class="text-muted">Public marketing funnel for the last 30 days.</p>
<div class="row g-3 mb-4">
  <div class="col-md-3"><div class="status-box"><strong>Visitors</strong><br><span class="display-6"><?= (int)$visitors ?></span></div></div>
  <div class="col-md-3"><div class="status-box"><strong>Page views</strong><br><span class="display-6"><?= (int)$pageViews ?></span></div></div>
  <div class="col-md-3"><div class="status-box"><strong>Pricing views</strong><br><span class="display-6"><?= (int)$pricing ?></span></div></div>
  <div class="col-md-3"><div class="status-box"><strong>Checkout conversion</strong><br><span class="display-6"><?= htmlspecialchars((string)$checkoutRate, ENT_QUOTES, 'UTF-8') ?>%</span></div></div>
</div>
<div class="foundation-card"><h2 class="h5 fw-bold">Funnel</h2><div class="table-responsive"><table class="table table-hover"><thead><tr><th>Event</th><th>Count</th></tr></thead><tbody><?php foreach($funnel as $row):?><tr><td><?=htmlspecialchars($row['event'],ENT_QUOTES,'UTF-8')?></td><td><?= (int)$row['count']?></td></tr><?php endforeach;?></tbody></table></div></div>
<?php $content=ob_get_clean(); render_dashboard_shell($user,'Marketing Analytics',$content);