<?php
/** /owner/homework/view?id=... */
require_once __DIR__ . '/../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../backend/php/shared/LearningAssignments.php';
require_once __DIR__ . '/../../../../../web/components/layout/dashboard_shell.php';
$user=require_role('owner_teacher');
$id=(int)($_GET['id']??0);
$homework=learning_homework_detail($id);
if(!$homework){http_response_code(404);$content='<div class="alert alert-danger">Homework not found.</div>';render_dashboard_shell($user,'Homework Not Found',$content);exit;}
ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4"><div><p class="text-muted mb-1">Homework detail and questions.</p><?= '<span class="badge text-bg-light border">'.htmlspecialchars($homework['status'],ENT_QUOTES,'UTF-8').'</span>' ?></div><div class="d-flex gap-2"><a class="btn btn-outline-brand" href="/owner/homework">Back</a><a class="btn btn-brand" href="/owner/homework/submissions?id=<?= (int)$id ?>">Submissions</a></div></div>
<div class="foundation-card mb-4"><h2 class="h4 fw-bold"><?= htmlspecialchars($homework['title'],ENT_QUOTES,'UTF-8') ?></h2><p class="text-muted">Student: <?= htmlspecialchars($homework['student_name']??'-',ENT_QUOTES,'UTF-8') ?> · Due: <?= htmlspecialchars($homework['due_at']??'-',ENT_QUOTES,'UTF-8') ?></p><div><?= nl2br(htmlspecialchars($homework['instructions']??'',ENT_QUOTES,'UTF-8')) ?></div></div>
<?php foreach($homework['questions'] as $q): $opts=learning_parse_options($q['options_json']??null); ?>
<div class="status-box mb-3"><strong><?= htmlspecialchars(strtoupper($q['question_type']),ENT_QUOTES,'UTF-8') ?></strong><p class="mb-2"><?= nl2br(htmlspecialchars($q['prompt'],ENT_QUOTES,'UTF-8')) ?></p><?php if(!empty($q['media_url'])):?><a href="<?= htmlspecialchars($q['media_url'],ENT_QUOTES,'UTF-8') ?>" target="_blank">Open media</a><?php endif; ?><?php if($opts): ?><ul class="mt-2"><?php foreach($opts as $k=>$v): ?><li><strong><?= htmlspecialchars($k,ENT_QUOTES,'UTF-8') ?>:</strong> <?= htmlspecialchars($v,ENT_QUOTES,'UTF-8') ?></li><?php endforeach; ?></ul><?php endif; ?><div class="small text-muted">Answer: <?= htmlspecialchars($q['answer_key']??'-',ENT_QUOTES,'UTF-8') ?> · Points: <?= htmlspecialchars((string)$q['points'],ENT_QUOTES,'UTF-8') ?></div></div>
<?php endforeach; ?>
<?php $content=ob_get_clean();render_dashboard_shell($user,'Homework Detail',$content);