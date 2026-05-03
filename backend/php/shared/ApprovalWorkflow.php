<?php
/**
 * Phase 7 approval workflow helper.
 *
 * Owner approval creates student/parent accounts only after payment, intake,
 * level check, and schedule review are acceptable. Login details are logged for
 * manual sending until real email/WhatsApp providers are configured.
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/AuditLogger.php';
require_once __DIR__ . '/Onboarding.php';

function approval_generate_password(): string
{
    return 'HN-' . bin2hex(random_bytes(4)) . '-' . random_int(100, 999);
}

function approval_safe_email(string $email, string $fallbackPrefix): string
{
    $email = strtolower(trim($email));
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return $email;
    }
    return $fallbackPrefix . '+' . bin2hex(random_bytes(4)) . '@mshabibanabil.local';
}

function approval_unique_email(string $email): string
{
    $base = strtolower(trim($email));
    $parts = explode('@', $base);
    $local = $parts[0] ?? 'user';
    $domain = $parts[1] ?? 'mshabibanabil.local';
    $candidate = $base;
    $i = 1;

    while (true) {
        $statement = db()->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
        $statement->execute([':email' => $candidate]);
        if (!$statement->fetch()) {
            return $candidate;
        }
        $candidate = $local . '+' . $i . '@' . $domain;
        $i++;
    }
}

function approval_create_user(string $email, string $name, string $role, string $temporaryPassword): int
{
    $statement = db()->prepare(
        'INSERT INTO users (email, password_hash, role, status, display_name)
         VALUES (:email, :password_hash, :role, "active", :display_name)'
    );
    $statement->execute([
        ':email' => $email,
        ':password_hash' => password_hash($temporaryPassword, PASSWORD_DEFAULT),
        ':role' => $role,
        ':display_name' => $name,
    ]);
    return (int) db()->lastInsertId();
}

function approval_payload(array $intake): array
{
    $payload = json_decode($intake['raw_payload'] ?? '{}', true);
    return is_array($payload) ? $payload : [];
}

function approval_is_child(array $intake): bool
{
    return str_contains((string) ($intake['learner_type'] ?? ''), 'child');
}

function approval_latest_level_for_intake(int $intakeId): ?array
{
    $statement = db()->prepare(
        'SELECT * FROM level_check_attempts WHERE intake_form_id = :intake_id ORDER BY reviewed_at DESC, submitted_at DESC, id DESC LIMIT 1'
    );
    $statement->execute([':intake_id' => $intakeId]);
    $row = $statement->fetch();
    return $row ?: null;
}

function approval_can_approve(array $intake): array
{
    $issues = [];
    $purchaseStatus = $intake['purchase_status'] ?? null;
    $formStatus = $intake['student_form_status'] ?? null;
    $levelStatus = $intake['level_check_status'] ?? null;
    $scheduleStatus = $intake['schedule_status'] ?? null;

    if (!in_array($purchaseStatus, ['paid'], true)) {
        $issues[] = 'Payment must be paid before approval.';
    }
    if ($formStatus !== 'submitted') {
        $issues[] = 'Student form must be submitted.';
    }
    if (!in_array($levelStatus, ['submitted', 'reviewed'], true)) {
        $issues[] = 'Level check must be submitted or reviewed.';
    }
    if (!in_array($scheduleStatus, ['requested', 'confirmed', 'not_selected'], true)) {
        $issues[] = 'Schedule status is invalid.';
    }

    return $issues;
}

function approval_create_lesson_package(int $studentUserId, ?int $purchaseId, ?int $planId, int $ownerUserId): ?int
{
    if (!$planId) {
        return null;
    }

    $planStatement = db()->prepare('SELECT * FROM plans WHERE id = :id LIMIT 1');
    $planStatement->execute([':id' => $planId]);
    $plan = $planStatement->fetch();

    if (!$plan) {
        return null;
    }

    $credits = (float) ($plan['included_sessions'] ?? 0);
    $packageName = $plan['name_en'] ?: 'Arabic Lesson Package';

    db()->prepare(
        'INSERT INTO lesson_packages (student_user_id, purchase_id, package_name, total_credits, remaining_credits, status)
         VALUES (:student_user_id, :purchase_id, :package_name, :total_credits, :remaining_credits, "active")'
    )->execute([
        ':student_user_id' => $studentUserId,
        ':purchase_id' => $purchaseId,
        ':package_name' => $packageName,
        ':total_credits' => $credits,
        ':remaining_credits' => $credits,
    ]);
    $packageId = (int) db()->lastInsertId();

    db()->prepare(
        'INSERT INTO lesson_credit_transactions (package_id, student_user_id, transaction_type, credits, reason, created_by_user_id)
         VALUES (:package_id, :student_user_id, "add", :credits, :reason, :created_by_user_id)'
    )->execute([
        ':package_id' => $packageId,
        ':student_user_id' => $studentUserId,
        ':credits' => $credits,
        ':reason' => 'Initial package created after Owner approval.',
        ':created_by_user_id' => $ownerUserId,
    ]);

    return $packageId;
}

function approval_log_login_details(int $userId, ?int $studentUserId, ?int $parentUserId, int $intakeId, ?int $purchaseId, string $recipient, string $subject, string $body, string $temporaryPassword, int $ownerUserId): void
{
    db()->prepare(
        'INSERT INTO login_detail_logs
          (user_id, related_student_user_id, related_parent_user_id, intake_form_id, purchase_id, delivery_channel, recipient, subject, message_body, temporary_password, status, created_by_user_id)
         VALUES
          (:user_id, :student_user_id, :parent_user_id, :intake_id, :purchase_id, "manual_log", :recipient, :subject, :body, :temporary_password, "logged", :created_by)'
    )->execute([
        ':user_id' => $userId,
        ':student_user_id' => $studentUserId,
        ':parent_user_id' => $parentUserId,
        ':intake_id' => $intakeId,
        ':purchase_id' => $purchaseId,
        ':recipient' => $recipient,
        ':subject' => $subject,
        ':body' => $body,
        ':temporary_password' => $temporaryPassword,
        ':created_by' => $ownerUserId,
    ]);
}

function approval_approve_intake(int $intakeId, int $ownerUserId, string $note = ''): array
{
    $intake = onboarding_form_detail($intakeId);

    if (!$intake) {
        throw new RuntimeException('Onboarding submission not found.');
    }

    $existing = db()->prepare('SELECT * FROM onboarding_account_links WHERE intake_form_id = :intake_id LIMIT 1');
    $existing->execute([':intake_id' => $intakeId]);
    $existingLink = $existing->fetch();
    if ($existingLink) {
        return [
            'student_user_id' => (int) $existingLink['student_user_id'],
            'parent_user_id' => $existingLink['parent_user_id'] ? (int) $existingLink['parent_user_id'] : null,
            'already_created' => true,
        ];
    }

    $issues = approval_can_approve($intake);
    if ($issues) {
        throw new RuntimeException(implode(' ', $issues));
    }

    $payload = approval_payload($intake);
    $isChild = approval_is_child($intake);
    $purchaseId = !empty($intake['purchase_id']) ? (int) $intake['purchase_id'] : null;
    $planId = null;

    if ($purchaseId) {
        $purchaseStatement = db()->prepare('SELECT plan_id FROM purchases WHERE id = :id LIMIT 1');
        $purchaseStatement->execute([':id' => $purchaseId]);
        $planId = (int) ($purchaseStatement->fetchColumn() ?: 0);
    }

    $pdo = db();
    $pdo->beginTransaction();

    try {
        $level = approval_latest_level_for_intake($intakeId);
        $finalLevel = $level['final_level'] ?? $level['suggested_level'] ?? null;
        $studentUserId = null;
        $parentUserId = null;
        $packageId = null;
        $created = [];

        if ($isChild) {
            $parentName = trim((string) ($payload['parent_name'] ?? $intake['checkout_name'] ?? 'Parent'));
            $childName = trim((string) ($payload['child_name'] ?? $intake['learner_name'] ?? 'Child learner'));
            $parentEmail = approval_unique_email(approval_safe_email((string) ($payload['email'] ?? $intake['checkout_email'] ?? ''), 'parent'));
            $childEmail = approval_unique_email('child+' . preg_replace('/[^a-z0-9]+/i', '-', strtolower($childName)) . '+' . bin2hex(random_bytes(3)) . '@mshabibanabil.local');
            $parentPassword = approval_generate_password();
            $childPassword = approval_generate_password();

            $parentUserId = approval_create_user($parentEmail, $parentName, 'parent', $parentPassword);
            $studentUserId = approval_create_user($childEmail, $childName, 'student', $childPassword);

            $pdo->prepare('INSERT INTO user_profiles (user_id, phone, country, preferred_language) VALUES (:user_id, :phone, :country, "en")')
                ->execute([':user_id' => $parentUserId, ':phone' => $intake['whatsapp'] ?? null, ':country' => $payload['country'] ?? null]);
            $pdo->prepare('INSERT INTO parent_profiles (user_id, phone, preferred_contact_method, notes) VALUES (:user_id, :phone, "whatsapp", :notes)')
                ->execute([':user_id' => $parentUserId, ':phone' => $intake['whatsapp'] ?? null, ':notes' => 'Created from onboarding approval.']);

            $pdo->prepare('INSERT INTO user_profiles (user_id, phone, country, preferred_language) VALUES (:user_id, NULL, :country, "en")')
                ->execute([':user_id' => $studentUserId, ':country' => $payload['country'] ?? null]);
            $pdo->prepare('INSERT INTO student_profiles (user_id, learner_type, current_level, learning_goal, preferred_dialect, notes) VALUES (:user_id, "child", :level, :goal, :dialect, :notes)')
                ->execute([
                    ':user_id' => $studentUserId,
                    ':level' => $finalLevel,
                    ':goal' => $payload['child_learning_goal'] ?? $intake['main_goal'] ?? null,
                    ':dialect' => $payload['preferred_arabic_type'] ?? null,
                    ':notes' => 'Child profile created after Owner approval.',
                ]);
            $pdo->prepare('INSERT INTO parent_child_links (parent_user_id, child_user_id, child_name, relationship, status) VALUES (:parent, :child, :child_name, "parent", "active")')
                ->execute([':parent' => $parentUserId, ':child' => $studentUserId, ':child_name' => $childName]);

            approval_log_login_details(
                $parentUserId,
                $studentUserId,
                $parentUserId,
                $intakeId,
                $purchaseId,
                $parentEmail,
                'Welcome to Habiba Nabil Arabic Academy - Parent Access',
                "Welcome {$parentName}. Your parent login email is {$parentEmail} and your temporary password is {$parentPassword}. Your child profile is linked to this account.",
                $parentPassword,
                $ownerUserId
            );
            approval_log_login_details(
                $studentUserId,
                $studentUserId,
                $parentUserId,
                $intakeId,
                $purchaseId,
                $childEmail,
                'Child student account created',
                "Child student account created for {$childName}. Login email is {$childEmail} and temporary password is {$childPassword}.",
                $childPassword,
                $ownerUserId
            );

            $created['parent_email'] = $parentEmail;
            $created['student_email'] = $childEmail;
        } else {
            $studentName = trim((string) ($payload['full_name'] ?? $intake['learner_name'] ?? $intake['checkout_name'] ?? 'Student'));
            $studentEmail = approval_unique_email(approval_safe_email((string) ($payload['email'] ?? $intake['checkout_email'] ?? ''), 'student'));
            $studentPassword = approval_generate_password();

            $studentUserId = approval_create_user($studentEmail, $studentName, 'student', $studentPassword);
            $pdo->prepare('INSERT INTO user_profiles (user_id, phone, country, preferred_language) VALUES (:user_id, :phone, :country, "en")')
                ->execute([':user_id' => $studentUserId, ':phone' => $intake['whatsapp'] ?? null, ':country' => $payload['country'] ?? null]);
            $pdo->prepare('INSERT INTO student_profiles (user_id, learner_type, current_level, learning_goal, preferred_dialect, notes) VALUES (:user_id, "adult", :level, :goal, :dialect, :notes)')
                ->execute([
                    ':user_id' => $studentUserId,
                    ':level' => $finalLevel,
                    ':goal' => $payload['main_goal'] ?? $intake['main_goal'] ?? null,
                    ':dialect' => $payload['preferred_arabic_type'] ?? null,
                    ':notes' => 'Adult student account created after Owner approval.',
                ]);

            approval_log_login_details(
                $studentUserId,
                $studentUserId,
                null,
                $intakeId,
                $purchaseId,
                $studentEmail,
                'Welcome to Habiba Nabil Arabic Academy',
                "Welcome {$studentName}. Your login email is {$studentEmail} and your temporary password is {$studentPassword}. Please log in and change your password.",
                $studentPassword,
                $ownerUserId
            );

            $created['student_email'] = $studentEmail;
        }

        $packageId = approval_create_lesson_package($studentUserId, $purchaseId, $planId ?: null, $ownerUserId);

        $pdo->prepare(
            'UPDATE student_intake_forms
             SET owner_review_status = "approved", approved_at = NOW(), approved_by_user_id = :owner_id,
                 created_student_user_id = :student_id, created_parent_user_id = :parent_id, approval_note = :note, student_user_id = :student_id
             WHERE id = :intake_id'
        )->execute([
            ':owner_id' => $ownerUserId,
            ':student_id' => $studentUserId,
            ':parent_id' => $parentUserId,
            ':note' => $note ?: null,
            ':intake_id' => $intakeId,
        ]);

        if ($purchaseId) {
            $pdo->prepare(
                'UPDATE purchases
                 SET owner_review_status = "approved", approved_at = NOW(), approved_by_user_id = :owner_id,
                     created_student_user_id = :student_id, created_parent_user_id = :parent_id, user_id = :student_id
                 WHERE id = :purchase_id'
            )->execute([
                ':owner_id' => $ownerUserId,
                ':student_id' => $studentUserId,
                ':parent_id' => $parentUserId,
                ':purchase_id' => $purchaseId,
            ]);
        }

        $pdo->prepare(
            'INSERT INTO onboarding_account_links (intake_form_id, purchase_id, learner_type, student_user_id, parent_user_id, lesson_package_id, created_by_user_id)
             VALUES (:intake_id, :purchase_id, :learner_type, :student_id, :parent_id, :package_id, :owner_id)'
        )->execute([
            ':intake_id' => $intakeId,
            ':purchase_id' => $purchaseId,
            ':learner_type' => $isChild ? 'child' : 'adult',
            ':student_id' => $studentUserId,
            ':parent_id' => $parentUserId,
            ':package_id' => $packageId,
            ':owner_id' => $ownerUserId,
        ]);

        audit_log($ownerUserId, 'onboarding_approved_accounts_created', 'student_intake_form', (string) $intakeId, [
            'student_user_id' => $studentUserId,
            'parent_user_id' => $parentUserId,
            'lesson_package_id' => $packageId,
            'learner_type' => $isChild ? 'child' : 'adult',
        ]);

        $pdo->commit();

        return [
            'student_user_id' => $studentUserId,
            'parent_user_id' => $parentUserId,
            'lesson_package_id' => $packageId,
            'already_created' => false,
            'created' => $created,
        ];
    } catch (Throwable $exception) {
        $pdo->rollBack();
        throw $exception;
    }
}

function approval_students(): array
{
    return db()->query(
        'SELECT users.*, student_profiles.learner_type, student_profiles.current_level, student_profiles.learning_goal, user_profiles.phone, user_profiles.country
         FROM users
         INNER JOIN student_profiles ON student_profiles.user_id = users.id
         LEFT JOIN user_profiles ON user_profiles.user_id = users.id
         WHERE users.role = "student"
         ORDER BY users.created_at DESC
         LIMIT 300'
    )->fetchAll();
}

function approval_parents(): array
{
    return db()->query(
        'SELECT users.*, parent_profiles.phone, parent_profiles.preferred_contact_method,
                COUNT(parent_child_links.id) AS child_count
         FROM users
         INNER JOIN parent_profiles ON parent_profiles.user_id = users.id
         LEFT JOIN parent_child_links ON parent_child_links.parent_user_id = users.id AND parent_child_links.status = "active"
         WHERE users.role = "parent"
         GROUP BY users.id
         ORDER BY users.created_at DESC
         LIMIT 300'
    )->fetchAll();
}

function approval_student_detail(int $studentUserId): ?array
{
    $statement = db()->prepare(
        'SELECT users.*, student_profiles.learner_type, student_profiles.current_level, student_profiles.learning_goal, student_profiles.preferred_dialect, student_profiles.notes,
                user_profiles.phone, user_profiles.country, lesson_packages.package_name, lesson_packages.total_credits, lesson_packages.remaining_credits, lesson_packages.status AS package_status
         FROM users
         LEFT JOIN student_profiles ON student_profiles.user_id = users.id
         LEFT JOIN user_profiles ON user_profiles.user_id = users.id
         LEFT JOIN lesson_packages ON lesson_packages.student_user_id = users.id
         WHERE users.id = :id AND users.role = "student"
         ORDER BY lesson_packages.id DESC
         LIMIT 1'
    );
    $statement->execute([':id' => $studentUserId]);
    $row = $statement->fetch();
    return $row ?: null;
}

function approval_parent_detail(int $parentUserId): ?array
{
    $statement = db()->prepare(
        'SELECT users.*, parent_profiles.phone, parent_profiles.preferred_contact_method, parent_profiles.notes
         FROM users
         LEFT JOIN parent_profiles ON parent_profiles.user_id = users.id
         WHERE users.id = :id AND users.role = "parent"
         LIMIT 1'
    );
    $statement->execute([':id' => $parentUserId]);
    $parent = $statement->fetch();
    if (!$parent) return null;

    $children = db()->prepare(
        'SELECT parent_child_links.*, users.email AS child_email, users.display_name AS child_display_name, student_profiles.current_level
         FROM parent_child_links
         LEFT JOIN users ON users.id = parent_child_links.child_user_id
         LEFT JOIN student_profiles ON student_profiles.user_id = parent_child_links.child_user_id
         WHERE parent_child_links.parent_user_id = :id
         ORDER BY parent_child_links.created_at DESC'
    );
    $children->execute([':id' => $parentUserId]);
    $parent['children'] = $children->fetchAll();

    return $parent;
}

function approval_login_logs_for_user(int $userId): array
{
    $statement = db()->prepare('SELECT * FROM login_detail_logs WHERE user_id = :id ORDER BY created_at DESC LIMIT 20');
    $statement->execute([':id' => $userId]);
    return $statement->fetchAll();
}
