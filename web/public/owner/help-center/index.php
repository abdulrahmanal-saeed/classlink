<?php
require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../backend/php/shared/HelpCenter.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';
$user=require_role('owner_teacher');
$articles=help_owner_articles();
ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4"><p class="text-muted mb-1">Manage platform help content and role-based guides.</p><a class="btn btn-brand" href="/owner/help-center/articles/new">New Help Article</a></div>
<div class="foundation-card"><h2 class="h5 fw-bold">Help Articles</h2><?php if(!$articles):?><div class="alert alert-light border">No help articles yet.</div><?php else:?><div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Title</th><th>Role</th><th>Category</th><th>Language</th><th>Status</th><th>Action</th></tr></thead><tbody><?php foreach($articles as $a):?><tr><td><?=htmlspecialchars($a['title'],ENT_QUOTES,'UTF-8')?></td><td><?=htmlspecialchars($a['role'],ENT_QUOTES,'UTF-8')?></td><td><?=htmlspecialchars($a['category'],ENT_QUOTES,'UTF-8')?></td><td><?=htmlspecialchars($a['language'],ENT_QUOTES,'UTF-8')?></td><td><span class="badge text-bg-light border"><?=htmlspecialchars($a['status'],ENT_QUOTES,'UTF-8')?></span></td><td><a class="btn btn-sm btn-outline-brand" href="/owner/help-center/articles/edit?id=<?=(int)$a['id']?>">Edit</a></td></tr><?php endforeach;?></tbody></table></div><?php endif;?></div>
<?php $content=ob_get_clean();render_dashboard_shell($user,'Help Center CMS',$content);