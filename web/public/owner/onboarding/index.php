<?php
/**
 * /owner/onboarding
 *
 * Owner onboarding pipeline list. Shows submitted student forms and the current
 * onboarding statuses connected to each checkout/purchase.
 */

require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../backend/php/shared/Onboarding.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';

$user = require_role('owner_teacher');
$forms = onboarding_latest_forms();

ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
  <div>
    <p class="text-muted mb-1">Review new post-payment student forms and approve or reject onboarding submissions.</p>
    <small class="text-muted">Showing latest 150 submissions.</small>
  </div>
  <a class="btn btn-outline-brand" href="/owner/payments">View payments</a>
</div>

<?php if (!$forms): ?>
  <div class="alert alert-light border">No onboarding submissions yet.</div>
<?php else: ?>
  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead>
        <tr>
          <th>Submitted</th>
          <th>Learner</th>
          <th>Plan</th>
          <th>Payment</th>
          <th>Statuses</th>
          <th>Review</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($forms as $form): ?>
          <tr>
            <td class="small text-muted"><?= htmlspecialchars($form['submitted_at'] ?? $form['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
            <td>
              <strong><?= htmlspecialchars($form['learner_name'] ?? $form['checkout_name'] ?? 'Learner', ENT_QUOTES, 'UTF-8') ?></strong><br>
              <small class="text-muted"><?= htmlspecialchars($form['checkout_email'] ?? '', ENT_QUOTES, 'UTF-8') ?></small><br>
              <span class="badge text-bg-light border"><?= htmlspecialchars($form['learner_type'] ?? '-', ENT_QUOTES, 'UTF-8') ?></span>
            </td>
            <td><?= htmlspecialchars($form['plan_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
            <td><span class="badge text-bg-light border"><?= htmlspecialchars($form['purchase_status'] ?? '-', ENT_QUOTES, 'UTF-8') ?></span></td>
            <td class="small">
              Form: <?= htmlspecialchars($form['student_form_status'] ?? '-', ENT_QUOTES, 'UTF-8') ?><br>
              Level: <?= htmlspecialchars($form['level_check_status'] ?? '-', ENT_QUOTES, 'UTF-8') ?><br>
              Schedule: <?= htmlspecialchars($form['schedule_status'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
            </td>
            <td><span class="badge text-bg-light border"><?= htmlspecialchars($form['owner_review_status'] ?? $form['purchase_review_status'] ?? '-', ENT_QUOTES, 'UTF-8') ?></span></td>
            <td><a class="btn btn-sm btn-outline-brand" href="/owner/onboarding/view?id=<?= (int) $form['id'] ?>">Review</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Owner Onboarding Pipeline', $content);
