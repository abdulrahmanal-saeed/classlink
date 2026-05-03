<?php
/**
 * /owner/dashboard
 *
 * Owner/Teacher dashboard placeholder for Phase 1.
 * The route is protected server-side, not only by frontend navigation.
 */

require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';

$user = require_role('owner_teacher');

ob_start();
?>
<p class="text-muted">Welcome to the Owner/Teacher dashboard. This role can access all dashboards and settings in later phases.</p>
<div class="row g-3">
  <div class="col-md-4"><div class="status-box"><strong>Students</strong><br><span class="text-muted">Coming soon</span></div></div>
  <div class="col-md-4"><div class="status-box"><strong>Homework</strong><br><span class="text-muted">Coming soon</span></div></div>
  <div class="col-md-4"><div class="status-box"><strong>Scenarios</strong><br><span class="text-muted">Coming soon</span></div></div>
</div>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Owner/Teacher Dashboard', $content);
