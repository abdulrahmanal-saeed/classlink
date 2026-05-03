# Phase 6A V2 Execution Report
# تقرير تنفيذ المرحلة 6A V2

## Phase Name / اسم المرحلة

Free Public Level Tests, Random Question Bank, Audio Bank, and Owner Control

اختبارات المستوى المجانية العامة، بنك الأسئلة العشوائي، بنك الصوتيات، وتحكم المالك

---

## Important Stack Decision / قرار مهم بخصوص التقنية

The prompt mentioned PostgreSQL/Replit, but this project agreement is PHP + MySQL with Flutter and optional Firebase support. Therefore, this phase was implemented using PHP + MySQL and does not migrate or switch the database engine.

البرومبت ذكر PostgreSQL/Replit، لكن اتفاق المشروع الحالي هو PHP + MySQL مع Flutter ودعم Firebase عند الحاجة. لذلك تم تنفيذ هذه المرحلة باستخدام PHP + MySQL بدون تغيير محرك قاعدة البيانات.

---

## Separation Rule / قاعدة الفصل

This phase is completely separate from paid post-payment onboarding level checks.

هذه المرحلة منفصلة تمامًا عن اختبار المستوى بعد الدفع.

It does not:

```text
require payment
create paid student account
create lesson package
create credits
mark anything as paid
change checkout/payment/onboarding flow from Phases 4/5
```

---

## Files Created / الملفات التي تم إنشاؤها

### Database / قاعدة البيانات

```text
backend/php/database/migrations/006a_free_public_level_test_foundation.sql
```

### Backend helper / أدوات الباك إند

```text
backend/php/shared/FreeLevelTest.php
```

### Public pages / الصفحات العامة

```text
web/public/level-test/index.php
web/public/level-test/entry/index.php
web/public/level-test/register/index.php
web/public/level-test/start/index.php
web/public/level-test/thank-you/index.php
web/public/level-test/quick/index.php
web/public/level-test/quick-result/index.php
```

### Old route redirects / تحويل الروابط القديمة

```text
web/public/leveltest/quick.php
web/public/leveltest/quick-result.php
web/public/leveltest/entry.php
web/public/leveltest/register.php
web/public/leveltest/start.php
web/public/leveltest/thankyou.php
```

### Owner pages / صفحات المالك

```text
web/public/owner/free-level-test/settings/index.php
web/public/owner/free-level-test/attempts/index.php
web/public/owner/free-level-test/attempts/view/index.php
```

---

## Files Changed / الملفات التي تم تعديلها

```text
web/components/layout/dashboard_shell.php
```

Added Owner navigation links:

```text
/owner/free-level-test/attempts
/owner/free-level-test/settings
```

---

## Database Tables / جداول قاعدة البيانات

Migration 006A creates separate tables:

```text
free_level_test_settings
free_level_test_applicants
free_level_test_listening_scripts
free_level_test_listening_questions
free_level_test_reading_texts
free_level_test_reading_questions
free_level_test_writing_prompts
free_level_test_speaking_prompts
free_level_test_attempts
free_level_test_answers
free_level_test_uploads
free_level_test_manual_reviews
```

These tables are separate from:

```text
level_check_attempts
level_check_answers
level_check_uploads
purchases
lesson_packages
```

---

## Test Types / أنواع الاختبارات

Implemented:

```text
Quick Arabic Level Check
Full Free Arabic Placement Test
```

### Quick Arabic Level Check

Routes:

```text
/level-test/quick
/level-test/quick-result
```

Behavior:

- Public.
- No registration.
- Reading only.
- Random snapshot from quick reading bank.
- Instant preliminary CEFR result.
- Clear disclaimer that result is reading-only.
- CTAs to full assessment, pricing, and WhatsApp contact.

### Full Free Arabic Placement Test

Routes:

```text
/level-test
/level-test/entry
/level-test/register
/level-test/start
/level-test/thank-you
```

Behavior:

- Free.
- New applicant registration.
- Existing student entry by student code foundation.
- Listening → Reading → Writing + Speaking.
- Listening and Reading auto-graded.
- Writing and Speaking are manually reviewed by Owner/Teacher.
- Result expectation copy: within 48 hours by WhatsApp/email.

---

## Random Snapshot / السنابشوت العشوائي الثابت

Implemented:

- Attempts store `snapshot_json`.
- Refresh reloads the same attempt snapshot.
- Questions do not change after attempt starts.
- Device ID cookie is generated if missing.
- IP is stored as hash.
- User agent is stored as hash.
- WhatsApp and device ID are used as repeat-tracking foundation.

Implemented privacy rules:

```text
No raw IP stored
Device/IP data is not shown to public users
```

Current anti-repeat status:

- Foundation data is stored.
- Random selection is implemented.
- Full anti-repeat scoring by previous question history can be expanded later.

---

## Audio Naming / تسمية ملفات الصوت

Implemented in seeded/stored audio URLs:

```text
[level]_[scriptNumber].mp3
```

Examples:

```text
a2_1.mp3
b1_10.mp3
c2_10.mp3
```

A2 uses 9 files only:

```text
a2_1.mp3 to a2_9.mp3
```

No leading zero is used.

Expected URL structure:

```text
/assets/audio/level-test/listening/a2/a2_1.mp3
/assets/audio/level-test/listening/b1/b1_1.mp3
```

---

## Question Bank Foundation / أساس بنك الأسئلة

Owner/database supports:

### Listening Scripts

```text
level
script_number
audio_url
title
topic
dialect_style
script_text
notes
active/inactive
3 MCQ questions
```

### Reading Texts

```text
bank_type: quick/full/shared
level
text_number
title
passage_text
topic
dialect_style
notes
active/inactive
5 MCQ questions
```

### Writing Prompts

```text
task_type: task1/task2
level: all/B1/B2/C1/C2
title
prompt_text
word_min
word_max
instructions
diagnostic_criteria
active/inactive
```

### Speaking Prompts

```text
phase: warm_up/description/discussion/abstract_argument
target_level
title
prompt_text
image_url
evaluation_notes
sort_order
active/inactive
```

---

## Seed Data / بيانات أولية

`FreeLevelTest.php` includes a safe starter seed if the bank is empty:

- Quick reading demo texts.
- Listening script records with required audio URL format.
- Writing task prompts.
- Speaking phase prompts.
- Default settings.

Important:

```text
This starter seed is not a replacement for the full files in files needed/level test.
```

When the full files are available in the repo or uploaded directly, they should be imported into these bank tables.

---

## Owner Settings / إعدادات المالك

Implemented page:

```text
/owner/free-level-test/settings
```

Settings include:

```text
enable_full_test
enable_quick_check
enable_randomization
anti_repeat_window_days
listening_blocks_per_level
reading_texts_per_level
quick_reading_text_count
writing_task1_count
writing_task2_count
speaking_prompts_per_phase
allow_retakes
minimum_days_before_retake
show_quick_check_homepage
show_full_test_cta_homepage
result_copy
whatsapp_followup_template
email_followup_template
```

The page also shows audio availability by level:

```text
A2/B1/B2/C1/C2 available count
Required per test
Status OK/Warning
```

---

## Owner Review / مراجعة المالك

Implemented pages:

```text
/owner/free-level-test/attempts
/owner/free-level-test/attempts/view?id=...
```

Owner can view:

- Applicant info.
- Test type.
- Status.
- Listening score.
- Reading score.
- Auto estimate.
- Preliminary level.
- Attempt snapshot.
- Answers.
- Uploads.

Owner can manually review:

### Writing Review

```text
writing_score
writing_level
writing_feedback
```

### Speaking Review

```text
fluency /4
grammar /4
vocabulary /4
pronunciation /4
depth organization /4
speaking total /20
speaking level
speaking feedback
```

### Final Review

```text
final_level
teacher_notes
next_step_notes
mark reviewed
```

Audit log records:

```text
free_level_test_reviewed
```

---

## Uploads / رفع الملفات

Speaking upload supports:

```text
mp3, wav, m4a, webm
Max: 25MB
```

Files are stored under:

```text
uploads/free-level-tests/{attemptId}/
```

Important deployment note:

```text
Create uploads/free-level-tests on the server and make sure PHP can write to it.
```

---

## Old Route Support / دعم الروابط القديمة

Implemented `.php` redirect files:

```text
/leveltest/quick.php -> /level-test/quick
/leveltest/quick-result.php -> /level-test/quick-result
/leveltest/entry.php -> /level-test/entry
/leveltest/register.php -> /level-test/register
/leveltest/start.php -> /level-test/start
/leveltest/thankyou.php -> /level-test/thank-you
```

Note:

```text
Directory-style old routes such as /leveltest/quick without .php can be added later through .htaccess if needed.
```

---

## Known Limitations / القيود الحالية

- The full external bank files under `files needed/level test` were not found as importable files in the current visible repo search results, so a starter seed was added instead.
- Full CRUD pages for adding/editing each bank item are not fully built yet; database supports them and Owner settings/review pages exist.
- Browser audio recording is not implemented yet; upload fallback is implemented.
- Anti-repeat history foundation exists, but deeper weighted anti-repeat selection can be expanded later.
- Existing student profile completeness check is only foundational; full integration with real student profile module can be expanded later.
- Old directory-style routes without `.php` are not added yet.
- CSRF protection still needs to be strengthened before production.

---

## Manual Test Checklist / قائمة الاختبار اليدوي

1. Apply migration:

```text
backend/php/database/migrations/006a_free_public_level_test_foundation.sql
```

2. Make sure upload folder exists and is writable:

```text
uploads/free-level-tests
```

3. Open quick check:

```text
/level-test/quick
```

4. Complete quick check.
5. Confirm redirect to:

```text
/level-test/quick-result?token=...
```

6. Confirm preliminary result and disclaimer appear.
7. Open full test entry:

```text
/level-test/entry
```

8. Register new applicant:

```text
/level-test/register
```

9. Confirm WhatsApp validation rejects numbers without country code.
10. Start full test:

```text
/level-test/start?token=...
```

11. Complete Listening.
12. Refresh after Listening and confirm it stays on Reading with same attempt.
13. Complete Reading.
14. Complete Writing + upload Speaking audio.
15. Confirm redirect:

```text
/level-test/thank-you?token=...
```

16. Login as Owner.
17. Open settings:

```text
/owner/free-level-test/settings
```

18. Check audio availability.
19. Open attempts:

```text
/owner/free-level-test/attempts
```

20. Review attempt:

```text
/owner/free-level-test/attempts/view?id=...
```

21. Add writing/speaking/final review.
22. Confirm attempt becomes reviewed.
23. Confirm audit log contains:

```text
free_level_test_reviewed
```

---

## Stop Point / نقطة التوقف

Stop here. Test this phase before continuing.

توقف هنا. اختبر هذه المرحلة قبل الانتقال للمرحلة التالية.
