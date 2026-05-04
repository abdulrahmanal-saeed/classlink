<?php
/**
 * Phase 18 Referral System helper.
 * Student/parent referral codes, referral tracking, payment qualification, and manual reward application.
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/AuditLogger.php';
require_once __DIR__ . '/CommunicationCenter.php';

function referral_setting(string $key, $default = null)
{
    $s = db()->prepare('SELECT setting_value FROM settings WHERE setting_key = :key LIMIT 1');
    $s->execute([':key' => $key]);
    $value = $s->fetchColumn();
    return $value === false ? $default : $value;
}

function referral_update_setting(int $ownerId, string $key, string $value): void
{
    db()->prepare('UPDATE settings SET setting_value = :value, updated_by_user_id = :owner WHERE setting_key = :key')
        ->execute([':value' => $value, ':owner' => $ownerId, ':key' => $key]);
    audit_log($ownerId, 'referral_setting_updated', 'setting', $key, ['value' => $value]);
}

function referral_generate_code(int $userId, ?string $email = null): string
{
    return 'HN' . $userId . strtoupper(substr(md5($userId . '|' . ($email ?? '') . '|' . time()), 0, 5));
}

function referral_get_or_create_code(int $userId): array
{
    $s = db()->prepare('SELECT * FROM referral_codes WHERE owner_user_id = :user LIMIT 1');
    $s->execute([':user' => $userId]);
    $code = $s->fetch();
    if ($code) return $code;

    $email = null;
    $u = db()->prepare('SELECT email FROM users WHERE id = :id LIMIT 1');
    $u->execute([':id' => $userId]);
    $email = $u->fetchColumn() ?: null;
    $newCode = referral_generate_code($userId, $email);

    db()->prepare('INSERT INTO referral_codes (owner_user_id, code) VALUES (:user, :code)')
        ->execute([':user' => $userId, ':code' => $newCode]);

    $s->execute([':user' => $userId]);
    return $s->fetch();
}

function referral_public_link(string $code): string
{
    return rtrim((string) referral_setting('referral_public_base_url', 'https://mshabibanabil.com/?ref='), '=') . '=' . urlencode($code);
}

function referral_find_code(string $code): ?array
{
    $s = db()->prepare('SELECT referral_codes.*, users.display_name, users.email FROM referral_codes LEFT JOIN users ON users.id = referral_codes.owner_user_id WHERE referral_codes.code = :code AND referral_codes.status = "active" LIMIT 1');
    $s->execute([':code' => trim($code)]);
    $row = $s->fetch();
    return $row ?: null;
}

function referral_record_landing(string $code): void
{
    $row = referral_find_code($code);
    if (!$row) return;
    db()->prepare('UPDATE referral_codes SET landing_count = landing_count + 1 WHERE id = :id')->execute([':id' => (int) $row['id']]);
}

function referral_attach_to_purchase(int $purchaseId, ?string $code): ?int
{
    if (!$code || referral_setting('referral_program_enabled', '1') !== '1') return null;
    $refCode = referral_find_code($code);
    if (!$refCode) return null;

    db()->prepare('UPDATE purchases SET referral_code = :code, referral_code_id = :code_id WHERE id = :purchase')
        ->execute([':code' => $refCode['code'], ':code_id' => (int) $refCode['id'], ':purchase' => $purchaseId]);

    return (int) $refCode['id'];
}

function referral_qualify_from_paid_purchase(int $purchaseId, ?int $paymentRecordId = null): ?int
{
    $p = db()->prepare('SELECT purchases.*, users.email, users.display_name FROM purchases LEFT JOIN users ON users.id = purchases.user_id WHERE purchases.id = :id LIMIT 1');
    $p->execute([':id' => $purchaseId]);
    $purchase = $p->fetch();
    if (!$purchase || empty($purchase['referral_code_id']) || referral_setting('referral_program_enabled', '1') !== '1') return null;

    $refCode = db()->prepare('SELECT * FROM referral_codes WHERE id = :id LIMIT 1');
    $refCode->execute([':id' => (int) $purchase['referral_code_id']]);
    $code = $refCode->fetch();
    if (!$code) return null;

    if ((int) $code['owner_user_id'] === (int) ($purchase['user_id'] ?? 0)) return null;

    $exists = db()->prepare('SELECT id FROM referrals WHERE purchase_id = :purchase LIMIT 1');
    $exists->execute([':purchase' => $purchaseId]);
    $existingId = $exists->fetchColumn();
    if ($existingId) return (int) $existingId;

    $rewardType = referral_setting('referral_reward_type', 'free_session');
    $rewardValue = (float) referral_setting('referral_reward_value', '1');

    db()->prepare(
        'INSERT INTO referrals
          (referrer_user_id, referral_code_id, source_referral_code, referred_email, referred_user_id, referred_name, referral_code, purchase_id, payment_record_id, status, reward_type, reward_value, reward_amount, qualified_at)
         VALUES
          (:referrer, :code_id, :source_code, :email, :referred_user, :name, :legacy_code, :purchase, :payment, "reward_pending", :reward_type, :reward_value, :reward_amount, NOW())'
    )->execute([
        ':referrer' => (int) $code['owner_user_id'],
        ':code_id' => (int) $code['id'],
        ':source_code' => $code['code'],
        ':email' => $purchase['email'] ?? null,
        ':referred_user' => $purchase['user_id'] ?: null,
        ':name' => $purchase['display_name'] ?? null,
        ':legacy_code' => $code['code'] . '-' . $purchaseId,
        ':purchase' => $purchaseId,
        ':payment' => $paymentRecordId,
        ':reward_type' => $rewardType,
        ':reward_value' => $rewardValue,
        ':reward_amount' => $rewardType === 'aed_discount' || $rewardType === 'both' ? $rewardValue : null,
    ]);

    $referralId = (int) db()->lastInsertId();
    db()->prepare('UPDATE purchases SET referral_id = :referral WHERE id = :purchase')->execute([':referral' => $referralId, ':purchase' => $purchaseId]);
    if ($paymentRecordId) db()->prepare('UPDATE payment_records SET referral_id = :referral WHERE id = :payment')->execute([':referral' => $referralId, ':payment' => $paymentRecordId]);
    audit_log(null, 'referral_qualified', 'referral', (string) $referralId, ['purchase_id' => $purchaseId, 'payment_record_id' => $paymentRecordId]);
    return $referralId;
}

function referral_all(): array
{
    return db()->query('SELECT referrals.*, referrer.display_name AS referrer_name, referred.display_name AS referred_user_name FROM referrals LEFT JOIN users referrer ON referrer.id = referrals.referrer_user_id LEFT JOIN users referred ON referred.id = referrals.referred_user_id ORDER BY referrals.created_at DESC LIMIT 300')->fetchAll();
}

function referral_for_user(int $userId): array
{
    $s = db()->prepare('SELECT referrals.*, referred.display_name AS referred_user_name FROM referrals LEFT JOIN users referred ON referred.id = referrals.referred_user_id WHERE referrals.referrer_user_id = :user ORDER BY referrals.created_at DESC LIMIT 100');
    $s->execute([':user' => $userId]);
    return $s->fetchAll();
}

function referral_apply_reward(int $ownerId, int $referralId, array $post = []): void
{
    $s = db()->prepare('SELECT * FROM referrals WHERE id = :id LIMIT 1');
    $s->execute([':id' => $referralId]);
    $referral = $s->fetch();
    if (!$referral) throw new RuntimeException('Referral not found.');
    if ($referral['status'] === 'reward_applied') throw new RuntimeException('Reward already applied.');

    $rewardType = $post['reward_type'] ?? ($referral['reward_type'] ?: referral_setting('referral_reward_type', 'free_session'));
    $rewardValue = (float) ($post['reward_value'] ?? ($referral['reward_value'] ?: referral_setting('referral_reward_value', '1')));
    $creditAmount = in_array($rewardType, ['free_session','both'], true) ? $rewardValue : null;
    $discountAmount = in_array($rewardType, ['aed_discount','both'], true) ? $rewardValue : null;

    if ($creditAmount !== null && $creditAmount > 0) {
        $package = db()->prepare('SELECT id FROM lesson_packages WHERE student_user_id = :student AND status = "active" ORDER BY id DESC LIMIT 1');
        $package->execute([':student' => (int) $referral['referrer_user_id']]);
        $packageId = $package->fetchColumn();
        if ($packageId) {
            db()->prepare('UPDATE lesson_packages SET total_credits = total_credits + :credits, remaining_credits = remaining_credits + :credits WHERE id = :package')
                ->execute([':credits' => $creditAmount, ':package' => (int) $packageId]);
            db()->prepare('INSERT INTO lesson_credit_transactions (package_id, student_user_id, transaction_type, credits, reason, created_by_user_id) VALUES (:package, :student, "add", :credits, :reason, :owner)')
                ->execute([':package' => (int) $packageId, ':student' => (int) $referral['referrer_user_id'], ':credits' => $creditAmount, ':reason' => 'Referral reward #' . $referralId, ':owner' => $ownerId]);
        }
    }

    db()->prepare('UPDATE referrals SET status = "reward_applied", reward_type = :type, reward_value = :value, reward_credit_amount = :credits, reward_discount_amount = :discount, applied_by_user_id = :owner, reward_applied_at = NOW(), notes = :notes WHERE id = :id')
        ->execute([
            ':type' => $rewardType,
            ':value' => $rewardValue,
            ':credits' => $creditAmount,
            ':discount' => $discountAmount,
            ':owner' => $ownerId,
            ':notes' => trim($post['notes'] ?? ''),
            ':id' => $referralId,
        ]);

    audit_log($ownerId, 'referral_reward_applied', 'referral', (string) $referralId, ['reward_type' => $rewardType, 'reward_value' => $rewardValue]);
}

function referral_reject(int $ownerId, int $referralId, string $notes = ''): void
{
    db()->prepare('UPDATE referrals SET status = "rejected", applied_by_user_id = :owner, notes = :notes WHERE id = :id')
        ->execute([':owner' => $ownerId, ':notes' => $notes, ':id' => $referralId]);
    audit_log($ownerId, 'referral_rejected', 'referral', (string) $referralId, ['notes' => $notes]);
}
