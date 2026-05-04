<?php
/**
 * Phase 21 Cron Jobs and Automated Reminders helper.
 * Safe to run repeatedly. Duplicate reminders are prevented by scheduled_reminder_logs.
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/AuditLogger.php';
require_once __DIR__ . '/CommunicationCenter.php';
require_once __DIR__ . '/PushNotifications.php';
require_once __DIR__ . '/LearningEngagement.php';
require_once __DIR__ . '/ReferralSystem.php';
require_once __DIR__ . '/AITools.php';

function cron_setting(string $key, $default = null)
{
    $s = db()->prepare('SELECT setting_value FROM settings WHERE setting_key = :key LIMIT 1');
    $s->execute([':key' => $key]);
    $value = $s->fetchColumn();
    return $value === false ? $default : $value;
}

function cron_timezone(): string
{
    return cron_setting('cron_timezone', 'Asia/Dubai') ?: 'Asia/Dubai';
}

function cron_now(): DateTime
{
    return new DateTime('now', new DateTimeZone(cron_timezone()));
}

function cron_is_enabled(): bool
{
    return cron_setting('cron_enabled', '1') === '1';
}

function cron_validate_token(): void
{
    $expected = (string) cron_setting('cron_secret_token', 'change-this-cron-token');
    $provided = $_GET['token'] ?? $_POST['token'] ?? ($_SERVER['HTTP_X_CRON_TOKEN'] ?? '');
    if ($expected === '' || $expected === 'change-this-cron-token') {
        throw new RuntimeException('Cron secret token is not configured. Update cron_secret_token first.');
    }
    if (!hash_equals($expected, (string) $provided)) {
        http_response_code(403);
        throw new RuntimeException('Invalid cron token.');
    }
}

function cron_start_run(string $jobKey, string $source = 'cron', ?int $userId = null, array $metadata = []): int
{
    db()->prepare('INSERT INTO scheduled_job_runs (job_key, trigger_source, status, started_at, metadata, created_by_user_id) VALUES (:job, :source, "running", NOW(), :metadata, :user)')
        ->execute([
            ':job' => $jobKey,
            ':source' => $source,
            ':metadata' => json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ':user' => $userId,
        ]);
    return (int) db()->lastInsertId();
}

function cron_finish_run(int $runId, string $status, array $counts = [], ?string $error = null): void
{
    db()->prepare(
        'UPDATE scheduled_job_runs SET status=:status, finished_at=NOW(), duration_ms=TIMESTAMPDIFF(MICROSECOND, started_at, NOW())/1000, processed_count=:processed, sent_count=:sent, skipped_count=:skipped, failed_count=:failed, error_message=:error WHERE id=:id'
    )->execute([
        ':status' => $status,
        ':processed' => (int) ($counts['processed'] ?? 0),
        ':sent' => (int) ($counts['sent'] ?? 0),
        ':skipped' => (int) ($counts['skipped'] ?? 0),
        ':failed' => (int) ($counts['failed'] ?? 0),
        ':error' => $error,
        ':id' => $runId,
    ]);
}

function cron_has_reminder(string $reminderKey, ?int $targetUserId, ?string $entityType, ?string $entityId, ?string $scheduledFor): bool
{
    $s = db()->prepare('SELECT id FROM scheduled_reminder_logs WHERE reminder_key = :key AND COALESCE(target_user_id,0) = COALESCE(:user,0) AND COALESCE(related_entity_type,"") = COALESCE(:type,"") AND COALESCE(related_entity_id,"") = COALESCE(:entity,"") AND COALESCE(scheduled_for,"1000-01-01 00:00:00") = COALESCE(:scheduled,"1000-01-01 00:00:00") LIMIT 1');
    $s->execute([':key' => $reminderKey, ':user' => $targetUserId, ':type' => $entityType, ':entity' => $entityId, ':scheduled' => $scheduledFor]);
    return (bool) $s->fetchColumn();
}

function cron_log_reminder(array $data): int
{
    db()->prepare(
        'INSERT IGNORE INTO scheduled_reminder_logs
        (job_key, reminder_key, target_user_id, target_role, related_entity_type, related_entity_id, scheduled_for, delivery_channel, title, message, action_url, status, job_run_id, notification_id, email_log_id, push_log_ids, error_message, metadata, sent_at)
        VALUES
        (:job, :reminder, :user, :role, :type, :entity, :scheduled, :channel, :title, :message, :url, :status, :run, :notification, :email, :push, :error, :metadata, NOW())'
    )->execute([
        ':job' => $data['job_key'],
        ':reminder' => $data['reminder_key'],
        ':user' => $data['target_user_id'] ?? null,
        ':role' => $data['target_role'] ?? null,
        ':type' => $data['related_entity_type'] ?? null,
        ':entity' => $data['related_entity_id'] ?? null,
        ':scheduled' => $data['scheduled_for'] ?? null,
        ':channel' => $data['delivery_channel'] ?? 'multi',
        ':title' => $data['title'],
        ':message' => $data['message'] ?? null,
        ':url' => $data['action_url'] ?? null,
        ':status' => $data['status'] ?? 'sent',
        ':run' => $data['job_run_id'] ?? null,
        ':notification' => $data['notification_id'] ?? null,
        ':email' => $data['email_log_id'] ?? null,
        ':push' => isset($data['push_log_ids']) ? implode(',', (array) $data['push_log_ids']) : null,
        ':error' => $data['error_message'] ?? null,
        ':metadata' => json_encode($data['metadata'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    ]);
    return (int) db()->lastInsertId();
}

function cron_send_in_app_email_push(int $runId, string $jobKey, string $reminderKey, int $userId, string $role, string $title, string $message, string $entityType, string $entityId, string $actionLabel, string $actionUrl, ?string $scheduledFor = null, ?string $emailTemplate = null, array $emailVars = []): array
{
    if (cron_has_reminder($reminderKey, $userId, $entityType, $entityId, $scheduledFor)) {
        return ['status' => 'skipped', 'reason' => 'duplicate'];
    }

    $notificationId = null;
    $emailLogId = null;
    $pushLogs = [];
    $error = null;

    try {
        $notificationId = comm_create_notification([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $reminderKey,
            'target_role' => $role,
            'related_entity_type' => $entityType,
            'related_entity_id' => $entityId,
            'action_label' => $actionLabel,
            'action_url' => $actionUrl,
        ]);
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }

    if ($emailTemplate) {
        try {
            $u = db()->prepare('SELECT email, display_name FROM users WHERE id = :id LIMIT 1');
            $u->execute([':id' => $userId]);
            $user = $u->fetch();
            if ($user && !empty($user['email'])) {
                $emailVars = array_merge(['Name' => $user['display_name'] ?: 'Student'], $emailVars);
                $emailLogId = comm_log_email(0, $userId, $user['email'], $user['display_name'] ?? null, $emailTemplate, $emailVars, 'en', $entityType, $entityId);
            }
        } catch (Throwable $e) {
            $error = trim(($error ? $error . ' | ' : '') . $e->getMessage());
        }
    }

    try {
        $pushLogs = push_send_to_user($userId, $reminderKey, $title, $message, $actionUrl, ['entity_type' => $entityType, 'entity_id' => $entityId], $role);
    } catch (Throwable $e) {
        $error = trim(($error ? $error . ' | ' : '') . $e->getMessage());
    }

    cron_log_reminder([
        'job_key' => $jobKey,
        'reminder_key' => $reminderKey,
        'target_user_id' => $userId,
        'target_role' => $role,
        'related_entity_type' => $entityType,
        'related_entity_id' => $entityId,
        'scheduled_for' => $scheduledFor,
        'delivery_channel' => 'multi',
        'title' => $title,
        'message' => $message,
        'action_url' => $actionUrl,
        'status' => $error ? 'failed' : 'sent',
        'job_run_id' => $runId,
        'notification_id' => $notificationId,
        'email_log_id' => $emailLogId,
        'push_log_ids' => $pushLogs,
        'error_message' => $error,
    ]);

    return ['status' => $error ? 'failed' : 'sent', 'notification_id' => $notificationId, 'email_log_id' => $emailLogId, 'push_log_ids' => $pushLogs, 'error' => $error];
}

function cron_run_job(string $jobKey, callable $callback, string $source = 'cron', ?int $userId = null): array
{
    if (!cron_is_enabled()) return ['ok' => false, 'error' => 'Cron is disabled.'];
    $runId = cron_start_run($jobKey, $source, $userId);
    $counts = ['processed' => 0, 'sent' => 0, 'skipped' => 0, 'failed' => 0];
    try {
        $counts = $callback($runId, $counts);
        $status = ($counts['failed'] ?? 0) > 0 ? 'partial' : 'success';
        cron_finish_run($runId, $status, $counts);
        return ['ok' => true, 'run_id' => $runId, 'counts' => $counts];
    } catch (Throwable $e) {
        cron_finish_run($runId, 'failed', $counts, $e->getMessage());
        return ['ok' => false, 'run_id' => $runId, 'error' => $e->getMessage(), 'counts' => $counts];
    }
}

function cron_lesson_reminders_job(string $source = 'cron', ?int $userId = null): array
{
    return cron_run_job('send_lesson_reminders', function (int $runId, array $counts) {
        if (cron_setting('cron_lesson_reminders_enabled', '1') !== '1') return $counts;
        $sql = 'SELECT ls.*, u.display_name, u.email FROM lesson_sessions ls JOIN users u ON u.id = ls.student_user_id WHERE ls.status IN ("planned","confirmed") AND ls.start_at BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 25 HOUR) ORDER BY ls.start_at ASC LIMIT 200';
        $rows = db()->query($sql)->fetchAll();
        foreach ($rows as $row) {
            $counts['processed']++;
            $start = strtotime($row['start_at']);
            $hours = ($start - time()) / 3600;
            $window = ($hours <= 2) ? '1h' : (($hours <= 25 && $hours >= 23) ? '24h' : null);
            if (!$window) { $counts['skipped']++; continue; }
            $reminderKey = 'lesson_reminder_' . $window;
            $title = $window === '1h' ? 'Lesson starts in 1 hour' : 'Lesson reminder for tomorrow';
            $message = 'Your Arabic lesson is scheduled on ' . $row['start_at'];
            $res = cron_send_in_app_email_push($runId, 'send_lesson_reminders', $reminderKey, (int) $row['student_user_id'], 'student', $title, $message, 'booking', (string) $row['id'], 'Open Lessons', '/student/lessons', $row['start_at'], 'lesson_time_confirmation', ['Lesson Date' => date('Y-m-d', $start), 'Lesson Time' => date('H:i', $start), 'Meeting Link' => $row['meeting_link'] ?? '']);
            $counts[$res['status'] === 'sent' ? 'sent' : ($res['status'] === 'skipped' ? 'skipped' : 'failed')]++;
        }
        return $counts;
    }, $source, $userId);
}

function cron_homework_reminders_job(string $source = 'cron', ?int $userId = null): array
{
    return cron_run_job('send_homework_reminders', function (int $runId, array $counts) {
        if (cron_setting('cron_homework_reminders_enabled', '1') !== '1') return $counts;
        $hours = (int) cron_setting('cron_homework_due_hours_before', 24);
        $sql = 'SELECT h.*, u.display_name FROM homeworks h JOIN users u ON u.id = h.student_user_id WHERE h.status = "published" AND h.due_at IS NOT NULL AND h.due_at BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL ' . $hours . ' HOUR) AND NOT EXISTS (SELECT 1 FROM homework_submissions hs WHERE hs.homework_id = h.id AND hs.student_user_id = h.student_user_id) ORDER BY h.due_at ASC LIMIT 200';
        $rows = db()->query($sql)->fetchAll();
        foreach ($rows as $row) {
            $counts['processed']++;
            $res = cron_send_in_app_email_push($runId, 'send_homework_reminders', 'homework_reminder', (int) $row['student_user_id'], 'student', 'Homework reminder', 'Your homework is due soon: ' . $row['title'], 'homework', (string) $row['id'], 'Open Homework', '/student/homework/view?id=' . (int) $row['id'], $row['due_at'], 'homework_corrected', ['Homework Link' => '/student/homework/view?id=' . (int) $row['id'], 'Result Link' => '/student/homework/view?id=' . (int) $row['id']]);
            $counts[$res['status'] === 'sent' ? 'sent' : ($res['status'] === 'skipped' ? 'skipped' : 'failed')]++;
        }
        return $counts;
    }, $source, $userId);
}

function cron_check_badges_job(string $source = 'cron', ?int $userId = null): array
{
    return cron_run_job('check_badges', function (int $runId, array $counts) {
        if (cron_setting('cron_badge_checks_enabled', '1') !== '1') return $counts;
        $students = db()->query('SELECT id FROM users WHERE role = "student" LIMIT 500')->fetchAll();
        foreach ($students as $student) {
            $counts['processed']++;
            try {
                $awarded = engagement_award_badges((int) $student['id']);
                $counts['sent'] += count($awarded);
            } catch (Throwable $e) {
                $counts['failed']++;
            }
        }
        return $counts;
    }, $source, $userId);
}

function cron_weekly_summaries_job(string $source = 'cron', ?int $userId = null): array
{
    return cron_run_job('weekly_summaries', function (int $runId, array $counts) use ($userId) {
        if (cron_setting('cron_weekly_summaries_enabled', '1') !== '1') return $counts;
        $weekEnd = date('Y-m-d');
        $weekStart = date('Y-m-d', strtotime('-6 days'));
        $students = db()->query('SELECT id FROM users WHERE role = "student" LIMIT 500')->fetchAll();
        foreach ($students as $student) {
            $counts['processed']++;
            try {
                db()->prepare('INSERT IGNORE INTO weekly_ai_summary_queue (student_user_id, week_start, week_end, status, queued_at) VALUES (:student, :start, :end, "queued", NOW())')
                    ->execute([':student' => (int) $student['id'], ':start' => $weekStart, ':end' => $weekEnd]);
                $counts['sent']++;
            } catch (Throwable $e) { $counts['failed']++; }
        }
        return $counts;
    }, $source, $userId);
}

function cron_referral_checks_job(string $source = 'cron', ?int $userId = null): array
{
    return cron_run_job('referral_checks', function (int $runId, array $counts) {
        if (cron_setting('cron_referral_checks_enabled', '1') !== '1') return $counts;
        $rows = db()->query('SELECT id FROM purchases WHERE status IN ("paid","approved","verified") AND referral_code_id IS NOT NULL AND referral_id IS NULL LIMIT 200')->fetchAll();
        foreach ($rows as $row) {
            $counts['processed']++;
            try {
                $id = referral_qualify_from_paid_purchase((int) $row['id'], null);
                $id ? $counts['sent']++ : $counts['skipped']++;
            } catch (Throwable $e) { $counts['failed']++; }
        }
        return $counts;
    }, $source, $userId);
}

function cron_low_credits_job(string $source = 'cron', ?int $userId = null): array
{
    return cron_run_job('low_credits_reminders', function (int $runId, array $counts) {
        if (cron_setting('cron_low_credits_reminders_enabled', '1') !== '1') return $counts;
        $threshold = (float) cron_setting('cron_low_credit_threshold', 2);
        $rows = db()->prepare('SELECT lp.*, u.display_name FROM lesson_packages lp JOIN users u ON u.id = lp.student_user_id WHERE lp.status = "active" AND lp.remaining_credits <= :threshold LIMIT 200');
        $rows->execute([':threshold' => $threshold]);
        foreach ($rows->fetchAll() as $row) {
            $counts['processed']++;
            $res = cron_send_in_app_email_push($runId, 'low_credits_reminders', 'low_credits', (int) $row['student_user_id'], 'student', 'Low lesson credits', 'Your lesson balance is low: ' . $row['remaining_credits'] . ' credits left.', 'payment', (string) $row['id'], 'Open Balance', '/student/balance', date('Y-m-d 00:00:00'), null, []);
            $counts[$res['status'] === 'sent' ? 'sent' : ($res['status'] === 'skipped' ? 'skipped' : 'failed')]++;
        }
        return $counts;
    }, $source, $userId);
}

function cron_job_runs(int $limit = 200): array
{
    $s = db()->prepare('SELECT * FROM scheduled_job_runs ORDER BY started_at DESC LIMIT :limit');
    $s->bindValue(':limit', $limit, PDO::PARAM_INT);
    $s->execute();
    return $s->fetchAll();
}

function cron_reminder_logs(int $limit = 300): array
{
    $s = db()->prepare('SELECT scheduled_reminder_logs.*, users.display_name FROM scheduled_reminder_logs LEFT JOIN users ON users.id = scheduled_reminder_logs.target_user_id ORDER BY sent_at DESC LIMIT :limit');
    $s->bindValue(':limit', $limit, PDO::PARAM_INT);
    $s->execute();
    return $s->fetchAll();
}
