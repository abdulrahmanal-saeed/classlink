# Security Audit Report — Phase 34

## Project

Habiba Nabil Arabic Academy SaaS Platform

## Phase

Security Audit, Access Control, Data Protection, and Production Hardening

## Scope

This audit focuses on security, access control, data protection, upload safety, payment safety, AI key safety, and production readiness.

No business features were added.
No redesign was performed.
No database provider migration was performed.

---

## Executive Summary

### Status

```text
NOT READY FOR PRODUCTION UNTIL CRITICAL BLOCKERS ARE RESOLVED
```

### Main Findings

1. **Critical blocker:** The project instruction says the active production database must be Supabase PostgreSQL, but the current repository database connection file `backend/php/config/db.php` uses PDO MySQL variables and a MySQL DSN.
2. Authentication had secure session basics, but needed login rate limiting and stricter session settings.
3. Central upload validation was missing; a shared upload security helper was added.
4. Central security headers, CSRF helpers, hashing helpers, and rate limiting helpers were missing; a shared security helper was added.
5. `.env.example` had MySQL-first values and lacked placeholders for AI/upload/security limits; it was updated with safe placeholders and a production warning.
6. README incorrectly documented MySQL as the approved database without the production Supabase PostgreSQL warning; it was updated.

---

## Issues Fixed in This Phase

### 1. Authentication Hardening

Updated:

```text
backend/php/core/Auth.php
```

Added:

```text
Login rate limiting by email + client fingerprint
Session strict mode
HTTPS-aware secure cookie setting
login_at session timestamp
Security headers on protected pages
require_any_role helper
Safe generic login error message
```

Existing secure behavior confirmed:

```text
Password verification uses password_verify
Session ID regenerates on login
Logout clears session and destroys cookie
Protected pages call require_role
Unauthorized access is audit logged
```

### 2. Shared Security Helper

Created:

```text
backend/php/shared/Security.php
```

Includes:

```text
security_headers
security_csrf_token
security_csrf_input
security_verify_csrf
security_rate_limit_check
security_rate_limit_record
security_rate_limit_clear
security_hash_value
security_client_fingerprint
security_require_post
security_require_json
```

### 3. Upload Security Helper

Created:

```text
backend/php/shared/UploadSecurity.php
```

Includes validation for:

```text
MIME type
File extension
File size
Dangerous extensions
Safe stored filename generation
Private download headers
```

Blocked extensions include:

```text
.php
.phtml
.phar
.exe
.bat
.cmd
.sh
.msi
.dll
.ps1
.com
.scr
.cgi
.pl
.py
.rb
.jar
```

Allowed upload categories include:

```text
audio
video
image
pdf
powerpoint
document
html
```

Important: Existing upload routes must adopt this helper page-by-page before production.

### 4. Environment Documentation Hardening

Updated:

```text
.env.example
```

Added safe placeholders for:

```text
Supabase PostgreSQL database URLs
Security pepper
Login rate limits
Upload limits
AI provider/API key placeholders
Firebase service account placeholder
Ziina token placeholder
```

No real secrets were added.

### 5. README Production Security Update

Updated:

```text
README.md
```

Added:

```text
Production security notice
Database provider blocker warning
Security rules
Supabase PostgreSQL production target note
No Replit @base database warning
```

---

## Critical Production Blockers

### Blocker 1 — Database Provider Mismatch

Requirement:

```text
The active database is Supabase PostgreSQL.
The app must not use Replit internal @base database.
Do not change database provider in this phase.
```

Finding:

```text
backend/php/config/db.php currently uses MySQL DSN:
mysql:host={$host};port={$port};dbname={$name};charset={$charset}
```

Risk:

```text
Production may be deployed against the wrong database provider.
Supabase PostgreSQL URLs in .env would not be used by current db.php.
MySQL migrations are not PostgreSQL-compatible.
```

Required action before production:

```text
Create a dedicated database-provider migration phase.
Update db.php to support Supabase PostgreSQL using DATABASE_URL / DATABASE_URL_SESSIONPOOLER.
Convert or replace MySQL-specific migrations with PostgreSQL-compatible schema.
Run full regression tests.
Do not launch until resolved.
```

### Blocker 2 — Upload Routes Must Adopt UploadSecurity.php

Finding:

```text
A central upload validator was added, but existing upload endpoints must be patched individually to use it.
```

Risk:

```text
A legacy upload endpoint could still allow unsafe files if not updated.
```

Required action:

```text
Patch every upload route to call upload_security_validate before move_uploaded_file.
```

### Blocker 3 — Route/API Ownership Audit Must Be Completed Page-by-Page

Finding:

```text
Role-based pages use require_role in many areas, but route-by-route ownership checks must be verified for dynamic IDs.
```

Risk:

```text
Student/Parent/Academy/Media Buyer users may access records by changing IDs if a page lacks ownership filtering.
```

Required action:

```text
Audit each dynamic route and API endpoint.
Ensure SQL queries include current user ownership conditions.
Return 403 or safe redirect when ownership fails.
```

---

## Authentication Security Checklist

| Check | Status | Notes |
|---|---:|---|
| Secure password verification | Pass | Uses `password_verify` in Auth.php |
| Generic login error | Pass | Uses “Invalid email or password.” |
| Login rate limiting | Improved | Added temp-file based rate limiter |
| Session regeneration on login | Pass | `session_regenerate_id(true)` |
| HttpOnly session cookie | Pass | Set in session params |
| Secure cookie over HTTPS | Pass | HTTPS-aware setting |
| Logout clears session | Pass | Session cleared and destroyed |
| Protected pages require login | Partial | Pages using `require_role` pass; all routes still need audit |
| Account lockout | Partial | Cooldown/rate-limit added, not database lockout |

---

## Role-Based Access Control Checklist

| Area | Status | Notes |
|---|---:|---|
| `/owner/*` | Partial pass | Requires route-by-route check; dashboard uses owner role |
| `/student/*` | Partial pass | Must verify dynamic ownership pages |
| `/parent/*` | Partial pass | Must verify linked child ownership on every child route |
| `/academy/*` | Partial pass | Must verify brief ownership on every detail route |
| `/media/*` | Improved | Media agreement gating exists; ownership checks still need full API audit |
| `/api/*` | Needs audit | API endpoints must be checked one by one |
| Upload/download routes | Needs audit | Must adopt UploadSecurity.php and ownership checks |

---

## Data Ownership Rules Required

### Student

Student queries must filter by:

```text
current user id / student_user_id / student profile id
```

Student must only access:

```text
own homework
own scenario
own reviews/tests
own materials
own notifications
own package balance
own bookings
own progress
own level checks
```

### Parent

Parent queries must verify linked child relation before showing:

```text
homework
scenarios
reviews
materials
progress
balance
bookings
```

### Academy Partner

Academy partner queries must filter by:

```text
academy_partner_user_id = current user id
```

### Media Buyer

Media buyer queries must filter by:

```text
media_buyer_id linked to current user id
```

---

## Database and Secret Security Status

| Secret | Required status | Audit status |
|---|---|---|
| DATABASE_URL | Server-side only | Placeholder added, connection not yet using it |
| DATABASE_URL_SESSIONPOOLER | Server-side only | Placeholder added, connection not yet using it |
| Ziina API token | Server-side only | Placeholder only |
| AI API key | Server-side only | Placeholder only |
| Firebase service account | Server-side only | Placeholder only |
| `.env.example` | Placeholders only | Pass |
| Replit @base | Must not be used | No @base usage confirmed in changed files; full repo grep recommended |
| Startup logs | Must not expose secrets | Needs runtime check |

---

## Payment Security Status

Payment rules that must remain enforced:

```text
Never mark payment paid from frontend redirect alone.
Thank-you can set pending_verification if no confirmed provider status.
Plan amount must be calculated server-side.
Checkout reference must be secure and non-guessable.
Manual status changes must be Owner-only and audit logged.
No card data should be stored.
```

Audit status:

```text
Needs route-level review of checkout, thank-you, Ziina status check, and Owner payment actions.
```

Known requirement:

```text
User must not be able to fake payment by editing URL.
```

---

## Upload Security Status

Fixed foundation:

```text
UploadSecurity.php created.
Dangerous extensions blocked centrally.
MIME and extension validation available.
File size validation available.
Safe stored filenames generated.
```

Still required:

```text
Patch every upload endpoint to use UploadSecurity.php.
Protect private upload downloads with ownership checks.
Ensure HTML materials are sandboxed or download-only.
Ensure unapproved testimonial media is not public.
```

---

## Free Level Test Security Status

Required checks:

```text
Attempt token/reference must be secure.
Correct answers must not be exposed before submission.
Attempts must not regenerate on refresh.
Step locking must be server-side.
Owner review pages must be protected.
Uploads must use UploadSecurity.php.
```

Audit status:

```text
Needs dedicated route/API audit.
```

---

## Notification Security Status

Required checks:

```text
Notification list must filter by current user.
Mark-as-read must affect current user's notification only.
Action URLs must still go through protected route ownership checks.
```

Audit status:

```text
Needs endpoint-level audit.
```

---

## AI Security Status

Required rules:

```text
AI key server-side only.
AI output draft/preview only.
No auto-publish.
Owner-only AI logs.
Fail safely if missing key.
No secrets in AI logs.
```

Audit status:

```text
AI environment placeholders added.
Route-level AI implementation must be reviewed before enabling AI in production.
```

---

## Owner Settings Security Status

Required:

```text
Owner-only access.
Validate settings.
Audit log critical settings changes.
No secret values returned to browser.
Mask API keys.
```

Audit status:

```text
Needs page-by-page settings audit.
```

---

## Audit Log Coverage Required

Critical actions that must be audited:

```text
payment status changes
owner approval/rejection
account creation
role changes
settings changes
homework correction
scenario correction
review manual override
material creation/deletion
testimonial approval/rejection
free level test review
package/credit manual adjustment
booking confirmation/cancellation
AI apply/save action
media buyer agreement acceptance
commission/payout changes
```

Current status:

```text
AuditLogger exists and several actions use it.
Full coverage must be verified route-by-route.
```

---

## Public Website Security Status

Required:

```text
Public content must show approved/published items only.
Public forms sanitize input.
Testimonials are moderated.
Unsafe HTML is not rendered directly.
Production errors do not expose stack traces.
```

Audit status:

```text
Needs route-level public form and CMS rendering review.
```

---

## API Security Status

Required:

```text
Auth checks on protected APIs.
Role checks on every protected API.
Ownership checks on dynamic IDs.
Input validation.
Safe error messages.
Rate limiting for login, checkout, uploads, level test submit, testimonial submit, contact forms.
```

Fixed foundation:

```text
Security.php now provides POST, JSON, CSRF, and rate-limit helpers.
```

Still required:

```text
Patch API endpoints to use these helpers.
```

---

## Production Security Checklist

Before production launch:

```text
Resolve database provider mismatch: Supabase PostgreSQL vs current PDO MySQL.
Set APP_ENV=production.
Set APP_DEBUG=false.
Use HTTPS only.
Set a strong APP_KEY and SECURITY_HASH_PEPPER.
Store all secrets in Hostinger/server env only.
Do not commit .env.
Configure Ziina production/test mode intentionally.
Keep AI disabled until key is configured and route security is verified.
Configure upload limits.
Patch all upload routes to use UploadSecurity.php.
Verify every dynamic route ownership check.
Verify every API route role and ownership check.
Verify payment cannot be faked from URL.
Verify audit log is Owner-only.
Verify public errors do not show stack traces.
Verify System Health does not expose secret values.
Run the manual security test list.
```

---

## Manual Security Tests Required

```text
1. Logged-out user tries /owner/dashboard.
2. Student tries /owner/dashboard.
3. Parent tries /student/dashboard.
4. Student A tries Student B result URL.
5. Parent tries unlinked child URL.
6. Academy Partner tries another partner brief.
7. User tries to fake payment status in URL.
8. User uploads invalid .php file.
9. User uploads oversized file.
10. Public tries to access private upload.
11. Public tries to access unapproved testimonial.
12. Public tries to access another level test result.
13. Student tries another notification action URL.
14. Missing AI key does not crash AI page.
15. Missing Ziina key does not crash checkout.
```

---

## Files Changed in Phase 34

```text
backend/php/shared/Security.php
backend/php/shared/UploadSecurity.php
backend/php/core/Auth.php
.env.example
README.md
docs/SECURITY_AUDIT_REPORT.md
```

---

## Final Security Decision

```text
Do not launch production until critical blockers are resolved.
```

Main blocker:

```text
Current repository database connection is PDO MySQL, while production requirement says Supabase PostgreSQL.
```

Stop here. Review this security report before continuing.
