<?php
/** /student/testimonial - student testimonial submission. */
require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../backend/php/shared/Testimonials.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';

$user = require_role('student');
$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!testimonial_bool('testimonials_from_students_enabled', true)) throw new RuntimeException('Student testimonials are currently disabled.');
        $displayPreference = $_POST['display_preference'] ?? 'first_name';
        $displayName = testimonial_display_name($user, $displayPreference);
        testimonial_create([
            'submitter_type' => 'student',
            'student_user_id' => (int)$user['id'],
            'source' => 'student_dashboard',
            'rating' => $_POST['rating'] ?? null,
            'text' => $_POST['testimonial_text'] ?? '',
            'display_name' => $displayName,
            'display_preference' => $displayPreference,
            'level' => $_POST['level'] ?? null,
            'learning_goal' => $_POST['learning_goal'] ?? null,
            'permission_to_publish' => !empty($_POST['permission_to_publish']),
        ], $user, $_FILES);
        $message = 'Thank you! Your testimonial has been sent for review.';
    } catch (Throwable $e) { $error = $e->getMessage(); }
}

ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
  <div><p class="text-muted mb-1">Tell us about your Arabic learning experience. Nothing is published before review.</p></div>
  <a class="btn btn-outline-brand" href="/student/dashboard">Back to dashboard</a>
</div>
<?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
<form method="post" enctype="multipart/form-data" class="foundation-card">
  <div class="row g-3">
    <div class="col-md-4"><label class="form-label">Rating</label><select class="form-select" name="rating" required><option value="">Choose</option><?php for($i=5;$i>=1;$i--):?><option value="<?= $i ?>"><?= $i ?> stars</option><?php endfor;?></select></div>
    <div class="col-md-4"><label class="form-label">Display name</label><select class="form-select" name="display_preference"><option value="full_name">Full name</option><option value="first_name" selected>First name only</option><option value="anonymous">Anonymous</option></select></div>
    <div class="col-md-4"><label class="form-label">Level optional</label><select class="form-select" name="level"><option value="">Not sure</option><?php foreach(['A0','A1','A2','B1','B2','C1','C2'] as $l):?><option value="<?= $l ?>"><?= $l ?></option><?php endforeach;?></select></div>
    <div class="col-md-6"><label class="form-label">Learning goal optional</label><select class="form-select" name="learning_goal"><option value="">Choose</option><option>Speaking</option><option>Reading & Writing</option><option>Work Arabic</option><option>Child Arabic</option><option>Emirati dialect</option><option>Other</option></select></div>
    <div class="col-12"><label class="form-label">Text testimonial</label><textarea class="form-control" name="testimonial_text" rows="5" placeholder="What changed for you while learning Arabic?"></textarea></div>
    <?php if (testimonial_bool('testimonials_allow_audio', true)): ?><div class="col-md-6"><label class="form-label">Audio testimonial optional</label><input class="form-control" type="file" name="audio" accept=".mp3,.wav,.m4a,.webm,audio/*"><small class="text-muted">Allowed: mp3, wav, m4a, webm.</small></div><?php endif; ?>
    <?php if (testimonial_bool('testimonials_allow_video', true)): ?><div class="col-md-6"><label class="form-label">Video testimonial optional</label><input class="form-control" type="file" name="video" accept=".mp4,.webm,.mov,video/*"><small class="text-muted">Allowed: mp4, webm, mov.</small></div><?php endif; ?>
    <div class="col-12"><div class="form-check border rounded-4 p-3 ps-5"><input class="form-check-input" type="checkbox" name="permission_to_publish" required><label class="form-check-label">I allow Habiba Nabil Arabic Academy to publish this testimonial on the website or social media after review.</label></div></div>
    <div class="col-12"><button class="btn btn-brand">Submit testimonial</button></div>
  </div>
</form>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Leave a Testimonial', $content);
