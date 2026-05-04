<?php
/** /student/scenarios/result?id=... */
require_once __DIR__ . '/../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../backend/php/shared/LearningAssignments.php';
require_once __DIR__ . '/../../../../../web/components/layout/dashboard_shell.php';
$user=require_role('student');$id=(int)($_GET['id']??0);$scenario=learning_scenario_detail($id,(int)$user['id']);$sub=learning_scenario_submission($id,(int)$user['id']);
if(!$scenario||!$sub){http_response_code(404);$content='<div class="alert alert-danger">Scenario result not found.</div>';render_dashboard_shell($user,'Scenario Result Not Found',$content);exit;}
ob_start();
?>
<div class="d-flex justify-content-between mb-4"><p class="text-muted">Your speaking scenario result and teacher feedback.</p><a class="btn btn-outline-brand" href="/student/scenarios">Back</a></div>
<div class="foundation-card mb-4"><h2 class="h4 fw-bold"><?=htmlspecialchars($scenario['title'],ENT_QUOTES,'UTF-8')?></h2><p><?=nl2br(htmlspecialchars($scenario['situation'],ENT_QUOTES,'UTF-8'))?></p><?php if(!empty($scenario['model_answer'])):?><div class="alert alert-light border"><strong>Model answer:</strong><br><?=nl2br(htmlspecialchars($scenario['model_answer'],ENT_QUOTES,'UTF-8'))?></div><?php endif;?></div>
<div class="foundation-card"><h2 class="h5 fw-bold">Your submission</h2><?php if(!empty($sub['audio_path'])):?><a target="_blank" href="<?=htmlspecialchars($sub['audio_path'],ENT_QUOTES,'UTF-8')?>">Open your audio/upload</a><?php endif;?><p class="mt-2"><?=nl2br(htmlspecialchars($sub['transcript']??'',ENT_QUOTES,'UTF-8'))?></p><p>Score: <strong><?=htmlspecialchars((string)($sub['score']??'-'),ENT_QUOTES,'UTF-8')?></strong></p><?php if(!empty($sub['feedback'])):?><div class="alert alert-info"><?=nl2br(htmlspecialchars($sub['feedback'],ENT_QUOTES,'UTF-8'))?></div><?php else:?><div class="alert alert-light border">Waiting for teacher feedback.</div><?php endif;?></div>
<?php $content=ob_get_clean();render_dashboard_shell($user,'Scenario Result',$content);