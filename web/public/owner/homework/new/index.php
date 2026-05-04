<?php
/** /owner/homework/new - Create homework with dynamic section rows. */
require_once __DIR__ . '/../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../backend/php/shared/LearningAssignments.php';
require_once __DIR__ . '/../../../../../web/components/layout/dashboard_shell.php';
$user = require_role('owner_teacher');
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try { $id = learning_create_homework((int)$user['id'], $_POST); header('Location: /owner/homework/view?id=' . $id); exit; }
    catch (Throwable $e) { $error = $e->getMessage(); }
}
$students = learning_students_for_select();
ob_start();
?>
<div class="d-flex justify-content-between mb-4"><p class="text-muted">Create homework with MCQ, reading, listening, writing, and speaking questions.</p><a class="btn btn-outline-brand" href="/owner/homework">Back</a></div>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
<form method="post" class="foundation-card">
 <div class="row g-3">
  <div class="col-md-6"><label class="form-label">Student</label><select class="form-select" name="student_user_id" required><?php foreach($students as $s): ?><option value="<?= (int)$s['id'] ?>"><?= htmlspecialchars($s['display_name'].' — '.$s['email'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></div>
  <div class="col-md-6"><label class="form-label">Status</label><select class="form-select" name="status"><option value="draft">draft</option><option value="published">published</option></select></div>
  <div class="col-md-8"><label class="form-label">Title</label><input class="form-control" name="title" required></div>
  <div class="col-md-4"><label class="form-label">Due at</label><input class="form-control" type="datetime-local" name="due_at"></div>
  <div class="col-12"><label class="form-label">Instructions</label><textarea class="form-control" name="instructions" rows="3"></textarea></div>
 </div>
 <hr>
 <h2 class="h5 fw-bold">Questions</h2>
 <?php for($i=0;$i<5;$i++): ?>
 <div class="border rounded-4 p-3 mb-3">
  <div class="row g-2">
   <div class="col-md-3"><label class="form-label">Type</label><select class="form-select" name="question_type[]"><option value="mcq">MCQ</option><option value="reading">Reading</option><option value="listening">Listening</option><option value="writing">Writing</option><option value="speaking">Speaking</option></select></div>
   <div class="col-md-2"><label class="form-label">Points</label><input class="form-control" name="points[]" value="1"></div>
   <div class="col-md-4"><label class="form-label">Media URL</label><input class="form-control" name="media_url[]" placeholder="audio/video optional"></div>
   <div class="col-md-3"><label class="form-label">Answer key</label><input class="form-control" name="answer_key[]" placeholder="A or full answer"></div>
   <div class="col-12"><label class="form-label">Prompt</label><textarea class="form-control" name="prompt[]" rows="2"></textarea></div>
   <?php foreach(['a','b','c','d'] as $l): ?><div class="col-md-3"><input class="form-control" name="option_<?= $l ?>[]" placeholder="Option <?= strtoupper($l) ?>"></div><?php endforeach; ?>
   <div class="col-12"><input class="form-control" name="explanation[]" placeholder="Explanation / correction note optional"></div>
  </div>
 </div>
 <?php endfor; ?>
 <button class="btn btn-brand" type="submit">Create homework</button>
</form>
<?php $content=ob_get_clean(); render_dashboard_shell($user,'Create Homework',$content);