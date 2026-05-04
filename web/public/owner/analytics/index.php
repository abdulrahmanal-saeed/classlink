<?php
/** /owner/analytics - Advanced analytics overview. */
require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../backend/php/shared/Analytics.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';
$user = require_role('owner_teacher');
$summary = analytics_dashboard_summary();
$funnel = analytics_funnel(30);
ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
  <div><p class="text-muted mb-1">Marketing, conversion, revenue, and engagement analytics.</p><small class="text-muted">Privacy-first mode avoids storing sensitive unnecessary data.</small></div>
  <div class="d-flex gap-2 flex-wrap"><a class="btn btn-outline-brand" href="/owner/analytics/marketing">Marketing</a><a class="btn btn-outline-brand" href="/owner/analytics/students">Students</a><a class="btn btn-outline-brand" href="/owner/analytics/revenue">Revenue</a><a class="btn btn-outline-brand" href="/owner/analytics/content">Content</a></div>
</div>
<div class="row g-3 mb-4">
<?php foreach ($summary as $label => $value): ?>
  <div class="col-md-3"><div class="status-box h-100"><strong><?= htmlspecialchars(str_replace('_',' ',ucwords($label,'_')), ENT_QUOTES, 'UTF-8') ?></strong><br><span class="display-6"><?= htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8') ?></span></div></div>
<?php endforeach; ?>
</div>
<div class="foundation-card">
  <h2 class="h5 fw-bold">Conversion funnel last 30 days</h2>
  <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Step</th><th>Count</th></tr></thead><tbody>
  <?php foreach ($funnel as $step): ?><tr><td><?= htmlspecialchars($step['event'], ENT_QUOTES, 'UTF-8') ?></td><td><?= (int)$step['count'] ?></td></tr><?php endforeach; ?>
  </tbody></table></div>
</div>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Advanced Analytics', $content);
