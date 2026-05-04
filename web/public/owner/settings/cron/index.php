<?php
/** /owner/settings/cron - cron setup and settings. */
require_once __DIR__ . '/../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../backend/php/shared/CronJobs.php';
require_once __DIR__ . '/../../../../../web/components/layout/dashboard_shell.php';

$user = require_role('owner_teacher');
$message = null;
$error = null;
$keys = [
  'cron_enabled','cron_secret_token','cron_timezone','cron_lesson_reminders_enabled','cron_homework_reminders_enabled','cron_level_check_reminders_enabled','cron_student_form_reminders_enabled','cron_low_credits_reminders_enabled','cron_weekly_summaries_enabled','cron_badge_checks_enabled','cron_referral_checks_enabled','cron_low_credit_threshold','cron_homework_due_hours_before','cron_level_check_reminder_days_after','cron_student_form_reminder_days_after'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        foreach ($keys as $key) {
            $value = $_POST[$key] ?? '0';
            if (str_ends_with($key, '_enabled') || $key === 'cron_enabled') $value = isset($_POST[$key]) ? '1' : '0';
            db()->prepare('UPDATE settings SET setting_value = :value, updated_by_user_id = :user WHERE setting_key = :key')
                ->execute([':value' => $value, ':user' => (int)$user['id'], ':key' => $key]);
        }
        audit_log((int)$user['id'], 'cron_settings_updated', 'settings', 'cron', []);
        $message = 'Cron settings updated.';
    } catch (Throwable $e) { $error = $e->getMessage(); }
}

$token = cron_setting('cron_secret_token', 'change-this-cron-token');
$base = rtrim((isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? 'example.com'), '/');

ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
  <div><p class="text-muted mb-1">Cron endpoints are protected by a secret token. Default timezone is Asia/Dubai.</p></div>
  <div class="d-flex gap-2"><a class="btn btn-outline-brand" href="/owner/jobs">Jobs</a><a class="btn btn-outline-brand" href="/owner/jobs/logs">Job logs</a></div>
</div>
<?php if($message):?><div class="alert alert-success"><?=htmlspecialchars($message,ENT_QUOTES,'UTF-8')?></div><?php endif;?>
<?php if($error):?><div class="alert alert-danger"><?=htmlspecialchars($error,ENT_QUOTES,'UTF-8')?></div><?php endif;?>
<form method="post" class="foundation-card mb-4">
  <div class="row g-3">
    <div class="col-md-4"><div class="form-check mt-4"><input class="form-check-input" type="checkbox" name="cron_enabled" <?= cron_setting('cron_enabled','1')==='1'?'checked':'' ?>><label class="form-check-label">Cron enabled</label></div></div>
    <div class="col-md-4"><label class="form-label">Secret token</label><input class="form-control" name="cron_secret_token" value="<?=htmlspecialchars((string)$token,ENT_QUOTES,'UTF-8')?>"></div>
    <div class="col-md-4"><label class="form-label">Timezone</label><input class="form-control" name="cron_timezone" value="<?=htmlspecialchars((string)cron_setting('cron_timezone','Asia/Dubai'),ENT_QUOTES,'UTF-8')?>"></div>
    <?php foreach(['cron_lesson_reminders_enabled'=>'Lesson reminders','cron_homework_reminders_enabled'=>'Homework reminders','cron_level_check_reminders_enabled'=>'Level check reminders','cron_student_form_reminders_enabled'=>'Student form reminders','cron_low_credits_reminders_enabled'=>'Low credits reminders','cron_weekly_summaries_enabled'=>'Weekly summaries queue','cron_badge_checks_enabled'=>'Streak/badge checks','cron_referral_checks_enabled'=>'Referral qualification checks'] as $key=>$label): ?>
      <div class="col-md-6"><div class="form-check border rounded-4 p-3 ps-5"><input class="form-check-input" type="checkbox" name="<?=htmlspecialchars($key,ENT_QUOTES,'UTF-8')?>" <?= cron_setting($key,'1')==='1'?'checked':'' ?>><label class="form-check-label"><?=htmlspecialchars($label,ENT_QUOTES,'UTF-8')?></label></div></div>
    <?php endforeach; ?>
    <div class="col-md-4"><label class="form-label">Low credit threshold</label><input class="form-control" name="cron_low_credit_threshold" value="<?=htmlspecialchars((string)cron_setting('cron_low_credit_threshold','2'),ENT_QUOTES,'UTF-8')?>"></div>
    <div class="col-md-4"><label class="form-label">Homework due hours before</label><input class="form-control" name="cron_homework_due_hours_before" value="<?=htmlspecialchars((string)cron_setting('cron_homework_due_hours_before','24'),ENT_QUOTES,'UTF-8')?>"></div>
    <div class="col-md-4"><label class="form-label">Level check days after</label><input class="form-control" name="cron_level_check_reminder_days_after" value="<?=htmlspecialchars((string)cron_setting('cron_level_check_reminder_days_after','1'),ENT_QUOTES,'UTF-8')?>"></div>
    <div class="col-md-4"><label class="form-label">Student form days after</label><input class="form-control" name="cron_student_form_reminder_days_after" value="<?=htmlspecialchars((string)cron_setting('cron_student_form_reminder_days_after','1'),ENT_QUOTES,'UTF-8')?>"></div>
    <div class="col-12"><button class="btn btn-brand">Save cron settings</button></div>
  </div>
</form>
<div class="foundation-card">
  <h2 class="h5 fw-bold">Hostinger Cron URLs</h2>
  <p class="text-muted">Use these URLs in Hostinger Cron Jobs. Keep token private.</p>
  <pre class="bg-light border rounded-4 p-3" style="white-space:pre-wrap;"><?=htmlspecialchars($base.'/api/cron/send-lesson-reminders?token='.$token."\n".$base.'/api/cron/send-homework-reminders?token='.$token."\n".$base.'/api/cron/check-badges?token='.$token."\n".$base.'/api/cron/weekly-summaries?token='.$token,ENT_QUOTES,'UTF-8')?></pre>
</div>
<?php $content=ob_get_clean(); render_dashboard_shell($user,'Cron Settings',$content);