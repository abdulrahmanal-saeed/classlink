# Hostinger Deployment Addendum — Phase 33

## Phase 33

Website Performance, Speed, and Technical Optimization.

## Database Migration

Run after Phase 32:

```text
backend/php/database/migrations/033_performance_indexes.sql
```

Export a database backup first.

## Files Created

```text
backend/php/shared/Performance.php
web/public/assets/js/performance.js
backend/php/database/migrations/033_performance_indexes.sql
```

## Files Updated

```text
web/public/assets/css/app.css
web/components/layout/public_layout.php
web/components/layout/dashboard_shell.php
```

## URLs to Test

```text
/
/pricing
/owner/dashboard
/student/dashboard
/parent/dashboard
/media/dashboard
/articles
/videos
```

## Manual Test

```text
Run migration 033
Open homepage on mobile
Open pricing page
Open student dashboard
Open parent dashboard
Open owner dashboard
Check browser console for errors
Check Network tab for large assets
Confirm scripts are deferred
Confirm non-critical images lazy load
Confirm lazy video shell works where used
```

## Optional Manual .htaccess Performance Rules

The repository already has:

```text
web/public/.htaccess
```

GitHub returned an update error while trying to patch this file. If you want to add Apache compression and cache manually in Hostinger File Manager, add these sections below the existing rewrite rules:

```apache
<IfModule mod_deflate.c>
  AddOutputFilterByType DEFLATE text/plain
  AddOutputFilterByType DEFLATE text/html
  AddOutputFilterByType DEFLATE text/xml
  AddOutputFilterByType DEFLATE text/css
  AddOutputFilterByType DEFLATE application/xml
  AddOutputFilterByType DEFLATE application/xhtml+xml
  AddOutputFilterByType DEFLATE application/rss+xml
  AddOutputFilterByType DEFLATE application/javascript
  AddOutputFilterByType DEFLATE application/x-javascript
  AddOutputFilterByType DEFLATE application/json
  AddOutputFilterByType DEFLATE image/svg+xml
</IfModule>

<IfModule mod_expires.c>
  ExpiresActive On
  ExpiresByType text/css "access plus 30 days"
  ExpiresByType application/javascript "access plus 30 days"
  ExpiresByType application/x-javascript "access plus 30 days"
  ExpiresByType image/jpeg "access plus 30 days"
  ExpiresByType image/png "access plus 30 days"
  ExpiresByType image/webp "access plus 30 days"
  ExpiresByType image/svg+xml "access plus 30 days"
  ExpiresByType image/gif "access plus 30 days"
  ExpiresByType font/woff2 "access plus 180 days"
  ExpiresByType font/woff "access plus 180 days"
  ExpiresByType video/mp4 "access plus 7 days"
  ExpiresByType audio/mpeg "access plus 7 days"
  ExpiresDefault "access plus 1 hour"
</IfModule>

<IfModule mod_headers.c>
  <FilesMatch "\.(css|js|png|jpg|jpeg|gif|webp|svg|woff|woff2)$">
    Header set Cache-Control "public, max-age=2592000, immutable"
  </FilesMatch>
  <FilesMatch "\.(mp4|webm|mp3|wav|m4a|pdf|ppt|pptx|doc|docx)$">
    Header set Cache-Control "public, max-age=604800"
  </FilesMatch>
  <FilesMatch "\.(php)$">
    Header set Cache-Control "no-store, no-cache, must-revalidate, max-age=0"
  </FilesMatch>
  Header always set X-Content-Type-Options "nosniff"
</IfModule>
```

## Current Limitations

```text
Pagination helper exists but must be wired page-by-page for large tables.
Lazy video helper exists but video pages need matching markup or perf_lazy_video_embed.
No Lighthouse score was measured here.
```

Stop here. Test Phase 33 before continuing.
