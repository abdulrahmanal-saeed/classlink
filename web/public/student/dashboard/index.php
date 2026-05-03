<?php
/**
 * /student/dashboard
 *
 * Student dashboard placeholder for Phase 1.
 * Students must never access another student's or role's data.
 */

require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';

$user = require_role('student');

ob_start();
?>
<p class="text-muted">Welcome to your Student dashboard. Homework, scenarios, reviews, and materials will appear here in later phases.</p>
<div class="status-box">
  <strong>Today’s learning area</strong><br>
  <span class="text-muted">No assignments yet. This is expected in Phase 1.</span>
</div>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Student Dashboard', $content);
