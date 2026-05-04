# Hostinger Deployment Addendum — Phase 24

## Phase 24

Advanced Testimonials: Text, Audio, Video, Student/Parent Submission, and Owner Moderation.

## Database migration

Run after Phase 23:

```text
backend/php/database/migrations/024_advanced_testimonials.sql
```

Export a database backup first.

## Backend files to upload

```text
backend/php/shared/Testimonials.php
backend/php/database/migrations/024_advanced_testimonials.sql
```

## Student and Parent pages

```text
web/public/student/testimonial/index.php
web/public/parent/testimonial/index.php
```

## Owner pages

```text
web/public/owner/testimonials/index.php
web/public/owner/testimonials/pending/index.php
web/public/owner/testimonials/view/index.php
web/public/owner/settings/testimonials/index.php
```

## Public pages updated

```text
web/public/testimonials/index.php
web/public/submit-testimonial/index.php
```

## Layout updated

```text
web/components/layout/dashboard_shell.php
```

## Upload folder

Make sure this folder is writable by PHP:

```text
web/public/uploads/testimonials
```

The system creates year/month subfolders automatically.

## URLs to test

```text
/student/testimonial
/parent/testimonial
/owner/testimonials
/owner/testimonials/pending
/owner/settings/testimonials
/testimonials
/submit-testimonial
```

## Manual test

```text
Submit testimonial as Student
Submit testimonial as Parent
Submit testimonial from public form
Open Owner pending queue
Review detail page
Play audio/video
Approve testimonial
Confirm public display
Reject another testimonial
Confirm rejected item is hidden publicly
Check audit log
```

## Current limitations

```text
Browser recording UI is not implemented yet; upload fallback is available.
Delete action is intentionally not implemented yet.
Homepage needs to call testimonial_public(true) in its testimonials section.
Student/Parent dashboard cards can be improved later; sidebar links are added now.
Approved notification back to student/parent is not implemented yet.
```

Stop here. Test Phase 24 before continuing.
