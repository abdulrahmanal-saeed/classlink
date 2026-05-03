<?php
/**
 * /parent/contact
 * Parent teacher/contact shortcuts.
 */

require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../backend/php/shared/ParentPortal.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';

$user = require_role('parent');
$children = parent_portal_children((int) $user['id']);
$whatsapp = '+971000000000';
$email = 'hello@mshabibanabil.com';

ob_start();
?>
<p class="text-muted">Use these shortcuts to contact the teacher/admin about your child lessons.</p>
<div class="row g-3">
  <div class="col-md-6"><div class="status-box h-100"><h2 class="h5 fw-bold">WhatsApp</h2><p class="text-muted">Fastest way for schedule, homework, and urgent updates.</p><a class="btn btn-brand" target="_blank" href="https://wa.me/<?= preg_replace('/\D+/', '', $whatsapp) ?>">Open WhatsApp</a></div></div>
  <div class="col-md-6"><div class="status-box h-100"><h2 class="h5 fw-bold">Email</h2><p class="text-muted">Use email for longer questions or documents.</p><a class="btn btn-outline-brand" href="mailto:<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>">Send email</a></div></div>
</div>
<div class="foundation-card mt-4">
  <h2 class="h5 fw-bold">Linked children</h2>
  <?php if (!$children): ?><p class="text-muted">No linked child yet.</p><?php else: ?><ul class="mb-0"><?php foreach ($children as $child): ?><li><?= htmlspecialchars($child['child_name'] ?? 'Child learner', ENT_QUOTES, 'UTF-8') ?> — <?= htmlspecialchars($child['current_level'] ?? '-', ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul><?php endif; ?>
</div>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Contact Teacher', $content);
