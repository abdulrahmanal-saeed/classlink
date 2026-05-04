<?php
/** /owner/settings/testimonials */
require_once __DIR__ . '/../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../backend/php/shared/Testimonials.php';
require_once __DIR__ . '/../../../../../web/components/layout/dashboard_shell.php';

$user = require_role('owner_teacher');
$message = null;
$error = null;
$keys = [
 'testimonials_public_form_enabled','testimonials_from_students_enabled','testimonials_from_parents_enabled','testimonials_allow_audio','testimonials_allow_video','testimonials_require_publish_permission','testimonials_require_completed_lesson','testimonials_show_on_homepage','testimonials_show_page','testimonials_max_audio_mb','testimonials_max_video_mb','testimonials_default_status'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  try {
    foreach ($keys as $key) {
      $value = $_POST[$key] ?? '0';
      if (in_array($key, ['testimonials_public_form_enabled','testimonials_from_students_enabled','testimonials_from_parents_enabled','testimonials_allow_audio','testimonials_allow_video','testimonials_require_publish_permission','testimonials_require_completed_lesson','testimonials_show_on_homepage','testimonials_show_page'], true)) $value = isset($_POST[$key]) ? '1' : '0';
      db()->prepare('UPDATE settings SET setting_value=:value, updated_by_user_id=:user WHERE setting_key=:key')->execute([':value'=>$value, ':user'=>(int)$user['id'], ':key'=>$key]);
    }
    audit_log((int)$user['id'], 'testimonial_settings_updated', 'settings', 'testimonials', []);
    $message = 'Testimonial settings updated.';
  } catch (Throwable $e) { $error = $e->getMessage(); }
}

ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4"><p class="text-muted mb-1">Control testimonial submission, media, moderation, and public display.</p><a class="btn btn-outline-brand" href="/owner/testimonials">Testimonials</a></div>
<?php if($message):?><div class="alert alert-success"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div><?php endif;?>
<?php if($error):?><div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif;?>
<form method="post" class="foundation-card"><div class="row g-3">
<?php foreach([
 'testimonials_public_form_enabled'=>'Enable public testimonial form','testimonials_from_students_enabled'=>'Enable student testimonials','testimonials_from_parents_enabled'=>'Enable parent testimonials','testimonials_allow_audio'=>'Allow audio testimonials','testimonials_allow_video'=>'Allow video testimonials','testimonials_require_publish_permission'=>'Require publish permission checkbox','testimonials_require_completed_lesson'=>'Require completed lesson before testimonial','testimonials_show_on_homepage'=>'Show testimonials on homepage','testimonials_show_page'=>'Show testimonials page'
] as $key=>$label): ?>
<div class="col-md-6"><div class="form-check border rounded-4 p-3 ps-5"><input class="form-check-input" type="checkbox" name="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>" <?= testimonial_setting($key,'1')==='1'?'checked':'' ?>><label class="form-check-label"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></label></div></div>
<?php endforeach; ?>
<div class="col-md-4"><label class="form-label">Max audio MB</label><input class="form-control" name="testimonials_max_audio_mb" value="<?= htmlspecialchars((string)testimonial_setting('testimonials_max_audio_mb','20'), ENT_QUOTES, 'UTF-8') ?>"></div>
<div class="col-md-4"><label class="form-label">Max video MB</label><input class="form-control" name="testimonials_max_video_mb" value="<?= htmlspecialchars((string)testimonial_setting('testimonials_max_video_mb','150'), ENT_QUOTES, 'UTF-8') ?>"></div>
<div class="col-md-4"><label class="form-label">Default status</label><select class="form-select" name="testimonials_default_status"><option value="pending_review">pending_review</option></select></div>
<div class="col-12"><button class="btn btn-brand">Save settings</button></div>
</div></form>
<?php
$content=ob_get_clean();
render_dashboard_shell($user,'Testimonial Settings',$content);
