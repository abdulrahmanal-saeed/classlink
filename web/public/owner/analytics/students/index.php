<?php
/** /owner/analytics/students */
require_once __DIR__ . '/../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../backend/php/shared/Analytics.php';
require_once __DIR__ . '/../../../../../web/components/layout/dashboard_shell.php';
$user = require_role('owner_teacher');
$engagement = analytics_student_engagement();
$low = analytics_low_activity_students();
ob_start();
?>
<p class="text-muted">Student engagement metrics from learning activity logs and learning submissions.</p>
<div class="row g-4">
  <div class="col-lg-7"><div class="foundation-card h-100"><h2 class="h5 fw-bold">Student engagement last 30 days</h2><?php if(!$engagement):?><div class="alert alert-light border">No engagement data yet.</div><?php else:?><div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Student</th><th>Activity</th><th>Last activity</th></tr></thead><tbody><?php foreach($engagement as $row):?><tr><td><?=htmlspecialchars($row['display_name'] ?? '-', ENT_QUOTES, 'UTF-8')?><br><small class="text-muted"><?=htmlspecialchars($row['email'] ?? '', ENT_QUOTES, 'UTF-8')?></small></td><td><?= (int)$row['activity_count']?></td><td><?=htmlspecialchars($row['last_activity'] ?? '-', ENT_QUOTES, 'UTF-8')?></td></tr><?php endforeach;?></tbody></table></div><?php endif;?></div></div>
  <div class="col-lg-5"><div class="foundation-card h-100"><h2 class="h5 fw-bold">Low activity students</h2><?php if(!$low):?><div class="alert alert-light border">No low-activity students found.</div><?php else:?><div class="list-group list-group-flush"><?php foreach($low as $row):?><div class="list-group-item px-0"><strong><?=htmlspecialchars($row['display_name'] ?? '-', ENT_QUOTES, 'UTF-8')?></strong><br><small class="text-muted">Last: <?=htmlspecialchars($row['last_activity'] ?? 'No activity', ENT_QUOTES, 'UTF-8')?> · Activity: <?= (int)$row['activity_count']?></small></div><?php endforeach;?></div><?php endif;?></div></div>
</div>
<?php $content=ob_get_clean(); render_dashboard_shell($user,'Student Analytics',$content);