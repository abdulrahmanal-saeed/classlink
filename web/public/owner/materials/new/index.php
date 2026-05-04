<?php
/** /owner/materials/new - Create/assign learning material. */
require_once __DIR__ . '/../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../backend/php/shared/LearningAssignments.php';
require_once __DIR__ . '/../../../../../web/components/layout/dashboard_shell.php';
$user=require_role('owner_teacher');$error=null;
if($_SERVER['REQUEST_METHOD']==='POST'){
 try{$id=learning_create_material((int)$user['id'],$_POST);header('Location: /owner/materials');exit;}catch(Throwable $e){$error=$e->getMessage();}
}
$students=learning_students_for_select();ob_start();
?>
<div class="d-flex justify-content-between mb-4"><p class="text-muted">Create material as link, YouTube/video, PDF, PowerPoint/file, text or HTML.</p><a class="btn btn-outline-brand" href="/owner/materials">Back</a></div>
<?php if($error):?><div class="alert alert-danger"><?=htmlspecialchars($error,ENT_QUOTES,'UTF-8')?></div><?php endif;?>
<form method="post" class="foundation-card"><div class="row g-3">
 <div class="col-md-6"><label class="form-label">Title</label><input class="form-control" name="title" required></div>
 <div class="col-md-3"><label class="form-label">Type</label><select class="form-select" name="material_type"><option value="link">Link</option><option value="youtube">YouTube</option><option value="video">Video</option><option value="pdf">PDF</option><option value="powerpoint">PowerPoint</option><option value="file">File</option><option value="html">HTML</option><option value="text">Text</option></select></div>
 <div class="col-md-3"><label class="form-label">Active?</label><div class="form-check mt-2"><input class="form-check-input" type="checkbox" name="is_active" checked><label class="form-check-label">Active</label></div></div>
 <div class="col-md-6"><label class="form-label">Assign to student optional</label><select class="form-select" name="assigned_student_user_id"><option value="">All matching level/global</option><?php foreach($students as $s):?><option value="<?= (int)$s['id']?>"><?=htmlspecialchars($s['display_name'].' — '.$s['email'],ENT_QUOTES,'UTF-8')?></option><?php endforeach;?></select></div>
 <div class="col-md-6"><label class="form-label">Level optional</label><input class="form-control" name="level" placeholder="A1, A2, B1..."></div>
 <div class="col-12"><label class="form-label">File/link URL</label><input class="form-control" name="file_path" placeholder="https://... or /uploads/materials/file.pdf"></div>
 <div class="col-12"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="2"></textarea></div>
 <div class="col-12"><label class="form-label">Text / HTML content</label><textarea class="form-control" name="content" rows="6"></textarea></div>
 <div class="col-12"><button class="btn btn-brand">Create material</button></div>
</div></form>
<?php $content=ob_get_clean();render_dashboard_shell($user,'Create Material',$content);