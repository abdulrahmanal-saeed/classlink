# Hostinger Deployment Guide
# دليل رفع المشروع على Hostinger

## Important Security Rule / قاعدة أمان مهمة

Never commit SSH passwords, database passwords, API tokens, or real `.env` files to GitHub.

لا ترفع أبدًا كلمات مرور SSH أو قاعدة البيانات أو API tokens أو ملف `.env` الحقيقي على GitHub.

If any secret was shared in chat or committed by mistake, rotate it immediately from the provider dashboard.

لو أي قيمة سرية اتشاركت في الشات أو اترفعت بالغلط، غيّرها فورًا من لوحة مزود الخدمة.

---

## Current Deployment Target / هدف النشر الحالي

Staging domain:

```text
https://staging.mshabibanabil.com
```

Expected public web root on Hostinger:

```text
/home/YOUR_HOSTINGER_USER/domains/mshabibanabil.com/public_html/staging
```

The web server should point to the contents of:

```text
web/public
```

---

## Recommended Upload Method / طريقة الرفع المقترحة

Run these commands from your local computer terminal, not from ChatGPT.

شغّل الأوامر دي من جهازك، وليس من داخل ChatGPT.

### 1. Pull latest code

```bash
git clone https://github.com/abdulrahmanal-saeed/classlink.git
cd classlink
```

If already cloned:

```bash
git pull origin main
```

### 2. Create production env file locally or on server only

Create `.env` on Hostinger only. Do not upload it to GitHub.

اعمل ملف `.env` على السيرفر فقط. لا ترفعه على GitHub.

Required variables:

```text
APP_NAME
APP_ENV
APP_DEBUG
APP_URL
APP_TIMEZONE
DB_HOST
DB_PORT
DB_NAME
DB_USER
DB_PASS
DB_CHARSET
APP_KEY
SESSION_NAME
ZIINA_API_BASE
ZIINA_API_TOKEN
ZIINA_TEST_MODE
```

### 3. Upload files with rsync

Use the template script:

```text
scripts/deploy-hostinger-template.sh
```

Before running it, replace placeholders with your server details on your own machine.

قبل تشغيله، استبدل placeholders ببيانات السيرفر على جهازك فقط.

### 4. Folder layout on server

Recommended structure:

```text
/home/YOUR_HOSTINGER_USER/domains/mshabibanabil.com/
  app/
    backend/
    docs/
    mobile/
    web/
    .env
  public_html/
    staging/
      index.php
      assets/
      checkout/
      thank-you/
      owner/
      ...
```

Simple staging option:

Upload the whole repository into a private app folder, then copy `web/public` contents into public_html/staging.

---

## Database Migration Order / ترتيب تشغيل قاعدة البيانات

Apply migrations in this order:

```text
backend/php/database/migrations/001_create_auth_tables.sql
backend/php/database/migrations/002_create_core_platform_tables.sql
backend/php/database/migrations/003_public_website_cms_foundation.sql
backend/php/database/migrations/004_checkout_payment_foundation.sql
backend/php/database/migrations/004b_ziina_payment_intent_support.sql
```

Then run demo seed only on staging/development if needed:

```bash
php backend/php/database/seeds/seed_demo_accounts.php
php backend/php/database/seeds/seed_phase_2_demo_data.php
```

---

## Post-deployment Test Checklist / اختبار بعد الرفع

Open:

```text
https://staging.mshabibanabil.com/
https://staging.mshabibanabil.com/pricing
https://staging.mshabibanabil.com/checkout?plan=single
https://staging.mshabibanabil.com/login
```

Test Owner login:

```text
owner@demo.com
```

Do not store the password in this document.

اختبر:

```text
/owner/dashboard
/owner/settings
/owner/payments
/owner/audit-log
```

---

## Ziina Test / اختبار Ziina

Make sure the server `.env` contains the Ziina variables.

تأكد أن `.env` على السيرفر يحتوي على متغيرات Ziina.

Then:

1. Open `/checkout?plan=single`.
2. Submit checkout form.
3. Confirm redirect to Ziina hosted payment page.
4. Complete test payment.
5. Confirm return to `/thank-you`.
6. Confirm status becomes paid only if Ziina returns completed.
7. Check `/owner/payments` and `/owner/audit-log`.

---

## Stop Rule / قاعدة التوقف

After every deployment, test staging before continuing to the next phase.

بعد كل رفع، اختبر staging قبل الانتقال للمرحلة التالية.
