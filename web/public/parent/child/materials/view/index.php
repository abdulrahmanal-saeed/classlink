<?php
require_once __DIR__ . '/../../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../../backend/php/shared/MaterialsLibrary.php';
require_once __DIR__ . '/../../../../../../web/components/layout/dashboard_shell.php';
$user=require_role('parent');$childId=(int)($_GET['child_id']??0);$id=(int)($_GET['material_id']??0);
if(!material_parent_can_access((int)$user['id'],$childId,$id)){http_response_code(403);render_dashboard_shell($user,'Unauthorized','<div class="alert alert-danger">You cannot access this material.</div>');exit;}
$m=material_find($id);material_track_view($id,$childId,$user);ob_start();
?>
<div class="d-flex justify-content-between mb-4"><p class="text-muted">Child assigned material.</p><a class="btn btn-outline-brand" href="/parent/child/materials?child_id=<?=$childId?>">Back</a></div>
<div class="foundation-card"><h2 class="h4 fw-bold"><?=htmlspecialchars($m['title'],ENT_QUOTES,'UTF-8')?></h2><p class="text-muted"><?=nl2br(htmlspecialchars($m['description']??'',ENT_QUOTES,'UTF-8'))?></p><?=material_render_viewer($m)?><?php if($m['allow_download'] && $m['file_url']):?><div class="mt-3"><a class="btn btn-outline-brand" download href="<?=htmlspecialchars($m['file_url'],ENT_QUOTES,'UTF-8')?>">Download</a></div><?php endif;?></div>
<?php $content=ob_get_clean();render_dashboard_shell($user,'Child Material',$content);