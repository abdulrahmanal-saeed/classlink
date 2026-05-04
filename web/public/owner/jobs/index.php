<?php
/** /owner/jobs - manual run scheduled jobs in development. */
require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../backend/php/shared/CronJobs.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';

$user = require_role('owner_teacher');
$result = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $job = $_POST['job_key'] ?? '';
        $result = match ($job) {
            'send_lesson_reminders' => cron_lesson_reminders_job('manual', (int)$user['id']),
            'send_homework_reminders' => cron_homework_reminders_job('manual', (int)$user['id']),
            'check_badges' => cron_check_badges_job('manual', (int)$user['id']),
            'weekly_summaries' => cron_weekly_summaries_job('manual', (int)$user['id']),
            'low_credits_reminders' => cron_low_credits_job('manual', (int)$user['id']),
            'referral_checks' => cron_referral_checks_job('manual', (int)$user['id']),
            default => throw new RuntimeException('Unknown job.'),
        };
    } catch (Throwable $e) { $error = $e->getMessage(); }
}

$jobs = [
  'send_lesson_reminders' => 'Lesson reminder 24h/1h',
  'send_homework_reminders' => 'Homework reminders',
  'check_badges' => 'Streak/badge checks',
  'weekly_summaries' => 'Weekly AI summary queue',
  'low_credits_reminders' => 'Low credits reminders',
  'referral_checks' => 'Referral qualification checks',
];

ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
  <div><p class="text-muted mb-1">Run scheduled jobs manually for development/testing. Jobs are safe to run multiple times.</p></div>
  <div class="d-flex gap-2"><a class="btn btn-outline-brand" href="/owner/settings/cron">Cron settings</a><a class="btn btn-outline-brand" href="/owner/jobs/logs">Logs</a></div>
</div>
<?php if($error):?><div class="alert alert-danger"><?=htmlspecialchars($error,ENT_QUOTES,'UTF-8')?></div><?php endif;?>
<?php if($result):?><div class="alert alert-info"><pre class="mb-0" style="white-space:pre-wrap;"><?=htmlspecialchars(json_encode($result,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT),ENT_QUOTES,'UTF-8')?></pre></div><?php endif;?>
<div class="row g-3">
<?php foreach($jobs as $key=>$label): ?>
  <div class="col-md-6"><form method="post" class="foundation-card h-100"><input type="hidden" name="job_key" value="<?=htmlspecialchars($key,ENT_QUOTES,'UTF-8')?>"><h2 class="h5 fw-bold"><?=htmlspecialchars($label,ENT_QUOTES,'UTF-8')?></h2><p class="text-muted"><code><?=htmlspecialchars($key,ENT_QUOTES,'UTF-8')?></code></p><button class="btn btn-brand">Run job now</button></form></div>
<?php endforeach; ?>
</div>
<?php $content=ob_get_clean(); render_dashboard_shell($user,'Scheduled Jobs',$content);