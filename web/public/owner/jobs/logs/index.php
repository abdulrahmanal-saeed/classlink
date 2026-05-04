<?php
/** /owner/jobs/logs */
require_once __DIR__ . '/../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../backend/php/shared/CronJobs.php';
require_once __DIR__ . '/../../../../../web/components/layout/dashboard_shell.php';

$user = require_role('owner_teacher');
$runs = cron_job_runs(100);
$reminders = cron_reminder_logs(200);

ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
  <div><p class="text-muted mb-1">Scheduled job run logs and reminder delivery logs.</p></div>
  <div class="d-flex gap-2"><a class="btn btn-outline-brand" href="/owner/jobs">Jobs</a><a class="btn btn-outline-brand" href="/owner/settings/cron">Cron settings</a></div>
</div>
<div class="foundation-card mb-4">
  <h2 class="h5 fw-bold">Job runs</h2>
  <?php if(!$runs):?><div class="alert alert-light border mb-0">No job runs yet.</div><?php else:?><div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Started</th><th>Job</th><th>Source</th><th>Status</th><th>Counts</th><th>Error</th></tr></thead><tbody><?php foreach($runs as $run):?><tr><td><?=htmlspecialchars($run['started_at'],ENT_QUOTES,'UTF-8')?></td><td><code><?=htmlspecialchars($run['job_key'],ENT_QUOTES,'UTF-8')?></code></td><td><?=htmlspecialchars($run['trigger_source'],ENT_QUOTES,'UTF-8')?></td><td><span class="badge text-bg-light border"><?=htmlspecialchars($run['status'],ENT_QUOTES,'UTF-8')?></span></td><td class="small">P:<?= (int)$run['processed_count']?> S:<?= (int)$run['sent_count']?> Sk:<?= (int)$run['skipped_count']?> F:<?= (int)$run['failed_count']?></td><td><?=htmlspecialchars(mb_strimwidth((string)($run['error_message']??''),0,120,'...'),ENT_QUOTES,'UTF-8')?></td></tr><?php endforeach;?></tbody></table></div><?php endif; ?>
</div>
<div class="foundation-card">
  <h2 class="h5 fw-bold">Reminder logs</h2>
  <?php if(!$reminders):?><div class="alert alert-light border mb-0">No reminders yet.</div><?php else:?><div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Sent</th><th>User</th><th>Reminder</th><th>Related</th><th>Status</th><th>Action</th></tr></thead><tbody><?php foreach($reminders as $log):?><tr><td><?=htmlspecialchars($log['sent_at'],ENT_QUOTES,'UTF-8')?></td><td><?=htmlspecialchars($log['display_name']??'-',ENT_QUOTES,'UTF-8')?></td><td><strong><?=htmlspecialchars($log['title'],ENT_QUOTES,'UTF-8')?></strong><br><small><code><?=htmlspecialchars($log['reminder_key'],ENT_QUOTES,'UTF-8')?></code></small></td><td><?=htmlspecialchars(($log['related_entity_type']??'-').' #'.($log['related_entity_id']??'-'),ENT_QUOTES,'UTF-8')?></td><td><span class="badge text-bg-light border"><?=htmlspecialchars($log['status'],ENT_QUOTES,'UTF-8')?></span></td><td><?php if($log['action_url']):?><a class="btn btn-sm btn-outline-brand" href="<?=htmlspecialchars($log['action_url'],ENT_QUOTES,'UTF-8')?>">Open</a><?php endif;?></td></tr><?php endforeach;?></tbody></table></div><?php endif; ?>
</div>
<?php $content=ob_get_clean(); render_dashboard_shell($user,'Job Logs',$content);