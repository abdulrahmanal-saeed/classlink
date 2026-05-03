<?php
/**
 * /owner/free-level-test/attempts
 * Owner/Teacher list for free quick checks and full placement tests.
 */

require_once __DIR__ . '/../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../backend/php/shared/FreeLevelTest.php';
require_once __DIR__ . '/../../../../../web/components/layout/dashboard_shell.php';

$user = require_role('owner_teacher');
flt_seed_defaults();

$type = $_GET['type'] ?? '';
$status = $_GET['status'] ?? '';
$params = [];
$where = [];

if (in_array($type, ['quick', 'full'], true)) {
    $where[] = 'a.test_type = :type';
    $params[':type'] = $type;
}
if (in_array($status, ['started', 'submitted', 'reviewed', 'abandoned'], true)) {
    $where[] = 'a.status = :status';
    $params[':status'] = $status;
}
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$stmt = db()->prepare("SELECT a.*, p.full_name, p.whatsapp AS applicant_whatsapp, p.email, p.applicant_type
    FROM free_level_test_attempts a
    LEFT JOIN free_level_test_applicants p ON p.id = a.applicant_id
    {$whereSql}
    ORDER BY a.created_at DESC
    LIMIT 200");
$stmt->execute($params);
$attempts = $stmt->fetchAll();

ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
  <div>
    <p class="text-muted mb-1">Review free public Arabic level tests. These are lead/placement tests only, not paid onboarding tests.</p>
    <small class="text-muted">Showing latest 200 attempts.</small>
  </div>
  <a class="btn btn-outline-brand" href="/owner/free-level-test/settings">Settings</a>
</div>

<div class="d-flex flex-wrap gap-2 mb-4">
  <a class="btn btn-sm <?= $type === '' ? 'btn-brand' : 'btn-outline-brand' ?>" href="/owner/free-level-test/attempts">All</a>
  <a class="btn btn-sm <?= $type === 'quick' ? 'btn-brand' : 'btn-outline-brand' ?>" href="/owner/free-level-test/attempts?type=quick">Quick</a>
  <a class="btn btn-sm <?= $type === 'full' ? 'btn-brand' : 'btn-outline-brand' ?>" href="/owner/free-level-test/attempts?type=full">Full</a>
</div>

<?php if (!$attempts): ?>
  <div class="alert alert-light border">No free level test attempts yet.</div>
<?php else: ?>
  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead><tr><th>Created</th><th>Applicant</th><th>Type</th><th>Status</th><th>Scores</th><th>Level</th><th>Action</th></tr></thead>
      <tbody>
      <?php foreach ($attempts as $attempt): ?>
        <tr>
          <td class="small text-muted"><?= htmlspecialchars($attempt['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
          <td>
            <strong><?= htmlspecialchars($attempt['full_name'] ?? 'Anonymous quick check', ENT_QUOTES, 'UTF-8') ?></strong><br>
            <small class="text-muted"><?= htmlspecialchars($attempt['applicant_whatsapp'] ?? $attempt['whatsapp'] ?? '', ENT_QUOTES, 'UTF-8') ?></small>
          </td>
          <td><span class="badge text-bg-light border"><?= htmlspecialchars($attempt['test_type'], ENT_QUOTES, 'UTF-8') ?></span></td>
          <td><span class="badge text-bg-light border"><?= htmlspecialchars($attempt['status'], ENT_QUOTES, 'UTF-8') ?></span></td>
          <td class="small">L: <?= htmlspecialchars((string) ($attempt['listening_score'] ?? '-'), ENT_QUOTES, 'UTF-8') ?> / R: <?= htmlspecialchars((string) ($attempt['reading_score'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
          <td><?= htmlspecialchars($attempt['final_level'] ?: ($attempt['auto_estimated_level'] ?: ($attempt['preliminary_level'] ?: '-')), ENT_QUOTES, 'UTF-8') ?></td>
          <td><a class="btn btn-sm btn-outline-brand" href="/owner/free-level-test/attempts/view?id=<?= (int) $attempt['id'] ?>">Review</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Free Level Test Attempts', $content);
