# Phase 29 Execution Report

## Phase Name

Platform Guide, Help Center, and Role-Based Onboarding

## Goal

Add a complete in-platform guide system that explains how the platform works for each role without rebuilding dashboards or changing core business logic.

## Database Migration

```text
backend/php/database/migrations/029_platform_guide_help_center.sql
```

## Files Created

### Backend

```text
backend/php/shared/HelpCenter.php
backend/php/database/migrations/029_platform_guide_help_center.sql
```

### Public Help Center

```text
web/public/help/index.php
web/public/help/role/index.php
web/public/help/owner/index.php
web/public/help/student/index.php
web/public/help/parent/index.php
web/public/help/academy/index.php
web/public/help/media-buyer/index.php
```

### Dashboard Help Shortcuts

```text
web/public/owner/help/index.php
web/public/student/help/index.php
web/public/parent/help/index.php
web/public/academy/help/index.php
web/public/media/help/index.php
```

### Owner Help CMS

```text
web/public/owner/help-center/index.php
web/public/owner/help-center/articles/index.php
web/public/owner/help-center/articles/new/index.php
web/public/owner/help-center/articles/edit/index.php
```

## Database Tables

```text
help_articles
user_onboarding_progress
user_tour_progress
```

## Help Center Routes

```text
/help
/help/student
/help/parent
/help/owner
/help/academy
/help/media-buyer
```

## Role Shortcuts

```text
/owner/help
/student/help
/parent/help
/academy/help
/media/help
```

## Owner CMS Routes

```text
/owner/help-center
/owner/help-center/articles
/owner/help-center/articles/new
/owner/help-center/articles/edit?id={id}
```

## Seeded Help Content

Default Arabic and English help articles were seeded for:

```text
General platform overview
Owner quick start
Student quick start
Parent quick start
Academy partner quick start
Media buyer quick start
How homework works
How parent progress works
How tracking links work
```

## Features Implemented

```text
Public help center
Role-based help pages
Search inside help articles
Categories displayed on cards
Still need help button
Owner help article CMS
Arabic/English help article support
Role-based onboarding checklist helper
Role-based guided tour helper
Dashboard help shortcuts
```

## Security

```text
Owner help CMS is Owner-only
Role-specific public help pages show only public + role articles
No private student/parent records are exposed
Media buyer help does not expose Owner-only settings or private customer data
```

## Current Limitations

```text
Dashboard cards were not injected directly into each dashboard to avoid rebuilding dashboards.
Contextual tooltips are available as helper/CMS content foundation but not wired into every page yet.
Tour display is a lightweight guide card/foundation; a JS overlay tour can be added later.
Checklist progress storage exists; automatic completion from actions is not fully wired yet.
```

## Manual Test Checklist

```text
Run migration 029
Open /help
Open /help/student
Open /help/parent
Open /help/owner
Open /help/academy
Open /help/media-buyer
Search for homework
Login as Owner
Open /owner/help-center
Create a new help article
Edit an existing help article
Confirm role pages show relevant articles
Open /student/help and confirm redirect to /help/student
Open /media/help and confirm redirect to /help/media-buyer
Confirm media buyer cannot access Owner help CMS
Test mobile layout
```

Stop here. Test help center and onboarding guidance before continuing.
