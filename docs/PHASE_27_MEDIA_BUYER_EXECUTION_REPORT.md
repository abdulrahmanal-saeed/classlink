# Phase 27 Execution Report

## Phase Name

Media Buyer / Marketing Partner Dashboard, Tracking, Analytics, and Commission System

## Goal

Create a restricted media buyer role and dashboard for marketing partners who promote Habiba Nabil Arabic Academy and earn commission from paid orders.

Media buyers must not have Owner access and must not see private student, parent, learning, homework, scenario, review, or level-test data.

---

## Database Migration

```text
backend/php/database/migrations/027_media_buyer_commissions.sql
```

---

## Files Created

### Backend

```text
backend/php/shared/MediaBuyer.php
```

### Public tracking

```text
web/public/assets/js/media-tracking.js
web/public/api/media/track/index.php
web/public/api/media/attach-order/index.php
```

### Media buyer pages

```text
web/public/media/dashboard/index.php
web/public/media/campaigns/index.php
web/public/media/links/index.php
web/public/media/orders/index.php
web/public/media/commissions/index.php
web/public/media/payouts/index.php
web/public/media/settings/index.php
```

### Owner pages

```text
web/public/owner/media-buyers/index.php
web/public/owner/media-buyers/new/index.php
web/public/owner/media-buyers/view/index.php
web/public/owner/media-commissions/index.php
```

---

## Files Updated

```text
backend/php/core/Auth.php
web/components/layout/public_layout.php
web/components/layout/dashboard_shell.php
```

---

## New Role

```text
media_buyer
```

`role_home_path()` now routes media buyers to:

```text
/media/dashboard
```

---

## Database Models

```text
media_buyer_profiles
marketing_campaigns
attribution_events
order_attributions
commission_records
payout_records
```

---

## Settings Added

```text
media_buyers_enabled
media_global_commission_type
media_global_commission_rate
media_fixed_commission_amount
media_commission_approval_required
media_payout_cycle
media_cookie_days
media_default_attribution_model
media_export_enabled
```

---

## Public Tracking

The public layout now loads:

```text
/assets/js/media-tracking.js
```

The script captures:

```text
partner
utm_source
utm_medium
utm_campaign
utm_content
utm_term
landing page
first touch
last touch
visitor/session IDs
```

It stores attribution in browser cookies and sends a public event to:

```text
POST /api/media/track
```

---

## Checkout Attribution Integration

A secure Owner-protected endpoint was added:

```text
POST /api/media/attach-order
```

This attaches attribution to a checkout order and creates commission if payment status is paid.

Important integration note:

Checkout/payment backend should call `media_attach_order_attribution()` directly when an order is created or payment status changes. The API endpoint is currently Owner-protected for manual/internal testing to avoid public manipulation.

---

## Commission Logic

Implemented in:

```text
backend/php/shared/MediaBuyer.php
```

Rules:

```text
Commission is created only when payment_status = paid
Pending/failed/cancelled/refunded orders are not payable
Duplicate commission for same media buyer + order is prevented
Owner can change commission status
Commission changes are audit logged
```

Statuses:

```text
pending
approved
rejected
paid
reversed
```

---

## Media Buyer Dashboard

Routes:

```text
/media/dashboard
/media/campaigns
/media/links
/media/orders
/media/commissions
/media/payouts
/media/settings
```

Media buyer can see only own:

```text
clicks
checkout starts
paid orders
revenue
conversion rate
pending commission
approved commission
paid commission
tracking links
attributed orders
commission records
payout history
```

Private student/parent learning data is not shown.

---

## Owner Management

Routes:

```text
/owner/media-buyers
/owner/media-buyers/new
/owner/media-buyers/view?id={id}
/owner/media-commissions
```

Owner can:

```text
Create media buyer account
Set partner code
Set commission type/rate
View partner summary
View tracking link
Approve/reject/mark paid/reverse commission
```

---

## Security

Implemented:

```text
Media buyer role has separate dashboard
Media buyer cannot access Owner routes
Media buyer pages use require_role('media_buyer')
Owner pages use require_role('owner_teacher')
Media buyer queries are scoped to own profile ID
Order table shows masked customer name only
No email, WhatsApp, recordings, homework, scenarios, reviews, or level tests are exposed to media buyer
Commission changes are Owner-only
Audit log records commission changes
```

---

## Current Limitations

```text
Checkout flow must still be wired to call media_attach_order_attribution() on order creation/payment status update.
Refund reversal should be called from payment refund workflow.
Campaign creation UI is not fully built yet; campaigns table and display exist.
Media buyer export is not implemented yet.
Payout creation UI is not implemented yet.
Package-specific commission overrides are not implemented yet.
Customer progression after purchase is Owner-only future enhancement and not exposed to media buyer.
```

---

## Manual Test Checklist

```text
Run migration 027
Create media buyer from /owner/media-buyers/new
Log in as media buyer
Open /media/dashboard
Open /media/links
Open tracking link with partner and UTM params
Confirm attribution event is stored
Create/attach test order attribution as Owner
Mark test order paid through attach-order or helper
Confirm commission record appears
Open /media/orders as media buyer
Open /media/commissions as media buyer
Confirm only own data appears
Open /owner/media-commissions
Approve commission
Mark commission paid
Check audit log
Try opening Owner routes as media buyer and confirm blocked
Confirm no private student/parent learning data appears
```

---

## Stop Point

Stop here. Test media buyer tracking and commission before continuing.
