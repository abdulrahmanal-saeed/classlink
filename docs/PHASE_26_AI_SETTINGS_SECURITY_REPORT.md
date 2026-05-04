# Phase 26 Execution Report

## Phase Name

AI Settings, API Key Security, and Usage Logs

## Goal

AI tools must not work unless Owner/Teacher configures an AI provider and API key. API keys must never be hardcoded, exposed in frontend code, or stored in normal client-side settings.

## Database Migration

```text
backend/php/database/migrations/026_ai_settings_security.sql
```

## Files Created

```text
backend/php/shared/AISettings.php
web/public/owner/settings/ai/index.php
```

## Files Updated

```text
web/public/owner/ai/logs/index.php
```

## New Database Tables

```text
ai_provider_secrets
ai_usage_logs
```

## AI Settings Added

```text
ai_features_enabled
ai_provider
ai_model_name
ai_regenerate_limit_per_tool
ai_monthly_usage_limit
ai_output_mode
ai_connection_status
```

## Owner Pages

```text
/owner/settings/ai
/owner/ai/logs
```

## Security Rules Implemented

```text
No API key hardcoded
No API key sent to browser
Full API key is never displayed after saving
Masked key only is shown
AI API key is encrypted server-side
AI calls can be blocked if AI is not configured
AI output mode is fixed as preview/draft only
AI usage logs record blocked, failed, and successful attempts
```

## Environment Requirement

Set one of these server environment variables:

```text
AI_SETTINGS_ENCRYPTION_KEY
```

or fallback:

```text
APP_KEY
```

Important: the encryption key should be at least 32 characters.

## Supported Providers

```text
OpenAI
Anthropic Claude
Gemini
Other
```

Internal values:

```text
openai
anthropic
gemini
other
```

## AI Helper

```text
backend/php/shared/AISettings.php
```

Main functions:

```text
ai_save_settings
ai_status
ai_require_configured
ai_log_usage
ai_logs
ai_test_connection
```

## Blocking Behavior

If AI is disabled or not configured, `ai_require_configured()` throws:

```text
AI is not configured yet. Please add your API key in Owner Settings.
```

and logs:

```text
status = blocked_not_configured
```

## Usage Logs

`/owner/ai/logs` shows:

```text
tool name
provider
model
related entity
student if any
created date
estimated input/output tokens
estimated cost
status
error message
```

## Test Connection

The current test validates server-side configuration safely. Provider-specific live network checks can be added later when final API client logic is wired.

## Important Note About Materials Phase

The prompt included another Advanced Materials phase. That work was already implemented in Phase 25 with PHP + MySQL + Hostinger/local uploads. It was not rebuilt here.

## Manual Test Checklist

```text
Run migration 026
Set AI_SETTINGS_ENCRYPTION_KEY in environment
Open /owner/settings/ai
Confirm status is not configured
Enable AI features
Choose provider
Add model name
Save without key and confirm not configured
Add API key and save
Confirm only masked key appears
Click Test AI Connection
Open /owner/ai/logs
Confirm test log appears
Remove/disable AI and confirm AI tools should be blocked
Confirm no API key appears in HTML/source/frontend
```

Stop here. Test AI settings and security before continuing.
