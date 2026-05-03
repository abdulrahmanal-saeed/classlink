# Phase 9 Execution Report
# تقرير تنفيذ المرحلة 9

## Phase Name / اسم المرحلة

Real Booking Availability Calendar

تقويم حقيقي للتوفر والحجوزات

---

## Goal / الهدف

Build a real availability and booking calendar.

بناء تقويم حقيقي لإدارة أوقات التوفر وطلبات الحجز.

---

## Files Created / الملفات التي تم إنشاؤها

### Database / قاعدة البيانات

```text
backend/php/database/migrations/009_real_booking_availability_calendar.sql
```

### Backend helper / أدوات الباك إند

```text
backend/php/shared/BookingCalendar.php
```

### Owner pages / صفحات المالك

```text
web/public/owner/calendar/index.php
web/public/owner/availability/index.php
web/public/owner/bookings/index.php
```

### Student pages / صفحات الطالب

```text
web/public/student/book/index.php
web/public/student/lessons/index.php
```

### Parent pages / صفحات ولي الأمر

```text
web/public/parent/book/index.php
web/public/parent/child/lessons/index.php
```

---

## Files Changed / الملفات التي تم تعديلها

```text
web/components/layout/dashboard_shell.php
web/public/parent/dashboard/index.php
```

Changes:

- Owner sidebar now includes Calendar, Availability, and Bookings.
- Student sidebar now includes Book Lesson and My Lessons.
- Parent sidebar now includes Book for Child.
- Parent dashboard now includes View Lessons link for each linked child.

---

## Database Changes / تغييرات قاعدة البيانات

Migration 009 updates:

```text
availability_rules
blocked_times
bookings
settings
```

### availability_rules additions

```text
session_duration_minutes
buffer_minutes
max_sessions_per_day
notes
```

### blocked_times additions

```text
is_full_day
```

### bookings additions

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

### Booking statuses

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

---

## Settings / الإعدادات

Migration 009 inserts default booking settings:

```text
booking.default_session_duration_minutes = 90
booking.default_buffer_minutes = 0
booking.max_days_ahead = 30
booking.allow_parent_booking = true
booking.allow_student_booking = true
booking.reschedule_min_hours_before_lesson = 24
booking.default_meeting_link = empty
```

---

## Owner Availability / توفر المالك

Implemented:

```text
/owner/availability
```

Owner can:

- Add weekly working day rule.
- Set start/end times.
- Set session duration.
- Set buffer time.
- Set optional max sessions per day.
- Add blocked/unavailable times.

---

## Owner Calendar / تقويم المالك

Implemented:

```text
/owner/calendar
```

Owner can see:

- Next generated available slots.
- Latest bookings.
- Booking status.
- Meeting link if available.

Generated slots respect:

```text
availability rules
blocked times
existing requested/confirmed bookings
past time prevention
```

---

## Owner Bookings / حجوزات المالك

Implemented:

```text
/owner/bookings
```

Owner can:

- View all booking requests.
- Confirm requested bookings.
- Add meeting link.
- Reject booking.
- Cancel booking.
- Mark completed.
- Mark no-show.

Confirming a booking creates a confirmed lesson session in:

```text
lesson_sessions
```

Marking completed/no_show calls Phase 8 attendance/credit logic and can deduct credits.

---

## Student Booking / حجز الطالب

Implemented:

```text
/student/book
```

Student can:

- See available slots.
- Create booking request.
- Add optional note.

Rules:

- Student must have active package.
- Student must have remaining credits.
- Slot must be inside availability.
- Slot must not be blocked.
- Slot must not already be taken.

---

## Student Lessons / حصص الطالب

Implemented:

```text
/student/lessons
```

Student can:

- See requested and confirmed lessons.
- See meeting link when confirmed.
- Request reschedule to an available slot.

---

## Parent Booking / حجز ولي الأمر

Implemented:

```text
/parent/book
```

Parent can:

- Choose linked child.
- See available slots.
- Create booking request for child.

Security:

```text
Parent can only book for children linked through parent_child_links.
```

---

## Parent Child Lessons / حصص الطفل لولي الأمر

Implemented:

```text
/parent/child/lessons?id={childUserId}
```

This is the practical query route equivalent of:

```text
/parent/child/[id]/lessons
```

Parent can see lessons only for linked children.

Unauthorized child ID returns 403.

---

## Double Booking Prevention / منع الحجز المكرر

The helper prevents overlapping bookings where status is:

```text
requested
confirmed
reschedule_requested
rescheduled
```

Overlap check uses:

```text
start_at < selected_end AND end_at > selected_start
```

---

## Package/Credit Link / ربط الباقة والرصيد

Booking request requires:

```text
active lesson package
remaining_credits > 0
```

Booking stores:

```text
package_id
student_user_id
parent_user_id if parent booking
requested_by_user_id
```

Confirmed booking creates:

```text
lesson_sessions.status = confirmed
lesson_sessions.package_id = booking.package_id
```

Credit deduction happens later when Owner marks session/booking as:

```text
completed
no_show
```

through Phase 8 attendance rules.

---

## Audit Log / سجل المراجعة

Audit actions added:

```text
availability_rule_created
calendar_unavailable_time_created
booking_requested
booking_confirmed
booking_status_updated
booking_reschedule_requested
```

---

## Important URLs / روابط مهمة

Owner:

```text
/owner/calendar
/owner/availability
/owner/bookings
```

Student:

```text
/student/book
/student/lessons
```

Parent:

```text
/parent/book
/parent/child/lessons?id={childUserId}
```

---

## Known Limitations / القيود الحالية

- Clean route `/parent/child/[id]/lessons` is implemented as `/parent/child/lessons?id=...` for now.
- Reschedule policy is basic; it checks slot availability but does not yet enforce minimum hours before lesson.
- Owner availability delete/edit buttons are not added yet; current version supports adding rules and blocks.
- Owner confirmation creates a new lesson session. If a rescheduled confirmed booking already had a previous session, deeper session reconciliation can be improved later.
- Calendar UI is list-based, not a visual grid calendar yet.
- Meeting link is stored on booking and copied into session notes.
- CSRF protection still needs to be strengthened before production.

---

## Manual Test Checklist / قائمة الاختبار اليدوي

1. Apply migration:

```text
backend/php/database/migrations/009_real_booking_availability_calendar.sql
```

2. Login as Owner.
3. Open:

```text
/owner/availability
```

4. Add availability rule, for example:

```text
Monday 10:00 → 16:00
Session duration: 90
Buffer: 15
```

5. Add blocked time inside that range.
6. Open:

```text
/owner/calendar
```

7. Confirm generated slots appear and blocked time is excluded.
8. Login as student with active package and remaining credits.
9. Open:

```text
/student/book
```

10. Book an available slot.
11. Open as Owner:

```text
/owner/bookings
```

12. Confirm the request and add meeting link.
13. Login as student.
14. Open:

```text
/student/lessons
```

15. Confirm lesson appears with confirmed status and meeting link.
16. Try booking same slot again with another student/parent.
17. Confirm double booking is prevented.
18. Login as Parent.
19. Open:

```text
/parent/book
```

20. Book for linked child.
21. Confirm as Owner.
22. Open:

```text
/parent/child/lessons?id={childUserId}
```

23. Confirm parent sees linked child's lessons only.
24. Request reschedule from student lessons.
25. Confirm Owner sees reschedule request in bookings.
26. Mark a confirmed booking as completed.
27. Confirm Phase 8 credit deduction happens.
28. Mark another booking as no_show.
29. Confirm credit deduction happens.
30. Open audit log:

```text
/owner/audit-log
```

31. Confirm booking audit actions exist.

---

## Stop Point / نقطة التوقف

Stop here. Test this phase before continuing.

توقف هنا. اختبر هذه المرحلة قبل الانتقال للمرحلة التالية.
