<?php
require_once __DIR__ . '/../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../backend/php/shared/MaterialsLibrary.php';
require_once __DIR__ . '/../../../../../web/components/layout/dashboard_shell.php';
$user=require_role('parent');$childId=(int)($_GET['child_id']??$_GET['id']??0);
if(!$childId){render_dashboard_shell($user,'Child Materials','<div class="alert alert-danger">Child ID is required.</div>');exit;}
$s=db()->prepare('SELECT 1 FROM parent_child_links WHERE parent_user_id=:p AND student_user_id=:c LIMIT 1');$s->execute([':p'=>(int)$user['id'],':c'=>$childId]);
if(!$s->fetchColumn()){http_response_code(403);render_dashboard_shell($user,'Unauthorized','<div class="alert alert-danger">You cannot access this child materials.</div>');exit;}
$materials=material_student_assigned($childId);ob_start();
?>
<p class="text-muted">Materials assigned to your child.</p><?php if(!$materials):?><div class="alert alert-light border">No assigned materials yet.</div><?php else:?><div class="row g-3"><?php foreach($materials as $m):?><div class="col-md-6"><div class="status-box h-100"><h2 class="h5 fw-bold"><?=htmlspecialchars($m['title'],ENT_QUOTES,'UTF-8')?></h2><div class="small text-muted mb-2"><?=htmlspecialchars($m['material_type'],ENT_QUOTES,'UTF-8')?> · <?=htmlspecialchars($m['progress_status']??'assigned',ENT_QUOTES,'UTF-8')?></div><?php if($m['completed_at']):?><span class="badge text-bg-success">Completed</span><?php endif; ?><p><?=htmlspecialchars(mb_strimwidth($m['description']??'',0,120,'...'),ENT_QUOTES,'UTF-8')?></p><a class="btn btn-sm btn-brand" href="/parent/child/materials/view?child_id=<?=$childId?>&material_id=<?=(int)$m['id']?>">Open</a></div></div><?php endforeach;?></div><?php endif;?>
<?php $content=ob_get_clean();render_dashboard_shell($user,'Child Materials',$content);