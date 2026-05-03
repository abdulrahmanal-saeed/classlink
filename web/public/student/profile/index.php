<?php
/**
 * /student/profile
 * Student can view own profile only.
 */

require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../backend/php/shared/StudentPortal.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';

$user = require_role('student');
$profile = student_portal_profile((int) $user['id']);

ob_start();
?>
<p class="text-muted">Your student profile. Contact the teacher if something needs to be updated.</p>
<div class="foundation-card">
  <dl class="row mb-0">
    <dt class="col-sm-4">Name</dt><dd class="col-sm-8"><?= htmlspecialchars($profile['display_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>
    <dt class="col-sm-4">Email</dt><dd class="col-sm-8"><?= htmlspecialchars($profile['email'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>
    <dt class="col-sm-4">Phone</dt><dd class="col-sm-8"><?= htmlspecialchars($profile['phone'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>
    <dt class="col-sm-4">Country</dt><dd class="col-sm-8"><?= htmlspecialchars($profile['country'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>
    <dt class="col-sm-4">Learner type</dt><dd class="col-sm-8"><?= htmlspecialchars($profile['learner_type'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>
    <dt class="col-sm-4">Current level</dt><dd class="col-sm-8"><?= htmlspecialchars($profile['current_level'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>
    <dt class="col-sm-4">Target level</dt><dd class="col-sm-8"><?= htmlspecialchars($profile['target_level'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>
    <dt class="col-sm-4">Learning goal</dt><dd class="col-sm-8"><?= htmlspecialchars($profile['learning_goal'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>
    <dt class="col-sm-4">Preferred dialect</dt><dd class="col-sm-8"><?= htmlspecialchars($profile['preferred_dialect'] ?? '-', ENT_QUOTES, 'UTF-8') ?></dd>
    <dt class="col-sm-4">Timezone</dt><dd class="col-sm-8"><?= htmlspecialchars($profile['timezone'] ?? 'Asia/Dubai', ENT_QUOTES, 'UTF-8') ?></dd>
  </dl>
</div>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'My Profile', $content);
