<?php
require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../backend/php/shared/MediaBuyer.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';
$user=require_role('media_buyer');$buyer=media_buyer_by_user((int)$user['id']);$rows=media_commissions((int)$buyer['id']);
ob_start();
?>
<p class="text-muted">Only paid orders generate commission. Failed, pending, cancelled, or refunded orders are not payable.</p>
<div class="foundation-card"><?php if(!$rows):?><div class="alert alert-light border">No commissions yet.</div><?php else:?><div class="table-responsive"><table class="table table-hover"><thead><tr><th>Date</th><th>Order</th><th>Package</th><th>Amount</th><th>Commission</th><th>Status</th></tr></thead><tbody><?php foreach($rows as $r):?><tr><td><?=htmlspecialchars($r['created_at'],ENT_QUOTES,'UTF-8')?></td><td class="ltr-safe"><?=htmlspecialchars($r['checkout_order_id'],ENT_QUOTES,'UTF-8')?></td><td><?=htmlspecialchars($r['package_name']??'-',ENT_QUOTES,'UTF-8')?></td><td><?=htmlspecialchars((string)$r['order_amount'],ENT_QUOTES,'UTF-8')?></td><td><?=htmlspecialchars((string)$r['commission_amount'],ENT_QUOTES,'UTF-8')?></td><td><span class="badge text-bg-light border"><?=htmlspecialchars($r['status'],ENT_QUOTES,'UTF-8')?></span></td></tr><?php endforeach;?></tbody></table></div><?php endif;?></div>
<?php $content=ob_get_clean();render_dashboard_shell($user,'Commissions',$content);