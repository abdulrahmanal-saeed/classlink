<?php
require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../backend/php/shared/MediaBuyer.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';
$user=require_role('media_buyer');$buyer=media_buyer_by_user((int)$user['id']);
$s=db()->prepare('SELECT * FROM payout_records WHERE media_buyer_id=:id ORDER BY created_at DESC LIMIT 200');$s->execute([':id'=>(int)$buyer['id']]);$rows=$s->fetchAll();
ob_start();
?>
<p class="text-muted">Your payout history. Payouts are controlled by the Owner.</p>
<div class="foundation-card"><?php if(!$rows):?><div class="alert alert-light border">No payouts yet.</div><?php else:?><table class="table"><thead><tr><th>Date</th><th>Amount</th><th>Status</th><th>Paid At</th></tr></thead><tbody><?php foreach($rows as $r):?><tr><td><?=htmlspecialchars($r['created_at'],ENT_QUOTES,'UTF-8')?></td><td><?=htmlspecialchars((string)$r['amount'],ENT_QUOTES,'UTF-8')?></td><td><?=htmlspecialchars($r['status'],ENT_QUOTES,'UTF-8')?></td><td><?=htmlspecialchars($r['paid_at']??'-',ENT_QUOTES,'UTF-8')?></td></tr><?php endforeach;?></tbody></table><?php endif;?></div>
<?php $content=ob_get_clean();render_dashboard_shell($user,'Payouts',$content);