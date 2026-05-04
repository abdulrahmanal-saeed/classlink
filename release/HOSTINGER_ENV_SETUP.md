# Hostinger Environment Variables Setup

## Purpose

This file explains how to configure production environment variables for Hostinger.

The real `.env` file must exist only on the server. Never upload real secrets to GitHub, public folders, screenshots, or ZIP files shared publicly.

## Where to Add Environment Variables

For a PHP shared-hosting deployment, create a `.env` file in the project root that is **outside public web access** if possible.

Recommended location:

```text
/home/USER/domains/DOMAIN/
```

The app currently loads `.env` from the repository/project root using:

```text
backend/php/config/db.php
```

If you upload files directly to `public_html`, make sure `.env` is not publicly accessible. The safer approach is:

```text
/home/USER/domains/DOMAIN/app/.env
/home/USER/domains/DOMAIN/public_html/...
```

and adjust paths only if required.

## Required Variables

```text
APP_ENV=production
APP_DEBUG=false
APP_URL=https://mshabibanabil.com
APP_TIMEZONE=Asia/Dubai
DB_HOST=your_hostinger_mysql_host
DB_PORT=3306
DB_NAME=your_database_name
DB_USER=your_database_user
DB_PASS=your_database_password
DB_CHARSET=utf8mb4
APP_KEY=long_random_secret
SECURITY_HASH_PEPPER=long_random_private_pepper
SESSION_NAME=hn_academy_session
LOGIN_MAX_ATTEMPTS=8
LOGIN_WINDOW_SECONDS=900
FORCE_HTTPS=true
```

## Required for Payments if Enabled

```text
ZIINA_API_BASE=https://api-v2.ziina.com/api
ZIINA_API_TOKEN=server_side_only
ZIINA_TEST_MODE=false
```

Use test mode only in staging.

## Required for AI if Enabled

```text
AI_ENABLED=true
AI_PROVIDER=anthropic
AI_MODEL=your_model_name
AI_API_KEY=server_side_only
AI_REGENERATE_LIMIT=3
```

AI should remain disabled until the Owner/Teacher configures and tests it.

## Optional Services

Email:

```text
EMAIL_HOST=
EMAIL_PORT=587
EMAIL_USER=
EMAIL_PASSWORD=
EMAIL_FROM_EMAIL=
EMAIL_FROM_NAME="Habiba Nabil Arabic Academy"
```

Firebase:

```text
FIREBASE_ENABLED=false
FIREBASE_PROJECT_ID=
FIREBASE_CLIENT_EMAIL=
FIREBASE_PRIVATE_KEY="placeholder_only"
```

Cron:

```text
CRON_SECRET=long_random_secret
```

## Upload Variables

```text
UPLOAD_STORAGE_DRIVER=local
UPLOAD_BASE_PATH=./uploads
PUBLIC_ASSETS_BASE_PATH=./public/assets
PUBLIC_FILE_BASE_URL=https://mshabibanabil.com
MAX_AUDIO_UPLOAD_BYTES=26214400
MAX_VIDEO_UPLOAD_BYTES=209715200
MAX_IMAGE_UPLOAD_BYTES=10485760
MAX_DOCUMENT_UPLOAD_BYTES=52428800
MAX_HTML_UPLOAD_BYTES=2097152
```

## Security Rules

- Never put real `.env` in `public_html` if it can be downloaded.
- Never include `.env` in release ZIPs.
- Never commit real API keys.
- Mask keys in screenshots.
- Keep AI, Ziina, Firebase, database, and email credentials server-side only.
