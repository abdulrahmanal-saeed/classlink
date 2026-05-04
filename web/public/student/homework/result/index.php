<?php
/** /student/homework/result?id=... */
require_once __DIR__ . '/../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../backend/php/shared/LearningAssignments.php';
require_once __DIR__ . '/../../../../../web/components/layout/dashboard_shell.php';
$user=require_role('student');$id=(int)($_GET['id']??0);$homework=learning_homework_detail($id,(int)$user['id']);$sub=learning_homework_submission($id,(int)$user['id']);
if(!$homework||!$sub){http_response_code(404);$content='<div class="alert alert-danger">Result not found.</div>';render_dashboard_shell($user,'Homework Result Not Found',$content);exit;}
$answers=learning_result_answers($sub);
ob_start();
?>
<div class="d-flex justify-content-between mb-4"><p class="text-muted">Your result shows the exact answer you selected, the correct answer if incorrect, and teacher feedback.</p><a class="btn btn-outline-brand" href="/student/homework">Back</a></div>
<div class="foundation-card mb-4"><h2 class="h4 fw-bold"><?=htmlspecialchars($homework['title'],ENT_QUOTES,'UTF-8')?></h2><p>Score: <strong><?=htmlspecialchars((string)($sub['score']??'-'),ENT_QUOTES,'UTF-8')?> / <?=htmlspecialchars((string)($sub['max_score']??'-'),ENT_QUOTES,'UTF-8')?></strong></p><span class="badge text-bg-light border"><?=htmlspecialchars($sub['status'],ENT_QUOTES,'UTF-8')?></span><?php if(!empty($sub['feedback'])):?><div class="alert alert-info mt-3 mb-0"><?=nl2br(htmlspecialchars($sub['feedback'],ENT_QUOTES,'UTF-8'))?></div><?php endif;?></div>
<?php foreach($answers as $a):?><div class="status-box mb-3"><strong><?=htmlspecialchars($a['prompt'],ENT_QUOTES,'UTF-8')?></strong><br><div class="mt-2">Your answer: <?=htmlspecialchars($a['selected_text']??'-',ENT_QUOTES,'UTF-8')?></div><?php if(($a['is_correct']??null)===false):?><div class="text-danger">Correct answer: <?=htmlspecialchars($a['correct_text']??'-',ENT_QUOTES,'UTF-8')?></div><?php elseif(($a['is_correct']??null)===true):?><div class="text-success">Correct</div><?php else:?><div class="text-muted">Waiting for/manual teacher correction</div><?php endif;?><?php if(!empty($a['explanation'])):?><small class="text-muted"><?=htmlspecialchars($a['explanation'],ENT_QUOTES,'UTF-8')?></small><?php endif;?></div><?php endforeach;?>
<?php $content=ob_get_clean();render_dashboard_shell($user,'Homework Result',$content);