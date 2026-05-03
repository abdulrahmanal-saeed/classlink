<?php
/**
 * /academy/briefs/new
 * Academy partner submits a student brief.
 */

require_once __DIR__ . '/../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../backend/php/shared/AcademyPortal.php';
require_once __DIR__ . '/../../../../../web/components/layout/dashboard_shell.php';

$user = require_role('academy_partner');
$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $id = academy_submit_brief((int) $user['id'], academy_brief_payload_from_post($_POST));
        header('Location: /academy/briefs/view?id=' . $id);
        exit;
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
    }
}

ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
  <div><p class="text-muted mb-1">Submit a new student brief for Owner review.</p></div>
  <a class="btn btn-outline-brand" href="/academy/briefs">Back to briefs</a>
</div>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

<form method="post" class="foundation-card">
  <div class="row g-3">
    <div class="col-md-6"><label class="form-label">Student name</label><input class="form-control" name="student_name" required></div>
    <div class="col-md-3"><label class="form-label">Age</label><input class="form-control" type="number" name="age" min="1" max="100"></div>
    <div class="col-md-3"><label class="form-label">Nationality/country</label><input class="form-control" name="nationality_country"></div>
    <div class="col-md-6"><label class="form-label">Current Arabic level</label><input class="form-control" name="current_arabic_level" placeholder="A0, A1, can speak but cannot read..."></div>
    <div class="col-md-6"><label class="form-label">Preferred schedule if known</label><input class="form-control" name="preferred_schedule"></div>
    <div class="col-12"><label class="form-label">Goal</label><textarea class="form-control" name="goal" rows="3"></textarea></div>
    <div class="col-md-6"><label class="form-label">Speaking ability</label><textarea class="form-control" name="speaking_ability" rows="4"></textarea></div>
    <div class="col-md-6"><label class="form-label">Reading/writing ability</label><textarea class="form-control" name="reading_writing_ability" rows="4"></textarea></div>
    <div class="col-12"><label class="form-label">Parent/contact info if child</label><textarea class="form-control" name="parent_contact_info" rows="3"></textarea></div>
    <div class="col-12"><label class="form-label">Notes from academy</label><textarea class="form-control" name="notes_from_academy" rows="4"></textarea></div>
    <div class="col-12"><button class="btn btn-brand" type="submit">Submit brief</button></div>
  </div>
</form>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Submit Academy Brief', $content);
