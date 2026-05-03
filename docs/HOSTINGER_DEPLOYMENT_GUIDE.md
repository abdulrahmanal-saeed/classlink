# Hostinger Deployment Guide
# دليل رفع المشروع على Hostinger باستخدام File Manager و phpMyAdmin

> آخر تحديث لهذا الدليل: بعد Phase 8  
> Current covered phases: Phase 0 → Phase 8

---

## 0) قاعدة ذهبية قبل أي رفع

لا ترفع أبدًا على GitHub:

```text
.env
SSH password
Database password
API tokens
Ziina API token
أي بيانات دخول حقيقية
```

أي بيانات حقيقية تتحط يدويًا داخل Hostinger فقط.

لو أي secret اتشارك أو اترفع بالغلط، غيّره فورًا من مزود الخدمة.

---

## 1) الهدف من الدليل

هذا الملف يشرح بالتفصيل الممل طريقة رفع مشروع Habiba Nabil Arabic Academy على Hostinger من خلال:

```text
Hostinger File Manager
phpMyAdmin
```

بدون الاعتماد على SSH في الخطوات الأساسية.

الدليل يغطي:

1. ترتيب ملفات المشروع على Hostinger.
2. ماذا ترفع من GitHub.
3. أين تضع كل فولدر.
4. ماذا تكتب داخل `.env`.
5. كيف تشغل migrations من phpMyAdmin.
6. ماذا تختبر بعد كل Phase.
7. قائمة بكل Phases المنفذة حتى الآن.
8. القاعدة التي سنتبعها بعد كل Phase جديدة.

---

## 2) معلومات عامة عن Hostinger

Hostinger File Manager هو أداة لإدارة ملفات الموقع مباشرة من المتصفح داخل hPanel. ملفات الموقع التي تظهر للزوار يجب أن تكون داخل فولدر `public_html` أو فولدر الدومين/الساب دومين المخصص للموقع.

phpMyAdmin يستخدم لاستيراد ملفات SQL أو تشغيل أوامر SQL داخل قاعدة البيانات. عادة يمكن رفع ملفات `.sql` أو `.sql.zip` من تبويب Import.

---

## 3) Target الحالي

Staging domain:

```text
https://staging.mshabibanabil.com
```

المسار المتوقع للـ staging public root:

```text
/home/<HOSTINGER_USER>/domains/mshabibanabil.com/public_html/staging
```

لا تكتب بيانات المستخدم الحقيقية في هذا الملف. استخدمها فقط داخل Hostinger.

---

## 4) فهم هيكل المشروع الحالي

المشروع في GitHub مقسم تقريبًا هكذا:

```text
backend/
  php/
    config/
    core/
    database/
      migrations/
      seeds/
    shared/

web/
  public/
    index.php
    login/
    owner/
    student/
    parent/
    level-test/
    level-check/
    assets/
  components/
    layout/

docs/
phases/
.env.example
README.md
```

### معنى كل فولدر

| الفولدر | وظيفته | هل يظهر للزائر؟ |
|---|---|---|
| `web/public` | ملفات الموقع العامة التي يجب أن تكون داخل public root | نعم |
| `backend` | كود PHP الخلفي، الاتصال بقاعدة البيانات، helpers | لا يفضل أن يكون public |
| `web/components` | ملفات layout المشتركة التي تستدعيها صفحات public | لا تظهر مباشرة |
| `backend/php/database/migrations` | ملفات SQL لتحديث قاعدة البيانات | لا تظهر للزائر |
| `backend/php/database/seeds` | ملفات seed/import اختيارية | لا تظهر للزائر |
| `docs` | توثيق فقط | لا يحتاج الرفع للموقع |
| `phases` | ملفات phases وملفات Level Test source | لا تظهر للزائر |

---

## 5) أهم نقطة في الرفع: layout على السيرفر

لأن صفحات `web/public` تستدعي ملفات من `backend` و `web/components` باستخدام relative paths، فأفضل layout باستخدام File Manager يكون كالتالي:

```text
/home/<HOSTINGER_USER>/domains/mshabibanabil.com/
  .env
  backend/
  web/
    components/
  phases/
    files needed/
      Level Test/
  uploads/
    level-checks/
    free-level-tests/
  public_html/
    staging/
      index.php
      assets/
      login/
      logout/
      owner/
      student/
      parent/
      academy/
      checkout/
      thank-you/
      student-form/
      level-check/
      level-check-intro/
      level-check-thank-you/
      level-test/
      leveltest/
      pricing/
      articles/
      videos/
      testimonials/
      submit-testimonial/
      terms/
      privacy/
      refund/
      unauthorized/
```

### ماذا يعني ذلك عمليًا؟

#### داخل Hostinger domain root

هذا المسار:

```text
/home/<HOSTINGER_USER>/domains/mshabibanabil.com/
```

ضع فيه:

```text
backend/
web/components/
phases/              اختياري لكنه مطلوب لو هتشغل importer الخاص بـ Level Test
.env
uploads/
```

#### داخل public_html/staging

هذا المسار:

```text
/home/<HOSTINGER_USER>/domains/mshabibanabil.com/public_html/staging
```

ضع فيه **محتويات**:

```text
web/public/*
```

مهم جدًا: لا تضع فولدر `web/public` نفسه داخل staging، بل ضع محتوياته.

صحيح:

```text
public_html/staging/index.php
public_html/staging/assets/
public_html/staging/login/
```

خطأ:

```text
public_html/staging/web/public/index.php
```

---

## 6) تجهيز الملفات قبل الرفع

### الطريقة اليدوية من GitHub

1. افتح repo:

```text
https://github.com/abdulrahmanal-saeed/classlink
```

2. اضغط:

```text
Code → Download ZIP
```

3. فك الضغط على جهازك.

4. جهز ملفات الرفع كالتالي.

---

## 7) ماذا ترفع بالضبط؟

### 7.1 ارفع public files

من جهازك افتح:

```text
classlink/web/public/
```

حدد كل المحتويات الموجودة داخله، مثل:

```text
index.php
assets/
login/
logout/
owner/
student/
parent/
academy/
checkout/
thank-you/
student-form/
level-check/
level-check-intro/
level-check-thank-you/
level-test/
leveltest/
pricing/
articles/
videos/
testimonials/
submit-testimonial/
terms/
privacy/
refund/
unauthorized/
```

اضغطهم في ملف zip مثل:

```text
public-files.zip
```

ارفعه إلى:

```text
public_html/staging
```

ثم Extract.

بعد الاستخراج تأكد أن `index.php` موجود مباشرة داخل:

```text
public_html/staging/index.php
```

---

### 7.2 ارفع backend

من جهازك اضغط فولدر:

```text
classlink/backend
```

إلى:

```text
backend.zip
```

ارفعه إلى:

```text
/home/<HOSTINGER_USER>/domains/mshabibanabil.com/
```

ثم Extract.

يجب أن يصبح:

```text
/home/<HOSTINGER_USER>/domains/mshabibanabil.com/backend/php/config/db.php
```

---

### 7.3 ارفع web/components

من جهازك اضغط:

```text
classlink/web/components
```

وارفعه بحيث يصبح على السيرفر:

```text
/home/<HOSTINGER_USER>/domains/mshabibanabil.com/web/components/layout/public_layout.php
/home/<HOSTINGER_USER>/domains/mshabibanabil.com/web/components/layout/dashboard_shell.php
```

مهم: لا تنسَ إنشاء فولدر `web` ثم داخله `components` إذا لم يكن موجودًا.

---

### 7.4 ارفع phases/files needed/Level Test

هذا مطلوب لو هتستخدم importer الخاص ببنك اختبار المستوى المجاني.

ارفع هذا المسار:

```text
phases/files needed/Level Test
```

ليصبح:

```text
/home/<HOSTINGER_USER>/domains/mshabibanabil.com/phases/files needed/Level Test
```

مهم لمرحلة:

```text
Phase 6A
```

---

### 7.5 ارفع ملفات الصوت الخاصة بالـ Level Test

ملفات الصوت الفعلية يجب أن تكون داخل public assets:

```text
public_html/staging/assets/audio/level-test/listening/a2/
public_html/staging/assets/audio/level-test/listening/b1/
public_html/staging/assets/audio/level-test/listening/b2/
public_html/staging/assets/audio/level-test/listening/c1/
public_html/staging/assets/audio/level-test/listening/c2/
```

صيغة الملفات المطلوبة:

```text
[level]_[scriptNumber].mp3
```

أمثلة:

```text
a2_1.mp3
a2_9.mp3
b1_10.mp3
c2_10.mp3
```

مهم:

```text
A2 فيه a2_1.mp3 إلى a2_9.mp3 فقط
لا يوجد a2_10.mp3
لا تستخدم a2_01.mp3
```

---

## 8) إنشاء فولدرات uploads

من File Manager أنشئ:

```text
/home/<HOSTINGER_USER>/domains/mshabibanabil.com/uploads/level-checks
/home/<HOSTINGER_USER>/domains/mshabibanabil.com/uploads/free-level-tests
```

ولو محتاج الملفات تكون قابلة للعرض مباشرة من المتصفح، أنشئ أيضًا:

```text
/home/<HOSTINGER_USER>/domains/mshabibanabil.com/public_html/staging/uploads
```

ملاحظة مهمة:

الكود الحالي يحفظ uploads في:

```text
/home/<HOSTINGER_USER>/domains/mshabibanabil.com/uploads
```

لكن بعض صفحات المراجعة تعرض الرابط كـ:

```text
/uploads/...
```

في بيئة production الأفضل لاحقًا نعمل Patch صغير لتوحيد مسار التخزين والعرض، أو نعمل secure download route. لا تعتبر هذا blocker لاختبار باقي النظام، لكنه مهم عند اختبار تشغيل ملفات uploads من المتصفح.

---

## 9) إنشاء ملف .env على Hostinger

في File Manager أنشئ ملف:

```text
/home/<HOSTINGER_USER>/domains/mshabibanabil.com/.env
```

لا تضعه داخل:

```text
public_html/staging
```

ولا ترفعه إلى GitHub.

### محتوى .env المقترح

استخدم القيم الحقيقية من Hostinger وZiina داخل Hostinger فقط:

```env
APP_NAME="Habiba Nabil Arabic Academy"
APP_ENV=staging
APP_DEBUG=false
APP_URL=https://staging.mshabibanabil.com
APP_TIMEZONE=Asia/Dubai
APP_KEY=CHANGE_THIS_TO_LONG_RANDOM_SECRET

DB_HOST=localhost
DB_PORT=3306
DB_NAME=YOUR_HOSTINGER_DATABASE_NAME
DB_USER=YOUR_HOSTINGER_DATABASE_USER
DB_PASS=YOUR_HOSTINGER_DATABASE_PASSWORD
DB_CHARSET=utf8mb4

SESSION_NAME=hn_session

ZIINA_API_BASE=YOUR_ZIINA_API_BASE
ZIINA_API_TOKEN=YOUR_ZIINA_API_TOKEN
ZIINA_TEST_MODE=true
```

### مهم جدًا

لو قاعدة البيانات في Hostinger تظهر لك host مختلف مثل:

```text
mysql.hostinger.com
```

أو أي server name، استخدمه في:

```env
DB_HOST=...
```

لكن غالبًا في نفس الاستضافة يكون:

```env
DB_HOST=localhost
```

---

## 10) تحديث قاعدة البيانات من phpMyAdmin

### قبل أي SQL

1. افتح Hostinger hPanel.
2. ادخل على Websites.
3. اختار الدومين.
4. افتح Databases / Management.
5. افتح phpMyAdmin لقاعدة البيانات الخاصة بالـ staging.
6. اعمل Export backup قبل أي تعديل.

اسم ملف الباك أب المقترح:

```text
backup-before-phase-X-YYYY-MM-DD.sql
```

---

## 11) ترتيب تشغيل migrations من Phase 0 إلى Phase 8

شغّل ملفات SQL بالترتيب التالي فقط.

افتح كل ملف من GitHub أو من الملفات التي رفعتها، ثم انسخ محتواه داخل تبويب SQL في phpMyAdmin واضغط Go.

### Phase 1 — Authentication

```text
backend/php/database/migrations/001_create_auth_tables.sql
```

### Phase 2 — Core Platform Foundation

```text
backend/php/database/migrations/002_create_core_platform_tables.sql
```

### Phase 3 — Public Website + CMS

```text
backend/php/database/migrations/003_public_website_cms_foundation.sql
```

### Phase 4 — Checkout + Payment Foundation

```text
backend/php/database/migrations/004_checkout_payment_foundation.sql
```

### Phase 4B — Ziina Payment Intent Support

```text
backend/php/database/migrations/004b_ziina_payment_intent_support.sql
```

### Phase 5 — Student Form + Onboarding

```text
backend/php/database/migrations/005_onboarding_student_form_foundation.sql
```

### Phase 6 — Paid Post-payment Level Check

```text
backend/php/database/migrations/006_level_check_foundation.sql
```

### Phase 6A — Free Public Level Test

```text
backend/php/database/migrations/006a_free_public_level_test_foundation.sql
```

### Phase 7 — Owner Approval + Account Creation

```text
backend/php/database/migrations/007_owner_approval_account_creation.sql
```

### Phase 8 — Lesson Credits + Attendance + Balance

```text
backend/php/database/migrations/008_lesson_credits_attendance_balance.sql
```

---

## 12) طريقة تشغيل SQL من phpMyAdmin بالتفصيل

لكل migration:

1. افتح phpMyAdmin.
2. اختار قاعدة البيانات الصحيحة من الشمال.
3. افتح تبويب SQL.
4. الصق محتوى ملف migration.
5. اضغط Go.
6. لو ظهر Success، انتقل للملف التالي.
7. لو ظهر Error، لا تكمل. احفظ نص الخطأ وابعتلي صورة/نص الخطأ.

### لا تعمل الآتي

لا تشغل كل الملفات عشوائيًا بدون ترتيب.

لا تشغل migration جديد قبل القديم.

لا تستخدم Import لملف SQL فيه أوامر:

```text
DROP DATABASE
CREATE DATABASE
GRANT ALL PRIVILEGES
DEFINER
SUPER privilege
```

لو ظهر خطأ duplicate أو column exists، ابعتلي الخطأ قبل أي تعديل يدوي.

---

## 13) Seeds وملفات import

### Demo seeds

هذه ملفات PHP وليست SQL:

```text
backend/php/database/seeds/seed_demo_accounts.php
backend/php/database/seeds/seed_phase_2_demo_data.php
```

لو بتستخدم File Manager + phpMyAdmin فقط، لا تستطيع تشغيل ملفات PHP CLI مباشرة من phpMyAdmin.

في staging فقط، عند الحاجة، عندك 3 حلول:

1. تشغيلها محليًا على XAMPP ثم تصدير SQL واستيراده في Hostinger.
2. استخدام SSH مرة واحدة لتشغيلها.
3. أطلب مني أعمل ملف SQL seed بدل PHP seed.

### Level Test bank importer

ملف importer:

```text
backend/php/database/seeds/import_free_level_test_bank.php
```

هذا الملف يقرأ من:

```text
phases/files needed/Level Test
```

ويستورد reading/listening banks.

لو File Manager + phpMyAdmin فقط، لا يمكن تشغيله من phpMyAdmin لأنه PHP script وليس SQL.

الحل الأفضل بدون SSH:

```text
أطلب مني Patch يحول بنك Level Test إلى SQL seed جاهز للاستيراد من phpMyAdmin.
```

الحل المؤقت في staging فقط:

```text
تشغيل importer من SSH أو من runner مؤقت محمي ثم حذف runner فورًا.
```

لا تترك أي runner عام على الموقع.

---

## 14) إعدادات Ziina بعد الرفع

تأكد من `.env`:

```env
ZIINA_API_BASE=...
ZIINA_API_TOKEN=...
ZIINA_TEST_MODE=true
```

ثم اختبر:

```text
/checkout?plan=single
/checkout?plan=monthly
/checkout?plan=bundle
```

مهم:

- الوصول إلى thank-you لا يعني paid.
- status يتحول paid فقط حسب verification/API أو manual Owner action.
- راجع `/owner/payments` بعد كل تجربة.

---

## 15) الملفات التي لا ترفعها إلى public_html/staging

لا تضع هذه الملفات داخل public root:

```text
.env
backend/
docs/
phases/
README.md
.git/
.github/
```

الاستثناء العملي الحالي:

```text
web/public/*
```

فقط محتويات `web/public` تذهب داخل:

```text
public_html/staging
```

---

## 16) ماذا تفعل عند كل Phase جديدة؟

بعد كل Phase جديدة نعمل نفس الخطوات:

### 16.1 GitHub

1. أضيف/أعدل الملفات.
2. أضيف تقرير Phase داخل docs.
3. أضيف أو أعدل migration لو في قاعدة بيانات.
4. أعدل هذا الملف `HOSTINGER_DEPLOYMENT_GUIDE.md` وأضيف قسم Phase الجديد.

### 16.2 Hostinger File Manager

ارفع فقط الملفات الجديدة/المعدلة أو ارفع نسخة public كاملة لو أسهل.

لو الملفات الجديدة داخل:

```text
web/public/...
```

ارفعها إلى:

```text
public_html/staging/...
```

لو الملفات الجديدة داخل:

```text
backend/...
```

ارفعها إلى:

```text
/home/<HOSTINGER_USER>/domains/mshabibanabil.com/backend/...
```

لو الملفات الجديدة داخل:

```text
web/components/...
```

ارفعها إلى:

```text
/home/<HOSTINGER_USER>/domains/mshabibanabil.com/web/components/...
```

### 16.3 phpMyAdmin

لو فيه migration جديد:

1. اعمل Export backup.
2. شغل migration الجديد فقط.
3. لا تعيد تشغيل كل migrations من البداية إلا لو قاعدة جديدة فاضية.
4. اختبر الصفحات الجديدة.

### 16.4 تقرير الاختبار

بعد كل Phase اختبر:

```text
صفحة Owner الخاصة بالميزة
صفحة Student أو Parent لو موجودة
Audit log لو الميزة بتسجل audit
قاعدة البيانات والجداول الجديدة
```

---

# 17) ملخص كل Phase حتى الآن

## Phase 0 — Project Foundation

### الهدف

تأسيس المشروع والاتفاق على stack والهيكل.

### Stack المعتمد

```text
Backend: PHP
Database: MySQL/MariaDB
App: Flutter
Firebase: allowed as supporting service only
```

### Deployment action

لا يوجد migration أساسي معروف هنا.

ارفع فقط أي docs/folders foundational لو محتاجها كمرجع، لكن لا تضع docs داخل public root.

---

## Phase 1 — Authentication, Roles, Demo Accounts, Access Control

### Migration

```text
backend/php/database/migrations/001_create_auth_tables.sql
```

### ارفع ملفات

```text
backend/php/core/Auth.php
web/public/login/
web/public/logout/
web/public/owner/dashboard/
web/public/student/dashboard/
web/public/parent/dashboard/
web/public/academy/dashboard/
web/public/unauthorized/
web/components/layout/dashboard_shell.php
```

### اختبر

```text
/login
/logout
/owner/dashboard
/student/dashboard
/parent/dashboard
/academy/dashboard
/unauthorized
```

### Checklist

- Owner يدخل dashboard الخاص به فقط.
- Student لا يدخل Owner dashboard.
- Parent لا يرى غير Parent dashboard.
- Logout يعمل.
- Audit log يسجل login/logout.

---

## Phase 2 — Database Foundation, Settings, Audit Log, Seed Data

### Migration

```text
backend/php/database/migrations/002_create_core_platform_tables.sql
```

### ارفع ملفات

```text
web/public/owner/settings/
web/public/owner/audit-log/
web/public/owner/dev/seed-data/
backend/php/shared/
backend/php/database/seeds/
```

### اختبر

```text
/owner/settings
/owner/audit-log
/owner/dev/seed-data
```

### ملاحظات

لو seed-data PHP لا يعمل من File Manager/phpMyAdmin، اطلب SQL seed أو شغله محليًا ثم صدّر database.

---

## Phase 3 — Public Website, Pricing, Legal Pages, CMS

### Migration

```text
backend/php/database/migrations/003_public_website_cms_foundation.sql
```

### ارفع ملفات public

```text
web/public/index.php
web/public/pricing/
web/public/terms/
web/public/privacy/
web/public/refund/
web/public/articles/
web/public/videos/
web/public/testimonials/
web/public/submit-testimonial/
web/public/owner/cms/
web/public/owner/settings/public-website/
```

### اختبر

```text
/
/pricing
/terms
/privacy
/refund
/articles
/videos
/testimonials
/submit-testimonial
/owner/cms/articles
/owner/cms/videos
/owner/cms/testimonials
/owner/settings/public-website
```

### Checklist

- Public pages تعمل mobile وdesktop.
- Pricing CTAs تذهب إلى checkout.
- Owner CMS محمي.
- Testimonials لا تظهر إلا بعد approval.

---

## Phase 4 — Checkout, Payment Status, Thank You

### Migrations

```text
backend/php/database/migrations/004_checkout_payment_foundation.sql
backend/php/database/migrations/004b_ziina_payment_intent_support.sql
```

### ارفع ملفات

```text
web/public/checkout/
web/public/thank-you/
web/public/owner/payments/
backend/php/shared/Checkout.php
backend/php/shared/Payment.php
```

### .env مطلوب

```env
ZIINA_API_BASE=...
ZIINA_API_TOKEN=...
ZIINA_TEST_MODE=true
```

### اختبر

```text
/checkout?plan=single
/checkout?plan=monthly
/checkout?plan=bundle
/thank-you?ref=...
/owner/payments
/owner/payments/view?id=...
```

### Checklist

- Policy checkbox مطلوب.
- Checkout ينشئ reference.
- Thank-you لا يجعل status paid تلقائيًا.
- Owner يقدر يعمل manual payment update.
- Audit log يسجل status changes.

---

## Phase 5 — Post-payment Student Form and Onboarding Pipeline

### Migration

```text
backend/php/database/migrations/005_onboarding_student_form_foundation.sql
```

### ارفع ملفات

```text
web/public/student-form/
web/public/owner/onboarding/
backend/php/shared/Onboarding.php
```

### اختبر

```text
/student-form?ref={checkoutReference}
/owner/onboarding
/owner/onboarding/view?id=...
```

### Checklist

- Adult form يظهر للبالغ.
- Child form يظهر للطفل.
- Someone_else يحدد adult/child.
- بعد submit يفتح level-check intro.
- Owner يرى submission.

---

## Phase 6 — Paid Adult Level Check and Child Literacy Check

### Migration

```text
backend/php/database/migrations/006_level_check_foundation.sql
```

### ارفع ملفات

```text
backend/php/shared/LevelCheck.php
web/public/level-check-intro/
web/public/level-check/
web/public/level-check-thank-you/
web/public/owner/level-checks/
```

### أنشئ فولدر uploads

```text
/home/<HOSTINGER_USER>/domains/mshabibanabil.com/uploads/level-checks
```

### اختبر

```text
/level-check-intro?intakeId=...
/level-check?intakeId=...
/level-check-thank-you?attemptId=...
/owner/level-checks
/owner/level-checks/view?id=...
```

### Checklist

- Adult auto score يظهر.
- Child letter score يظهر.
- Speaking/audio upload يعمل.
- Owner يراجع ويحدد final level.
- Audit log يسجل review.

---

## Phase 6A V2 — Free Public Level Tests

### Migration

```text
backend/php/database/migrations/006a_free_public_level_test_foundation.sql
```

### ارفع ملفات

```text
backend/php/shared/FreeLevelTest.php
backend/php/database/seeds/import_free_level_test_bank.php
web/public/level-test/
web/public/leveltest/
web/public/owner/free-level-test/
phases/files needed/Level Test/
```

### ارفع audio assets

```text
web/public/assets/audio/level-test/listening/a2/
web/public/assets/audio/level-test/listening/b1/
web/public/assets/audio/level-test/listening/b2/
web/public/assets/audio/level-test/listening/c1/
web/public/assets/audio/level-test/listening/c2/
```

إلى:

```text
public_html/staging/assets/audio/level-test/listening/...
```

### تشغيل بنك Level Test

لو عندك SSH:

```bash
php backend/php/database/seeds/import_free_level_test_bank.php
```

لو File Manager + phpMyAdmin فقط:

```text
أطلب مني SQL seed جاهز للاستيراد من phpMyAdmin.
```

### اختبر

```text
/level-test/quick
/level-test/quick-result?token=...
/level-test/entry
/level-test/register
/level-test/start?token=...
/level-test/thank-you?token=...
/owner/free-level-test/settings
/owner/free-level-test/attempts
/owner/free-level-test/attempts/view?id=...
```

### Checklist

- Quick check لا يحتاج تسجيل.
- Full test يحتاج name + WhatsApp.
- WhatsApp country code validation يعمل.
- Refresh لا يغير questions snapshot.
- Owner يراجع writing/speaking.

---

## Phase 7 — Owner Approval and Account Creation

### Migration

```text
backend/php/database/migrations/007_owner_approval_account_creation.sql
```

### ارفع ملفات

```text
backend/php/shared/ApprovalWorkflow.php
web/public/owner/onboarding/approve/
web/public/owner/students/
web/public/owner/parents/
web/public/parent/dashboard/
```

### اختبر

```text
/owner/onboarding/approve?id={intakeId}
/owner/students
/owner/students/view?id={studentUserId}
/owner/parents
/owner/parents/view?id={parentUserId}
/parent/dashboard
```

### Checklist

- لا يتم إنشاء account قبل approval.
- Adult approval ينشئ student account.
- Child approval ينشئ parent account + child profile.
- Parent يرى child فقط.
- Login details تظهر في logs.
- Audit log يسجل approval.

---

## Phase 8 — Lesson Credits, Packages, Attendance, Balance

### Migration

```text
backend/php/database/migrations/008_lesson_credits_attendance_balance.sql
```

### ارفع ملفات

```text
backend/php/shared/LessonCredits.php
web/public/owner/packages/
web/public/owner/students/credits/
web/public/student/balance/
web/public/parent/child/balance/
web/components/layout/dashboard_shell.php
web/public/parent/dashboard/
```

### اختبر

```text
/owner/packages
/owner/students/credits?id={studentUserId}
/student/balance
/parent/child/balance?id={childUserId}
```

### Checklist

- Package balance يظهر صح.
- Ledger يظهر كل تغيير.
- Manual adjustment يحتاج reason.
- Completed يخصم 1 credit.
- No-show يخصم 1 credit.
- Late cancellation يخصم 1 credit.
- On-time cancellation لا يخصم أو يرجع credit.
- Student يرى رصيده فقط.
- Parent يرى رصيد الطفل المرتبط فقط.
- Audit log يسجل adjustments/attendance.

---

# 18) اختبار كامل بعد رفع كل شيء

افتح الروابط بالترتيب:

## Public

```text
/
/pricing
/articles
/videos
/testimonials
/level-test/quick
/level-test/entry
```

## Auth

```text
/login
/logout
/unauthorized
```

## Owner

```text
/owner/dashboard
/owner/settings
/owner/audit-log
/owner/payments
/owner/onboarding
/owner/level-checks
/owner/free-level-test/settings
/owner/free-level-test/attempts
/owner/packages
/owner/students
/owner/parents
```

## Student

```text
/student/dashboard
/student/balance
```

## Parent

```text
/parent/dashboard
/parent/child/balance?id=...
```

---

# 19) أخطاء متوقعة وحلها

## 19.1 صفحة بيضاء

افتح `.env` مؤقتًا:

```env
APP_DEBUG=true
```

ثم افتح الصفحة وشوف الخطأ.

بعد الإصلاح أرجعه:

```env
APP_DEBUG=false
```

## 19.2 Database connection failed

راجع `.env`:

```env
DB_HOST
DB_NAME
DB_USER
DB_PASS
DB_PORT
```

وتأكد من صلاحيات مستخدم قاعدة البيانات داخل Hostinger.

## 19.3 404 على صفحة مثل /owner/packages

تأكد أن الفولدر موجود داخل:

```text
public_html/staging/owner/packages/index.php
```

## 19.4 Layout لا يظهر أو require error

تأكد من وجود:

```text
/home/<HOSTINGER_USER>/domains/mshabibanabil.com/web/components/layout/public_layout.php
/home/<HOSTINGER_USER>/domains/mshabibanabil.com/web/components/layout/dashboard_shell.php
```

## 19.5 backend file not found

تأكد من وجود:

```text
/home/<HOSTINGER_USER>/domains/mshabibanabil.com/backend/php/config/db.php
/home/<HOSTINGER_USER>/domains/mshabibanabil.com/backend/php/shared/
```

## 19.6 Audio لا يعمل في Level Test

تأكد من وجود الملفات داخل:

```text
public_html/staging/assets/audio/level-test/listening/a2/a2_1.mp3
```

وتأكد أن الاسم lowercase ولا يحتوي spaces.

## 19.7 Upload لا يظهر كرابط

هذا غالبًا بسبب اختلاف storage path عن public path.

الحل المؤقت:

- تأكد أن upload نفسه اتسجل في قاعدة البيانات.
- تأكد أن فولدر uploads موجود وقابل للكتابة.
- لاحقًا نعمل Patch لتوحيد مسار uploads أو secure download route.

---

# 20) Backup قبل وبعد كل Phase

قبل تشغيل migration جديد:

```text
phpMyAdmin → Export → Quick → SQL → Download
```

بعد نجاح phase:

```text
phpMyAdmin → Export → Quick → SQL → Download
```

اسم الملفات:

```text
before-phase-8-YYYY-MM-DD.sql
after-phase-8-YYYY-MM-DD.sql
```

---

# 21) لا تكمل للمرحلة التالية إلا بعد

- الموقع يفتح.
- تسجيل الدخول يعمل.
- صفحات Owner الأساسية تعمل.
- migrations اشتغلت بدون error.
- Audit log لا يظهر errors.
- الميزة الجديدة تم اختبارها يدويًا.

Stop here after every deployment and test before continuing.

توقف هنا بعد كل رفع واختبر قبل الانتقال للمرحلة التالية.
