<?php
require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../backend/php/shared/MaterialsLibrary.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';
$user=require_role('student');$materials=material_student_assigned((int)$user['id']);
ob_start();
?>
<p class="text-muted">Assigned materials from your teacher. Open materials and mark them completed when finished.</p>
<?php if(!$materials):?><div class="alert alert-light border">No assigned materials yet.</div><?php else:?><div class="row g-3"><?php foreach($materials as $m):?><div class="col-md-6"><div class="status-box h-100"><h2 class="h5 fw-bold"><?=htmlspecialchars($m['title'],ENT_QUOTES,'UTF-8')?></h2><div class="small text-muted mb-2"><?=htmlspecialchars($m['material_type'],ENT_QUOTES,'UTF-8')?> · <?=htmlspecialchars($m['category_name']??'-',ENT_QUOTES,'UTF-8')?> · <?=htmlspecialchars($m['level'],ENT_QUOTES,'UTF-8')?></div><span class="badge text-bg-light border"><?=htmlspecialchars($m['progress_status']??'assigned',ENT_QUOTES,'UTF-8')?></span><?php if($m['completed_at']):?><span class="badge text-bg-success ms-1">Completed</span><?php endif; ?><p class="mt-2"><?=htmlspecialchars(mb_strimwidth($m['description']??'',0,120,'...'),ENT_QUOTES,'UTF-8')?></p><a class="btn btn-sm btn-brand" href="/student/materials/view?id=<?=(int)$m['id']?>">Open Material</a></div></div><?php endforeach;?></div><?php endif; ?>
<?php $content=ob_get_clean();render_dashboard_shell($user,'My Materials',$content);