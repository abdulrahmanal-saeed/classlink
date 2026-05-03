<?php
/**
 * /owner/audit-log
 *
 * Owner-only audit log viewer. This gives early visibility into important
 * platform actions like login/logout and settings changes.
 */

require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../backend/php/config/db.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';

$user = require_role('owner_teacher');

$statement = db()->query(
    'SELECT audit_logs.*, users.email, users.display_name
     FROM audit_logs
     LEFT JOIN users ON users.id = audit_logs.user_id
     ORDER BY audit_logs.created_at DESC
     LIMIT 100'
);
$logs = $statement->fetchAll();

ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
  <div>
    <p class="text-muted mb-1">Review the latest important platform actions.</p>
    <small class="text-muted">Showing the latest 100 audit records.</small>
  </div>
  <a class="btn btn-outline-brand" href="/owner/settings">Back to settings</a>
</div>

<?php if (!$logs): ?>
  <div class="alert alert-light border">No audit records yet.</div>
<?php else: ?>
  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead>
        <tr>
          <th>Time</th>
          <th>User</th>
          <th>Action</th>
          <th>Entity</th>
          <th>Metadata</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($logs as $log): ?>
          <tr>
            <td class="small text-muted"><?= htmlspecialchars($log['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
            <td>
              <div class="fw-semibold"><?= htmlspecialchars($log['display_name'] ?? 'System', ENT_QUOTES, 'UTF-8') ?></div>
              <small class="text-muted"><?= htmlspecialchars($log['email'] ?? 'system', ENT_QUOTES, 'UTF-8') ?></small>
            </td>
            <td><span class="badge text-bg-light border"><?= htmlspecialchars($log['action'], ENT_QUOTES, 'UTF-8') ?></span></td>
            <td class="small">
              <?= htmlspecialchars($log['entity_type'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
              <?php if (!empty($log['entity_id'])): ?>
                <br><code><?= htmlspecialchars($log['entity_id'], ENT_QUOTES, 'UTF-8') ?></code>
              <?php endif; ?>
            </td>
            <td><pre class="small mb-0" style="white-space: pre-wrap;"><?= htmlspecialchars($log['metadata'] ?? '', ENT_QUOTES, 'UTF-8') ?></pre></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Audit Log', $content);
