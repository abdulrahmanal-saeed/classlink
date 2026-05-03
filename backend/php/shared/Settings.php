<?php
/**
 * Settings helper.
 *
 * The Settings Center uses this helper so all settings reads/writes stay in one
 * place. This makes it easier to add validation, caching, or encryption later.
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/AuditLogger.php';

function settings_sections(): array
{
    return [
        'pricing' => 'Pricing',
        'payment' => 'Payment',
        'email_templates' => 'Email templates',
        'whatsapp_templates' => 'WhatsApp templates',
        'seo' => 'SEO',
        'ai_settings' => 'AI settings',
        'upload_limits' => 'Upload limits',
        'lesson_cancellation_policy' => 'Lesson cancellation policy',
        'referral_rewards' => 'Referral rewards',
        'badge_settings' => 'Badge settings',
        'availability' => 'Availability',
        'notifications' => 'Notifications',
        'legal_pages' => 'Legal pages',
    ];
}

function setting_get(string $key, ?string $default = null): ?string
{
    $statement = db()->prepare('SELECT setting_value FROM settings WHERE setting_key = :key LIMIT 1');
    $statement->execute([':key' => $key]);
    $value = $statement->fetchColumn();

    return $value === false ? $default : (string) $value;
}

function setting_set(string $key, string $value, string $group, int $userId, string $type = 'text'): void
{
    $oldValue = setting_get($key);

    $statement = db()->prepare(
        'INSERT INTO settings (setting_key, setting_value, setting_group, value_type, updated_by_user_id)
         VALUES (:setting_key, :setting_value, :setting_group, :value_type, :updated_by_user_id)
         ON DUPLICATE KEY UPDATE
           setting_value = VALUES(setting_value),
           setting_group = VALUES(setting_group),
           value_type = VALUES(value_type),
           updated_by_user_id = VALUES(updated_by_user_id)'
    );

    $statement->execute([
        ':setting_key' => $key,
        ':setting_value' => $value,
        ':setting_group' => $group,
        ':value_type' => $type,
        ':updated_by_user_id' => $userId,
    ]);

    audit_log($userId, 'setting_updated', 'setting', $key, [
        'old_value' => $oldValue,
        'new_value' => $value,
        'group' => $group,
    ]);
}

function settings_group_values(): array
{
    $statement = db()->query('SELECT setting_key, setting_value, setting_group FROM settings ORDER BY setting_group, setting_key');
    $rows = $statement->fetchAll();
    $grouped = [];

    foreach ($rows as $row) {
        $grouped[$row['setting_group']][$row['setting_key']] = $row['setting_value'];
    }

    return $grouped;
}
