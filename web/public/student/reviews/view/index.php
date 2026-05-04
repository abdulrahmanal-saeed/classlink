<?php
/** /student/reviews/view?id=... */
require_once __DIR__ . '/../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../backend/php/shared/LearningAssignments.php';
require_once __DIR__ . '/../../../../../backend/php/shared/LearningEngagement.php';
require_once __DIR__ . '/../../../../../backend/php/shared/Analytics.php';
require_once __DIR__ . '/../../../../../backend/php/shared/PushNotifications.php';
require_once __DIR__ . '/../../../../../web/components/layout/dashboard_shell.php';
$user=require_role('student');$id=(int)($_GET['id']??0);$error=null;
if($_SERVER['REQUEST_METHOD']==='POST'){try{$submissionId=learning_submit_review($id,(int)$user['id'],$_POST);engagement_log_activity((int)$user['id'],'review_taken','review_test',(string)$id,['submission_id'=>$submissionId]);analytics_track('review_submit',['role'=>'student','entity_type'=>'review','entity_id'=>$id,'metadata'=>['submission_id'=>$submissionId]],(int)$user['id']);push_send_to_owners('review_submitted','Review submitted',($user['display_name'] ?? 'Student').' submitted review/test #'.$id,'/owner/reviews/results?id='.$id,['review_id'=>$id,'submission_id'=>$submissionId,'student_id'=>(int)$user['id']]);header('Location: /student/reviews/result?id='.$id);exit;}catch(Throwable $e){$error=$e->getMessage();}}
$test=learning_review_detail($id,(int)$user['id']);
if(!$test){http_response_code(404);$content='<div class="alert alert-danger">Review not found.</div>';render_dashboard_shell($user,'Review Not Found',$content);exit;}
ob_start();
?>
<div class="d-flex justify-content-between mb-4"><p class="text-muted">Answer the review/test questions.</p><a class="btn btn-outline-brand" href="/student/reviews">Back</a></div><?php if($error):?><div class="alert alert-danger"><?=htmlspecialchars($error,ENT_QUOTES,'UTF-8')?></div><?php endif;?>
<form method="post" class="foundation-card"><h2 class="h4 fw-bold"><?=htmlspecialchars($test['title'],ENT_QUOTES,'UTF-8')?></h2><p class="text-muted"><?=nl2br(htmlspecialchars($test['instructions']??'',ENT_QUOTES,'UTF-8'))?></p>
<?php foreach($test['questions'] as $q):$opts=learning_parse_options($q['options_json']??null);?><div class="border rounded-4 p-3 mb-3"><strong><?=htmlspecialchars(strtoupper($q['question_type']),ENT_QUOTES,'UTF-8')?></strong><p><?=nl2br(htmlspecialchars($q['prompt'],ENT_QUOTES,'UTF-8'))?></p><?php if(!empty($q['media_url'])):?><a target="_blank" href="<?=htmlspecialchars($q['media_url'],ENT_QUOTES,'UTF-8')?>">Open media</a><?php endif;?><?php if($opts):foreach($opts as $k=>$v):?><div class="form-check"><input class="form-check-input" type="radio" name="answer[<?= (int)$q['id']?>]" value="<?=htmlspecialchars($k,ENT_QUOTES,'UTF-8')?>"><label class="form-check-label"><strong><?=htmlspecialchars($k,ENT_QUOTES,'UTF-8')?>:</strong> <?=htmlspecialchars($v,ENT_QUOTES,'UTF-8')?></label></div><?php endforeach;else:?><textarea class="form-control" name="answer[<?= (int)$q['id']?>]" rows="3" placeholder="Your answer / audio link"></textarea><?php endif;?></div><?php endforeach;?><button class="btn btn-brand">Submit review</button></form>
<?php $content=ob_get_clean();render_dashboard_shell($user,'Review/Test',$content);