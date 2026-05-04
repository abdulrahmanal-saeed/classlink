# Hostinger Deployment Addendum — Phase 27

## Phase 27

Media Buyer / Marketing Partner Dashboard, Tracking, Analytics, and Commission System.

## Database Migration

Run after Phase 26:

```text
backend/php/database/migrations/027_media_buyer_commissions.sql
```

Export a database backup first.

## Backend Files

```text
backend/php/shared/MediaBuyer.php
backend/php/database/migrations/027_media_buyer_commissions.sql
backend/php/core/Auth.php
```

## Public Tracking Files

```text
web/public/assets/js/media-tracking.js
web/public/api/media/track/index.php
web/public/api/media/attach-order/index.php
web/components/layout/public_layout.php
```

## Media Buyer Pages

```text
web/public/media/dashboard/index.php
web/public/media/campaigns/index.php
web/public/media/links/index.php
web/public/media/orders/index.php
web/public/media/commissions/index.php
web/public/media/payouts/index.php
web/public/media/settings/index.php
```

## Owner Pages

```text
web/public/owner/media-buyers/index.php
web/public/owner/media-buyers/new/index.php
web/public/owner/media-buyers/view/index.php
web/public/owner/media-commissions/index.php
```

## Layout Updated

```text
web/components/layout/dashboard_shell.php
```

## URLs to Test

```text
/owner/media-buyers
/owner/media-buyers/new
/owner/media-commissions
/media/dashboard
/media/links
/media/orders
/media/commissions
/media/payouts
/media/settings
/pricing?partner=demo&utm_source=facebook&utm_campaign=kids_arabic
```

## Checkout Integration Required

The checkout/payment backend still needs to call the MediaBuyer helper when an order is created or when payment status changes.

Required order data:

```text
checkout reference
partner code
selected plan
order amount
payment status
masked customer name
UTM source
UTM medium
UTM campaign
UTM content
UTM term
```

If payment status is paid, commission is created automatically.

## Security Checks

```text
Media buyer cannot access Owner routes
Media buyer sees only own orders and commissions
Customer data is masked
No learning data is shown to media buyer
Commission changes are Owner-only
Audit log records commission status updates
```

## Current Limitations

```text
Checkout must still be wired to attribution helper.
Refund workflow must call commission reversal.
Campaign creation UI is not fully built yet.
Export and payout creation UI are not implemented yet.
Package-specific commission overrides are future enhancement.
```

Stop here. Test Phase 27 before continuing.
