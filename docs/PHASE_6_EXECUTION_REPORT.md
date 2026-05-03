# Phase 6 Execution Report
# تقرير تنفيذ المرحلة 6

## Phase Name / اسم المرحلة

Adult Level Check and Child Literacy Check

اختبار مستوى البالغين واختبار القراءة والكتابة للأطفال

---

## What was built / ما الذي تم بناؤه

Phase 6 built the adult Arabic level check, child literacy check, uploads validation, auto scoring, suggested level placement, and Owner final review pages.

قامت المرحلة 6 ببناء اختبار مستوى البالغين، واختبار قراءة وكتابة الأطفال، والتحقق من الملفات المرفوعة، والتصحيح الآلي، والمستوى المقترح، وصفحات مراجعة المالك النهائية.

---

## Files Created / الملفات التي تم إنشاؤها

### Database / قاعدة البيانات

```text
backend/php/database/migrations/006_level_check_foundation.sql
```

### Backend helpers / أدوات الباك إند

```text
backend/php/shared/LevelCheck.php
```

### Public pages / الصفحات العامة

```text
web/public/level-check/index.php
web/public/level-check-thank-you/index.php
```

### Owner pages / صفحات المالك

```text
web/public/owner/level-checks/index.php
web/public/owner/level-checks/view/index.php
```

---

## Files Changed / الملفات التي تم تعديلها

```text
web/public/student-form/index.php
web/public/level-check-intro/index.php
web/components/layout/dashboard_shell.php
```

Changes:

- Student form now redirects to `/level-check-intro?intakeId={id}`.
- Level check intro now uses `intakeId` instead of checkout reference.
- Owner sidebar now includes `/owner/level-checks`.

---

## Database Changes / تغييرات قاعدة البيانات

Migration 006 updates:

```text
level_check_attempts
level_check_answers
level_check_uploads
student_intake_forms
```

Added support for:

```text
intake_form_id
checkout_reference
vocabulary_score
sentence_score
reading_score
letter_score
suggested_level
recommended_first_lesson
manual_score
teacher_notes
upload MIME and size metadata
```

---

## Public Routes / المسارات العامة

Implemented:

```text
/level-check-intro?intakeId={id}
/level-check?intakeId={id}
/level-check-thank-you?attemptId={id}
```

---

## Owner Routes / مسارات المالك

Implemented:

```text
/owner/level-checks
/owner/level-checks/view?id=...
```

The requested clean route `/owner/level-checks/{id}` can be added later through `.htaccess` if needed.

---

## Adult Level Check / اختبار مستوى البالغين

Sections built:

1. Self Assessment
2. Vocabulary MCQ — 10 auto-graded questions
3. Sentence Building — 5 auto-graded questions
4. Reading Comprehension — short text + 3 questions
5. Writing — manual review
6. Speaking — audio upload

Auto score placement:

```text
0–30% = A0 Complete Beginner
31–55% = A1
56–75% = A2
76–90% = Strong A2 / B1 Activation
91–100% = B1 / Needs Speaking Assessment
```

Important note is shown in review: high auto score + weak speaking should not over-place the student. Owner final review decides.

---

## Child Literacy Check / اختبار قراءة وكتابة الأطفال

Sections built:

1. Parent questions
2. Letter recognition — 10 auto-graded questions
3. Similar letters
4. Reading audio upload
5. Writing upload/photo
6. Dictation upload/photo

Suggested literacy levels:

```text
Level 0: cannot recognize letters
Level 1: recognizes some letters
Level 2: recognizes letters but struggles with connections
Level 3: can read simple words
Level 4: can read/write simple sentences with support
```

---

## Upload Rules / قواعد رفع الملفات

Audio:

```text
mp3, wav, m4a, webm
Max: 25MB
```

Images/PDF:

```text
jpg, jpeg, png, pdf
Max: 10MB
```

The system validates:

- File extension
- MIME type
- File size

Uploaded files are stored under:

```text
uploads/level-checks/{attemptId}/
```

Important deployment note:

```text
The uploads folder is ignored by Git. Create it on the server and make sure PHP can write to it.
```

---

## Owner Review / مراجعة المالك

Adult review page shows:

- Student info
- Plan purchased
- Intake answers
- Auto score
- Vocabulary score
- Sentence building score
- Reading score
- Writing answer
- Speaking recording upload
- Suggested level
- Recommended first lesson
- Manual score
- Final level dropdown
- Teacher notes
- Mark reviewed

Child review page shows:

- Parent/child info
- Parent answers
- Letter recognition score
- Similar letter mistakes
- Reading audio
- Writing upload
- Dictation upload
- Suggested literacy level
- Recommended first lesson
- Final literacy level dropdown
- Teacher notes
- Mark reviewed

---

## Status Updates / تحديثات الحالة

After level check submit:

```text
purchases.level_check_status = submitted
level_check_attempts.status = submitted
```

After Owner review:

```text
level_check_attempts.status = reviewed
purchases.level_check_status = reviewed
```

Audit log records:

```text
level_check_submitted
level_check_reviewed
```

---

## Security Checks / فحوصات الأمان

- Level check loads intake by ID from saved onboarding form.
- Upload file extensions are validated.
- MIME types are validated.
- File size limits are enforced.
- Owner review pages are protected by `owner_teacher` role.
- Final level is decided by Owner review, not auto score only.
- Audit log records review actions.

---

## Known Limitations / القيود الحالية

- Audio recording in browser is not built yet; current version supports upload.
- Owner clean route `/owner/level-checks/{id}` is not added yet; `/owner/level-checks/view?id=...` works.
- Upload folder must be created and made writable on the server.
- Current questions are starter/demo question sets and can later be moved into a dynamic question bank.
- No advanced anti-cheating or attempt lock yet.
- CSRF protection still needs to be strengthened before production.

---

## Manual Test Checklist / قائمة الاختبار اليدوي

1. Apply migration:

```text
backend/php/database/migrations/006_level_check_foundation.sql
```

2. Make sure upload folder exists and is writable:

```text
uploads/level-checks
```

3. Complete checkout and student form.
4. Confirm redirect to:

```text
/level-check-intro?intakeId={id}
```

5. Complete adult check.
6. Upload speaking audio.
7. Confirm redirect to:

```text
/level-check-thank-you?attemptId={id}
```

8. Create another checkout/student form for child learner.
9. Complete child literacy check.
10. Upload reading audio, writing image, and dictation image.
11. Login as Owner.
12. Open:

```text
/owner/level-checks
```

13. Open a review page:

```text
/owner/level-checks/view?id=...
```

14. Review adult attempt and mark reviewed.
15. Review child attempt and mark reviewed.
16. Confirm `level_check_status` becomes reviewed.
17. Confirm audit log contains `level_check_reviewed`.

---

## Stop Point / نقطة التوقف

Stop here. Test this phase before continuing.

توقف هنا. اختبر هذه المرحلة قبل الانتقال للمرحلة التالية.
