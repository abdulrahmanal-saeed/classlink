# Habiba Nabil SaaS Platform

## عربي

هذا هو الريبو الأساسي لبناء منصة SaaS خاصة بـ Habiba Nabil Arabic Academy.

المشروع عبارة عن موقع ويب وتطبيق موبايل لإدارة الطلاب، أولياء الأمور، شركاء الأكاديمية، الواجبات، السيناريوهات، اختبارات المستوى، المتابعة، الإشعارات، والمدفوعات لاحقًا.

## English

This is the main repository for building the Habiba Nabil Arabic Academy SaaS platform.

The project includes a bilingual web platform and mobile app for student, parent, academy partner, homework, speaking scenario, level test, progress tracking, notification, and payment workflows.

---

## Production Security Notice / ملاحظة أمان قبل الإنتاج

Phase 34 security correction:

- Active database is MySQL / MariaDB.
- The current PHP database connection file `backend/php/config/db.php` uses PDO MySQL and this is the approved direction for this project.
- The earlier Supabase PostgreSQL note was sent by mistake and is not part of the current production plan.
- Do not use Replit internal `@base` database.
- Secrets must remain server-side only and must never be committed to GitHub.

---

## Approved Tech Stack / التقنيات المعتمدة

- Backend: PHP
- Database: MySQL / MariaDB
- Web Frontend: HTML5, CSS3, Bootstrap, JavaScript
- Mobile App: Flutter
- Firebase: supporting service only when needed

Firebase may be used for:

- Push notifications
- Analytics
- Crashlytics
- Remote Config
- App services
- Authentication only if selected intentionally

---

## Active Roles / الرولات المعتمدة حاليًا

- Public Visitor / زائر عام
- Owner/Teacher / المالك والمعلم
- Student / الطالب
- Parent / ولي الأمر
- Academy Partner / شريك الأكاديمية
- Media Buyer / Marketing Partner / شريك التسويق

There is no separate Admin and Teacher in the current product direction. The same user is Owner/Teacher.

لا يوجد Admin منفصل عن Teacher في الاتجاه الحالي للمنتج. نفس المستخدم هو Owner/Teacher.

---

## Rejected Unless Explicitly Requested / مرفوض إلا لو اتطلب صراحة

- Next.js
- Node.js
- MongoDB
- Prisma
- React Native
- Expo
- Laravel
- Supabase PostgreSQL unless explicitly re-approved later
- Supabase Storage unless explicitly requested

---

## Main Architecture Rules / قواعد تنظيم المشروع

- The website and app must support Arabic and English.
- Arabic UI must support RTL.
- English UI must support LTR.
- Every major feature must be separated into its own files.
- Every user role must have its own folder.
- Any new feature related to a specific role must be placed inside that role folder.
- Translation files must be separated into `ar.js` and `en.js` for web.
- Code must be clean, readable, secure, and easy to maintain.
- Comments must explain why something is done, not only what it does.
- Each feature should be built in a way that makes future editing easy.

---

## Security Rules / قواعد الأمان

- Protected pages must call server-side role checks.
- Frontend hiding buttons is never enough.
- Student data must be isolated by student ownership checks.
- Parent data must be isolated by linked-child checks.
- Academy partner data must be isolated by partner ownership checks.
- Media buyer data must be isolated by media buyer ownership checks.
- Payment must never be marked paid from frontend redirect alone.
- Uploads must validate MIME type, extension, size, and role.
- Dangerous upload extensions are blocked.
- AI keys, Ziina keys, Firebase service credentials, and database credentials must be server-side only.
- Critical actions must write audit logs.

---

## Documentation Files / ملفات التوثيق

Project documentation lives inside the `docs/` folder:

- `docs/PROJECT_MASTER_SPEC.md`
- `docs/TECH_STACK_RULES.md`
- `docs/ARCHITECTURE_AND_FOLDERS.md`
- `docs/CODING_STANDARDS.md`
- `docs/BRAND_GUIDELINES.md`
- `docs/PHASES.md`
- `docs/SECURITY_AUDIT_REPORT.md`
- `docs/DEPLOYMENT_CHECKLIST.md`

---

## Main Project Goal / الهدف الرئيسي

Build a clean, bilingual SaaS platform for Arabic learning with:

- PHP backend
- MySQL / MariaDB database
- Flutter mobile app
- Firebase support services
- Arabic and English UI
- Role-based dashboards
- Clean feature-based code structure
- Strong access control and production hardening
