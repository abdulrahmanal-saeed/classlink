# Architecture and Folder Rules
# قواعد تنظيم الملفات والمعمارية

## Main Rule / القاعدة الأساسية

The project must be organized by role and feature.

لازم المشروع يتقسم حسب الرول والفيتشر.

## Approved Structure / الهيكل المعتمد

```text
backend/
  php/
    config/
    core/
    middleware/
    shared/
    api/
      auth/
      owner/
      admin/
      teacher/
      student/
      parent/
      media-buyer/
      public/
    database/
      migrations/
      seeds/

web/
  public/
    index.php
    assets/
      css/
      js/
        lang/
          ar.js
          en.js
      images/
  pages/
    auth/
    owner/
    admin/
    teacher/
    student/
    parent/
    media-buyer/
    public/
  components/
    layout/
    cards/
    forms/
    tables/
    modals/

mobile/
  flutter_app/
    lib/
      app/
      core/
      shared/
      features/
        auth/
        student/
        teacher/
        parent/
      l10n/
      services/
        api/
        firebase/

docs/
```

## Role Rule / قاعدة الرولات

Each role has its own folder.

كل رول له فولدر مستقل.

Approved roles:

```text
owner
admin
teacher
student
parent
media-buyer
public
auth
shared
```

## Feature Rule / قاعدة الفيشرز

Any feature related to a role must live inside that role folder.

أي فيتشر خاصة برول معين تتحط داخل فولدر الرول.

Examples:

```text
backend/php/api/teacher/homeworks/
backend/php/api/teacher/scenarios/
backend/php/api/student/homeworks/
backend/php/api/student/scenarios/
backend/php/api/owner/settings/
backend/php/api/owner/payments/
```

## Shared Code / الكود المشترك

Shared code should not be duplicated.

أي كود مشترك مايتكررش.

Use shared folders for database, auth, validation, response helpers, security helpers, and reusable UI components.

## Translation Files / ملفات الترجمة

The website must use separated language files:

```text
web/public/assets/js/lang/ar.js
web/public/assets/js/lang/en.js
```

Do not hardcode UI text when the text should be translated.

ما نكتبش نصوص الواجهة مباشرة لو النص محتاج ترجمة.

## Comments Rule / قاعدة الكومنتات

Important files must include useful comments explaining why the logic exists, security notes, and future improvements.

الملفات المهمة لازم يكون فيها كومنتات مفيدة تشرح السبب، ملاحظات الأمان، والتحسينات المستقبلية.

Good comment example:

```php
// We validate the user role on the server side because frontend checks can be bypassed.
```

## Clean Code Rule / قاعدة الكود النظيف

- Keep files small and focused.
- Avoid mixing many features in one file.
- Use clear names for files, functions, variables, and tables.
- Keep backend validation and security checks mandatory.
