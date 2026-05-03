# Phase 11 Execution Report
# تقرير تنفيذ المرحلة 11

## Phase Name / اسم المرحلة

Parent Portal for Child Learners

بوابة ولي الأمر للطلاب الأطفال

---

## Goal / الهدف

Build the parent portal for child learners.

بناء بوابة ولي الأمر لمتابعة الأطفال المرتبطين بحسابه.

---

## Important Database Note / ملاحظة مهمة بخصوص قاعدة البيانات

Phase 11 does not add a new migration.

هذه المرحلة لا تحتاج migration جديد لأنها تعتمد على جداول موجودة من المراحل السابقة:

```text
users
parent_child_links
student_profiles
user_profiles
bookings
lesson_sessions
lesson_packages
lesson_credit_transactions
homeworks
homework_submissions
level_check_attempts
student_intake_forms
student_badges
badge_definitions
notifications
```

---

## Files Created / الملفات التي تم إنشاؤها

### Backend helper / أدوات الباك إند

```text
backend/php/shared/ParentPortal.php
```

### Parent pages / صفحات ولي الأمر

```text
web/public/parent/children/index.php
web/public/parent/child/view/index.php
web/public/parent/child/progress/index.php
web/public/parent/child/homework/index.php
web/public/parent/child/session-notes/index.php
web/public/parent/notifications/index.php
web/public/parent/contact/index.php
```

---

## Files Changed / الملفات التي تم تعديلها

```text
web/public/parent/dashboard/index.php
web/components/layout/dashboard_shell.php
```

---

## Existing Parent Pages Used / صفحات موجودة مستخدمة من مراحل سابقة

These pages already existed from Phase 8/9 and are now part of the parent portal:

```text
web/public/parent/book/index.php
web/public/parent/child/lessons/index.php
web/public/parent/child/balance/index.php
```

---

## Implemented Parent URLs / روابط ولي الأمر المنفذة

Implemented or connected:

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

Practical query routes are used instead of clean `[id]` routes for now.

---

## Parent Dashboard Shows / محتوى لوحة ولي الأمر

The dashboard now shows:

```text
Linked children list
Child dashboard shortcut
Upcoming lesson
Homework status
Teacher notes
Payment/package balance
Level/literacy check result
Recommended first lesson
WhatsApp/contact shortcut
Badges/streak for child
Progress summary
```

---

## Security / الأمان

All parent portal pages use:

```php
require_role('parent')
```

All child-specific pages call:

```php
parent_portal_require_child(parentId, childId)
```

This checks:

```text
parent_child_links.parent_user_id = current logged-in parent
parent_child_links.child_user_id = requested child id
parent_child_links.status = active
```

If the child is not linked, the page returns 403 and stops.

---

## ParentPortal Helper / ملف ParentPortal.php

Added centralized functions:

```text
parent_portal_children
parent_portal_can_access_child
parent_portal_require_child
parent_portal_child_profile
parent_portal_upcoming_lesson
parent_portal_latest_level_check
parent_portal_homeworks
parent_portal_session_notes
parent_portal_progress
parent_portal_child_dashboard
parent_portal_notifications
parent_portal_first_child_id
```

Reason:

```text
Keep parent-child access control centralized and safe.
```

---

## Page Details / تفاصيل الصفحات

### /parent/dashboard

Parent dashboard with child overview and first child summary.

Shows:

- Linked children.
- Child dashboard action.
- Lessons action.
- Balance action.
- Upcoming lesson.
- Homework status.
- Level/literacy result.
- Package balance.
- Progress summary.
- Badges and streak.
- Recent teacher notes.
- Contact teacher button.

---

### /parent/children

Shows all children linked to the parent account.

Actions:

```text
Open child dashboard
Progress
Lessons
```

---

### /parent/child/view?id={childUserId}

Child dashboard page for a single linked child.

Shows:

```text
Level
Remaining credits
Streak
Upcoming lesson
Homework
Level/literacy check
Teacher notes
```

---

### /parent/child/progress?id={childUserId}

Shows:

```text
Current level
Completed sessions
Submitted homework
Streak
Level/literacy check
Recommended first lesson
Badges
Practice words due
```

---

### /parent/child/homework?id={childUserId}

Shows child homework list and status.

Includes:

```text
Title
Due date
Submission status
Feedback when available
```

---

### /parent/child/session-notes?id={childUserId}

Shows child teacher/session notes.

---

### /parent/child/lessons?id={childUserId}

Already existed from Phase 9 and remains used for child lessons.

---

### /parent/child/balance?id={childUserId}

Already existed from Phase 8 and remains used for child package balance.

---

### /parent/book

Already existed from Phase 9. Parent can request lesson booking for linked child only.

---

### /parent/notifications

Shows notifications for the parent account.

---

### /parent/contact

Shows teacher/admin contact shortcuts:

```text
WhatsApp shortcut
Email shortcut
Linked children list
```

Current WhatsApp and email are placeholders and should later be made editable from settings.

---

## Acceptance Criteria Status / حالة معايير القبول

### Parent sees only linked child data

Done.

All child pages enforce `parent_child_links` ownership.

### Parent sees child package balance

Done through:

```text
/parent/child/balance?id={childUserId}
/parent/dashboard
```

### Parent sees upcoming lessons

Done through dashboard and lessons page.

### Parent can request/book/reschedule lesson

Booking exists through:

```text
/parent/book
```

Reschedule is partially available in student lessons flow. Parent-specific reschedule UI can be improved in a later patch.

### Parent sees homework status and teacher notes

Done through:

```text
/parent/child/homework?id={childUserId}
/parent/child/session-notes?id={childUserId}
/parent/dashboard
```

---

## Known Limitations / القيود الحالية

- Clean routes like `/parent/child/[id]` are implemented as query routes like `/parent/child/view?id=...` for now.
- Parent-specific reschedule UI is not fully implemented yet, but booking and lessons views exist.
- Contact WhatsApp/email are placeholders and should later be moved to Owner settings.
- Notifications are displayed but not marked as read yet.
- Streak logic currently uses lightweight existing activity/notification logic inherited from StudentPortal.
- CSRF protection still needs strengthening before production.

---

## Manual Test Checklist / قائمة الاختبار اليدوي

1. Login as demo parent.
2. Open:

```text
/parent/dashboard
```

3. Confirm linked child/children appear.
4. Open:

```text
/parent/children
```

5. Open child dashboard:

```text
/parent/child/view?id={linkedChildId}
```

6. Open child progress:

```text
/parent/child/progress?id={linkedChildId}
```

7. Open child lessons:

```text
/parent/child/lessons?id={linkedChildId}
```

8. Open child homework:

```text
/parent/child/homework?id={linkedChildId}
```

9. Open child session notes:

```text
/parent/child/session-notes?id={linkedChildId}
```

10. Open child balance:

```text
/parent/child/balance?id={linkedChildId}
```

11. Open:

```text
/parent/book
```

12. Book a lesson for linked child.
13. Open:

```text
/parent/notifications
/parent/contact
```

14. Try opening unlinked child ID:

```text
/parent/child/view?id={unlinkedChildId}
/parent/child/progress?id={unlinkedChildId}
/parent/child/homework?id={unlinkedChildId}
/parent/child/session-notes?id={unlinkedChildId}
/parent/child/balance?id={unlinkedChildId}
```

15. Confirm access is blocked.
16. Confirm mobile layout.

---

## Stop Point / نقطة التوقف

Stop here. Test this phase before continuing.

توقف هنا. اختبر هذه المرحلة قبل الانتقال للمرحلة التالية.
