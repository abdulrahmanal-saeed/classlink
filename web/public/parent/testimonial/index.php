<?php
require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../backend/php/shared/Testimonials.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';

$user = require_role('parent');
$message = null;
$error = null;
$childrenStmt = db()->prepare('SELECT pcl.student_user_id AS id, u.display_name FROM parent_child_links pcl JOIN users u ON u.id = pcl.student_user_id WHERE pcl.parent_user_id = :parent');
$childrenStmt->execute([':parent' => (int)$user['id']]);
$children = $childrenStmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  try {
    if (!testimonial_bool('testimonials_from_parents_enabled', true)) throw new RuntimeException('Parent testimonials are disabled.');
    $childId = (int)($_POST['child_student_user_id'] ?? 0);
    if (!$childId || !testimonial_parent_can_access_child((int)$user['id'], $childId)) throw new RuntimeException('You can only submit for linked children.');
    $pref = $_POST['display_preference'] ?? 'show_parent_first_name';
    $displayName = $pref === 'anonymous' ? 'Anonymous' : testimonial_first_name($user['display_name'] ?? 'Parent');
    testimonial_create([
      'submitter_type' => 'parent',
      'parent_user_id' => (int)$user['id'],
      'child_student_user_id' => $childId,
      'source' => 'parent_dashboard',
      'rating' => $_POST['rating'] ?? null,
      'text' => $_POST['testimonial_text'] ?? '',
      'display_name' => $displayName,
      'display_preference' => $pref,
      'child_learning_focus' => $_POST['child_learning_focus'] ?? null,
      'permission_to_publish' => !empty($_POST['permission_to_publish']),
    ], $user, $_FILES);
    $message = 'Thank you! Your testimonial has been sent for review.';
  } catch (Throwable $e) { $error = $e->getMessage(); }
}

ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4"><p class="text-muted mb-1">Share your child’s Arabic learning progress. Nothing is published before review.</p><a class="btn btn-outline-brand" href="/parent/dashboard">Back</a></div>
<?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
<form method="post" enctype="multipart/form-data" class="foundation-card"><div class="row g-3">
<div class="col-md-4"><label class="form-label">Child</label><select class="form-select" name="child_student_user_id" required><option value="">Choose</option><?php foreach($children as $child): ?><option value="<?= (int)$child['id'] ?>"><?= htmlspecialchars($child['display_name'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></div>
<div class="col-md-4"><label class="form-label">Rating</label><select class="form-select" name="rating" required><option value="">Choose</option><?php for($i=5;$i>=1;$i--): ?><option value="<?= $i ?>"><?= $i ?> stars</option><?php endfor; ?></select></div>
<div class="col-md-4"><label class="form-label">Display</label><select class="form-select" name="display_preference"><option value="show_parent_first_name">Parent first name</option><option value="show_child_first_name">Child first name</option><option value="anonymous">Anonymous</option></select></div>
<div class="col-md-6"><label class="form-label">Focus</label><select class="form-select" name="child_learning_focus"><option value="">Choose</option><option>Reading Arabic</option><option>Writing Arabic</option><option>Speaking Arabic</option><option>Letter connection</option><option>School support</option><option>Quran reading basics</option><option>Other</option></select></div>
<div class="col-12"><label class="form-label">Testimonial</label><textarea class="form-control" name="testimonial_text" rows="5"></textarea></div>
<div class="col-md-6"><label class="form-label">Audio optional</label><input class="form-control" type="file" name="audio" accept=".mp3,.wav,.m4a,.webm,audio/*"></div>
<div class="col-md-6"><label class="form-label">Video optional</label><input class="form-control" type="file" name="video" accept=".mp4,.webm,.mov,video/*"></div>
<div class="col-12"><div class="form-check border rounded-4 p-3 ps-5"><input class="form-check-input" type="checkbox" name="permission_to_publish" required><label class="form-check-label">I allow publication after review.</label></div></div>
<div class="col-12"><button class="btn btn-brand">Submit testimonial</button></div>
</div></form>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Leave a Parent Testimonial', $content);
