<?php
/**
 * /owner/students/credits?id={studentUserId}
 * Owner credit balance, ledger, attendance, and manual adjustment page.
 */

require_once __DIR__ . '/../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../backend/php/shared/LessonCredits.php';
require_once __DIR__ . '/../../../../../backend/php/shared/ApprovalWorkflow.php';
require_once __DIR__ . '/../../../../../web/components/layout/dashboard_shell.php';

$user = require_role('owner_teacher');
$studentId = (int) ($_GET['id'] ?? 0);
$student = approval_student_detail($studentId);
$message = null;
$error = null;

if (!$student) {
    http_response_code(404);
    $content = '<div class="alert alert-danger">Student not found.</div><a class="btn btn-outline-brand" href="/owner/students">Back</a>';
    render_dashboard_shell($user, 'Student Not Found', $content);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $action = $_POST['action'] ?? '';
        if ($action === 'manual_adjust') {
            credits_manual_adjust($studentId, (float) ($_POST['credits'] ?? 0), trim($_POST['reason'] ?? ''), (int) $user['id']);
            $message = 'Manual credit adjustment saved.';
        } elseif ($action === 'create_session') {
            credits_create_session($studentId, (int) $user['id'], trim($_POST['title'] ?? 'Arabic lesson'), $_POST['start_at'] ?? '', $_POST['end_at'] ?? '');
            $message = 'Session created as planned.';
        } elseif ($action === 'mark_attendance') {
            credits_mark_attendance((int) ($_POST['session_id'] ?? 0), $_POST['status'] ?? 'planned', (int) $user['id'], trim($_POST['notes'] ?? ''));
            $message = 'Attendance/session status updated.';
        }
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
    }
}

$summary = credits_student_summary($studentId);
$transactions = credits_transactions_for_student($studentId);
$sessions = credits_sessions_for_student($studentId);

ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
  <div>
    <p class="text-muted mb-1">Credit balance and ledger for <?= htmlspecialchars($student['display_name'], ENT_QUOTES, 'UTF-8') ?>.</p>
    <small class="text-muted">Every credit change is recorded in the ledger.</small>
  </div>
  <a class="btn btn-outline-brand" href="/owner/students/view?id=<?= (int) $studentId ?>">Back to student</a>
</div>

<?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

<div class="row g-3 mb-4">
  <div class="col-md-4"><div class="status-box h-100"><strong>Total credits</strong><br><span class="display-6"><?= htmlspecialchars((string) $summary['total'], ENT_QUOTES, 'UTF-8') ?></span></div></div>
  <div class="col-md-4"><div class="status-box h-100"><strong>Used credits</strong><br><span class="display-6"><?= htmlspecialchars((string) $summary['used'], ENT_QUOTES, 'UTF-8') ?></span></div></div>
  <div class="col-md-4"><div class="status-box h-100"><strong>Remaining credits</strong><br><span class="display-6"><?= htmlspecialchars((string) $summary['remaining'], ENT_QUOTES, 'UTF-8') ?></span></div></div>
</div>

<div class="row g-4">
  <div class="col-lg-7">
    <div class="foundation-card mb-4">
      <h2 class="h5 fw-bold mb-3">Packages</h2>
      <div class="table-responsive"><table class="table table-sm align-middle"><thead><tr><th>Package</th><th>Total</th><th>Remaining</th><th>Status</th></tr></thead><tbody>
      <?php foreach ($summary['packages'] as $package): ?>
        <tr><td><?= htmlspecialchars($package['package_name'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars((string) $package['total_credits'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars((string) $package['remaining_credits'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($package['status'], ENT_QUOTES, 'UTF-8') ?></td></tr>
      <?php endforeach; ?>
      </tbody></table></div>
    </div>

    <div class="foundation-card mb-4">
      <h2 class="h5 fw-bold mb-3">Sessions / Attendance</h2>
      <?php if (!$sessions): ?><div class="alert alert-light border">No sessions yet.</div><?php endif; ?>
      <?php foreach ($sessions as $session): ?>
        <form method="post" class="border rounded-4 p-3 mb-3">
          <input type="hidden" name="action" value="mark_attendance">
          <input type="hidden" name="session_id" value="<?= (int) $session['id'] ?>">
          <div class="row g-2 align-items-end">
            <div class="col-md-4"><strong><?= htmlspecialchars($session['title'], ENT_QUOTES, 'UTF-8') ?></strong><br><small class="text-muted"><?= htmlspecialchars($session['start_at'], ENT_QUOTES, 'UTF-8') ?></small></div>
            <div class="col-md-3"><label class="form-label">Status</label><select class="form-select" name="status"><?php foreach (['planned','confirmed','completed','canceled_on_time','canceled_late','rescheduled','no_show'] as $status): ?><option value="<?= $status ?>" <?= $session['status'] === $status ? 'selected' : '' ?>><?= $status ?></option><?php endforeach; ?></select></div>
            <div class="col-md-3"><label class="form-label">Notes</label><input class="form-control" name="notes" value="<?= htmlspecialchars($session['notes'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></div>
            <div class="col-md-2"><button class="btn btn-sm btn-brand w-100" type="submit">Save</button></div>
          </div>
        </form>
      <?php endforeach; ?>
    </div>

    <div class="foundation-card">
      <h2 class="h5 fw-bold mb-3">Credit ledger</h2>
      <div class="table-responsive"><table class="table table-sm align-middle"><thead><tr><th>Date</th><th>Type</th><th>Credits</th><th>Balance</th><th>Reason</th></tr></thead><tbody>
      <?php foreach ($transactions as $tx): ?>
        <tr><td class="small text-muted"><?= htmlspecialchars($tx['created_at'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($tx['transaction_type'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars((string) $tx['credits'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars((string) ($tx['balance_after'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($tx['reason'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td></tr>
      <?php endforeach; ?>
      </tbody></table></div>
    </div>
  </div>
  <div class="col-lg-5">
    <div class="foundation-card mb-4">
      <h2 class="h5 fw-bold">Manual credit adjustment</h2>
      <form method="post" class="row g-3">
        <input type="hidden" name="action" value="manual_adjust">
        <div class="col-12"><label class="form-label">Credits (+ add / - deduct)</label><input class="form-control" type="number" step="0.25" name="credits" required></div>
        <div class="col-12"><label class="form-label">Reason</label><textarea class="form-control" name="reason" rows="3" required></textarea></div>
        <div class="col-12"><button class="btn btn-brand" type="submit">Apply adjustment</button></div>
      </form>
    </div>

    <div class="foundation-card">
      <h2 class="h5 fw-bold">Create planned session</h2>
      <form method="post" class="row g-3">
        <input type="hidden" name="action" value="create_session">
        <div class="col-12"><label class="form-label">Title</label><input class="form-control" name="title" value="Arabic lesson" required></div>
        <div class="col-md-6"><label class="form-label">Start</label><input class="form-control" type="datetime-local" name="start_at" required></div>
        <div class="col-md-6"><label class="form-label">End</label><input class="form-control" type="datetime-local" name="end_at" required></div>
        <div class="col-12"><button class="btn btn-outline-brand" type="submit">Create session</button></div>
      </form>
    </div>
  </div>
</div>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Student Credits', $content);
