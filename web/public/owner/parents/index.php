<?php
/**
 * /owner/parents
 * Owner parent accounts list created after child learner approval.
 */

require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../backend/php/shared/ApprovalWorkflow.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';

$user = require_role('owner_teacher');
$parents = approval_parents();

ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
  <div>
    <p class="text-muted mb-1">Approved parent accounts.</p>
    <small class="text-muted">Parent accounts are created for child learners after Owner approval.</small>
  </div>
  <a class="btn btn-outline-brand" href="/owner/onboarding">Onboarding pipeline</a>
</div>

<?php if (!$parents): ?>
  <div class="alert alert-light border">No parent accounts created yet.</div>
<?php else: ?>
  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead><tr><th>Parent</th><th>Phone</th><th>Contact</th><th>Children</th><th>Status</th><th>Action</th></tr></thead>
      <tbody>
        <?php foreach ($parents as $parent): ?>
          <tr>
            <td><strong><?= htmlspecialchars($parent['display_name'], ENT_QUOTES, 'UTF-8') ?></strong><br><small class="text-muted"><?= htmlspecialchars($parent['email'], ENT_QUOTES, 'UTF-8') ?></small></td>
            <td><?= htmlspecialchars($parent['phone'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($parent['preferred_contact_method'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= (int) $parent['child_count'] ?></td>
            <td><span class="badge text-bg-light border"><?= htmlspecialchars($parent['status'], ENT_QUOTES, 'UTF-8') ?></span></td>
            <td><a class="btn btn-sm btn-outline-brand" href="/owner/parents/view?id=<?= (int) $parent['id'] ?>">View</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Owner Parents', $content);
