# Hostinger Deployment Addendum — Phase 25

## Phase 25

Advanced Materials Library, Uploads, Viewers, and Student Access.

## Stack

Implemented with PHP + MySQL and Hostinger/local file storage. Supabase Storage is not used.

## Database Migration

Run after Phase 24:

```text
backend/php/database/migrations/025_advanced_materials_library.sql
```

Export a database backup first.

## Backend Files

```text
backend/php/shared/MaterialsLibrary.php
backend/php/database/migrations/025_advanced_materials_library.sql
```

## Owner Pages

```text
web/public/owner/materials/index.php
web/public/owner/materials/new/index.php
web/public/owner/materials/view/index.php
web/public/owner/materials/edit/index.php
web/public/owner/materials/assign/index.php
web/public/owner/materials/categories/index.php
web/public/owner/materials/analytics/index.php
```

## Student Pages

```text
web/public/student/materials/index.php
web/public/student/materials/view/index.php
```

## Parent Pages

```text
web/public/parent/child/materials/index.php
web/public/parent/child/materials/view/index.php
```

## Layout Updated

```text
web/components/layout/dashboard_shell.php
```

## Upload Folder

Make this folder writable by PHP:

```text
web/public/uploads/materials
```

The system creates subfolders automatically.

## Settings

Important settings inserted by migration:

```text
materials_upload_storage_driver = local
materials_upload_base_path = web/public/uploads/materials
materials_allow_html_upload = 1
materials_html_default_mode = download_only
```

## URLs to Test

```text
/owner/materials
/owner/materials/new
/owner/materials/categories
/owner/materials/analytics
/student/materials
```

Parent routes use query parameters:

```text
/parent/child/materials?child_id={childId}
/parent/child/materials/view?child_id={childId}&material_id={materialId}
```

## Manual Test

```text
Run migration 025
Create material
Upload supported file
Assign to student
Open as student
Mark completed
Open as parent for linked child
Confirm unlinked child blocked
Check storage health
Check notifications
Check audit log
```

## Current Limitations

```text
Assign page assigns one student at a time in MVP.
Delete and replace-file actions are not implemented yet.
Download count tracking is not wired yet.
Attachment UI for homework/lesson/scenario/review is not fully wired yet.
```

Stop here. Test Phase 25 before continuing.
