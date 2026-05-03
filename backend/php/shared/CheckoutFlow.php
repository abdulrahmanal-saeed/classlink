<?php
/**
 * Checkout flow helper for Phase 4.
 *
 * This helper creates checkout references, stores checkout orders, and lets the
 * Owner update order status manually. Reaching thank-you never means paid.
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/AuditLogger.php';
require_once __DIR__ . '/Settings.php';

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
            ':provider' => setting_get('payment.provider', 'manual_or_ziina'),
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
        'SELECT purchases.*, plans.name_en AS plan_name, plans.description_en AS plan_description
         FROM purchases
         LEFT JOIN plans ON plans.id = purchases.plan_id
         WHERE purchases.checkout_reference = :reference
         LIMIT 1'
    );
    $statement->execute([':reference' => $reference]);
    $purchase = $statement->fetch();

    return $purchase ?: null;
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
