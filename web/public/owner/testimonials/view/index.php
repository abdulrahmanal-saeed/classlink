<?php
/** /owner/testimonials/view?id=... */
require_once __DIR__ . '/../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../backend/php/shared/Testimonials.php';
require_once __DIR__ . '/../../../../../web/components/layout/dashboard_shell.php';

$user = require_role('owner_teacher');
$id = (int)($_GET['id'] ?? 0);
$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  try {
    $action = $_POST['action'] ?? 'save';
    if ($action === 'save') testimonial_update_owner((int)$user['id'], $id, $_POST);
    if (in_array($action, ['approved','rejected','archived','pending_review'], true)) testimonial_set_status((int)$user['id'], $id, $action);
    $message = 'Testimonial updated.';
  } catch (Throwable $e) { $error = $e->getMessage(); }
}

$row = testimonial_find($id);
if (!$row) { http_response_code(404); render_dashboard_shell($user, 'Testimonial Not Found', '<div class="alert alert-danger">Not found.</div>'); exit; }
$media = testimonial_media($id);

ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4"><div><p class="text-muted mb-1">Review testimonial before public display.</p></div><a class="btn btn-outline-brand" href="/owner/testimonials">Back</a></div>
<?php if($message):?><div class="alert alert-success"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div><?php endif;?>
<?php if($error):?><div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif;?>
<div class="row g-4">
  <div class="col-lg-7"><div class="foundation-card h-100"><h2 class="h5 fw-bold">Submission</h2><p><strong>Status:</strong> <span class="badge text-bg-light border"><?= htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8') ?></span></p><p><strong>Submitter:</strong> <?= htmlspecialchars($row['submitter_type'], ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars($row['source'], ENT_QUOTES, 'UTF-8') ?></p><p><strong>Rating:</strong> <?= str_repeat('★', (int)$row['rating']) ?></p><p><strong>Permission:</strong> <?= (int)$row['permission_to_publish'] ? 'Yes' : 'No' ?></p><hr><p><?= nl2br(htmlspecialchars($row['testimonial_text'] ?? '', ENT_QUOTES, 'UTF-8')) ?></p><?php if($row['audio_url']):?><h3 class="h6">Audio</h3><audio controls preload="metadata" src="<?= htmlspecialchars($row['audio_url'], ENT_QUOTES, 'UTF-8') ?>" class="w-100"></audio><?php endif;?><?php if($row['video_url']):?><h3 class="h6 mt-3">Video</h3><video controls preload="metadata" src="<?= htmlspecialchars($row['video_url'], ENT_QUOTES, 'UTF-8') ?>" class="w-100 rounded-4"></video><?php endif;?></div></div>
  <div class="col-lg-5"><form method="post" class="foundation-card h-100"><input type="hidden" name="action" value="save"><h2 class="h5 fw-bold">Public display controls</h2><div class="mb-3"><label class="form-label">Public display name</label><input class="form-control" name="display_name" value="<?= htmlspecialchars($row['display_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></div><div class="mb-3"><label class="form-label">Public text override</label><textarea class="form-control" name="public_text_override" rows="4"><?= htmlspecialchars($row['public_text_override'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea></div><div class="mb-3"><label class="form-label">Internal notes</label><textarea class="form-control" name="owner_notes" rows="4"><?= htmlspecialchars($row['owner_notes'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea></div><div class="row g-2"><div class="col-6"><div class="form-check"><input class="form-check-input" type="checkbox" name="featured" <?= $row['featured']?'checked':'' ?>><label class="form-check-label">Featured</label></div></div><div class="col-6"><div class="form-check"><input class="form-check-input" type="checkbox" name="show_on_homepage" <?= $row['show_on_homepage']?'checked':'' ?>><label class="form-check-label">Homepage</label></div></div><div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" name="show_on_testimonials_page" <?= $row['show_on_testimonials_page']?'checked':'' ?>><label class="form-check-label">Testimonials page</label></div></div></div><div class="mb-3 mt-3"><label class="form-label">Sort order</label><input class="form-control" name="sort_order" value="<?= (int)$row['sort_order'] ?>"></div><button class="btn btn-brand">Save controls</button></form></div>
</div>
<div class="foundation-card mt-4"><h2 class="h5 fw-bold">Moderation actions</h2><div class="d-flex gap-2 flex-wrap"><form method="post"><input type="hidden" name="action" value="approved"><button class="btn btn-success">Approve</button></form><form method="post"><input type="hidden" name="action" value="rejected"><button class="btn btn-outline-danger">Reject</button></form><form method="post"><input type="hidden" name="action" value="archived"><button class="btn btn-outline-secondary">Archive</button></form><form method="post"><input type="hidden" name="action" value="pending_review"><button class="btn btn-outline-brand">Back to pending</button></form></div></div>
<?php
$content=ob_get_clean();
render_dashboard_shell($user,'Review Testimonial',$content);
