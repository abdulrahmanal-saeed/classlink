<?php
/**
 * Phase 12 Academy Partner Portal helper.
 * Handles academy briefs, partner ownership, Owner review, and conversion.
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/AuditLogger.php';

function academy_brief_payload_from_post(array $post): array
{
    return [
        'student_name' => trim($post['student_name'] ?? ''),
        'age' => $post['age'] !== '' ? (int) $post['age'] : null,
        'nationality_country' => trim($post['nationality_country'] ?? ''),
        'current_arabic_level' => trim($post['current_arabic_level'] ?? ''),
        'goal' => trim($post['goal'] ?? ''),
        'speaking_ability' => trim($post['speaking_ability'] ?? ''),
        'reading_writing_ability' => trim($post['reading_writing_ability'] ?? ''),
        'notes_from_academy' => trim($post['notes_from_academy'] ?? ''),
        'parent_contact_info' => trim($post['parent_contact_info'] ?? ''),
        'preferred_schedule' => trim($post['preferred_schedule'] ?? ''),
    ];
}

function academy_submit_brief(int $partnerUserId, array $payload): int
{
    if (($payload['student_name'] ?? '') === '') {
        throw new RuntimeException('Student name is required.');
    }

    db()->prepare(
        'INSERT INTO academy_briefs
          (academy_partner_user_id, contact_name, student_name, age, nationality_country, current_arabic_level, goal, goals, speaking_ability, reading_writing_ability, notes_from_academy, parent_contact_info, preferred_schedule, status)
         VALUES
          (:partner, :contact_name, :student_name, :age, :country, :level, :goal, :goals, :speaking, :reading_writing, :notes, :parent_contact, :schedule, "submitted")'
    )->execute([
        ':partner' => $partnerUserId,
        ':contact_name' => $payload['student_name'],
        ':student_name' => $payload['student_name'],
        ':age' => $payload['age'],
        ':country' => $payload['nationality_country'] ?: null,
        ':level' => $payload['current_arabic_level'] ?: null,
        ':goal' => $payload['goal'] ?: null,
        ':goals' => $payload['goal'] ?: null,
        ':speaking' => $payload['speaking_ability'] ?: null,
        ':reading_writing' => $payload['reading_writing_ability'] ?: null,
        ':notes' => $payload['notes_from_academy'] ?: null,
        ':parent_contact' => $payload['parent_contact_info'] ?: null,
        ':schedule' => $payload['preferred_schedule'] ?: null,
    ]);

    $id = (int) db()->lastInsertId();
    audit_log($partnerUserId, 'academy_brief_submitted', 'academy_brief', (string) $id, ['student_name' => $payload['student_name']]);
    return $id;
}

function academy_partner_briefs(int $partnerUserId): array
{
    $s = db()->prepare('SELECT * FROM academy_briefs WHERE academy_partner_user_id = :partner ORDER BY created_at DESC LIMIT 300');
    $s->execute([':partner' => $partnerUserId]);
    return $s->fetchAll();
}

function academy_partner_brief_detail(int $partnerUserId, int $briefId): ?array
{
    $s = db()->prepare('SELECT * FROM academy_briefs WHERE id = :id AND academy_partner_user_id = :partner LIMIT 1');
    $s->execute([':id' => $briefId, ':partner' => $partnerUserId]);
    $row = $s->fetch();
    return $row ?: null;
}

function academy_owner_briefs(): array
{
    return db()->query(
        'SELECT academy_briefs.*, users.display_name AS partner_name, users.email AS partner_email
         FROM academy_briefs
         LEFT JOIN users ON users.id = academy_briefs.academy_partner_user_id
         ORDER BY academy_briefs.created_at DESC
         LIMIT 500'
    )->fetchAll();
}

function academy_owner_brief_detail(int $briefId): ?array
{
    $s = db()->prepare(
        'SELECT academy_briefs.*, users.display_name AS partner_name, users.email AS partner_email
         FROM academy_briefs
         LEFT JOIN users ON users.id = academy_briefs.academy_partner_user_id
         WHERE academy_briefs.id = :id
         LIMIT 1'
    );
    $s->execute([':id' => $briefId]);
    $row = $s->fetch();
    return $row ?: null;
}

function academy_update_review(int $briefId, string $status, string $internalNotes, int $ownerUserId): void
{
    if (!in_array($status, ['submitted','under_review','rejected','converted_to_student'], true)) {
        throw new RuntimeException('Invalid brief status.');
    }

    db()->prepare(
        'UPDATE academy_briefs
         SET status = :status, internal_notes = :notes, reviewed_by_user_id = :owner, reviewed_at = NOW()
         WHERE id = :id'
    )->execute([
        ':status' => $status,
        ':notes' => $internalNotes ?: null,
        ':owner' => $ownerUserId,
        ':id' => $briefId,
    ]);

    audit_log($ownerUserId, 'academy_brief_review_updated', 'academy_brief', (string) $briefId, ['status' => $status]);
}

function academy_convert_to_onboarding(int $briefId, int $ownerUserId, ?int $studentUserId = null): int
{
    $brief = academy_owner_brief_detail($briefId);
    if (!$brief) throw new RuntimeException('Academy brief not found.');

    if (!empty($brief['converted_intake_form_id'])) {
        return (int) $brief['converted_intake_form_id'];
    }

    $raw = [
        'source' => 'academy_partner_brief',
        'academy_brief_id' => $briefId,
        'student_name' => $brief['student_name'],
        'age' => $brief['age'],
        'nationality_country' => $brief['nationality_country'],
        'current_arabic_level' => $brief['current_arabic_level'],
        'goal' => $brief['goal'] ?: $brief['goals'],
        'speaking_ability' => $brief['speaking_ability'],
        'reading_writing_ability' => $brief['reading_writing_ability'],
        'notes_from_academy' => $brief['notes_from_academy'],
        'parent_contact_info' => $brief['parent_contact_info'],
        'preferred_schedule' => $brief['preferred_schedule'],
    ];

    db()->prepare(
        'INSERT INTO student_intake_forms
          (student_user_id, learner_name, age, country, main_goal, status, raw_payload, owner_review_status, owner_review_note)
         VALUES
          (:student_user_id, :learner_name, :age, :country, :goal, "submitted", :raw_payload, "pending_review", :owner_note)'
    )->execute([
        ':student_user_id' => $studentUserId,
        ':learner_name' => $brief['student_name'] ?: $brief['contact_name'],
        ':age' => $brief['age'],
        ':country' => $brief['nationality_country'] ?: null,
        ':goal' => ($brief['goal'] ?: $brief['goals']) ?: null,
        ':raw_payload' => json_encode($raw, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ':owner_note' => 'Converted from Academy Partner brief #' . $briefId,
    ]);

    $intakeId = (int) db()->lastInsertId();

    db()->prepare(
        'UPDATE academy_briefs
         SET status = "converted_to_student", converted_student_user_id = :student_id, converted_intake_form_id = :intake_id,
             converted_by_user_id = :owner, converted_at = NOW(), reviewed_by_user_id = :owner2, reviewed_at = NOW()
         WHERE id = :id'
    )->execute([
        ':student_id' => $studentUserId,
        ':intake_id' => $intakeId,
        ':owner' => $ownerUserId,
        ':owner2' => $ownerUserId,
        ':id' => $briefId,
    ]);

    audit_log($ownerUserId, 'academy_brief_converted_to_onboarding', 'academy_brief', (string) $briefId, [
        'intake_form_id' => $intakeId,
        'student_user_id' => $studentUserId,
    ]);

    return $intakeId;
}

function academy_status_badge(string $status): string
{
    $class = match ($status) {
        'submitted' => 'text-bg-primary',
        'under_review' => 'text-bg-warning',
        'converted_to_student' => 'text-bg-success',
        'rejected' => 'text-bg-danger',
        default => 'text-bg-light border',
    };
    return '<span class="badge ' . $class . '">' . htmlspecialchars($status, ENT_QUOTES, 'UTF-8') . '</span>';
}
