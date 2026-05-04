# Phase 19 Execution Report
# تقرير تنفيذ المرحلة 19

## Phase Name / اسم المرحلة

Advanced Analytics

التحليلات المتقدمة

---

## Goal / الهدف

Build analytics for marketing, conversion, revenue, and engagement.

بناء تحليلات للتسويق، التحويلات، الإيرادات، وتفاعل الطلاب.

---

## Database Migration / تحديث قاعدة البيانات

Phase 19 adds a migration:

```text
backend/php/database/migrations/019_advanced_analytics.sql
```

---

## Files Created / الملفات التي تم إنشاؤها

### Database

```text
backend/php/database/migrations/019_advanced_analytics.sql
```

### Backend helper

```text
backend/php/shared/Analytics.php
```

### Owner analytics pages

```text
web/public/owner/analytics/index.php
web/public/owner/analytics/marketing/index.php
web/public/owner/analytics/students/index.php
web/public/owner/analytics/revenue/index.php
web/public/owner/analytics/content/index.php
```

---

## Files Changed / الملفات التي تم تعديلها

```text
web/components/layout/dashboard_shell.php
web/public/student/homework/view/index.php
web/public/student/scenarios/view/index.php
web/public/student/reviews/view/index.php
web/public/student/flashcards/index.php
```

---

## Migration 019 Changes / تغييرات Migration 019

The existing `analytics_events` table is expanded with:

```text
event_category
session_id
visitor_id
role
entity_type
entity_id
referrer_url
utm_source
utm_medium
utm_campaign
device_type
ip_hash
```

Indexes added:

```text
idx_analytics_category_date
idx_analytics_visitor_date
idx_analytics_entity
idx_analytics_event_date
```

Analytics settings inserted:

```text
analytics_enabled = 1
analytics_privacy_mode = privacy_first
analytics_track_public_pages = 1
analytics_track_learning_events = 1
analytics_ip_hash_salt = change-this-salt-in-production
analytics_retention_days = 365
```

---

## Privacy / الخصوصية

Implemented privacy-first behavior:

```text
No raw IP storage.
IP is hashed with salt.
Visitor ID is random cookie.
Sensitive fields such as password/token/api_key are removed from metadata.
No public analytics data is exposed.
Owner analytics pages are protected by owner role.
```

Important production note:

```text
Change analytics_ip_hash_salt in production.
```

---

## Trackable Public Events / أحداث الموقع العامة

Supported by `analytics_track()`:

```text
page_view
pricing_view
checkout_start
checkout_submit
payment_pending
student_form_submit
level_check_start
level_check_submit
booking_request
article_open
video_play
testimonial_submit
```

---

## Trackable Learning Events / أحداث التعلم

Supported by `analytics_track()`:

```text
login
homework_submit
scenario_submit
review_submit
flashcard_review
session_completed
badge_earned
```

---

## Events Wired in This Phase / الأحداث المربوطة فعلياً في هذه المرحلة

Wired now:

```text
homework_submit
scenario_submit
review_submit
flashcard_review
```

These pages now call `analytics_track()`:

```text
/student/homework/view?id={homeworkId}
/student/scenarios/view?id={scenarioId}
/student/reviews/view?id={reviewId}
/student/flashcards
```

---

## Hooks Ready for Public Pages / دوال جاهزة لصفحات الموقع العامة

Use:

```php
analytics_track('page_view');
analytics_track('pricing_view');
analytics_track('checkout_start');
analytics_track('checkout_submit');
analytics_track('payment_pending');
analytics_track('student_form_submit');
analytics_track('level_check_start');
analytics_track('level_check_submit');
analytics_track('booking_request');
analytics_track('article_open', ['entity_type' => 'article', 'entity_id' => $articleId]);
analytics_track('video_play', ['entity_type' => 'video', 'entity_id' => $videoId]);
analytics_track('testimonial_submit');
```

These should be added to public page controllers/routes as they are reviewed.

---

## Implemented Owner Pages / صفحات المالك المنفذة

```text
/owner/analytics
/owner/analytics/marketing
/owner/analytics/students
/owner/analytics/revenue
/owner/analytics/content
```

---

## Analytics Dashboard / لوحة التحليلات

Shows:

```text
Visitors last 30 days
Page views last 30 days
Checkout starts last 30 days
Checkout submits last 30 days
Active students last 30 days
Homework submits last 30 days
Sessions completed last 30 days
Paid revenue total
Conversion funnel
```

---

## Marketing Analytics / تحليلات التسويق

Shows:

```text
Visitors
Page views
Pricing views
Checkout conversion
Conversion funnel
```

---

## Student Analytics / تحليلات الطلاب

Shows:

```text
Student engagement last 30 days
Activity count
Last activity
Low activity students
```

Uses:

```text
learning_activity_logs
analytics_events
homework_submissions
lesson_sessions
```

---

## Revenue Analytics / تحليلات الإيرادات

Shows:

```text
Payment status breakdown
Revenue by plan
```

Uses:

```text
payment_records
purchases
plans
```

---

## Content Analytics / تحليلات المحتوى

Shows:

```text
Article opens
Video plays
Referral performance
```

Referral performance includes:

```text
Referral code
Owner name
Landing count
Referral count
Rewards applied
```

---

## Backend Helper / ملف المساعدة

Implemented:

```text
backend/php/shared/Analytics.php
```

Main functions:

```text
analytics_track
analytics_funnel
analytics_dashboard_summary
analytics_payment_breakdown
analytics_revenue_by_plan
analytics_student_engagement
analytics_low_activity_students
analytics_content_performance
analytics_referral_performance
```

---

## Navigation / التنقل

Owner sidebar now includes:

```text
Analytics
Marketing Analytics
Student Analytics
Revenue Analytics
Content Analytics
```

---

## Known Limitations / القيود الحالية

- Public page tracking hooks are ready but not fully wired across all public pages yet.
- Login event tracking is supported but not wired because auth/login file was not modified in this phase.
- Revenue conversion depends on payment/purchase statuses being updated correctly.
- Article/video analytics require adding `analytics_track()` to article/video detail pages.
- No charts yet; pages use cards and tables for stable MVP.
- CSRF protection still needs strengthening before production.

---

## Manual Test Checklist / قائمة الاختبار اليدوي

1. Run migration:

```text
backend/php/database/migrations/019_advanced_analytics.sql
```

2. Submit homework as student.
3. Submit scenario as student.
4. Submit review/test as student.
5. Review flashcard as student.
6. Open:

```text
/owner/analytics
```

7. Confirm overview metrics show.
8. Open:

```text
/owner/analytics/marketing
```

9. Confirm funnel is visible.
10. Open:

```text
/owner/analytics/students
```

11. Confirm engagement and low activity sections show.
12. Open:

```text
/owner/analytics/revenue
```

13. Confirm payment breakdown and revenue by plan show.
14. Open:

```text
/owner/analytics/content
```

15. Confirm content/referral performance sections show.
16. Add public hooks for page_view/checkout events and repeat funnel test.

---

## Stop Point / نقطة التوقف

Stop here. Test this phase before continuing.

توقف هنا. اختبر هذه المرحلة قبل الانتقال للمرحلة التالية.
