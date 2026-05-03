# Phase 3 Execution Report
# تقرير تنفيذ المرحلة 3

## Phase Name / اسم المرحلة

Public Website, Pricing, Legal Pages, and CMS Basics

الموقع العام، التسعير، الصفحات القانونية، وأساسيات نظام إدارة المحتوى

---

## What was built / ما الذي تم بناؤه

Phase 3 created the first real public marketing website and basic Owner CMS foundations.

قامت المرحلة 3 ببناء أول نسخة فعلية من الموقع التسويقي العام، وأساسيات إدارة المحتوى للمالك.

---

## Files Created / الملفات التي تم إنشاؤها

### Database / قاعدة البيانات

```text
backend/php/database/migrations/003_public_website_cms_foundation.sql
```

### Backend helpers / أدوات الباك إند

```text
backend/php/shared/PublicContent.php
```

### Layout / التخطيط

```text
web/components/layout/public_layout.php
```

### Public pages / الصفحات العامة

```text
web/public/pricing/index.php
web/public/terms/index.php
web/public/privacy/index.php
web/public/refund/index.php
web/public/articles/index.php
web/public/videos/index.php
web/public/testimonials/index.php
web/public/submit-testimonial/index.php
web/public/checkout/index.php
```

### Owner CMS pages / صفحات إدارة المحتوى

```text
web/public/owner/cms/articles/index.php
web/public/owner/cms/articles/new/index.php
web/public/owner/cms/articles/edit/index.php
web/public/owner/cms/videos/index.php
web/public/owner/cms/videos/edit/index.php
web/public/owner/cms/testimonials/index.php
web/public/owner/settings/public-website/index.php
```

---

## Files Changed / الملفات التي تم تعديلها

```text
web/public/index.php
web/public/.htaccess
web/components/layout/dashboard_shell.php
```

Changes:

- Homepage replaced with real marketing homepage.
- Clean URL rewrites added for articles and videos.
- Owner dashboard navigation now includes CMS links.
- Owner pages include noindex meta tag.

---

## Database Changes / تغييرات قاعدة البيانات

Migration 003 adds:

- SEO fields for articles.
- SEO, slug, description, and thumbnail fields for videos.
- Testimonial moderation fields.
- `public_page_contents` table for Terms, Privacy, and Refund pages.
- Default legal page placeholder content.
- Default pricing plans.

Important note:

Pricing default plans are inserted as launch offers:

```text
Single Session: AED 80 instead of AED 120, one 90-minute lesson.
Monthly Plan: AED 640 instead of AED 960, 8 sessions / 12 hours.
30-Hour Bundle: AED 1600 instead of AED 2400, 20 sessions / 30 hours, never expires.
```

---

## Public Pages / الصفحات العامة

Implemented:

```text
/
/pricing
/terms
/privacy
/refund
/articles
/articles/{slug}
/videos
/videos/{slug}
/testimonials
/submit-testimonial
/checkout?plan=single
/checkout?plan=monthly
/checkout?plan=bundle
```

The checkout page is a safe placeholder. Pricing CTAs go to checkout, not directly to payment.

صفحة checkout حالياً placeholder آمن. أزرار التسعير تذهب إلى checkout وليس للدفع مباشرة.

---

## Owner CMS / نظام إدارة المحتوى للمالك

Implemented:

```text
/owner/cms/articles
/owner/cms/articles/new
/owner/cms/articles/edit?id=...
/owner/cms/videos
/owner/cms/videos/edit?id=...
/owner/cms/testimonials
/owner/settings/public-website
```

Capabilities:

- Owner can create articles.
- Owner can edit articles.
- Owner can publish or move articles back to draft.
- Owner can create videos.
- Owner can edit videos.
- Owner can publish or move videos back to draft.
- Owner can approve/reject testimonials.
- Public testimonial submissions remain pending until approved.
- Owner can toggle homepage sections from settings.

---

## Homepage Section Toggles / تحكم أقسام الصفحة الرئيسية

Added toggles under:

```text
/owner/settings/public-website
```

Settings:

```text
homepage.show_articles
homepage.show_videos
homepage.show_testimonials
homepage.show_pricing
```

Each change is saved in `settings` and written to `audit_logs` through the settings helper.

---

## SEO / تحسين محركات البحث

Implemented:

- Meta title and description in public layout.
- One H1 per main public page.
- Articles and videos have SEO fields.
- Public pages are indexable.
- Owner dashboard/CMS pages are noindex.
- Legal pages are stored in `public_page_contents` with SEO fields.

---

## Security Checks / فحوصات الأمان

- Owner CMS is protected by `owner_teacher` role.
- Public testimonial submissions are saved as `pending` only.
- Only approved testimonials appear on public pages.
- Pricing buttons route to `/checkout`, not payment directly.
- Owner pages are noindex.
- Important CMS actions are written to audit log.

---

## Known Limitations / القيود الحالية

- Legal page content is placeholder text and must be reviewed by a lawyer before real use.
- Public pages are English-first in this phase; Arabic-ready structure exists but full bilingual content/forms can be expanded later.
- Article/video CMS is functional but simple; advanced image uploads, rich text editor, delete actions, and revision history are not built yet.
- Checkout is a safe placeholder only; payment workflow will be implemented later.
- Migration uses `ADD COLUMN IF NOT EXISTS` and `CREATE INDEX IF NOT EXISTS`; if the target MySQL/MariaDB version does not support that syntax, apply the SQL manually with adjusted statements.
- CSRF protection still needs to be strengthened before production.

---

## Manual Test Checklist / قائمة الاختبار اليدوي

1. Apply Phase 3 migration:

```text
backend/php/database/migrations/003_public_website_cms_foundation.sql
```

2. Start local server:

```bash
php -S localhost:8000 -t web/public
```

3. Open homepage desktop and mobile:

```text
http://localhost:8000/
```

4. Open pricing:

```text
http://localhost:8000/pricing
```

5. Click all pricing CTAs and confirm they go to:

```text
/checkout?plan=single
/checkout?plan=monthly
/checkout?plan=bundle
```

6. Open legal pages:

```text
/terms
/privacy
/refund
```

7. Login as Owner:

```text
owner@demo.com
Password: demo password
```

8. Create article draft:

```text
/owner/cms/articles/new
```

9. Publish article from:

```text
/owner/cms/articles
```

10. Open published article:

```text
/articles/{slug}
```

11. Create and publish a video:

```text
/owner/cms/videos
```

12. Open published video:

```text
/videos/{slug}
```

13. Submit testimonial publicly:

```text
/submit-testimonial
```

14. Confirm testimonial is not visible immediately on:

```text
/testimonials
```

15. Approve testimonial from:

```text
/owner/cms/testimonials
```

16. Confirm approved testimonial appears on:

```text
/testimonials
```

17. Change homepage toggles from:

```text
/owner/settings/public-website
```

18. Confirm audit log records settings and CMS actions:

```text
/owner/audit-log
```

---

## Stop Point / نقطة التوقف

Stop here. Test this phase before continuing.

توقف هنا. اختبر هذه المرحلة قبل الانتقال للمرحلة التالية.
