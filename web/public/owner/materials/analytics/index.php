<?php
require_once __DIR__ . '/../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../backend/php/shared/MaterialsLibrary.php';
require_once __DIR__ . '/../../../../../web/components/layout/dashboard_shell.php';
$user=require_role('owner_teacher');
$health=material_storage_health();
$materials=(int)db()->query('SELECT COUNT(*) FROM course_materials')->fetchColumn();
$assignments=(int)db()->query('SELECT COUNT(*) FROM material_assignments')->fetchColumn();
$completed=(int)db()->query('SELECT COUNT(*) FROM material_progress WHERE status="completed"')->fetchColumn();
$viewed=(int)db()->query('SELECT COUNT(*) FROM material_progress WHERE status IN ("viewed","completed")')->fetchColumn();
ob_start();
?>
<div class="d-flex justify-content-between mb-4"><p class="text-muted">Material analytics and storage health.</p><a class="btn btn-outline-brand" href="/owner/materials">Back</a></div>
<div class="row g-3 mb-4"><div class="col-md-3"><div class="status-box">Materials<br><strong><?=$materials?></strong></div></div><div class="col-md-3"><div class="status-box">Assignments<br><strong><?=$assignments?></strong></div></div><div class="col-md-3"><div class="status-box">Viewed<br><strong><?=$viewed?></strong></div></div><div class="col-md-3"><div class="status-box">Completed<br><strong><?=$completed?></strong></div></div></div>
<div class="foundation-card"><h2 class="h5 fw-bold">Storage Health</h2><div class="table-responsive"><table class="table"><tbody><tr><th>Storage driver</th><td><?=htmlspecialchars($health['storage_driver'],ENT_QUOTES,'UTF-8')?></td></tr><tr><th>Upload folder exists</th><td><?=$health['upload_root_exists']?'Yes':'No'?></td></tr><tr><th>Upload folder writable</th><td><?=$health['upload_root_writable']?'Yes':'No'?></td></tr><tr><th>Database connected</th><td><?=$health['database_connected']?'Yes':'No'?></td></tr><tr><th>Notes</th><td><?=htmlspecialchars($health['notes'],ENT_QUOTES,'UTF-8')?></td></tr></tbody></table></div></div>
<?php $content=ob_get_clean();render_dashboard_shell($user,'Material Analytics',$content);