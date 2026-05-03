# Phase 8 Execution Report
# تقرير تنفيذ المرحلة 8

## Phase Name / اسم المرحلة

Lesson Credits, Packages, Attendance, and Balance

أرصدة الحصص، الباقات، الحضور، والرصيد

---

## Goal / الهدف

Build core package balance and lesson credit tracking.

بناء نظام أساسي لتتبع رصيد الباقات والحصص.

---

## Files Created / الملفات التي تم إنشاؤها

### Database / قاعدة البيانات

```text
backend/php/database/migrations/008_lesson_credits_attendance_balance.sql
```

### Backend helper / أدوات الباك إند

```text
backend/php/shared/LessonCredits.php
```

### Owner pages / صفحات المالك

```text
web/public/owner/packages/index.php
web/public/owner/students/credits/index.php
```

### Student page / صفحة الطالب

```text
web/public/student/balance/index.php
```

### Parent page / صفحة ولي الأمر

```text
web/public/parent/child/balance/index.php
```

---

## Files Changed / الملفات التي تم تعديلها

```text
web/components/layout/dashboard_shell.php
web/public/parent/dashboard/index.php
```

Changes:

- Owner sidebar now includes Packages & Credits.
- Student sidebar now includes My Balance.
- Parent dashboard now includes View Balance button for each linked child.

---

## Database Changes / تغييرات قاعدة البيانات

Migration 008 updates:

```text
lesson_credit_transactions
lesson_sessions
settings
```

Adds:

```text
attendance_records
```

---

## Credit Transaction Types / أنواع معاملات الرصيد

Phase 8 supports new types:

```text
purchase_grant
session_deducted
cancellation_return
manual_adjustment
refund_adjustment
```

For compatibility, the enum still allows old Phase 2 values:

```text
add
deduct
refund
adjust
```

This prevents older approval code or historic rows from breaking.

---

## Session Statuses / حالات الحصص

Implemented statuses:

```text
planned
confirmed
completed
canceled_on_time
canceled_late
rescheduled
no_show
```

Old statuses are migrated:

```text
scheduled -> planned
cancelled -> canceled_on_time
```

---

## Attendance Records / سجلات الحضور

New table:

```text
attendance_records
```

Stores:

```text
session_id
student_user_id
package_id
status
credit_action
credit_transaction_id
marked_by_user_id
notes
marked_at
```

---

## Credit Rules / قواعد الرصيد

Default settings inserted:

```text
credits.no_show_deducts_credit = true
credits.late_cancellation_deducts_credit = true
credits.on_time_cancellation_keeps_credit = true
credits.default_single_session_credits = 1
credits.default_monthly_plan_credits = 8
credits.default_bundle_credits = 20
credits.late_cancellation_hours = 24
```

Rules implemented in helper:

- Completed session deducts 1 credit.
- No-show deducts 1 credit by default.
- Late cancellation deducts 1 credit by default.
- On-time cancellation keeps credit if no deduction happened.
- On-time cancellation can return credit if credit was already deducted.
- Owner manual adjustment can add or deduct credits with reason.

---

## Owner Packages / صفحة الباقات للمالك

Implemented:

```text
/owner/packages
```

Shows:

- Student.
- Package name.
- Total credits.
- Remaining credits.
- Used credits.
- Status.
- Link to student credits page.

---

## Owner Student Credits / صفحة رصيد طالب للمالك

Implemented:

```text
/owner/students/credits?id={studentUserId}
```

This is the practical route equivalent of:

```text
/owner/students/[id]/credits
```

Owner can:

- See package summary.
- See total/used/remaining credits.
- See credit ledger.
- Create planned session.
- Mark session status/attendance.
- Manually adjust credits with reason.

Audit log records:

```text
lesson_credit_manual_adjustment
lesson_attendance_marked
```

---

## Student Balance / صفحة رصيد الطالب

Implemented:

```text
/student/balance
```

Student can see only own:

- Total credits.
- Used credits.
- Remaining credits.
- Packages.
- Sessions.
- Credit ledger.

---

## Parent Child Balance / صفحة رصيد الطفل لولي الأمر

Implemented:

```text
/parent/child/balance?id={childUserId}
```

This is the practical route equivalent of:

```text
/parent/child/[id]/balance
```

Security:

- Parent can only access child balance if `parent_child_links.parent_user_id` matches logged-in parent.
- Unauthorized access returns 403.

---

## Helper Functions / وظائف المساعدة

Added:

```text
credits_student_packages
credits_active_package
credits_transactions_for_student
credits_all_packages
credits_add_transaction
credits_manual_adjust
credits_create_session
credits_mark_attendance
credits_student_summary
credits_parent_can_access_child
```

---

## Known Limitations / القيود الحالية

- Clean routes `/owner/students/[id]/credits` and `/parent/child/[id]/balance` are implemented as query routes for now.
- Phase 7 helper still may create `add` transaction type; migration 008 keeps old values allowed for compatibility.
- Settings UI for editing credit rules is not fully built yet, but settings are stored in the `settings` table.
- Session scheduling UX is simple; full calendar/booking workflow comes later.
- If a session is marked completed twice, helper prevents double deduction using `credit_deducted`.
- CSRF protection still needs to be strengthened before production.

---

## Manual Test Checklist / قائمة الاختبار اليدوي

1. Apply migration:

```text
backend/php/database/migrations/008_lesson_credits_attendance_balance.sql
```

2. Approve a paid onboarding from Phase 7 to create package.
3. Open Owner packages:

```text
/owner/packages
```

4. Open student credit page:

```text
/owner/students/credits?id={studentUserId}
```

5. Create planned session.
6. Mark session as:

```text
completed
```

7. Confirm 1 credit is deducted.
8. Create another planned session.
9. Mark it as:

```text
no_show
```

10. Confirm 1 credit is deducted.
11. Create another planned session.
12. Mark it as:

```text
canceled_on_time
```

13. Confirm credit is kept/not deducted.
14. Use manual adjustment:

```text
+1 credit
-1 credit
```

15. Confirm ledger shows every change.
16. Open audit log:

```text
/owner/audit-log
```

17. Confirm adjustment and attendance audit actions exist.
18. Login as student and open:

```text
/student/balance
```

19. Confirm student sees own balance only.
20. Login as parent and open:

```text
/parent/dashboard
```

21. Click View Balance for linked child.
22. Confirm parent sees linked child balance only.
23. Try changing child id in URL to another student.
24. Confirm access is blocked.

---

## Stop Point / نقطة التوقف

Stop here. Test this phase before continuing.

توقف هنا. اختبر هذه المرحلة قبل الانتقال للمرحلة التالية.
