# Hostinger Deployment Addendum — Phase 13
# إضافة دليل الرفع على Hostinger — المرحلة 13

> Use this addendum together with:

```text
docs/HOSTINGER_DEPLOYMENT_GUIDE.md
docs/HOSTINGER_PHASE_9_DEPLOYMENT_ADDENDUM.md
docs/HOSTINGER_PHASE_10_DEPLOYMENT_ADDENDUM.md
docs/HOSTINGER_PHASE_11_DEPLOYMENT_ADDENDUM.md
docs/HOSTINGER_PHASE_12_DEPLOYMENT_ADDENDUM.md
```

This addendum covers Phase 13 only.

---

## Phase 13 Name / اسم المرحلة

Owner/Teacher Dashboard Core

لوحة تحكم المالك/المعلم الأساسية

---

## 1) What Phase 13 Adds / ماذا تضيف المرحلة 13

Phase 13 adds:

```text
Central Owner/Teacher dashboard stats
Pending action sections
Expanded student detail page
Owner homework overview
Owner scenarios overview
Owner reviews overview
Owner materials overview
Owner notifications overview
Central OwnerDashboard helper
```

---

## 2) Database Migration / تحديث قاعدة البيانات

Phase 13 does not require a new migration.

هذه المرحلة لا تحتاج migration جديد.

It uses existing tables from previous phases.

So in phpMyAdmin:

```text
No SQL migration required for Phase 13.
```

---

## 3) Files to Upload / الملفات المطلوب رفعها

### Backend helper

Upload:

```text
backend/php/shared/OwnerDashboard.php
```

To:

```text
/home/<HOSTINGER_USER>/domains/mshabibanabil.com/backend/php/shared/OwnerDashboard.php
```

---

### Updated shared layout

Upload:

```text
web/components/layout/dashboard_shell.php
```

To:

```text
/home/<HOSTINGER_USER>/domains/mshabibanabil.com/web/components/layout/dashboard_shell.php
```

Reason:

```text
Adds Owner Homework, Scenarios, Reviews, Materials, and Notifications links.
```

---

### Updated Owner dashboard

Upload:

```text
web/public/owner/dashboard/index.php
```

To:

```text
public_html/staging/owner/dashboard/index.php
```

---

### Updated Owner student detail

Upload:

```text
web/public/owner/students/view/index.php
```

To:

```text
public_html/staging/owner/students/view/index.php
```

---

### New Owner public pages

Upload:

```text
web/public/owner/homework/index.php
web/public/owner/scenarios/index.php
web/public/owner/reviews/index.php
web/public/owner/materials/index.php
web/public/owner/notifications/index.php
```

To:

```text
public_html/staging/owner/homework/index.php
public_html/staging/owner/scenarios/index.php
public_html/staging/owner/reviews/index.php
public_html/staging/owner/materials/index.php
public_html/staging/owner/notifications/index.php
```

---

## 4) URLs to Test / روابط الاختبار

Login as Owner/Teacher and test:

```text
/owner/dashboard
/owner/students
/owner/students/view?id={studentUserId}
/owner/parents
/owner/parents/view?id={parentUserId}
/owner/payments
/owner/onboarding
/owner/calendar
/owner/homework
/owner/scenarios
/owner/reviews
/owner/materials
/owner/notifications
```

---

## 5) Dashboard Stats Test / اختبار كروت الداشبورد

The dashboard should show:

```text
Active students
Active child learners
Pending payments
Pending level checks
Upcoming lessons today
Homework submitted
Scenarios submitted
Reviews pending correction
Students low on credits
AI weekly summaries due
```

If a count is zero, it should show 0 clearly, not fail.

---

## 6) Student Detail Test / اختبار صفحة الطالب

Open:

```text
/owner/students/view?id={studentUserId}
```

Confirm it shows:

```text
Profile
Level
Package balance
Upcoming/past lessons
Homework history
Scenarios
Reviews
Practice words
Common mistakes placeholder
Session notes
AI notes/summaries placeholder
Audit history related to student
```

---

## 7) Empty State Test / اختبار الحالات الفارغة

For new students or empty modules, confirm clean messages appear for:

```text
No lessons yet
No homework yet
No scenarios yet
No reviews yet
No practice words
No session notes
No audit history
```

---

## 8) No phpMyAdmin Changes / لا يوجد تعديل phpMyAdmin

For Phase 13:

```text
No new SQL file.
No migration required.
No database import required.
```

Only upload files.

---

## 9) Manual Full Test Checklist / قائمة اختبار كاملة

1. Upload Phase 13 files.
2. Login as Owner/Teacher.
3. Open `/owner/dashboard`.
4. Confirm dashboard stats load.
5. Confirm pending sections load.
6. Open `/owner/students`.
7. Open `/owner/students/view?id={studentUserId}`.
8. Confirm full student detail sections load.
9. Open `/owner/parents`.
10. Open `/owner/parents/view?id={parentUserId}`.
11. Open `/owner/homework`.
12. Open `/owner/scenarios`.
13. Open `/owner/reviews`.
14. Open `/owner/materials`.
15. Open `/owner/notifications`.
16. Confirm mobile layout is usable.
17. Login as student/parent and try `/owner/dashboard`.
18. Confirm access is blocked.

---

## 10) Known Limitations / القيود الحالية

- Clean routes are still query routes, for example:

```text
/owner/students/view?id=...
```

- Common mistakes module is shown as a placeholder because the table/module is not built yet.
- AI summaries are shown as a placeholder until the AI summary phase is implemented.
- AI weekly summaries due is a lightweight placeholder count for active students.
- Detailed correction workflows are not expanded here; this phase focuses on central visibility.
- CSRF protection still needs strengthening before production.

---

## 11) Stop Rule / قاعدة التوقف

Stop here. Test Phase 13 fully before moving to Phase 14.

توقف هنا. اختبر Phase 13 بالكامل قبل الانتقال إلى Phase 14.
