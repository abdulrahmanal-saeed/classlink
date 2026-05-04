# Hostinger Storage and Uploads Guide

## Storage Decision

The current app stores uploaded files on local/Hostinger server storage.

Database stores:

```text
file path / URL
metadata
uploader
file size
MIME type
```

Actual files are stored in folders on the server.

## Upload Folders

Create these folders if they do not exist:

```text
uploads/
uploads/materials/
uploads/materials/videos/
uploads/materials/audio/
uploads/materials/pdf/
uploads/materials/powerpoint/
uploads/materials/images/
uploads/materials/html/
uploads/materials/documents/
uploads/testimonials/
uploads/testimonials/audio/
uploads/testimonials/video/
uploads/level-tests/
uploads/homework/
uploads/scenarios/
public/assets/materials/
public/assets/materials/thumbnails/
```

Depending on deployment structure, public assets may map to:

```text
public_html/assets/materials/
```

## Permissions

Folders used for uploads must be writable by PHP.

Typical safe permissions:

```text
folders: 755 or 775 depending on Hostinger setup
files: 644
```

Avoid `777` unless Hostinger support explicitly requires it for testing, and never leave it if a safer permission works.

## Protect Private Uploads

Private student files should not be placed in openly browsable public folders unless access is controlled.

Rules:

```text
Student can only access own files.
Parent can only access linked child files.
Public cannot access unapproved testimonial media.
HTML materials must be sandboxed or download-only.
```

## Allowed File Types

Audio:

```text
mp3, wav, m4a, webm
```

Video:

```text
mp4, webm, mov
```

Images:

```text
jpg, jpeg, png, webp
```

PDF:

```text
pdf
```

PowerPoint:

```text
ppt, pptx
```

Documents:

```text
doc, docx
```

HTML:

```text
html only, sandboxed or download-only
```

## Blocked File Types

Never allow executable or script uploads:

```text
.php
.phtml
.phar
.exe
.bat
.cmd
.sh
.msi
.dll
.ps1
.com
.scr
.cgi
.pl
.py
.rb
.jar
```

## Maximum Upload Sizes

Configured by environment variables:

```text
MAX_AUDIO_UPLOAD_BYTES=26214400
MAX_VIDEO_UPLOAD_BYTES=209715200
MAX_IMAGE_UPLOAD_BYTES=10485760
MAX_DOCUMENT_UPLOAD_BYTES=52428800
MAX_HTML_UPLOAD_BYTES=2097152
```

Also check Hostinger PHP settings:

```text
upload_max_filesize
post_max_size
max_execution_time
memory_limit
```

## Material Upload Notes

### Videos

- Do not autoplay.
- Use lazy loading where possible.
- Large videos may need compression before upload.

### PDFs

- Preview in browser if supported.
- Otherwise allow safe download.

### PowerPoint

- Preview may not work in browser.
- Provide safe download/open message.

### HTML Materials

- HTML can contain active code.
- Use sandboxed iframe if previewing.
- If safety is uncertain, make download-only.
- Uploaded HTML must not access app cookies, sessions, localStorage, or authenticated requests.

### Images

- Prefer WebP/JPG compressed versions.
- Keep dimensions reasonable.
- Use lazy loading for non-critical images.

### Audio

- Do not autoplay.
- Validate MIME and extension.

## Moving Existing Uploads

If moving from staging to production:

1. Download the `uploads` folder from staging.
2. Upload it to the same relative path on Hostinger.
3. Preserve folder names.
4. Check database file paths still match.
5. Test opening materials, homework recordings, testimonials, and level test uploads.

## Backup Uploaded Files

At least weekly:

1. Compress the `uploads` folder.
2. Download it securely.
3. Store backup outside public access.
4. Keep database backup from the same date.

Suggested backup names:

```text
uploads_backup_YYYY_MM_DD.zip
database_backup_YYYY_MM_DD.sql
```
