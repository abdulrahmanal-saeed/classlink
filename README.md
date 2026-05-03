# Habiba Nabil SaaS Platform

## عربي

هذا هو الريبو الأساسي لبناء منصة SaaS خاصة بـ Habiba Nabil Arabic Academy.

المشروع عبارة عن موقع ويب وتطبيق موبايل لإدارة الطلاب، الواجبات، السيناريوهات، اختبارات المستوى، المتابعة، الإشعارات، والمدفوعات لاحقًا.

## English

This is the main repository for building the Habiba Nabil Arabic Academy SaaS platform.

The project includes a bilingual web platform and mobile app for student management, homework, speaking scenarios, level tests, progress tracking, notifications, and later payments.

---

## Approved Tech Stack / التقنيات المعتمدة

- Backend: PHP
- Database: MySQL
- Mobile App: Flutter
- Firebase: supporting service only when needed

Firebase may be used for:
- Push notifications
- Analytics
- Crashlytics
- Remote config
- App services
- Authentication only if selected intentionally

---

## Rejected Unless Explicitly Requested / مرفوض إلا لو اتطلب صراحة

- Next.js
- Supabase
- Node.js
- MongoDB
- Prisma
- React Native
- Laravel

---

## Main Architecture Rules / قواعد تنظيم المشروع

- The website and app must support Arabic and English.
- Arabic UI must support RTL.
- English UI must support LTR.
- Every major feature must be separated into its own files.
- Every user role must have its own folder.
- Any new feature related to a specific role must be placed inside that role folder.
- Translation files must be separated into `ar.js` and `en.js`.
- Code must be clean, readable, secure, and easy to maintain.
- Comments must explain why something is done, not only what it does.
- Each feature should be built in a way that makes future editing easy.

---

## Documentation Files / ملفات التوثيق

Project documentation should live inside the `docs/` folder:

- `docs/PROJECT_MASTER_SPEC.md`
- `docs/TECH_STACK_RULES.md`
- `docs/ARCHITECTURE_AND_FOLDERS.md`
- `docs/CODING_STANDARDS.md`
- `docs/BRAND_GUIDELINES.md`
- `docs/PHASES.md`

---

## Main Project Goal / الهدف الرئيسي

Build a clean, bilingual SaaS platform for Arabic learning with:

- PHP backend
- MySQL database
- Flutter mobile app
- Firebase support services
- Arabic and English UI
- Role-based dashboards
- Clean feature-based code structure
