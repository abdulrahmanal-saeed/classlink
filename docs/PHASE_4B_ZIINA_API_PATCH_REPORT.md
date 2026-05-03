# Phase 4B Ziina API Patch Report
# تقرير Patch ربط Ziina API

## What was built / ما الذي تم بناؤه

This patch connects Phase 4 checkout to Ziina Payment Intent API without using webhooks.

هذا التعديل يربط checkout في Phase 4 مع Ziina Payment Intent API بدون استخدام webhooks.

---

## Important Security Note / ملاحظة أمان مهمة

A real payment API secret was found in `.env.example` and was removed immediately.

تم العثور على قيمة سرية حقيقية خاصة بالدفع داخل `.env.example` وتم حذفها فورًا.

Because the repository is public and the value was committed before, treat it as exposed.

لأن الريبو Public والقيمة تم رفعها سابقًا، يجب اعتبارها مكشوفة.

Required action:

```text
Revoke or rotate the old Ziina API secret from the Ziina dashboard immediately.
Create a new value and keep it only in the server .env file.
Never commit real secrets to GitHub.
```

---

## Files Created / الملفات التي تم إنشاؤها

```text
backend/php/database/migrations/004b_ziina_payment_intent_support.sql
backend/php/shared/ZiinaClient.php
docs/PHASE_4B_ZIINA_API_PATCH_REPORT.md
```

---

## Files Changed / الملفات التي تم تعديلها

```text
backend/php/shared/CheckoutFlow.php
web/public/checkout/index.php
web/public/thank-you/index.php
.env.example
```

---

## Database Changes / تغييرات قاعدة البيانات

Added to `payment_records`:

```text
provider_payment_intent_id
provider_status
provider_payload
```

These fields store Ziina payment intent information for verification and review.

---

## Environment Variables / متغيرات البيئة

Real values must exist only in server `.env`.

القيم الحقيقية يجب أن تكون في ملف `.env` على السيرفر فقط.

Required names:

```text
ZIINA_API_BASE
ZIINA_API_TOKEN
ZIINA_TEST_MODE
APP_URL
```

Do not put real values in `.env.example` or GitHub.

---

## Checkout Flow / تدفق checkout

When customer submits checkout:

1. System creates `purchase` with `pending` status.
2. System creates `payment_record` with `pending` status.
3. If Ziina API is configured, system creates Ziina Payment Intent.
4. System saves Ziina payment intent id and provider status.
5. Customer is redirected to Ziina hosted payment page.
6. If Ziina API is not configured, system falls back to safe pending/manual review flow.

---

## Thank-you Verification / التحقق في صفحة الشكر

When customer returns to:

```text
/thank-you?ref=...&intent_id=...
```

The system:

1. Fetches Ziina Payment Intent status from the API.
2. If status is `completed`, purchase becomes `paid` and payment record becomes `verified`.
3. If status is `failed` or `canceled`, purchase becomes `failed`.
4. Any other status becomes or remains `pending_verification`.
5. Every important status change is written to `audit_logs`.

---

## Payment Safety Rules / قواعد أمان الدفع

- Reaching thank-you does not automatically mean paid.
- Only Ziina API status `completed` can auto-mark as paid.
- Without valid API verification, status remains `pending_verification`.
- Owner can still manually review from `/owner/payments`.
- No card data is collected or stored.

---

## Manual Test Checklist / قائمة الاختبار اليدوي

1. Rotate or revoke the exposed old value from Ziina.
2. Put the new value only in server `.env`.
3. Apply migration:

```text
backend/php/database/migrations/004b_ziina_payment_intent_support.sql
```

4. Confirm `.env` has the required Ziina names.
5. Start server:

```bash
php -S localhost:8000 -t web/public
```

6. Open checkout:

```text
/checkout?plan=single
```

7. Submit valid checkout.
8. Confirm customer redirects to Ziina hosted payment page.
9. Complete test payment.
10. Confirm return to thank-you page.
11. Confirm status becomes `paid` only if Ziina returns `completed`.
12. Open Owner payments:

```text
/owner/payments
```

13. Confirm payment record includes Ziina intent id/provider status.
14. Open audit log:

```text
/owner/audit-log
```

15. Confirm Ziina status check events are recorded.

---

## Known Limitations / القيود الحالية

- No webhook support yet.
- Status is verified when the customer reaches thank-you.
- If the user closes the browser before returning to thank-you, payment may remain pending until Owner manual review.
- A scheduled verifier can be added later to re-check pending Ziina intents.
- Refund API is not implemented yet.

---

## Stop Point / نقطة التوقف

Stop here. Test this patch before continuing.

توقف هنا. اختبر هذا التعديل قبل الانتقال للمرحلة التالية.
