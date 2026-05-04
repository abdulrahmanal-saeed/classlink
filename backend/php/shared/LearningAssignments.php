<?php
/**
 * Phase 14 Learning Assignments helper.
 * Handles homework, scenarios, reviews/tests, materials, submissions, scoring, and results.
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/AuditLogger.php';
require_once __DIR__ . '/StudentPortal.php';

function learning_students_for_select(): array
{
    return db()->query('SELECT users.id, users.display_name, users.email, student_profiles.current_level FROM users LEFT JOIN student_profiles ON student_profiles.user_id = users.id WHERE users.role = "student" AND users.status = "active" ORDER BY users.display_name ASC')->fetchAll();
}

function learning_json(array $data): string
{
    return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

function learning_parse_options(?string $raw): array
{
    if (!$raw) return [];
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

function learning_create_homework(int $ownerId, array $post): int
{
    $studentId = (int) ($post['student_user_id'] ?? 0);
    if (!$studentId) throw new RuntimeException('Student is required.');
    db()->prepare('INSERT INTO homeworks (student_user_id, created_by_user_id, title, instructions, status, due_at) VALUES (:student, :owner, :title, :instructions, :status, :due_at)')
        ->execute([
            ':student' => $studentId,
            ':owner' => $ownerId,
            ':title' => trim($post['title'] ?? 'Homework'),
            ':instructions' => trim($post['instructions'] ?? ''),
            ':status' => $post['status'] ?? 'draft',
            ':due_at' => ($post['due_at'] ?? '') ?: null,
        ]);
    $homeworkId = (int) db()->lastInsertId();

    $types = $post['question_type'] ?? [];
    foreach ($types as $i => $type) {
        $prompt = trim($post['prompt'][$i] ?? '');
        if ($prompt === '') continue;
        $options = [];
        foreach (['a','b','c','d'] as $letter) {
            $value = trim($post['option_' . $letter][$i] ?? '');
            if ($value !== '') $options[strtoupper($letter)] = $value;
        }
        db()->prepare('INSERT INTO homework_questions (homework_id, question_type, prompt, options_json, answer_key, points, media_url, explanation, sort_order) VALUES (:homework, :type, :prompt, :options, :answer, :points, :media, :explanation, :sort_order)')
            ->execute([
                ':homework' => $homeworkId,
                ':type' => $type,
                ':prompt' => $prompt,
                ':options' => $options ? learning_json($options) : null,
                ':answer' => trim($post['answer_key'][$i] ?? ''),
                ':points' => (float) ($post['points'][$i] ?? 1),
                ':media' => trim($post['media_url'][$i] ?? '') ?: null,
                ':explanation' => trim($post['explanation'][$i] ?? '') ?: null,
                ':sort_order' => $i + 1,
            ]);
    }
    audit_log($ownerId, 'homework_created', 'homework', (string) $homeworkId, ['student_user_id' => $studentId]);
    return $homeworkId;
}

function learning_homework_detail(int $homeworkId, ?int $studentId = null): ?array
{
    $sql = 'SELECT homeworks.*, users.display_name AS student_name FROM homeworks LEFT JOIN users ON users.id = homeworks.student_user_id WHERE homeworks.id = :id';
    $params = [':id' => $homeworkId];
    if ($studentId) { $sql .= ' AND homeworks.student_user_id = :student'; $params[':student'] = $studentId; }
    $s = db()->prepare($sql . ' LIMIT 1');
    $s->execute($params);
    $homework = $s->fetch();
    if (!$homework) return null;
    $q = db()->prepare('SELECT * FROM homework_questions WHERE homework_id = :id ORDER BY sort_order ASC, id ASC');
    $q->execute([':id' => $homeworkId]);
    $homework['questions'] = $q->fetchAll();
    return $homework;
}

function learning_submit_homework(int $homeworkId, int $studentId, array $post, array $files = []): int
{
    $homework = learning_homework_detail($homeworkId, $studentId);
    if (!$homework || $homework['status'] !== 'published') throw new RuntimeException('Homework not available.');
    $answers = [];
    $score = 0; $max = 0;
    foreach ($homework['questions'] as $q) {
        $qid = (int) $q['id'];
        $options = learning_parse_options($q['options_json'] ?? null);
        $answerValue = trim($post['answer'][$qid] ?? '');
        $answerText = $options[$answerValue] ?? $answerValue;
        $correctKey = trim((string) $q['answer_key']);
        $correctText = $options[$correctKey] ?? $correctKey;
        $points = (float) ($q['points'] ?? 1);
        $max += $points;
        $autoCorrectTypes = ['mcq','reading','listening'];
        $isCorrect = in_array($q['question_type'], $autoCorrectTypes, true) && $correctKey !== '' ? (mb_strtolower($answerValue) === mb_strtolower($correctKey) || mb_strtolower($answerText) === mb_strtolower($correctKey)) : null;
        if ($isCorrect === true) $score += $points;
        $answers[$qid] = [
            'question_id' => $qid,
            'question_type' => $q['question_type'],
            'prompt' => $q['prompt'],
            'selected_key' => $answerValue,
            'selected_text' => $answerText,
            'correct_key' => $correctKey,
            'correct_text' => $correctText,
            'is_correct' => $isCorrect,
            'points' => $points,
            'earned' => $isCorrect === true ? $points : 0,
            'explanation' => $q['explanation'] ?? '',
        ];
    }
    db()->prepare('INSERT INTO homework_submissions (homework_id, student_user_id, status, submitted_payload, score, max_score, submitted_at) VALUES (:homework, :student, "submitted", :payload, :score, :max_score, NOW()) ON DUPLICATE KEY UPDATE submitted_payload = VALUES(submitted_payload), score = VALUES(score), max_score = VALUES(max_score), status = "submitted", submitted_at = NOW()')
        ->execute([':homework' => $homeworkId, ':student' => $studentId, ':payload' => learning_json(['answers' => $answers]), ':score' => $score, ':max_score' => $max]);
    $id = (int) db()->lastInsertId();
    if (!$id) {
        $s = db()->prepare('SELECT id FROM homework_submissions WHERE homework_id = :homework AND student_user_id = :student LIMIT 1');
        $s->execute([':homework' => $homeworkId, ':student' => $studentId]);
        $id = (int) $s->fetchColumn();
    }
    audit_log($studentId, 'homework_submitted', 'homework', (string) $homeworkId, ['submission_id' => $id]);
    return $id;
}

function learning_homework_submission(int $homeworkId, int $studentId): ?array
{
    $s = db()->prepare('SELECT * FROM homework_submissions WHERE homework_id = :homework AND student_user_id = :student LIMIT 1');
    $s->execute([':homework' => $homeworkId, ':student' => $studentId]);
    $row = $s->fetch();
    return $row ?: null;
}

function learning_correct_homework(int $homeworkId, int $studentId, int $ownerId, array $post): void
{
    $submission = learning_homework_submission($homeworkId, $studentId);
    if (!$submission) throw new RuntimeException('Submission not found.');
    db()->prepare('UPDATE homework_submissions SET status = "corrected", score = :score, max_score = :max_score, feedback = :feedback, manual_override_payload = :override_payload, reviewed_by_user_id = :owner, reviewed_at = NOW() WHERE id = :id')
        ->execute([
            ':score' => (float) ($post['score'] ?? $submission['score']),
            ':max_score' => (float) ($post['max_score'] ?? $submission['max_score']),
            ':feedback' => trim($post['feedback'] ?? ''),
            ':override_payload' => learning_json(['note' => trim($post['override_note'] ?? ''), 'score' => $post['score'] ?? null]),
            ':owner' => $ownerId,
            ':id' => (int) $submission['id'],
        ]);
    audit_log($ownerId, 'homework_corrected', 'homework', (string) $homeworkId, ['student_user_id' => $studentId]);
}

function learning_create_scenario(int $ownerId, array $post): int
{
    db()->prepare('INSERT INTO scenarios (student_user_id, created_by_user_id, title, situation, prompt, keywords, model_answer, time_limit_seconds, status) VALUES (:student, :owner, :title, :situation, :prompt, :keywords, :model, :limit_seconds, :status)')
        ->execute([
            ':student' => (int) $post['student_user_id'], ':owner' => $ownerId, ':title' => trim($post['title'] ?? 'Speaking Scenario'), ':situation' => trim($post['situation'] ?? ''), ':prompt' => trim($post['prompt'] ?? ''), ':keywords' => trim($post['keywords'] ?? ''), ':model' => trim($post['model_answer'] ?? '') ?: null, ':limit_seconds' => (int) ($post['time_limit_seconds'] ?? 60), ':status' => $post['status'] ?? 'draft'
        ]);
    $id = (int) db()->lastInsertId();
    audit_log($ownerId, 'scenario_created', 'scenario', (string) $id, ['student_user_id' => (int) $post['student_user_id']]);
    return $id;
}

function learning_scenario_detail(int $scenarioId, ?int $studentId = null): ?array
{
    $sql = 'SELECT scenarios.*, users.display_name AS student_name FROM scenarios LEFT JOIN users ON users.id = scenarios.student_user_id WHERE scenarios.id = :id';
    $params = [':id' => $scenarioId];
    if ($studentId) { $sql .= ' AND scenarios.student_user_id = :student'; $params[':student'] = $studentId; }
    $s = db()->prepare($sql . ' LIMIT 1'); $s->execute($params); $row = $s->fetch(); return $row ?: null;
}

function learning_submit_scenario(int $scenarioId, int $studentId, array $post): int
{
    $scenario = learning_scenario_detail($scenarioId, $studentId);
    if (!$scenario || $scenario['status'] !== 'published') throw new RuntimeException('Scenario not available.');
    db()->prepare('INSERT INTO scenario_submissions (scenario_id, student_user_id, audio_path, transcript, submitted_at) VALUES (:scenario, :student, :audio, :transcript, NOW())')
        ->execute([':scenario' => $scenarioId, ':student' => $studentId, ':audio' => trim($post['audio_path'] ?? '') ?: null, ':transcript' => trim($post['transcript'] ?? '') ?: null]);
    $id = (int) db()->lastInsertId();
    audit_log($studentId, 'scenario_submitted', 'scenario', (string) $scenarioId, ['submission_id' => $id]);
    return $id;
}

function learning_scenario_submission(int $scenarioId, int $studentId): ?array
{
    $s = db()->prepare('SELECT * FROM scenario_submissions WHERE scenario_id = :scenario AND student_user_id = :student ORDER BY id DESC LIMIT 1');
    $s->execute([':scenario' => $scenarioId, ':student' => $studentId]);
    $row = $s->fetch(); return $row ?: null;
}

function learning_correct_scenario(int $submissionId, int $ownerId, array $post): void
{
    db()->prepare('UPDATE scenario_submissions SET score = :score, feedback = :feedback, owner_feedback = :owner_feedback, reviewed_by_user_id = :owner, reviewed_at = NOW() WHERE id = :id')
        ->execute([':score' => (float) ($post['score'] ?? 0), ':feedback' => trim($post['feedback'] ?? ''), ':owner_feedback' => trim($post['owner_feedback'] ?? ''), ':owner' => $ownerId, ':id' => $submissionId]);
    audit_log($ownerId, 'scenario_feedback_saved', 'scenario_submission', (string) $submissionId, []);
}

function learning_create_review(int $ownerId, array $post): int
{
    db()->prepare('INSERT INTO review_tests (student_user_id, title, instructions, test_type, status) VALUES (:student, :title, :instructions, :type, :status)')
        ->execute([':student' => (int) $post['student_user_id'], ':title' => trim($post['title'] ?? 'Review Test'), ':instructions' => trim($post['instructions'] ?? ''), ':type' => $post['test_type'] ?? 'weekly', ':status' => $post['status'] ?? 'draft']);
    $testId = (int) db()->lastInsertId();
    foreach (($post['question_type'] ?? []) as $i => $type) {
        $prompt = trim($post['prompt'][$i] ?? ''); if ($prompt === '') continue;
        $options = [];
        foreach (['a','b','c','d'] as $letter) { $value = trim($post['option_' . $letter][$i] ?? ''); if ($value !== '') $options[strtoupper($letter)] = $value; }
        db()->prepare('INSERT INTO review_questions (review_test_id, question_type, prompt, options_json, answer_key, points, media_url, explanation, sort_order) VALUES (:test, :type, :prompt, :options, :answer, :points, :media, :explanation, :sort_order)')
            ->execute([':test' => $testId, ':type' => $type, ':prompt' => $prompt, ':options' => $options ? learning_json($options) : null, ':answer' => trim($post['answer_key'][$i] ?? ''), ':points' => (float) ($post['points'][$i] ?? 1), ':media' => trim($post['media_url'][$i] ?? '') ?: null, ':explanation' => trim($post['explanation'][$i] ?? '') ?: null, ':sort_order' => $i + 1]);
    }
    audit_log($ownerId, 'review_created', 'review_test', (string) $testId, ['student_user_id' => (int) $post['student_user_id']]);
    return $testId;
}

function learning_review_detail(int $testId, ?int $studentId = null): ?array
{
    $sql = 'SELECT review_tests.*, users.display_name AS student_name FROM review_tests LEFT JOIN users ON users.id = review_tests.student_user_id WHERE review_tests.id = :id';
    $params = [':id' => $testId]; if ($studentId) { $sql .= ' AND review_tests.student_user_id = :student'; $params[':student'] = $studentId; }
    $s = db()->prepare($sql . ' LIMIT 1'); $s->execute($params); $test = $s->fetch(); if (!$test) return null;
    $q = db()->prepare('SELECT * FROM review_questions WHERE review_test_id = :id ORDER BY sort_order ASC, id ASC'); $q->execute([':id' => $testId]); $test['questions'] = $q->fetchAll(); return $test;
}

function learning_submit_review(int $testId, int $studentId, array $post): int
{
    $test = learning_review_detail($testId, $studentId); if (!$test || $test['status'] !== 'published') throw new RuntimeException('Review not available.');
    $answers = []; $score = 0; $max = 0;
    foreach ($test['questions'] as $q) {
        $qid = (int) $q['id']; $options = learning_parse_options($q['options_json'] ?? null); $answerValue = trim($post['answer'][$qid] ?? ''); $answerText = $options[$answerValue] ?? $answerValue; $correctKey = trim((string) $q['answer_key']); $correctText = $options[$correctKey] ?? $correctKey; $points = (float) ($q['points'] ?? 1); $max += $points;
        $auto = in_array($q['question_type'], ['mcq','matching','fill_blank','complete_sentence'], true) && $correctKey !== '';
        $isCorrect = $auto ? (mb_strtolower($answerValue) === mb_strtolower($correctKey) || mb_strtolower($answerText) === mb_strtolower($correctKey)) : null;
        if ($isCorrect === true) $score += $points;
        $answers[$qid] = ['question_id'=>$qid,'question_type'=>$q['question_type'],'prompt'=>$q['prompt'],'selected_key'=>$answerValue,'selected_text'=>$answerText,'correct_key'=>$correctKey,'correct_text'=>$correctText,'is_correct'=>$isCorrect,'points'=>$points,'earned'=>$isCorrect===true?$points:0,'explanation'=>$q['explanation'] ?? ''];
    }
    db()->prepare('INSERT INTO review_submissions (review_test_id, student_user_id, submitted_payload, score, max_score, submitted_at) VALUES (:test, :student, :payload, :score, :max_score, NOW()) ON DUPLICATE KEY UPDATE submitted_payload = VALUES(submitted_payload), score = VALUES(score), max_score = VALUES(max_score), submitted_at = NOW()')
        ->execute([':test' => $testId, ':student' => $studentId, ':payload' => learning_json(['answers' => $answers]), ':score' => $score, ':max_score' => $max]);
    db()->prepare('UPDATE review_tests SET status = "submitted" WHERE id = :id')->execute([':id' => $testId]);
    $id = (int) db()->lastInsertId(); if (!$id) { $s = db()->prepare('SELECT id FROM review_submissions WHERE review_test_id = :test AND student_user_id = :student LIMIT 1'); $s->execute([':test'=>$testId, ':student'=>$studentId]); $id = (int) $s->fetchColumn(); }
    audit_log($studentId, 'review_submitted', 'review_test', (string) $testId, ['submission_id' => $id]); return $id;
}

function learning_review_submission(int $testId, int $studentId): ?array
{
    $s = db()->prepare('SELECT * FROM review_submissions WHERE review_test_id = :test AND student_user_id = :student LIMIT 1'); $s->execute([':test'=>$testId, ':student'=>$studentId]); $row = $s->fetch(); return $row ?: null;
}

function learning_correct_review(int $testId, int $studentId, int $ownerId, array $post): void
{
    $submission = learning_review_submission($testId, $studentId); if (!$submission) throw new RuntimeException('Submission not found.');
    db()->prepare('UPDATE review_submissions SET score = :score, max_score = :max_score, feedback = :feedback, manual_override_payload = :payload, reviewed_by_user_id = :owner, reviewed_at = NOW() WHERE id = :id')
        ->execute([':score'=>(float)($post['score'] ?? $submission['score']), ':max_score'=>(float)($post['max_score'] ?? $submission['max_score']), ':feedback'=>trim($post['feedback'] ?? ''), ':payload'=>learning_json(['note'=>trim($post['override_note'] ?? '')]), ':owner'=>$ownerId, ':id'=>(int)$submission['id']]);
    db()->prepare('UPDATE review_tests SET status = "reviewed", manual_override_note = :note WHERE id = :id')->execute([':note'=>trim($post['override_note'] ?? '') ?: null, ':id'=>$testId]);
    audit_log($ownerId, 'review_corrected', 'review_test', (string) $testId, ['student_user_id'=>$studentId]);
}

function learning_create_material(int $ownerId, array $post): int
{
    db()->prepare('INSERT INTO course_materials (title, material_type, file_path, content, level, assigned_student_user_id, description, is_active, created_by_user_id) VALUES (:title, :type, :file, :content, :level, :student, :description, :active, :owner)')
        ->execute([':title'=>trim($post['title'] ?? 'Material'), ':type'=>$post['material_type'] ?? 'text', ':file'=>trim($post['file_path'] ?? '') ?: null, ':content'=>trim($post['content'] ?? '') ?: null, ':level'=>trim($post['level'] ?? '') ?: null, ':student'=>($_POST['assigned_student_user_id'] ?? '') !== '' ? (int) $_POST['assigned_student_user_id'] : null, ':description'=>trim($post['description'] ?? '') ?: null, ':active'=>isset($post['is_active']) ? 1 : 0, ':owner'=>$ownerId]);
    $id = (int) db()->lastInsertId(); audit_log($ownerId, 'material_created', 'course_material', (string) $id, []); return $id;
}

function learning_result_answers(?array $submission): array
{
    if (!$submission || empty($submission['submitted_payload'])) return [];
    $payload = json_decode($submission['submitted_payload'], true);
    return $payload['answers'] ?? [];
}
