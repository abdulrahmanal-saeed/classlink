# Phase 14 Execution Report
# تقرير تنفيذ المرحلة 14

## Phase Name / اسم المرحلة

Homework, Speaking Scenarios, Reviews/Tests, Materials

نظام الواجبات، المواقف الكلامية، الاختبارات/المراجعات، والمواد التعليمية

---

## Goal / الهدف

Build the learning assignment system.

بناء نظام التكليفات التعليمية الأساسي.

---

## Database Migration / تحديث قاعدة البيانات

Phase 14 adds a migration:

```text
backend/php/database/migrations/014_learning_assignment_system.sql
```

This migration expands existing Phase 2 learning tables without replacing them.

---

## Files Created / الملفات التي تم إنشاؤها

### Database

```text
backend/php/database/migrations/014_learning_assignment_system.sql
```

### Backend helper

```text
backend/php/shared/LearningAssignments.php
```

### Owner Homework

```text
web/public/owner/homework/new/index.php
web/public/owner/homework/view/index.php
web/public/owner/homework/submissions/index.php
```

### Student Homework

```text
web/public/student/homework/view/index.php
web/public/student/homework/result/index.php
```

### Owner Scenarios

```text
web/public/owner/scenarios/new/index.php
web/public/owner/scenarios/submissions/index.php
```

### Student Scenarios

```text
web/public/student/scenarios/view/index.php
web/public/student/scenarios/result/index.php
```

### Owner Reviews/Tests

```text
web/public/owner/reviews/new/index.php
web/public/owner/reviews/results/index.php
```

### Student Reviews/Tests

```text
web/public/student/reviews/view/index.php
web/public/student/reviews/result/index.php
```

### Materials

```text
web/public/owner/materials/new/index.php
```

---

## Files Changed / الملفات التي تم تعديلها

```text
web/public/owner/materials/index.php
```

Adds Create Material button and assignment display.

---

## Migration 014 Changes / تغييرات Migration 014

### homework_questions

Adds:

```text
points
media_url
explanation
```

### homework_submissions

Adds:

```text
score
max_score
manual_override_payload
reviewed_by_user_id
```

### scenario_submissions

Adds:

```text
owner_feedback
reviewed_by_user_id
```

### review_tests

Adds:

```text
instructions
manual_override_note
```

### review_questions

Adds:

```text
points
media_url
explanation
```

### review_submissions

Adds:

```text
max_score
manual_override_payload
reviewed_by_user_id
```

### course_materials

Expands material types to:

```text
pdf
video
youtube
audio
link
text
html
powerpoint
file
```

Adds:

```text
assigned_student_user_id
description
created_by_user_id
```

---

## Implemented Owner URLs / روابط المالك المنفذة

Homework:

```text
/owner/homework
/owner/homework/new
/owner/homework/view?id={homeworkId}
/owner/homework/submissions?id={homeworkId}
```

Scenarios:

```text
/owner/scenarios
/owner/scenarios/new
/owner/scenarios/submissions?id={scenarioId}
```

Reviews:

```text
/owner/reviews
/owner/reviews/new
/owner/reviews/results?id={reviewTestId}
```

Materials:

```text
/owner/materials
/owner/materials/new
```

---

## Implemented Student URLs / روابط الطالب المنفذة

Homework:

```text
/student/homework/view?id={homeworkId}
/student/homework/result?id={homeworkId}
```

Scenarios:

```text
/student/scenarios/view?id={scenarioId}
/student/scenarios/result?id={scenarioId}
```

Reviews:

```text
/student/reviews/view?id={reviewTestId}
/student/reviews/result?id={reviewTestId}
```

Materials:

```text
/student/materials
```

Student materials already existed and now benefits from assigned materials fields after migration.

---

## Homework Features / خصائص الواجبات

Implemented homework types:

```text
MCQ
Reading
Listening
Writing
Speaking
```

Owner can:

```text
Create homework
Add questions
Add options A/B/C/D
Add answer key
Add points
Add media URL
Add explanation
Publish/draft homework
View submission
Correct submission
Add feedback
Manual override score
```

Student can:

```text
Open homework
Submit answers
See result
See exact selected answer text
See correct answer when incorrect
See teacher feedback
```

Important requirement satisfied:

```text
Student result shows selected answer text, not only A/B/C.
```

---

## Scenario Features / خصائص المواقف الكلامية

Owner can create scenario with:

```text
Real-life speaking task
Situation
Prompt
Time limit
Keywords
Model answer optional
Published/draft status
```

Student can:

```text
Open scenario
Submit audio/upload URL placeholder
Submit transcript/notes
See result
See model answer if provided
See score and teacher feedback
```

Owner can:

```text
Review scenario submission
Open uploaded audio/link
Add student-facing feedback
Add internal owner feedback
Add score
```

Important limitation:

```text
Browser-based live audio recording/upload handling is not fully implemented yet.
Current MVP accepts audio/upload URL or path.
```

---

## Review/Test Features / خصائص الاختبارات والمراجعات

Supported question types:

```text
MCQ
Matching
Fill-in-the-blank
Complete the sentence
Writing
Speaking
```

Owner can:

```text
Create review/test
Add questions
Add answer key
Add points
Add media URL
View results
Manual override score
Add teacher feedback
Add manual override note
```

Student can:

```text
Open review/test
Submit answers
See result
See selected answer text
See correct answer if incorrect
See teacher feedback
See manual override note
```

Manual override affects:

```text
Owner view
Student result view
Stored submission payload/score for later exports
```

---

## Materials Features / خصائص المواد التعليمية

Owner can create material types:

```text
Links
YouTube/video
PDF
PowerPoint/file
HTML
Text
Audio
```

Owner can assign materials by:

```text
All/global
Level
Specific student
```

Student materials page displays available materials through the existing Student Portal material logic.

---

## Helper Functions / دوال المساعدة

Added in:

```text
backend/php/shared/LearningAssignments.php
```

Main functions:

```text
learning_students_for_select
learning_create_homework
learning_homework_detail
learning_submit_homework
learning_homework_submission
learning_correct_homework
learning_create_scenario
learning_scenario_detail
learning_submit_scenario
learning_scenario_submission
learning_correct_scenario
learning_create_review
learning_review_detail
learning_submit_review
learning_review_submission
learning_correct_review
learning_create_material
learning_result_answers
```

---

## Audit Log / سجل المراجعة

Added audit actions:

```text
homework_created
homework_submitted
homework_corrected
scenario_created
scenario_submitted
scenario_feedback_saved
review_created
review_submitted
review_corrected
material_created
```

---

## Known Limitations / القيود الحالية

- Clean routes are implemented as query routes for now, for example `/student/homework/view?id=...`.
- Browser recording and real upload processing are not fully implemented yet; audio/upload paths are entered manually as MVP.
- Homework and review creation currently supports a fixed number of visible question rows in the form.
- Detailed per-question manual correction is stored as score/override note, not yet individual per-question override UI.
- Materials upload is URL/path-based; real file uploader can be added later.
- Parent child assignment views can display homework/notes from existing pages, but parent-specific assignment actions are not added in this phase.
- CSRF protection still needs strengthening before production.

---

## Manual Test Checklist / قائمة الاختبار اليدوي

1. Run migration:

```text
backend/php/database/migrations/014_learning_assignment_system.sql
```

2. Login as Owner.
3. Create homework:

```text
/owner/homework/new
```

4. Include MCQ, reading, listening, writing, and speaking rows.
5. Publish homework.
6. Login as student.
7. Open:

```text
/student/homework/view?id={homeworkId}
```

8. Submit homework.
9. Open:

```text
/student/homework/result?id={homeworkId}
```

10. Confirm selected answer text and correct answer display.
11. Login as Owner.
12. Open:

```text
/owner/homework/submissions?id={homeworkId}
```

13. Correct homework and add feedback.
14. Confirm student sees feedback.
15. Create scenario:

```text
/owner/scenarios/new
```

16. Login as student and submit:

```text
/student/scenarios/view?id={scenarioId}
```

17. Owner adds feedback:

```text
/owner/scenarios/submissions?id={scenarioId}
```

18. Student sees result:

```text
/student/scenarios/result?id={scenarioId}
```

19. Create review/test:

```text
/owner/reviews/new
```

20. Student submits:

```text
/student/reviews/view?id={reviewTestId}
```

21. Owner manual override:

```text
/owner/reviews/results?id={reviewTestId}
```

22. Student sees override in:

```text
/student/reviews/result?id={reviewTestId}
```

23. Create material:

```text
/owner/materials/new
```

24. Confirm material appears in Owner materials and Student materials if active/assigned.

---

## Stop Point / نقطة التوقف

Stop here. Test this phase before continuing.

توقف هنا. اختبر هذه المرحلة قبل الانتقال للمرحلة التالية.
