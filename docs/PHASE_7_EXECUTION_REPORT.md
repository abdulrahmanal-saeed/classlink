# Phase 7 Execution Report
# تقرير تنفيذ المرحلة 7

## Phase Name / اسم المرحلة

Owner Approval, Student/Parent Account Creation, and Login Details

موافقة المالك، إنشاء حسابات الطالب/ولي الأمر، وبيانات الدخول

---

## Goal / الهدف

After payment, form, level check, and schedule request/review, Owner/Teacher approves and creates accounts.

بعد الدفع والفورم واختبار المستوى ومراجعة الجدولة، يقوم المالك/المعلم بالموافقة وإنشاء الحسابات.

Rule:

```text
Teacher approves → Create Student Account → Send/log login details
```

---

## Files Created / الملفات التي تم إنشاؤها

### Database / قاعدة البيانات

```text
backend/php/database/migrations/007_owner_approval_account_creation.sql
```

### Backend helper / أدوات الباك إند

```text
backend/php/shared/ApprovalWorkflow.php
```

### Owner pages / صفحات المالك

```text
web/public/owner/onboarding/approve/index.php
web/public/owner/students/index.php
web/public/owner/students/view/index.php
web/public/owner/parents/index.php
web/public/owner/parents/view/index.php
```

---

## Files Changed / الملفات التي تم تعديلها

```text
web/components/layout/dashboard_shell.php
web/public/parent/dashboard/index.php
```

Changes:

- Owner sidebar now includes Students and Parents.
- Parent dashboard now shows only linked child learners for the logged-in parent.

---

## Database Changes / تغييرات قاعدة البيانات

Migration 007 adds approval/account tracking to:

```text
student_intake_forms
purchases
```

Added tables:

```text
login_detail_logs
onboarding_account_links
```

Added default templates:

```text
student_welcome_login
parent_welcome_login
```

in:

```text
email_templates
whatsapp_templates
```

---

## Approval Workflow / مسار الموافقة

Implemented page:

```text
/owner/onboarding/approve?id={intakeId}
```

The approval page checks:

```text
payment status = paid
student_form_status = submitted
level_check_status = submitted or reviewed
schedule_status = not_selected/requested/confirmed
```

If checks fail, approval is blocked with real messages.

If checks pass, Owner can approve and create accounts.

---

## Adult Learner Approval / موافقة الطالب البالغ

When adult learner is approved:

1. Creates `users` row with role `student`.
2. Creates `user_profiles` row.
3. Creates `student_profiles` row with learner_type `adult`.
4. Uses latest level check final/suggested level when available.
5. Creates initial `lesson_packages` from purchased plan.
6. Creates `lesson_credit_transactions` add entry.
7. Logs login details in `login_detail_logs`.
8. Updates `student_intake_forms` and `purchases` approval fields.
9. Inserts `onboarding_account_links` row.
10. Writes audit log.

---

## Child Learner Approval / موافقة الطفل

When child learner is approved:

1. Creates Parent `users` row with role `parent`.
2. Creates Child `users` row with role `student`.
3. Creates parent `user_profiles` and `parent_profiles`.
4. Creates child `user_profiles` and `student_profiles` with learner_type `child`.
5. Creates `parent_child_links` active link.
6. Creates initial `lesson_packages` for child student from purchased plan.
7. Creates `lesson_credit_transactions` add entry.
8. Logs parent login details.
9. Logs child account login details.
10. Updates `student_intake_forms` and `purchases` approval fields.
11. Inserts `onboarding_account_links` row.
12. Writes audit log.

---

## Login Details / بيانات الدخول

Implemented:

```text
login_detail_logs
```

Since real email/WhatsApp provider is not configured yet, login details are logged for manual sending.

لأن مزود الإيميل/واتساب الحقيقي غير مفعل بعد، يتم تسجيل بيانات الدخول للمالك لإرسالها يدويًا.

The log stores:

```text
recipient
subject
message_body
temporary_password
status = logged
```

Important:

```text
This is acceptable for staging/testing only.
Before production, replace temporary password logs with secure setup links or provider-based delivery.
```

---

## Owner Students / صفحات الطلاب للمالك

Implemented:

```text
/owner/students
/owner/students/view?id={studentUserId}
```

Owner can see:

- Student account.
- Adult/child learner type.
- Current level.
- Learning goal.
- Phone/country.
- Lesson package and credits.
- Login detail logs.

---

## Owner Parents / صفحات أولياء الأمور للمالك

Implemented:

```text
/owner/parents
/owner/parents/view?id={parentUserId}
```

Owner can see:

- Parent account.
- Contact method.
- Linked children.
- Child level.
- Login detail logs.

---

## Parent Dashboard / لوحة ولي الأمر

Updated:

```text
/parent/dashboard
```

Parent can only see children linked to their own account using:

```text
parent_child_links.parent_user_id = current logged-in parent
```

This satisfies:

```text
Confirm parent sees child only
```

---

## Audit Log / سجل المراجعة

Audit actions added:

```text
onboarding_approved_accounts_created
```

Existing login/logout audit still applies when created users log in.

---

## Important URLs / روابط مهمة

Approval:

```text
/owner/onboarding/approve?id={intakeId}
```

Students:

```text
/owner/students
/owner/students/view?id={studentUserId}
```

Parents:

```text
/owner/parents
/owner/parents/view?id={parentUserId}
```

Parent login area:

```text
/parent/dashboard
```

---

## Known Limitations / القيود الحالية

- Requested clean route `/owner/onboarding/[id]/approve` was implemented as `/owner/onboarding/approve?id=...` to avoid `.htaccess` dependency.
- Requested clean routes `/owner/students/[id]` and `/owner/parents/[id]` were implemented as `/owner/students/view?id=...` and `/owner/parents/view?id=...`.
- Real email/WhatsApp sending is not implemented yet; login details are logged for manual sending.
- Temporary passwords are visible to Owner in logs for staging/testing. Replace with secure setup links before production.
- Schedule request page is not fully built yet, so the approval checker allows `not_selected`, `requested`, or `confirmed` for now.
- The onboarding review page did not get the extra approve button due to a GitHub tool filter, but direct approval URL works.
- CSRF protection still needs to be strengthened before production.

---

## Manual Test Checklist / قائمة الاختبار اليدوي

1. Apply migration:

```text
backend/php/database/migrations/007_owner_approval_account_creation.sql
```

2. Prepare an adult onboarding submission with:

```text
payment = paid
student_form_status = submitted
level_check_status = submitted or reviewed
```

3. Open:

```text
/owner/onboarding/approve?id={adultIntakeId}
```

4. Click approve.
5. Confirm student account was created.
6. Open:

```text
/owner/students
```

7. Open created student:

```text
/owner/students/view?id={studentUserId}
```

8. Copy login details from log.
9. Log out and log in as adult student.
10. Confirm student reaches:

```text
/student/dashboard
```

11. Prepare a child onboarding submission with paid/form/level status ready.
12. Open:

```text
/owner/onboarding/approve?id={childIntakeId}
```

13. Click approve.
14. Confirm parent account and child profile were created.
15. Open:

```text
/owner/parents
```

16. Open created parent:

```text
/owner/parents/view?id={parentUserId}
```

17. Confirm linked child appears.
18. Copy parent login details.
19. Log out and log in as parent.
20. Confirm parent dashboard shows linked child only:

```text
/parent/dashboard
```

21. Open audit log:

```text
/owner/audit-log
```

22. Confirm:

```text
onboarding_approved_accounts_created
```

---

## Stop Point / نقطة التوقف

Stop here. Test this phase before continuing.

توقف هنا. اختبر هذه المرحلة قبل الانتقال للمرحلة التالية.
