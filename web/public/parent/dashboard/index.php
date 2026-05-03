<?php
/**
 * /parent/dashboard
 *
 * Parent dashboard placeholder for Phase 1.
 * Parent access will later be limited to linked child learner records.
 */

require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';

$user = require_role('parent');

ob_start();
?>
<p class="text-muted">Welcome to the Parent dashboard. Linked child progress and lesson updates will appear here later.</p>
<div class="status-box">
  <strong>Child learner area</strong><br>
  <span class="text-muted">Placeholder only. Parent-child links will be activated in a later phase.</span>
</div>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Parent Dashboard', $content);
