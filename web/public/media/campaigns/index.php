<?php
require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../backend/php/shared/MediaBuyer.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';
$user=require_role('media_buyer');$buyer=media_buyer_by_user((int)$user['id']);$rows=media_campaigns((int)$buyer['id']);
ob_start();
?>
<p class="text-muted">Your campaigns and UTM performance. Campaign creation is managed by the Owner in this MVP.</p>
<div class="foundation-card"><?php if(!$rows):?><div class="alert alert-light border">No campaigns yet. Use your tracking links for now.</div><?php else:?><div class="table-responsive"><table class="table"><thead><tr><th>Name</th><th>Source</th><th>Campaign</th><th>Status</th></tr></thead><tbody><?php foreach($rows as $r):?><tr><td><?=htmlspecialchars($r['name'],ENT_QUOTES,'UTF-8')?></td><td><?=htmlspecialchars($r['utm_source']??'-',ENT_QUOTES,'UTF-8')?></td><td><?=htmlspecialchars($r['utm_campaign']??'-',ENT_QUOTES,'UTF-8')?></td><td><?=htmlspecialchars($r['status'],ENT_QUOTES,'UTF-8')?></td></tr><?php endforeach;?></tbody></table></div><?php endif;?></div>
<?php $content=ob_get_clean();render_dashboard_shell($user,'Campaigns',$content);