<?php
/**
 * Phase 1 demo account seeder.
 *
 * Run this manually after applying the migration:
 * php backend/php/database/seeds/seed_demo_accounts.php
 *
 * We generate password hashes at runtime instead of committing static hashes.
 */

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../shared/AuditLogger.php';

$pdo = db();
$password = 'demo password';

$accounts = [
    [
        'email' => 'owner@demo.com',
        'role' => 'owner_teacher',
        'display_name' => 'Demo Owner / Teacher',
        'preferred_language' => 'en',
    ],
    [
        'email' => 'adult.student@demo.com',
        'role' => 'student',
        'display_name' => 'Demo Adult Student',
        'preferred_language' => 'en',
    ],
    [
        'email' => 'parent@demo.com',
        'role' => 'parent',
        'display_name' => 'Demo Parent',
        'preferred_language' => 'en',
    ],
    [
        'email' => 'academy@demo.com',
        'role' => 'academy_partner',
        'display_name' => 'Demo Academy Partner',
        'preferred_language' => 'en',
    ],
];

$pdo->beginTransaction();

try {
    $userStatement = $pdo->prepare(
        'INSERT INTO users (email, password_hash, role, status, display_name)
         VALUES (:email, :password_hash, :role, "active", :display_name)
         ON DUPLICATE KEY UPDATE
           password_hash = VALUES(password_hash),
           role = VALUES(role),
           status = "active",
           display_name = VALUES(display_name)'
    );

    $profileStatement = $pdo->prepare(
        'INSERT INTO user_profiles (user_id, preferred_language)
         VALUES (:user_id, :preferred_language)
         ON DUPLICATE KEY UPDATE preferred_language = VALUES(preferred_language)'
    );

    $partnerStatement = $pdo->prepare(
        'INSERT INTO academy_partner_profiles (user_id, academy_name, contact_person, status)
         VALUES (:user_id, "Demo Academy", "Demo Partner", "placeholder")'
    );

    $parentLinkStatement = $pdo->prepare(
        'INSERT INTO parent_child_links (parent_user_id, child_name, relationship, status)
         VALUES (:parent_user_id, "Demo Child", "parent", "placeholder")'
    );

    foreach ($accounts as $account) {
        $userStatement->execute([
            ':email' => $account['email'],
            ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
            ':role' => $account['role'],
            ':display_name' => $account['display_name'],
        ]);

        $userId = (int) $pdo->lastInsertId();

        if ($userId === 0) {
            $lookup = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
            $lookup->execute([':email' => $account['email']]);
            $userId = (int) $lookup->fetchColumn();
        }

        $profileStatement->execute([
            ':user_id' => $userId,
            ':preferred_language' => $account['preferred_language'],
        ]);

        if ($account['role'] === 'academy_partner') {
            $exists = $pdo->prepare('SELECT id FROM academy_partner_profiles WHERE user_id = :user_id LIMIT 1');
            $exists->execute([':user_id' => $userId]);
            if (!$exists->fetchColumn()) {
                $partnerStatement->execute([':user_id' => $userId]);
            }
        }

        if ($account['role'] === 'parent') {
            $exists = $pdo->prepare('SELECT id FROM parent_child_links WHERE parent_user_id = :user_id LIMIT 1');
            $exists->execute([':user_id' => $userId]);
            if (!$exists->fetchColumn()) {
                $parentLinkStatement->execute([':parent_user_id' => $userId]);
            }
        }
    }

    audit_log(null, 'demo_accounts_seeded', 'system', null, ['count' => count($accounts)]);

    $pdo->commit();
    echo "Demo accounts seeded successfully. Password: demo password" . PHP_EOL;
} catch (Throwable $exception) {
    $pdo->rollBack();
    throw $exception;
}
