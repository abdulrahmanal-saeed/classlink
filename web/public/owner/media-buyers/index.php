<?php
require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../backend/php/shared/MediaBuyer.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';
$user=require_role('owner_teacher');$buyers=media_all_buyers();
ob_start();
?>
<p class="text-muted">Manage marketing partners. They do not have Owner access.</p><p><a class="btn btn-brand" href="/owner/media-buyers/new">Create Media Buyer</a> <a class="btn btn-outline-brand" href="/owner/media-commissions">Commissions</a></p>
<div class="foundation-card"><?php if(!$buyers):?><div class="alert alert-light border">No media buyers yet.</div><?php else:?><table class="table"><thead><tr><th>Name</th><th>Code</th><th>Status</th><th></th></tr></thead><tbody><?php foreach($buyers as $b):?><tr><td><?=htmlspecialchars($b['display_name'],ENT_QUOTES,'UTF-8')?></td><td class="ltr-safe"><?=htmlspecialchars($b['partner_code'],ENT_QUOTES,'UTF-8')?></td><td><?=htmlspecialchars($b['status'],ENT_QUOTES,'UTF-8')?></td><td><a class="btn btn-sm btn-outline-brand" href="/owner/media-buyers/view?id=<?=(int)$b['id']?>">View</a></td></tr><?php endforeach;?></tbody></table><?php endif;?></div>
<?php $content=ob_get_clean();render_dashboard_shell($user,'Media Buyers',$content);