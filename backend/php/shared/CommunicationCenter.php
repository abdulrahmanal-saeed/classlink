<?php
/**
 * Phase 17 Communication Center helper.
 * Email/WhatsApp templates, email log fallback, actionable internal notifications.
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/AuditLogger.php';
require_once __DIR__ . '/ParentPortal.php';

function comm_setting(string $key, $default = null)
{
    $s = db()->prepare('SELECT setting_value FROM settings WHERE setting_key = :key LIMIT 1');
    $s->execute([':key' => $key]);
    $value = $s->fetchColumn();
    return $value === false ? $default : $value;
}

function comm_replace_variables(string $text, array $vars): string
{
    foreach ($vars as $key => $value) {
        $text = str_replace('[' . $key . ']', (string) $value, $text);
    }
    return $text;
}

function comm_email_templates(): array
{
    return db()->query('SELECT * FROM email_templates ORDER BY sort_order ASC, id ASC')->fetchAll();
}

function comm_whatsapp_templates(): array
{
    return db()->query('SELECT * FROM whatsapp_templates ORDER BY sort_order ASC, id ASC')->fetchAll();
}

function comm_template(string $table, string $key): ?array
{
    if (!in_array($table, ['email_templates', 'whatsapp_templates'], true)) throw new RuntimeException('Invalid template table.');
    $s = db()->prepare('SELECT * FROM ' . $table . ' WHERE template_key = :key LIMIT 1');
    $s->execute([':key' => $key]);
    $row = $s->fetch();
    return $row ?: null;
}

function comm_update_email_template(int $ownerId, int $templateId, array $post): void
{
    db()->prepare('UPDATE email_templates SET name=:name, subject_en=:subject_en, subject_ar=:subject_ar, body_en=:body_en, body_ar=:body_ar, variables=:variables, sort_order=:sort_order, is_active=:active WHERE id=:id')
        ->execute([
            ':name' => trim($post['name'] ?? ''),
            ':subject_en' => trim($post['subject_en'] ?? ''),
            ':subject_ar' => trim($post['subject_ar'] ?? ''),
            ':body_en' => trim($post['body_en'] ?? ''),
            ':body_ar' => trim($post['body_ar'] ?? ''),
            ':variables' => trim($post['variables'] ?? ''),
            ':sort_order' => (int) ($post['sort_order'] ?? 0),
            ':active' => isset($post['is_active']) ? 1 : 0,
            ':id' => $templateId,
        ]);
    audit_log($ownerId, 'email_template_updated', 'email_template', (string) $templateId, []);
}

function comm_update_whatsapp_template(int $ownerId, int $templateId, array $post): void
{
    db()->prepare('UPDATE whatsapp_templates SET name=:name, body_en=:body_en, body_ar=:body_ar, variables=:variables, sort_order=:sort_order, is_active=:active WHERE id=:id')
        ->execute([
            ':name' => trim($post['name'] ?? ''),
            ':body_en' => trim($post['body_en'] ?? ''),
            ':body_ar' => trim($post['body_ar'] ?? ''),
            ':variables' => trim($post['variables'] ?? ''),
            ':sort_order' => (int) ($post['sort_order'] ?? 0),
            ':active' => isset($post['is_active']) ? 1 : 0,
            ':id' => $templateId,
        ]);
    audit_log($ownerId, 'whatsapp_template_updated', 'whatsapp_template', (string) $templateId, []);
}

function comm_render_email(string $templateKey, array $vars = [], string $lang = 'en'): array
{
    $template = comm_template('email_templates', $templateKey);
    if (!$template || (int) $template['is_active'] !== 1) throw new RuntimeException('Email template not found or inactive.');
    $subject = $lang === 'ar' ? ($template['subject_ar'] ?: $template['subject_en']) : ($template['subject_en'] ?: $template['subject_ar']);
    $body = $lang === 'ar' ? ($template['body_ar'] ?: $template['body_en']) : ($template['body_en'] ?: $template['body_ar']);
    return [
        'subject' => comm_replace_variables((string) $subject, $vars),
        'body' => comm_replace_variables((string) $body, $vars),
        'template' => $template,
    ];
}

function comm_render_whatsapp(string $templateKey, array $vars = [], string $lang = 'en'): array
{
    $template = comm_template('whatsapp_templates', $templateKey);
    if (!$template || (int) $template['is_active'] !== 1) throw new RuntimeException('WhatsApp template not found or inactive.');
    $body = $lang === 'ar' ? ($template['body_ar'] ?: $template['body_en']) : ($template['body_en'] ?: $template['body_ar']);
    return [
        'message' => comm_replace_variables((string) $body, $vars),
        'template' => $template,
    ];
}

function comm_whatsapp_link(string $phone, string $message): string
{
    $digits = preg_replace('/\D+/', '', $phone);
    if ($digits === '') return '#';
    return 'https://wa.me/' . $digits . '?text=' . rawurlencode($message);
}

function comm_log_email(int $ownerId, ?int $userId, string $recipientEmail, ?string $recipientName, string $templateKey, array $vars = [], string $lang = 'en', ?string $relatedType = null, ?string $relatedId = null): int
{
    $rendered = comm_render_email($templateKey, $vars, $lang);
    $providerConfigured = comm_setting('email_provider_configured', '0') === '1';
    $status = $providerConfigured ? 'queued' : 'logged';

    db()->prepare(
        'INSERT INTO email_logs (user_id, recipient_email, recipient_name, template_key, subject, body, related_entity_type, related_entity_id, provider, status)
         VALUES (:user, :email, :name, :template, :subject, :body, :related_type, :related_id, :provider, :status)'
    )->execute([
        ':user' => $userId,
        ':email' => $recipientEmail,
        ':name' => $recipientName,
        ':template' => $templateKey,
        ':subject' => $rendered['subject'],
        ':body' => $rendered['body'],
        ':related_type' => $relatedType,
        ':related_id' => $relatedId,
        ':provider' => $providerConfigured ? 'configured_provider_placeholder' : 'fallback_log_only',
        ':status' => $status,
    ]);

    $id = (int) db()->lastInsertId();
    audit_log($ownerId, 'email_logged', 'email_log', (string) $id, ['template_key' => $templateKey, 'provider_configured' => $providerConfigured]);
    return $id;
}

function comm_email_logs(): array
{
    return db()->query('SELECT email_logs.*, users.display_name FROM email_logs LEFT JOIN users ON users.id = email_logs.user_id ORDER BY email_logs.created_at DESC LIMIT 300')->fetchAll();
}

function comm_action_route(string $targetRole, string $entityType, string $entityId, ?int $childId = null, string $mode = 'open'): array
{
    $label = 'Open';
    $url = '#';
    if ($targetRole === 'student') {
        if ($entityType === 'homework') { $label = $mode === 'result' ? 'View Result' : 'Open Homework'; $url = $mode === 'result' ? "/student/homework/result?id={$entityId}" : "/student/homework/view?id={$entityId}"; }
        if ($entityType === 'scenario') { $label = $mode === 'result' ? 'View Feedback' : 'Start Scenario'; $url = $mode === 'result' ? "/student/scenarios/result?id={$entityId}" : "/student/scenarios/view?id={$entityId}"; }
        if ($entityType === 'review') { $label = $mode === 'result' ? 'View Result' : 'Start Review'; $url = $mode === 'result' ? "/student/reviews/result?id={$entityId}" : "/student/reviews/view?id={$entityId}"; }
        if ($entityType === 'material') { $label = 'Open Material'; $url = '/student/materials'; }
        if ($entityType === 'booking') { $label = 'Open Lessons'; $url = '/student/lessons'; }
        if ($entityType === 'payment') { $label = 'Open Balance'; $url = '/student/balance'; }
        if ($entityType === 'badge') { $label = 'View Badge'; $url = '/student/badges'; }
    }
    if ($targetRole === 'parent' && $childId) {
        if ($entityType === 'homework') { $label = $mode === 'result' ? 'View Result' : 'Open Homework'; $url = $mode === 'result' ? "/parent/child/homework?id={$childId}" : "/parent/child/homework?id={$childId}"; }
        if ($entityType === 'scenario') { $label = $mode === 'result' ? 'View Feedback' : 'View Scenario'; $url = "/parent/child/view?id={$childId}"; }
        if ($entityType === 'review') { $label = $mode === 'result' ? 'View Result' : 'View Review'; $url = "/parent/child/progress?id={$childId}"; }
        if ($entityType === 'material') { $label = 'Open Materials'; $url = "/parent/child/view?id={$childId}"; }
        if ($entityType === 'booking') { $label = 'Open Lessons'; $url = "/parent/child/lessons?id={$childId}"; }
        if ($entityType === 'payment') { $label = 'Open Balance'; $url = "/parent/child/balance?id={$childId}"; }
        if ($entityType === 'badge') { $label = 'View Badge'; $url = "/parent/child/badges?id={$childId}"; }
    }
    if ($targetRole === 'owner') {
        if ($entityType === 'homework') { $label = 'Correct Homework'; $url = "/owner/homework/submissions?id={$entityId}"; }
        if ($entityType === 'scenario') { $label = 'Review Scenario'; $url = "/owner/scenarios/submissions?id={$entityId}"; }
        if ($entityType === 'review') { $label = 'Review Test'; $url = "/owner/reviews/results?id={$entityId}"; }
        if ($entityType === 'material') { $label = 'Open Materials'; $url = '/owner/materials'; }
        if ($entityType === 'booking') { $label = 'Open Booking'; $url = '/owner/bookings'; }
        if ($entityType === 'payment') { $label = 'Review Payment'; $url = "/owner/payments/view?id={$entityId}"; }
        if ($entityType === 'badge') { $label = 'Badge Settings'; $url = '/owner/badges/settings'; }
    }
    return ['label' => $label, 'url' => $url];
}

function comm_notification_requires_action(?string $entityType): bool
{
    return in_array($entityType, ['homework','scenario','review','material','booking','payment','badge'], true);
}

function comm_create_notification(array $data): int
{
    $entityType = $data['related_entity_type'] ?? null;
    if (comm_notification_requires_action($entityType) && (empty($data['action_label']) || empty($data['action_url']) || $data['action_url'] === '#')) {
        throw new RuntimeException('Actionable notification requires actionLabel and actionUrl.');
    }

    db()->prepare(
        'INSERT INTO notifications
          (user_id, title, body, notification_type, target_role, related_entity_type, related_entity_id, action_label, action_url, channel, status)
         VALUES
          (:user, :title, :body, :type, :role, :related_type, :related_id, :action_label, :action_url, "in_app", "queued")'
    )->execute([
        ':user' => $data['user_id'] ?? null,
        ':title' => $data['title'],
        ':body' => $data['message'] ?? $data['body'] ?? null,
        ':type' => $data['type'] ?? 'general',
        ':role' => $data['target_role'] ?? null,
        ':related_type' => $entityType,
        ':related_id' => $data['related_entity_id'] ?? null,
        ':action_label' => $data['action_label'] ?? null,
        ':action_url' => $data['action_url'] ?? null,
    ]);

    return (int) db()->lastInsertId();
}

function comm_mark_notification_read(int $notificationId, int $userId): void
{
    db()->prepare('UPDATE notifications SET status = "read", read_at = NOW() WHERE id = :id AND user_id = :user')
        ->execute([':id' => $notificationId, ':user' => $userId]);
}

function comm_user_notifications(int $userId, string $role): array
{
    $s = db()->prepare('SELECT * FROM notifications WHERE user_id = :user AND (target_role = :role OR target_role IS NULL) ORDER BY created_at DESC LIMIT 100');
    $s->execute([':user' => $userId, ':role' => $role]);
    return $s->fetchAll();
}

function comm_demo_variables(): array
{
    return [
        'Name' => 'Student Name',
        'Plan Name' => 'Monthly Plan',
        'Student Form Link' => '/student-form?ref=demo',
        'Booking Link' => '/student/book',
        'Lesson Date' => date('Y-m-d'),
        'Lesson Time' => '18:00',
        'Meeting Link' => 'https://meet.example.com/demo',
        'Level Check Link' => '/level-check-intro?intakeId=demo',
        'Homework Link' => '/student/homework/view?id=1',
        'Result Link' => '/student/homework/result?id=1',
        'Next Focus' => 'Speaking confidence',
        'Summary Link' => '/student/progress',
        'Scenario Link' => '/student/scenarios/view?id=1',
        'Review Link' => '/student/reviews/view?id=1',
        'Remaining Credits' => '2',
        'Payment Link' => '/pricing',
    ];
}
