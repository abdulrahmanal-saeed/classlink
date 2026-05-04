<?php
/** /student/homework/view?id=... */
require_once __DIR__ . '/../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../backend/php/shared/LearningAssignments.php';
require_once __DIR__ . '/../../../../../backend/php/shared/LearningEngagement.php';
require_once __DIR__ . '/../../../../../backend/php/shared/Analytics.php';
require_once __DIR__ . '/../../../../../backend/php/shared/PushNotifications.php';
require_once __DIR__ . '/../../../../../web/components/layout/dashboard_shell.php';
$user=require_role('student');$id=(int)($_GET['id']??0);$message=$error=null;
if($_SERVER['REQUEST_METHOD']==='POST'){try{$submissionId=learning_submit_homework($id,(int)$user['id'],$_POST,$_FILES);engagement_log_activity((int)$user['id'],'homework_submitted','homework',(string)$id,['submission_id'=>$submissionId]);analytics_track('homework_submit',['role'=>'student','entity_type'=>'homework','entity_id'=>$id,'metadata'=>['submission_id'=>$submissionId]],(int)$user['id']);push_send_to_owners('homework_submitted','Homework submitted',($user['display_name'] ?? 'Student').' submitted homework #'.$id,'/owner/homework/submissions?id='.$id,['homework_id'=>$id,'submission_id'=>$submissionId,'student_id'=>(int)$user['id']]);header('Location: /student/homework/result?id='.$id);exit;}catch(Throwable $e){$error=$e->getMessage();}}
$homework=learning_homework_detail($id,(int)$user['id']);
if(!$homework){http_response_code(404);$content='<div class="alert alert-danger">Homework not found.</div>';render_dashboard_shell($user,'Homework Not Found',$content);exit;}
ob_start();
?>
<div class="d-flex justify-content-between mb-4"><p class="text-muted">Submit your answers. For speaking/audio, paste uploaded audio link for now.</p><a class="btn btn-outline-brand" href="/student/homework">Back</a></div>
<?php if($error):?><div class="alert alert-danger"><?=htmlspecialchars($error,ENT_QUOTES,'UTF-8')?></div><?php endif;?>
<form method="post" class="foundation-card"><h2 class="h4 fw-bold"><?=htmlspecialchars($homework['title'],ENT_QUOTES,'UTF-8')?></h2><p class="text-muted"><?=nl2br(htmlspecialchars($homework['instructions']??'',ENT_QUOTES,'UTF-8'))?></p>
<?php foreach($homework['questions'] as $q): $opts=learning_parse_options($q['options_json']??null); ?>
<div class="border rounded-4 p-3 mb-3"><strong><?=htmlspecialchars(strtoupper($q['question_type']),ENT_QUOTES,'UTF-8')?></strong><p><?=nl2br(htmlspecialchars($q['prompt'],ENT_QUOTES,'UTF-8'))?></p><?php if(!empty($q['media_url'])):?><a target="_blank" href="<?=htmlspecialchars($q['media_url'],ENT_QUOTES,'UTF-8')?>">Open media</a><?php endif;?><?php if($opts): foreach($opts as $k=>$v): ?><div class="form-check"><input class="form-check-input" type="radio" name="answer[<?= (int)$q['id']?>]" value="<?=htmlspecialchars($k,ENT_QUOTES,'UTF-8')?>"><label class="form-check-label"><strong><?=htmlspecialchars($k,ENT_QUOTES,'UTF-8')?>:</strong> <?=htmlspecialchars($v,ENT_QUOTES,'UTF-8')?></label></div><?php endforeach; else:?><textarea class="form-control" name="answer[<?= (int)$q['id']?>]" rows="3" placeholder="Your answer / audio link"></textarea><?php endif;?></div>
<?php endforeach; ?>
<button class="btn btn-brand" type="submit">Submit homework</button></form>
<?php $content=ob_get_clean();render_dashboard_shell($user,'Homework',$content);