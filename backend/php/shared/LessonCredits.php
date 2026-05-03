<?php
/**
 * Phase 8 Lesson Credits helper.
 *
 * Centralizes package balance, credit ledger, attendance status, cancellation
 * rules, and Owner manual adjustments.
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/AuditLogger.php';

function credits_setting(string $key, $default = null)
{
    $statement = db()->prepare('SELECT setting_value, value_type FROM settings WHERE setting_key = :key LIMIT 1');
    $statement->execute([':key' => $key]);
    $row = $statement->fetch();
    if (!$row) return $default;

    return match ($row['value_type']) {
        'boolean' => in_array(strtolower((string) $row['setting_value']), ['1', 'true', 'yes', 'on'], true),
        'number' => (float) $row['setting_value'],
        'json' => json_decode($row['setting_value'] ?: 'null', true),
        default => $row['setting_value'],
    };
}

function credits_student_packages(int $studentUserId): array
{
    $statement = db()->prepare(
        'SELECT lesson_packages.*, purchases.status AS purchase_status, plans.name_en AS plan_name
         FROM lesson_packages
         LEFT JOIN purchases ON purchases.id = lesson_packages.purchase_id
         LEFT JOIN plans ON plans.id = purchases.plan_id
         WHERE lesson_packages.student_user_id = :student_id
         ORDER BY lesson_packages.status = "active" DESC, lesson_packages.created_at DESC'
    );
    $statement->execute([':student_id' => $studentUserId]);
    return $statement->fetchAll();
}

function credits_active_package(int $studentUserId): ?array
{
    $statement = db()->prepare(
        'SELECT * FROM lesson_packages
         WHERE student_user_id = :student_id AND status = "active"
         ORDER BY remaining_credits DESC, created_at ASC
         LIMIT 1'
    );
    $statement->execute([':student_id' => $studentUserId]);
    $package = $statement->fetch();
    return $package ?: null;
}

function credits_transactions_for_student(int $studentUserId): array
{
    $statement = db()->prepare(
        'SELECT lesson_credit_transactions.*, lesson_packages.package_name, lesson_sessions.title AS session_title, lesson_sessions.start_at
         FROM lesson_credit_transactions
         LEFT JOIN lesson_packages ON lesson_packages.id = lesson_credit_transactions.package_id
         LEFT JOIN lesson_sessions ON lesson_sessions.id = lesson_credit_transactions.session_id
         WHERE lesson_credit_transactions.student_user_id = :student_id
         ORDER BY lesson_credit_transactions.created_at DESC, lesson_credit_transactions.id DESC
         LIMIT 300'
    );
    $statement->execute([':student_id' => $studentUserId]);
    return $statement->fetchAll();
}

function credits_all_packages(): array
{
    return db()->query(
        'SELECT lesson_packages.*, users.display_name AS student_name, users.email AS student_email, purchases.status AS purchase_status
         FROM lesson_packages
         INNER JOIN users ON users.id = lesson_packages.student_user_id
         LEFT JOIN purchases ON purchases.id = lesson_packages.purchase_id
         ORDER BY lesson_packages.status = "active" DESC, lesson_packages.updated_at DESC
         LIMIT 500'
    )->fetchAll();
}

function credits_package_by_id(int $packageId): ?array
{
    $statement = db()->prepare('SELECT * FROM lesson_packages WHERE id = :id LIMIT 1');
    $statement->execute([':id' => $packageId]);
    $package = $statement->fetch();
    return $package ?: null;
}

function credits_add_transaction(int $packageId, int $studentUserId, string $type, float $credits, string $reason, ?int $sessionId, ?int $ownerUserId, array $metadata = []): int
{
    $package = credits_package_by_id($packageId);
    if (!$package) {
        throw new RuntimeException('Lesson package not found.');
    }

    $current = (float) $package['remaining_credits'];
    $newBalance = $current;

    if (in_array($type, ['purchase_grant', 'cancellation_return'], true)) {
        $newBalance += abs($credits);
    } elseif (in_array($type, ['session_deducted', 'refund_adjustment'], true)) {
        $newBalance -= abs($credits);
    } elseif ($type === 'manual_adjustment') {
        $newBalance += $credits;
    } else {
        throw new InvalidArgumentException('Invalid credit transaction type.');
    }

    if ($newBalance < 0) {
        $newBalance = 0;
    }

    db()->prepare(
        'UPDATE lesson_packages SET remaining_credits = :remaining WHERE id = :id'
    )->execute([':remaining' => $newBalance, ':id' => $packageId]);

    db()->prepare(
        'INSERT INTO lesson_credit_transactions
          (package_id, student_user_id, session_id, transaction_type, credits, balance_after, reason, metadata, created_by_user_id)
         VALUES
          (:package_id, :student_id, :session_id, :type, :credits, :balance_after, :reason, :metadata, :created_by)'
    )->execute([
        ':package_id' => $packageId,
        ':student_id' => $studentUserId,
        ':session_id' => $sessionId,
        ':type' => $type,
        ':credits' => $credits,
        ':balance_after' => $newBalance,
        ':reason' => $reason,
        ':metadata' => $metadata ? json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
        ':created_by' => $ownerUserId,
    ]);

    return (int) db()->lastInsertId();
}

function credits_manual_adjust(int $studentUserId, float $credits, string $reason, int $ownerUserId): int
{
    if (trim($reason) === '') {
        throw new RuntimeException('Manual adjustment reason is required.');
    }

    $package = credits_active_package($studentUserId);
    if (!$package) {
        throw new RuntimeException('No active package found for this student.');
    }

    $transactionId = credits_add_transaction((int) $package['id'], $studentUserId, 'manual_adjustment', $credits, $reason, null, $ownerUserId, [
        'source' => 'owner_manual_adjustment',
    ]);

    audit_log($ownerUserId, 'lesson_credit_manual_adjustment', 'lesson_package', (string) $package['id'], [
        'student_user_id' => $studentUserId,
        'credits' => $credits,
        'reason' => $reason,
        'transaction_id' => $transactionId,
    ]);

    return $transactionId;
}

function credits_sessions_for_student(int $studentUserId): array
{
    $statement = db()->prepare(
        'SELECT lesson_sessions.*, lesson_packages.package_name
         FROM lesson_sessions
         LEFT JOIN lesson_packages ON lesson_packages.id = lesson_sessions.package_id
         WHERE lesson_sessions.student_user_id = :student_id
         ORDER BY lesson_sessions.start_at DESC
         LIMIT 200'
    );
    $statement->execute([':student_id' => $studentUserId]);
    return $statement->fetchAll();
}

function credits_create_session(int $studentUserId, ?int $teacherUserId, string $title, string $startAt, string $endAt, ?int $packageId = null): int
{
    $packageId = $packageId ?: (credits_active_package($studentUserId)['id'] ?? null);
    db()->prepare(
        'INSERT INTO lesson_sessions (student_user_id, teacher_user_id, package_id, title, start_at, end_at, status)
         VALUES (:student_id, :teacher_id, :package_id, :title, :start_at, :end_at, "planned")'
    )->execute([
        ':student_id' => $studentUserId,
        ':teacher_id' => $teacherUserId,
        ':package_id' => $packageId,
        ':title' => $title,
        ':start_at' => $startAt,
        ':end_at' => $endAt,
    ]);
    return (int) db()->lastInsertId();
}

function credits_session_by_id(int $sessionId): ?array
{
    $statement = db()->prepare('SELECT * FROM lesson_sessions WHERE id = :id LIMIT 1');
    $statement->execute([':id' => $sessionId]);
    $session = $statement->fetch();
    return $session ?: null;
}

function credits_mark_attendance(int $sessionId, string $status, int $ownerUserId, string $notes = ''): void
{
    $allowed = ['planned','confirmed','completed','canceled_on_time','canceled_late','rescheduled','no_show'];
    if (!in_array($status, $allowed, true)) {
        throw new InvalidArgumentException('Invalid session status.');
    }

    $session = credits_session_by_id($sessionId);
    if (!$session) {
        throw new RuntimeException('Session not found.');
    }

    $packageId = $session['package_id'] ? (int) $session['package_id'] : null;
    $studentUserId = (int) $session['student_user_id'];
    $creditAction = 'none';
    $transactionId = null;

    $deduct = false;
    if ($status === 'completed') {
        $deduct = true;
    }
    if ($status === 'no_show' && credits_setting('credits.no_show_deducts_credit', true)) {
        $deduct = true;
    }
    if ($status === 'canceled_late' && credits_setting('credits.late_cancellation_deducts_credit', true)) {
        $deduct = true;
    }

    $pdo = db();
    $pdo->beginTransaction();

    try {
        if ($deduct && !$session['credit_deducted']) {
            if (!$packageId) {
                $active = credits_active_package($studentUserId);
                $packageId = $active ? (int) $active['id'] : null;
            }
            if (!$packageId) {
                throw new RuntimeException('No active package found for credit deduction.');
            }
            $transactionId = credits_add_transaction($packageId, $studentUserId, 'session_deducted', 1, 'Credit deducted for session status: ' . $status, $sessionId, $ownerUserId, [
                'session_status' => $status,
            ]);
            $creditAction = 'deducted';
        } elseif ($status === 'canceled_on_time') {
            $creditAction = $session['credit_deducted'] ? 'returned' : 'kept';
            if ($session['credit_deducted'] && $packageId) {
                $transactionId = credits_add_transaction($packageId, $studentUserId, 'cancellation_return', 1, 'Credit returned for on-time cancellation.', $sessionId, $ownerUserId, [
                    'session_status' => $status,
                ]);
            }
        }

        $pdo->prepare(
            'UPDATE lesson_sessions
             SET status = :status,
                 credit_deducted = :credit_deducted,
                 credit_transaction_id = COALESCE(:transaction_id, credit_transaction_id),
                 attendance_marked_by_user_id = :marked_by,
                 attendance_marked_at = NOW(),
                 notes = :notes
             WHERE id = :id'
        )->execute([
            ':status' => $status,
            ':credit_deducted' => ($deduct || ($session['credit_deducted'] && $status !== 'canceled_on_time')) ? 1 : 0,
            ':transaction_id' => $transactionId,
            ':marked_by' => $ownerUserId,
            ':notes' => $notes ?: $session['notes'],
            ':id' => $sessionId,
        ]);

        $pdo->prepare(
            'INSERT INTO attendance_records (session_id, student_user_id, package_id, status, credit_action, credit_transaction_id, marked_by_user_id, notes, marked_at)
             VALUES (:session_id, :student_id, :package_id, :status, :credit_action, :transaction_id, :marked_by, :notes, NOW())'
        )->execute([
            ':session_id' => $sessionId,
            ':student_id' => $studentUserId,
            ':package_id' => $packageId,
            ':status' => $status,
            ':credit_action' => $creditAction,
            ':transaction_id' => $transactionId,
            ':marked_by' => $ownerUserId,
            ':notes' => $notes ?: null,
        ]);

        audit_log($ownerUserId, 'lesson_attendance_marked', 'lesson_session', (string) $sessionId, [
            'status' => $status,
            'credit_action' => $creditAction,
            'transaction_id' => $transactionId,
        ]);

        $pdo->commit();
    } catch (Throwable $exception) {
        $pdo->rollBack();
        throw $exception;
    }
}

function credits_student_summary(int $studentUserId): array
{
    $packages = credits_student_packages($studentUserId);
    $total = 0.0;
    $remaining = 0.0;
    foreach ($packages as $package) {
        $total += (float) $package['total_credits'];
        if ($package['status'] === 'active') {
            $remaining += (float) $package['remaining_credits'];
        }
    }
    $used = max(0, $total - $remaining);
    return ['total' => $total, 'remaining' => $remaining, 'used' => $used, 'packages' => $packages];
}

function credits_parent_can_access_child(int $parentUserId, int $childUserId): bool
{
    $statement = db()->prepare('SELECT id FROM parent_child_links WHERE parent_user_id = :parent_id AND child_user_id = :child_id AND status = "active" LIMIT 1');
    $statement->execute([':parent_id' => $parentUserId, ':child_id' => $childUserId]);
    return (bool) $statement->fetch();
}
