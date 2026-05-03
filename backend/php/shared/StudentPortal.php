<?php
/**
 * Phase 10 Student Portal helper.
 * Centralizes student-owned data for dashboard and student portal pages.
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/LessonCredits.php';
require_once __DIR__ . '/BookingCalendar.php';

function student_portal_profile(int $studentId): ?array
{
    $s = db()->prepare(
        'SELECT users.id, users.email, users.display_name, users.status, users.created_at,
                user_profiles.phone, user_profiles.country, user_profiles.preferred_language, user_profiles.timezone,
                student_profiles.learner_type, student_profiles.current_level, student_profiles.target_level,
                student_profiles.learning_goal, student_profiles.preferred_dialect, student_profiles.notes
         FROM users
         LEFT JOIN user_profiles ON user_profiles.user_id = users.id
         LEFT JOIN student_profiles ON student_profiles.user_id = users.id
         WHERE users.id = :id AND users.role = "student"
         LIMIT 1'
    );
    $s->execute([':id' => $studentId]);
    $row = $s->fetch();
    return $row ?: null;
}

function student_portal_upcoming_lesson(int $studentId): ?array
{
    $s = db()->prepare(
        'SELECT bookings.*, lesson_packages.package_name
         FROM bookings
         LEFT JOIN lesson_packages ON lesson_packages.id = bookings.package_id
         WHERE bookings.student_user_id = :student
           AND bookings.status IN ("requested","confirmed","reschedule_requested","rescheduled")
           AND bookings.start_at >= NOW()
         ORDER BY bookings.start_at ASC
         LIMIT 1'
    );
    $s->execute([':student' => $studentId]);
    $row = $s->fetch();
    return $row ?: null;
}

function student_portal_current_homework(int $studentId): ?array
{
    $s = db()->prepare(
        'SELECT homeworks.*,
                homework_submissions.status AS submission_status,
                homework_submissions.submitted_at
         FROM homeworks
         LEFT JOIN homework_submissions ON homework_submissions.homework_id = homeworks.id AND homework_submissions.student_user_id = :student_a
         WHERE homeworks.student_user_id = :student_b
           AND homeworks.status = "published"
           AND homework_submissions.id IS NULL
         ORDER BY COALESCE(homeworks.due_at, homeworks.created_at) ASC
         LIMIT 1'
    );
    $s->execute([':student_a' => $studentId, ':student_b' => $studentId]);
    $row = $s->fetch();
    return $row ?: null;
}

function student_portal_homeworks(int $studentId): array
{
    $s = db()->prepare(
        'SELECT homeworks.*,
                homework_submissions.status AS submission_status,
                homework_submissions.feedback,
                homework_submissions.submitted_at,
                homework_submissions.reviewed_at
         FROM homeworks
         LEFT JOIN homework_submissions ON homework_submissions.homework_id = homeworks.id AND homework_submissions.student_user_id = :student_a
         WHERE homeworks.student_user_id = :student_b
         ORDER BY COALESCE(homeworks.due_at, homeworks.created_at) DESC
         LIMIT 200'
    );
    $s->execute([':student_a' => $studentId, ':student_b' => $studentId]);
    return $s->fetchAll();
}

function student_portal_current_scenario(int $studentId): ?array
{
    $s = db()->prepare(
        'SELECT scenarios.*,
                scenario_submissions.submitted_at,
                scenario_submissions.score
         FROM scenarios
         LEFT JOIN scenario_submissions ON scenario_submissions.scenario_id = scenarios.id AND scenario_submissions.student_user_id = :student_a
         WHERE scenarios.student_user_id = :student_b
           AND scenarios.status = "published"
           AND scenario_submissions.id IS NULL
         ORDER BY scenarios.created_at DESC
         LIMIT 1'
    );
    $s->execute([':student_a' => $studentId, ':student_b' => $studentId]);
    $row = $s->fetch();
    return $row ?: null;
}

function student_portal_scenarios(int $studentId): array
{
    $s = db()->prepare(
        'SELECT scenarios.*,
                scenario_submissions.submitted_at,
                scenario_submissions.feedback,
                scenario_submissions.score
         FROM scenarios
         LEFT JOIN scenario_submissions ON scenario_submissions.scenario_id = scenarios.id AND scenario_submissions.student_user_id = :student_a
         WHERE scenarios.student_user_id = :student_b
         ORDER BY scenarios.created_at DESC
         LIMIT 200'
    );
    $s->execute([':student_a' => $studentId, ':student_b' => $studentId]);
    return $s->fetchAll();
}

function student_portal_reviews(int $studentId): array
{
    $s = db()->prepare(
        'SELECT review_tests.*,
                review_submissions.score,
                review_submissions.feedback,
                review_submissions.submitted_at,
                review_submissions.reviewed_at
         FROM review_tests
         LEFT JOIN review_submissions ON review_submissions.review_test_id = review_tests.id AND review_submissions.student_user_id = :student_a
         WHERE review_tests.student_user_id = :student_b
         ORDER BY review_tests.created_at DESC
         LIMIT 200'
    );
    $s->execute([':student_a' => $studentId, ':student_b' => $studentId]);
    return $s->fetchAll();
}

function student_portal_materials(int $studentId): array
{
    $profile = student_portal_profile($studentId);
    $level = $profile['current_level'] ?? null;
    $s = db()->prepare(
        'SELECT * FROM course_materials
         WHERE is_active = 1 AND (level IS NULL OR level = "" OR level = :level)
         ORDER BY created_at DESC
         LIMIT 200'
    );
    $s->execute([':level' => $level]);
    return $s->fetchAll();
}

function student_portal_practice_words(int $studentId): array
{
    $s = db()->prepare(
        'SELECT practice_words.*,
                MAX(flashcard_reviews.reviewed_at) AS last_reviewed_at,
                MIN(flashcard_reviews.next_review_at) AS next_review_at
         FROM practice_words
         LEFT JOIN flashcard_reviews ON flashcard_reviews.practice_word_id = practice_words.id AND flashcard_reviews.student_user_id = :student_a
         WHERE practice_words.student_user_id = :student_b
         GROUP BY practice_words.id
         ORDER BY COALESCE(next_review_at, practice_words.created_at) ASC
         LIMIT 200'
    );
    $s->execute([':student_a' => $studentId, ':student_b' => $studentId]);
    return $s->fetchAll();
}

function student_portal_words_due_count(int $studentId): int
{
    $s = db()->prepare(
        'SELECT COUNT(*) FROM practice_words
         LEFT JOIN flashcard_reviews ON flashcard_reviews.practice_word_id = practice_words.id AND flashcard_reviews.student_user_id = practice_words.student_user_id
         WHERE practice_words.student_user_id = :student
           AND (flashcard_reviews.next_review_at IS NULL OR flashcard_reviews.next_review_at <= NOW())'
    );
    $s->execute([':student' => $studentId]);
    return (int) $s->fetchColumn();
}

function student_portal_notifications(int $studentId, int $limit = 100): array
{
    $s = db()->prepare('SELECT * FROM notifications WHERE user_id = :student ORDER BY created_at DESC LIMIT ' . (int) $limit);
    $s->execute([':student' => $studentId]);
    return $s->fetchAll();
}

function student_portal_unread_notifications_count(int $studentId): int
{
    $s = db()->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = :student AND read_at IS NULL');
    $s->execute([':student' => $studentId]);
    return (int) $s->fetchColumn();
}

function student_portal_badges(int $studentId): array
{
    $s = db()->prepare(
        'SELECT badge_definitions.*, student_badges.awarded_at
         FROM student_badges
         INNER JOIN badge_definitions ON badge_definitions.id = student_badges.badge_definition_id
         WHERE student_badges.student_user_id = :student
         ORDER BY student_badges.awarded_at DESC
         LIMIT 50'
    );
    $s->execute([':student' => $studentId]);
    return $s->fetchAll();
}

function student_portal_session_notes(int $studentId): array
{
    $s = db()->prepare(
        'SELECT lesson_sessions.*
         FROM lesson_sessions
         WHERE lesson_sessions.student_user_id = :student
           AND lesson_sessions.notes IS NOT NULL
           AND lesson_sessions.notes != ""
         ORDER BY lesson_sessions.start_at DESC
         LIMIT 100'
    );
    $s->execute([':student' => $studentId]);
    return $s->fetchAll();
}

function student_portal_referrals(int $studentId): array
{
    $s = db()->prepare('SELECT * FROM referrals WHERE referrer_user_id = :student ORDER BY created_at DESC LIMIT 100');
    $s->execute([':student' => $studentId]);
    return $s->fetchAll();
}

function student_portal_progress_summary(int $studentId): array
{
    $sessions = db()->prepare('SELECT COUNT(*) FROM lesson_sessions WHERE student_user_id = :student AND status = "completed"');
    $sessions->execute([':student' => $studentId]);

    $homeworks = db()->prepare('SELECT COUNT(*) FROM homework_submissions WHERE student_user_id = :student');
    $homeworks->execute([':student' => $studentId]);

    $scenarios = db()->prepare('SELECT COUNT(*) FROM scenario_submissions WHERE student_user_id = :student');
    $scenarios->execute([':student' => $studentId]);

    return [
        'completed_sessions' => (int) $sessions->fetchColumn(),
        'submitted_homeworks' => (int) $homeworks->fetchColumn(),
        'submitted_scenarios' => (int) $scenarios->fetchColumn(),
    ];
}

function student_portal_streak(int $studentId): int
{
    $s = db()->prepare(
        'SELECT DISTINCT DATE(created_at) AS activity_date
         FROM notifications
         WHERE user_id = :student
         ORDER BY activity_date DESC
         LIMIT 30'
    );
    $s->execute([':student' => $studentId]);
    $dates = array_column($s->fetchAll(), 'activity_date');
    if (!$dates) return 0;

    $streak = 0;
    $cursor = new DateTime('today');
    foreach ($dates as $date) {
        if ($date === $cursor->format('Y-m-d')) {
            $streak++;
            $cursor->modify('-1 day');
        }
    }
    return $streak;
}

function student_portal_dashboard(int $studentId): array
{
    return [
        'profile' => student_portal_profile($studentId),
        'upcoming_lesson' => student_portal_upcoming_lesson($studentId),
        'balance' => credits_student_summary($studentId),
        'current_homework' => student_portal_current_homework($studentId),
        'current_scenario' => student_portal_current_scenario($studentId),
        'reviews' => array_slice(student_portal_reviews($studentId), 0, 3),
        'progress' => student_portal_progress_summary($studentId),
        'streak' => student_portal_streak($studentId),
        'badges' => array_slice(student_portal_badges($studentId), 0, 4),
        'practice_due' => student_portal_words_due_count($studentId),
        'session_notes' => array_slice(student_portal_session_notes($studentId), 0, 3),
        'notifications' => array_slice(student_portal_notifications($studentId, 5), 0, 5),
        'unread_notifications' => student_portal_unread_notifications_count($studentId),
    ];
}

function student_portal_card(string $title, string $body, string $buttonLabel = '', string $buttonHref = ''): string
{
    $button = $buttonLabel && $buttonHref ? '<a class="btn btn-sm btn-outline-brand mt-2" href="' . htmlspecialchars($buttonHref, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($buttonLabel, ENT_QUOTES, 'UTF-8') . '</a>' : '';
    return '<div class="status-box h-100"><h2 class="h5 fw-bold">' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h2><div class="text-muted">' . $body . '</div>' . $button . '</div>';
}
