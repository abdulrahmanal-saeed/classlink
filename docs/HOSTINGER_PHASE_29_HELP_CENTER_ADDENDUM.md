# Hostinger Deployment Addendum — Phase 29

## Phase 29

Platform Guide, Help Center, and Role-Based Onboarding.

## Database Migration

Run after Phase 28:

```text
backend/php/database/migrations/029_platform_guide_help_center.sql
```

Export a database backup first.

## Backend Files

```text
backend/php/shared/HelpCenter.php
backend/php/database/migrations/029_platform_guide_help_center.sql
```

## Public Help Files

```text
web/public/help/index.php
web/public/help/role/index.php
web/public/help/owner/index.php
web/public/help/student/index.php
web/public/help/parent/index.php
web/public/help/academy/index.php
web/public/help/media-buyer/index.php
```

## Dashboard Shortcuts

```text
web/public/owner/help/index.php
web/public/student/help/index.php
web/public/parent/help/index.php
web/public/academy/help/index.php
web/public/media/help/index.php
```

## Owner CMS Files

```text
web/public/owner/help-center/index.php
web/public/owner/help-center/articles/index.php
web/public/owner/help-center/articles/new/index.php
web/public/owner/help-center/articles/edit/index.php
```

## URLs to Test

```text
/help
/help/student
/help/parent
/help/owner
/help/academy
/help/media-buyer
/owner/help-center
/owner/help-center/articles/new
```

## Manual Test

```text
Run migration 029
Open public help center
Open each role guide
Search help articles
Login as Owner
Create help article
Edit help article
Confirm role pages show relevant content
Confirm non-owner cannot access Owner Help CMS
Test mobile layout
```

## Current Limitations

```text
Dashboard cards are not injected into every dashboard yet.
JS overlay tours are not implemented yet.
Checklist storage exists but automatic completion is not fully wired yet.
Contextual help can be added page-by-page later.
```

Stop here. Test Phase 29 before continuing.
