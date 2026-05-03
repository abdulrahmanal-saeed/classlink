# Phase 4 Execution Report
# تقرير تنفيذ المرحلة 4

## Phase Name / اسم المرحلة

Checkout, Policy Agreement, Payment Status, and Thank You Page

صفحة الدفع، الموافقة على السياسات، حالة الدفع، وصفحة الشكر

---

## What was built / ما الذي تم بناؤه

Phase 4 created the pre-payment checkout flow, checkout reference creation, thank-you page, payment status rules, and Owner payment review screens.

قامت المرحلة 4 ببناء تدفق checkout قبل الدفع، وإنشاء checkout reference آمن، وصفحة الشكر، وقواعد حالة الدفع، وصفحات مراجعة المدفوعات للمالك.

---

## Files Created / الملفات التي تم إنشاؤها

### Database / قاعدة البيانات

```text
backend/php/database/migrations/004_checkout_payment_foundation.sql
```

### Backend helpers / أدوات الباك إند

```text
backend/php/shared/CheckoutFlow.php
```

### Public pages / الصفحات العامة

```text
web/public/thank-you/index.php
```

### Owner payment pages / صفحات مراجعة المدفوعات

```text
web/public/owner/payments/index.php
web/public/owner/payments/view/index.php
```

---

## Files Changed / الملفات التي تم تعديلها

```text
web/public/checkout/index.php
web/components/layout/dashboard_shell.php
web/public/.htaccess
```

Changes:

- Checkout placeholder was replaced with a real checkout form.
- Owner sidebar now includes Payments.
- `/owner/payments/{id}` clean route was added.

---

## Database Changes / تغييرات قاعدة البيانات

Migration 004 updates:

```text
purchases
payment_records
```

Added checkout/order fields:

```text
checkout_reference
full_name
email
whatsapp
student_age
learner_type
main_goal
preferred_contact_method
policy_agreed_at
payment_redirect_url
```

Updated status support:

```text
pending
pending_verification
paid
failed
cancelled
refunded
```

Payment records support:

```text
checkout_reference
manual_status_note
pending_verification
manual_approved
```

---

## Public Routes / المسارات العامة

Implemented:

```text
/checkout?plan=single
/checkout?plan=monthly
/checkout?plan=bundle
/thank-you?ref={checkoutReference}
```

Checkout fields:

```text
full_name
email
whatsapp
selected_plan
student_age
learner_type
main_goal
preferred_contact_method
policy agreement checkbox
```

Policy text:

```text
I agree to the Terms of Service, Refund Policy, Cancellation Policy, and Privacy Policy.
```

---

## Payment Rules / قواعد الدفع

Implemented rules:

- Checkout creates purchase with `pending` status.
- Checkout creates payment record with `pending` status.
- Checkout creates secure reference like `HN-XXXXXXXXXXXXXXX`.
- If `payment.ziina_link` is configured in settings, checkout redirects to it with `?ref=`.
- If payment link is missing, checkout redirects to thank-you with setup message.
- Thank-you page does not mark payment as paid.
- If no webhook/API verification exists, thank-you changes `pending` to `pending_verification`.
- Owner can manually mark purchase as `pending`, `pending_verification`, `paid`, `failed`, `refunded`, or `cancelled`.
- Every Owner status update is written to `audit_logs`.

---

## Owner Pages / صفحات المالك

Implemented:

```text
/owner/payments
/owner/payments/view?id=...
/owner/payments/{id}
```

Owner can:

- View checkout orders.
- Filter by payment status.
- Open payment detail page.
- Review customer/plan/payment details.
- Manually update payment status.
- Add a review note.

---

## Thank You Page / صفحة الشكر

Implemented content:

- Thank you for your payment!
- Your Arabic learning journey has started 🎉
- Payment may be pending verification.
- Next steps:
  1. Complete student form
  2. Complete level check if required
  3. Choose lesson time
  4. Tutor prepares personalized first lesson
- Button: Complete Student Form
- Button: Choose Lesson Time disabled until form and level check are complete
- Optional welcome video placeholder

---

## Security Checks / فحوصات الأمان

- Policy agreement checkbox is required.
- Checkout validates email and required fields server-side.
- Checkout reference is generated server-side using random bytes.
- Payment is not marked paid automatically.
- Thank-you page only moves status to `pending_verification`.
- Owner payment review pages are protected by `owner_teacher` role.
- Manual status updates are written to audit log.
- No card data is collected or stored.

---

## Known Limitations / القيود الحالية

- No real Ziina/API/webhook verification yet.
- `payment.ziina_link` must be configured manually in settings if redirection is needed.
- Checkout UI is English-first; Arabic-ready structure can be expanded later.
- Student form page is not implemented yet; the thank-you button points to a future `/student-form?ref=...` route.
- Booking/lesson time selection is intentionally disabled until onboarding and level check are built.
- CSRF protection still needs to be strengthened before production.
- Migration uses `ADD COLUMN IF NOT EXISTS` and `CREATE INDEX IF NOT EXISTS`; if the target MySQL/MariaDB version does not support that syntax, apply adjusted SQL manually.

---

## Manual Test Checklist / قائمة الاختبار اليدوي

1. Apply Phase 4 migration:

```text
backend/php/database/migrations/004_checkout_payment_foundation.sql
```

2. Start local server:

```bash
php -S localhost:8000 -t web/public
```

3. Open checkout for each plan:

```text
/checkout?plan=single
/checkout?plan=monthly
/checkout?plan=bundle
```

4. Submit checkout without policy checkbox and confirm it is blocked.

5. Submit valid checkout.

6. Confirm a record is created in:

```text
purchases
payment_records
```

7. Open thank-you page from redirect:

```text
/thank-you?ref={checkoutReference}
```

8. Confirm purchase status becomes:

```text
pending_verification
```

9. Login as Owner:

```text
owner@demo.com
Password: demo password
```

10. Open payments list:

```text
/owner/payments
```

11. Open payment detail:

```text
/owner/payments/view?id=1
```

or clean route if Apache rewrite is enabled:

```text
/owner/payments/1
```

12. Mark payment as paid manually.

13. Confirm status changes in database.

14. Open audit log:

```text
/owner/audit-log
```

15. Confirm status change was recorded.

---

## Stop Point / نقطة التوقف

Stop here. Test this phase before continuing.

توقف هنا. اختبر هذه المرحلة قبل الانتقال للمرحلة التالية.
