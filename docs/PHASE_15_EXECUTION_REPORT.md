# Phase 15 Execution Report
# تقرير تنفيذ المرحلة 15

## Phase Name / اسم المرحلة

Practice Words, Flashcards, Progress, Streaks, Badges, and Badge Settings

كلمات التدريب، بطاقات المراجعة، التقدم، الاستمرارية، الشارات، وإعدادات الشارات

---

## Goal / الهدف

Build learning engagement features.

بناء خصائص تحفيز ومتابعة التعلم.

---

## Database Migration / تحديث قاعدة البيانات

Phase 15 adds a migration:

```text
backend/php/database/migrations/015_learning_engagement_features.sql
```

---

## Files Created / الملفات التي تم إنشاؤها

### Database

```text
backend/php/database/migrations/015_learning_engagement_features.sql
```

### Backend helper

```text
backend/php/shared/LearningEngagement.php
```

### Student pages

```text
web/public/student/progress/index.php
web/public/student/flashcards/index.php
web/public/student/badges/index.php
```

### Parent pages

```text
web/public/parent/child/badges/index.php
```

### Owner pages

```text
web/public/owner/students/practice-words/index.php
web/public/owner/badges/settings/index.php
```

---

## Files Changed / الملفات التي تم تعديلها

```text
web/components/layout/dashboard_shell.php
web/public/student/homework/view/index.php
web/public/student/scenarios/view/index.php
web/public/student/reviews/view/index.php
```

The student submission pages now record activity logs when the student submits homework, scenarios, or reviews.

---

## Migration 015 Changes / تغييرات Migration 015

### New table: learning_activity_logs

Tracks:

```text
homework_submitted
scenario_submitted
review_taken
flashcards_reviewed
session_completed
level_check_completed
badge_awarded
practice_word_added
```

### badge_definitions expanded

Adds:

```text
trigger_type
required_value
display_order
visibility
color_style
```

### student_badges expanded

Adds:

```text
source_type
source_id
```

### practice_words expanded

Adds:

```text
next_review_at
last_reviewed_at
due_status
```

### flashcard_reviews expanded

Allows ratings:

```text
missed
almost
got_it
again
hard
good
easy
```

Adds:

```text
previous_review_at
```

---

## Default Badges / الشارات الافتراضية

Migration 015 inserts or updates:

```text
First Step
5-Day Streak
10-Day Streak
30-Day Streak
10 Sessions
25 Sessions
Vocab Builder
Speaking Star
Perfect Week
First Level Check Completed
```

---

## Flashcard Logic / منطق بطاقات المراجعة

Implemented:

```text
Got it  -> review in 3 days
Almost  -> review in 1 day
Missed it -> review today/tomorrow, currently 12 hours
```

Mastery logic:

```text
Got it increases mastery by 1
Almost keeps mastery at least 1
Missed it decreases mastery by 1 down to 0
Mastery 5 marks the word as mastered
```

---

## Activity Log / سجل النشاط

Implemented sources:

```text
Homework submitted
Scenario submitted
Review/test taken
Flashcards reviewed
Practice word added
Badge awarded
```

Session completed and level check completed are supported as activity types in the schema and helper, but need to be called from their related future/patch flows.

---

## Badge Awarding / منح الشارات

Automatic awarding is handled by:

```text
engagement_award_badges(studentId)
```

Badges are awarded based on active badge definitions only.

If Owner disables a badge:

```text
is_active = 0
```

then it will not be awarded automatically.

---

## Implemented Student URLs / روابط الطالب

```text
/student/progress
/student/flashcards
/student/badges
/student/practice-words
```

Existing practice words page remains available and flashcards now use the expanded due dates.

---

## Implemented Parent URLs / روابط ولي الأمر

```text
/parent/child/progress?id={childUserId}
/parent/child/badges?id={childUserId}
```

Parent child progress already existed and now can be used with engagement data. Child badges page was added in this phase.

Security:

```text
Parent can only see linked child badges/progress.
```

---

## Implemented Owner URLs / روابط المالك

```text
/owner/students/practice-words?id={studentUserId}
/owner/badges/settings
```

Owner can:

```text
Add practice words
View student word mastery/due status
Edit badge name
Edit description
Edit icon/emoji
Edit trigger type
Edit required value
Enable/disable badge
Edit display order
Edit visibility
Edit color/style
```

---

## Navigation / التنقل

Student sidebar now includes:

```text
Progress
Flashcards
Badges
```

Owner sidebar now includes:

```text
Badge Settings
```

---

## Known Limitations / القيود الحالية

- Session completed activity is supported but not yet wired from the attendance/session completion flow.
- Level check completed activity is supported but not yet wired from the level check review flow.
- Perfect Week uses activity count logic; a more exact calendar-week implementation can be improved later.
- Parent child progress page was not fully redesigned in this phase, but child badges were added.
- Flashcard UI reviews due cards; it does not yet include flip animation.
- CSRF protection still needs strengthening before production.

---

## Manual Test Checklist / قائمة الاختبار اليدوي

1. Run migration:

```text
backend/php/database/migrations/015_learning_engagement_features.sql
```

2. Login as Owner.
3. Add practice words:

```text
/owner/students/practice-words?id={studentUserId}
```

4. Login as Student.
5. Open:

```text
/student/flashcards
```

6. Review a card with Got it.
7. Confirm next review is about 3 days later.
8. Review another card with Almost.
9. Confirm next review is about 1 day later.
10. Review another card with Missed it.
11. Confirm next review is today/tomorrow.
12. Open:

```text
/student/progress
```

13. Confirm activity log and streak display.
14. Submit homework/scenario/review.
15. Confirm activity appears in progress.
16. Open:

```text
/student/badges
```

17. Confirm earned/locked badges display.
18. Login as Owner.
19. Open:

```text
/owner/badges/settings
```

20. Disable a badge.
21. Trigger the requirement again.
22. Confirm disabled badge does not award.
23. Login as Parent.
24. Open:

```text
/parent/child/badges?id={childUserId}
```

25. Confirm only linked child badges are visible.

---

## Stop Point / نقطة التوقف

Stop here. Test this phase before continuing.

توقف هنا. اختبر هذه المرحلة قبل الانتقال للمرحلة التالية.
