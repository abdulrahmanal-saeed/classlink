<?php
require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../backend/php/shared/MediaBuyer.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';
$user=require_role('media_buyer');$buyer=media_buyer_by_user((int)$user['id']);
$links=[
 ['Pricing',media_tracking_link($buyer,'/pricing',['utm_source'=>'facebook','utm_medium'=>'paid_social','utm_campaign'=>'pricing'])],
 ['Kids Arabic',media_tracking_link($buyer,'/pricing',['utm_source'=>'facebook','utm_medium'=>'paid_social','utm_campaign'=>'kids_arabic'])],
 ['Work Arabic',media_tracking_link($buyer,'/pricing',['utm_source'=>'instagram','utm_medium'=>'paid_social','utm_campaign'=>'work_arabic'])]
];
ob_start();
?>
<p class="text-muted">Use these links in campaigns. Attribution is stored when visitors open them.</p>
<div class="foundation-card"><h2 class="h5 fw-bold">Tracking Links</h2><?php foreach($links as $row):?><div class="mb-3"><label class="form-label"><?=htmlspecialchars($row[0],ENT_QUOTES,'UTF-8')?></label><input class="form-control ltr-safe" readonly value="<?=htmlspecialchars($row[1],ENT_QUOTES,'UTF-8')?>"></div><?php endforeach;?></div>
<?php $content=ob_get_clean();render_dashboard_shell($user,'Tracking Links',$content);