# Hostinger Deployment Addendum — Phase 26

## Phase 26

AI Settings, API Key Security, and Usage Logs.

## Database Migration

Run after Phase 25:

```text
backend/php/database/migrations/026_ai_settings_security.sql
```

Export a database backup first.

## Backend Files

```text
backend/php/shared/AISettings.php
backend/php/database/migrations/026_ai_settings_security.sql
```

## Owner Pages

```text
web/public/owner/settings/ai/index.php
web/public/owner/ai/logs/index.php
```

## Environment Variable Required

Add to Hostinger environment/config:

```text
AI_SETTINGS_ENCRYPTION_KEY=your-long-random-secret-at-least-32-chars
```

Do not commit this value to GitHub.

## URLs to Test

```text
/owner/settings/ai
/owner/ai/logs
```

## Security Checks

```text
Full API key must never appear in browser
Only masked key is shown
AI key is encrypted server-side
AI tools must be disabled when not configured
AI logs must not show secrets
AI output must stay preview/draft only
```

## Manual Test

```text
Run migration 026
Set AI_SETTINGS_ENCRYPTION_KEY
Open /owner/settings/ai
Save provider/model
Add API key
Confirm masked key only
Test connection
Open /owner/ai/logs
Confirm log exists
Disable AI and confirm tools should be blocked
```

Stop here. Test Phase 26 before continuing.
