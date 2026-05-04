<?php
/**
 * Phase 15 Learning Engagement helper.
 * Practice words, flashcards, activity log, streaks, badges, and progress.
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/AuditLogger.php';
require_once __DIR__ . '/StudentPortal.php';
require_once __DIR__ . '/ParentPortal.php';

function engagement_log_activity(int $studentId, string $type, ?string $sourceType = null, ?string $sourceId = null, array $metadata = [], float $points = 1): void
{
    db()->prepare(
        'INSERT INTO learning_activity_logs (student_user_id, activity_type, source_type, source_id, points, metadata, activity_date)
         VALUES (:student, :type, :source_type, :source_id, :points, :metadata, CURDATE())'
    )->execute([
        ':student' => $studentId,
        ':type' => $type,
        ':source_type' => $sourceType,
        ':source_id' => $sourceId,
        ':points' => $points,
        ':metadata' => json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    ]);

    engagement_award_badges($studentId);
}

function engagement_streak_days(int $studentId): int
{
    $s = db()->prepare('SELECT DISTINCT activity_date FROM learning_activity_logs WHERE student_user_id = :student ORDER BY activity_date DESC LIMIT 120');
    $s->execute([':student' => $studentId]);
    $dates = array_column($s->fetchAll(), 'activity_date');
    if (!$dates) return 0;

    $dateSet = array_flip($dates);
    $cursor = new DateTime('today');
    $streak = 0;

    while (isset($dateSet[$cursor->format('Y-m-d')])) {
        $streak++;
        $cursor->modify('-1 day');
    }

    return $streak;
}

function engagement_activity_count(int $studentId, ?string $type = null, ?int $days = null): int
{
    $sql = 'SELECT COUNT(*) FROM learning_activity_logs WHERE student_user_id = :student';
    $params = [':student' => $studentId];
    if ($type) { $sql .= ' AND activity_type = :type'; $params[':type'] = $type; }
    if ($days) { $sql .= ' AND activity_date >= DATE_SUB(CURDATE(), INTERVAL ' . (int) $days . ' DAY)'; }
    $s = db()->prepare($sql);
    $s->execute($params);
    return (int) $s->fetchColumn();
}

function engagement_completed_sessions_count(int $studentId): int
{
    $s = db()->prepare('SELECT COUNT(*) FROM lesson_sessions WHERE student_user_id = :student AND status = "completed"');
    $s->execute([':student' => $studentId]);
    return (int) $s->fetchColumn();
}

function engagement_practice_words_mastered_count(int $studentId): int
{
    $s = db()->prepare('SELECT COUNT(*) FROM practice_words WHERE student_user_id = :student AND (due_status = "mastered" OR mastery_level >= 5)');
    $s->execute([':student' => $studentId]);
    return (int) $s->fetchColumn();
}

function engagement_badge_metric_value(int $studentId, string $triggerType): int
{
    return match ($triggerType) {
        'activity_count' => engagement_activity_count($studentId, null, null),
        'streak_days' => engagement_streak_days($studentId),
        'sessions_completed' => engagement_completed_sessions_count($studentId),
        'practice_words_mastered' => engagement_practice_words_mastered_count($studentId),
        'scenarios_submitted' => engagement_activity_count($studentId, 'scenario_submitted'),
        'homework_submitted' => engagement_activity_count($studentId, 'homework_submitted'),
        'level_check_completed' => engagement_activity_count($studentId, 'level_check_completed'),
        default => 0,
    };
}

function engagement_award_badges(int $studentId): array
{
    $badges = db()->query('SELECT * FROM badge_definitions WHERE is_active = 1 ORDER BY display_order ASC, id ASC')->fetchAll();
    $awarded = [];

    foreach ($badges as $badge) {
        if ($badge['trigger_type'] === 'manual') continue;
        $metric = engagement_badge_metric_value($studentId, $badge['trigger_type']);
        if ($metric < (int) $badge['required_value']) continue;

        $exists = db()->prepare('SELECT id FROM student_badges WHERE student_user_id = :student AND badge_definition_id = :badge LIMIT 1');
        $exists->execute([':student' => $studentId, ':badge' => (int) $badge['id']]);
        if ($exists->fetch()) continue;

        db()->prepare('INSERT INTO student_badges (student_user_id, badge_definition_id, awarded_at, source_type, source_id) VALUES (:student, :badge, NOW(), :source_type, :source_id)')
            ->execute([':student' => $studentId, ':badge' => (int) $badge['id'], ':source_type' => $badge['trigger_type'], ':source_id' => (string) $metric]);
        $awarded[] = $badge;

        db()->prepare('INSERT INTO learning_activity_logs (student_user_id, activity_type, source_type, source_id, points, metadata, activity_date) VALUES (:student, "badge_awarded", "badge", :badge, 1, :metadata, CURDATE())')
            ->execute([':student' => $studentId, ':badge' => (string) $badge['id'], ':metadata' => json_encode(['badge_key' => $badge['badge_key']], JSON_UNESCAPED_UNICODE)]);
    }

    return $awarded;
}

function engagement_add_practice_word(int $ownerId, int $studentId, array $post): int
{
    db()->prepare(
        'INSERT INTO practice_words (student_user_id, word_ar, word_en, example_sentence_ar, source, mastery_level, next_review_at, due_status)
         VALUES (:student, :ar, :en, :example, :source, 0, NOW(), "due")'
    )->execute([
        ':student' => $studentId,
        ':ar' => trim($post['word_ar'] ?? ''),
        ':en' => trim($post['word_en'] ?? '') ?: null,
        ':example' => trim($post['example_sentence_ar'] ?? '') ?: null,
        ':source' => trim($post['source'] ?? 'owner_manual') ?: 'owner_manual',
    ]);

    $id = (int) db()->lastInsertId();
    engagement_log_activity($studentId, 'practice_word_added', 'practice_word', (string) $id, ['created_by_user_id' => $ownerId], 0);
    audit_log($ownerId, 'practice_word_added', 'practice_word', (string) $id, ['student_user_id' => $studentId]);
    return $id;
}

function engagement_due_flashcards(int $studentId): array
{
    $s = db()->prepare(
        'SELECT * FROM practice_words
         WHERE student_user_id = :student
           AND due_status != "mastered"
           AND (next_review_at IS NULL OR next_review_at <= NOW())
         ORDER BY COALESCE(next_review_at, created_at) ASC, id ASC'
    );
    $s->execute([':student' => $studentId]);
    return $s->fetchAll();
}

function engagement_all_practice_words(int $studentId): array
{
    $s = db()->prepare('SELECT * FROM practice_words WHERE student_user_id = :student ORDER BY due_status ASC, next_review_at ASC, created_at DESC');
    $s->execute([':student' => $studentId]);
    return $s->fetchAll();
}

function engagement_review_flashcard(int $studentId, int $wordId, string $rating): void
{
    if (!in_array($rating, ['got_it', 'almost', 'missed'], true)) throw new RuntimeException('Invalid flashcard rating.');

    $s = db()->prepare('SELECT * FROM practice_words WHERE id = :id AND student_user_id = :student LIMIT 1');
    $s->execute([':id' => $wordId, ':student' => $studentId]);
    $word = $s->fetch();
    if (!$word) throw new RuntimeException('Practice word not found.');

    $currentMastery = (int) $word['mastery_level'];
    $mastery = match ($rating) {
        'got_it' => min(5, $currentMastery + 1),
        'almost' => max(1, $currentMastery),
        'missed' => max(0, $currentMastery - 1),
    };

    $next = match ($rating) {
        'got_it' => 'DATE_ADD(NOW(), INTERVAL 3 DAY)',
        'almost' => 'DATE_ADD(NOW(), INTERVAL 1 DAY)',
        'missed' => 'DATE_ADD(NOW(), INTERVAL 12 HOUR)',
    };
    $status = $mastery >= 5 ? 'mastered' : 'scheduled';

    db()->prepare('INSERT INTO flashcard_reviews (practice_word_id, student_user_id, rating, reviewed_at, previous_review_at, next_review_at) VALUES (:word, :student, :rating, NOW(), :previous, ' . $next . ')')
        ->execute([':word' => $wordId, ':student' => $studentId, ':rating' => $rating, ':previous' => $word['last_reviewed_at'] ?: null]);

    db()->prepare('UPDATE practice_words SET mastery_level = :mastery, last_reviewed_at = NOW(), next_review_at = ' . $next . ', due_status = :status WHERE id = :id AND student_user_id = :student')
        ->execute([':mastery' => $mastery, ':status' => $status, ':id' => $wordId, ':student' => $studentId]);

    engagement_log_activity($studentId, 'flashcards_reviewed', 'practice_word', (string) $wordId, ['rating' => $rating, 'mastery_level' => $mastery]);
}

function engagement_progress(int $studentId): array
{
    $summary = student_portal_progress_summary($studentId);
    return [
        'summary' => $summary,
        'streak' => engagement_streak_days($studentId),
        'activity_count' => engagement_activity_count($studentId),
        'week_activity_count' => engagement_activity_count($studentId, null, 7),
        'completed_sessions' => engagement_completed_sessions_count($studentId),
        'due_flashcards' => count(engagement_due_flashcards($studentId)),
        'practice_words_total' => count(engagement_all_practice_words($studentId)),
        'practice_words_mastered' => engagement_practice_words_mastered_count($studentId),
        'badges' => student_portal_badges($studentId),
        'recent_activity' => engagement_recent_activity($studentId),
    ];
}

function engagement_recent_activity(int $studentId, int $limit = 30): array
{
    $s = db()->prepare('SELECT * FROM learning_activity_logs WHERE student_user_id = :student ORDER BY created_at DESC LIMIT ' . (int) $limit);
    $s->execute([':student' => $studentId]);
    return $s->fetchAll();
}

function engagement_badge_definitions(): array
{
    return db()->query('SELECT * FROM badge_definitions ORDER BY display_order ASC, id ASC')->fetchAll();
}

function engagement_update_badge(int $ownerId, int $badgeId, array $post): void
{
    db()->prepare(
        'UPDATE badge_definitions
         SET name_en = :name_en, name_ar = :name_ar, description_en = :description_en, description_ar = :description_ar,
             icon = :icon, trigger_type = :trigger_type, required_value = :required_value, is_active = :is_active,
             display_order = :display_order, visibility = :visibility, color_style = :color_style
         WHERE id = :id'
    )->execute([
        ':name_en' => trim($post['name_en'] ?? ''),
        ':name_ar' => trim($post['name_ar'] ?? ''),
        ':description_en' => trim($post['description_en'] ?? ''),
        ':description_ar' => trim($post['description_ar'] ?? ''),
        ':icon' => trim($post['icon'] ?? ''),
        ':trigger_type' => $post['trigger_type'] ?? 'manual',
        ':required_value' => (int) ($post['required_value'] ?? 1),
        ':is_active' => isset($post['is_active']) ? 1 : 0,
        ':display_order' => (int) ($post['display_order'] ?? 0),
        ':visibility' => $post['visibility'] ?? 'student_parent',
        ':color_style' => trim($post['color_style'] ?? '') ?: null,
        ':id' => $badgeId,
    ]);
    audit_log($ownerId, 'badge_setting_updated', 'badge_definition', (string) $badgeId, []);
}
