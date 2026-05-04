# Production Deployment Checklist

## Phase 34 Security Gate

Do not launch production until every critical blocker below is resolved.

## Critical Blockers

### 1. Database Provider

```text
Production requirement: Supabase PostgreSQL
Current repository implementation: PDO MySQL in backend/php/config/db.php
```

Required before launch:

```text
Create a dedicated database-provider migration phase.
Update db.php to use Supabase PostgreSQL / DATABASE_URL.
Convert schema/migrations to PostgreSQL-compatible SQL.
Run full regression tests.
Do not use Replit internal @base database.
```

### 2. Upload Endpoints

Required before launch:

```text
Patch every upload endpoint to use backend/php/shared/UploadSecurity.php.
Verify MIME, extension, size, and ownership checks.
Block dangerous extensions.
Protect private uploads from public access.
```

### 3. Route/API Ownership

Required before launch:

```text
Audit every dynamic route and API endpoint.
Student must only access own records.
Parent must only access linked child records.
Academy Partner must only access own briefs.
Media Buyer must only access own marketing data.
Owner-only routes must require owner_teacher.
```

---

## Environment

Production `.env` must be stored on the server only.

Required:

```text
APP_ENV=production
APP_DEBUG=false
APP_URL=https://mshabibanabil.com
APP_TIMEZONE=Asia/Dubai
APP_KEY=strong_random_secret
SECURITY_HASH_PEPPER=strong_random_private_pepper
SESSION_NAME=hn_academy_session
LOGIN_MAX_ATTEMPTS=8
LOGIN_WINDOW_SECONDS=900
FORCE_HTTPS=true
```

Database:

```text
DATABASE_URL=server_side_only
DATABASE_URL_SESSIONPOOLER=server_side_only
SUPABASE_DATABASE_URL=server_side_only
```

Secrets:

```text
ZIINA_API_TOKEN=server_side_only
AI_API_KEY=server_side_only
FIREBASE_SERVICE_ACCOUNT_JSON=server_side_only
WHATSAPP_API_TOKEN=server_side_only
```

Never commit `.env` to GitHub.

---

## Authentication Tests

```text
Logged-out user cannot access /owner/dashboard.
Student cannot access /owner/dashboard.
Parent cannot access /student/dashboard.
Academy Partner cannot access /owner/dashboard.
Media Buyer cannot access Owner pages.
Wrong password shows generic error.
Repeated failed login triggers cooldown/rate limit.
Logout clears session.
```

---

## Payment Tests

```text
Checkout amount is calculated server-side.
User cannot change plan amount from frontend.
Thank-you page does not mark paid from URL alone.
Pending payments remain pending_verification unless verified.
Owner manual status changes require confirmation.
Payment status changes are audit logged.
No card data is stored.
Ziina missing key fails safely.
```

---

## Upload Tests

```text
Upload valid mp3/wav/m4a/webm audio.
Upload valid jpg/png/webp image.
Upload valid pdf.
Upload valid ppt/pptx.
Try .php and confirm rejected.
Try .exe and confirm rejected.
Try oversized file and confirm rejected.
Try mismatched MIME/extension and confirm rejected.
Try unapproved testimonial media publicly and confirm blocked.
Try private student file as another student and confirm blocked.
```

---

## AI Tests

```text
AI disabled by default.
Missing AI key disables/fails safely.
AI key is never returned to browser.
AI logs are Owner-only.
AI output is preview/draft only.
AI cannot auto-publish articles/homework/scenarios/materials/messages.
```

---

## Notification Tests

```text
Student sees own notifications only.
Parent sees own/linked child notifications only.
Academy sees own brief notifications only.
Media Buyer sees own marketing notifications only.
Mark-as-read only affects current user's notification.
Notification action URL still enforces route permissions.
```

---

## Owner Settings Tests

```text
Only Owner/Teacher can change settings.
Critical settings changes are audit logged.
Invalid settings are rejected.
Secrets are masked and never returned full to browser.
```

---

## Public Website Tests

```text
Public pages load without stack traces.
Public pages show approved/published content only.
Unapproved testimonials are hidden.
Public forms sanitize input.
SEO metadata does not expose secrets.
```

---

## Technical Hardening

```text
HTTPS enabled.
Secure cookies enabled over HTTPS.
APP_DEBUG=false.
Server error logs are private.
Public directory listing disabled.
Upload folders have least-privilege permissions.
System Health does not expose secret values.
Browser console has no errors.
Network tab shows no secret values.
```

---

## Final Go/No-Go

Production launch is allowed only when:

```text
Database provider blocker is resolved.
All upload routes use UploadSecurity.php.
All dynamic routes/API endpoints pass ownership tests.
Payment cannot be faked.
Secrets are server-side only.
Security audit report has no unresolved critical issues.
```
