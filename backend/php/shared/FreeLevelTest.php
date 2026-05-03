<?php
/**
 * Phase 6A V2 Free Public Level Test helper.
 *
 * Separate from paid post-payment level checks. This helper handles public lead
 * tests, random snapshots, anti-repeat basics, quick reading check, and full
 * placement attempts using MySQL.
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/AuditLogger.php';

function flt_setting(string $key, $default = null)
{
    $statement = db()->prepare('SELECT setting_value, value_type FROM free_level_test_settings WHERE setting_key = :key LIMIT 1');
    $statement->execute([':key' => $key]);
    $row = $statement->fetch();
    if (!$row) return $default;

    return match ($row['value_type']) {
        'boolean' => in_array(strtolower((string) $row['setting_value']), ['1','true','yes','on'], true),
        'number' => (int) $row['setting_value'],
        'json' => json_decode($row['setting_value'] ?: 'null', true),
        default => $row['setting_value'],
    };
}

function flt_set_setting(string $key, string $value, string $type, ?int $userId = null): void
{
    db()->prepare(
        'INSERT INTO free_level_test_settings (setting_key, setting_value, value_type, updated_by_user_id)
         VALUES (:k, :v, :t, :u)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), value_type = VALUES(value_type), updated_by_user_id = VALUES(updated_by_user_id)'
    )->execute([':k' => $key, ':v' => $value, ':t' => $type, ':u' => $userId]);
    audit_log($userId, 'free_level_test_setting_updated', 'setting', $key, ['value' => $value]);
}

function flt_defaults(): array
{
    return [
        'enable_full_test' => ['1', 'boolean'],
        'enable_quick_check' => ['1', 'boolean'],
        'enable_randomization' => ['1', 'boolean'],
        'anti_repeat_window_days' => ['60', 'number'],
        'listening_blocks_per_level' => ['2', 'number'],
        'reading_texts_per_level' => ['1', 'number'],
        'quick_reading_text_count' => ['5', 'number'],
        'writing_task1_count' => ['1', 'number'],
        'writing_task2_count' => ['1', 'number'],
        'speaking_prompts_per_phase' => ['1', 'number'],
        'allow_retakes' => ['1', 'boolean'],
        'minimum_days_before_retake' => ['7', 'number'],
        'show_quick_check_homepage' => ['1', 'boolean'],
        'show_full_test_cta_homepage' => ['1', 'boolean'],
        'result_copy' => ['This is a preliminary estimate. Final placement requires teacher review.', 'text'],
        'whatsapp_followup_template' => ['Hi {{name}}, your free Arabic placement test was received. We will send your result and next step within 48 hours.', 'text'],
        'email_followup_template' => ['Your free Arabic placement test was received. We will send your result and next step within 48 hours.', 'text'],
    ];
}

function flt_seed_defaults(): void
{
    foreach (flt_defaults() as $key => [$value, $type]) {
        $exists = db()->prepare('SELECT id FROM free_level_test_settings WHERE setting_key = :k LIMIT 1');
        $exists->execute([':k' => $key]);
        if (!$exists->fetch()) {
            flt_set_setting($key, $value, $type, null);
        }
    }

    flt_seed_question_bank_if_empty();
}

function flt_seed_question_bank_if_empty(): void
{
    $count = (int) db()->query('SELECT COUNT(*) FROM free_level_test_reading_texts')->fetchColumn();
    if ($count > 0) return;

    $levels = ['A1','A2','B1','B2','C1','C2'];
    foreach ($levels as $idx => $level) {
        $title = 'Quick Reading ' . $level;
        $passage = 'هذا نص عربي قصير لاختبار القراءة. يتحدث النص عن طالب يتعلم اللغة العربية ويستخدمها في الحياة اليومية والعمل.';
        db()->prepare('INSERT INTO free_level_test_reading_texts (bank_type, level, text_number, title, passage_text, topic, dialect_style) VALUES ("quick", :level, :num, :title, :passage, "Daily Arabic", "فصحى")')
            ->execute([':level' => $level, ':num' => $idx + 1, ':title' => $title, ':passage' => $passage]);
        $textId = (int) db()->lastInsertId();
        for ($i = 1; $i <= 5; $i++) {
            db()->prepare('INSERT INTO free_level_test_reading_questions (reading_text_id, question_text, option_a, option_b, option_c, option_d, correct_option, sort_order) VALUES (:id, :q, "تعلم العربية", "السفر فقط", "النوم", "لا أعرف", "A", :sort)')
                ->execute([':id' => $textId, ':q' => 'ما موضوع النص؟', ':sort' => $i]);
        }
    }

    foreach (['A2','B1','B2','C1','C2'] as $level) {
        $max = $level === 'A2' ? 9 : 10;
        for ($i = 1; $i <= $max; $i++) {
            $lower = strtolower($level);
            $audioUrl = "/assets/audio/level-test/listening/{$lower}/{$lower}_{$i}.mp3";
            db()->prepare('INSERT INTO free_level_test_listening_scripts (level, script_number, audio_url, title, topic, dialect_style, script_text) VALUES (:level, :num, :url, :title, "General", "فصحى", "Demo listening script text")')
                ->execute([':level' => $level, ':num' => $i, ':url' => $audioUrl, ':title' => $level . ' Listening ' . $i]);
            $scriptId = (int) db()->lastInsertId();
            for ($q = 1; $q <= 3; $q++) {
                db()->prepare('INSERT INTO free_level_test_listening_questions (script_id, question_text, option_a, option_b, option_c, option_d, correct_option, sort_order) VALUES (:id, :q, "الإجابة الأولى", "الإجابة الثانية", "الإجابة الثالثة", "لا أعرف", "A", :sort)')
                    ->execute([':id' => $scriptId, ':q' => 'اختر الإجابة الصحيحة من المقطع الصوتي.', ':sort' => $q]);
            }
        }
    }

    $task1 = ['تجربتك في تعلم اللغة العربية','يوم لا تنساه في حياتك','شخص أثّر كثيراً في حياتك','مكان تحبه كثيراً','حلم تريد تحقيقه في المستقبل'];
    foreach ($task1 as $i => $prompt) {
        db()->prepare('INSERT INTO free_level_test_writing_prompts (task_type, level, title, prompt_text, word_min, word_max, instructions) VALUES ("task1", "all", :title, :prompt, 80, 100, "اكتب 80 إلى 100 كلمة.")')
            ->execute([':title' => $prompt, ':prompt' => $prompt]);
    }

    $task2 = [
        'B1' => ['التكنولوجيا والحياة','السفر والتعلم'],
        'B2' => ['وسائل التواصل الاجتماعي','الذكاء الاصطناعي وسوق العمل'],
        'C1' => ['حرية التعبير','العدالة والمساواة'],
        'C2' => ['الحرية والمسؤولية','الحقيقة في عصر المعلومات'],
    ];
    foreach ($task2 as $level => $prompts) {
        foreach ($prompts as $prompt) {
            db()->prepare('INSERT INTO free_level_test_writing_prompts (task_type, level, title, prompt_text, word_min, word_max, instructions) VALUES ("task2", :level, :title, :prompt, 120, 250, "اكتب إجابة مناسبة لمستواك.")')
                ->execute([':level' => $level, ':title' => $prompt, ':prompt' => $prompt]);
        }
    }

    $phases = [
        'warm_up' => ['Self introduction', 'Introduce yourself and talk about your daily routine.'],
        'description' => ['Describe a scene', 'Describe a place or picture and explain what is happening.'],
        'discussion' => ['Give your opinion', 'Give your opinion about learning online.'],
        'abstract_argument' => ['Build an argument', 'Discuss the relationship between language, identity, and thinking.'],
    ];
    foreach ($phases as $phase => [$title, $prompt]) {
        db()->prepare('INSERT INTO free_level_test_speaking_prompts (phase, target_level, title, prompt_text, sort_order) VALUES (:phase, "A2-C2", :title, :prompt, 1)')
            ->execute([':phase' => $phase, ':title' => $title, ':prompt' => $prompt]);
    }
}

function flt_device_id(): string
{
    if (empty($_COOKIE['hn_flt_device_id'])) {
        $id = bin2hex(random_bytes(16));
        setcookie('hn_flt_device_id', $id, time() + 3600 * 24 * 365, '/', '', false, true);
        $_COOKIE['hn_flt_device_id'] = $id;
    }
    return $_COOKIE['hn_flt_device_id'];
}

function flt_hash(?string $value): ?string
{
    if (!$value) return null;
    return hash('sha256', $value . '|' . (getenv('APP_KEY') ?: 'hn-default-key'));
}

function flt_context(?string $whatsapp = null): array
{
    return [
        'device_id' => flt_device_id(),
        'whatsapp' => $whatsapp,
        'ip_hash' => flt_hash($_SERVER['REMOTE_ADDR'] ?? ''),
        'user_agent_hash' => flt_hash($_SERVER['HTTP_USER_AGENT'] ?? ''),
    ];
}

function flt_token(): string
{
    return hash('sha256', bin2hex(random_bytes(24)) . microtime(true));
}

function flt_pick_rows(string $sql, array $params, int $limit): array
{
    $statement = db()->prepare($sql);
    $statement->execute($params);
    $rows = $statement->fetchAll();
    shuffle($rows);
    return array_slice($rows, 0, $limit);
}

function flt_questions_for_reading(int $textId): array
{
    $s = db()->prepare('SELECT * FROM free_level_test_reading_questions WHERE reading_text_id = :id AND is_active = 1 ORDER BY sort_order ASC, id ASC');
    $s->execute([':id' => $textId]);
    return $s->fetchAll();
}

function flt_questions_for_listening(int $scriptId): array
{
    $s = db()->prepare('SELECT * FROM free_level_test_listening_questions WHERE script_id = :id AND is_active = 1 ORDER BY sort_order ASC, id ASC');
    $s->execute([':id' => $scriptId]);
    return $s->fetchAll();
}

function flt_generate_quick_snapshot(): array
{
    flt_seed_defaults();
    $count = (int) flt_setting('quick_reading_text_count', 5);
    $texts = flt_pick_rows('SELECT * FROM free_level_test_reading_texts WHERE is_active = 1 AND bank_type IN ("quick","shared")', [], $count);
    if (!$texts) {
        $texts = flt_pick_rows('SELECT * FROM free_level_test_reading_texts WHERE is_active = 1', [], $count);
    }
    $snapshot = ['reading_texts' => [], 'warnings' => []];
    foreach ($texts as $text) {
        $snapshot['reading_texts'][] = ['text' => $text, 'questions' => flt_questions_for_reading((int) $text['id'])];
    }
    if (count($texts) < $count) $snapshot['warnings'][] = 'Not enough quick reading texts available.';
    return $snapshot;
}

function flt_level_percent_to_cefr(float $percent): string
{
    if ($percent < 25) return 'A1';
    if ($percent < 40) return 'A2';
    if ($percent < 55) return 'B1';
    if ($percent < 70) return 'B2';
    if ($percent < 85) return 'C1';
    return 'C2';
}

function flt_percent_to_full_level(float $percent): string
{
    if ($percent < 40) return 'A2 or below';
    if ($percent < 55) return 'B1';
    if ($percent < 70) return 'B2';
    if ($percent < 85) return 'C1';
    return 'C2';
}

function flt_create_attempt(string $testType, ?int $applicantId, ?string $whatsapp, array $snapshot, string $currentStep = 'listening'): array
{
    $ctx = flt_context($whatsapp);
    $token = flt_token();
    db()->prepare('INSERT INTO free_level_test_attempts (attempt_token, test_type, applicant_id, device_id, whatsapp, ip_hash, user_agent_hash, current_step, snapshot_json, generated_with_warnings, warnings_json) VALUES (:token, :type, :applicant, :device, :whatsapp, :ip, :ua, :step, :snapshot, :warn, :warnings)')
        ->execute([
            ':token' => $token,
            ':type' => $testType,
            ':applicant' => $applicantId,
            ':device' => $ctx['device_id'],
            ':whatsapp' => $whatsapp,
            ':ip' => $ctx['ip_hash'],
            ':ua' => $ctx['user_agent_hash'],
            ':step' => $currentStep,
            ':snapshot' => json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ':warn' => !empty($snapshot['warnings']) ? 1 : 0,
            ':warnings' => !empty($snapshot['warnings']) ? json_encode($snapshot['warnings'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
        ]);
    return ['id' => (int) db()->lastInsertId(), 'token' => $token];
}

function flt_attempt_by_token(string $token): ?array
{
    $s = db()->prepare('SELECT * FROM free_level_test_attempts WHERE attempt_token = :t LIMIT 1');
    $s->execute([':t' => $token]);
    $row = $s->fetch();
    return $row ?: null;
}

function flt_create_applicant(array $data): int
{
    $ctx = flt_context($data['whatsapp'] ?? null);
    db()->prepare('INSERT INTO free_level_test_applicants (applicant_type, existing_student_code, full_name, whatsapp, email, age, country, device_id, ip_hash, user_agent_hash) VALUES (:type, :code, :name, :whatsapp, :email, :age, :country, :device, :ip, :ua)')
        ->execute([
            ':type' => $data['applicant_type'] ?? 'new_applicant',
            ':code' => $data['existing_student_code'] ?? null,
            ':name' => $data['full_name'],
            ':whatsapp' => $data['whatsapp'],
            ':email' => $data['email'] ?: null,
            ':age' => $data['age'] ?: null,
            ':country' => $data['country'] ?: null,
            ':device' => $ctx['device_id'],
            ':ip' => $ctx['ip_hash'],
            ':ua' => $ctx['user_agent_hash'],
        ]);
    return (int) db()->lastInsertId();
}

function flt_generate_full_snapshot(): array
{
    flt_seed_defaults();
    $levels = ['A2','B1','B2','C1','C2'];
    $listeningPer = (int) flt_setting('listening_blocks_per_level', 2);
    $readingPer = (int) flt_setting('reading_texts_per_level', 1);
    $snapshot = ['listening_scripts' => [], 'reading_texts' => [], 'writing_prompts' => [], 'speaking_prompts' => [], 'warnings' => []];

    foreach ($levels as $level) {
        $scripts = flt_pick_rows('SELECT * FROM free_level_test_listening_scripts WHERE is_active = 1 AND level = :level', [':level' => $level], $listeningPer);
        if (count($scripts) < $listeningPer) $snapshot['warnings'][] = $level . ': fewer listening scripts than required.';
        foreach ($scripts as $script) {
            $snapshot['listening_scripts'][] = ['script' => $script, 'questions' => flt_questions_for_listening((int) $script['id'])];
        }

        $texts = flt_pick_rows('SELECT * FROM free_level_test_reading_texts WHERE is_active = 1 AND bank_type IN ("full","shared") AND level = :level', [':level' => $level], $readingPer);
        if (count($texts) < $readingPer) $snapshot['warnings'][] = $level . ': fewer reading texts than required.';
        foreach ($texts as $text) {
            $snapshot['reading_texts'][] = ['text' => $text, 'questions' => flt_questions_for_reading((int) $text['id'])];
        }
    }

    $task1 = flt_pick_rows('SELECT * FROM free_level_test_writing_prompts WHERE is_active = 1 AND task_type = "task1"', [], 1);
    $task2 = flt_pick_rows('SELECT * FROM free_level_test_writing_prompts WHERE is_active = 1 AND task_type = "task2"', [], 1);
    $snapshot['writing_prompts'] = array_merge($task1, $task2);

    foreach (['warm_up','description','discussion','abstract_argument'] as $phase) {
        $prompts = flt_pick_rows('SELECT * FROM free_level_test_speaking_prompts WHERE is_active = 1 AND phase = :phase', [':phase' => $phase], 1);
        $snapshot['speaking_prompts'] = array_merge($snapshot['speaking_prompts'], $prompts);
    }
    return $snapshot;
}

function flt_grade_reading_answers(array $snapshot, array $post, int $attemptId, string $sourceType = 'reading'): array
{
    $correct = 0; $total = 0; $points = 0; $max = 0;
    foreach ($snapshot['reading_texts'] ?? [] as $block) {
        foreach ($block['questions'] as $q) {
            $name = 'q_' . $q['id'];
            $selected = $post[$name] ?? 'X';
            $isCorrect = $selected === $q['correct_option'];
            $correct += $isCorrect ? 1 : 0; $total++; $points += $isCorrect ? (float) $q['points'] : 0; $max += (float) $q['points'];
            flt_save_answer($attemptId, $sourceType, $sourceType, (int) $block['text']['id'], (int) $q['id'], $q['question_text'], $selected, $q['correct_option'], $isCorrect, (float) $q['points'], $isCorrect ? (float) $q['points'] : 0);
        }
    }
    return ['correct' => $correct, 'total' => $total, 'points' => $points, 'max' => $max, 'percent' => $max > 0 ? round(($points / $max) * 100, 2) : 0];
}

function flt_grade_listening_answers(array $snapshot, array $post, int $attemptId): array
{
    $correct = 0; $total = 0; $points = 0; $max = 0;
    foreach ($snapshot['listening_scripts'] ?? [] as $block) {
        foreach ($block['questions'] as $q) {
            $name = 'lq_' . $q['id'];
            $selected = $post[$name] ?? 'X';
            $isCorrect = $selected === $q['correct_option'];
            $correct += $isCorrect ? 1 : 0; $total++; $points += $isCorrect ? (float) $q['points'] : 0; $max += (float) $q['points'];
            flt_save_answer($attemptId, 'listening', 'listening', (int) $block['script']['id'], (int) $q['id'], $q['question_text'], $selected, $q['correct_option'], $isCorrect, (float) $q['points'], $isCorrect ? (float) $q['points'] : 0);
        }
    }
    return ['correct' => $correct, 'total' => $total, 'points' => $points, 'max' => $max, 'percent' => $max > 0 ? round(($points / $max) * 100, 2) : 0];
}

function flt_save_answer(int $attemptId, string $section, string $sourceType, ?int $sourceId, ?int $questionId, string $question, string $selected, ?string $correct, ?bool $isCorrect, float $points, float $score): void
{
    db()->prepare('INSERT INTO free_level_test_answers (attempt_id, section_key, source_type, source_id, question_id, question_text, selected_option, correct_option, is_correct, points, score) VALUES (:attempt, :section, :source_type, :source, :question_id, :question, :selected, :correct, :is_correct, :points, :score)')
        ->execute([
            ':attempt' => $attemptId, ':section' => $section, ':source_type' => $sourceType, ':source' => $sourceId,
            ':question_id' => $questionId, ':question' => $question, ':selected' => $selected, ':correct' => $correct,
            ':is_correct' => $isCorrect === null ? null : (int) $isCorrect, ':points' => $points, ':score' => $score,
        ]);
}

function flt_upload_rules(): array
{
    return [
        'audio' => ['ext' => ['mp3','wav','m4a','webm'], 'mime' => ['audio/mpeg','audio/wav','audio/x-wav','audio/mp4','audio/webm','video/webm'], 'max' => 25 * 1024 * 1024],
        'document' => ['ext' => ['jpg','jpeg','png','pdf'], 'mime' => ['image/jpeg','image/png','application/pdf'], 'max' => 10 * 1024 * 1024],
    ];
}

function flt_store_upload(int $attemptId, string $field, string $purpose, string $kind): ?string
{
    if (empty($_FILES[$field]) || ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
    $file = $_FILES[$field];
    if ($file['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('Upload failed: ' . $field);
    $rules = flt_upload_rules()[$kind];
    if ($file['size'] > $rules['max']) throw new RuntimeException('File too large: ' . $field);
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $rules['ext'], true)) throw new RuntimeException('Invalid extension: ' . $ext);
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!in_array($mime, $rules['mime'], true)) throw new RuntimeException('Invalid MIME: ' . $mime);
    $dir = dirname(__DIR__, 3) . '/uploads/free-level-tests/' . $attemptId;
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $name = $purpose . '-' . bin2hex(random_bytes(6)) . '.' . $ext;
    $target = $dir . '/' . $name;
    if (!move_uploaded_file($file['tmp_name'], $target)) throw new RuntimeException('Could not save uploaded file.');
    $path = 'uploads/free-level-tests/' . $attemptId . '/' . $name;
    db()->prepare('INSERT INTO free_level_test_uploads (attempt_id, purpose, file_path, original_filename, mime_type, size_bytes) VALUES (:attempt, :purpose, :path, :original, :mime, :size)')
        ->execute([':attempt' => $attemptId, ':purpose' => $purpose, ':path' => $path, ':original' => $file['name'], ':mime' => $mime, ':size' => (int) $file['size']]);
    return $path;
}

function flt_audio_availability(): array
{
    $levels = ['A2','B1','B2','C1','C2'];
    $required = (int) flt_setting('listening_blocks_per_level', 2);
    $out = [];
    foreach ($levels as $level) {
        $s = db()->prepare('SELECT COUNT(*) FROM free_level_test_listening_scripts WHERE level = :level AND is_active = 1');
        $s->execute([':level' => $level]);
        $count = (int) $s->fetchColumn();
        $out[$level] = ['available' => $count, 'required' => $required, 'status' => $count >= $required ? 'OK' : 'Warning'];
    }
    return $out;
}
