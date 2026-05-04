# Hostinger Deployment Addendum — Phase 16
# إضافة دليل الرفع على Hostinger — المرحلة 16

> Use this addendum together with:

```text
docs/HOSTINGER_DEPLOYMENT_GUIDE.md
docs/HOSTINGER_PHASE_9_DEPLOYMENT_ADDENDUM.md
docs/HOSTINGER_PHASE_10_DEPLOYMENT_ADDENDUM.md
docs/HOSTINGER_PHASE_11_DEPLOYMENT_ADDENDUM.md
docs/HOSTINGER_PHASE_12_DEPLOYMENT_ADDENDUM.md
docs/HOSTINGER_PHASE_13_DEPLOYMENT_ADDENDUM.md
docs/HOSTINGER_PHASE_14_DEPLOYMENT_ADDENDUM.md
docs/HOSTINGER_PHASE_15_DEPLOYMENT_ADDENDUM.md
```

This addendum covers Phase 16 only.

---

## Phase 16 Name / اسم المرحلة

AI Teacher and Marketing Tools

---

## 1) What Phase 16 Adds / ماذا تضيف المرحلة 16

Phase 16 adds:

```text
AI Teacher tools
AI Marketing tools
Anthropic API provider support
AI preview drafts
AI usage logs
Regenerate limits
Weekly student summaries
Generated article drafts
AI apply/save workflow
```

---

## 2) Database Migration / تحديث قاعدة البيانات

Phase 16 requires migration:

```text
backend/php/database/migrations/016_ai_teacher_marketing_tools.sql
```

Run it in phpMyAdmin after Phase 15.

---

## 3) Anthropic API Key / مفتاح Anthropic API

Do not upload the API key to GitHub.

لا ترفع مفتاح Anthropic على GitHub.

Add it only to the server environment or `.env`:

```text
ANTHROPIC_API_KEY=your_key_here
```

The code reads:

```text
ANTHROPIC_API_KEY
```

---

## 4) Files to Upload / الملفات المطلوب رفعها

### Backend

```text
backend/php/shared/AITools.php
backend/php/database/migrations/016_ai_teacher_marketing_tools.sql
```

Upload helper to:

```text
/home/<HOSTINGER_USER>/domains/mshabibanabil.com/backend/php/shared/AITools.php
```

---

### Updated shared layout

```text
web/components/layout/dashboard_shell.php
```

To:

```text
/home/<HOSTINGER_USER>/domains/mshabibanabil.com/web/components/layout/dashboard_shell.php
```

---

### Owner AI pages

```text
web/public/owner/ai/index.php
web/public/owner/ai/logs/index.php
web/public/owner/ai/preview/index.php
web/public/owner/students/ai/index.php
web/public/owner/weekly-summaries/index.php
web/public/owner/cms/articles/generate/index.php
```

To:

```text
public_html/staging/owner/ai/index.php
public_html/staging/owner/ai/logs/index.php
public_html/staging/owner/ai/preview/index.php
public_html/staging/owner/students/ai/index.php
public_html/staging/owner/weekly-summaries/index.php
public_html/staging/owner/cms/articles/generate/index.php
```

---

## 5) phpMyAdmin Steps / خطوات phpMyAdmin

1. Open Hostinger hPanel.
2. Open Databases.
3. Open phpMyAdmin.
4. Select staging database.
5. Export backup first.
6. Open SQL tab.
7. Paste contents of:

```text
016_ai_teacher_marketing_tools.sql
```

8. Click Go.
9. If success appears, continue.
10. If error appears, stop and send the error.

---

## 6) .env / Server Environment Steps / خطوات .env

Add:

```text
ANTHROPIC_API_KEY=your_key_here
```

Recommended settings in database are inserted by migration:

```text
ai_provider = anthropic
ai_default_model = claude-sonnet-4-20250514
ai_regenerate_limit_per_tool_per_day = 3
ai_max_preview_tokens = 2500
```

If you want to change model later, update:

```text
settings.ai_default_model
```

---

## 7) URLs to Test / روابط الاختبار

```text
/owner/ai
/owner/ai/logs
/owner/ai/preview?id={draftId}
/owner/students/ai?id={studentUserId}
/owner/weekly-summaries
/owner/cms/articles/generate
```

---

## 8) Student AI Tool Test / اختبار أدوات الطالب

1. Login as Owner.
2. Open:

```text
/owner/students/ai?id={studentUserId}
```

3. Run:

```text
Analyze Student
Plan Remaining Sessions
Prepare Next Lesson
Generate Homework
Generate Scenario
Generate Weekly Student Summary
```

4. Confirm every output goes to preview page first:

```text
/owner/ai/preview?id={draftId}
```

5. Confirm nothing is auto-published.

---

## 9) Apply Test / اختبار التطبيق بعد المعاينة

### Homework

1. Generate Homework.
2. Preview.
3. Click Apply as draft homework.
4. Confirm created homework has:

```text
status = draft
```

### Scenario

1. Generate Scenario.
2. Preview.
3. Click Apply as draft scenario.
4. Confirm scenario has:

```text
status = draft
```

### Weekly Summary

1. Generate weekly summary.
2. Preview.
3. Click Save weekly summary.
4. Confirm saved summary appears in:

```text
/owner/weekly-summaries
```

### Article

1. Open:

```text
/owner/cms/articles/generate
```

2. Generate Article.
3. Preview.
4. Click Apply as draft article.
5. Confirm article has:

```text
status = draft
```

---

## 10) AI Logs Test / اختبار سجلات الذكاء الاصطناعي

Open:

```text
/owner/ai/logs
```

Confirm each run shows:

```text
Date
User
Tool
Related entity
Model
Tokens
Estimated cost
Status
Error if any
```

---

## 11) Regenerate Limit Test / اختبار حد إعادة التوليد

Default:

```text
3 times per tool per day
```

Run the same tool more than 3 times for the same related entity.

Expected:

```text
Daily regenerate limit reached for this AI tool.
```

---

## 12) Anthropic Failure Test / اختبار فشل Anthropic

Temporarily remove or rename:

```text
ANTHROPIC_API_KEY
```

Run an AI tool.

Expected:

```text
System does not crash.
Fallback preview appears.
AI log stores error_message.
```

---

## 13) Known Limitations / القيود الحالية

- Anthropic integration uses PHP cURL.
- Hosting must allow outbound HTTPS requests to Anthropic.
- If cURL/outbound requests are blocked, fallback preview is shown and error is logged.
- Apply Homework/Scenario creates draft shell content; parsing AI output into detailed question rows can be improved later.
- No streaming UI yet.
- Cost calculation depends on Owner setting `ai_estimated_cost_per_1k_tokens`.
- CSRF protection still needs strengthening before production.

---

## 14) Stop Rule / قاعدة التوقف

Stop here. Test Phase 16 fully before moving to Phase 17.

توقف هنا. اختبر Phase 16 بالكامل قبل الانتقال إلى Phase 17.
