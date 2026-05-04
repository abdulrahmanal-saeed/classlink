<?php
/** /owner/analytics/revenue */
require_once __DIR__ . '/../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../backend/php/shared/Analytics.php';
require_once __DIR__ . '/../../../../../web/components/layout/dashboard_shell.php';
$user = require_role('owner_teacher');
$payments = analytics_payment_breakdown();
$plans = analytics_revenue_by_plan();
ob_start();
?>
<p class="text-muted">Revenue, plan performance, and payment status breakdown.</p>
<div class="row g-4">
  <div class="col-lg-6"><div class="foundation-card h-100"><h2 class="h5 fw-bold">Payment status breakdown</h2><?php if(!$payments):?><div class="alert alert-light border">No payment records yet.</div><?php else:?><div class="table-responsive"><table class="table table-hover"><thead><tr><th>Status</th><th>Count</th><th>Amount</th></tr></thead><tbody><?php foreach($payments as $row):?><tr><td><span class="badge text-bg-light border"><?=htmlspecialchars($row['status'],ENT_QUOTES,'UTF-8')?></span></td><td><?= (int)$row['count']?></td><td><?=htmlspecialchars((string)$row['amount'],ENT_QUOTES,'UTF-8')?></td></tr><?php endforeach;?></tbody></table></div><?php endif;?></div></div>
  <div class="col-lg-6"><div class="foundation-card h-100"><h2 class="h5 fw-bold">Revenue by plan</h2><?php if(!$plans):?><div class="alert alert-light border">No paid purchases yet.</div><?php else:?><div class="table-responsive"><table class="table table-hover"><thead><tr><th>Plan</th><th>Purchases</th><th>Revenue</th></tr></thead><tbody><?php foreach($plans as $row):?><tr><td><?=htmlspecialchars($row['plan_name'],ENT_QUOTES,'UTF-8')?></td><td><?= (int)$row['purchases_count']?></td><td><?=htmlspecialchars($row['currency'].' '.$row['revenue'],ENT_QUOTES,'UTF-8')?></td></tr><?php endforeach;?></tbody></table></div><?php endif;?></div></div>
</div>
<?php $content=ob_get_clean(); render_dashboard_shell($user,'Revenue Analytics',$content);