<?php
/**
 * /academy/briefs/view?id={briefId}
 * Academy partner can view only own brief.
 */

require_once __DIR__ . '/../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../backend/php/shared/AcademyPortal.php';
require_once __DIR__ . '/../../../../../web/components/layout/dashboard_shell.php';

$user = require_role('academy_partner');
$briefId = (int) ($_GET['id'] ?? 0);
$brief = academy_partner_brief_detail((int) $user['id'], $briefId);

if (!$brief) {
    http_response_code(404);
    $content = '<div class="alert alert-danger">Brief not found or not owned by this academy account.</div><a class="btn btn-outline-brand" href="/academy/briefs">Back</a>';
    render_dashboard_shell($user, 'Brief Not Found', $content);
    exit;
}

ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
  <div>
    <p class="text-muted mb-1">Brief status and submitted details.</p>
    <?= academy_status_badge($brief['status']) ?>
  </div>
  <a class="btn btn-outline-brand" href="/academy/briefs">Back to briefs</a>
</div>

<div class="foundation-card">
  <dl class="row mb-0">
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
    <dt class="col-sm-4">Owner note</dt><dd class="col-sm-8"><?= nl2br(htmlspecialchars($brief['internal_notes'] ?? '-', ENT_QUOTES, 'UTF-8')) ?></dd>
    <dt class="col-sm-4">Converted intake</dt><dd class="col-sm-8"><?= htmlspecialchars((string) ($brief['converted_intake_form_id'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></dd>
  </dl>
</div>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Academy Brief Detail', $content);
