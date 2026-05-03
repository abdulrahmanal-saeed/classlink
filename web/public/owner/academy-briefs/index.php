<?php
/**
 * /owner/academy-briefs
 * Owner sees all academy partner briefs.
 */

require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../backend/php/shared/AcademyPortal.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';

$user = require_role('owner_teacher');
$briefs = academy_owner_briefs();

ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
  <div>
    <p class="text-muted mb-1">Review and convert academy partner student briefs.</p>
    <small class="text-muted">Owner can see all submitted academy briefs.</small>
  </div>
</div>

<?php if (!$briefs): ?>
  <div class="alert alert-light border">No academy briefs yet.</div>
<?php else: ?>
  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead><tr><th>Student</th><th>Partner</th><th>Age</th><th>Level</th><th>Status</th><th>Converted</th><th>Action</th></tr></thead>
      <tbody>
      <?php foreach ($briefs as $brief): ?>
        <tr>
          <td><strong><?= htmlspecialchars($brief['student_name'] ?: $brief['contact_name'], ENT_QUOTES, 'UTF-8') ?></strong><br><small class="text-muted"><?= htmlspecialchars($brief['created_at'], ENT_QUOTES, 'UTF-8') ?></small></td>
          <td><?= htmlspecialchars($brief['partner_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?><br><small class="text-muted"><?= htmlspecialchars($brief['partner_email'] ?? '', ENT_QUOTES, 'UTF-8') ?></small></td>
          <td><?= htmlspecialchars((string) ($brief['age'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
          <td><?= htmlspecialchars($brief['current_arabic_level'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
          <td><?= academy_status_badge($brief['status']) ?></td>
          <td><?= htmlspecialchars((string) ($brief['converted_intake_form_id'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
          <td><a class="btn btn-sm btn-outline-brand" href="/owner/academy-briefs/view?id=<?= (int) $brief['id'] ?>">Review</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Academy Briefs', $content);
