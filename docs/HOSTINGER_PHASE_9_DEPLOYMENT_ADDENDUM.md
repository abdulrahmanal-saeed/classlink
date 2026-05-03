# Hostinger Deployment Addendum — Phase 9
# إضافة دليل الرفع على Hostinger — المرحلة 9

> Use this addendum together with:

```text
docs/HOSTINGER_DEPLOYMENT_GUIDE.md
```

The main guide covers Phase 0 → Phase 8. This addendum adds Phase 9 deployment steps.

الدليل الأساسي يغطي Phase 0 → Phase 8. هذا الملف يضيف خطوات رفع Phase 9 فقط.

---

## Phase 9 Name / اسم المرحلة

Real Booking Availability Calendar

تقويم حقيقي للتوفر والحجوزات

---

## 1) What Phase 9 Adds / ماذا تضيف المرحلة 9

Phase 9 adds a real booking and availability system:

```text
Owner availability rules
Blocked/unavailable times
Available slot generation
Student booking request
Parent booking request for linked child
Owner booking confirmation
Meeting link field
Reschedule request
Booking linked to package/credits
Double booking prevention
```

---

## 2) Files to Upload / الملفات المطلوب رفعها

### Backend files

Upload this file to:

```text
/home/<HOSTINGER_USER>/domains/mshabibanabil.com/backend/php/shared/BookingCalendar.php
```

Source path in GitHub:

```text
backend/php/shared/BookingCalendar.php
```

---

### Migration file

Upload or copy this SQL file content when using phpMyAdmin:

```text
backend/php/database/migrations/009_real_booking_availability_calendar.sql
```

It does not go inside public_html.

---

### Owner public pages

Upload these folders into:

```text
public_html/staging/owner/
```

Source paths:

```text
web/public/owner/calendar/index.php
web/public/owner/availability/index.php
web/public/owner/bookings/index.php
```

Expected Hostinger paths:

```text
public_html/staging/owner/calendar/index.php
public_html/staging/owner/availability/index.php
public_html/staging/owner/bookings/index.php
```

---

### Student public pages

Upload these folders into:

```text
public_html/staging/student/
```

Source paths:

```text
web/public/student/book/index.php
web/public/student/lessons/index.php
```

Expected Hostinger paths:

```text
public_html/staging/student/book/index.php
public_html/staging/student/lessons/index.php
```

---

### Parent public pages

Upload these folders into:

```text
public_html/staging/parent/
```

Source paths:

```text
web/public/parent/book/index.php
web/public/parent/child/lessons/index.php
```

Expected Hostinger paths:

```text
public_html/staging/parent/book/index.php
public_html/staging/parent/child/lessons/index.php
```

---

### Changed files

Upload the updated shared layout:

```text
web/components/layout/dashboard_shell.php
```

To:

```text
/home/<HOSTINGER_USER>/domains/mshabibanabil.com/web/components/layout/dashboard_shell.php
```

Upload updated parent dashboard:

```text
web/public/parent/dashboard/index.php
```

To:

```text
public_html/staging/parent/dashboard/index.php
```

---

## 3) Database Migration / تحديث قاعدة البيانات

### Migration to run

Run this migration after Phase 8:

```text
backend/php/database/migrations/009_real_booking_availability_calendar.sql
```

### How to run from phpMyAdmin

1. Open Hostinger hPanel.
2. Open Databases.
3. Open phpMyAdmin for the staging database.
4. Select the correct database from the left sidebar.
5. Click Export first and download a backup.
6. Open the SQL tab.
7. Paste the full content of:

```text
009_real_booking_availability_calendar.sql
```

8. Click Go.
9. If Success appears, continue testing.
10. If an error appears, stop and send the error before editing anything manually.

---

## 4) Tables Updated / الجداول التي يتم تحديثها

Migration 009 updates:

```text
availability_rules
blocked_times
bookings
settings
```

---

## 5) New Columns / الأعمدة الجديدة

### availability_rules

Adds:

```text
session_duration_minutes
buffer_minutes
max_sessions_per_day
notes
```

Purpose:

- Session duration default is 90 minutes.
- Buffer time can be added between slots.
- Max sessions per day can limit daily capacity.

---

### blocked_times

Adds:

```text
is_full_day
```

Purpose:

- Allows blocking a full day later.
- Current UI supports start/end blocked time.

---

### bookings

Adds:

```text
owner_user_id
requested_by_user_id
package_id
title
meeting_link
student_note
owner_note
reschedule_reason
reschedule_requested_at
confirmed_by_user_id
confirmed_at
canceled_at
```

Purpose:

- Store who requested booking.
- Store Owner/Teacher responsible.
- Link booking to lesson package.
- Store meeting link.
- Track confirmation and reschedule requests.

---

## 6) Booking Statuses / حالات الحجز

Phase 9 supports:

```text
requested
confirmed
rejected
reschedule_requested
rescheduled
canceled
cancelled   legacy compatibility
completed
no_show
```

Note:

```text
cancelled
```

is kept for compatibility with old data.

---

## 7) New Settings / الإعدادات الجديدة

Migration 009 inserts:

```text
booking.default_session_duration_minutes = 90
booking.default_buffer_minutes = 0
booking.max_days_ahead = 30
booking.allow_parent_booking = 1
booking.allow_student_booking = 1
booking.reschedule_min_hours_before_lesson = 24
booking.default_meeting_link = empty
```

These are stored in:

```text
settings
```

---

## 8) Owner URLs to Test / روابط المالك للاختبار

After upload and migration, login as Owner and open:

```text
/owner/availability
/owner/calendar
/owner/bookings
```

### /owner/availability

Use it to create:

```text
Working day
Start time
End time
Session duration
Buffer time
Max sessions per day optional
Blocked time
```

Example test:

```text
Day: Monday
Start: 10:00
End: 16:00
Duration: 90
Buffer: 15
```

Then add a blocked time inside that day.

---

### /owner/calendar

Use it to confirm generated slots.

Expected:

- Slots appear based on availability rules.
- Blocked time does not appear.
- Past time does not appear.
- Already requested/confirmed slots do not appear.

---

### /owner/bookings

Use it to:

```text
Confirm booking
Add meeting link
Reject booking
Cancel booking
Mark completed
Mark no_show
```

When Owner confirms a booking:

```text
lesson_sessions
```

gets a new row with:

```text
status = confirmed
```

When Owner marks completed/no_show, Phase 8 attendance and credit deduction logic is used.

---

## 9) Student URLs to Test / روابط الطالب للاختبار

Login as a student with an active package and remaining credits.

Open:

```text
/student/book
/student/lessons
```

### /student/book

Student can:

- See available slots.
- Choose a slot.
- Send booking request.

Expected rules:

- Booking is blocked if student has no active package.
- Booking is blocked if remaining credits are 0.
- Booking is blocked outside availability.
- Booking is blocked for unavailable time.
- Booking is blocked if slot is already taken.

---

### /student/lessons

Student can:

- See requested lessons.
- See confirmed lessons.
- See meeting link after Owner confirmation.
- Request reschedule to another available slot.

---

## 10) Parent URLs to Test / روابط ولي الأمر للاختبار

Login as Parent with linked child.

Open:

```text
/parent/book
/parent/dashboard
/parent/child/lessons?id={childUserId}
```

### /parent/book

Parent can:

- Choose linked child.
- Choose available slot.
- Send booking request.

Security rule:

```text
Parent can only book for child linked in parent_child_links.
```

---

### /parent/child/lessons?id={childUserId}

Parent can:

- See requested/confirmed lessons for linked child only.
- See meeting link when available.

Try changing `childUserId` to another student.

Expected:

```text
403 unauthorized
```

---

## 11) Double Booking Test / اختبار منع الحجز المكرر

1. Login as Student A.
2. Book slot:

```text
Monday 10:00
```

3. Login as Student B or Parent.
4. Try booking the same slot.

Expected:

```text
Selected slot is already booked.
```

The system blocks overlaps where booking status is:

```text
requested
confirmed
reschedule_requested
rescheduled
```

---

## 12) Credit Link Test / اختبار ربط الرصيد

1. Confirm a booking as Owner.
2. Open:

```text
/owner/bookings
```

3. Mark booking as:

```text
completed
```

Expected:

- Lesson session becomes completed through attendance logic.
- 1 credit is deducted.
- Credit ledger shows `session_deducted`.
- Audit log records action.

Repeat with:

```text
no_show
```

Expected:

- 1 credit is deducted by default.

---

## 13) File Manager Upload Checklist / قائمة رفع الملفات

Upload these exact files/folders:

### Backend

```text
backend/php/shared/BookingCalendar.php
```

### Migration

```text
backend/php/database/migrations/009_real_booking_availability_calendar.sql
```

### Public Owner

```text
web/public/owner/calendar/
web/public/owner/availability/
web/public/owner/bookings/
```

### Public Student

```text
web/public/student/book/
web/public/student/lessons/
```

### Public Parent

```text
web/public/parent/book/
web/public/parent/child/lessons/
web/public/parent/dashboard/index.php
```

### Components

```text
web/components/layout/dashboard_shell.php
```

---

## 14) phpMyAdmin Checklist / قائمة phpMyAdmin

Before running migration:

```text
Export backup
```

Run:

```text
009_real_booking_availability_calendar.sql
```

After running migration, check these tables:

```text
availability_rules
blocked_times
bookings
settings
```

Check settings include:

```text
booking.default_session_duration_minutes
booking.max_days_ahead
booking.reschedule_min_hours_before_lesson
```

---

## 15) Manual Full Test Checklist / قائمة اختبار كاملة

1. Run migration 009.
2. Upload Phase 9 files.
3. Login as Owner.
4. Open `/owner/availability`.
5. Create availability rule.
6. Add blocked time.
7. Open `/owner/calendar`.
8. Confirm slots appear and blocked time is excluded.
9. Login as student.
10. Open `/student/book`.
11. Create booking request.
12. Login as Owner.
13. Open `/owner/bookings`.
14. Confirm booking and add meeting link.
15. Login as student.
16. Open `/student/lessons`.
17. Confirm meeting link appears.
18. Try double booking the same slot.
19. Confirm it is prevented.
20. Login as parent.
21. Open `/parent/book`.
22. Book for linked child.
23. Confirm as Owner.
24. Open `/parent/child/lessons?id={childUserId}`.
25. Confirm parent sees child lessons only.
26. Request reschedule from student lessons.
27. Confirm Owner sees reschedule request.
28. Mark confirmed booking completed.
29. Confirm credit is deducted.
30. Mark another booking no_show.
31. Confirm credit is deducted.
32. Open `/owner/audit-log`.
33. Confirm booking audit events exist.

---

## 16) Known Limitations / القيود الحالية

- Calendar UI is list-based, not a full visual calendar grid yet.
- Clean route `/parent/child/[id]/lessons` is implemented as:

```text
/parent/child/lessons?id={childUserId}
```

- Reschedule policy minimum hours is stored in settings but not fully enforced yet.
- Owner can add availability and blocked times, but edit/delete UI is not built yet.
- Confirming a reschedule creates/updates booking flow, but deeper session reconciliation can be improved later.
- CSRF protection still needs strengthening before production.

---

## 17) Stop Rule / قاعدة التوقف

Stop here. Test Phase 9 fully before moving to Phase 10.

توقف هنا. اختبر Phase 9 بالكامل قبل الانتقال إلى Phase 10.
