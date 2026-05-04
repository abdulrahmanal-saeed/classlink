<?php
require_once __DIR__ . '/../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../backend/php/shared/MaterialsLibrary.php';
require_once __DIR__ . '/../../../../../web/components/layout/dashboard_shell.php';
$user=require_role('owner_teacher');$id=(int)($_GET['id']??0);$m=material_find($id);
if(!$m){http_response_code(404);render_dashboard_shell($user,'Material Not Found','<div class="alert alert-danger">Material not found.</div>');exit;}
ob_start();
?>
<div class="d-flex justify-content-between mb-4"><p class="text-muted">Preview material as Owner.</p><div class="d-flex gap-2"><a class="btn btn-outline-brand" href="/owner/materials/edit?id=<?=$id?>">Edit</a><a class="btn btn-outline-brand" href="/owner/materials/assign?id=<?=$id?>">Assign</a><a class="btn btn-outline-brand" href="/owner/materials">Back</a></div></div>
<div class="foundation-card mb-4"><h2 class="h4 fw-bold"><?=htmlspecialchars($m['title'],ENT_QUOTES,'UTF-8')?></h2><p class="text-muted"><?=nl2br(htmlspecialchars($m['description']??'',ENT_QUOTES,'UTF-8'))?></p><div class="d-flex gap-2 flex-wrap mb-3"><span class="badge text-bg-light border"><?=htmlspecialchars($m['material_type'],ENT_QUOTES,'UTF-8')?></span><span class="badge text-bg-light border"><?=htmlspecialchars($m['status'],ENT_QUOTES,'UTF-8')?></span><span class="badge text-bg-light border"><?=htmlspecialchars($m['level'],ENT_QUOTES,'UTF-8')?></span><span class="badge text-bg-light border"><?=htmlspecialchars($m['material_language'],ENT_QUOTES,'UTF-8')?></span></div><?=material_render_viewer($m)?><?php if($m['allow_download'] && $m['file_url']):?><div class="mt-3"><a class="btn btn-outline-brand" download href="<?=htmlspecialchars($m['file_url'],ENT_QUOTES,'UTF-8')?>">Download</a></div><?php endif;?></div>
<?php $content=ob_get_clean();render_dashboard_shell($user,'Material Preview',$content);