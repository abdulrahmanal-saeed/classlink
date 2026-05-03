# Phase 5 Execution Report
# تقرير تنفيذ المرحلة 5

## Phase Name / اسم المرحلة

Post-Payment Student Form and Onboarding Pipeline

فورم الطالب بعد الدفع ومسار المتابعة للمالك

---

## What was built / ما الذي تم بناؤه

Phase 5 created the post-payment student information form and the Owner onboarding pipeline.

قامت المرحلة 5 ببناء فورم بيانات الطالب بعد الدفع، ومسار مراجعة المالك للطلبات الجديدة.

---

## Files Created / الملفات التي تم إنشاؤها

### Database / قاعدة البيانات

```text
backend/php/database/migrations/005_onboarding_student_form_foundation.sql
```

### Backend helpers / أدوات الباك إند

```text
backend/php/shared/Onboarding.php
```

### Public pages / الصفحات العامة

```text
web/public/student-form/index.php
web/public/level-check-intro/index.php
```

### Owner pages / صفحات المالك

```text
web/public/owner/onboarding/index.php
web/public/owner/onboarding/view/index.php
```

---

## Files Changed / الملفات التي تم تعديلها

```text
web/components/layout/dashboard_shell.php
```

Owner sidebar now includes:

```text
/owner/onboarding
```

---

## Database Changes / تغييرات قاعدة البيانات

Migration 005 updates `purchases` with onboarding statuses:

```text
student_form_status: not_started / submitted
level_check_status: not_started / submitted / reviewed
schedule_status: not_selected / requested / confirmed
owner_review_status: pending_review / approved / rejected
```

Migration 005 updates `student_intake_forms` with:

```text
purchase_id
checkout_reference
learner_type
submitted_at
owner_review_status
owner_review_note
```

Migration 005 creates:

```text
email_fallback_logs
```

This logs email actions when the email provider is not configured yet.

---

## Public Routes / المسارات العامة

Implemented:

```text
/student-form?ref={checkoutReference}
/level-check-intro?ref={checkoutReference}
```

The student form loads checkout data by secure reference.

الفورم يسحب بيانات checkout باستخدام reference آمن.

---

## Conditional Form Logic / منطق الفورم الشرطي

Supported learner types:

```text
adult
child
someone_else
```

Behavior:

- Adult checkout shows adult fields.
- Child checkout shows child fields.
- Someone_else asks if the learner is adult or child, then shows the matching field set.

---

## Adult Fields / حقول البالغ

Implemented:

```text
Age
Native language
Current Arabic level
Can read Arabic?
Can write Arabic?
Main goal
Learning reason
Use context
Preferred Arabic type
Biggest difficulty
Difficulty details
Scheduling preferences
Notes for tutor
```

---

## Child Fields / حقول الطفل

Implemented:

```text
Parent name
Child name
Child age
Child native language
Does child speak Arabic?
Can child read Arabic?
Can child write Arabic?
Child learning goal
Studied Arabic before?
Struggles
Learning style notes
Scheduling preferences
Notes for tutor
```

---

## After Submit / بعد الإرسال

When the form is submitted:

1. Student form is saved in `student_intake_forms`.
2. `purchases.student_form_status` becomes `submitted`.
3. `owner_review_status` remains `pending_review`.
4. Email fallback log is created if email provider is not configured.
5. Audit log records `student_form_submitted`.
6. User redirects to:

```text
/level-check-intro?ref={checkoutReference}
```

---

## Owner Onboarding Pipeline / مسار المتابعة للمالك

Implemented:

```text
/owner/onboarding
/owner/onboarding/view?id=...
```

Owner can:

- See new form submissions.
- View learner details and submitted answers.
- See purchase/payment/form/level/schedule statuses.
- Approve, reject, or keep pending review.
- Add review note.

Every Owner review update writes to audit log.

---

## Security Checks / فحوصات الأمان

- Student form uses checkout reference lookup.
- Owner onboarding pages are protected by `owner_teacher` role.
- Required fields are validated server-side.
- Email fallback is logged instead of assuming email provider exists.
- Owner review updates are written to audit log.
- No student account is automatically created yet.

---

## Known Limitations / القيود الحالية

- Full level check flow is not implemented yet; only intro placeholder exists.
- Schedule selection is not implemented yet.
- Student account creation after approval is not implemented yet.
- Email provider is not configured; emails are logged in `email_fallback_logs`.
- The clean route `/owner/onboarding/{id}` was not added to `.htaccess` due a tool filter, but `/owner/onboarding/view?id=...` works.
- CSRF protection still needs to be strengthened before production.

---

## Manual Test Checklist / قائمة الاختبار اليدوي

1. Apply migration:

```text
backend/php/database/migrations/005_onboarding_student_form_foundation.sql
```

2. Create checkout order from:

```text
/checkout?plan=single
```

3. Open thank-you page and click:

```text
Complete Student Form
```

or open directly:

```text
/student-form?ref={checkoutReference}
```

4. Submit adult form.
5. Create another checkout with learner type child and submit child form.
6. Create another checkout with learner type someone_else and test adult/child selection.
7. Confirm records are saved in:

```text
student_intake_forms
email_fallback_logs
audit_logs
```

8. Login as Owner.
9. Open:

```text
/owner/onboarding
```

10. Open a submission:

```text
/owner/onboarding/view?id=...
```

11. Approve or reject the submission.
12. Confirm `owner_review_status` updates in both form and purchase.
13. Confirm audit log contains `onboarding_review_updated`.

---

## Stop Point / نقطة التوقف

Stop here. Test this phase before continuing.

توقف هنا. اختبر هذه المرحلة قبل الانتقال للمرحلة التالية.
