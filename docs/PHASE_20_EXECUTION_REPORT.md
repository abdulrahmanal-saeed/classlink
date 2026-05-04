# Phase 20 Execution Report
# تقرير تنفيذ المرحلة 20

## Phase Name / اسم المرحلة

Firebase Push Notifications

إشعارات Firebase Push

---

## Goal / الهدف

Add push notification foundation for web/mobile, especially Owner/Teacher mobile app later.

إضافة أساس إشعارات push للويب والموبايل، خصوصاً تطبيق Owner/Teacher لاحقاً.

---

## Important Security Note / ملاحظة أمان مهمة

Firebase service account must never be committed to GitHub.

لا ترفع Firebase service account على GitHub نهائياً.

Use server environment variables only:

```text
FIREBASE_SERVICE_ACCOUNT_JSON={service account json}
```

or:

```text
GOOGLE_APPLICATION_CREDENTIALS=/secure/path/service-account.json
```

---

## Official FCM Approach / طريقة FCM المستخدمة

The implementation uses Firebase Cloud Messaging HTTP v1 from the server side.

FCM HTTP v1 requires a server-side OAuth 2.0 Bearer token generated from a service account. The helper creates a JWT, exchanges it for an access token, then calls:

```text
https://fcm.googleapis.com/v1/projects/{projectId}/messages:send
```

---

## Database Migration / تحديث قاعدة البيانات

Phase 20 adds:

```text
backend/php/database/migrations/020_firebase_push_notifications.sql
```

---

## Files Created / الملفات التي تم إنشاؤها

### Database

```text
backend/php/database/migrations/020_firebase_push_notifications.sql
```

### Backend helper

```text
backend/php/shared/PushNotifications.php
```

### API endpoints

```text
web/public/api/push/register-device/index.php
web/public/api/push/test/index.php
```

### Owner pages

```text
web/public/owner/settings/notifications/index.php
```

---

## Files Changed / الملفات التي تم تعديلها

```text
web/public/owner/notifications/index.php
web/components/layout/dashboard_shell.php
web/public/student/homework/view/index.php
web/public/student/scenarios/view/index.php
web/public/student/reviews/view/index.php
```

---

## Migration 020 Changes / تغييرات Migration 020

### New table: push_device_tokens

Stores registered FCM tokens per user:

```text
user_id
token_hash
device_token
platform: web / android / ios / unknown
device_label
app_version
user_agent
status: active / disabled / invalid
last_seen_at
```

---

### New table: push_notification_logs

Stores push send attempts:

```text
user_id
device_token_id
target_role
event_key
title
body
action_url
payload_json
provider
provider_message_id
status: queued / sent / failed / skipped
error_message
sent_at
```

---

### New table: push_notification_preferences

Stores user preference per event:

```text
user_id
event_key
is_enabled
```

---

### Push settings inserted

```text
push_enabled = 1
push_provider = firebase_fcm
firebase_project_id = empty by default
firebase_service_account_json_env = FIREBASE_SERVICE_ACCOUNT_JSON
firebase_service_account_path_env = GOOGLE_APPLICATION_CREDENTIALS
push_owner_default_enabled = 1
push_student_parent_default_enabled = 0
```

---

## Implemented Backend Helper / ملف المساعدة

Implemented:

```text
backend/php/shared/PushNotifications.php
```

Main functions:

```text
push_register_device
push_send_to_user
push_send_to_owners
push_send_to_token
push_logs
push_get_preferences
push_set_preference
push_owner_event_keys
push_student_parent_event_keys
push_fcm_access_token
```

---

## API Endpoints / نقاط API

### Register device token

```text
POST /api/push/register-device
```

Expected JSON:

```json
{
  "device_token": "FCM_TOKEN",
  "platform": "web",
  "device_label": "Chrome desktop",
  "app_version": "1.0.0"
}
```

Response:

```json
{
  "ok": true,
  "device_token_id": 1
}
```

---

### Test push

```text
POST /api/push/test
```

Expected JSON optional:

```json
{
  "title": "Test push",
  "body": "Hello from FCM",
  "action_url": "/owner/notifications"
}
```

Response:

```json
{
  "ok": true,
  "log_ids": [1]
}
```

---

## Implemented Owner Pages / صفحات المالك

```text
/owner/settings/notifications
/owner/notifications
```

`/owner/settings/notifications` includes:

```text
Push enable/disable
Firebase project ID
Owner event preferences
Secret env names display only
```

`/owner/notifications` includes:

```text
Push logs
Test push button
Internal notification log
Registered device count for current owner
```

---

## Owner Push Events / أحداث Push للمالك

Preferences added for:

```text
payment_pending_verification
student_form_submitted
level_check_submitted
homework_submitted
scenario_submitted
review_submitted
testimonial_submitted
academy_brief_submitted
booking_requested
```

---

## Student/Parent Future Events / أحداث الطالب وولي الأمر لاحقاً

Helper supports keys for:

```text
lesson_confirmed
lesson_reminder
homework_published
homework_corrected
scenario_feedback_ready
low_credits
```

Student/Parent push is foundation only in this phase.

---

## Events Wired in This Phase / الأحداث المربوطة فعلياً

Owner push attempt is wired for:

```text
homework_submitted
scenario_submitted
review_submitted
```

These pages call `push_send_to_owners()`:

```text
/student/homework/view?id={homeworkId}
/student/scenarios/view?id={scenarioId}
/student/reviews/view?id={reviewId}
```

---

## Behavior / السلوك

When an event triggers:

```text
If push is disabled -> log status skipped
If preference disabled -> log status skipped
If no active device token -> log status skipped
If Firebase credentials missing -> log status failed
If FCM succeeds -> log status sent
```

No main user flow should fail because of push notification failure.

---

## Security / الأمان

Implemented:

```text
No Firebase service account in GitHub
Service account read from environment only
Device registration requires logged-in user
Test push requires logged-in user
Push sends server-side only
Invalid token can be marked invalid
```

---

## Known Limitations / القيود الحالية

- Web frontend Firebase client registration script/service worker is not included yet.
- Mobile app token registration is ready via API but not implemented in Flutter yet.
- Owner events for payment/form/level check/testimonial/academy brief/booking are defined but not wired into their submit/approval flows yet.
- Student/Parent push events are defined for later.
- No queue worker yet; send is synchronous MVP.
- CSRF protection still needs strengthening before production.

---

## Manual Test Checklist / قائمة الاختبار اليدوي

1. Run migration:

```text
backend/php/database/migrations/020_firebase_push_notifications.sql
```

2. Add Firebase service account to server environment:

```text
FIREBASE_SERVICE_ACCOUNT_JSON={json}
```

or:

```text
GOOGLE_APPLICATION_CREDENTIALS=/secure/path/service-account.json
```

3. Login as Owner.
4. Open:

```text
/owner/settings/notifications
```

5. Confirm push is enabled and Owner events are enabled.
6. Register a token:

```text
POST /api/push/register-device
```

7. Send test push:

```text
POST /api/push/test
```

or click Send test push from:

```text
/owner/notifications
```

8. Open:

```text
/owner/notifications
```

9. Confirm push log entry appears.
10. Submit homework as student.
11. Confirm `homework_submitted` push log appears.
12. Submit scenario as student.
13. Confirm `scenario_submitted` push log appears.
14. Submit review as student.
15. Confirm `review_submitted` push log appears.

---

## Stop Point / نقطة التوقف

Stop here. Test this phase before continuing.

توقف هنا. اختبر هذه المرحلة قبل الانتقال للمرحلة التالية.
