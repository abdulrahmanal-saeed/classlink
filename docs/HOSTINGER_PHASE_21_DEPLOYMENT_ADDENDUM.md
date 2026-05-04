# Hostinger Deployment Addendum — Phase 21
# إضافة دليل الرفع على Hostinger — المرحلة 21

This addendum covers Phase 21 only.

## Phase 21

Automated Cron Reminders and Scheduled Jobs.

---

## 1) What to deploy

Phase 21 adds:

```text
Cron settings
Manual scheduled job runner
Job run logs
Reminder delivery logs
Duplicate reminder prevention
Cron API endpoints protected by secret token
Weekly AI summary queue
Badge checks
Referral checks
Lesson reminders
Homework reminders
Low credit reminders
```

---

## 2) Database migration

Run this SQL file in phpMyAdmin after Phase 20:

```text
backend/php/database/migrations/021_cron_jobs_reminders.sql
```

Always export a database backup first.

---

## 3) Files to upload

Backend:

```text
backend/php/shared/CronJobs.php
backend/php/database/migrations/021_cron_jobs_reminders.sql
```

Cron API endpoints:

```text
web/public/api/cron/send-lesson-reminders/index.php
web/public/api/cron/send-homework-reminders/index.php
web/public/api/cron/check-badges/index.php
web/public/api/cron/weekly-summaries/index.php
```

Owner pages:

```text
web/public/owner/settings/cron/index.php
web/public/owner/jobs/index.php
web/public/owner/jobs/logs/index.php
```

Shared layout:

```text
web/components/layout/dashboard_shell.php
```

Docs:

```text
docs/PHASE_21_EXECUTION_REPORT.md
docs/HOSTINGER_PHASE_21_DEPLOYMENT_ADDENDUM.md
```

---

## 4) URLs to test

Owner:

```text
/owner/settings/cron
/owner/jobs
/owner/jobs/logs
```

Cron endpoints:

```text
/api/cron/send-lesson-reminders?token=SECRET
/api/cron/send-homework-reminders?token=SECRET
/api/cron/check-badges?token=SECRET
/api/cron/weekly-summaries?token=SECRET
```

---

## 5) Cron token setup

Open:

```text
/owner/settings/cron
```

Change:

```text
cron_secret_token
```

Do not keep the default value:

```text
change-this-cron-token
```

---

## 6) Hostinger Cron setup

In Hostinger hPanel:

```text
Advanced → Cron Jobs
```

Add cron jobs that call the URLs shown in:

```text
/owner/settings/cron
```

Suggested schedule:

```text
Lesson reminders: every 15 minutes
Homework reminders: every hour
Badge checks: once daily
Weekly summaries: once weekly
```

---

## 7) Manual job test

1. Login as Owner.
2. Open `/owner/jobs`.
3. Run:

```text
Lesson reminder 24h/1h
Homework reminders
Badge checks
Weekly summaries queue
Low credit reminders
Referral checks
```

4. Open `/owner/jobs/logs`.
5. Confirm job run appears.

---

## 8) Duplicate prevention test

1. Create an upcoming lesson.
2. Run lesson reminder job.
3. Confirm notification/email/push log appears.
4. Run the same job again.
5. Confirm duplicate reminder is skipped/prevented.

Duplicate prevention key:

```text
reminder_key + target_user_id + related_entity_type + related_entity_id + scheduled_for
```

---

## 9) Logs test

Open:

```text
/owner/jobs/logs
```

Confirm:

```text
Job runs
Processed/sent/skipped/failed counts
Reminder logs
Action URLs
Errors if any
```

---

## 10) Current limitations

```text
Level check reminder and student form reminder settings are present but not fully wired yet.
Weekly summaries job queues summaries; it does not generate AI drafts automatically yet.
Cron endpoints are protected by URL token only.
Sending is synchronous MVP; no queue worker yet.
CSRF protection still needs strengthening before production.
```

---

## Stop rule

Stop here. Test Phase 21 fully before moving to Phase 22.
