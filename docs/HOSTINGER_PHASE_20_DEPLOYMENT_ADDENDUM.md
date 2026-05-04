# Hostinger Deployment Addendum — Phase 20
# إضافة دليل الرفع على Hostinger — المرحلة 20

This addendum covers Phase 20 only.

## Phase 20

Firebase Push Notifications.

---

## 1) What to deploy

Phase 20 adds:

```text
Firebase FCM foundation
Device token registration API
Test push API
Push sending helper
Push notification logs
Owner push preferences
Owner notification settings page
Owner push log page
```

---

## 2) Database migration

Run this SQL file in phpMyAdmin after Phase 19:

```text
backend/php/database/migrations/020_firebase_push_notifications.sql
```

Always export a database backup first.

---

## 3) Files to upload

Backend:

```text
backend/php/shared/PushNotifications.php
backend/php/database/migrations/020_firebase_push_notifications.sql
```

API:

```text
web/public/api/push/register-device/index.php
web/public/api/push/test/index.php
```

Owner pages:

```text
web/public/owner/settings/notifications/index.php
web/public/owner/notifications/index.php
```

Updated event pages:

```text
web/public/student/homework/view/index.php
web/public/student/scenarios/view/index.php
web/public/student/reviews/view/index.php
```

Shared layout:

```text
web/components/layout/dashboard_shell.php
```

Docs:

```text
docs/PHASE_20_EXECUTION_REPORT.md
docs/HOSTINGER_PHASE_20_DEPLOYMENT_ADDENDUM.md
```

---

## 4) Firebase environment variables

Do not upload Firebase service account to GitHub.

Use one of these server environment options:

```text
FIREBASE_SERVICE_ACCOUNT_JSON={service account json}
```

or:

```text
GOOGLE_APPLICATION_CREDENTIALS=/secure/path/service-account.json
```

Optional setting:

```text
firebase_project_id
```

If blank, the helper tries to read project_id from the service account JSON.

---

## 5) URLs to test

```text
/owner/settings/notifications
/owner/notifications
```

API:

```text
POST /api/push/register-device
POST /api/push/test
```

---

## 6) Register device token test

Login first, then call:

```text
POST /api/push/register-device
```

JSON body:

```json
{
  "device_token": "FCM_TOKEN_HERE",
  "platform": "web",
  "device_label": "Chrome desktop",
  "app_version": "1.0.0"
}
```

Expected response:

```json
{
  "ok": true,
  "device_token_id": 1
}
```

---

## 7) Test push

Call:

```text
POST /api/push/test
```

or click Send test push from:

```text
/owner/notifications
```

Expected:

```text
Push log entry appears.
If Firebase credentials are valid, status should be sent.
If credentials are missing, status should be failed but the system should not crash.
```

---

## 8) Owner preferences test

Open:

```text
/owner/settings/notifications
```

Test enabling/disabling:

```text
homework_submitted
scenario_submitted
review_submitted
payment_pending_verification
student_form_submitted
level_check_submitted
testimonial_submitted
academy_brief_submitted
booking_requested
```

Expected:

```text
Disabled events are logged as skipped when triggered.
Enabled events try to send push.
```

---

## 9) Event trigger test

1. Register Owner device token.
2. Login as Student.
3. Submit homework.
4. Check `/owner/notifications`.
5. Confirm `homework_submitted` log appears.
6. Submit scenario.
7. Confirm `scenario_submitted` log appears.
8. Submit review/test.
9. Confirm `review_submitted` log appears.

---

## 10) Known limitations

```text
Web Firebase client script/service worker is not included yet.
Flutter device token registration is not implemented yet, but API is ready.
Some Owner events are defined but not wired yet.
Student/Parent push events are foundation only in this phase.
No queue worker yet; sending is synchronous MVP.
CSRF protection still needs strengthening before production.
```

---

## Stop rule

Stop here. Test Phase 20 fully before moving to Phase 21.
