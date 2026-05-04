# Phase 23 Execution Report
# تقرير تنفيذ المرحلة 23

## Phase Name / اسم المرحلة

Bilingual Arabic/English Website, RTL/LTR, and Localization QA

تعريب وإنجليزية المنصة، دعم RTL/LTR، ومراجعة جودة اللغة

---

## Goal / الهدف

Make the web platform professionally bilingual in Arabic and English with correct RTL/LTR support.

---

## Database Migration / تحديث قاعدة البيانات

Phase 23 adds:

```text
backend/php/database/migrations/023_bilingual_localization.sql
```

---

## Files Created / الملفات التي تم إنشاؤها

### Database

```text
backend/php/database/migrations/023_bilingual_localization.sql
```

### Backend helper

```text
backend/php/shared/Localization.php
```

### API

```text
web/public/api/localization/set-language/index.php
```

### JavaScript localization

```text
web/public/assets/js/i18n/en.js
web/public/assets/js/i18n/ar.js
web/public/assets/js/i18n/apply-i18n.js
```

### QA documentation

```text
docs/LOCALIZATION_QA_CHECKLIST.md
```

---

## Files Changed / الملفات التي تم تعديلها

```text
web/components/layout/dashboard_shell.php
web/public/assets/css/app.css
```

---

## Migration 023 Changes / تغييرات Migration 023

Adds to `users`:

```text
preferred_language ENUM('ar','en') NULL
```

Adds localization settings:

```text
platform_default_language = en
platform_supported_languages = ar,en
platform_language_cookie_days = 365
localization_rtl_enabled = 1
localization_copy_tone_ar = neutral_professional
localization_copy_tone_en = natural_saas
```

---

## Localization Helper / ملف اللغة المركزي

Implemented:

```text
backend/php/shared/Localization.php
```

Main functions:

```text
l10n_current_language
l10n_set_language
l10n_dir
l10n_is_arabic
l10n_language_switcher
__
```

---

## Language Preference / حفظ تفضيل اللغة

Supported:

```text
GET ?lang=ar / ?lang=en
Cookie: hn_lang
Logged-in user preference: users.preferred_language
```

Rules:

```text
If user changes language, cookie is updated.
If logged in, users.preferred_language is updated.
If no preference exists, Accept-Language and platform default are used.
```

---

## Language API / API تغيير اللغة

Implemented:

```text
POST /api/localization/set-language
```

Input:

```json
{
  "lang": "ar"
}
```

Response:

```json
{
  "ok": true,
  "lang": "ar",
  "dir": "rtl"
}
```

---

## Dashboard Shell Improvements / تحسينات Dashboard Shell

Updated:

```text
web/components/layout/dashboard_shell.php
```

Now includes:

```text
Localization helper
Persistent language switcher
Arabic RTL html dir
English LTR html dir
Bootstrap RTL for Arabic
Localized brand text
Localized navigation title
Localized logout/role labels
Correct active language button
JS i18n files loaded
Phase badge changed from fixed Phase 21 to Localization QA
```

---

## RTL/LTR CSS Fixes / إصلاحات RTL/LTR

Updated:

```text
web/public/assets/css/app.css
```

Adds:

```text
RTL body alignment
LTR body alignment
RTL dropdown menu alignment
RTL forms alignment
RTL tables alignment
LTR-safe class for email/url/code/number
Bidi isolate helper
RTL input-group border fixes
Mobile responsive card fixes
```

Important utility classes:

```text
.ltr-safe
.bidi-isolate
```

Use `.ltr-safe` for:

```text
Email addresses
URLs
Codes
API tokens
English-only IDs
Numbers that should stay LTR
```

---

## JavaScript i18n / ترجمة الواجهة بالـ JS

Created:

```text
web/public/assets/js/i18n/en.js
web/public/assets/js/i18n/ar.js
web/public/assets/js/i18n/apply-i18n.js
```

Supports:

```text
data-i18n="key"
data-i18n-placeholder="key"
```

This helps public pages or older pages translate labels without rewriting every PHP file immediately.

---

## Arabic Copy Rules / قواعد النص العربي

Documented in:

```text
docs/LOCALIZATION_QA_CHECKLIST.md
```

Avoid exclusively feminine forms:

```text
اكتبي
سجّلي
اختاري
أكملي
ارفعي
```

Use neutral forms:

```text
اكتب
سجّل
اختر
أكمل
ارفع
```

---

## English Copy Rules / قواعد النص الإنجليزي

Use natural SaaS-style English:

```text
Start Now
Book a Session
Complete Student Form
View Result
Open Homework
Save Changes
```

Avoid long paragraphs inside forms.

---

## QA Checklist / قائمة المراجعة

Created:

```text
docs/LOCALIZATION_QA_CHECKLIST.md
```

Covers:

```text
Public website
Pricing
Checkout
Thank-you
Student form
Level check
Booking calendar
Student portal
Parent portal
Academy portal
Owner dashboard
Homework/scenarios/reviews/materials
AI tools
Settings
Notifications
Emails/WhatsApp templates
```

---

## Acceptance Criteria Status / حالة القبول

Implemented now:

```text
User can switch Arabic/English in dashboard shell
Language preference persists by cookie
Logged-in user preference can persist in users.preferred_language
Arabic dashboard shell renders RTL
English dashboard shell renders LTR
Core navigation labels are bilingual
RTL/LTR CSS fixes added
Neutral Arabic copy rules documented
JS translation mechanism added
```

Partially implemented / requires continued audit:

```text
Every old hardcoded page label must be reviewed page by page
Public website shell should be connected to Localization.php where not using dashboard shell
Checkout/onboarding/level-check copy needs targeted audit
Email and WhatsApp template copy needs page-by-page QA
```

---

## Known Limitations / القيود الحالية

- This phase adds the central localization system and dashboard shell integration, but does not rewrite every hardcoded string across all previous phases.
- Public pages that do not use `dashboard_shell.php` need to include `Localization.php` or JS i18n manually.
- Some older pages may still have English-only labels until the QA checklist is completed page by page.
- Arabic UX copy needs a dedicated pass on checkout, onboarding, level checks, and AI tools.
- Automated detection of female-only Arabic copy is not implemented yet.

---

## Manual Test Checklist / قائمة الاختبار اليدوي

1. Run migration:

```text
backend/php/database/migrations/023_bilingual_localization.sql
```

2. Login as Owner.
3. Open:

```text
/owner/dashboard?lang=ar
```

4. Confirm page is RTL.
5. Confirm sidebar labels are Arabic.
6. Switch to English.
7. Confirm page is LTR.
8. Browse:

```text
/owner/settings
/owner/analytics
/owner/jobs
/student/dashboard
/parent/dashboard
/academy/dashboard
```

9. Confirm layouts do not break in Arabic.
10. Confirm email/code/url fields remain LTR.
11. Open mobile width and repeat Arabic/English checks.
12. Use:

```text
docs/LOCALIZATION_QA_CHECKLIST.md
```

for page-by-page copy QA.

---

## Stop Point / نقطة التوقف

Stop here. Test this phase before continuing.

توقف هنا. اختبر هذه المرحلة قبل الانتقال للمرحلة التالية.
