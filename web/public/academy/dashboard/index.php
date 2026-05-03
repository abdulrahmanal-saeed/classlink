<?php
/**
 * /academy/dashboard
 *
 * Academy Partner dashboard placeholder for Phase 1.
 * Academy partners will later access only their own briefs and referrals.
 */

require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';

$user = require_role('academy_partner');

ob_start();
?>
<p class="text-muted">Welcome to the Academy Partner dashboard. Briefs, partner leads, and referral tracking will appear here later.</p>
<div class="status-box">
  <strong>Partner area</strong><br>
  <span class="text-muted">Placeholder only. Partner workflows will be built in later phases.</span>
</div>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Academy Partner Dashboard', $content);
