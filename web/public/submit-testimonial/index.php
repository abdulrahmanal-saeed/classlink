<?php
/** /submit-testimonial - public moderated testimonial form. */
require_once __DIR__ . '/../../../backend/php/shared/Testimonials.php';
require_once __DIR__ . '/../../../web/components/layout/public_layout.php';

$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  try {
    if (!testimonial_bool('testimonials_public_form_enabled', true)) throw new RuntimeException('Public testimonials are currently disabled.');
    testimonial_create([
      'submitter_type' => 'public',
      'source' => 'public_form',
      'rating' => $_POST['rating'] ?? null,
      'text' => $_POST['testimonial_text'] ?? '',
      'display_name' => trim($_POST['display_name'] ?? ''),
      'display_preference' => $_POST['display_preference'] ?? 'first_name',
      'learning_goal' => $_POST['learning_goal'] ?? null,
      'permission_to_publish' => !empty($_POST['permission_to_publish']),
    ], null, $_FILES);
    $message = 'Thank you! Your testimonial has been sent for review.';
  } catch (Throwable $e) { $error = $e->getMessage(); }
}

ob_start();
?>
<section class="py-5"><div class="container"><div class="foundation-card" style="max-width:760px;margin:auto;"><h1 class="hero-title mb-3">Submit a testimonial</h1><p class="text-muted">Your testimonial will be reviewed before it appears publicly.</p><?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?><?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?><form method="post" enctype="multipart/form-data"><div class="row g-3"><div class="col-md-6"><label class="form-label">Display name</label><input class="form-control" name="display_name" required></div><div class="col-md-6"><label class="form-label">Display preference</label><select class="form-select" name="display_preference"><option value="first_name">First name only</option><option value="full_name">Full name</option><option value="anonymous">Anonymous</option></select></div><div class="col-md-6"><label class="form-label">Rating</label><select class="form-select" name="rating" required><?php for($i=5;$i>=1;$i--):?><option value="<?= $i ?>"><?= $i ?> stars</option><?php endfor;?></select></div><div class="col-md-6"><label class="form-label">Learning goal optional</label><select class="form-select" name="learning_goal"><option value="">Choose</option><option>Speaking</option><option>Reading & Writing</option><option>Work Arabic</option><option>Child Arabic</option><option>Emirati dialect</option><option>Other</option></select></div><div class="col-12"><label class="form-label">Testimonial</label><textarea class="form-control" name="testimonial_text" rows="5"></textarea></div><div class="col-md-6"><label class="form-label">Audio optional</label><input class="form-control" type="file" name="audio" accept=".mp3,.wav,.m4a,.webm,audio/*"></div><div class="col-md-6"><label class="form-label">Video optional</label><input class="form-control" type="file" name="video" accept=".mp4,.webm,.mov,video/*"></div><div class="col-12"><div class="form-check border rounded-4 p-3 ps-5"><input class="form-check-input" type="checkbox" name="permission_to_publish" required><label class="form-check-label">I allow Habiba Nabil Arabic Academy to publish this testimonial on the website or social media after review.</label></div></div><div class="col-12"><button class="btn btn-brand" type="submit">Submit testimonial</button></div></div></form></div></div></section>
<?php
render_public_layout('Submit Testimonial | Habiba Nabil Arabic Academy', 'Submit a moderated testimonial for Habiba Nabil Arabic Academy.', ob_get_clean(), true);
