<?php
/** /owner/scenarios/submissions?id=... */
require_once __DIR__ . '/../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../backend/php/shared/LearningAssignments.php';
require_once __DIR__ . '/../../../../../web/components/layout/dashboard_shell.php';
$user=require_role('owner_teacher');$scenarioId=(int)($_GET['id']??0);$scenario=learning_scenario_detail($scenarioId);$message=$error=null;
if(!$scenario){http_response_code(404);$content='<div class="alert alert-danger">Scenario not found.</div>';render_dashboard_shell($user,'Scenario Not Found',$content);exit;}
if($_SERVER['REQUEST_METHOD']==='POST'){try{learning_correct_scenario((int)$_POST['submission_id'],(int)$user['id'],$_POST);$message='Feedback saved.';}catch(Throwable $e){$error=$e->getMessage();}}
$sub=learning_scenario_submission($scenarioId,(int)$scenario['student_user_id']);
ob_start();
?>
<div class="d-flex justify-content-between mb-4"><p class="text-muted">Review student recording/upload and give feedback.</p><a class="btn btn-outline-brand" href="/owner/scenarios">Back</a></div><?php if($message):?><div class="alert alert-success"><?=htmlspecialchars($message,ENT_QUOTES,'UTF-8')?></div><?php endif;?><?php if($error):?><div class="alert alert-danger"><?=htmlspecialchars($error,ENT_QUOTES,'UTF-8')?></div><?php endif;?>
<div class="foundation-card mb-4"><h2 class="h4 fw-bold"><?=htmlspecialchars($scenario['title'],ENT_QUOTES,'UTF-8')?></h2><p><?=nl2br(htmlspecialchars($scenario['situation'],ENT_QUOTES,'UTF-8'))?></p><p><strong>Prompt:</strong><br><?=nl2br(htmlspecialchars($scenario['prompt'],ENT_QUOTES,'UTF-8'))?></p><p class="text-muted">Student: <?=htmlspecialchars($scenario['student_name']??'-',ENT_QUOTES,'UTF-8')?> · Limit: <?= (int)$scenario['time_limit_seconds']?> seconds</p></div>
<?php if(!$sub):?><div class="alert alert-light border">No scenario submission yet.</div><?php else:?>
<div class="foundation-card mb-4"><h2 class="h5 fw-bold">Submission</h2><?php if(!empty($sub['audio_path'])):?><a href="<?=htmlspecialchars($sub['audio_path'],ENT_QUOTES,'UTF-8')?>" target="_blank">Open audio/upload</a><?php endif;?><p class="mt-2"><?=nl2br(htmlspecialchars($sub['transcript']??'',ENT_QUOTES,'UTF-8'))?></p><p class="text-muted">Submitted: <?=htmlspecialchars($sub['submitted_at']??'-',ENT_QUOTES,'UTF-8')?></p></div>
<form method="post" class="foundation-card"><input type="hidden" name="submission_id" value="<?= (int)$sub['id']?>"><div class="row g-3"><div class="col-md-3"><label class="form-label">Score</label><input class="form-control" name="score" value="<?=htmlspecialchars((string)($sub['score']??0),ENT_QUOTES,'UTF-8')?>"></div><div class="col-12"><label class="form-label">Student feedback</label><textarea class="form-control" name="feedback" rows="4"><?=htmlspecialchars($sub['feedback']??'',ENT_QUOTES,'UTF-8')?></textarea></div><div class="col-12"><label class="form-label">Internal/owner feedback</label><textarea class="form-control" name="owner_feedback" rows="3"><?=htmlspecialchars($sub['owner_feedback']??'',ENT_QUOTES,'UTF-8')?></textarea></div><div class="col-12"><button class="btn btn-brand">Save feedback</button></div></div></form>
<?php endif;?>
<?php $content=ob_get_clean();render_dashboard_shell($user,'Scenario Submissions',$content);