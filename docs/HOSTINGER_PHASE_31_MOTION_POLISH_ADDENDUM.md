# Hostinger Deployment Addendum — Phase 31

## Phase 31

Website Animation, Microinteractions, and Motion Polish.

## Database Migration

No database migration is required.

## Files Created

```text
web/public/assets/js/motion-polish.js
```

## Files Updated

```text
web/public/assets/css/app.css
web/components/layout/public_layout.php
web/components/layout/dashboard_shell.php
```

## Deployment Steps

Upload the updated files to Hostinger.

Clear browser cache or use a hard refresh because CSS and JS changed.

## URLs to Test

```text
/
/pricing
/owner/dashboard
/student/dashboard
/parent/dashboard
/media/dashboard
```

## Manual Test

```text
Open homepage on desktop and mobile
Check hero entrance animation
Check CTA hover polish
Check pricing cards hover polish
Check FAQ expand/collapse
Open dashboards and check card/table hover polish
Enable reduced-motion setting and confirm animations are reduced
Check browser console for errors
Check mobile layout
```

## Notes

```text
No heavy animation libraries were added.
Animations are CSS-first and lightweight.
Reduced-motion preferences are respected.
Some page-specific effects need helper classes such as flashcard, badge-earned, success-pulse, or pending-action to be added in the relevant pages later.
```

Stop here. Test Phase 31 before continuing.
