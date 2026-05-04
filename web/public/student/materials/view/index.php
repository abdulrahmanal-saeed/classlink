<?php
require_once __DIR__ . '/../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../backend/php/shared/MaterialsLibrary.php';
require_once __DIR__ . '/../../../../../web/components/layout/dashboard_shell.php';
$user=require_role('student');$id=(int)($_GET['id']??0);$message=null;$error=null;
if(!material_can_student_access((int)$user['id'],$id)){http_response_code(403);render_dashboard_shell($user,'Unauthorized','<div class="alert alert-danger">You cannot access this material.</div>');exit;}
if($_SERVER['REQUEST_METHOD']==='POST'){try{material_mark_completed($id,(int)$user['id'],$user);$message='Material marked as completed.';}catch(Throwable $e){$error=$e->getMessage();}}
$m=material_find($id);material_track_view($id,(int)$user['id'],$user);
ob_start();
?>
<div class="d-flex justify-content-between mb-4"><p class="text-muted">Study material assigned to you.</p><a class="btn btn-outline-brand" href="/student/materials">Back</a></div><?php if($message):?><div class="alert alert-success"><?=htmlspecialchars($message,ENT_QUOTES,'UTF-8')?></div><?php endif;?><?php if($error):?><div class="alert alert-danger"><?=htmlspecialchars($error,ENT_QUOTES,'UTF-8')?></div><?php endif;?>
<div class="foundation-card"><h2 class="h4 fw-bold"><?=htmlspecialchars($m['title'],ENT_QUOTES,'UTF-8')?></h2><p class="text-muted"><?=nl2br(htmlspecialchars($m['description']??'',ENT_QUOTES,'UTF-8'))?></p><div class="mb-3 d-flex gap-2 flex-wrap"><span class="badge text-bg-light border"><?=htmlspecialchars($m['material_type'],ENT_QUOTES,'UTF-8')?></span><span class="badge text-bg-light border"><?=htmlspecialchars($m['material_language'],ENT_QUOTES,'UTF-8')?></span><span class="badge text-bg-light border"><?=htmlspecialchars($m['level'],ENT_QUOTES,'UTF-8')?></span></div><?=material_render_viewer($m)?><?php if($m['allow_download'] && $m['file_url']):?><div class="mt-3"><a class="btn btn-outline-brand" download href="<?=htmlspecialchars($m['file_url'],ENT_QUOTES,'UTF-8')?>">Download</a></div><?php endif;?><form method="post" class="mt-4"><button class="btn btn-brand">Mark completed</button></form></div>
<?php $content=ob_get_clean();render_dashboard_shell($user,'Material',$content);