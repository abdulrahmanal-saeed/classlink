<?php
/**
 * /student/scenarios
 * Student speaking scenarios. Own data only.
 */

require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../backend/php/shared/StudentPortal.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';

$user = require_role('student');
$scenarios = student_portal_scenarios((int) $user['id']);

ob_start();
?>
<p class="text-muted">Your speaking scenarios and feedback.</p>
<?php if (!$scenarios): ?>
  <div class="alert alert-light border">No scenarios yet. Speaking practice will appear here when published.</div>
<?php else: ?>
  <div class="row g-3">
    <?php foreach ($scenarios as $scenario): ?>
      <div class="col-md-6">
        <div class="status-box h-100">
          <h2 class="h5 fw-bold"><?= htmlspecialchars($scenario['title'], ENT_QUOTES, 'UTF-8') ?></h2>
          <div class="text-muted small mb-2">Status: <?= htmlspecialchars($scenario['submitted_at'] ? 'submitted' : $scenario['status'], ENT_QUOTES, 'UTF-8') ?></div>
          <p><strong>Situation:</strong><br><?= nl2br(htmlspecialchars($scenario['situation'], ENT_QUOTES, 'UTF-8')) ?></p>
          <p><strong>Task:</strong><br><?= nl2br(htmlspecialchars($scenario['prompt'], ENT_QUOTES, 'UTF-8')) ?></p>
          <?php if (!empty($scenario['keywords'])): ?><div class="small text-muted">Keywords: <?= htmlspecialchars($scenario['keywords'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
          <?php if (!empty($scenario['feedback'])): ?><div class="alert alert-info mt-3 mb-0"><?= nl2br(htmlspecialchars($scenario['feedback'], ENT_QUOTES, 'UTF-8')) ?></div><?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'My Scenarios', $content);
