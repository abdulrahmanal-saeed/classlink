# Workflow After Each Phase
# طريقة الشغل بعد كل مرحلة

## Arabic / عربي

بعد كل Phase، لازم نعمل الآتي:

1. تنفيذ المرحلة فقط بدون الدخول في المرحلة التالية.
2. رفع كل الملفات الجديدة أو المعدلة على GitHub.
3. إنشاء تقرير تنفيذ داخل `docs/` باسم واضح مثل:

```text
PHASE_1_EXECUTION_REPORT.md
PHASE_2_EXECUTION_REPORT.md
```

4. التقرير لازم يحتوي على:

- ما الذي تم بناؤه
- الملفات التي تم إنشاؤها أو تعديلها
- تغييرات قاعدة البيانات
- الصفحات والمسارات
- APIs
- فحوصات الأمان
- القيود الحالية
- خطوات الاختبار اليدوي
- نقطة التوقف

5. بعد الرفع، يتم إرسال ملخص للمستخدم في الشات.
6. لا نبدأ Phase جديدة إلا بعد طلب واضح من المستخدم.
7. النشر على Hostinger يتم بطريقة آمنة من خلال checklist وأوامر ينفذها المستخدم، بدون استخدام أو مشاركة كلمات مرور أو SSH credentials داخل الشات.

---

## English

After each Phase, we must:

1. Build only the requested phase.
2. Push all new or changed files to GitHub.
3. Create an execution report inside `docs/` with a clear name, such as:

```text
PHASE_1_EXECUTION_REPORT.md
PHASE_2_EXECUTION_REPORT.md
```

4. The report must include:

- What was built
- Files created or changed
- Database changes
- Pages and routes
- APIs
- Security checks
- Known limitations
- Manual test checklist
- Stop point

5. After pushing, send a summary to the user in chat.
6. Do not start the next Phase until the user explicitly asks.
7. Hostinger deployment must be handled safely through a checklist and commands run by the user, without sharing or using passwords or SSH credentials in chat.
