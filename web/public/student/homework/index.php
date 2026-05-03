<?php
/**
 * /student/homework
 * Student homework list. Own data only.
 */

require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../backend/php/shared/StudentPortal.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';

$user = require_role('student');
$homeworks = student_portal_homeworks((int) $user['id']);

ob_start();
?>
<p class="text-muted">Your homework assignments and correction status.</p>
<?php if (!$homeworks): ?>
  <div class="alert alert-light border">No homework yet. When your teacher publishes homework, it will appear here.</div>
<?php else: ?>
  <div class="row g-3">
    <?php foreach ($homeworks as $hw): ?>
      <div class="col-md-6">
        <div class="status-box h-100">
          <h2 class="h5 fw-bold"><?= htmlspecialchars($hw['title'], ENT_QUOTES, 'UTF-8') ?></h2>
          <div class="text-muted small mb-2">Due: <?= htmlspecialchars($hw['due_at'] ?? '-', ENT_QUOTES, 'UTF-8') ?></div>
          <span class="badge text-bg-light border"><?= htmlspecialchars($hw['submission_status'] ?? 'not_submitted', ENT_QUOTES, 'UTF-8') ?></span>
          <p class="mt-2 mb-0"><?= nl2br(htmlspecialchars($hw['instructions'] ?? 'No instructions.', ENT_QUOTES, 'UTF-8')) ?></p>
          <?php if (!empty($hw['feedback'])): ?><div class="alert alert-info mt-3 mb-0"><?= nl2br(htmlspecialchars($hw['feedback'], ENT_QUOTES, 'UTF-8')) ?></div><?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'My Homework', $content);
