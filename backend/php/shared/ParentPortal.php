<?php
/**
 * Phase 11 Parent Portal helper.
 * Centralizes parent-owned child data and enforces parent-child ownership checks.
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/StudentPortal.php';
require_once __DIR__ . '/LessonCredits.php';
require_once __DIR__ . '/BookingCalendar.php';

function parent_portal_children(int $parentId): array
{
    $s = db()->prepare(
        'SELECT parent_child_links.*, users.display_name AS child_name, users.email AS child_email,
                student_profiles.current_level, student_profiles.target_level, student_profiles.learning_goal,
                student_profiles.learner_type, student_profiles.preferred_dialect
         FROM parent_child_links
         INNER JOIN users ON users.id = parent_child_links.child_user_id
         LEFT JOIN student_profiles ON student_profiles.user_id = parent_child_links.child_user_id
         WHERE parent_child_links.parent_user_id = :parent
           AND parent_child_links.status = "active"
         ORDER BY users.display_name ASC'
    );
    $s->execute([':parent' => $parentId]);
    return $s->fetchAll();
}

function parent_portal_can_access_child(int $parentId, int $childId): bool
{
    $s = db()->prepare(
        'SELECT id FROM parent_child_links
         WHERE parent_user_id = :parent AND child_user_id = :child AND status = "active"
         LIMIT 1'
    );
    $s->execute([':parent' => $parentId, ':child' => $childId]);
    return (bool) $s->fetch();
}

function parent_portal_require_child(int $parentId, int $childId): void
{
    if (!$childId || !parent_portal_can_access_child($parentId, $childId)) {
        http_response_code(403);
        echo '<div style="font-family:Arial;padding:24px"><h1>Unauthorized</h1><p>You are not allowed to view this child profile.</p><p><a href="/parent/dashboard">Back to dashboard</a></p></div>';
        exit;
    }
}

function parent_portal_child_profile(int $parentId, int $childId): ?array
{
    parent_portal_require_child($parentId, $childId);
    return student_portal_profile($childId);
}

function parent_portal_upcoming_lesson(int $parentId, int $childId): ?array
{
    parent_portal_require_child($parentId, $childId);
    return student_portal_upcoming_lesson($childId);
}

function parent_portal_latest_level_check(int $parentId, int $childId): ?array
{
    parent_portal_require_child($parentId, $childId);
    $s = db()->prepare(
        'SELECT level_check_attempts.*, student_intake_forms.recommended_first_lesson,
                student_intake_forms.owner_review_note, student_intake_forms.learner_type
         FROM student_intake_forms
         LEFT JOIN level_check_attempts ON level_check_attempts.intake_form_id = student_intake_forms.id
         WHERE student_intake_forms.created_student_user_id = :child
         ORDER BY level_check_attempts.reviewed_at DESC, level_check_attempts.submitted_at DESC, level_check_attempts.id DESC
         LIMIT 1'
    );
    $s->execute([':child' => $childId]);
    $row = $s->fetch();
    return $row ?: null;
}

function parent_portal_homeworks(int $parentId, int $childId): array
{
    parent_portal_require_child($parentId, $childId);
    return student_portal_homeworks($childId);
}

function parent_portal_session_notes(int $parentId, int $childId): array
{
    parent_portal_require_child($parentId, $childId);
    return student_portal_session_notes($childId);
}

function parent_portal_progress(int $parentId, int $childId): array
{
    parent_portal_require_child($parentId, $childId);
    return [
        'summary' => student_portal_progress_summary($childId),
        'streak' => student_portal_streak($childId),
        'badges' => student_portal_badges($childId),
        'practice_due' => student_portal_words_due_count($childId),
        'level_check' => parent_portal_latest_level_check($parentId, $childId),
    ];
}

function parent_portal_child_dashboard(int $parentId, int $childId): array
{
    parent_portal_require_child($parentId, $childId);
    return [
        'profile' => student_portal_profile($childId),
        'upcoming_lesson' => student_portal_upcoming_lesson($childId),
        'balance' => credits_student_summary($childId),
        'current_homework' => student_portal_current_homework($childId),
        'session_notes' => array_slice(student_portal_session_notes($childId), 0, 3),
        'progress' => student_portal_progress_summary($childId),
        'streak' => student_portal_streak($childId),
        'badges' => array_slice(student_portal_badges($childId), 0, 4),
        'level_check' => parent_portal_latest_level_check($parentId, $childId),
    ];
}

function parent_portal_notifications(int $parentId): array
{
    $s = db()->prepare('SELECT * FROM notifications WHERE user_id = :parent ORDER BY created_at DESC LIMIT 200');
    $s->execute([':parent' => $parentId]);
    return $s->fetchAll();
}

function parent_portal_first_child_id(int $parentId): ?int
{
    $children = parent_portal_children($parentId);
    return $children ? (int) $children[0]['child_user_id'] : null;
}
