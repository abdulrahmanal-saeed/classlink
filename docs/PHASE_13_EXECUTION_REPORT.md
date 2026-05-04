# Phase 13 Execution Report
# تقرير تنفيذ المرحلة 13

## Phase Name / اسم المرحلة

Owner/Teacher Dashboard Core

لوحة تحكم المالك/المعلم الأساسية

---

## Goal / الهدف

Build the central Owner/Teacher dashboard.

بناء لوحة مركزية للمالك/المعلم لإدارة الكيانات الأساسية.

---

## Important Database Note / ملاحظة مهمة بخصوص قاعدة البيانات

Phase 13 does not add a new migration.

هذه المرحلة لا تحتاج migration جديد لأنها تعتمد على الجداول الموجودة بالفعل من المراحل السابقة:

```text
users
student_profiles
parent_profiles
parent_child_links
purchases
payment_records
level_check_attempts
bookings
lesson_sessions
lesson_packages
homeworks
homework_submissions
scenarios
scenario_submissions
review_tests
review_submissions
course_materials
practice_words
flashcard_reviews
student_badges
badge_definitions
notifications
audit_logs
```

---

## Files Created / الملفات التي تم إنشاؤها

### Backend helper / أدوات الباك إند

```text
backend/php/shared/OwnerDashboard.php
```

### Owner pages / صفحات المالك

```text
web/public/owner/homework/index.php
web/public/owner/scenarios/index.php
web/public/owner/reviews/index.php
web/public/owner/materials/index.php
web/public/owner/notifications/index.php
```

---

## Files Changed / الملفات التي تم تعديلها

```text
web/public/owner/dashboard/index.php
web/public/owner/students/view/index.php
web/components/layout/dashboard_shell.php
```

---

## Existing Owner Pages Connected / صفحات موجودة مرتبطة

These pages already existed and remain part of the Owner/Teacher core dashboard:

```text
/owner/students
/owner/students/view?id={studentUserId}
/owner/parents
/owner/parents/view?id={parentUserId}
/owner/payments
/owner/onboarding
/owner/calendar
/owner/bookings
/owner/packages
/owner/academy-briefs
/owner/level-checks
```

---

## Implemented Owner URLs / روابط المالك المنفذة أو المحدثة

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

Practical query routes are used instead of clean `[id]` routes for now.

---

## Dashboard Cards / كروت لوحة التحكم

The dashboard now shows:

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

Each relevant card has a direct action button to the related page.

---

## Dashboard Pending Sections / أقسام العناصر المعلقة

The dashboard includes quick pending lists for:

```text
Pending booking requests
Pending payments
Homework needing attention
Students low on credits
```

---

## OwnerDashboard Helper / ملف OwnerDashboard.php

Added centralized functions:

```text
owner_count
owner_dashboard_stats
owner_dashboard_pending_items
owner_students_core
owner_parents_core
owner_student_detail_full
owner_all_homework
owner_all_scenarios
owner_all_reviews
owner_all_materials
owner_all_notifications
```

Reason:

```text
Keep Owner/Teacher stats and cross-student data access centralized.
```

---

## Student Detail / تفاصيل الطالب

Updated:

```text
/owner/students/view?id={studentUserId}
```

Now shows:

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

## New Owner Core Pages / صفحات المالك المركزية الجديدة

### /owner/homework

Shows homework across students:

```text
Homework title
Student
Status
Submission status
Submitted at
Due date
```

---

### /owner/scenarios

Shows scenarios across students:

```text
Scenario title
Student
Status
Submitted at
Score
```

---

### /owner/reviews

Shows review tests across students:

```text
Review title
Student
Type
Status
Score
Reviewed date
```

---

### /owner/materials

Shows course materials:

```text
Title
Material type
Level/global
Active status
File/link
```

---

### /owner/notifications

Shows notification log across users:

```text
Date
User
Title/body preview
Channel
Status
```

---

## Navigation / التنقل

Owner sidebar now includes:

```text
Homework
Scenarios
Reviews
Materials
Notifications
```

alongside the existing pages.

---

## Security / الأمان

All Owner pages use:

```php
require_role('owner_teacher')
```

Student details are only available to Owner/Teacher role.

---

## Known Limitations / القيود الحالية

- Clean route `/owner/students/[id]` is implemented as `/owner/students/view?id=...` for now.
- Common mistakes table/module is not present yet, so Student Detail shows a clean empty-state placeholder.
- AI weekly summaries due currently uses a lightweight placeholder count of active students.
- AI notes/summaries display an empty-state placeholder until AI summary generation is implemented.
- Owner homework/scenario/review pages are overview pages; detailed correction workflows can be expanded later.
- No new creation/edit forms were added here to avoid duplicate actions and keep this phase focused on central visibility.
- CSRF protection still needs strengthening before production.

---

## Manual Test Checklist / قائمة الاختبار اليدوي

1. Login as Owner/Teacher.
2. Open:

```text
/owner/dashboard
```

3. Confirm stats cards load.
4. Confirm pending sections load.
5. Open:

```text
/owner/students
```

6. Open student detail:

```text
/owner/students/view?id={studentUserId}
```

7. Confirm student profile, balance, lessons, homework, scenarios, reviews, practice words, notes, and audit history sections load.
8. Open:

```text
/owner/parents
/owner/parents/view?id={parentUserId}
```

9. Open:

```text
/owner/homework
/owner/scenarios
/owner/reviews
/owner/materials
/owner/notifications
```

10. Confirm empty states are clean where no data exists.
11. Confirm mobile layout is responsive.
12. Confirm non-owner users cannot access Owner pages.

---

## Stop Point / نقطة التوقف

Stop here. Test this phase before continuing.

توقف هنا. اختبر هذه المرحلة قبل الانتقال للمرحلة التالية.
