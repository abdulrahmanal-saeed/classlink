<?php
require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../backend/php/shared/MediaBuyer.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';
$user=require_role('media_buyer');$buyer=media_buyer_by_user((int)$user['id']);$orders=media_orders((int)$buyer['id']);
ob_start();
?>
<p class="text-muted">Only your attributed orders are shown. Private customer learning data is hidden.</p>
<div class="foundation-card"><?php if(!$orders):?><div class="alert alert-light border">No attributed orders yet.</div><?php else:?><div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Reference</th><th>Date</th><th>Package</th><th>Amount</th><th>Payment</th><th>Commission</th><th>Customer</th></tr></thead><tbody><?php foreach($orders as $o):?><tr><td class="ltr-safe"><?=htmlspecialchars($o['checkout_order_id'],ENT_QUOTES,'UTF-8')?></td><td><?=htmlspecialchars($o['created_at'],ENT_QUOTES,'UTF-8')?></td><td><?=htmlspecialchars($o['selected_plan']??'-',ENT_QUOTES,'UTF-8')?></td><td><?=htmlspecialchars((string)$o['order_amount'],ENT_QUOTES,'UTF-8')?></td><td><span class="badge text-bg-light border"><?=htmlspecialchars($o['payment_status'],ENT_QUOTES,'UTF-8')?></span></td><td><?=htmlspecialchars((string)($o['commission_amount']??'-'),ENT_QUOTES,'UTF-8')?> <small><?=htmlspecialchars($o['commission_status']??'',ENT_QUOTES,'UTF-8')?></small></td><td><?=htmlspecialchars($o['customer_name_masked']??'Customer',ENT_QUOTES,'UTF-8')?></td></tr><?php endforeach;?></tbody></table></div><?php endif;?></div>
<?php $content=ob_get_clean();render_dashboard_shell($user,'Attributed Orders',$content);