<?php
/** /owner/analytics/content */
require_once __DIR__ . '/../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../backend/php/shared/Analytics.php';
require_once __DIR__ . '/../../../../../web/components/layout/dashboard_shell.php';
$user = require_role('owner_teacher');
$contentPerf = analytics_content_performance();
$referrals = analytics_referral_performance();
ob_start();
?>
<p class="text-muted">Article/video performance and referral performance.</p>
<div class="row g-4">
  <div class="col-lg-4"><div class="foundation-card h-100"><h2 class="h5 fw-bold">Article opens</h2><?php if(!$contentPerf['articles']):?><div class="alert alert-light border">No article analytics yet.</div><?php else:?><div class="table-responsive"><table class="table table-sm"><thead><tr><th>Article</th><th>Opens</th></tr></thead><tbody><?php foreach($contentPerf['articles'] as $row):?><tr><td>#<?=htmlspecialchars((string)($row['entity_id'] ?? '-'),ENT_QUOTES,'UTF-8')?></td><td><?= (int)$row['opens']?></td></tr><?php endforeach;?></tbody></table></div><?php endif;?></div></div>
  <div class="col-lg-4"><div class="foundation-card h-100"><h2 class="h5 fw-bold">Video plays</h2><?php if(!$contentPerf['videos']):?><div class="alert alert-light border">No video analytics yet.</div><?php else:?><div class="table-responsive"><table class="table table-sm"><thead><tr><th>Video</th><th>Plays</th></tr></thead><tbody><?php foreach($contentPerf['videos'] as $row):?><tr><td>#<?=htmlspecialchars((string)($row['entity_id'] ?? '-'),ENT_QUOTES,'UTF-8')?></td><td><?= (int)$row['plays']?></td></tr><?php endforeach;?></tbody></table></div><?php endif;?></div></div>
  <div class="col-lg-4"><div class="foundation-card h-100"><h2 class="h5 fw-bold">Referral performance</h2><?php if(!$referrals):?><div class="alert alert-light border">No referrals yet.</div><?php else:?><div class="table-responsive"><table class="table table-sm"><thead><tr><th>Code</th><th>Visits</th><th>Refs</th><th>Rewards</th></tr></thead><tbody><?php foreach($referrals as $row):?><tr><td><code><?=htmlspecialchars($row['code'],ENT_QUOTES,'UTF-8')?></code><br><small><?=htmlspecialchars($row['display_name'] ?? '-',ENT_QUOTES,'UTF-8')?></small></td><td><?= (int)$row['landing_count']?></td><td><?= (int)$row['referrals_count']?></td><td><?= (int)$row['rewards_applied']?></td></tr><?php endforeach;?></tbody></table></div><?php endif;?></div></div>
</div>
<?php $content=ob_get_clean(); render_dashboard_shell($user,'Content Analytics',$content);