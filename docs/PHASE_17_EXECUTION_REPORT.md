# Phase 17 Execution Report
# تقرير تنفيذ المرحلة 17

## Phase Name / اسم المرحلة

Email, WhatsApp Templates, Notifications, and Communication Center

قوالب البريد، قوالب واتساب، الإشعارات، ومركز التواصل

---

## Goal / الهدف

Build communication tools.

بناء أدوات التواصل والقوالب والإشعارات الداخلية القابلة للتنفيذ.

---

## Database Migration / تحديث قاعدة البيانات

Phase 17 adds a migration:

```text
backend/php/database/migrations/017_communication_center.sql
```

---

## Files Created / الملفات التي تم إنشاؤها

### Database

```text
backend/php/database/migrations/017_communication_center.sql
```

### Backend helper

```text
backend/php/shared/CommunicationCenter.php
```

### Owner pages

```text
web/public/owner/communication/index.php
web/public/owner/settings/email-templates/index.php
web/public/owner/settings/whatsapp-templates/index.php
web/public/owner/email-logs/index.php
```

---

## Files Changed / الملفات التي تم تعديلها

```text
web/public/student/notifications/index.php
web/public/parent/notifications/index.php
web/components/layout/dashboard_shell.php
```

---

## Migration 017 Changes / تغييرات Migration 017

### notifications expanded

Adds actionable notification fields:

```text
notification_type
target_role
related_entity_type
related_entity_id
action_label
action_url
```

Supported related entity types:

```text
homework
scenario
review
material
booking
payment
badge
level_check
weekly_summary
general
```

---

### New table: email_logs

Fallback email logging table:

```text
recipient_email
recipient_name
template_key
subject
body
related_entity_type
related_entity_id
provider
provider_message_id
status
error_message
created_at
sent_at
```

If email provider is missing:

```text
Email is logged with status = logged.
Main flow does not fail.
```

---

### email_templates expanded

Adds:

```text
name
variables
sort_order
```

---

### whatsapp_templates expanded

Adds:

```text
name
variables
sort_order
```

---

## Default Email Templates / قوالب البريد الافتراضية

Inserted/updated:

```text
Payment Confirmation
Student Form Received
Lesson Time Confirmation
Level Check Reminder
After First Lesson
Homework Corrected
Weekly Summary Ready
```

Template keys:

```text
payment_confirmation
student_form_received
lesson_time_confirmation
level_check_reminder
after_first_lesson
homework_corrected
weekly_summary_ready
```

---

## Default WhatsApp Templates / قوالب واتساب الافتراضية

Inserted/updated:

```text
After Payment
Lesson Confirmation
Level Check Received
Homework Published
Scenario Published
Review Published
Reminder 24h
Reminder 1h
Low Credits
```

Template keys:

```text
after_payment
lesson_confirmation
level_check_received
homework_published
scenario_published
review_published
reminder_24h
reminder_1h
low_credits
```

---

## Implemented Pages / الصفحات المنفذة

Owner:

```text
/owner/communication
/owner/settings/email-templates
/owner/settings/whatsapp-templates
/owner/email-logs
```

Student:

```text
/student/notifications
```

Parent:

```text
/parent/notifications
```

---

## Communication Center / مركز التواصل

Implemented:

```text
Template counts
Provider configured status
WhatsApp quick preview buttons
Fallback email log test
Actionable notification test
Recent email logs
```

---

## Template Variables / متغيرات القوالب

Supported replacement pattern:

```text
[Name]
[Lesson Date]
[Lesson Time]
[Booking Link]
[Homework Link]
[Result Link]
[Scenario Link]
[Review Link]
[Payment Link]
```

Implementation:

```text
comm_replace_variables()
```

---

## Email Fallback Rule / قاعدة البريد الاحتياطية

Implemented in:

```text
comm_log_email()
```

Behavior:

```text
If email_provider_configured = 0:
  email is logged only
  provider = fallback_log_only
  status = logged
  main flow does not fail

If email_provider_configured = 1:
  email is queued placeholder
  provider = configured_provider_placeholder
  status = queued
```

Actual SMTP/API provider sending can be added later.

---

## WhatsApp Buttons / أزرار واتساب

Implemented:

```text
comm_whatsapp_link(phone, message)
```

Output:

```text
https://wa.me/{phone}?text={encodedMessage}
```

Owner pages include demo buttons that open pre-filled WhatsApp messages.

---

## Actionable Notifications / الإشعارات القابلة للتنفيذ

Implemented helper:

```text
comm_create_notification()
```

Rules enforced:

```text
Notifications related to homework, scenario, review, material, booking, payment, or badge must include action_label and action_url.
```

If missing action data:

```text
The helper throws an error and does not create the notification.
```

---

## Action Routes / روابط الإجراءات

Implemented helper:

```text
comm_action_route(targetRole, entityType, entityId, childId, mode)
```

Student routes:

```text
homework open   -> /student/homework/view?id={homeworkId}
homework result -> /student/homework/result?id={homeworkId}
scenario open   -> /student/scenarios/view?id={scenarioId}
scenario result -> /student/scenarios/result?id={scenarioId}
review open     -> /student/reviews/view?id={reviewId}
review result   -> /student/reviews/result?id={reviewId}
material        -> /student/materials
booking         -> /student/lessons
payment         -> /student/balance
badge           -> /student/badges
```

Parent routes:

```text
homework        -> /parent/child/homework?id={childId}
scenario        -> /parent/child/view?id={childId}
review          -> /parent/child/progress?id={childId}
material        -> /parent/child/view?id={childId}
booking         -> /parent/child/lessons?id={childId}
payment         -> /parent/child/balance?id={childId}
badge           -> /parent/child/badges?id={childId}
```

Owner routes:

```text
homework        -> /owner/homework/submissions?id={homeworkId}
scenario        -> /owner/scenarios/submissions?id={scenarioId}
review          -> /owner/reviews/results?id={reviewId}
material        -> /owner/materials
booking         -> /owner/bookings
payment         -> /owner/payments/view?id={paymentId}
badge           -> /owner/badges/settings
```

---

## Mark as Read / تعليم الإشعار كمقروء

Student and Parent notification pages support:

```text
?read={notificationId}&to={actionUrl}
```

Behavior:

```text
Marks notification as read.
Redirects to action URL.
```

---

## Security / الأمان

Student notifications:

```text
Only notifications for logged-in student user_id are shown.
```

Parent notifications:

```text
Only notifications for logged-in parent user_id are shown.
```

Role-specific action URLs should be generated through:

```text
comm_action_route()
```

Existing linked-child pages already enforce parent-child ownership.

---

## Navigation / التنقل

Owner sidebar now includes:

```text
Communication Center
Email Templates
WhatsApp Templates
Email Logs
```

---

## Known Limitations / القيود الحالية

- Real SMTP/API sending is not implemented yet; email provider configured mode queues placeholder logs.
- WhatsApp is copy/open via wa.me link; no WhatsApp Business API sending yet.
- Owner notification page is not redesigned in this phase; student/parent notification pages are actionable.
- Parent clean routes like `/parent/child/[id]/homework/[homeworkId]/result` are approximated using current query-route structure.
- CSRF protection still needs strengthening before production.

---

## Manual Test Checklist / قائمة الاختبار اليدوي

1. Run migration:

```text
backend/php/database/migrations/017_communication_center.sql
```

2. Login as Owner.
3. Open:

```text
/owner/settings/email-templates
```

4. Edit an email template and save.
5. Open:

```text
/owner/settings/whatsapp-templates
```

6. Edit a WhatsApp template and save.
7. Click Open WhatsApp preview and confirm wa.me opens with pre-filled text.
8. Open:

```text
/owner/communication
```

9. Create a fallback email log.
10. Open:

```text
/owner/email-logs
```

11. Confirm email log appears.
12. Create an actionable student notification.
13. Login as Student.
14. Open:

```text
/student/notifications
```

15. Confirm notification shows action button.
16. Click action button.
17. Confirm notification becomes read and redirects to the action URL.
18. Create an actionable parent notification with child ID.
19. Login as Parent.
20. Open:

```text
/parent/notifications
```

21. Confirm action button works.
22. Confirm parent cannot access unlinked child routes.

---

## Stop Point / نقطة التوقف

Stop here. Test this phase before continuing.

توقف هنا. اختبر هذه المرحلة قبل الانتقال للمرحلة التالية.
