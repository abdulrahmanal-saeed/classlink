<?php
/**
 * Phase 16 AI Teacher and Marketing Tools helper.
 *
 * Rules:
 * - Preview only.
 * - Save prompt and response.
 * - Save usage tokens/cost when available.
 * - Enforce regenerate limits from settings.
 * - Never auto-publish.
 * - Owner must apply/save after preview.
 *
 * Anthropic:
 * - Store API key in .env/server environment as ANTHROPIC_API_KEY.
 * - Do not commit keys to GitHub.
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/AuditLogger.php';
require_once __DIR__ . '/OwnerDashboard.php';
require_once __DIR__ . '/LearningAssignments.php';

function ai_setting(string $key, $default = null)
{
    $s = db()->prepare('SELECT setting_value FROM settings WHERE setting_key = :key LIMIT 1');
    $s->execute([':key' => $key]);
    $value = $s->fetchColumn();
    return $value === false ? $default : $value;
}

function ai_env(string $key): ?string
{
    $value = getenv($key);
    if ($value) return $value;

    $paths = [__DIR__ . '/../../.env', __DIR__ . '/../../../.env'];
    foreach ($paths as $path) {
        if (!is_file($path)) continue;
        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
            [$envKey, $envValue] = explode('=', $line, 2);
            if (trim($envKey) === $key) return trim($envValue, " \t\n\r\0\x0B\"'");
        }
    }

    return null;
}

function ai_daily_regenerate_limit_reached(int $userId, string $toolName, ?string $relatedType = null, ?string $relatedId = null): bool
{
    $limit = (int) ai_setting('ai_regenerate_limit_per_tool_per_day', 3);
    if ($limit <= 0) return false;

    $sql = 'SELECT COUNT(*) FROM ai_usage_logs WHERE user_id = :user AND tool_name = :tool AND DATE(created_at) = CURDATE()';
    $params = [':user' => $userId, ':tool' => $toolName];
    if ($relatedType) { $sql .= ' AND related_type = :related_type'; $params[':related_type'] = $relatedType; }
    if ($relatedId) { $sql .= ' AND related_id = :related_id'; $params[':related_id'] = $relatedId; }

    $s = db()->prepare($sql);
    $s->execute($params);
    return (int) $s->fetchColumn() >= $limit;
}

function ai_tool_labels(): array
{
    return [
        'analyze_student' => 'Analyze Student',
        'plan_remaining_sessions' => 'Plan Remaining Sessions',
        'prepare_next_lesson' => 'Prepare Next Lesson',
        'generate_homework' => 'Generate Homework',
        'generate_scenario' => 'Generate Scenario',
        'weekly_student_summary' => 'Generate Weekly Student Summary',
        'generate_article' => 'Generate Article',
        'article_cover_prompt' => 'Generate Article Cover Image Prompt',
    ];
}

function ai_student_context(int $studentId): array
{
    $detail = owner_student_detail_full($studentId);
    if (!$detail) throw new RuntimeException('Student not found.');

    return [
        'profile' => $detail['profile'],
        'balance' => $detail['balance'],
        'progress' => $detail['progress'],
        'recent_homeworks' => array_slice($detail['homeworks'], 0, 5),
        'recent_scenarios' => array_slice($detail['scenarios'], 0, 5),
        'recent_reviews' => array_slice($detail['reviews'], 0, 5),
        'practice_words' => array_slice($detail['practice_words'], 0, 20),
        'session_notes' => array_slice($detail['session_notes'], 0, 5),
    ];
}

function ai_build_prompt(string $toolName, array $context, array $input = []): string
{
    $base = "You are an AI assistant for Habiba Nabil Arabic Academy.\n";
    $base .= "Use Arabic and English where useful. Keep output practical, teacher-friendly, and draft-only.\n";
    $base .= "Never claim this is published. Output must be preview-ready and editable.\n\n";

    $jsonContext = json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    $jsonInput = json_encode($input, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

    return match ($toolName) {
        'analyze_student' => $base . "Tool: Analyze Student.\nReturn: strengths, weaknesses, level notes, learning risks, recommended teacher focus, next actions.\nContext:\n{$jsonContext}\nInput:\n{$jsonInput}",
        'plan_remaining_sessions' => $base . "Tool: Plan Remaining Sessions.\nReturn a progressive session plan with title, skills, goals, teacher focus, student output, homework idea, difficulty, and why now.\nContext:\n{$jsonContext}\nInput:\n{$jsonInput}",
        'prepare_next_lesson' => $base . "Tool: Prepare Next Lesson.\nReturn a 90-minute lesson plan using Warm-up 10, Input 20, Guided Practice 20, Speaking Task 25, Feedback 10, Homework 5.\nContext:\n{$jsonContext}\nInput:\n{$jsonInput}",
        'generate_homework' => $base . "Tool: Generate Homework.\nReturn homework draft with listening, reading, MCQ, writing, and speaking sections. Keep it preview-only.\nContext:\n{$jsonContext}\nInput:\n{$jsonInput}",
        'generate_scenario' => $base . "Tool: Generate Scenario.\nReturn real-life speaking task with title, situation, prompt, time limit, keywords, and optional model answer.\nContext:\n{$jsonContext}\nInput:\n{$jsonInput}",
        'weekly_student_summary' => $base . "Tool: Generate Weekly Student Summary.\nReturn JSON-like sections: this_week_summary, what_went_well, areas_to_focus_on, suggested_focus_next_week, engagement_level High/Medium/Low.\nContext:\n{$jsonContext}\nInput:\n{$jsonInput}",
        'generate_article' => $base . "Tool: Generate Article.\nReturn Arabic title, optional English title, slug, SEO meta title, SEO meta description, excerpt, full article, CTA, target audience, keywords, suggested cover image prompt, status draft.\nInput:\n{$jsonInput}",
        'article_cover_prompt' => $base . "Tool: Generate Article Cover Image Prompt.\nReturn one detailed image prompt for a website article cover and Facebook-friendly layout.\nInput:\n{$jsonInput}",
        default => $base . "Tool: General AI Draft.\nContext:\n{$jsonContext}\nInput:\n{$jsonInput}",
    };
}

function ai_mock_response(string $toolName, array $context, array $input): array
{
    $studentName = $context['profile']['display_name'] ?? 'the student';
    $topic = $input['topic'] ?? $input['title'] ?? 'Arabic learning';

    return match ($toolName) {
        'analyze_student' => [
            'title' => 'AI Student Analysis — ' . $studentName,
            'content' => "Strengths:\n- Shows consistent learning potential.\n\nAreas to focus on:\n- Speaking confidence\n- Vocabulary recall\n- Sentence building\n\nRecommended next action:\nPrepare a practical speaking-first lesson with controlled vocabulary and a short real-life task.",
        ],
        'plan_remaining_sessions' => [
            'title' => 'Remaining Sessions Plan — ' . $studentName,
            'content' => "Session 1: Review and stabilize weak points.\nSession 2: Build practical speaking output.\nSession 3: Add listening + sentence expansion.\nSession 4: Real-life scenario and feedback.\n\nWhy now: The student needs structured repetition before moving to harder output.",
        ],
        'prepare_next_lesson' => [
            'title' => 'Next Lesson Draft — ' . $studentName,
            'content' => "Warm-up 10 min: quick review.\nInput 20 min: 6 useful words with examples.\nGuided Practice 20 min: sentence building.\nSpeaking Task 25 min: real-life conversation.\nFeedback 10 min: correction and pronunciation.\nHomework 5 min: short writing + voice note.",
        ],
        'generate_homework' => [
            'title' => 'Homework Draft — ' . $studentName,
            'content' => "Listening: Short audio/video task.\nReading: Short paragraph with 3 questions.\nMCQ: 5 vocabulary/meaning questions.\nWriting: 4 sentences about {$topic}.\nSpeaking: 45-second voice answer using 5 keywords.",
        ],
        'generate_scenario' => [
            'title' => 'Scenario Draft — ' . $topic,
            'content' => "Situation: You are in a real-life conversation about {$topic}.\nPrompt: Explain what you need and ask one follow-up question.\nTime limit: 60 seconds.\nKeywords: من فضلك، أريد، ممكن، الآن، شكراً.\nModel answer: Optional draft answer for teacher editing.",
        ],
        'weekly_student_summary' => [
            'title' => 'Weekly Summary — ' . $studentName,
            'content' => "This week summary:\nThe student continued practicing Arabic with steady engagement.\n\nWhat went well:\nVocabulary exposure and basic response building.\n\nAreas to focus on:\nSpeaking fluency and recall.\n\nSuggested focus next week:\nShort real-life speaking tasks with repeated keywords.\n\nEngagement level: Medium",
            'json' => [
                'this_week_summary' => 'The student continued practicing Arabic with steady engagement.',
                'what_went_well' => 'Vocabulary exposure and basic response building.',
                'areas_to_focus_on' => 'Speaking fluency and recall.',
                'suggested_focus_next_week' => 'Short real-life speaking tasks with repeated keywords.',
                'engagement_level' => 'Medium',
            ],
        ],
        'generate_article' => [
            'title' => 'Article Draft — ' . $topic,
            'content' => "Arabic title: لماذا يحتاج كل طالب عربي إلى خطة مختلفة؟\nEnglish title: Why every Arabic learner needs a different plan\nSlug: personalized-arabic-learning-plan\nSEO meta title: Personalized Arabic Lessons for Non-Native Speakers\nSEO meta description: Learn why every Arabic learner needs a personalized plan based on level, goal, and speaking confidence.\nExcerpt: Not every student should start from zero.\nFull article: Draft article content about {$topic}.\nCTA: Book a single session.\nTarget audience: Non-native Arabic learners.\nKeywords: Arabic lessons, Arabic tutor, learn Arabic online\nSuggested cover image prompt: Warm professional Arabic learning website cover with teacher reviewing student paths.\nStatus: draft",
            'json' => [
                'title_ar' => 'لماذا يحتاج كل طالب عربي إلى خطة مختلفة؟',
                'title_en' => 'Why every Arabic learner needs a different plan',
                'slug' => 'personalized-arabic-learning-plan-' . date('YmdHis'),
                'seo_meta_title' => 'Personalized Arabic Lessons for Non-Native Speakers',
                'seo_meta_description' => 'Learn why every Arabic learner needs a personalized plan based on level, goal, and speaking confidence.',
                'excerpt_ar' => 'ليس كل طالب يجب أن يبدأ من الصفر.',
                'body_ar' => 'Draft article content about ' . $topic,
                'cta' => 'Book a single session',
                'target_audience' => 'Non-native Arabic learners',
                'keywords' => 'Arabic lessons, Arabic tutor, learn Arabic online',
                'cover_image_prompt' => 'Warm professional Arabic learning website cover with teacher reviewing student paths.',
                'status' => 'draft',
            ],
        ],
        'article_cover_prompt' => [
            'title' => 'Cover Image Prompt — ' . $topic,
            'content' => "Create a warm, professional website article cover for Habiba Nabil Arabic Academy about {$topic}. Use soft teal, deep navy, warm gold, and off-white. Show an online Arabic teacher reviewing personalized student learning paths. Avoid childish design. Arabic text should be clear and elegant.",
        ],
        default => ['title' => 'AI Draft', 'content' => 'Preview draft generated.'],
    };
}

function ai_anthropic_response(string $prompt, string $toolName): array
{
    $apiKey = ai_env('ANTHROPIC_API_KEY');
    if (!$apiKey) {
        return ['ok' => false, 'error' => 'ANTHROPIC_API_KEY is missing. Add it to .env or server environment.', 'text' => null, 'input_tokens' => null, 'output_tokens' => null];
    }

    $model = ai_setting('ai_default_model', 'claude-sonnet-4-20250514');
    $maxTokens = (int) ai_setting('ai_max_preview_tokens', 2500);

    $payload = [
        'model' => $model,
        'max_tokens' => $maxTokens,
        'messages' => [
            ['role' => 'user', 'content' => $prompt],
        ],
    ];

    $ch = curl_init('https://api.anthropic.com/v1/messages');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'x-api-key: ' . $apiKey,
            'anthropic-version: 2023-06-01',
            'content-type: application/json',
        ],
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        CURLOPT_TIMEOUT => 45,
    ]);

    $raw = curl_exec($ch);
    $error = curl_error($ch);
    $http = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($raw === false || $error) {
        return ['ok' => false, 'error' => $error ?: 'Anthropic request failed.', 'text' => null, 'input_tokens' => null, 'output_tokens' => null];
    }

    $decoded = json_decode($raw, true);
    if ($http >= 400) {
        return ['ok' => false, 'error' => $decoded['error']['message'] ?? ('Anthropic HTTP ' . $http), 'text' => null, 'raw' => $decoded, 'input_tokens' => null, 'output_tokens' => null];
    }

    $text = '';
    foreach (($decoded['content'] ?? []) as $block) {
        if (($block['type'] ?? '') === 'text') $text .= $block['text'] ?? '';
    }

    return [
        'ok' => true,
        'error' => null,
        'text' => $text,
        'raw' => $decoded,
        'input_tokens' => $decoded['usage']['input_tokens'] ?? null,
        'output_tokens' => $decoded['usage']['output_tokens'] ?? null,
    ];
}

function ai_run_preview(int $userId, string $toolName, array $context = [], array $input = [], ?string $relatedType = null, ?string $relatedId = null): int
{
    if (ai_setting('ai_enabled', '1') !== '1') throw new RuntimeException('AI tools are disabled in settings.');
    if (ai_daily_regenerate_limit_reached($userId, $toolName, $relatedType, $relatedId)) throw new RuntimeException('Daily regenerate limit reached for this AI tool.');

    $prompt = ai_build_prompt($toolName, $context, $input);
    $provider = ai_setting('ai_provider', 'anthropic');
    $model = ai_setting('ai_default_model', 'claude-sonnet-4-20250514');

    $status = 'previewed';
    $error = null;
    $responseText = null;
    $responseJson = null;
    $inputTokens = null;
    $outputTokens = null;

    if ($provider === 'anthropic') {
        $result = ai_anthropic_response($prompt, $toolName);
        if ($result['ok']) {
            $responseText = $result['text'];
            $responseJson = $result['raw'] ?? null;
            $inputTokens = $result['input_tokens'];
            $outputTokens = $result['output_tokens'];
        } else {
            $status = 'failed';
            $error = $result['error'];
            $mock = ai_mock_response($toolName, $context, $input);
            $responseText = "AI provider failed, fallback preview draft:\n\n" . $mock['content'];
            $responseJson = ['fallback' => true, 'title' => $mock['title'], 'error' => $error, 'data' => $mock['json'] ?? null];
        }
    } else {
        $mock = ai_mock_response($toolName, $context, $input);
        $responseText = $mock['content'];
        $responseJson = ['title' => $mock['title'], 'data' => $mock['json'] ?? null];
        $model = 'mock-teacher-v1';
    }

    $estimatedTokens = (int) ($inputTokens ?? 0) + (int) ($outputTokens ?? 0);
    $costPer1k = (float) ai_setting('ai_estimated_cost_per_1k_tokens', 0);
    $estimatedCost = $estimatedTokens > 0 ? ($estimatedTokens / 1000) * $costPer1k : null;

    db()->prepare(
        'INSERT INTO ai_usage_logs
          (user_id, tool_name, related_type, related_id, model_name, prompt_text, response_text, response_json, prompt_tokens, completion_tokens, estimated_tokens, estimated_cost, status, error_message)
         VALUES
          (:user, :tool, :related_type, :related_id, :model, :prompt, :response, :json, :input_tokens, :output_tokens, :estimated_tokens, :cost, :status, :error)'
    )->execute([
        ':user' => $userId,
        ':tool' => $toolName,
        ':related_type' => $relatedType,
        ':related_id' => $relatedId,
        ':model' => $model,
        ':prompt' => $prompt,
        ':response' => $responseText,
        ':json' => json_encode($responseJson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ':input_tokens' => $inputTokens,
        ':output_tokens' => $outputTokens,
        ':estimated_tokens' => $estimatedTokens ?: null,
        ':cost' => $estimatedCost,
        ':status' => $status,
        ':error' => $error,
    ]);

    $usageId = (int) db()->lastInsertId();

    db()->prepare(
        'INSERT INTO ai_drafts (user_id, tool_name, related_type, related_id, title, prompt_text, response_text, response_json, status, usage_log_id)
         VALUES (:user, :tool, :related_type, :related_id, :title, :prompt, :response, :json, "draft", :usage)'
    )->execute([
        ':user' => $userId,
        ':tool' => $toolName,
        ':related_type' => $relatedType,
        ':related_id' => $relatedId,
        ':title' => ai_tool_labels()[$toolName] ?? 'AI Draft',
        ':prompt' => $prompt,
        ':response' => $responseText,
        ':json' => json_encode($responseJson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ':usage' => $usageId,
    ]);

    $draftId = (int) db()->lastInsertId();
    audit_log($userId, 'ai_preview_generated', 'ai_draft', (string) $draftId, ['tool_name' => $toolName, 'usage_log_id' => $usageId]);
    return $draftId;
}

function ai_draft(int $draftId): ?array
{
    $s = db()->prepare('SELECT * FROM ai_drafts WHERE id = :id LIMIT 1');
    $s->execute([':id' => $draftId]);
    $row = $s->fetch();
    return $row ?: null;
}

function ai_recent_drafts(?string $toolName = null): array
{
    if ($toolName) {
        $s = db()->prepare('SELECT * FROM ai_drafts WHERE tool_name = :tool ORDER BY created_at DESC LIMIT 100');
        $s->execute([':tool' => $toolName]);
        return $s->fetchAll();
    }
    return db()->query('SELECT * FROM ai_drafts ORDER BY created_at DESC LIMIT 100')->fetchAll();
}

function ai_usage_logs(): array
{
    return db()->query('SELECT ai_usage_logs.*, users.display_name FROM ai_usage_logs LEFT JOIN users ON users.id = ai_usage_logs.user_id ORDER BY ai_usage_logs.created_at DESC LIMIT 300')->fetchAll();
}

function ai_apply_homework_draft(int $draftId, int $ownerId, int $studentId): int
{
    $draft = ai_draft($draftId);
    if (!$draft || $draft['status'] !== 'draft') throw new RuntimeException('Draft not available.');

    $homeworkId = learning_create_homework($ownerId, [
        'student_user_id' => $studentId,
        'title' => $draft['title'] ?: 'AI Homework Draft',
        'instructions' => $draft['response_text'],
        'status' => 'draft',
        'due_at' => null,
        'question_type' => [],
    ]);

    ai_mark_applied($draftId, 'homework', (string) $homeworkId, $ownerId);
    return $homeworkId;
}

function ai_apply_scenario_draft(int $draftId, int $ownerId, int $studentId): int
{
    $draft = ai_draft($draftId);
    if (!$draft || $draft['status'] !== 'draft') throw new RuntimeException('Draft not available.');

    $scenarioId = learning_create_scenario($ownerId, [
        'student_user_id' => $studentId,
        'title' => $draft['title'] ?: 'AI Scenario Draft',
        'situation' => $draft['response_text'],
        'prompt' => 'Review and edit this AI draft before publishing.',
        'keywords' => '',
        'model_answer' => null,
        'time_limit_seconds' => 60,
        'status' => 'draft',
    ]);

    ai_mark_applied($draftId, 'scenario', (string) $scenarioId, $ownerId);
    return $scenarioId;
}

function ai_apply_weekly_summary(int $draftId, int $ownerId, int $studentId): int
{
    $draft = ai_draft($draftId);
    if (!$draft || $draft['status'] !== 'draft') throw new RuntimeException('Draft not available.');
    $json = json_decode($draft['response_json'] ?? '[]', true);
    $data = $json['data'] ?? [];

    db()->prepare(
        'INSERT INTO weekly_student_summaries
          (student_user_id, generated_by_user_id, week_start, week_end, summary_text, went_well, focus_areas, next_week_focus, engagement_level, source_ai_draft_id, status)
         VALUES
          (:student, :owner, DATE_SUB(CURDATE(), INTERVAL 6 DAY), CURDATE(), :summary, :went_well, :focus, :next_focus, :engagement, :draft, "saved")'
    )->execute([
        ':student' => $studentId,
        ':owner' => $ownerId,
        ':summary' => $data['this_week_summary'] ?? $draft['response_text'],
        ':went_well' => $data['what_went_well'] ?? null,
        ':focus' => $data['areas_to_focus_on'] ?? null,
        ':next_focus' => $data['suggested_focus_next_week'] ?? null,
        ':engagement' => in_array(($data['engagement_level'] ?? 'Medium'), ['High','Medium','Low'], true) ? $data['engagement_level'] : 'Medium',
        ':draft' => $draftId,
    ]);

    $summaryId = (int) db()->lastInsertId();
    ai_mark_applied($draftId, 'weekly_student_summary', (string) $summaryId, $ownerId);
    return $summaryId;
}

function ai_apply_article_draft(int $draftId, int $ownerId): int
{
    $draft = ai_draft($draftId);
    if (!$draft || $draft['status'] !== 'draft') throw new RuntimeException('Draft not available.');
    $json = json_decode($draft['response_json'] ?? '[]', true);
    $data = $json['data'] ?? [];

    $slug = $data['slug'] ?? ('ai-article-draft-' . date('YmdHis'));

    db()->prepare(
        'INSERT INTO articles
          (title_ar, title_en, slug, seo_meta_title, seo_meta_description, keywords, cta, target_audience, cover_image_prompt, excerpt_ar, body_ar, status, source_ai_draft_id)
         VALUES
          (:title_ar, :title_en, :slug, :seo_title, :seo_description, :keywords, :cta, :audience, :cover_prompt, :excerpt, :body, "draft", :draft)'
    )->execute([
        ':title_ar' => $data['title_ar'] ?? 'مقال جديد',
        ':title_en' => $data['title_en'] ?? null,
        ':slug' => $slug,
        ':seo_title' => $data['seo_meta_title'] ?? null,
        ':seo_description' => $data['seo_meta_description'] ?? null,
        ':keywords' => $data['keywords'] ?? null,
        ':cta' => $data['cta'] ?? null,
        ':audience' => $data['target_audience'] ?? null,
        ':cover_prompt' => $data['cover_image_prompt'] ?? null,
        ':excerpt' => $data['excerpt_ar'] ?? null,
        ':body' => $data['body_ar'] ?? $draft['response_text'],
        ':draft' => $draftId,
    ]);

    $articleId = (int) db()->lastInsertId();
    ai_mark_applied($draftId, 'article', (string) $articleId, $ownerId);
    return $articleId;
}

function ai_mark_applied(int $draftId, string $appliedType, string $appliedId, int $ownerId): void
{
    db()->prepare('UPDATE ai_drafts SET status = "applied", applied_to_type = :type, applied_to_id = :id, applied_at = NOW() WHERE id = :draft')
        ->execute([':type' => $appliedType, ':id' => $appliedId, ':draft' => $draftId]);
    db()->prepare('UPDATE ai_usage_logs SET status = "applied" WHERE id = (SELECT usage_log_id FROM ai_drafts WHERE id = :draft)')
        ->execute([':draft' => $draftId]);
    audit_log($ownerId, 'ai_draft_applied', 'ai_draft', (string) $draftId, ['applied_to_type' => $appliedType, 'applied_to_id' => $appliedId]);
}
