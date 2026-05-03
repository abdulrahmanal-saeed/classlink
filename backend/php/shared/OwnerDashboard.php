<?php
/**
 * Phase 13 Owner/Teacher Dashboard helper.
 * Centralizes Owner dashboard stats and student/parent detail data.
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/LessonCredits.php';
require_once __DIR__ . '/BookingCalendar.php';
require_once __DIR__ . '/StudentPortal.php';
require_once __DIR__ . '/ApprovalWorkflow.php';

function owner_count(string $sql, array $params = []): int
{
    $s = db()->prepare($sql);
    $s->execute($params);
    return (int) $s->fetchColumn();
}

function owner_dashboard_stats(): array
{
    return [
        'active_students' => owner_count('SELECT COUNT(*) FROM users WHERE role = "student" AND status = "active"'),
        'active_child_learners' => owner_count('SELECT COUNT(*) FROM student_profiles INNER JOIN users ON users.id = student_profiles.user_id WHERE users.status = "active" AND student_profiles.learner_type = "child"'),
        'pending_payments' => owner_count('SELECT COUNT(*) FROM purchases WHERE status IN ("pending","pending_verification")'),
        'pending_level_checks' => owner_count('SELECT COUNT(*) FROM level_check_attempts WHERE status = "submitted"'),
        'upcoming_lessons_today' => owner_count('SELECT COUNT(*) FROM bookings WHERE DATE(start_at) = CURDATE() AND status IN ("requested","confirmed","reschedule_requested","rescheduled")'),
        'homework_submitted' => owner_count('SELECT COUNT(*) FROM homework_submissions WHERE status IN ("submitted","needs_correction")'),
        'scenarios_submitted' => owner_count('SELECT COUNT(*) FROM scenario_submissions WHERE submitted_at IS NOT NULL AND reviewed_at IS NULL'),
        'reviews_pending_correction' => owner_count('SELECT COUNT(*) FROM review_submissions WHERE submitted_at IS NOT NULL AND reviewed_at IS NULL'),
        'students_low_on_credits' => owner_count('SELECT COUNT(*) FROM lesson_packages WHERE status = "active" AND remaining_credits <= 2'),
        'ai_weekly_summaries_due' => owner_count('SELECT COUNT(*) FROM users WHERE role = "student" AND status = "active"'),
    ];
}

function owner_dashboard_pending_items(): array
{
    return [
        'payments' => db()->query('SELECT purchases.*, plans.name_en AS plan_name FROM purchases LEFT JOIN plans ON plans.id = purchases.plan_id WHERE purchases.status IN ("pending","pending_verification") ORDER BY purchases.created_at DESC LIMIT 10')->fetchAll(),
        'bookings' => db()->query('SELECT bookings.*, users.display_name AS student_name FROM bookings LEFT JOIN users ON users.id = bookings.student_user_id WHERE bookings.status IN ("requested","reschedule_requested") ORDER BY bookings.start_at ASC LIMIT 10')->fetchAll(),
        'homework' => db()->query('SELECT homework_submissions.*, homeworks.title, users.display_name AS student_name FROM homework_submissions LEFT JOIN homeworks ON homeworks.id = homework_submissions.homework_id LEFT JOIN users ON users.id = homework_submissions.student_user_id WHERE homework_submissions.status IN ("submitted","needs_correction") ORDER BY homework_submissions.submitted_at DESC LIMIT 10')->fetchAll(),
        'level_checks' => db()->query('SELECT level_check_attempts.*, users.display_name AS student_name FROM level_check_attempts LEFT JOIN users ON users.id = level_check_attempts.user_id WHERE level_check_attempts.status = "submitted" ORDER BY level_check_attempts.submitted_at DESC LIMIT 10')->fetchAll(),
        'low_credits' => db()->query('SELECT lesson_packages.*, users.display_name AS student_name FROM lesson_packages LEFT JOIN users ON users.id = lesson_packages.student_user_id WHERE lesson_packages.status = "active" AND lesson_packages.remaining_credits <= 2 ORDER BY lesson_packages.remaining_credits ASC LIMIT 10')->fetchAll(),
    ];
}

function owner_students_core(): array
{
    return approval_students();
}

function owner_parents_core(): array
{
    return approval_parents();
}

function owner_student_detail_full(int $studentId): ?array
{
    $profile = student_portal_profile($studentId);
    if (!$profile) return null;

    $audit = db()->prepare('SELECT * FROM audit_logs WHERE entity_id = :id OR JSON_EXTRACT(metadata, "$.student_user_id") = :json_id ORDER BY created_at DESC LIMIT 50');
    $audit->execute([':id' => (string) $studentId, ':json_id' => $studentId]);

    return [
        'profile' => $profile,
        'balance' => credits_student_summary($studentId),
        'lessons' => booking_list_student($studentId),
        'homeworks' => student_portal_homeworks($studentId),
        'scenarios' => student_portal_scenarios($studentId),
        'reviews' => student_portal_reviews($studentId),
        'practice_words' => student_portal_practice_words($studentId),
        'session_notes' => student_portal_session_notes($studentId),
        'badges' => student_portal_badges($studentId),
        'progress' => student_portal_progress_summary($studentId),
        'audit' => $audit->fetchAll(),
    ];
}

function owner_all_homework(): array
{
    return db()->query('SELECT homeworks.*, users.display_name AS student_name, homework_submissions.status AS submission_status, homework_submissions.submitted_at FROM homeworks LEFT JOIN users ON users.id = homeworks.student_user_id LEFT JOIN homework_submissions ON homework_submissions.homework_id = homeworks.id AND homework_submissions.student_user_id = homeworks.student_user_id ORDER BY homeworks.created_at DESC LIMIT 300')->fetchAll();
}

function owner_all_scenarios(): array
{
    return db()->query('SELECT scenarios.*, users.display_name AS student_name, scenario_submissions.submitted_at, scenario_submissions.score FROM scenarios LEFT JOIN users ON users.id = scenarios.student_user_id LEFT JOIN scenario_submissions ON scenario_submissions.scenario_id = scenarios.id AND scenario_submissions.student_user_id = scenarios.student_user_id ORDER BY scenarios.created_at DESC LIMIT 300')->fetchAll();
}

function owner_all_reviews(): array
{
    return db()->query('SELECT review_tests.*, users.display_name AS student_name, review_submissions.score, review_submissions.submitted_at, review_submissions.reviewed_at FROM review_tests LEFT JOIN users ON users.id = review_tests.student_user_id LEFT JOIN review_submissions ON review_submissions.review_test_id = review_tests.id AND review_submissions.student_user_id = review_tests.student_user_id ORDER BY review_tests.created_at DESC LIMIT 300')->fetchAll();
}

function owner_all_materials(): array
{
    return db()->query('SELECT * FROM course_materials ORDER BY created_at DESC LIMIT 300')->fetchAll();
}

function owner_all_notifications(): array
{
    return db()->query('SELECT notifications.*, users.display_name, users.email FROM notifications LEFT JOIN users ON users.id = notifications.user_id ORDER BY notifications.created_at DESC LIMIT 300')->fetchAll();
}
