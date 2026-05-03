<?php
/**
 * /parent/child/session-notes?id={childUserId}
 * Parent child teacher/session notes. Linked child only.
 */

require_once __DIR__ . '/../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../backend/php/shared/ParentPortal.php';
require_once __DIR__ . '/../../../../../web/components/layout/dashboard_shell.php';

$user = require_role('parent');
$childId = (int) ($_GET['id'] ?? 0);
$profile = parent_portal_child_profile((int) $user['id'], $childId);
$notes = parent_portal_session_notes((int) $user['id'], $childId);

ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
  <div><p class="text-muted mb-1">Teacher notes for <?= htmlspecialchars($profile['display_name'] ?? 'Child learner', ENT_QUOTES, 'UTF-8') ?>.</p></div>
  <a class="btn btn-outline-brand" href="/parent/child/view?id=<?= (int) $childId ?>">Back to child dashboard</a>
</div>

<?php if (!$notes): ?>
  <div class="alert alert-light border">No teacher notes yet.</div>
<?php else: ?>
  <?php foreach ($notes as $note): ?>
    <div class="foundation-card mb-3"><h2 class="h5 fw-bold"><?= htmlspecialchars($note['title'], ENT_QUOTES, 'UTF-8') ?></h2><div class="small text-muted mb-2"><?= htmlspecialchars($note['start_at'], ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars($note['status'], ENT_QUOTES, 'UTF-8') ?></div><div><?= nl2br(htmlspecialchars($note['notes'], ENT_QUOTES, 'UTF-8')) ?></div></div>
  <?php endforeach; ?>
<?php endif; ?>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Child Session Notes', $content);
