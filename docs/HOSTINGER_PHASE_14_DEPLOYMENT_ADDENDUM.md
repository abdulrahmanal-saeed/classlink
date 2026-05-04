# Hostinger Deployment Addendum — Phase 14
# إضافة دليل الرفع على Hostinger — المرحلة 14

> Use this addendum together with:

```text
docs/HOSTINGER_DEPLOYMENT_GUIDE.md
docs/HOSTINGER_PHASE_9_DEPLOYMENT_ADDENDUM.md
docs/HOSTINGER_PHASE_10_DEPLOYMENT_ADDENDUM.md
docs/HOSTINGER_PHASE_11_DEPLOYMENT_ADDENDUM.md
docs/HOSTINGER_PHASE_12_DEPLOYMENT_ADDENDUM.md
docs/HOSTINGER_PHASE_13_DEPLOYMENT_ADDENDUM.md
```

This addendum covers Phase 14 only.

---

## Phase 14 Name / اسم المرحلة

Homework, Speaking Scenarios, Reviews/Tests, Materials

---

## 1) What Phase 14 Adds / ماذا تضيف المرحلة 14

Phase 14 adds the learning assignment system:

```text
Homework creation/submission/correction/result
Speaking scenario creation/submission/feedback/result
Review/test creation/submission/manual override/result
Material creation and assignment
LearningAssignments helper
```

---

## 2) Database Migration / تحديث قاعدة البيانات

Phase 14 requires migration:

```text
backend/php/database/migrations/014_learning_assignment_system.sql
```

Run it in phpMyAdmin after Phase 13.

---

## 3) Files to Upload / الملفات المطلوب رفعها

### Backend

```text
backend/php/shared/LearningAssignments.php
backend/php/database/migrations/014_learning_assignment_system.sql
```

Upload helper to:

```text
/home/<HOSTINGER_USER>/domains/mshabibanabil.com/backend/php/shared/LearningAssignments.php
```

---

### Owner homework pages

```text
web/public/owner/homework/new/index.php
web/public/owner/homework/view/index.php
web/public/owner/homework/submissions/index.php
```

To:

```text
public_html/staging/owner/homework/new/index.php
public_html/staging/owner/homework/view/index.php
public_html/staging/owner/homework/submissions/index.php
```

---

### Student homework pages

```text
web/public/student/homework/view/index.php
web/public/student/homework/result/index.php
```

To:

```text
public_html/staging/student/homework/view/index.php
public_html/staging/student/homework/result/index.php
```

---

### Owner scenario pages

```text
web/public/owner/scenarios/new/index.php
web/public/owner/scenarios/submissions/index.php
```

To:

```text
public_html/staging/owner/scenarios/new/index.php
public_html/staging/owner/scenarios/submissions/index.php
```

---

### Student scenario pages

```text
web/public/student/scenarios/view/index.php
web/public/student/scenarios/result/index.php
```

To:

```text
public_html/staging/student/scenarios/view/index.php
public_html/staging/student/scenarios/result/index.php
```

---

### Owner review/test pages

```text
web/public/owner/reviews/new/index.php
web/public/owner/reviews/results/index.php
```

To:

```text
public_html/staging/owner/reviews/new/index.php
public_html/staging/owner/reviews/results/index.php
```

---

### Student review/test pages

```text
web/public/student/reviews/view/index.php
web/public/student/reviews/result/index.php
```

To:

```text
public_html/staging/student/reviews/view/index.php
public_html/staging/student/reviews/result/index.php
```

---

### Materials

```text
web/public/owner/materials/index.php
web/public/owner/materials/new/index.php
```

To:

```text
public_html/staging/owner/materials/index.php
public_html/staging/owner/materials/new/index.php
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
014_learning_assignment_system.sql
```

8. Click Go.
9. If success appears, continue.
10. If error appears, stop and send the error.

---

## 5) URLs to Test / روابط الاختبار

Owner:

```text
/owner/homework/new
/owner/homework/view?id={homeworkId}
/owner/homework/submissions?id={homeworkId}
/owner/scenarios/new
/owner/scenarios/submissions?id={scenarioId}
/owner/reviews/new
/owner/reviews/results?id={reviewTestId}
/owner/materials
/owner/materials/new
```

Student:

```text
/student/homework/view?id={homeworkId}
/student/homework/result?id={homeworkId}
/student/scenarios/view?id={scenarioId}
/student/scenarios/result?id={scenarioId}
/student/reviews/view?id={reviewTestId}
/student/reviews/result?id={reviewTestId}
/student/materials
```

---

## 6) Manual Test Checklist / قائمة اختبار كاملة

1. Run migration 014.
2. Upload Phase 14 files.
3. Login as Owner.
4. Create homework from `/owner/homework/new`.
5. Publish homework.
6. Login as student.
7. Submit homework from `/student/homework/view?id=...`.
8. Confirm result from `/student/homework/result?id=...`.
9. Confirm result shows selected answer text and correct answer if wrong.
10. Login as Owner.
11. Correct homework from `/owner/homework/submissions?id=...`.
12. Confirm student result shows feedback.
13. Create scenario from `/owner/scenarios/new`.
14. Submit scenario as student from `/student/scenarios/view?id=...`.
15. Add feedback as Owner from `/owner/scenarios/submissions?id=...`.
16. Confirm student sees feedback in `/student/scenarios/result?id=...`.
17. Create review/test from `/owner/reviews/new`.
18. Submit as student from `/student/reviews/view?id=...`.
19. Owner manual override from `/owner/reviews/results?id=...`.
20. Student sees override in `/student/reviews/result?id=...`.
21. Create material from `/owner/materials/new`.
22. Confirm material appears in `/owner/materials` and `/student/materials` if active/assigned.

---

## 7) Known Limitations / القيود الحالية

- Audio recording/upload is still URL/path-based MVP, not full browser recorder.
- File upload for materials is URL/path-based, not real uploader yet.
- Clean `[id]` routes are implemented as query routes.
- Forms use a fixed number of visible question rows.
- Per-question manual override UI is not fully granular yet.
- CSRF protection still needs strengthening before production.

---

## 8) Stop Rule / قاعدة التوقف

Stop here. Test Phase 14 fully before moving to Phase 15.

توقف هنا. اختبر Phase 14 بالكامل قبل الانتقال إلى Phase 15.
