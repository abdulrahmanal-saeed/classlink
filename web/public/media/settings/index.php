<?php
require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../backend/php/shared/MediaBuyer.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';
$user=require_role('media_buyer');$buyer=media_buyer_by_user((int)$user['id']);
ob_start();
?>
<p class="text-muted">Your partner profile. Contact the Owner to change payout or commission details.</p>
<div class="foundation-card"><h2 class="h5 fw-bold"><?=htmlspecialchars($buyer['display_name'],ENT_QUOTES,'UTF-8')?></h2><p>Partner code: <strong class="ltr-safe"><?=htmlspecialchars($buyer['partner_code'],ENT_QUOTES,'UTF-8')?></strong></p><p>Status: <?=htmlspecialchars($buyer['status'],ENT_QUOTES,'UTF-8')?></p><p>Commission: <?=htmlspecialchars($buyer['commission_type'].' '.$buyer['commission_rate'],ENT_QUOTES,'UTF-8')?></p></div>
<?php $content=ob_get_clean();render_dashboard_shell($user,'Media Settings',$content);