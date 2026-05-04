# Phase 21 Execution Report
# تقرير تنفيذ المرحلة 21

## Phase Name / اسم المرحلة

Automated Cron Reminders and Scheduled Jobs

التذكيرات الآلية والمهام المجدولة

---

## Goal / الهدف

Build scheduled reminders and cron-compatible jobs.

بناء تذكيرات آلية ووظائف متوافقة مع Cron.

---

## Database Migration / تحديث قاعدة البيانات

Phase 21 adds:

```text
backend/php/database/migrations/021_cron_jobs_reminders.sql
```

---

## Files Created / الملفات التي تم إنشاؤها

### Database

```text
backend/php/database/migrations/021_cron_jobs_reminders.sql
```

### Backend helper

```text
backend/php/shared/CronJobs.php
```

### Cron API endpoints

```text
web/public/api/cron/send-lesson-reminders/index.php
web/public/api/cron/send-homework-reminders/index.php
web/public/api/cron/check-badges/index.php
web/public/api/cron/weekly-summaries/index.php
```

### Owner pages

```text
web/public/owner/settings/cron/index.php
web/public/owner/jobs/index.php
web/public/owner/jobs/logs/index.php
```

---

## Files Changed / الملفات التي تم تعديلها

```text
web/components/layout/dashboard_shell.php
```

---

## Migration 021 Changes / تغييرات Migration 021

### New table: scheduled_job_runs

Tracks every job execution:

```text
job_key
trigger_source: cron / manual / api
status: running / success / failed / partial
started_at
finished_at
duration_ms
processed_count
sent_count
skipped_count
failed_count
error_message
metadata
created_by_user_id
```

---

### New table: scheduled_reminder_logs

Prevents duplicate reminders and records each reminder delivery:

```text
job_key
reminder_key
target_user_id
target_role
related_entity_type
related_entity_id
scheduled_for
delivery_channel
title
message
action_url
status
job_run_id
notification_id
email_log_id
push_log_ids
error_message
metadata
sent_at
```

Duplicate prevention uses:

```text
reminder_key + target_user_id + related_entity_type + related_entity_id + scheduled_for
```

---

### New table: weekly_ai_summary_queue

Queues weekly AI summaries safely:

```text
student_user_id
week_start
week_end
status: queued / generated / failed / skipped
ai_draft_id
error_message
queued_at
processed_at
```

---

## Cron Settings / إعدادات Cron

Inserted settings:

```text
cron_enabled = 1
cron_secret_token = change-this-cron-token
cron_timezone = Asia/Dubai
cron_lesson_reminders_enabled = 1
cron_homework_reminders_enabled = 1
cron_level_check_reminders_enabled = 1
cron_student_form_reminders_enabled = 1
cron_low_credits_reminders_enabled = 1
cron_weekly_summaries_enabled = 1
cron_badge_checks_enabled = 1
cron_referral_checks_enabled = 1
cron_low_credit_threshold = 2
cron_homework_due_hours_before = 24
cron_level_check_reminder_days_after = 1
cron_student_form_reminder_days_after = 1
```

Important:

```text
Change cron_secret_token before using public cron endpoints.
```

---

## Implemented Jobs / الوظائف المنفذة

Implemented in:

```text
backend/php/shared/CronJobs.php
```

Jobs:

```text
cron_lesson_reminders_job
cron_homework_reminders_job
cron_check_badges_job
cron_weekly_summaries_job
cron_low_credits_job
cron_referral_checks_job
```

---

## Required API/Cron Endpoints / نقاط Cron API

```text
/api/cron/send-lesson-reminders?token=SECRET
/api/cron/send-homework-reminders?token=SECRET
/api/cron/check-badges?token=SECRET
/api/cron/weekly-summaries?token=SECRET
```

All endpoints use:

```text
cron_validate_token()
```

They are not open endpoints.

---

## Implemented Owner Pages / صفحات المالك المنفذة

```text
/owner/settings/cron
/owner/jobs
/owner/jobs/logs
```

### /owner/settings/cron

Shows:

```text
Cron enable/disable
Secret token
Timezone
Job toggles
Thresholds
Hostinger Cron URLs
```

### /owner/jobs

Allows manual development runs:

```text
Lesson reminders
Homework reminders
Badge checks
Weekly summaries queue
Low credit reminders
Referral checks
```

### /owner/jobs/logs

Shows:

```text
Job run logs
Reminder logs
Processed/sent/skipped/failed counts
Error messages
Action URLs
```

---

## Duplicate Prevention / منع التكرار

Each reminder checks `scheduled_reminder_logs` before sending.

If the same reminder is run again for the same user/entity/scheduled time:

```text
status = skipped
reason = duplicate
```

This makes jobs safe to run multiple times.

---

## Delivery Channels / قنوات الإرسال

Reminder helper attempts:

```text
Internal notification
Email log fallback
Push notification
```

If email provider is not configured:

```text
Email is logged only.
```

If push is not configured:

```text
Push log is skipped/failed without breaking the job.
```

---

## Timezone / المنطقة الزمنية

Default:

```text
Asia/Dubai
```

Stored in:

```text
cron_timezone
```

---

## Current Implemented Reminder Coverage / التغطية الحالية

Implemented and runnable:

```text
Lesson reminder 24 hours before
Lesson reminder 1 hour before
Homework reminder
Low credits reminder
Weekly AI summary queue
Streak/badge checks
Referral qualification checks
```

Foundation/settings added for:

```text
Level check reminder if not completed
Student form reminder if not completed
```

These two need final wiring after confirming exact onboarding/level-check table statuses in live schema.

---

## Known Limitations / القيود الحالية

- Level check reminder and student form reminder settings are present but not fully wired yet because table/status names vary across earlier phases.
- Weekly summaries job queues summaries; it does not generate AI drafts automatically yet.
- Cron endpoints are URL-token protected; server-only cron or IP restrictions can be added later.
- Sending is synchronous MVP; no queue worker yet.
- CSRF protection still needs strengthening before production.

---

## Manual Test Checklist / قائمة الاختبار اليدوي

1. Run migration:

```text
backend/php/database/migrations/021_cron_jobs_reminders.sql
```

2. Login as Owner.
3. Open:

```text
/owner/settings/cron
```

4. Change `cron_secret_token` from default.
5. Save settings.
6. Create upcoming lesson in next 24 hours.
7. Open:

```text
/owner/jobs
```

8. Run:

```text
Lesson reminder 24h/1h
```

9. Confirm internal notification/email log/push log appears.
10. Run the same job again.
11. Confirm duplicate is prevented/skipped.
12. Open:

```text
/owner/jobs/logs
```

13. Confirm job run and reminder logs appear.
14. Create due homework.
15. Run homework reminders.
16. Confirm no duplicate on second run.
17. Run badge checks.
18. Run weekly summaries queue.
19. Run referral checks.

---

## Stop Point / نقطة التوقف

Stop here. Test this phase before continuing.

توقف هنا. اختبر هذه المرحلة قبل الانتقال للمرحلة التالية.
