<?php
/**
 * /level-test/start?token=...
 * Full free placement test. Step order: Listening → Reading → Writing + Speaking.
 * Snapshot is generated once at registration and reused on refresh.
 */

require_once __DIR__ . '/../../../../backend/php/shared/FreeLevelTest.php';
require_once __DIR__ . '/../../../../web/components/layout/public_layout.php';

$token = preg_replace('/[^a-f0-9]/', '', $_GET['token'] ?? '');
$attempt = $token ? flt_attempt_by_token($token) : null;
$errors = [];

if (!$attempt || $attempt['test_type'] !== 'full') {
    http_response_code(404);
    render_public_layout('Full Test Not Found', 'Full placement attempt not found.', '<section class="py-5"><div class="container"><div class="foundation-card"><h1 class="hero-title">Attempt not found</h1></div></div></section>', false);
    exit;
}

$snapshot = json_decode($attempt['snapshot_json'], true) ?: [];
$currentStep = $attempt['current_step'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $postedStep = $_POST['step'] ?? '';
        if ($postedStep !== $currentStep) {
            throw new RuntimeException('Invalid step. Please continue from your current test step.');
        }

        if ($currentStep === 'listening') {
            $result = flt_grade_listening_answers($snapshot, $_POST, (int) $attempt['id']);
            $level = flt_percent_to_full_level((float) $result['percent']);
            db()->prepare('UPDATE free_level_test_attempts SET listening_score = :score, listening_estimated_level = :level, current_step = "reading" WHERE id = :id')
                ->execute([':score' => $result['percent'], ':level' => $level, ':id' => (int) $attempt['id']]);
            header('Location: /level-test/start?token=' . urlencode($token));
            exit;
        }

        if ($currentStep === 'reading') {
            $result = flt_grade_reading_answers($snapshot, $_POST, (int) $attempt['id'], 'reading');
            $level = flt_percent_to_full_level((float) $result['percent']);
            $attemptFresh = flt_attempt_by_token($token);
            $listeningLevel = $attemptFresh['listening_estimated_level'] ?: 'A2 or below';
            $autoLevel = flt_pick_highest_level([$listeningLevel, $level]);
            $writingTarget = in_array($autoLevel, ['C1','C2'], true) ? $autoLevel : (in_array($autoLevel, ['B1','B2'], true) ? $autoLevel : 'B1');
            db()->prepare('UPDATE free_level_test_attempts SET reading_score = :score, reading_estimated_level = :level, auto_estimated_level = :auto, writing_target_level = :writing, current_step = "writing_speaking" WHERE id = :id')
                ->execute([':score' => $result['percent'], ':level' => $level, ':auto' => $autoLevel, ':writing' => $writingTarget, ':id' => (int) $attempt['id']]);
            header('Location: /level-test/start?token=' . urlencode($token));
            exit;
        }

        if ($currentStep === 'writing_speaking') {
            foreach (($snapshot['writing_prompts'] ?? []) as $prompt) {
                $field = 'writing_' . $prompt['id'];
                $answer = trim($_POST[$field] ?? '');
                if ($answer === '') throw new RuntimeException('Please complete all writing tasks.');
                flt_save_answer((int) $attempt['id'], 'writing', 'writing', (int) $prompt['id'], null, $prompt['prompt_text'], $answer, null, null, 0, 0);
            }
            foreach (($snapshot['speaking_prompts'] ?? []) as $prompt) {
                flt_save_answer((int) $attempt['id'], 'speaking_prompt', 'speaking', (int) $prompt['id'], null, $prompt['prompt_text'], 'shown', null, null, 0, 0);
            }
            flt_store_upload((int) $attempt['id'], 'speaking_upload', 'speaking_upload', 'audio');
            db()->prepare('UPDATE free_level_test_attempts SET status = "submitted", current_step = "submitted", submitted_at = NOW() WHERE id = :id')
                ->execute([':id' => (int) $attempt['id']]);
            audit_log(null, 'free_full_level_test_submitted', 'free_level_test_attempt', (string) $attempt['id'], ['token' => $token]);
            header('Location: /level-test/thank-you?token=' . urlencode($token));
            exit;
        }
    } catch (Throwable $exception) {
        $errors[] = $exception->getMessage();
    }
}

function percent_label(string $step): string {
    return match ($step) {
        'listening' => '25%',
        'reading' => '55%',
        'writing_speaking' => '85%',
        default => '100%',
    };
}

ob_start();
?>
<section class="py-5">
  <div class="container">
    <div class="foundation-card">
      <div class="d-flex justify-content-between flex-wrap gap-3 mb-3">
        <div><h1 class="hero-title mb-1">Full Free Arabic Placement Test</h1><p class="text-muted mb-0">Listening → Reading → Writing + Speaking</p></div>
        <div class="badge text-bg-light border align-self-start">Progress: <?= percent_label($currentStep) ?></div>
      </div>
      <div class="progress mb-4"><div class="progress-bar" style="width: <?= percent_label($currentStep) ?>"></div></div>
      <?php if (!empty($snapshot['warnings'])): ?><div class="alert alert-warning"><?= htmlspecialchars(implode(' ', $snapshot['warnings']), ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
      <?php if ($errors): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $error): ?><li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul></div><?php endif; ?>

      <?php if ($currentStep === 'listening'): ?>
        <form method="post" class="row g-4">
          <input type="hidden" name="step" value="listening">
          <div class="col-12"><div class="alert alert-light border">Use headphones if possible. You can replay audio.</div></div>
          <?php foreach (($snapshot['listening_scripts'] ?? []) as $block): ?>
            <div class="col-12"><div class="status-box"><h2 class="h5 fw-bold"><?= htmlspecialchars($block['script']['title'], ENT_QUOTES, 'UTF-8') ?></h2><audio controls class="w-100"><source src="<?= htmlspecialchars($block['script']['audio_url'], ENT_QUOTES, 'UTF-8') ?>"></audio></div></div>
            <?php foreach ($block['questions'] as $q): ?>
              <div class="col-md-6"><div class="border rounded-4 p-3 h-100"><label class="form-label fw-bold"><?= htmlspecialchars($q['question_text'], ENT_QUOTES, 'UTF-8') ?></label><?php foreach (['A','B','C','D'] as $opt): $field = 'option_' . strtolower($opt); ?><div class="form-check"><input class="form-check-input" type="radio" name="lq_<?= (int) $q['id'] ?>" value="<?= $opt ?>" required><label class="form-check-label"><?= $opt ?>. <?= htmlspecialchars($q[$field], ENT_QUOTES, 'UTF-8') ?></label></div><?php endforeach; ?><div class="form-check"><input class="form-check-input" type="radio" name="lq_<?= (int) $q['id'] ?>" value="X" required><label class="form-check-label">X. I don’t know</label></div></div></div>
            <?php endforeach; ?>
          <?php endforeach; ?>
          <div class="col-12"><button class="btn btn-brand btn-lg" type="submit">Continue to Reading</button></div>
        </form>
      <?php elseif ($currentStep === 'reading'): ?>
        <form method="post" class="row g-4">
          <input type="hidden" name="step" value="reading">
          <?php foreach (($snapshot['reading_texts'] ?? []) as $block): ?>
            <div class="col-12"><div class="status-box"><h2 class="h5 fw-bold"><?= htmlspecialchars($block['text']['title'], ENT_QUOTES, 'UTF-8') ?></h2><p dir="rtl" class="mb-0"><?= nl2br(htmlspecialchars($block['text']['passage_text'], ENT_QUOTES, 'UTF-8')) ?></p></div></div>
            <?php foreach ($block['questions'] as $q): ?>
              <div class="col-md-6"><div class="border rounded-4 p-3 h-100"><label class="form-label fw-bold"><?= htmlspecialchars($q['question_text'], ENT_QUOTES, 'UTF-8') ?></label><?php foreach (['A','B','C','D'] as $opt): $field = 'option_' . strtolower($opt); ?><div class="form-check"><input class="form-check-input" type="radio" name="q_<?= (int) $q['id'] ?>" value="<?= $opt ?>" required><label class="form-check-label"><?= $opt ?>. <?= htmlspecialchars($q[$field], ENT_QUOTES, 'UTF-8') ?></label></div><?php endforeach; ?><div class="form-check"><input class="form-check-input" type="radio" name="q_<?= (int) $q['id'] ?>" value="X" required><label class="form-check-label">X. I don’t know</label></div></div></div>
            <?php endforeach; ?>
          <?php endforeach; ?>
          <div class="col-12"><button class="btn btn-brand btn-lg" type="submit">Continue to Writing + Speaking</button></div>
        </form>
      <?php elseif ($currentStep === 'writing_speaking'): ?>
        <form method="post" enctype="multipart/form-data" class="row g-4">
          <input type="hidden" name="step" value="writing_speaking">
          <div class="col-12"><h2 class="h4 fw-bold">Writing</h2></div>
          <?php foreach (($snapshot['writing_prompts'] ?? []) as $prompt): ?>
            <div class="col-12"><div class="border rounded-4 p-3"><h3 class="h5 fw-bold"><?= htmlspecialchars($prompt['title'], ENT_QUOTES, 'UTF-8') ?></h3><p><?= htmlspecialchars($prompt['prompt_text'], ENT_QUOTES, 'UTF-8') ?></p><small class="text-muted"><?= htmlspecialchars($prompt['instructions'] ?? '', ENT_QUOTES, 'UTF-8') ?></small><textarea class="form-control mt-3" name="writing_<?= (int) $prompt['id'] ?>" rows="6" required></textarea></div></div>
          <?php endforeach; ?>
          <div class="col-12"><h2 class="h4 fw-bold">Speaking</h2><p class="text-muted">Record using your phone or browser recorder, then upload one audio file.</p></div>
          <?php foreach (($snapshot['speaking_prompts'] ?? []) as $prompt): ?>
            <div class="col-md-6"><div class="status-box h-100"><strong><?= htmlspecialchars($prompt['phase'], ENT_QUOTES, 'UTF-8') ?>:</strong> <?= htmlspecialchars($prompt['prompt_text'], ENT_QUOTES, 'UTF-8') ?></div></div>
          <?php endforeach; ?>
          <div class="col-12"><label class="form-label">Speaking audio upload</label><input class="form-control" type="file" name="speaking_upload" accept=".mp3,.wav,.m4a,.webm" required><small class="text-muted">mp3, wav, m4a, webm up to 25MB</small></div>
          <div class="col-12"><button class="btn btn-brand btn-lg" type="submit">Submit full test</button></div>
        </form>
      <?php else: ?>
        <div class="alert alert-success">This attempt has already been submitted.</div><a class="btn btn-brand" href="/level-test/thank-you?token=<?= urlencode($token) ?>">View thank-you page</a>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php
render_public_layout('Full Free Arabic Placement Test | Habiba Nabil Arabic Academy', 'Complete your full free Arabic placement test.', ob_get_clean(), false);
