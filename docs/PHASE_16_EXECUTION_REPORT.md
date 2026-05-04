# Phase 16 Execution Report
# تقرير تنفيذ المرحلة 16

## Phase Name / اسم المرحلة

AI Teacher and Marketing Tools

أدوات الذكاء الاصطناعي للمعلم والتسويق

---

## Goal / الهدف

Build AI tools with strict preview, logging, and cost control.

بناء أدوات ذكاء اصطناعي مع preview فقط، logging كامل، وضبط تكلفة/عدد مرات التوليد.

---

## Important Security Note / ملاحظة أمان مهمة

Anthropic API key must never be committed to GitHub.

يجب عدم رفع مفتاح Anthropic API على GitHub نهائياً.

Use server environment or `.env` only:

```text
ANTHROPIC_API_KEY=your_key_here
```

The code reads this key from environment variables or `.env`.

---

## Database Migration / تحديث قاعدة البيانات

Phase 16 adds a migration:

```text
backend/php/database/migrations/016_ai_teacher_marketing_tools.sql
```

---

## Files Created / الملفات التي تم إنشاؤها

### Database

```text
backend/php/database/migrations/016_ai_teacher_marketing_tools.sql
```

### Backend helper

```text
backend/php/shared/AITools.php
```

### Owner AI pages

```text
web/public/owner/ai/index.php
web/public/owner/ai/logs/index.php
web/public/owner/ai/preview/index.php
web/public/owner/students/ai/index.php
web/public/owner/weekly-summaries/index.php
web/public/owner/cms/articles/generate/index.php
```

---

## Files Changed / الملفات التي تم تعديلها

```text
web/components/layout/dashboard_shell.php
```

---

## Migration 016 Changes / تغييرات Migration 016

### ai_usage_logs expanded

Adds:

```text
related_type
related_id
model_name
prompt_text
response_text
response_json
estimated_tokens
error_message
```

This satisfies:

```text
Save prompt
Save response
Save estimated tokens/cost if possible
Save tool name and related student/article/homework ID
```

---

### New table: ai_drafts

Stores preview outputs before Owner applies/saves them.

Fields include:

```text
tool_name
related_type
related_id
title
prompt_text
response_text
response_json
status: draft / applied / discarded
applied_to_type
applied_to_id
usage_log_id
```

AI output starts as:

```text
status = draft
```

---

### New table: weekly_student_summaries

Stores applied/saved weekly summaries.

Fields include:

```text
student_user_id
week_start
week_end
summary_text
went_well
focus_areas
next_week_focus
engagement_level: High / Medium / Low
source_ai_draft_id
status
```

---

### articles expanded

Adds:

```text
seo_meta_title
seo_meta_description
keywords
cta
target_audience
cover_image_prompt
source_ai_draft_id
```

---

### AI settings inserted

```text
ai_enabled = 1
ai_provider = anthropic
ai_default_model = claude-sonnet-4-20250514
ai_regenerate_limit_per_tool_per_day = 3
ai_estimated_cost_per_1k_tokens = 0.0000
ai_max_preview_tokens = 2500
```

---

## Anthropic Integration / تكامل Anthropic

Implemented in:

```text
backend/php/shared/AITools.php
```

The helper sends requests to:

```text
https://api.anthropic.com/v1/messages
```

Headers used:

```text
x-api-key: ANTHROPIC_API_KEY
anthropic-version: 2023-06-01
content-type: application/json
```

Default model:

```text
claude-sonnet-4-20250514
```

The model is configurable from settings:

```text
ai_default_model
```

If Anthropic fails or the key is missing, the system stores the failure in logs and returns a fallback preview draft instead of crashing.

---

## Implemented AI Tools / الأدوات المنفذة

Student AI tools:

```text
Analyze Student
Plan Remaining Sessions
Prepare Next Lesson
Generate Homework
Generate Scenario
Generate Weekly Student Summary
```

Marketing AI tools:

```text
Generate Article
Generate Article Cover Image Prompt
```

---

## Implemented Pages / الصفحات المنفذة

```text
/owner/ai
/owner/ai/logs
/owner/ai/preview?id={draftId}
/owner/students/ai?id={studentUserId}
/owner/weekly-summaries
/owner/cms/articles/generate
```

---

## Preview / Apply Rules / قواعد المعاينة والتطبيق

All AI outputs are preview-only first.

```text
AI output status starts as draft.
Owner must click Apply/Save.
Nothing is auto-published.
Generated articles are draft only.
Generated homework is draft only.
Generated scenario is draft only.
Weekly summary is saved only after applying preview.
```

---

## Apply Behaviors / ماذا يحدث عند Apply

### Generate Homework

Creates:

```text
homeworks.status = draft
```

### Generate Scenario

Creates:

```text
scenarios.status = draft
```

### Weekly Student Summary

Creates:

```text
weekly_student_summaries.status = saved
```

### Generate Article

Creates:

```text
articles.status = draft
```

---

## AI Usage Log / سجل استخدام الذكاء الاصطناعي

Each run records:

```text
user_id
tool_name
related_type
related_id
model_name
prompt_text
response_text
response_json
prompt_tokens
completion_tokens
estimated_tokens
estimated_cost
status
error_message
```

---

## Regenerate Limit / حد إعادة التوليد

Implemented through setting:

```text
ai_regenerate_limit_per_tool_per_day
```

Default:

```text
3 per tool per day
```

The check is applied before generating a new preview.

---

## Student Context Sent to AI / سياق الطالب المرسل للذكاء الاصطناعي

Student AI tools use:

```text
profile
balance
progress
recent homeworks
recent scenarios
recent reviews
practice words
session notes
```

---

## Weekly Summary Output / مخرجات الملخص الأسبوعي

Includes:

```text
This week summary
What went well
Areas to focus on
Suggested focus next week
Engagement level: High / Medium / Low
```

---

## Generate Article Output / مخرجات توليد المقال

Includes:

```text
Arabic title
English title optional
slug
SEO meta title
SEO meta description
excerpt
full article
CTA
target audience
keywords
suggested cover image prompt
status draft
```

---

## Navigation / التنقل

Owner sidebar now includes:

```text
AI Tools
Weekly Summaries
Generate Article
AI Logs
```

---

## Known Limitations / القيود الحالية

- The Anthropic call is implemented through raw PHP cURL.
- If hosting blocks outbound cURL, the fallback preview will appear and logs will show the error.
- AI cost calculation depends on `ai_estimated_cost_per_1k_tokens`; default is 0 until Owner sets a cost estimate.
- Apply homework/scenario creates a draft shell using AI output as instructions/situation; detailed parsing into individual questions can be improved later.
- No streaming UI yet.
- CSRF protection still needs strengthening before production.

---

## Manual Test Checklist / قائمة الاختبار اليدوي

1. Run migration:

```text
backend/php/database/migrations/016_ai_teacher_marketing_tools.sql
```

2. Add Anthropic key to `.env` or server environment:

```text
ANTHROPIC_API_KEY=your_key_here
```

3. Login as Owner.
4. Open:

```text
/owner/ai
```

5. Open student AI page:

```text
/owner/students/ai?id={studentUserId}
```

6. Generate Analyze Student preview.
7. Generate Homework preview.
8. Confirm it does not auto-apply.
9. Click Apply as draft homework.
10. Confirm homework is draft.
11. Generate Scenario preview.
12. Apply as draft scenario.
13. Generate weekly summary:

```text
/owner/weekly-summaries
```

14. Apply/save weekly summary.
15. Generate article:

```text
/owner/cms/articles/generate
```

16. Apply as draft article.
17. Confirm article is draft only.
18. Open:

```text
/owner/ai/logs
```

19. Confirm prompt, response, tool, related ID, tokens/cost/status are logged.
20. Generate the same tool more than the daily limit.
21. Confirm regenerate limit blocks the request.

---

## Stop Point / نقطة التوقف

Stop here. Test this phase before continuing.

توقف هنا. اختبر هذه المرحلة قبل الانتقال للمرحلة التالية.
