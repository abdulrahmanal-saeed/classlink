# Phase 18 Execution Report
# تقرير تنفيذ المرحلة 18

## Phase Name / اسم المرحلة

Referral System

نظام الترشيحات والمكافآت

---

## Goal / الهدف

Build student referral links and reward tracking.

بناء روابط ترشيح للطلاب وأولياء الأمور مع تتبع المكافآت.

---

## Database Migration / تحديث قاعدة البيانات

Phase 18 adds a migration:

```text
backend/php/database/migrations/018_referral_system.sql
```

---

## Files Created / الملفات التي تم إنشاؤها

### Database

```text
backend/php/database/migrations/018_referral_system.sql
```

### Backend helper

```text
backend/php/shared/ReferralSystem.php
```

### Pages

```text
web/public/parent/referrals/index.php
web/public/owner/referrals/index.php
web/public/owner/settings/referrals/index.php
```

---

## Files Changed / الملفات التي تم تعديلها

```text
web/public/student/referrals/index.php
web/components/layout/dashboard_shell.php
```

---

## Important Design Note / ملاحظة تصميم مهمة

The old `referrals` table had a unique `referral_code`, which is not enough for tracking many conversions from the same student code.

لذلك تم إضافة جدول جديد:

```text
referral_codes
```

This table owns the reusable referral code for each student/parent.

The existing `referrals` table is now used for conversion tracking and rewards.

---

## Migration 018 Changes / تغييرات Migration 018

### New table: referral_codes

```text
id
owner_user_id
code
status: active / disabled
landing_count
created_at
updated_at
```

Each student/parent can have one reusable referral code.

---

### referrals expanded

Adds support for:

```text
referral_code_id
source_referral_code
referred_user_id
referred_name
purchase_id
payment_record_id
reward_type
reward_value
reward_credit_amount
reward_discount_amount
applied_by_user_id
qualified_at
reward_applied_at
notes
```

Statuses now include:

```text
pending
qualified
reward_pending
reward_applied
rejected
```

Legacy statuses are kept for compatibility:

```text
new
converted
rewarded
cancelled
```

---

### purchases expanded

Adds:

```text
referral_code
referral_code_id
referral_id
```

---

### payment_records expanded

Adds:

```text
referral_code
referral_id
```

---

### Referral settings inserted

```text
referral_program_enabled = 1
referral_reward_type = free_session
referral_reward_value = 1
referral_terms_text = Share your referral link...
referral_public_base_url = https://mshabibanabil.com/?ref=
```

---

## Implemented Pages / الصفحات المنفذة

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

## Student/Parent Features / خصائص الطالب وولي الأمر

Student/Parent can:

```text
View referral code
Copy referral link
See landing/visit count
Read terms
Track referral status
Track reward status
```

---

## Owner Features / خصائص المالك

Owner can:

```text
View all referrals
Qualify referral manually from paid purchase
Apply reward manually
Reject referral
Edit referral settings
Enable/disable referral program
Set reward type
Set reward value
Set terms text
Set public base URL
```

---

## Reward Types / أنواع المكافآت

Supported:

```text
free_session
aed_discount
both
```

Reward application behavior:

```text
free_session -> adds credits to active lesson package if available
aed_discount -> records discount amount on referral
both -> adds credits and records discount amount
```

If the referrer has no active lesson package, the referral still becomes reward_applied, and the reward values are recorded for manual follow-up.

---

## Backend Helper / ملف المساعدة

Implemented:

```text
backend/php/shared/ReferralSystem.php
```

Main functions:

```text
referral_get_or_create_code
referral_public_link
referral_find_code
referral_record_landing
referral_attach_to_purchase
referral_qualify_from_paid_purchase
referral_apply_reward
referral_reject
referral_all
referral_for_user
referral_update_setting
```

---

## Checkout / Payment Integration Notes / ملاحظات ربط الدفع

The helper functions are ready for the checkout/payment flow:

### When visitor opens site with ref code

Call:

```php
referral_record_landing($_GET['ref']);
```

Then store the code in session/cookie.

---

### When checkout creates a purchase

Call:

```php
referral_attach_to_purchase($purchaseId, $referralCode);
```

This stores:

```text
purchases.referral_code
purchases.referral_code_id
```

---

### When payment is verified/approved

Call:

```php
referral_qualify_from_paid_purchase($purchaseId, $paymentRecordId);
```

This creates a referral row with:

```text
status = reward_pending
```

---

### When Owner applies reward

The Owner page calls:

```php
referral_apply_reward($ownerId, $referralId, $post);
```

This records audit log:

```text
referral_reward_applied
```

---

## Navigation / التنقل

Owner sidebar now includes:

```text
Referrals
Referral Settings
```

Parent sidebar now includes:

```text
Referrals
```

Student referral link already exists in student sidebar.

---

## Known Limitations / القيود الحالية

- Public landing page capture for `?ref=CODE` needs a small hook in the public homepage/checkout flow.
- Checkout file was not clearly discoverable in repository search, so integration is provided as helper functions and manual qualify form.
- Payment approval flow should call `referral_qualify_from_paid_purchase()` after a payment is marked verified/approved.
- Discount reward is recorded on referral but not yet applied to a future invoice/checkout automatically.
- If referrer has no active lesson package, free session credit cannot be added automatically and should be handled manually.
- CSRF protection still needs strengthening before production.

---

## Manual Test Checklist / قائمة الاختبار اليدوي

1. Run migration:

```text
backend/php/database/migrations/018_referral_system.sql
```

2. Login as Student.
3. Open:

```text
/student/referrals
```

4. Copy referral link.
5. Open public site with:

```text
?ref=CODE
```

6. During checkout, attach the referral code to purchase using:

```php
referral_attach_to_purchase($purchaseId, $referralCode);
```

7. Mark payment paid/verified.
8. Qualify referral using Owner page or payment flow:

```text
/owner/referrals
```

9. Confirm referral appears with:

```text
reward_pending
```

10. Apply reward.
11. Confirm status becomes:

```text
reward_applied
```

12. Confirm audit log contains:

```text
referral_reward_applied
```

13. Open:

```text
/owner/settings/referrals
```

14. Change reward settings.
15. Confirm future referrals use new settings.

---

## Stop Point / نقطة التوقف

Stop here. Test this phase before continuing.

توقف هنا. اختبر هذه المرحلة قبل الانتقال للمرحلة التالية.
