<?php
/**
 * /student/session-notes
 * Student session notes. Own data only.
 */

require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../backend/php/shared/StudentPortal.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';

$user = require_role('student');
$notes = student_portal_session_notes((int) $user['id']);

ob_start();
?>
<p class="text-muted">Notes shared after your lessons.</p>
<?php if (!$notes): ?>
  <div class="alert alert-light border">No session notes yet.</div>
<?php else: ?>
  <?php foreach ($notes as $note): ?>
    <div class="foundation-card mb-3">
      <h2 class="h5 fw-bold"><?= htmlspecialchars($note['title'], ENT_QUOTES, 'UTF-8') ?></h2>
      <div class="small text-muted mb-2"><?= htmlspecialchars($note['start_at'], ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars($note['status'], ENT_QUOTES, 'UTF-8') ?></div>
      <div><?= nl2br(htmlspecialchars($note['notes'], ENT_QUOTES, 'UTF-8')) ?></div>
    </div>
  <?php endforeach; ?>
<?php endif; ?>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Session Notes', $content);
