# Phase 24 Execution Report
# تقرير تنفيذ المرحلة 24

## Phase Name / اسم المرحلة

Advanced Testimonials: Text, Audio, Video, Student/Parent Submission, and Owner Moderation

نظام تقييمات متقدم: نص، صوت، فيديو، تقديم الطالب/ولي الأمر، ومراجعة المالك

---

## Goal / الهدف

Upgrade the testimonial system so students, parents, and public visitors can submit text, audio, or video testimonials, while Owner/Teacher reviews every testimonial before public display.

---

## Database Migration / تحديث قاعدة البيانات

Phase 24 adds:

```text
backend/php/database/migrations/024_advanced_testimonials.sql
```

---

## Files Created / الملفات التي تم إنشاؤها

### Database

```text
backend/php/database/migrations/024_advanced_testimonials.sql
```

### Backend helper

```text
backend/php/shared/Testimonials.php
```

### Student/Parent pages

```text
web/public/student/testimonial/index.php
web/public/parent/testimonial/index.php
```

### Owner pages

```text
web/public/owner/testimonials/index.php
web/public/owner/testimonials/pending/index.php
web/public/owner/testimonials/view/index.php
web/public/owner/settings/testimonials/index.php
```

---

## Files Changed / الملفات التي تم تعديلها

```text
web/public/testimonials/index.php
web/public/submit-testimonial/index.php
web/components/layout/dashboard_shell.php
```

---

## Database Tables / جداول قاعدة البيانات

### testimonials

Supports:

```text
submitter_type: student / parent / public / owner
student_user_id
parent_user_id
child_student_user_id
source: student_dashboard / parent_dashboard / public_form / owner_manual_entry
rating
testimonial_text
audio_url
video_url
media_type: text / audio / video / mixed
display_name
display_preference
level
learning_goal
child_learning_focus
permission_to_publish
status: pending_review / approved / rejected / archived
show_on_homepage
show_on_testimonials_page
featured
sort_order
owner_notes
public_text_override
approved_by_user_id
approved_at
rejected_at
archived_at
created_at
updated_at
```

### testimonial_media

Supports media metadata:

```text
testimonial_id
media_type: audio / video
file_url
original_filename
mime_type
file_size
duration_seconds
status: private / public / removed
created_at
```

---

## Settings Added / الإعدادات المضافة

```text
testimonials_public_form_enabled
testimonials_from_students_enabled
testimonials_from_parents_enabled
testimonials_allow_audio
testimonials_allow_video
testimonials_require_publish_permission
testimonials_require_completed_lesson
testimonials_show_on_homepage
testimonials_show_page
testimonials_max_audio_mb
testimonials_max_video_mb
testimonials_default_status
```

Owner page:

```text
/owner/settings/testimonials
```

---

## Backend Helper / ملف المساعدة

Implemented:

```text
backend/php/shared/Testimonials.php
```

Main functions:

```text
testimonial_create
testimonial_validate_file
testimonial_notify_owner
testimonial_all
testimonial_find
testimonial_media
testimonial_update_owner
testimonial_set_status
testimonial_public
testimonial_public_name
testimonial_public_text
testimonial_parent_can_access_child
```

---

## Student Submission / تقديم الطالب

Page:

```text
/student/testimonial
```

Student can submit:

```text
Rating 1-5
Text testimonial
Audio upload optional
Video upload optional
Display preference
Level optional
Learning goal optional
Publish permission checkbox
```

After submit:

```text
status = pending_review
Owner notification created
Push attempt created for Owner if configured
Audit log written
Success message shown
```

---

## Parent Submission / تقديم ولي الأمر

Page:

```text
/parent/testimonial
```

Parent can submit only for linked children.

Parent can submit:

```text
Rating 1-5
Text testimonial
Audio upload optional
Video upload optional
Child selection from linked children only
Display preference
Child learning focus
Publish permission checkbox
```

Security:

```text
Parent cannot submit testimonial for unlinked child.
```

---

## Public Submission / النموذج العام

Updated:

```text
/submit-testimonial
```

Now uses the same moderation flow:

```text
status = pending_review
permission required
text/audio/video supported
Owner notification created
```

---

## Owner Moderation / مراجعة المالك

Pages:

```text
/owner/testimonials
/owner/testimonials/pending
/owner/testimonials/view?id={id}
/owner/settings/testimonials
```

Owner can:

```text
View all testimonials
Filter by status, media type, submitter type
Open detail page
Read text
Play audio
Play video
Edit public display name
Edit public text override
Add internal notes
Approve
Reject
Archive
Mark featured
Show/hide on homepage
Show/hide on testimonials page
Set sort order
```

Approval is required before public display.

---

## Public Display / العرض العام

Updated:

```text
/testimonials
```

Rules:

```text
Only approved testimonials appear
Only testimonials with permission_to_publish = 1 appear
Only show_on_testimonials_page = 1 appears
Anonymous preference is respected
Private email/WhatsApp/internal notes are never shown
Audio/video is loaded with controls and preload=metadata
No autoplay
```

Homepage logic foundation:

```text
testimonial_public(true)
```

This returns featured approved testimonials for homepage use.

---

## Upload Validation / التحقق من الملفات

Audio allowed:

```text
mp3
wav
m4a
webm
```

Video allowed:

```text
mp4
webm
mov
```

Validation includes:

```text
Extension validation
MIME validation
File size validation
Random safe filename
No executable files
Media metadata stored
```

Upload path:

```text
web/public/uploads/testimonials/YYYY/MM/
```

---

## Notifications / الإشعارات

Owner receives actionable notification:

```text
Title: New testimonial submitted
Action label: Review Testimonial
Action URL: /owner/testimonials/view?id={id}
```

Push notification attempt is also created for Owner if Phase 20 push is configured.

---

## Audit Logs / سجلات المراجعة

Logged actions:

```text
testimonial_submitted
testimonial_media_uploaded
testimonial_edited
testimonial_approved
testimonial_rejected
testimonial_archived
testimonial_pending_review
testimonial_settings_updated
```

---

## Navigation / التنقل

Added links in dashboard shell:

Owner:

```text
Testimonials
Pending Testimonials
Testimonial Settings
```

Student:

```text
Leave a Testimonial
```

Parent:

```text
Leave Parent Testimonial
```

---

## Security / الأمان

Implemented:

```text
Student can submit only as self
Parent can submit only for linked child
Public visitor can submit only through public form
Owner can review all testimonials
Unapproved testimonials do not appear publicly
Permission checkbox required
Anonymous display preference respected
Private contact data not exposed publicly
Upload extension/MIME/size validation
Media is not included in public display unless testimonial is approved and permitted
```

---

## Acceptance Criteria Status / حالة القبول

Implemented:

```text
Student testimonial form
Parent testimonial form with linked-child check
Public testimonial form upgrade
Text/audio/video/mixed support via upload
Pending review by default
Owner notification
Owner moderation list
Owner review detail page
Approve/reject/archive
Featured/homepage/page visibility controls
Public approved-only testimonial display
Audit logging
Upload validation
```

Partially implemented / future improvement:

```text
Browser audio recording UI is not implemented yet; upload fallback is available
Browser video recording UI is not implemented yet; upload fallback is available
Delete testimonial action is not implemented yet to avoid accidental data loss
Homepage section needs to call testimonial_public(true) where homepage component is managed
Student/Parent dashboard cards are represented by sidebar links; dashboard card UI can be added in a UI-only pass
Approved notification back to student/parent is not implemented yet
```

---

## Manual Test Checklist / قائمة الاختبار اليدوي

1. Run migration:

```text
backend/php/database/migrations/024_advanced_testimonials.sql
```

2. Login as Student.
3. Open:

```text
/student/testimonial
```

4. Submit text testimonial.
5. Submit audio testimonial.
6. Submit video testimonial if enabled.
7. Confirm success message.
8. Login as Parent.
9. Open:

```text
/parent/testimonial
```

10. Submit testimonial for linked child.
11. Try unlinked child ID manually and confirm blocked.
12. Open public form:

```text
/submit-testimonial
```

13. Submit public testimonial.
14. Login as Owner.
15. Open:

```text
/owner/testimonials/pending
```

16. Open detail page.
17. Play audio/video.
18. Approve testimonial.
19. Open:

```text
/testimonials
```

20. Confirm approved testimonial appears.
21. Reject another testimonial.
22. Confirm rejected testimonial does not appear publicly.
23. Test anonymous display preference.
24. Check audit log.
25. Test mobile layout.

---

## Stop Point / نقطة التوقف

Stop here. Test this phase before continuing.

توقف هنا. اختبر هذه المرحلة قبل الانتقال للمرحلة التالية.
