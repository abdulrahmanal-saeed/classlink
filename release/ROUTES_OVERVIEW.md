# Routes Overview — Hostinger Release

## Public Routes

- `/`
- `/pricing`
- `/terms`
- `/privacy`
- `/refund`
- `/articles`
- `/articles/{slug}`
- `/videos`
- `/videos/{slug}`
- `/testimonials`
- `/submit-testimonial`
- `/help`
- `/help/student`
- `/help/parent`
- `/help/owner`
- `/help/academy`
- `/help/media-buyer`

## Authentication Routes

- `/login`
- `/logout`
- `/unauthorized`

## Checkout and Onboarding Routes

- `/checkout?plan=single`
- `/checkout?plan=monthly`
- `/checkout?plan=bundle`
- `/thank-you?ref={checkoutReference}`
- `/student-form?ref={checkoutReference}`
- `/level-check-intro?intakeId={id}`
- `/level-check?intakeId={id}`
- `/level-check-thank-you?attemptId={id}`

## Free Level Test Routes

- `/level-test`
- `/level-test/entry`
- `/level-test/register`
- `/level-test/start`
- `/level-test/thank-you`
- `/level-test/quick`
- `/level-test/quick-result`

Legacy redirect/support routes may exist under `/leveltest/*`.

## Owner / Teacher Routes

- `/owner/dashboard`
- `/owner/settings`
- `/owner/audit-log`
- `/owner/payments`
- `/owner/onboarding`
- `/owner/students`
- `/owner/parents`
- `/owner/calendar`
- `/owner/availability`
- `/owner/bookings`
- `/owner/packages`
- `/owner/homework`
- `/owner/scenarios`
- `/owner/reviews`
- `/owner/materials`
- `/owner/materials/new`
- `/owner/materials/categories`
- `/owner/materials/analytics`
- `/owner/notifications`
- `/owner/analytics`
- `/owner/referrals`
- `/owner/communication`
- `/owner/ai`
- `/owner/ai/logs`
- `/owner/help-center`
- `/owner/media-buyers`
- `/owner/media-commissions`

## Student Routes

- `/student/dashboard`
- `/student/profile`
- `/student/lessons`
- `/student/balance`
- `/student/homework`
- `/student/scenarios`
- `/student/reviews`
- `/student/materials`
- `/student/progress`
- `/student/flashcards`
- `/student/badges`
- `/student/practice-words`
- `/student/session-notes`
- `/student/notifications`
- `/student/referrals`
- `/student/testimonial`
- `/student/help`

## Parent Routes

- `/parent/dashboard`
- `/parent/children`
- `/parent/book`
- `/parent/notifications`
- `/parent/contact`
- `/parent/referrals`
- `/parent/testimonial`
- `/parent/help`

Child-specific parent routes exist under:

- `/parent/child/{id}/...`

or query-based child routes depending on implementation.

## Academy Partner Routes

- `/academy/dashboard`
- `/academy/briefs`
- `/academy/briefs/new`
- `/academy/help`

## Media Buyer Routes

- `/media/dashboard`
- `/media/agreement`
- `/media/agreement/accept`
- `/media/campaigns`
- `/media/links`
- `/media/orders`
- `/media/commissions`
- `/media/payouts`
- `/media/settings`
- `/media/help`

## API Routes

Key API route groups include:

- `/api/auth/login`
- `/api/auth/logout`
- `/api/auth/me`
- `/api/push/register-device`
- `/api/push/test`
- `/api/cron/send-lesson-reminders`
- `/api/cron/send-homework-reminders`
- `/api/cron/check-badges`
- `/api/cron/weekly-summaries`

Additional feature APIs exist under `/api/*` depending on the phase implementation.

## Security Notes

- All `/owner/*`, `/student/*`, `/parent/*`, `/academy/*`, and `/media/*` routes must require authentication.
- Role checks must happen server-side.
- Dynamic record routes must enforce ownership.
- API routes must enforce the same permissions as pages.
