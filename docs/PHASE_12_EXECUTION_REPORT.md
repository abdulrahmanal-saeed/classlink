# Phase 12 Execution Report
# تقرير تنفيذ المرحلة 12

## Phase Name / اسم المرحلة

Academy Partner Portal

بوابة شريك الأكاديمية

---

## Goal / الهدف

Build academy partner area for submitting and tracking student briefs.

بناء مساحة لشريك الأكاديمية لإرسال ومتابعة ملفات الطلاب المختصرة.

---

## Database Migration / تحديث قاعدة البيانات

Phase 12 adds a migration because the old `academy_briefs` table from Phase 2 had limited fields and old statuses.

```text
backend/php/database/migrations/012_academy_partner_portal.sql
```

---

## Files Created / الملفات التي تم إنشاؤها

### Database

```text
backend/php/database/migrations/012_academy_partner_portal.sql
```

### Backend helper

```text
backend/php/shared/AcademyPortal.php
```

### Academy Partner pages

```text
web/public/academy/briefs/index.php
web/public/academy/briefs/new/index.php
web/public/academy/briefs/view/index.php
```

### Owner pages

```text
web/public/owner/academy-briefs/index.php
web/public/owner/academy-briefs/view/index.php
```

---

## Files Changed / الملفات التي تم تعديلها

```text
web/public/academy/dashboard/index.php
web/components/layout/dashboard_shell.php
```

---

## Implemented URLs / الروابط المنفذة

Academy Partner:

```text
/academy/dashboard
/academy/briefs
/academy/briefs/new
/academy/briefs/view?id={briefId}
```

Owner:

```text
/owner/academy-briefs
/owner/academy-briefs/view?id={briefId}
```

Practical query routes are used instead of clean `[id]` routes for now.

---

## Brief Fields / حقول ملف الطالب

Implemented fields:

```text
Student name
Age
Nationality/country
Current Arabic level
Goal
Speaking ability
Reading/writing ability
Notes from academy
Parent/contact info if child
Preferred schedule if known
```

---

## Brief Statuses / حالات الملف

Implemented statuses:

```text
submitted
under_review
converted_to_student
rejected
```

Legacy compatibility remains for old Phase 2 statuses:

```text
new
reviewed
approved
```

Migration maps old statuses:

```text
new -> submitted
reviewed -> under_review
approved -> converted_to_student
```

---

## Academy Partner Flow / مسار شريك الأكاديمية

Academy Partner can:

1. Open dashboard.
2. Submit a student brief.
3. View only own briefs.
4. Track status updates.
5. See Owner internal note when available.
6. See converted intake ID after conversion.

Security:

```text
academy_partner_user_id = current logged-in academy partner
```

---

## Owner Flow / مسار المالك

Owner can:

1. View all academy briefs.
2. Review each brief.
3. Add internal notes.
4. Set status.
5. Convert brief to onboarding intake.
6. Optionally link existing student user ID.
7. See converted intake ID.

Conversion creates row in:

```text
student_intake_forms
```

With:

```text
status = submitted
owner_review_status = pending_review
raw_payload.source = academy_partner_brief
```

And updates academy brief:

```text
status = converted_to_student
converted_intake_form_id = new intake ID
converted_student_user_id = optional student ID
converted_by_user_id = Owner ID
converted_at = NOW()
```

---

## Audit Log / سجل المراجعة

Added audit actions:

```text
academy_brief_submitted
academy_brief_review_updated
academy_brief_converted_to_onboarding
```

---

## Security / الأمان

Academy pages use:

```php
require_role('academy_partner')
```

Owner pages use:

```php
require_role('owner_teacher')
```

Academy detail page only fetches briefs where:

```text
id = requested brief id
academy_partner_user_id = logged-in academy partner
```

So Academy Partner cannot view another partner's brief by changing the URL ID.

---

## Navigation / التنقل

Owner sidebar now includes:

```text
Academy Briefs
طلبات الأكاديميات
```

Academy sidebar now includes:

```text
Academy Dashboard
Student Briefs
Submit Brief
```

---

## Known Limitations / القيود الحالية

- Clean routes like `/academy/briefs/[id]` are implemented as `/academy/briefs/view?id=...` for now.
- Clean routes like `/owner/academy-briefs/[id]` are implemented as `/owner/academy-briefs/view?id=...` for now.
- Convert creates onboarding intake, but does not automatically create paid purchase/package/account. Final account creation still follows Owner approval workflow.
- Optional linking to an existing student uses manual student user ID entry for now.
- No email/WhatsApp notification is sent to academy partner yet; status is visible in dashboard.
- CSRF protection still needs strengthening before production.

---

## Manual Test Checklist / قائمة الاختبار اليدوي

1. Run migration:

```text
backend/php/database/migrations/012_academy_partner_portal.sql
```

2. Login as Academy Partner.
3. Open:

```text
/academy/dashboard
```

4. Open:

```text
/academy/briefs/new
```

5. Submit a student brief.
6. Confirm redirect to:

```text
/academy/briefs/view?id={briefId}
```

7. Confirm Academy Partner can see submitted brief.
8. Login as Owner.
9. Open:

```text
/owner/academy-briefs
```

10. Open the submitted brief:

```text
/owner/academy-briefs/view?id={briefId}
```

11. Change status to under_review and add internal note.
12. Confirm academy partner sees status update.
13. Convert brief.
14. Confirm intake is created.
15. Open Owner onboarding page and verify intake exists.
16. Confirm audit log contains:

```text
academy_brief_submitted
academy_brief_review_updated
academy_brief_converted_to_onboarding
```

17. Login as another Academy Partner if available.
18. Try opening brief ID from first academy partner.
19. Confirm access is blocked/not found.

---

## Stop Point / نقطة التوقف

Stop here. Test this phase before continuing.

توقف هنا. اختبر هذه المرحلة قبل الانتقال للمرحلة التالية.
