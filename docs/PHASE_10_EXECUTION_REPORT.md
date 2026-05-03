# Phase 10 Execution Report
# تقرير تنفيذ المرحلة 10

## Phase Name / اسم المرحلة

Student Portal Core

بوابة الطالب الأساسية

---

## Goal / الهدف

Build the adult student portal.

بناء البوابة الأساسية للطالب البالغ.

---

## Important Database Note / ملاحظة مهمة بخصوص قاعدة البيانات

Phase 10 does not add a new migration.

هذه المرحلة لا تحتاج migration جديد لأنها تستخدم الجداول الموجودة بالفعل من المراحل السابقة:

```text
users
user_profiles
student_profiles
bookings
lesson_sessions
lesson_packages
lesson_credit_transactions
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
referrals
```

---

## Files Created / الملفات التي تم إنشاؤها

### Backend helper / أدوات الباك إند

```text
backend/php/shared/StudentPortal.php
```

### Student pages / صفحات الطالب

```text
web/public/student/profile/index.php
web/public/student/homework/index.php
web/public/student/scenarios/index.php
web/public/student/reviews/index.php
web/public/student/materials/index.php
web/public/student/practice-words/index.php
web/public/student/session-notes/index.php
web/public/student/notifications/index.php
web/public/student/referrals/index.php
```

---

## Files Changed / الملفات التي تم تعديلها

```text
web/public/student/dashboard/index.php
web/components/layout/dashboard_shell.php
```

---

## Existing Pages Used / صفحات موجودة تم استخدامها من مراحل سابقة

```text
web/public/student/lessons/index.php
web/public/student/balance/index.php
web/public/student/book/index.php
```

These were built in Phase 8 and Phase 9 and are now part of the Student Portal navigation.

---

## Student Portal URLs / روابط بوابة الطالب

Implemented or connected:

```text
/student/dashboard
/student/profile
/student/lessons
/student/balance
/student/homework
/student/scenarios
/student/reviews
/student/materials
/student/practice-words
/student/session-notes
/student/notifications
/student/referrals
/student/book
```

---

## Dashboard Shows / محتوى لوحة الطالب

The dashboard now shows:

```text
Upcoming lesson
Package balance
Current homework
Current scenario
Review/test notifications
Progress summary
Streak
Badges preview
Practice words due
Session notes
Recent notifications
```

---

## Security / الأمان

All Student Portal pages use:

```php
require_role('student')
```

All data queries are filtered using the logged-in student ID:

```text
student_user_id = current logged-in student id
user_id = current logged-in student id
```

This prevents a student from viewing another student's data through the portal pages.

---

## StudentPortal Helper / ملف StudentPortal.php

Added centralized functions:

```text
student_portal_profile
student_portal_upcoming_lesson
student_portal_current_homework
student_portal_homeworks
student_portal_current_scenario
student_portal_scenarios
student_portal_reviews
student_portal_materials
student_portal_practice_words
student_portal_words_due_count
student_portal_notifications
student_portal_unread_notifications_count
student_portal_badges
student_portal_session_notes
student_portal_referrals
student_portal_progress_summary
student_portal_streak
student_portal_dashboard
```

Reason:

```text
Keep student data access centralized and ownership-safe.
```

---

## Page Details / تفاصيل الصفحات

### /student/dashboard

Main portal dashboard with cards and direct actions.

Shows:

- Current level.
- Remaining credits.
- Streak.
- Upcoming lesson.
- Package balance.
- Current homework.
- Current scenario.
- Progress summary.
- Practice words due.
- Badges preview.
- Session notes preview.
- Notifications preview.

---

### /student/profile

Shows own profile:

```text
Name
Email
Phone
Country
Learner type
Current level
Target level
Learning goal
Preferred dialect
Timezone
```

---

### /student/homework

Shows own homework assignments and submission/correction status.

Includes:

```text
Title
Due date
Submission status
Instructions
Feedback if available
```

---

### /student/scenarios

Shows own speaking scenarios and feedback.

Includes:

```text
Title
Situation
Prompt
Keywords
Feedback
Score if available
```

---

### /student/reviews

Shows own review tests.

Includes:

```text
Title
Type
Status
Score
Feedback
```

---

### /student/materials

Shows active course materials for:

```text
All levels
or current student level
```

---

### /student/practice-words

Shows own practice words.

Includes:

```text
Arabic word
English meaning
Example sentence
Mastery level
Next review date
```

---

### /student/session-notes

Shows notes from completed/confirmed lessons where notes exist.

---

### /student/notifications

Shows own notifications only.

---

### /student/referrals

Shows own referral records.

Referral creation is not fully built yet and can be expanded later.

---

## UI / الواجهة

Implemented with:

```text
Mobile-first Bootstrap layout
Cards
Empty states
Direct action buttons
Clear badges
Responsive grids
```

---

## Navigation / التنقل

Student sidebar now includes:

```text
Dashboard
Profile
Book Lesson
Lessons
Balance
Homework
Scenarios
Reviews
Materials
Practice Words
Session Notes
Notifications
Referrals
```

---

## Known Limitations / القيود الحالية

- Homework submission UI is not fully implemented yet; this phase displays homework and status.
- Scenario recording/submission UI is not fully implemented yet; this phase displays scenarios and feedback.
- Notifications are displayed but not marked as read yet.
- Referral creation is not fully implemented yet; the page displays existing referral records.
- Streak currently uses notification/activity dates as a lightweight placeholder and should later be replaced with a dedicated activity table or analytics event logic.
- Course materials are global/current-level only; per-student material assignment can be added later.
- CSRF protection still needs strengthening before production.

---

## Manual Test Checklist / قائمة الاختبار اليدوي

1. Login as demo student.
2. Open:

```text
/student/dashboard
```

3. Confirm dashboard loads without errors.
4. Confirm upcoming lesson appears if a booking exists.
5. Confirm package balance appears.
6. Open:

```text
/student/profile
/student/lessons
/student/balance
/student/homework
/student/scenarios
/student/reviews
/student/materials
/student/practice-words
/student/session-notes
/student/notifications
/student/referrals
/student/book
```

7. Confirm each page loads on mobile and desktop.
8. Confirm empty states appear when no data exists.
9. Confirm no page accepts another student ID in query string to show another student's data.
10. Confirm sidebar navigation works.
11. Confirm student cannot access Owner pages.
12. Confirm student cannot access Parent pages.

---

## Stop Point / نقطة التوقف

Stop here. Test this phase before continuing.

توقف هنا. اختبر هذه المرحلة قبل الانتقال للمرحلة التالية.
