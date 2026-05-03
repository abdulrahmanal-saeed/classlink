<?php
/**
 * /owner/onboarding/view?id=...
 *
 * Owner detail review page for a submitted student form. The Owner can approve,
 * reject, or keep the submission pending review.
 */

require_once __DIR__ . '/../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../backend/php/shared/Onboarding.php';
require_once __DIR__ . '/../../../../../web/components/layout/dashboard_shell.php';

$user = require_role('owner_teacher');
$formId = (int) ($_GET['id'] ?? 0);
$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        onboarding_owner_update_review(
            $formId,
            $_POST['owner_review_status'] ?? 'pending_review',
            trim($_POST['owner_review_note'] ?? ''),
            (int) $user['id']
        );
        $message = 'Onboarding review status updated.';
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
    }
}

$form = onboarding_form_detail($formId);

if (!$form) {
    http_response_code(404);
    $content = '<div class="alert alert-danger">Onboarding submission not found.</div><a class="btn btn-outline-brand" href="/owner/onboarding">Back to onboarding</a>';
    render_dashboard_shell($user, 'Onboarding Not Found', $content);
    exit;
}

$payload = json_decode($form['raw_payload'] ?? '{}', true);
$payload = is_array($payload) ? $payload : [];

ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
  <div>
    <p class="text-muted mb-1">Review the submitted student form and onboarding statuses.</p>
    <small class="text-muted">Reference: <code><?= htmlspecialchars($form['checkout_reference'] ?? '-', ENT_QUOTES, 'UTF-8') ?></code></small>
  </div>
  <a class="btn btn-outline-brand" href="/owner/onboarding">Back to pipeline</a>
</div>

<?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

<div class="row g-4">
  <div class="col-lg-7">
    <div class="status-box mb-4">
      <h2 class="h5 fw-bold">Submission summary</h2>
      <dl class="row mb-0 mt-3">
        <dt class="col-sm-4">Learner</dt><dd class="col-sm-8"><?= htmlspecialchars($form['learner_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>
        <dt class="col-sm-4">Learner type</dt><dd class="col-sm-8"><?= htmlspecialchars($form['learner_type'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>
        <dt class="col-sm-4">Plan</dt><dd class="col-sm-8"><?= htmlspecialchars($form['plan_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>
        <dt class="col-sm-4">Payment</dt><dd class="col-sm-8"><?= htmlspecialchars($form['purchase_status'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>
        <dt class="col-sm-4">Form status</dt><dd class="col-sm-8"><?= htmlspecialchars($form['student_form_status'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>
        <dt class="col-sm-4">Level check</dt><dd class="col-sm-8"><?= htmlspecialchars($form['level_check_status'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>
        <dt class="col-sm-4">Schedule</dt><dd class="col-sm-8"><?= htmlspecialchars($form['schedule_status'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>
      </dl>
    </div>

    <div class="foundation-card">
      <h2 class="h5 fw-bold mb-3">Submitted answers</h2>
      <?php if (!$payload): ?>
        <div class="alert alert-light border">No payload found.</div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-sm align-middle">
            <thead><tr><th>Field</th><th>Answer</th></tr></thead>
            <tbody>
              <?php foreach ($payload as $key => $value): ?>
                <tr>
                  <td><code><?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?></code></td>
                  <td><?= nl2br(htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8')) ?></td>
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
      <h2 class="h5 fw-bold">Owner review</h2>
      <p class="text-muted">Approve or reject this onboarding submission. This does not automatically create a student account yet.</p>
      <form method="post">
        <div class="mb-3">
          <label class="form-label">Review status</label>
          <select class="form-select" name="owner_review_status" required>
            <?php foreach (['pending_review', 'approved', 'rejected'] as $status): ?>
              <option value="<?= $status ?>" <?= ($form['owner_review_status'] ?? '') === $status ? 'selected' : '' ?>><?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Review note</label>
          <textarea class="form-control" name="owner_review_note" rows="5"><?= htmlspecialchars($form['owner_review_note'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>
        <button class="btn btn-brand" type="submit">Save review</button>
      </form>
    </div>
  </div>
</div>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Onboarding Submission Review', $content);
