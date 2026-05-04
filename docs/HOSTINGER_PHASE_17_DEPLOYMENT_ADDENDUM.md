# Hostinger Deployment Addendum — Phase 17
# إضافة دليل الرفع على Hostinger — المرحلة 17

This addendum covers Phase 17 only.

## Phase 17

Email, WhatsApp Templates, Notifications, and Communication Center.

---

## 1) What to deploy

Phase 17 adds:

```text
Communication Center
Email templates editor
WhatsApp templates editor
Email log fallback
Actionable internal notifications
Student notification action buttons
Parent notification action buttons
Mark notification as read on click
```

---

## 2) Database migration

Run this SQL file in phpMyAdmin after Phase 16:

```text
backend/php/database/migrations/017_communication_center.sql
```

Always export a database backup first.

---

## 3) Files to upload

Backend:

```text
backend/php/shared/CommunicationCenter.php
backend/php/database/migrations/017_communication_center.sql
```

Shared layout:

```text
web/components/layout/dashboard_shell.php
```

Owner pages:

```text
web/public/owner/communication/index.php
web/public/owner/settings/email-templates/index.php
web/public/owner/settings/whatsapp-templates/index.php
web/public/owner/email-logs/index.php
```

Student and Parent notification pages:

```text
web/public/student/notifications/index.php
web/public/parent/notifications/index.php
```

---

## 4) URLs to test

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

## 5) Email template test

1. Login as Owner.
2. Open `/owner/settings/email-templates`.
3. Edit a template.
4. Save.
5. Confirm the template preview replaces variables such as:

```text
[Name]
[Lesson Date]
[Booking Link]
```

---

## 6) Email log fallback test

1. Open `/owner/communication`.
2. Use the fallback email log test form.
3. Open `/owner/email-logs`.
4. Confirm the email appears in the log.

Expected behavior:

```text
If email_provider_configured = 0, email is logged only.
The main flow does not fail.
```

---

## 7) WhatsApp template test

1. Open `/owner/settings/whatsapp-templates`.
2. Edit a template.
3. Save.
4. Click Open WhatsApp.

Expected behavior:

```text
WhatsApp opens with a pre-filled message.
```

---

## 8) Actionable notification test

1. Open `/owner/communication`.
2. Create a student notification for homework, scenario, review, material, booking, payment, or badge.
3. Login as Student.
4. Open `/student/notifications`.
5. Confirm the notification shows:

```text
title
message
type / related entity
read or unread status
action button
```

6. Click the action button.

Expected behavior:

```text
The notification is marked as read.
The student is redirected to the correct action URL.
```

---

## 9) Parent notification test

1. Create a parent notification with a linked child ID.
2. Login as Parent.
3. Open `/parent/notifications`.
4. Click the action button.

Expected behavior:

```text
The notification is marked as read.
The parent is redirected to the linked child route.
Unlinked child access remains blocked by existing parent-child ownership checks.
```

---

## 10) Action URL examples

Student:

```text
/student/homework/view?id={homeworkId}
/student/homework/result?id={homeworkId}
/student/scenarios/view?id={scenarioId}
/student/scenarios/result?id={scenarioId}
/student/reviews/view?id={reviewId}
/student/reviews/result?id={reviewId}
/student/materials
/student/lessons
/student/balance
/student/badges
```

Parent:

```text
/parent/child/homework?id={childId}
/parent/child/lessons?id={childId}
/parent/child/balance?id={childId}
/parent/child/badges?id={childId}
```

Owner:

```text
/owner/homework/submissions?id={homeworkId}
/owner/scenarios/submissions?id={scenarioId}
/owner/reviews/results?id={reviewId}
/owner/materials
/owner/bookings
/owner/badges/settings
```

---

## 11) Known limitations

```text
Real SMTP/API email sending is not implemented yet.
WhatsApp Business API sending is not implemented yet.
This phase uses email logs and wa.me pre-filled links.
Owner notifications overview is not redesigned in this phase.
CSRF protection still needs strengthening before production.
```

---

## Stop rule

Stop here. Test Phase 17 fully before moving to Phase 18.
