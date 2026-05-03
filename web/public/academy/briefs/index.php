<?php
/**
 * /academy/briefs
 * Academy partner sees only own submitted briefs.
 */

require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../backend/php/shared/AcademyPortal.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';

$user = require_role('academy_partner');
$briefs = academy_partner_briefs((int) $user['id']);

ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
  <div><p class="text-muted mb-1">All student briefs submitted by your academy account.</p></div>
  <a class="btn btn-brand" href="/academy/briefs/new">Submit new brief</a>
</div>

<?php if (!$briefs): ?>
  <div class="alert alert-light border">No briefs yet.</div>
<?php else: ?>
  <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Student</th><th>Age</th><th>Country</th><th>Level</th><th>Status</th><th>Action</th></tr></thead><tbody>
  <?php foreach ($briefs as $brief): ?>
    <tr>
      <td><strong><?= htmlspecialchars($brief['student_name'] ?: $brief['contact_name'], ENT_QUOTES, 'UTF-8') ?></strong><br><small class="text-muted"><?= htmlspecialchars($brief['created_at'], ENT_QUOTES, 'UTF-8') ?></small></td>
      <td><?= htmlspecialchars((string) ($brief['age'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
      <td><?= htmlspecialchars($brief['nationality_country'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
      <td><?= htmlspecialchars($brief['current_arabic_level'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
      <td><?= academy_status_badge($brief['status']) ?></td>
      <td><a class="btn btn-sm btn-outline-brand" href="/academy/briefs/view?id=<?= (int) $brief['id'] ?>">Open</a></td>
    </tr>
  <?php endforeach; ?>
  </tbody></table></div>
<?php endif; ?>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Academy Briefs', $content);
