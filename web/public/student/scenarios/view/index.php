<?php
/** /student/scenarios/view?id=... */
require_once __DIR__ . '/../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../backend/php/shared/LearningAssignments.php';
require_once __DIR__ . '/../../../../../backend/php/shared/LearningEngagement.php';
require_once __DIR__ . '/../../../../../backend/php/shared/Analytics.php';
require_once __DIR__ . '/../../../../../backend/php/shared/PushNotifications.php';
require_once __DIR__ . '/../../../../../web/components/layout/dashboard_shell.php';
$user=require_role('student');$id=(int)($_GET['id']??0);$error=null;
if($_SERVER['REQUEST_METHOD']==='POST'){try{$submissionId=learning_submit_scenario($id,(int)$user['id'],$_POST);engagement_log_activity((int)$user['id'],'scenario_submitted','scenario',(string)$id,['submission_id'=>$submissionId]);analytics_track('scenario_submit',['role'=>'student','entity_type'=>'scenario','entity_id'=>$id,'metadata'=>['submission_id'=>$submissionId]],(int)$user['id']);push_send_to_owners('scenario_submitted','Scenario submitted',($user['display_name'] ?? 'Student').' submitted scenario #'.$id,'/owner/scenarios/submissions?id='.$id,['scenario_id'=>$id,'submission_id'=>$submissionId,'student_id'=>(int)$user['id']]);header('Location: /student/scenarios/result?id='.$id);exit;}catch(Throwable $e){$error=$e->getMessage();}}
$scenario=learning_scenario_detail($id,(int)$user['id']);
if(!$scenario){http_response_code(404);$content='<div class="alert alert-danger">Scenario not found.</div>';render_dashboard_shell($user,'Scenario Not Found',$content);exit;}
ob_start();
?>
<div class="d-flex justify-content-between mb-4"><p class="text-muted">Record/upload your answer. For now, paste audio/upload URL if browser recording is not configured.</p><a class="btn btn-outline-brand" href="/student/scenarios">Back</a></div><?php if($error):?><div class="alert alert-danger"><?=htmlspecialchars($error,ENT_QUOTES,'UTF-8')?></div><?php endif;?>
<div class="foundation-card mb-4"><h2 class="h4 fw-bold"><?=htmlspecialchars($scenario['title'],ENT_QUOTES,'UTF-8')?></h2><p><strong>Situation:</strong><br><?=nl2br(htmlspecialchars($scenario['situation'],ENT_QUOTES,'UTF-8'))?></p><p><strong>Prompt:</strong><br><?=nl2br(htmlspecialchars($scenario['prompt'],ENT_QUOTES,'UTF-8'))?></p><p class="text-muted">Time limit: <?= (int)$scenario['time_limit_seconds']?> seconds · Keywords: <?=htmlspecialchars($scenario['keywords']??'-',ENT_QUOTES,'UTF-8')?></p></div>
<form method="post" class="foundation-card"><div class="mb-3"><label class="form-label">Audio/upload URL</label><input class="form-control" name="audio_path" placeholder="/uploads/... or external link"></div><div class="mb-3"><label class="form-label">Transcript / notes optional</label><textarea class="form-control" name="transcript" rows="4"></textarea></div><button class="btn btn-brand">Submit scenario</button></form>
<?php $content=ob_get_clean();render_dashboard_shell($user,'Speaking Scenario',$content);