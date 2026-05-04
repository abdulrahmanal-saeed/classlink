# Hostinger Deployment Addendum — Phase 19
# إضافة دليل الرفع على Hostinger — المرحلة 19

This addendum covers Phase 19 only.

## Phase 19

Advanced Analytics.

---

## 1) What to deploy

Phase 19 adds:

```text
Advanced analytics migration
Analytics helper
Owner analytics dashboard
Marketing analytics
Student engagement analytics
Revenue analytics
Content and referral performance
Learning event tracking hooks
```

---

## 2) Database migration

Run this SQL file in phpMyAdmin after Phase 18:

```text
backend/php/database/migrations/019_advanced_analytics.sql
```

Always export a database backup first.

---

## 3) Files to upload

Backend:

```text
backend/php/shared/Analytics.php
backend/php/database/migrations/019_advanced_analytics.sql
```

Owner pages:

```text
web/public/owner/analytics/index.php
web/public/owner/analytics/marketing/index.php
web/public/owner/analytics/students/index.php
web/public/owner/analytics/revenue/index.php
web/public/owner/analytics/content/index.php
```

Updated learning pages:

```text
web/public/student/homework/view/index.php
web/public/student/scenarios/view/index.php
web/public/student/reviews/view/index.php
web/public/student/flashcards/index.php
```

Shared layout:

```text
web/components/layout/dashboard_shell.php
```

Docs:

```text
docs/PHASE_19_EXECUTION_REPORT.md
docs/HOSTINGER_PHASE_19_DEPLOYMENT_ADDENDUM.md
```

---

## 4) URLs to test

```text
/owner/analytics
/owner/analytics/marketing
/owner/analytics/students
/owner/analytics/revenue
/owner/analytics/content
```

---

## 5) Learning event test

1. Login as student.
2. Submit homework.
3. Submit scenario.
4. Submit review/test.
5. Review flashcard.
6. Login as Owner.
7. Open `/owner/analytics`.
8. Confirm activity appears in analytics.
9. Open `/owner/analytics/students`.
10. Confirm student engagement and low activity sections are visible.

---

## 6) Revenue test

1. Make sure purchases and payment records exist.
2. Open `/owner/analytics/revenue`.
3. Confirm:

```text
Payment status breakdown
Revenue by plan
```

---

## 7) Marketing funnel test

Public hooks are ready but must be added to public routes as needed:

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
```

After wiring those hooks, open `/owner/analytics/marketing` and confirm funnel counts.

---

## 8) Content analytics test

Use hooks on article/video detail pages:

```php
analytics_track('article_open', ['entity_type' => 'article', 'entity_id' => $articleId]);
analytics_track('video_play', ['entity_type' => 'video', 'entity_id' => $videoId]);
```

Then open:

```text
/owner/analytics/content
```

Confirm article/video performance appears.

---

## 9) Privacy notes

```text
Raw IP is not stored.
IP is stored as a hash using analytics_ip_hash_salt.
Sensitive metadata keys such as password, token, and api_key are removed.
Owner analytics pages are protected by Owner role.
```

Before production, update:

```text
analytics_ip_hash_salt
```

---

## 10) Known limitations

```text
Public tracking hooks are ready but not fully wired across all public pages yet.
Login event tracking is supported but not wired in the auth file in this phase.
No charts yet; MVP uses cards and tables.
CSRF protection still needs strengthening before production.
```

---

## Stop rule

Stop here. Test Phase 19 fully before moving to Phase 20.
