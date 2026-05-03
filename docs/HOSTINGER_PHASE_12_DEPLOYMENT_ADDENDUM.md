# Hostinger Deployment Addendum — Phase 12
# إضافة دليل الرفع على Hostinger — المرحلة 12

> Use this addendum together with:

```text
docs/HOSTINGER_DEPLOYMENT_GUIDE.md
docs/HOSTINGER_PHASE_9_DEPLOYMENT_ADDENDUM.md
docs/HOSTINGER_PHASE_10_DEPLOYMENT_ADDENDUM.md
docs/HOSTINGER_PHASE_11_DEPLOYMENT_ADDENDUM.md
```

This addendum covers Phase 12 only.

---

## Phase 12 Name / اسم المرحلة

Academy Partner Portal

بوابة شريك الأكاديمية

---

## 1) What Phase 12 Adds / ماذا تضيف المرحلة 12

Phase 12 adds:

```text
Academy partner dashboard
Academy student briefs list
Academy new brief form
Academy brief detail page
Owner all academy briefs page
Owner brief review page
Owner convert brief to onboarding
Central AcademyPortal helper
```

---

## 2) Database Migration / تحديث قاعدة البيانات

Phase 12 requires a migration:

```text
backend/php/database/migrations/012_academy_partner_portal.sql
```

Run this in phpMyAdmin after Phase 11.

---

## 3) What Migration 012 Changes / ماذا يفعل Migration 012

It expands the existing `academy_briefs` table.

Adds fields:

```text
student_name
age
nationality_country
current_arabic_level
goal
speaking_ability
reading_writing_ability
notes_from_academy
parent_contact_info
preferred_schedule
internal_notes
converted_student_user_id
converted_intake_form_id
reviewed_by_user_id
reviewed_at
converted_by_user_id
converted_at
```

Updates statuses to include:

```text
submitted
under_review
converted_to_student
rejected
```

Keeps old statuses for compatibility:

```text
new
reviewed
approved
```

Maps old statuses:

```text
new -> submitted
reviewed -> under_review
approved -> converted_to_student
```

---

## 4) Files to Upload / الملفات المطلوب رفعها

### Backend helper

Upload:

```text
backend/php/shared/AcademyPortal.php
```

To:

```text
/home/<HOSTINGER_USER>/domains/mshabibanabil.com/backend/php/shared/AcademyPortal.php
```

---

### Migration file

Keep/copy this file for phpMyAdmin:

```text
backend/php/database/migrations/012_academy_partner_portal.sql
```

---

### Updated shared layout

Upload:

```text
web/components/layout/dashboard_shell.php
```

To:

```text
/home/<HOSTINGER_USER>/domains/mshabibanabil.com/web/components/layout/dashboard_shell.php
```

Reason:

```text
Adds Owner Academy Briefs link and Academy Partner navigation links.
```

---

### Updated academy dashboard

Upload:

```text
web/public/academy/dashboard/index.php
```

To:

```text
public_html/staging/academy/dashboard/index.php
```

---

### New academy public pages

Upload:

```text
web/public/academy/briefs/index.php
web/public/academy/briefs/new/index.php
web/public/academy/briefs/view/index.php
```

To:

```text
public_html/staging/academy/briefs/index.php
public_html/staging/academy/briefs/new/index.php
public_html/staging/academy/briefs/view/index.php
```

---

### New Owner public pages

Upload:

```text
web/public/owner/academy-briefs/index.php
web/public/owner/academy-briefs/view/index.php
```

To:

```text
public_html/staging/owner/academy-briefs/index.php
public_html/staging/owner/academy-briefs/view/index.php
```

---

## 5) phpMyAdmin Steps / خطوات phpMyAdmin

1. Open Hostinger hPanel.
2. Open Databases.
3. Open phpMyAdmin for staging DB.
4. Select the correct database.
5. Export backup first.
6. Open SQL tab.
7. Paste the full content of:

```text
012_academy_partner_portal.sql
```

8. Click Go.
9. If success appears, continue.
10. If error appears, stop and send the error.

---

## 6) URLs to Test / روابط الاختبار

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

---

## 7) Academy Partner Test / اختبار شريك الأكاديمية

1. Login as Academy Partner.
2. Open:

```text
/academy/briefs/new
```

3. Submit brief with:

```text
Student name
Age
Country
Current Arabic level
Goal
Speaking ability
Reading/writing ability
Notes from academy
Parent/contact info if child
Preferred schedule
```

4. Confirm redirect to detail page.
5. Open:

```text
/academy/briefs
```

6. Confirm brief appears.
7. Confirm status is:

```text
submitted
```

---

## 8) Owner Review Test / اختبار المالك

1. Login as Owner.
2. Open:

```text
/owner/academy-briefs
```

3. Open submitted brief.
4. Change status to:

```text
under_review
```

5. Add internal note.
6. Save review.
7. Login again as Academy Partner.
8. Confirm status and note are visible.

---

## 9) Convert Brief Test / اختبار التحويل

1. Login as Owner.
2. Open:

```text
/owner/academy-briefs/view?id={briefId}
```

3. Click Convert brief.
4. Confirm new intake ID appears.
5. Open:

```text
/owner/onboarding
```

6. Confirm new onboarding intake exists.
7. Open audit log:

```text
/owner/audit-log
```

8. Confirm event:

```text
academy_brief_converted_to_onboarding
```

---

## 10) Ownership Security Test / اختبار الخصوصية

Academy Partner can only see own briefs.

Test:

1. Login as Academy Partner A.
2. Submit brief.
3. Login as Academy Partner B.
4. Try opening:

```text
/academy/briefs/view?id={PartnerABriefId}
```

Expected:

```text
Brief not found or not owned by this academy account.
```

---

## 11) Known Limitations / القيود الحالية

- Clean routes are implemented as query routes for now:

```text
/academy/briefs/view?id=...
/owner/academy-briefs/view?id=...
```

- Convert creates onboarding intake but does not automatically create paid purchase/package/account.
- Linking to existing student is manual using student user ID.
- No email/WhatsApp notification to academy partner yet.
- CSRF protection still needs strengthening before production.

---

## 12) Stop Rule / قاعدة التوقف

Stop here. Test Phase 12 fully before moving to Phase 13.

توقف هنا. اختبر Phase 12 بالكامل قبل الانتقال إلى Phase 13.
