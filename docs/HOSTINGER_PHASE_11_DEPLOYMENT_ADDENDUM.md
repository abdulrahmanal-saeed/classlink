# Hostinger Deployment Addendum — Phase 11
# إضافة دليل الرفع على Hostinger — المرحلة 11

> Use this addendum together with:

```text
docs/HOSTINGER_DEPLOYMENT_GUIDE.md
docs/HOSTINGER_PHASE_9_DEPLOYMENT_ADDENDUM.md
docs/HOSTINGER_PHASE_10_DEPLOYMENT_ADDENDUM.md
```

This addendum covers Phase 11 only.

---

## Phase 11 Name / اسم المرحلة

Parent Portal for Child Learners

بوابة ولي الأمر للطلاب الأطفال

---

## 1) What Phase 11 Adds / ماذا تضيف المرحلة 11

Phase 11 builds the Parent Portal for child learners.

It adds:

```text
Parent dashboard core
Linked children page
Child dashboard
Child progress
Child homework
Child session notes
Parent notifications
Parent contact shortcuts
Central ParentPortal helper
```

Existing pages from previous phases are connected into the portal:

```text
/parent/book
/parent/child/lessons?id=...
/parent/child/balance?id=...
```

---

## 2) Database Migration / تحديث قاعدة البيانات

Phase 11 does not require a new migration.

هذه المرحلة لا تحتاج migration جديد.

It uses existing tables:

```text
users
parent_child_links
student_profiles
user_profiles
bookings
lesson_sessions
lesson_packages
homeworks
homework_submissions
level_check_attempts
student_intake_forms
student_badges
badge_definitions
notifications
```

So in phpMyAdmin:

```text
No SQL migration required for Phase 11.
```

---

## 3) Files to Upload / الملفات المطلوب رفعها

### Backend helper

Upload:

```text
backend/php/shared/ParentPortal.php
```

To:

```text
/home/<HOSTINGER_USER>/domains/mshabibanabil.com/backend/php/shared/ParentPortal.php
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
Adds Parent Portal navigation links.
```

---

### Updated parent dashboard

Upload:

```text
web/public/parent/dashboard/index.php
```

To:

```text
public_html/staging/parent/dashboard/index.php
```

---

### New parent public pages

Upload these files/folders:

```text
web/public/parent/children/index.php
web/public/parent/child/view/index.php
web/public/parent/child/progress/index.php
web/public/parent/child/homework/index.php
web/public/parent/child/session-notes/index.php
web/public/parent/notifications/index.php
web/public/parent/contact/index.php
```

To:

```text
public_html/staging/parent/children/index.php
public_html/staging/parent/child/view/index.php
public_html/staging/parent/child/progress/index.php
public_html/staging/parent/child/homework/index.php
public_html/staging/parent/child/session-notes/index.php
public_html/staging/parent/notifications/index.php
public_html/staging/parent/contact/index.php
```

---

## 4) Existing Parent Pages Already Needed / صفحات موجودة مطلوبة من مراحل سابقة

Make sure these already exist from Phase 8/9:

```text
public_html/staging/parent/book/index.php
public_html/staging/parent/child/lessons/index.php
public_html/staging/parent/child/balance/index.php
```

If not, upload them again from:

```text
web/public/parent/book/index.php
web/public/parent/child/lessons/index.php
web/public/parent/child/balance/index.php
```

---

## 5) URLs to Test / روابط الاختبار

Login as Parent and test:

```text
/parent/dashboard
/parent/children
/parent/child/view?id={childUserId}
/parent/child/progress?id={childUserId}
/parent/child/lessons?id={childUserId}
/parent/child/homework?id={childUserId}
/parent/child/session-notes?id={childUserId}
/parent/child/balance?id={childUserId}
/parent/book
/parent/notifications
/parent/contact
```

---

## 6) Ownership Security Test / اختبار ملكية بيانات الطفل

Every child-specific parent page checks:

```text
parent_child_links.parent_user_id = logged-in parent
parent_child_links.child_user_id = requested child id
parent_child_links.status = active
```

Test:

1. Login as Parent A.
2. Open linked child page:

```text
/parent/child/view?id={linkedChildId}
```

Expected: page opens.

3. Try unlinked child:

```text
/parent/child/view?id={unlinkedChildId}
```

Expected:

```text
403 Unauthorized
```

Repeat for:

```text
/parent/child/progress?id=...
/parent/child/homework?id=...
/parent/child/session-notes?id=...
/parent/child/balance?id=...
/parent/child/lessons?id=...
```

---

## 7) Parent Dashboard Data Test / اختبار بيانات لوحة ولي الأمر

The dashboard should show:

```text
Linked children
Child progress
Upcoming lesson
Homework status
Teacher notes
Payment/package balance
Level/literacy check result
Recommended first lesson
WhatsApp/contact shortcuts
Badges/streaks for child
```

If no data exists, clear empty states should appear.

---

## 8) Booking Test / اختبار الحجز

1. Login as Parent.
2. Open:

```text
/parent/book
```

3. Choose linked child.
4. Choose available slot.
5. Submit request.
6. Login as Owner.
7. Open:

```text
/owner/bookings
```

8. Confirm booking and add meeting link.
9. Login as Parent.
10. Open:

```text
/parent/child/lessons?id={childUserId}
```

Expected:

```text
Confirmed lesson appears with meeting link.
```

---

## 9) No phpMyAdmin Changes / لا يوجد تعديل phpMyAdmin

For Phase 11:

```text
No new SQL file.
No migration required.
No database import required.
```

Only upload files.

---

## 10) Manual Full Test Checklist / قائمة اختبار كاملة

1. Upload Phase 11 files.
2. Login as demo parent.
3. Open `/parent/dashboard`.
4. Confirm linked children appear.
5. Open `/parent/children`.
6. Open `/parent/child/view?id={linkedChildId}`.
7. Open `/parent/child/progress?id={linkedChildId}`.
8. Open `/parent/child/lessons?id={linkedChildId}`.
9. Open `/parent/child/homework?id={linkedChildId}`.
10. Open `/parent/child/session-notes?id={linkedChildId}`.
11. Open `/parent/child/balance?id={linkedChildId}`.
12. Open `/parent/book`.
13. Book a lesson for linked child.
14. Open `/parent/notifications`.
15. Open `/parent/contact`.
16. Try unlinked child ID on child pages.
17. Confirm access is blocked.
18. Confirm mobile layout.

---

## 11) Known Limitations / القيود الحالية

- Clean routes like `/parent/child/[id]` are implemented as query routes for now.
- Parent-specific reschedule UI is not fully built yet.
- WhatsApp/email contact values are placeholders and should later come from Owner settings.
- Notifications are displayed but not marked as read yet.
- CSRF protection still needs strengthening before production.

---

## 12) Stop Rule / قاعدة التوقف

Stop here. Test Phase 11 fully before moving to Phase 12.

توقف هنا. اختبر Phase 11 بالكامل قبل الانتقال إلى Phase 12.
