<?php
/**
 * Onboarding helper for Phase 5.
 *
 * This file centralizes student form saving, status updates, and email fallback
 * logging so the public form and Owner pipeline remain consistent.
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/AuditLogger.php';
require_once __DIR__ . '/CheckoutFlow.php';

function onboarding_clean_reference(string $reference): string
{
    return preg_replace('/[^A-Z0-9\-]/', '', strtoupper($reference));
}

function onboarding_find_checkout(string $reference): ?array
{
    return checkout_find_purchase_by_reference(onboarding_clean_reference($reference));
}

function onboarding_normalize_learner_type(string $checkoutType, string $someoneElseType = ''): string
{
    if ($checkoutType === 'someone_else') {
        return $someoneElseType === 'child' ? 'someone_else_child' : 'someone_else_adult';
    }

    return $checkoutType === 'child' ? 'child' : 'adult';
}

function onboarding_required_keys_for_type(string $learnerType): array
{
    $common = ['full_name', 'email', 'whatsapp', 'who_is_learning', 'purchased_plan'];

    if (str_contains($learnerType, 'child')) {
        return array_merge($common, [
            'parent_name',
            'child_name',
            'child_age',
            'child_native_language',
            'child_speaks_arabic',
            'child_can_read_arabic',
            'child_can_write_arabic',
            'child_learning_goal',
            'studied_arabic_before',
            'struggles',
            'learning_style_notes',
            'scheduling_preferences',
            'notes_for_tutor',
        ]);
    }

    return array_merge($common, [
        'age',
        'native_language',
        'current_arabic_level',
        'can_read_arabic',
        'can_write_arabic',
        'main_goal',
        'learning_reason',
        'use_context',
        'preferred_arabic_type',
        'biggest_difficulty',
        'difficulty_details',
        'scheduling_preferences',
        'notes_for_tutor',
    ]);
}

function onboarding_validate_payload(array $payload, string $learnerType): array
{
    $errors = [];

    foreach (onboarding_required_keys_for_type($learnerType) as $key) {
        if (!isset($payload[$key]) || trim((string) $payload[$key]) === '') {
            $errors[] = $key . ' is required.';
        }
    }

    if (!filter_var($payload['email'] ?? '', FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email is required.';
    }

    return $errors;
}

function onboarding_save_form(array $purchase, string $learnerType, array $payload): int
{
    $pdo = db();
    $pdo->beginTransaction();

    try {
        $statement = $pdo->prepare(
            'INSERT INTO student_intake_forms
              (purchase_id, checkout_reference, student_user_id, learner_name, age, country, main_goal, preferred_arabic_type, status, raw_payload, learner_type, submitted_at, owner_review_status)
             VALUES
              (:purchase_id, :checkout_reference, NULL, :learner_name, :age, :country, :main_goal, :preferred_arabic_type, "submitted", :raw_payload, :learner_type, NOW(), "pending_review")'
        );

        $learnerName = str_contains($learnerType, 'child') ? ($payload['child_name'] ?? $payload['full_name']) : $payload['full_name'];
        $age = str_contains($learnerType, 'child') ? (int) ($payload['child_age'] ?? 0) : (int) ($payload['age'] ?? 0);

        $statement->execute([
            ':purchase_id' => (int) $purchase['id'],
            ':checkout_reference' => $purchase['checkout_reference'],
            ':learner_name' => $learnerName,
            ':age' => $age ?: null,
            ':country' => $payload['country'] ?? null,
            ':main_goal' => $payload['main_goal'] ?? $payload['child_learning_goal'] ?? null,
            ':preferred_arabic_type' => $payload['preferred_arabic_type'] ?? null,
            ':raw_payload' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ':learner_type' => $learnerType,
        ]);

        $formId = (int) $pdo->lastInsertId();

        $pdo->prepare(
            'UPDATE purchases
             SET student_form_status = "submitted", owner_review_status = "pending_review"
             WHERE id = :id'
        )->execute([':id' => (int) $purchase['id']]);

        onboarding_log_email_fallback((int) $purchase['id'], $purchase['checkout_reference'], $payload['email'], 'Student form submitted', 'Student form submitted. Email provider is not configured yet.');

        audit_log(null, 'student_form_submitted', 'student_intake_form', (string) $formId, [
            'purchase_id' => (int) $purchase['id'],
            'checkout_reference' => $purchase['checkout_reference'],
            'learner_type' => $learnerType,
        ]);

        $pdo->commit();
        return $formId;
    } catch (Throwable $exception) {
        $pdo->rollBack();
        throw $exception;
    }
}

function onboarding_log_email_fallback(int $purchaseId, string $reference, string $email, string $subject, string $body): void
{
    $statement = db()->prepare(
        'INSERT INTO email_fallback_logs (purchase_id, checkout_reference, recipient_email, subject, body)
         VALUES (:purchase_id, :checkout_reference, :recipient_email, :subject, :body)'
    );

    $statement->execute([
        ':purchase_id' => $purchaseId,
        ':checkout_reference' => $reference,
        ':recipient_email' => $email,
        ':subject' => $subject,
        ':body' => $body,
    ]);
}

function onboarding_latest_forms(): array
{
    $statement = db()->query(
        'SELECT student_intake_forms.*, purchases.full_name AS checkout_name, purchases.email AS checkout_email, purchases.status AS purchase_status,
                purchases.student_form_status, purchases.level_check_status, purchases.schedule_status, purchases.owner_review_status AS purchase_review_status,
                plans.name_en AS plan_name
         FROM student_intake_forms
         LEFT JOIN purchases ON purchases.id = student_intake_forms.purchase_id
         LEFT JOIN plans ON plans.id = purchases.plan_id
         ORDER BY student_intake_forms.submitted_at DESC, student_intake_forms.id DESC
         LIMIT 150'
    );

    return $statement->fetchAll();
}

function onboarding_form_detail(int $id): ?array
{
    $statement = db()->prepare(
        'SELECT student_intake_forms.*, purchases.full_name AS checkout_name, purchases.email AS checkout_email, purchases.whatsapp,
                purchases.status AS purchase_status, purchases.student_form_status, purchases.level_check_status, purchases.schedule_status,
                purchases.owner_review_status AS purchase_review_status, plans.name_en AS plan_name
         FROM student_intake_forms
         LEFT JOIN purchases ON purchases.id = student_intake_forms.purchase_id
         LEFT JOIN plans ON plans.id = purchases.plan_id
         WHERE student_intake_forms.id = :id
         LIMIT 1'
    );
    $statement->execute([':id' => $id]);
    $form = $statement->fetch();

    return $form ?: null;
}

function onboarding_owner_update_review(int $formId, string $status, string $note, int $ownerUserId): void
{
    if (!in_array($status, ['pending_review', 'approved', 'rejected'], true)) {
        throw new InvalidArgumentException('Invalid review status.');
    }

    $form = onboarding_form_detail($formId);

    if (!$form) {
        throw new RuntimeException('Onboarding form not found.');
    }

    db()->prepare(
        'UPDATE student_intake_forms SET owner_review_status = :status, owner_review_note = :note WHERE id = :id'
    )->execute([
        ':status' => $status,
        ':note' => $note ?: null,
        ':id' => $formId,
    ]);

    if (!empty($form['purchase_id'])) {
        db()->prepare('UPDATE purchases SET owner_review_status = :status WHERE id = :purchase_id')
            ->execute([':status' => $status, ':purchase_id' => (int) $form['purchase_id']]);
    }

    audit_log($ownerUserId, 'onboarding_review_updated', 'student_intake_form', (string) $formId, [
        'status' => $status,
        'note' => $note,
    ]);
}
