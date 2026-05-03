# Phase 0000 Start — Reviewed Prompt
# برومت البداية بعد المراجعة

## Review Summary / ملخص المراجعة

This phase is approved as the starting prompt for the Habiba Nabil Arabic Academy SaaS platform, with one required stack correction:

تم اعتماد هذه المرحلة كبرومت البداية لمنصة Habiba Nabil Arabic Academy SaaS، مع تعديل إجباري واحد في التقنيات:

- Replace React Native / Expo with Flutter.
- استبدال React Native / Expo بـ Flutter.

---

## Approved Prompt / البرومت المعتمد

You are a senior full-stack product engineer and product architect.

Build a new full-stack SaaS-style learning platform from scratch for Habiba Nabil Arabic Academy.

## Approved Tech Stack

Use only:

- Backend: PHP
- Database: MySQL
- Web frontend: HTML5, CSS3, Bootstrap, JavaScript
- Mobile app: Flutter
- Firebase: supporting service only when needed

Firebase can be used for push notifications, analytics, crash reporting, remote config, app services, or authentication only if selected intentionally.

Do not use Next.js, Supabase, Node.js, MongoDB, Prisma, Laravel, React Native, or Expo unless explicitly requested later.

## Product Name

Habiba Nabil Arabic Academy

## Product Idea

Learn Arabic the Smart Way

## Purpose

A complete online Arabic learning platform for non-native Arabic speakers.

The platform must support:

1. Public visitors
2. Students
3. Parents of child learners
4. Academy partners
5. One Owner/Teacher user

There is no separate admin and teacher in this version. The same user is Owner/Teacher.

## Core Roles

1. Public Visitor
2. Owner/Teacher
3. Student
4. Parent
5. Academy Partner

## Teaching Philosophy

- Speaking-first
- Practical Arabic
- Real-life communication
- Gradual learning
- Light grammar only when needed
- Vocabulary in context
- Confidence and speaking output are the priority
- Every learner starts from their real level, not automatically from zero

## Important Global Rules

- Build incrementally by phases.
- Do not build everything at once.
- Do not hardcode secrets or API keys.
- Use environment variables.
- Never store card data.
- Do not mark payment as paid unless verified or manually approved.
- Every important action should be logged in an audit log.
- AI outputs must always be preview/draft first.
- AI must never auto-publish.
- Student can only access their own data.
- Parent can only access linked child data.
- Academy partner can only access their own briefs.
- Owner/Teacher can access all dashboards and settings.
- UI must be responsive.
- Support English and Arabic.
- Support RTL/LTR.
- Arabic copy must be neutral, not female-only.
- Use clean SaaS UI: cards, rounded corners, soft shadows, teal/green brand feel, light/dark mode.
- Keep every feature separated into its own files.
- Keep every role inside its own folder.
- Any new feature related to a role must be placed inside that role folder.
- Use `ar.js` and `en.js` for web translations.
- Write useful comments in important files explaining why the logic exists and what can be improved later.

## Main Modules To Build Across Phases

- Public website
- Pricing and checkout
- Payment verification workflow
- Post-payment onboarding
- Adult level check
- Child literacy check
- Owner review and approval
- Student/Parent account creation after approval
- Student portal
- Parent portal
- Academy partner portal
- Owner/Teacher dashboard
- Lesson credits/package balance
- Real booking availability calendar
- Homework system
- Speaking scenario system
- Reviews/tests
- Materials
- Practice words and flashcards
- Badges, streaks, badge settings
- AI teacher tools
- AI weekly summaries
- Generate Article tool
- Email and WhatsApp templates
- Referral system
- Advanced analytics
- Firebase push notifications
- Automated reminders/cron jobs
- Full mobile app later using Flutter

## Development Method

For each phase:

1. Explain what you will build.
2. Build only that phase.
3. Include database models/migrations needed.
4. Include pages/routes/components needed.
5. Include API routes needed.
6. Include UI states: loading, error, empty.
7. Include security checks.
8. Seed demo data if relevant.
9. Add useful comments in important files.
10. At the end, report:
   - Files created/changed
   - Database tables/migrations changed
   - Routes/pages created
   - Tests performed
   - Known limitations
   - Manual test checklist
11. Stop and say: “Stop here. Test this phase before continuing.”

Do not continue to the next phase until the user explicitly asks to continue.

---

## Notes / ملاحظات

- The original phase is good as a foundation.
- The only rejected part was React Native/Expo because the approved mobile stack is Flutter.
- CSS5 was corrected conceptually to CSS3 because CSS5 is not a real standard stack term.
- This reviewed file should be used as the approved reference for Phase 0000.
