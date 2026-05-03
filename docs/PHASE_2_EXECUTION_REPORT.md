# Phase 2 Execution Report
# تقرير تنفيذ المرحلة 2

## Phase Name / اسم المرحلة

Database Foundation, Settings Center Foundation, Audit Log, and Seed Data

تأسيس قاعدة البيانات، مركز الإعدادات، سجل المراجعة، وبيانات الديمو

---

## What was built / ما الذي تم بناؤه

Phase 2 created the core platform database foundation and added the first Owner-only Settings Center, Audit Log viewer, and development seed-data control.

قامت المرحلة 2 بإنشاء أساس قاعدة البيانات للمنصة، وإضافة أول نسخة من مركز إعدادات المالك، وسجل المراجعة، والتحكم في بيانات الديمو للتطوير.

---

## Files Created / الملفات التي تم إنشاؤها

### Database / قاعدة البيانات

```text
backend/php/database/migrations/002_create_core_platform_tables.sql
backend/php/database/seeds/seed_phase_2_demo_data.php
```

### Backend helpers / أدوات الباك إند

```text
backend/php/shared/Settings.php
```

### Owner pages / صفحات المالك

```text
web/public/owner/settings/index.php
web/public/owner/audit-log/index.php
web/public/owner/dev/seed-data/index.php
```

---

## Files Changed / الملفات التي تم تعديلها

```text
web/components/layout/dashboard_shell.php
```

The dashboard shell now shows Owner links for:

- Owner Dashboard
- Settings Center
- Audit Log
- Dev Seed Data

---

## Database Models / نماذج قاعدة البيانات

Phase 2 migration adds foundation tables for:

```text
student_profiles
parent_profiles
academy_briefs
plans
purchases
payment_records
student_intake_forms
level_check_attempts
level_check_answers
level_check_uploads
lesson_packages
lesson_credit_transactions
lesson_sessions
availability_rules
blocked_times
bookings
homeworks
homework_questions
homework_submissions
scenarios
scenario_submissions
review_tests
review_questions
review_submissions
course_materials
practice_words
flashcard_reviews
badge_definitions
student_badges
notifications
email_templates
whatsapp_templates
referrals
ai_usage_logs
articles
videos
testimonials
analytics_events
settings
```

Existing Phase 1 tables kept in place:

```text
users
user_profiles
parent_child_links
academy_partner_profiles
audit_logs
```

---

## Pages and Routes / الصفحات والمسارات

```text
/owner/settings
/owner/audit-log
/owner/dev/seed-data
```

All three pages are protected by `owner_teacher` role.

كل الصفحات محمية برول `owner_teacher` فقط.

---

## Settings Center Foundation / أساس مركز الإعدادات

Settings sections created in the UI:

```text
Pricing
Payment
Email templates
WhatsApp templates
SEO
AI settings
Upload limits
Lesson cancellation policy
Referral rewards
Badge settings
Availability
Notifications
Legal pages
```

The Owner can add or update a setting using:

- Section
- Setting key
- Setting value

Every setting update writes an audit log entry.

أي تعديل في الإعدادات يتم تسجيله في سجل المراجعة.

---

## Audit Log Viewer / صفحة سجل المراجعة

The Owner can open:

```text
/owner/audit-log
```

The page shows the latest 100 audit records, including:

- Time
- User
- Action
- Entity type
- Entity ID
- Metadata

---

## Development Seed Data / بيانات الديمو للتطوير

A development-only seed page was added:

```text
/owner/dev/seed-data
```

It only works when:

```text
APP_ENV=local
```

or:

```text
APP_ENV=development
```

The seed creates demo data for:

- Student profile
- Parent profile if parent exists
- Academy brief if academy partner exists
- Plans
- Pending purchase
- Badge definition
- Email template
- WhatsApp template
- Starter settings

It does not mark payments as paid.

لا يقوم بتحويل أي دفع إلى paid تلقائيًا.

---

## Security Checks / فحوصات الأمان

- Settings Center is Owner-only.
- Audit Log is Owner-only.
- Dev Seed Data page is Owner-only.
- Dev Seed Data page is blocked outside local/development environments.
- Settings changes are written to audit log.
- Payments are not auto-marked as paid.
- Core access still uses Phase 1 server-side role checks.

---

## Known Limitations / القيود الحالية

- This phase creates the database foundation, not full CRUD screens for every model.
- Settings Center is a simple key/value foundation, not final advanced forms.
- Audit Log viewer has basic filtering only by latest 100 records; advanced filters will come later.
- Demo seed can insert repeated plan rows if run multiple times; this should be improved later using unique plan keys.
- CSRF protection still needs to be strengthened before production.
- No migration runner has been built yet; SQL migrations are applied manually.

---

## Manual Test Checklist / قائمة الاختبار اليدوي

1. Apply Phase 1 migration first if not already applied:

```text
backend/php/database/migrations/001_create_auth_tables.sql
```

2. Apply Phase 2 migration:

```text
backend/php/database/migrations/002_create_core_platform_tables.sql
```

3. Confirm `.env` is configured with database credentials.

4. Run Phase 1 demo accounts if needed:

```bash
php backend/php/database/seeds/seed_demo_accounts.php
```

5. Start local server:

```bash
php -S localhost:8000 -t web/public
```

6. Login as Owner:

```text
owner@demo.com
Password: demo password
```

7. Open Settings Center:

```text
http://localhost:8000/owner/settings
```

8. Create a setting, for example:

```text
Section: Pricing
Key: pricing.test_price
Value: 80 AED
```

9. Open Audit Log:

```text
http://localhost:8000/owner/audit-log
```

10. Confirm `setting_updated` appears.

11. Open Dev Seed Data:

```text
http://localhost:8000/owner/dev/seed-data
```

12. Run seed data and confirm demo plans/settings/templates appear in the database.

13. Try opening `/owner/settings` while logged out and confirm redirect to `/login`.

14. Try opening `/owner/settings` as student and confirm redirect to `/unauthorized`.

---

## Stop Point / نقطة التوقف

Stop here. Test this phase before continuing.

توقف هنا. اختبر هذه المرحلة قبل الانتقال للمرحلة التالية.
