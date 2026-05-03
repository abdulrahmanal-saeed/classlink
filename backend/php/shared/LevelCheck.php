<?php
/**
 * Level check helper for Phase 6.
 *
 * Contains question sets, auto-grading, upload validation, and Owner review helpers.
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/AuditLogger.php';
require_once __DIR__ . '/Onboarding.php';

function level_check_adult_questions(): array
{
    return [
        'vocabulary' => [
            ['key' => 'v1', 'q' => 'What does بيت mean?', 'options' => ['House', 'Book', 'Car'], 'answer' => 'House'],
            ['key' => 'v2', 'q' => 'What does ماء mean?', 'options' => ['Water', 'Food', 'Door'], 'answer' => 'Water'],
            ['key' => 'v3', 'q' => 'What does أكل mean?', 'options' => ['He ate', 'He slept', 'He wrote'], 'answer' => 'He ate'],
            ['key' => 'v4', 'q' => 'What does مدرسة mean?', 'options' => ['School', 'Hospital', 'Market'], 'answer' => 'School'],
            ['key' => 'v5', 'q' => 'What does كبير mean?', 'options' => ['Big', 'Small', 'Fast'], 'answer' => 'Big'],
            ['key' => 'v6', 'q' => 'What does الآن mean?', 'options' => ['Now', 'Yesterday', 'Tomorrow'], 'answer' => 'Now'],
            ['key' => 'v7', 'q' => 'What does صديق mean?', 'options' => ['Friend', 'Teacher', 'Student'], 'answer' => 'Friend'],
            ['key' => 'v8', 'q' => 'What does عمل mean?', 'options' => ['Work', 'Color', 'Street'], 'answer' => 'Work'],
            ['key' => 'v9', 'q' => 'What does أنا mean?', 'options' => ['I', 'You', 'He'], 'answer' => 'I'],
            ['key' => 'v10', 'q' => 'What does شكراً mean?', 'options' => ['Thank you', 'Sorry', 'Goodbye'], 'answer' => 'Thank you'],
        ],
        'sentence' => [
            ['key' => 's1', 'q' => 'Choose the correct sentence: I live in Dubai.', 'options' => ['أنا أسكن في دبي', 'دبي في أسكن أنا', 'في أنا دبي أسكن'], 'answer' => 'أنا أسكن في دبي'],
            ['key' => 's2', 'q' => 'Choose: I want water.', 'options' => ['أنا أريد ماء', 'ماء أريد هو', 'أريد أنا كتاب'], 'answer' => 'أنا أريد ماء'],
            ['key' => 's3', 'q' => 'Choose: This is my friend.', 'options' => ['هذا صديقي', 'أنا صديقي هذا', 'صديقي في هذا'], 'answer' => 'هذا صديقي'],
            ['key' => 's4', 'q' => 'Choose: Where do you work?', 'options' => ['أين تعمل؟', 'متى تعمل؟', 'كيف تعمل؟'], 'answer' => 'أين تعمل؟'],
            ['key' => 's5', 'q' => 'Choose: I am learning Arabic.', 'options' => ['أنا أتعلم العربية', 'العربية أنا بيت', 'أتعلم في أنا'], 'answer' => 'أنا أتعلم العربية'],
        ],
        'reading' => [
            'text' => 'اسمي أحمد. أنا من مصر وأسكن في دبي. أعمل في شركة صغيرة. أحب تعلم اللغة العربية كل يوم.',
            'questions' => [
                ['key' => 'r1', 'q' => 'Where does Ahmed live?', 'options' => ['Dubai', 'Cairo', 'School'], 'answer' => 'Dubai'],
                ['key' => 'r2', 'q' => 'Where is Ahmed from?', 'options' => ['Egypt', 'UAE', 'Jordan'], 'answer' => 'Egypt'],
                ['key' => 'r3', 'q' => 'What does Ahmed like?', 'options' => ['Learning Arabic', 'Playing football', 'Cooking'], 'answer' => 'Learning Arabic'],
            ],
        ],
    ];
}

function level_check_child_questions(): array
{
    return [
        'letters' => [
            ['key' => 'l1', 'q' => 'Choose the letter: ب', 'options' => ['ب', 'ت', 'ث'], 'answer' => 'ب'],
            ['key' => 'l2', 'q' => 'Choose the letter: ج', 'options' => ['ح', 'ج', 'خ'], 'answer' => 'ج'],
            ['key' => 'l3', 'q' => 'Choose the letter: د', 'options' => ['د', 'ذ', 'ر'], 'answer' => 'د'],
            ['key' => 'l4', 'q' => 'Choose the letter: س', 'options' => ['ش', 'ص', 'س'], 'answer' => 'س'],
            ['key' => 'l5', 'q' => 'Choose the letter: ع', 'options' => ['غ', 'ع', 'ف'], 'answer' => 'ع'],
            ['key' => 'l6', 'q' => 'Choose the letter: ق', 'options' => ['ف', 'ق', 'ك'], 'answer' => 'ق'],
            ['key' => 'l7', 'q' => 'Choose the letter: م', 'options' => ['م', 'ن', 'هـ'], 'answer' => 'م'],
            ['key' => 'l8', 'q' => 'Choose the letter: و', 'options' => ['و', 'ي', 'ا'], 'answer' => 'و'],
            ['key' => 'l9', 'q' => 'Choose the letter: ط', 'options' => ['ظ', 'ط', 'ض'], 'answer' => 'ط'],
            ['key' => 'l10', 'q' => 'Choose the letter: ص', 'options' => ['س', 'ش', 'ص'], 'answer' => 'ص'],
        ],
        'similar' => [
            ['key' => 'sim1', 'q' => 'Which letter has one dot under?', 'options' => ['ب', 'ت', 'ث'], 'answer' => 'ب'],
            ['key' => 'sim2', 'q' => 'Which letter has three dots above?', 'options' => ['ث', 'ت', 'ب'], 'answer' => 'ث'],
            ['key' => 'sim3', 'q' => 'Which letter is خ?', 'options' => ['ج', 'ح', 'خ'], 'answer' => 'خ'],
            ['key' => 'sim4', 'q' => 'Which letter is ض?', 'options' => ['ص', 'ض', 'ط'], 'answer' => 'ض'],
            ['key' => 'sim5', 'q' => 'Which letter is ظ?', 'options' => ['ط', 'ظ', 'ض'], 'answer' => 'ظ'],
        ],
    ];
}

function level_score_percent(int $correct, int $total): float
{
    return $total > 0 ? round(($correct / $total) * 100, 2) : 0.0;
}

function level_adult_suggested_level(float $score): string
{
    if ($score <= 30) return 'A0 Complete Beginner';
    if ($score <= 55) return 'A1';
    if ($score <= 75) return 'A2';
    if ($score <= 90) return 'Strong A2 / B1 Activation';
    return 'B1 / Needs Speaking Assessment';
}

function level_child_suggested_level(float $letterScore, array $parentPayload): string
{
    if ($letterScore <= 10) return 'Level 0: cannot recognize letters';
    if ($letterScore <= 45) return 'Level 1: recognizes some letters';
    if ($letterScore <= 70) return 'Level 2: recognizes letters but struggles with connections';
    if ($letterScore <= 90) return 'Level 3: can read simple words';
    return 'Level 4: can read/write simple sentences with support';
}

function level_recommended_first_lesson(string $attemptType, string $suggestedLevel): string
{
    if ($attemptType === 'child_literacy') {
        return 'Start with letter recognition, similar letters, and simple sound-to-letter practice based on the child literacy level.';
    }

    return 'Start with a speaking-first diagnostic lesson: introductions, daily-life questions, and targeted correction before confirming final level.';
}

function level_get_intake(int $intakeId): ?array
{
    return onboarding_form_detail($intakeId);
}

function level_allowed_uploads(): array
{
    return [
        'audio' => [
            'extensions' => ['mp3', 'wav', 'm4a', 'webm'],
            'mimes' => ['audio/mpeg', 'audio/wav', 'audio/x-wav', 'audio/mp4', 'audio/webm', 'video/webm'],
            'max_mb' => 25,
        ],
        'document' => [
            'extensions' => ['jpg', 'jpeg', 'png', 'pdf'],
            'mimes' => ['image/jpeg', 'image/png', 'application/pdf'],
            'max_mb' => 10,
        ],
    ];
}

function level_store_upload(int $attemptId, string $fieldName, string $purpose, string $kind): ?array
{
    if (empty($_FILES[$fieldName]) || ($_FILES[$fieldName]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    $file = $_FILES[$fieldName];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload failed for ' . $fieldName);
    }

    $rules = level_allowed_uploads()[$kind] ?? null;
    if (!$rules) {
        throw new RuntimeException('Invalid upload type.');
    }

    $sizeBytes = (int) $file['size'];
    if ($sizeBytes > ($rules['max_mb'] * 1024 * 1024)) {
        throw new RuntimeException('File is larger than allowed limit: ' . $rules['max_mb'] . 'MB.');
    }

    $original = $file['name'];
    $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
    if (!in_array($ext, $rules['extensions'], true)) {
        throw new RuntimeException('Invalid file extension: ' . $ext);
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']) ?: '';
    if (!in_array($mime, $rules['mimes'], true)) {
        throw new RuntimeException('Invalid file MIME type: ' . $mime);
    }

    $baseDir = dirname(__DIR__, 3) . '/uploads/level-checks/' . $attemptId;
    if (!is_dir($baseDir)) {
        mkdir($baseDir, 0755, true);
    }

    $safeName = $purpose . '-' . bin2hex(random_bytes(6)) . '.' . $ext;
    $target = $baseDir . '/' . $safeName;
    if (!move_uploaded_file($file['tmp_name'], $target)) {
        throw new RuntimeException('Could not save uploaded file.');
    }

    $relativePath = 'uploads/level-checks/' . $attemptId . '/' . $safeName;

    db()->prepare(
        'INSERT INTO level_check_uploads (attempt_id, file_path, original_filename, file_type, mime_type, size_bytes, purpose)
         VALUES (:attempt_id, :file_path, :original_filename, :file_type, :mime_type, :size_bytes, :purpose)'
    )->execute([
        ':attempt_id' => $attemptId,
        ':file_path' => $relativePath,
        ':original_filename' => $original,
        ':file_type' => $kind,
        ':mime_type' => $mime,
        ':size_bytes' => $sizeBytes,
        ':purpose' => $purpose,
    ]);

    return ['path' => $relativePath, 'mime' => $mime, 'size' => $sizeBytes];
}

function level_create_attempt_from_intake(array $intake, string $attemptType, array $scores, string $suggestedLevel, string $recommendedLesson): int
{
    $statement = db()->prepare(
        'INSERT INTO level_check_attempts
          (intake_form_id, checkout_reference, user_id, attempt_type, status, auto_score, vocabulary_score, sentence_score, reading_score, letter_score, suggested_level, recommended_first_lesson, submitted_at)
         VALUES
          (:intake_form_id, :checkout_reference, NULL, :attempt_type, "submitted", :auto_score, :vocabulary_score, :sentence_score, :reading_score, :letter_score, :suggested_level, :recommended_first_lesson, NOW())'
    );

    $statement->execute([
        ':intake_form_id' => (int) $intake['id'],
        ':checkout_reference' => $intake['checkout_reference'],
        ':attempt_type' => $attemptType,
        ':auto_score' => $scores['auto'] ?? null,
        ':vocabulary_score' => $scores['vocabulary'] ?? null,
        ':sentence_score' => $scores['sentence'] ?? null,
        ':reading_score' => $scores['reading'] ?? null,
        ':letter_score' => $scores['letter'] ?? null,
        ':suggested_level' => $suggestedLevel,
        ':recommended_first_lesson' => $recommendedLesson,
    ]);

    return (int) db()->lastInsertId();
}

function level_save_answer(int $attemptId, string $section, string $key, string $question, ?string $answer, ?string $correct, ?float $score, array $metadata = []): void
{
    db()->prepare(
        'INSERT INTO level_check_answers (attempt_id, section_key, question_key, question_text, answer_text, correct_answer, is_correct, score, metadata)
         VALUES (:attempt_id, :section_key, :question_key, :question_text, :answer_text, :correct_answer, :is_correct, :score, :metadata)'
    )->execute([
        ':attempt_id' => $attemptId,
        ':section_key' => $section,
        ':question_key' => $key,
        ':question_text' => $question,
        ':answer_text' => $answer,
        ':correct_answer' => $correct,
        ':is_correct' => $correct === null ? null : (int) ($answer === $correct),
        ':score' => $score,
        ':metadata' => $metadata ? json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
    ]);
}

function level_mark_purchase_submitted(int $purchaseId): void
{
    db()->prepare('UPDATE purchases SET level_check_status = "submitted" WHERE id = :purchase_id')->execute([':purchase_id' => $purchaseId]);
}

function level_attempt_detail(int $attemptId): ?array
{
    $statement = db()->prepare(
        'SELECT level_check_attempts.*, student_intake_forms.raw_payload, student_intake_forms.learner_name, purchases.full_name, purchases.email, purchases.whatsapp, purchases.status AS purchase_status, plans.name_en AS plan_name
         FROM level_check_attempts
         LEFT JOIN student_intake_forms ON student_intake_forms.id = level_check_attempts.intake_form_id
         LEFT JOIN purchases ON purchases.id = student_intake_forms.purchase_id
         LEFT JOIN plans ON plans.id = purchases.plan_id
         WHERE level_check_attempts.id = :id
         LIMIT 1'
    );
    $statement->execute([':id' => $attemptId]);
    $attempt = $statement->fetch();

    return $attempt ?: null;
}

function level_attempt_answers(int $attemptId): array
{
    $statement = db()->prepare('SELECT * FROM level_check_answers WHERE attempt_id = :id ORDER BY id ASC');
    $statement->execute([':id' => $attemptId]);
    return $statement->fetchAll();
}

function level_attempt_uploads(int $attemptId): array
{
    $statement = db()->prepare('SELECT * FROM level_check_uploads WHERE attempt_id = :id ORDER BY id ASC');
    $statement->execute([':id' => $attemptId]);
    return $statement->fetchAll();
}

function level_latest_attempts(): array
{
    return db()->query(
        'SELECT level_check_attempts.*, student_intake_forms.learner_name, purchases.full_name, purchases.email, plans.name_en AS plan_name
         FROM level_check_attempts
         LEFT JOIN student_intake_forms ON student_intake_forms.id = level_check_attempts.intake_form_id
         LEFT JOIN purchases ON purchases.id = student_intake_forms.purchase_id
         LEFT JOIN plans ON plans.id = purchases.plan_id
         ORDER BY level_check_attempts.created_at DESC
         LIMIT 150'
    )->fetchAll();
}

function level_owner_mark_reviewed(int $attemptId, string $finalLevel, ?float $manualScore, string $teacherNotes, int $ownerUserId): void
{
    $attempt = level_attempt_detail($attemptId);
    if (!$attempt) {
        throw new RuntimeException('Attempt not found.');
    }

    db()->prepare(
        'UPDATE level_check_attempts SET status = "reviewed", final_level = :final_level, manual_score = :manual_score, teacher_notes = :teacher_notes, reviewer_user_id = :reviewer_user_id, reviewed_at = NOW() WHERE id = :id'
    )->execute([
        ':final_level' => $finalLevel,
        ':manual_score' => $manualScore,
        ':teacher_notes' => $teacherNotes ?: null,
        ':reviewer_user_id' => $ownerUserId,
        ':id' => $attemptId,
    ]);

    if (!empty($attempt['intake_form_id'])) {
        $intake = onboarding_form_detail((int) $attempt['intake_form_id']);
        if ($intake && !empty($intake['purchase_id'])) {
            db()->prepare('UPDATE purchases SET level_check_status = "reviewed" WHERE id = :purchase_id')
                ->execute([':purchase_id' => (int) $intake['purchase_id']]);
        }
    }

    audit_log($ownerUserId, 'level_check_reviewed', 'level_check_attempt', (string) $attemptId, [
        'final_level' => $finalLevel,
        'manual_score' => $manualScore,
    ]);
}
