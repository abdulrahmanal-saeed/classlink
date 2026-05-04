# Hostinger Deployment Addendum — Phase 15
# إضافة دليل الرفع على Hostinger — المرحلة 15

> Use this addendum together with:

```text
docs/HOSTINGER_DEPLOYMENT_GUIDE.md
docs/HOSTINGER_PHASE_9_DEPLOYMENT_ADDENDUM.md
docs/HOSTINGER_PHASE_10_DEPLOYMENT_ADDENDUM.md
docs/HOSTINGER_PHASE_11_DEPLOYMENT_ADDENDUM.md
docs/HOSTINGER_PHASE_12_DEPLOYMENT_ADDENDUM.md
docs/HOSTINGER_PHASE_13_DEPLOYMENT_ADDENDUM.md
docs/HOSTINGER_PHASE_14_DEPLOYMENT_ADDENDUM.md
```

This addendum covers Phase 15 only.

---

## Phase 15 Name / اسم المرحلة

Practice Words, Flashcards, Progress, Streaks, Badges, and Badge Settings

---

## 1) What Phase 15 Adds / ماذا تضيف المرحلة 15

Phase 15 adds:

```text
Practice words management by Owner
Flashcard review system
Spaced repetition basics
Activity log
Student progress dashboard
Streak calculation
Badge settings
Automatic student badge awarding
Student badges page
Parent child badges view
```

---

## 2) Database Migration / تحديث قاعدة البيانات

Phase 15 requires migration:

```text
backend/php/database/migrations/015_learning_engagement_features.sql
```

Run it in phpMyAdmin after Phase 14.

---

## 3) Files to Upload / الملفات المطلوب رفعها

### Backend

```text
backend/php/shared/LearningEngagement.php
backend/php/database/migrations/015_learning_engagement_features.sql
```

Upload helper to:

```text
/home/<HOSTINGER_USER>/domains/mshabibanabil.com/backend/php/shared/LearningEngagement.php
```

---

### Updated shared layout

```text
web/components/layout/dashboard_shell.php
```

To:

```text
/home/<HOSTINGER_USER>/domains/mshabibanabil.com/web/components/layout/dashboard_shell.php
```

---

### Student pages

```text
web/public/student/progress/index.php
web/public/student/flashcards/index.php
web/public/student/badges/index.php
```

To:

```text
public_html/staging/student/progress/index.php
public_html/staging/student/flashcards/index.php
public_html/staging/student/badges/index.php
```

---

### Updated student submission pages

```text
web/public/student/homework/view/index.php
web/public/student/scenarios/view/index.php
web/public/student/reviews/view/index.php
```

To:

```text
public_html/staging/student/homework/view/index.php
public_html/staging/student/scenarios/view/index.php
public_html/staging/student/reviews/view/index.php
```

---

### Parent page

```text
web/public/parent/child/badges/index.php
```

To:

```text
public_html/staging/parent/child/badges/index.php
```

---

### Owner pages

```text
web/public/owner/students/practice-words/index.php
web/public/owner/badges/settings/index.php
```

To:

```text
public_html/staging/owner/students/practice-words/index.php
public_html/staging/owner/badges/settings/index.php
```

---

## 4) phpMyAdmin Steps / خطوات phpMyAdmin

1. Open Hostinger hPanel.
2. Open Databases.
3. Open phpMyAdmin.
4. Select staging database.
5. Export backup first.
6. Open SQL tab.
7. Paste contents of:

```text
015_learning_engagement_features.sql
```

8. Click Go.
9. If success appears, continue.
10. If error appears, stop and send the error.

---

## 5) URLs to Test / روابط الاختبار

Student:

```text
/student/progress
/student/flashcards
/student/badges
/student/practice-words
```

Owner:

```text
/owner/students/practice-words?id={studentUserId}
/owner/badges/settings
```

Parent:

```text
/parent/child/progress?id={childUserId}
/parent/child/badges?id={childUserId}
```

---

## 6) Flashcard Test / اختبار بطاقات المراجعة

1. Login as Owner.
2. Open:

```text
/owner/students/practice-words?id={studentUserId}
```

3. Add three words.
4. Login as Student.
5. Open:

```text
/student/flashcards
```

6. Review one word with:

```text
Got it
```

Expected:

```text
next_review_at = around 3 days later
```

7. Review one word with:

```text
Almost
```

Expected:

```text
next_review_at = around 1 day later
```

8. Review one word with:

```text
Missed it
```

Expected:

```text
next_review_at = today/tomorrow, currently about 12 hours later
```

---

## 7) Activity Log Test / اختبار سجل النشاط

1. Submit homework as student.
2. Submit scenario as student.
3. Submit review/test as student.
4. Review flashcards.
5. Open:

```text
/student/progress
```

Expected:

```text
Recent activity table shows homework_submitted, scenario_submitted, review_taken, flashcards_reviewed
```

---

## 8) Badge Settings Test / اختبار إعدادات الشارات

1. Login as Owner.
2. Open:

```text
/owner/badges/settings
```

3. Edit a badge required value.
4. Disable a badge.
5. Trigger the badge requirement as student.

Expected:

```text
Disabled badge is not awarded.
```

6. Enable badge again.
7. Trigger requirement again.

Expected:

```text
Badge can be awarded if requirements are met.
```

---

## 9) Parent Child Badge Test / اختبار شارات الطفل لولي الأمر

1. Login as Parent.
2. Open:

```text
/parent/child/badges?id={linkedChildId}
```

Expected:

```text
Parent sees linked child badges only.
```

3. Try unlinked child ID.

Expected:

```text
Access is blocked.
```

---

## 10) Known Limitations / القيود الحالية

- Session completed and level check completed activity types are supported but not fully wired from their related flows yet.
- Perfect Week uses activity count; exact calendar-week validation can be improved later.
- Flashcards do not have flip animation yet.
- Parent progress page existed already; this phase adds badges and shared engagement logic.
- CSRF protection still needs strengthening before production.

---

## 11) Stop Rule / قاعدة التوقف

Stop here. Test Phase 15 fully before moving to Phase 16.

توقف هنا. اختبر Phase 15 بالكامل قبل الانتقال إلى Phase 16.
