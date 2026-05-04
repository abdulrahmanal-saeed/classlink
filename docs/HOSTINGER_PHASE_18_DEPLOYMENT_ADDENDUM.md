# Hostinger Deployment Addendum — Phase 18
# إضافة دليل الرفع على Hostinger — المرحلة 18

This addendum covers Phase 18 only.

## Phase 18

Referral System.

---

## 1) What to deploy

Phase 18 adds:

```text
Student referral link
Parent referral link
Owner referral tracking
Referral settings
Reward application
Referral helper functions
```

---

## 2) Database migration

Run this SQL file in phpMyAdmin after Phase 17:

```text
backend/php/database/migrations/018_referral_system.sql
```

Always export a database backup first.

---

## 3) Files to upload

Backend:

```text
backend/php/shared/ReferralSystem.php
backend/php/database/migrations/018_referral_system.sql
```

Pages:

```text
web/public/student/referrals/index.php
web/public/parent/referrals/index.php
web/public/owner/referrals/index.php
web/public/owner/settings/referrals/index.php
```

Shared layout:

```text
web/components/layout/dashboard_shell.php
```

Docs:

```text
docs/PHASE_18_EXECUTION_REPORT.md
docs/HOSTINGER_PHASE_18_DEPLOYMENT_ADDENDUM.md
```

---

## 4) URLs to test

Student:

```text
/student/referrals
```

Parent:

```text
/parent/referrals
```

Owner:

```text
/owner/referrals
/owner/settings/referrals
```

---

## 5) Referral flow test

1. Login as Student.
2. Open `/student/referrals`.
3. Copy referral link.
4. Open the public site with:

```text
?ref=CODE
```

5. Complete checkout.
6. Attach the referral code to the purchase.
7. Mark payment paid or verified.
8. Qualify referral from `/owner/referrals`.
9. Apply reward.
10. Confirm status becomes:

```text
reward_applied
```

---

## 6) Required checkout/payment hooks

When visitor lands with referral code:

```php
referral_record_landing($_GET['ref']);
```

When checkout creates purchase:

```php
referral_attach_to_purchase($purchaseId, $referralCode);
```

When payment is verified or manually approved:

```php
referral_qualify_from_paid_purchase($purchaseId, $paymentRecordId);
```

These helper functions are ready in:

```text
backend/php/shared/ReferralSystem.php
```

---

## 7) Reward behavior

Supported reward types:

```text
free_session
aed_discount
both
```

Behavior:

```text
free_session adds credit to active lesson package if available.
aed_discount records discount amount on the referral.
both does both actions.
```

If no active package exists, the reward is still recorded for manual follow-up.

---

## 8) Settings test

Open:

```text
/owner/settings/referrals
```

Test:

```text
Enable/disable program
Change reward type
Change reward value
Change terms text
Change public base URL
```

---

## 9) Known limitations

```text
Public landing capture for ?ref=CODE needs to be added to the public homepage or routing layer.
Checkout/payment files were not clearly discoverable in repo search, so helper functions and manual qualification were added.
Discount rewards are recorded but not automatically applied to future checkout yet.
CSRF protection still needs strengthening before production.
```

---

## Stop rule

Stop here. Test Phase 18 fully before moving to Phase 19.
