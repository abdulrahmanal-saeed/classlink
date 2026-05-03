<?php
/**
 * /owner/academy-briefs/view?id={briefId}
 * Owner reviews academy brief and can convert it to onboarding.
 */

require_once __DIR__ . '/../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../backend/php/shared/AcademyPortal.php';
require_once __DIR__ . '/../../../../../web/components/layout/dashboard_shell.php';

$user = require_role('owner_teacher');
$briefId = (int) ($_GET['id'] ?? 0);
$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $action = $_POST['action'] ?? '';
        if ($action === 'review') {
            academy_update_review($briefId, $_POST['status'] ?? 'under_review', trim($_POST['internal_notes'] ?? ''), (int) $user['id']);
            $message = 'Brief review updated.';
        } elseif ($action === 'convert') {
            $intakeId = academy_convert_to_onboarding($briefId, (int) $user['id'], $_POST['student_user_id'] !== '' ? (int) $_POST['student_user_id'] : null);
            $message = 'Brief converted to onboarding intake #' . $intakeId . '.';
        }
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
    }
}

$brief = academy_owner_brief_detail($briefId);
if (!$brief) {
    http_response_code(404);
    $content = '<div class="alert alert-danger">Academy brief not found.</div><a class="btn btn-outline-brand" href="/owner/academy-briefs">Back</a>';
    render_dashboard_shell($user, 'Brief Not Found', $content);
    exit;
}

ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
  <div>
    <p class="text-muted mb-1">Review academy partner brief and convert it into onboarding when ready.</p>
    <?= academy_status_badge($brief['status']) ?>
  </div>
  <a class="btn btn-outline-brand" href="/owner/academy-briefs">Back to briefs</a>
</div>

<?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

<div class="row g-4">
  <div class="col-lg-7">
    <div class="foundation-card h-100">
      <h2 class="h5 fw-bold">Brief details</h2>
      <dl class="row mb-0 mt-3">
        <dt class="col-sm-4">Partner</dt><dd class="col-sm-8"><?= htmlspecialchars($brief['partner_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?><br><small class="text-muted"><?= htmlspecialchars($brief['partner_email'] ?? '', ENT_QUOTES, 'UTF-8') ?></small></dd>
        <dt class="col-sm-4">Student name</dt><dd class="col-sm-8"><?= htmlspecialchars($brief['student_name'] ?: $brief['contact_name'], ENT_QUOTES, 'UTF-8') ?></dd>
        <dt class="col-sm-4">Age</dt><dd class="col-sm-8"><?= htmlspecialchars((string) ($brief['age'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></dd>
        <dt class="col-sm-4">Country</dt><dd class="col-sm-8"><?= htmlspecialchars($brief['nationality_country'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>
        <dt class="col-sm-4">Current level</dt><dd class="col-sm-8"><?= htmlspecialchars($brief['current_arabic_level'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>
        <dt class="col-sm-4">Goal</dt><dd class="col-sm-8"><?= nl2br(htmlspecialchars($brief['goal'] ?: $brief['goals'] ?: '-', ENT_QUOTES, 'UTF-8')) ?></dd>
        <dt class="col-sm-4">Speaking ability</dt><dd class="col-sm-8"><?= nl2br(htmlspecialchars($brief['speaking_ability'] ?? '-', ENT_QUOTES, 'UTF-8')) ?></dd>
        <dt class="col-sm-4">Reading/writing ability</dt><dd class="col-sm-8"><?= nl2br(htmlspecialchars($brief['reading_writing_ability'] ?? '-', ENT_QUOTES, 'UTF-8')) ?></dd>
        <dt class="col-sm-4">Parent/contact info</dt><dd class="col-sm-8"><?= nl2br(htmlspecialchars($brief['parent_contact_info'] ?? '-', ENT_QUOTES, 'UTF-8')) ?></dd>
        <dt class="col-sm-4">Preferred schedule</dt><dd class="col-sm-8"><?= nl2br(htmlspecialchars($brief['preferred_schedule'] ?? '-', ENT_QUOTES, 'UTF-8')) ?></dd>
        <dt class="col-sm-4">Academy notes</dt><dd class="col-sm-8"><?= nl2br(htmlspecialchars($brief['notes_from_academy'] ?? '-', ENT_QUOTES, 'UTF-8')) ?></dd>
      </dl>
    </div>
  </div>
  <div class="col-lg-5">
    <div class="foundation-card mb-4">
      <h2 class="h5 fw-bold">Owner review</h2>
      <form method="post" class="row g-3">
        <input type="hidden" name="action" value="review">
        <div class="col-12"><label class="form-label">Status</label><select class="form-select" name="status"><?php foreach (['submitted','under_review','rejected','converted_to_student'] as $status): ?><option value="<?= $status ?>" <?= $brief['status'] === $status ? 'selected' : '' ?>><?= $status ?></option><?php endforeach; ?></select></div>
        <div class="col-12"><label class="form-label">Internal notes</label><textarea class="form-control" name="internal_notes" rows="5"><?= htmlspecialchars($brief['internal_notes'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea></div>
        <div class="col-12"><button class="btn btn-outline-brand" type="submit">Save review</button></div>
      </form>
    </div>

    <div class="foundation-card">
      <h2 class="h5 fw-bold">Convert to onboarding/student</h2>
      <?php if (!empty($brief['converted_intake_form_id'])): ?>
        <div class="alert alert-success">Already converted to intake #<?= (int) $brief['converted_intake_form_id'] ?>.</div>
        <a class="btn btn-sm btn-outline-brand" href="/owner/onboarding/view?id=<?= (int) $brief['converted_intake_form_id'] ?>">Open onboarding</a>
      <?php else: ?>
        <p class="text-muted">This creates a submitted onboarding intake from the brief. You can optionally link an existing student user ID.</p>
        <form method="post" class="row g-3">
          <input type="hidden" name="action" value="convert">
          <div class="col-12"><label class="form-label">Existing student user ID optional</label><input class="form-control" type="number" name="student_user_id" placeholder="Leave empty to create onboarding only"></div>
          <div class="col-12"><button class="btn btn-brand" type="submit">Convert brief</button></div>
        </form>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Review Academy Brief', $content);
