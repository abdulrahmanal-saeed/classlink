# Coding Standards
# معايير كتابة الكود

## Goal / الهدف

Build clean, secure, readable, and maintainable code.

نبني كود نضيف، آمن، واضح، وسهل الصيانة.

---

## General Rules / قواعد عامة

- Keep each file focused on one responsibility.
- خلي كل ملف مسؤول عن حاجة واحدة قدر الإمكان.
- Separate features by role and folder.
- افصل الفيشرز حسب الرول والفولدر.
- Avoid duplicated logic.
- تجنب تكرار نفس اللوجيك.
- Use clear names for files, functions, variables, classes, and tables.
- استخدم أسماء واضحة للملفات، الدوال، المتغيرات، الكلاسات، والجداول.

---

## Comments / الكومنتات

Comments are required for important logic.

الكومنتات مطلوبة في اللوجيك المهم.

Good comments explain:

- Why this logic exists
- Security reasons
- Expected input and output
- Future improvement ideas
- Edge cases

الكومنتات الجيدة تشرح:

- لماذا هذا اللوجيك موجود
- أسباب الأمان
- شكل البيانات الداخلة والخارجة
- أفكار تطوير مستقبلية
- الحالات الاستثنائية

Bad comment:

```php
// set value
```

Good comment:

```php
// We check the role on the backend because frontend route guards can be bypassed.
```

---

## PHP Rules / قواعد PHP

- Use prepared statements for all SQL queries.
- استخدم prepared statements في كل استعلامات SQL.
- Never trust frontend input.
- لا تثق في أي بيانات جاية من الواجهة.
- Validate and sanitize all inputs.
- تحقق من البيانات ونظفها.
- Return consistent JSON responses from APIs.
- خلي ردود الـ API ثابتة ومنظمة.
- Keep config files separate.
- افصل ملفات الإعدادات.

Example response shape:

```json
{
  "success": true,
  "message": "Done",
  "data": {}
}
```

---

## MySQL Rules / قواعد MySQL

- Use clear table names.
- استخدم أسماء جداول واضحة.
- Use timestamps where useful.
- استخدم timestamps عند الحاجة.
- Add indexes for searchable fields.
- أضف indexes للحقول المستخدمة في البحث.
- Avoid deleting important business records permanently unless required.
- تجنب حذف البيانات المهمة نهائيًا إلا عند الحاجة.

---

## Flutter Rules / قواعد Flutter

- Organize app by feature.
- نظم التطبيق حسب الفيشر.
- Keep API services separate from UI screens.
- افصل خدمات الـ API عن شاشات الواجهة.
- Keep Firebase services in a separate service folder.
- خلي خدمات Firebase في فولدر منفصل.
- Use localization for Arabic and English.
- استخدم localization للعربي والإنجليزي.

---

## Security Rules / قواعد الأمان

- Authentication must be checked on backend.
- التحقق من تسجيل الدخول لازم يكون في الباك إند.
- Role permissions must be checked on backend.
- صلاحيات الرولات لازم تتراجع في الباك إند.
- Never expose database credentials in public files.
- لا تعرض بيانات قاعدة البيانات في ملفات عامة.
- Never commit secrets or API keys.
- لا ترفع أسرار أو API keys على GitHub.
