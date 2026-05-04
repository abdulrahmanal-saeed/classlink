<?php
/** /owner/homework/submissions?id=... */
require_once __DIR__ . '/../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../backend/php/shared/LearningAssignments.php';
require_once __DIR__ . '/../../../../../web/components/layout/dashboard_shell.php';
$user=require_role('owner_teacher');
$homeworkId=(int)($_GET['id']??0);
$homework=learning_homework_detail($homeworkId);
if(!$homework){http_response_code(404);$content='<div class="alert alert-danger">Homework not found.</div>';render_dashboard_shell($user,'Homework Not Found',$content);exit;}
$message=$error=null;
if($_SERVER['REQUEST_METHOD']==='POST'){
 try{learning_correct_homework($homeworkId,(int)$_POST['student_user_id'],(int)$user['id'],$_POST);$message='Homework corrected.';}catch(Throwable $e){$error=$e->getMessage();}
}
$sub=learning_homework_submission($homeworkId,(int)$homework['student_user_id']);
$answers=learning_result_answers($sub);
ob_start();
?>
<div class="d-flex justify-content-between mb-4"><p class="text-muted">Correct submission and add feedback.</p><a class="btn btn-outline-brand" href="/owner/homework/view?id=<?= (int)$homeworkId ?>">Back</a></div>
<?php if($message):?><div class="alert alert-success"><?=htmlspecialchars($message,ENT_QUOTES,'UTF-8')?></div><?php endif;?><?php if($error):?><div class="alert alert-danger"><?=htmlspecialchars($error,ENT_QUOTES,'UTF-8')?></div><?php endif;?>
<?php if(!$sub):?><div class="alert alert-light border">No submission yet.</div><?php else:?>
<div class="foundation-card mb-4"><h2 class="h5 fw-bold">Submission</h2><p class="text-muted">Score: <?=htmlspecialchars((string)($sub['score']??'-'),ENT_QUOTES,'UTF-8')?> / <?=htmlspecialchars((string)($sub['max_score']??'-'),ENT_QUOTES,'UTF-8')?> · Status: <?=htmlspecialchars($sub['status'],ENT_QUOTES,'UTF-8')?></p>
<?php foreach($answers as $a):?><div class="border rounded-4 p-2 mb-2"><strong><?=htmlspecialchars($a['prompt'],ENT_QUOTES,'UTF-8')?></strong><br><span>Student selected: <?=htmlspecialchars(($a['selected_text']??'-'),ENT_QUOTES,'UTF-8')?></span><br><?php if(($a['is_correct']??null)===false):?><span class="text-danger">Correct answer: <?=htmlspecialchars(($a['correct_text']??'-'),ENT_QUOTES,'UTF-8')?></span><?php elseif(($a['is_correct']??null)===true):?><span class="text-success">Correct</span><?php else:?><span class="text-muted">Manual review required</span><?php endif;?></div><?php endforeach;?>
</div>
<form method="post" class="foundation-card"><input type="hidden" name="student_user_id" value="<?= (int)$homework['student_user_id']?>"><div class="row g-3"><div class="col-md-3"><label class="form-label">Score</label><input class="form-control" name="score" value="<?=htmlspecialchars((string)($sub['score']??0),ENT_QUOTES,'UTF-8') ?>"></div><div class="col-md-3"><label class="form-label">Max score</label><input class="form-control" name="max_score" value="<?=htmlspecialchars((string)($sub['max_score']??0),ENT_QUOTES,'UTF-8') ?>"></div><div class="col-12"><label class="form-label">Teacher feedback</label><textarea class="form-control" name="feedback" rows="4"><?=htmlspecialchars($sub['feedback']??'',ENT_QUOTES,'UTF-8')?></textarea></div><div class="col-12"><label class="form-label">Manual override note</label><textarea class="form-control" name="override_note" rows="2"></textarea></div><div class="col-12"><button class="btn btn-brand">Save correction</button></div></div></form>
<?php endif;?>
<?php $content=ob_get_clean();render_dashboard_shell($user,'Homework Submissions',$content);