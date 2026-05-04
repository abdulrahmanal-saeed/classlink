# Hostinger Deployment Guide

## Deployment Type Recommendation

This project is currently:

```text
PHP + MySQL / MariaDB
Server-rendered PHP pages
Role-based dashboards
Database-backed login/auth
Uploads
Payments/AI/Firebase/email placeholders
Cron endpoints planned
```

It is **not** a Node.js/Next.js app and it is **not** a static website.

## Recommended Path

### Primary Recommendation

```text
Hostinger Shared Hosting with PHP + MySQL / MariaDB
```

Use File Manager / public_html upload for the PHP public files, with backend folders and `.env` protected as much as possible.

### Use VPS if Needed

Use Hostinger VPS only if you need:

```text
Full server control
Custom cron setup
Private upload routing outside public_html
Advanced queue workers
Custom PHP extensions
Better process/log management
```

### Do Not Use Static Upload Only

A simple static upload is not enough because the app has:

```text
login
dashboards
PHP backend
database
uploads
payments
AI settings
cron endpoints
```

---

# PATH A — Hostinger Node.js App Deployment

Not recommended for the current project.

Use this path only if the project is later converted into a Node.js/Next.js app.

If used later, typical steps would be:

1. Open Hostinger hPanel.
2. Go to Websites.
3. Choose the domain.
4. Open Dashboard.
5. Choose Node.js Apps / Web Apps.
6. Connect GitHub or upload project.
7. Set install command: `npm ci`.
8. Set build command: `npm run build`.
9. Set start command: `npm run start`.
10. Add environment variables.
11. Run migrations.
12. Test login, checkout, dashboards, and uploads.

Current project note:

```text
No package.json / Node production start command is required for the current PHP app.
```

---

# PATH B — Hostinger File Manager / public_html Upload

Use this path for the current PHP/MySQL app.

## Step 1 — Prepare Files

Recommended folders/files to upload:

```text
web/public/*        -> public_html/
backend/           -> protected app folder if possible
web/components/    -> protected app folder if possible
.env               -> protected project root, not public
```

If Hostinger structure does not support a protected app folder easily, upload the whole project carefully and block access to sensitive folders where possible.

## Step 2 — Open hPanel

1. Open Hostinger hPanel.
2. Go to Websites.
3. Choose the target domain.
4. Open Dashboard.
5. Open File Manager.
6. Open `public_html`.

## Step 3 — Upload Public Files

1. Upload the contents of:

```text
web/public
```

2. Extract inside:

```text
public_html
```

3. Confirm this file exists:

```text
public_html/index.php
```

## Step 4 — Upload Backend Files

The public files reference backend files using relative paths.

For simplest deployment, preserve repository structure so paths still work.

Recommended staging structure:

```text
/home/USER/domains/DOMAIN/app/backend
/home/USER/domains/DOMAIN/app/web/components
/home/USER/domains/DOMAIN/public_html
```

If you change structure, update PHP `require_once` paths carefully.

## Step 5 — Add .env

Create `.env` using:

```text
release/.env.example
```

Do not use real secrets in GitHub.

## Step 6 — Create Database

Follow:

```text
release/HOSTINGER_DATABASE_GUIDE.md
```

## Step 7 — Import Migrations

Use phpMyAdmin to import migration files from:

```text
backend/php/database/migrations
```

Run in numeric order.

## Step 8 — Configure Upload Folders

Follow:

```text
release/HOSTINGER_STORAGE_UPLOADS_GUIDE.md
```

## Step 9 — Test Production

Test:

```text
/
/pricing
/login
/owner/dashboard
/student/dashboard
/parent/dashboard
/academy/dashboard
/media/dashboard
/checkout?plan=single
```

## Step 10 — Check Logs

Check Hostinger error logs if:

```text
blank page
500 error
login fails
database connection fails
uploads fail
checkout fails
```

Common missing values:

```text
DB_HOST
DB_NAME
DB_USER
DB_PASS
APP_URL
APP_KEY
ZIINA_API_TOKEN if payments enabled
AI_API_KEY if AI enabled
```

---

# PATH C — VPS Deployment

Use this only if shared hosting is not enough.

High-level steps:

1. Create Hostinger VPS.
2. Install PHP, web server, and MySQL/MariaDB.
3. Upload project or connect GitHub.
4. Configure `.env`.
5. Configure database.
6. Run migrations.
7. Configure web root to `web/public`.
8. Configure private storage/upload folders.
9. Configure cron jobs.
10. Configure SSL.
11. Test all production flows.

VPS requires more technical setup but gives better control.

---

## What to Do if Deployment Fails

### 500 Error

Check:

```text
PHP version
missing files
wrong require_once path
wrong .env path
DB connection error
file permissions
```

### Database Error

Check:

```text
DB_HOST
DB_NAME
DB_USER
DB_PASS
DB_CHARSET=utf8mb4
migrations imported
```

### Upload Error

Check:

```text
upload folder exists
write permissions
PHP upload_max_filesize
PHP post_max_size
app upload limits
```

### Payment Error

Check:

```text
Ziina token
Ziina test/live mode
server-side status check
payment audit logs
```

### AI Error

Check:

```text
AI_ENABLED
AI_PROVIDER
AI_API_KEY
AI_MODEL
AI logs
```

---

## Redeploy After Changes

1. Export database backup if database changed.
2. Upload changed PHP/CSS/JS files.
3. Run any new migrations.
4. Clear browser cache.
5. Test login and dashboards.
6. Check logs.
