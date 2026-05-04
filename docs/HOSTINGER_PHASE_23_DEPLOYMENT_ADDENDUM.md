# Hostinger Deployment Addendum — Phase 23

## Phase 23

Bilingual Arabic/English Website, RTL/LTR, and Localization QA.

## Database migration

Run after Phase 22:

```text
backend/php/database/migrations/023_bilingual_localization.sql
```

Export a database backup first.

## Backend files to upload

```text
backend/php/shared/Localization.php
backend/php/database/migrations/023_bilingual_localization.sql
```

## API files to upload

```text
web/public/api/localization/set-language/index.php
```

## Frontend assets to upload

```text
web/public/assets/js/i18n/en.js
web/public/assets/js/i18n/ar.js
web/public/assets/js/i18n/apply-i18n.js
web/public/assets/css/app.css
```

## Layout file to upload

```text
web/components/layout/dashboard_shell.php
```

## Docs to upload/keep

```text
docs/PHASE_23_EXECUTION_REPORT.md
docs/HOSTINGER_PHASE_23_DEPLOYMENT_ADDENDUM.md
docs/LOCALIZATION_QA_CHECKLIST.md
```

## URLs to test

```text
/owner/dashboard?lang=ar
/owner/dashboard?lang=en
/student/dashboard?lang=ar
/student/dashboard?lang=en
/parent/dashboard?lang=ar
/parent/dashboard?lang=en
/academy/dashboard?lang=ar
/academy/dashboard?lang=en
```

## Language API test

```text
POST /api/localization/set-language
```

Body:

```json
{
  "lang": "ar"
}
```

Expected:

```json
{
  "ok": true,
  "lang": "ar",
  "dir": "rtl"
}
```

## Manual QA

Check:

```text
Arabic pages render RTL
English pages render LTR
Language switcher persists preference
Sidebar labels switch language
Tables do not break in RTL
Forms align correctly
Emails, URLs, codes, and numbers remain LTR
Mobile layout works in both languages
```

Use:

```text
docs/LOCALIZATION_QA_CHECKLIST.md
```

## Known limitations

```text
This phase adds central localization foundation and dashboard shell integration.
Some older hardcoded strings still need page-by-page QA.
Public pages not using dashboard_shell.php need Localization.php or JS i18n integration.
Checkout, onboarding, level checks, emails, and WhatsApp templates need dedicated copy review.
```

Stop here. Test Phase 23 before continuing.
