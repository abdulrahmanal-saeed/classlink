<?php
/**
 * Checkout flow helper for Phase 4 and 4B.
 *
 * This helper creates checkout references, stores checkout orders, integrates
 * with Ziina Payment Intent API when configured, and keeps manual Owner review.
 * Reaching thank-you never means paid unless Ziina status verifies completed.
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/AuditLogger.php';
require_once __DIR__ . '/Settings.php';
require_once __DIR__ . '/ZiinaClient.php';

function checkout_plan_map(): array
{
    return [
        'single' => 'Single Session',
        'monthly' => 'Monthly Plan',
        'bundle' => '30-Hour Bundle',
    ];
}

function checkout_find_plan(string $planSlug): ?array
{
    $planName = checkout_plan_map()[$planSlug] ?? null;

    if (!$planName) {
        return null;
    }

    $statement = db()->prepare('SELECT * FROM plans WHERE name_en = :name AND is_active = 1 ORDER BY id DESC LIMIT 1');
    $statement->execute([':name' => $planName]);
    $plan = $statement->fetch();

    return $plan ?: null;
}

function checkout_reference(): string
{
    return 'HN-' . strtoupper(bin2hex(random_bytes(8)));
}

function checkout_create_purchase(array $data, array $plan): array
{
    $reference = checkout_reference();
    $pdo = db();
    $pdo->beginTransaction();

    try {
        $purchase = $pdo->prepare(
            'INSERT INTO purchases
              (checkout_reference, user_id, plan_id, status, amount, currency, source, full_name, email, whatsapp, student_age, learner_type, main_goal, preferred_contact_method, policy_agreed_at)
             VALUES
              (:checkout_reference, NULL, :plan_id, "pending", :amount, :currency, "public_checkout", :full_name, :email, :whatsapp, :student_age, :learner_type, :main_goal, :preferred_contact_method, NOW())'
        );

        $purchase->execute([
            ':checkout_reference' => $reference,
            ':plan_id' => (int) $plan['id'],
            ':amount' => $plan['price_amount'],
            ':currency' => $plan['currency'],
            ':full_name' => $data['full_name'],
            ':email' => $data['email'],
            ':whatsapp' => $data['whatsapp'],
            ':student_age' => $data['student_age'] ?: null,
            ':learner_type' => $data['learner_type'],
            ':main_goal' => $data['main_goal'],
            ':preferred_contact_method' => $data['preferred_contact_method'],
        ]);

        $purchaseId = (int) $pdo->lastInsertId();

        $payment = $pdo->prepare(
            'INSERT INTO payment_records (checkout_reference, purchase_id, provider, status, amount, currency, notes)
             VALUES (:checkout_reference, :purchase_id, :provider, "pending", :amount, :currency, :notes)'
        );

        $payment->execute([
            ':checkout_reference' => $reference,
            ':purchase_id' => $purchaseId,
            ':provider' => ziina_is_configured() ? 'ziina' : 'manual_or_ziina',
            ':amount' => $plan['price_amount'],
            ':currency' => $plan['currency'],
            ':notes' => 'Created from checkout. Still waiting for verification.',
        ]);

        audit_log(null, 'checkout_created', 'purchase', (string) $purchaseId, [
            'checkout_reference' => $reference,
            'plan' => $plan['name_en'],
            'status' => 'pending',
        ]);

        $pdo->commit();

        return ['purchase_id' => $purchaseId, 'reference' => $reference];
    } catch (Throwable $exception) {
        $pdo->rollBack();
        throw $exception;
    }
}

function checkout_find_purchase_by_reference(string $reference): ?array
{
    $statement = db()->prepare(
        'SELECT purchases.*, plans.name_en AS plan_name, plans.description_en AS plan_description,
                payment_records.provider_payment_intent_id, payment_records.provider_status, payment_records.provider_payload
         FROM purchases
         LEFT JOIN plans ON plans.id = purchases.plan_id
         LEFT JOIN payment_records ON payment_records.purchase_id = purchases.id
         WHERE purchases.checkout_reference = :reference
         LIMIT 1'
    );
    $statement->execute([':reference' => $reference]);
    $purchase = $statement->fetch();

    return $purchase ?: null;
}

function checkout_public_url(string $path): string
{
    $appUrl = rtrim((string) (getenv('APP_URL') ?: ''), '/');

    if ($appUrl !== '') {
        return $appUrl . '/' . ltrim($path, '/');
    }

    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    $scheme = $isHttps ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';

    return $scheme . '://' . $host . '/' . ltrim($path, '/');
}

function checkout_create_ziina_intent(string $reference): ?array
{
    if (!ziina_is_configured()) {
        return null;
    }

    $purchase = checkout_find_purchase_by_reference($reference);

    if (!$purchase) {
        throw new RuntimeException('Purchase not found for Ziina payment intent.');
    }

    $successUrl = checkout_public_url('/thank-you?ref=' . urlencode($reference) . '&intent_id={PAYMENT_INTENT_ID}');
    $cancelUrl = checkout_public_url('/checkout?plan=' . urlencode(array_search($purchase['plan_name'], checkout_plan_map(), true) ?: 'single') . '&cancelled=1');

    $response = ziina_create_payment_intent($purchase, $successUrl, $cancelUrl);
    $intentId = ziina_extract_intent_id($response);
    $redirectUrl = ziina_extract_redirect_url($response);
    $providerStatus = ziina_extract_status($response);

    if (!$intentId || !$redirectUrl) {
        throw new RuntimeException('Ziina response did not include payment intent id or redirect URL.');
    }

    db()->prepare(
        'UPDATE payment_records
         SET provider = "ziina", provider_payment_intent_id = :intent_id, provider_status = :provider_status, provider_payload = :payload
         WHERE checkout_reference = :reference'
    )->execute([
        ':intent_id' => $intentId,
        ':provider_status' => $providerStatus,
        ':payload' => json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ':reference' => $reference,
    ]);

    audit_log(null, 'ziina_intent_created', 'purchase', (string) $purchase['id'], [
        'checkout_reference' => $reference,
        'intent_id' => $intentId,
        'provider_status' => $providerStatus,
    ]);

    return [
        'intent_id' => $intentId,
        'redirect_url' => $redirectUrl,
        'provider_status' => $providerStatus,
    ];
}

function checkout_mark_pending_verification(string $reference): void
{
    $purchase = checkout_find_purchase_by_reference($reference);

    if (!$purchase || $purchase['status'] !== 'pending') {
        return;
    }

    db()->prepare('UPDATE purchases SET status = "pending_verification" WHERE checkout_reference = :reference AND status = "pending"')
        ->execute([':reference' => $reference]);

    db()->prepare('UPDATE payment_records SET status = "pending_verification", notes = :notes WHERE checkout_reference = :reference AND status = "pending"')
        ->execute([
            ':reference' => $reference,
            ':notes' => 'Thank-you reached. Still requires verification.',
        ]);

    audit_log(null, 'payment_pending_verification', 'purchase', (string) $purchase['id'], [
        'checkout_reference' => $reference,
    ]);
}

function checkout_verify_ziina_status(string $reference, ?string $intentId = null): ?string
{
    $purchase = checkout_find_purchase_by_reference($reference);

    if (!$purchase) {
        return null;
    }

    $intentId = $intentId ?: ($purchase['provider_payment_intent_id'] ?? null);

    if (!$intentId || !ziina_is_configured()) {
        checkout_mark_pending_verification($reference);
        return $purchase['status'];
    }

    try {
        $response = ziina_get_payment_intent($intentId);
        $providerStatus = ziina_extract_status($response);

        db()->prepare(
            'UPDATE payment_records SET provider_status = :provider_status, provider_payload = :payload WHERE checkout_reference = :reference'
        )->execute([
            ':provider_status' => $providerStatus,
            ':payload' => json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ':reference' => $reference,
        ]);

        if ($providerStatus === 'completed') {
            db()->prepare('UPDATE purchases SET status = "paid" WHERE checkout_reference = :reference')->execute([':reference' => $reference]);
            db()->prepare('UPDATE payment_records SET status = "verified", verified_at = NOW(), notes = :notes WHERE checkout_reference = :reference')
                ->execute([':reference' => $reference, ':notes' => 'Verified through Ziina Payment Intent API.']);
            audit_log(null, 'payment_verified_by_ziina_api', 'purchase', (string) $purchase['id'], [
                'checkout_reference' => $reference,
                'intent_id' => $intentId,
                'provider_status' => $providerStatus,
            ]);
            return 'paid';
        }

        if (in_array($providerStatus, ['failed', 'canceled'], true)) {
            db()->prepare('UPDATE purchases SET status = "failed" WHERE checkout_reference = :reference')->execute([':reference' => $reference]);
            db()->prepare('UPDATE payment_records SET status = "failed", notes = :notes WHERE checkout_reference = :reference')
                ->execute([':reference' => $reference, ':notes' => 'Ziina returned non-completed status: ' . $providerStatus]);
            audit_log(null, 'payment_failed_by_ziina_api', 'purchase', (string) $purchase['id'], [
                'checkout_reference' => $reference,
                'intent_id' => $intentId,
                'provider_status' => $providerStatus,
            ]);
            return 'failed';
        }

        checkout_mark_pending_verification($reference);
        return 'pending_verification';
    } catch (Throwable $exception) {
        checkout_mark_pending_verification($reference);
        audit_log(null, 'ziina_status_check_failed', 'purchase', (string) $purchase['id'], [
            'checkout_reference' => $reference,
            'intent_id' => $intentId,
            'error' => $exception->getMessage(),
        ]);
        return 'pending_verification';
    }
}

function owner_update_purchase_status(int $purchaseId, string $status, int $ownerUserId, string $note = ''): void
{
    $allowed = ['pending', 'pending_verification', 'paid', 'failed', 'refunded', 'cancelled'];

    if (!in_array($status, $allowed, true)) {
        throw new InvalidArgumentException('Invalid status.');
    }

    $pdo = db();
    $lookup = $pdo->prepare('SELECT * FROM purchases WHERE id = :id LIMIT 1');
    $lookup->execute([':id' => $purchaseId]);
    $purchase = $lookup->fetch();

    if (!$purchase) {
        throw new RuntimeException('Purchase not found.');
    }

    $oldStatus = $purchase['status'];

    $pdo->prepare('UPDATE purchases SET status = :status WHERE id = :id')->execute([
        ':status' => $status,
        ':id' => $purchaseId,
    ]);

    $recordStatus = match ($status) {
        'paid' => 'manual_approved',
        'refunded' => 'refunded',
        'failed' => 'failed',
        'pending_verification' => 'pending_verification',
        default => 'pending',
    };

    $pdo->prepare('UPDATE payment_records SET status = :status, verified_by_user_id = :owner_id, verified_at = NOW(), manual_status_note = :note WHERE purchase_id = :purchase_id')
        ->execute([
            ':status' => $recordStatus,
            ':owner_id' => $ownerUserId,
            ':note' => $note ?: null,
            ':purchase_id' => $purchaseId,
        ]);

    audit_log($ownerUserId, 'purchase_status_updated', 'purchase', (string) $purchaseId, [
        'old_status' => $oldStatus,
        'new_status' => $status,
        'note' => $note,
    ]);
}
