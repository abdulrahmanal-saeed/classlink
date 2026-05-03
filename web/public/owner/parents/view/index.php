<?php
/**
 * /owner/parents/view?id=...
 * Owner parent account detail with linked children.
 */

require_once __DIR__ . '/../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../backend/php/shared/ApprovalWorkflow.php';
require_once __DIR__ . '/../../../../../web/components/layout/dashboard_shell.php';

$user = require_role('owner_teacher');
$parentId = (int) ($_GET['id'] ?? 0);
$parent = approval_parent_detail($parentId);

if (!$parent) {
    http_response_code(404);
    $content = '<div class="alert alert-danger">Parent not found.</div><a class="btn btn-outline-brand" href="/owner/parents">Back</a>';
    render_dashboard_shell($user, 'Parent Not Found', $content);
    exit;
}

$logs = approval_login_logs_for_user($parentId);

ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
  <div>
    <p class="text-muted mb-1">Parent account and linked child profiles.</p>
    <small class="text-muted">User ID: <?= (int) $parentId ?></small>
  </div>
  <a class="btn btn-outline-brand" href="/owner/parents">Back to parents</a>
</div>

<div class="row g-4">
  <div class="col-lg-7">
    <div class="status-box mb-4">
      <h2 class="h5 fw-bold">Parent profile</h2>
      <dl class="row mb-0 mt-3">
        <dt class="col-sm-4">Name</dt><dd class="col-sm-8"><?= htmlspecialchars($parent['display_name'], ENT_QUOTES, 'UTF-8') ?></dd>
        <dt class="col-sm-4">Email</dt><dd class="col-sm-8"><?= htmlspecialchars($parent['email'], ENT_QUOTES, 'UTF-8') ?></dd>
        <dt class="col-sm-4">Phone</dt><dd class="col-sm-8"><?= htmlspecialchars($parent['phone'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>
        <dt class="col-sm-4">Contact</dt><dd class="col-sm-8"><?= htmlspecialchars($parent['preferred_contact_method'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>
        <dt class="col-sm-4">Status</dt><dd class="col-sm-8"><?= htmlspecialchars($parent['status'], ENT_QUOTES, 'UTF-8') ?></dd>
      </dl>
    </div>

    <div class="foundation-card">
      <h2 class="h5 fw-bold mb-3">Linked children</h2>
      <?php if (empty($parent['children'])): ?>
        <div class="alert alert-light border">No linked children.</div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-sm align-middle">
            <thead><tr><th>Child</th><th>Email</th><th>Level</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
              <?php foreach ($parent['children'] as $child): ?>
                <tr>
                  <td><?= htmlspecialchars($child['child_display_name'] ?? $child['child_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars($child['child_email'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars($child['current_level'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars($child['status'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?php if (!empty($child['child_user_id'])): ?><a class="btn btn-sm btn-outline-brand" href="/owner/students/view?id=<?= (int) $child['child_user_id'] ?>">Open</a><?php endif; ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
  <div class="col-lg-5">
    <div class="foundation-card h-100">
      <h2 class="h5 fw-bold">Login details log</h2>
      <?php if (!$logs): ?>
        <div class="alert alert-light border">No login detail logs found.</div>
      <?php else: ?>
        <?php foreach ($logs as $log): ?>
          <div class="border rounded-4 p-3 mb-3">
            <div class="fw-bold"><?= htmlspecialchars($log['subject'], ENT_QUOTES, 'UTF-8') ?></div>
            <div class="small text-muted mb-2"><?= htmlspecialchars($log['created_at'], ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars($log['recipient'], ENT_QUOTES, 'UTF-8') ?></div>
            <pre class="small mb-2" style="white-space:pre-wrap;"><?= htmlspecialchars($log['message_body'], ENT_QUOTES, 'UTF-8') ?></pre>
            <div class="badge text-bg-warning">Temporary password: <?= htmlspecialchars($log['temporary_password'] ?? '-', ENT_QUOTES, 'UTF-8') ?></div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Parent Detail', $content);
