# Hostinger Deployment Addendum — Phase 10
# إضافة دليل الرفع على Hostinger — المرحلة 10

> Use this addendum together with:

```text
docs/HOSTINGER_DEPLOYMENT_GUIDE.md
docs/HOSTINGER_PHASE_9_DEPLOYMENT_ADDENDUM.md
```

This addendum covers Phase 10 only.

---

## Phase 10 Name / اسم المرحلة

Student Portal Core

بوابة الطالب الأساسية

---

## 1) What Phase 10 Adds / ماذا تضيف المرحلة 10

Phase 10 builds the adult student portal.

It adds:

```text
Student dashboard core
Student profile page
Homework page
Scenarios page
Reviews page
Materials page
Practice words page
Session notes page
Notifications page
Referrals page
Full student navigation links
Central StudentPortal helper
```

Existing pages from previous phases are connected into the portal:

```text
/student/book
/student/lessons
/student/balance
```

---

## 2) Database Migration / تحديث قاعدة البيانات

Phase 10 does not require a new migration.

هذه المرحلة لا تحتاج migration جديد.

It uses existing tables from previous phases:

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

So in phpMyAdmin:

```text
No SQL migration required for Phase 10.
```

---

## 3) Files to Upload / الملفات المطلوب رفعها

### Backend helper

Upload:

```text
backend/php/shared/StudentPortal.php
```

To:

```text
/home/<HOSTINGER_USER>/domains/mshabibanabil.com/backend/php/shared/StudentPortal.php
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
Adds the full Student Portal navigation links.
```

---

### Updated student dashboard

Upload:

```text
web/public/student/dashboard/index.php
```

To:

```text
public_html/staging/student/dashboard/index.php
```

---

### New student public pages

Upload these folders/files:

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

To:

```text
public_html/staging/student/profile/index.php
public_html/staging/student/homework/index.php
public_html/staging/student/scenarios/index.php
public_html/staging/student/reviews/index.php
public_html/staging/student/materials/index.php
public_html/staging/student/practice-words/index.php
public_html/staging/student/session-notes/index.php
public_html/staging/student/notifications/index.php
public_html/staging/student/referrals/index.php
```

---

## 4) Existing Student Pages Already Needed / صفحات موجودة مطلوبة من مراحل سابقة

Make sure these already exist from Phase 8/9:

```text
public_html/staging/student/book/index.php
public_html/staging/student/lessons/index.php
public_html/staging/student/balance/index.php
```

If not, upload them again from:

```text
web/public/student/book/index.php
web/public/student/lessons/index.php
web/public/student/balance/index.php
```

---

## 5) URLs to Test / روابط الاختبار

Login as a student and test:

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

## 6) Dashboard Data Test / اختبار بيانات لوحة الطالب

The dashboard should show:

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

If there is no data, the page should show clear empty states, not errors.

---

## 7) Ownership Security Test / اختبار ملكية البيانات

Every Student Portal query uses the logged-in student ID.

Test:

1. Login as Student A.
2. Open all student portal pages.
3. Confirm only Student A data appears.
4. Try adding query parameters like:

```text
/student/homework?id=OTHER_STUDENT_ID
/student/scenarios?student_id=OTHER_STUDENT_ID
```

Expected:

```text
The page still shows only the logged-in student's data.
```

---

## 8) Mobile Test / اختبار الموبايل

On mobile, confirm:

```text
Cards stack vertically
Buttons are easy to tap
Tables are responsive
Empty states are readable
Sidebar/navigation does not break layout
```

---

## 9) No phpMyAdmin Changes / لا يوجد تعديل phpMyAdmin

For Phase 10:

```text
No new SQL file.
No migration required.
No database import required.
```

Only upload files.

---

## 10) Manual Full Test Checklist / قائمة اختبار كاملة

1. Upload Phase 10 files.
2. Login as demo student.
3. Open `/student/dashboard`.
4. Confirm dashboard cards load.
5. Open `/student/profile`.
6. Open `/student/lessons`.
7. Open `/student/balance`.
8. Open `/student/homework`.
9. Open `/student/scenarios`.
10. Open `/student/reviews`.
11. Open `/student/materials`.
12. Open `/student/practice-words`.
13. Open `/student/session-notes`.
14. Open `/student/notifications`.
15. Open `/student/referrals`.
16. Open `/student/book`.
17. Confirm empty states appear where no data exists.
18. Confirm Student cannot access Owner pages.
19. Confirm Student cannot access Parent pages.
20. Confirm mobile layout is usable.

---

## 11) Known Limitations / القيود الحالية

- Homework submission UI is not fully built yet.
- Scenario recording/submission UI is not fully built yet.
- Notifications are displayed but not marked as read yet.
- Referral creation is not fully built yet.
- Streak logic is lightweight and should later move to a dedicated activity/analytics system.
- Per-student course material assignment is not built yet.
- CSRF protection still needs strengthening before production.

---

## 12) Stop Rule / قاعدة التوقف

Stop here. Test Phase 10 fully before moving to Phase 11.

توقف هنا. اختبر Phase 10 بالكامل قبل الانتقال إلى Phase 11.
