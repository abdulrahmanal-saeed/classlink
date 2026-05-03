<?php
/**
 * /level-check?intakeId={id}
 *
 * Adult level check or child literacy check depending on intake learner type.
 */

require_once __DIR__ . '/../../../backend/php/shared/LevelCheck.php';
require_once __DIR__ . '/../../../web/components/layout/public_layout.php';

$intakeId = (int) ($_GET['intakeId'] ?? 0);
$intake = $intakeId ? level_get_intake($intakeId) : null;
$errors = [];

if (!$intake) {
    http_response_code(404);
    $content = '<section class="py-5"><div class="container"><div class="foundation-card"><h1 class="hero-title">Intake not found</h1><p class="text-muted">We could not find this student form.</p></div></div></section>';
    render_public_layout('Level Check Not Found | Habiba Nabil Arabic Academy', 'Level check intake not found.', $content, false);
    exit;
}

$isChild = str_contains((string) $intake['learner_type'], 'child');
$adultQuestions = level_check_adult_questions();
$childQuestions = level_check_child_questions();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if ($isChild) {
            $letterCorrect = 0;
            $letterTotal = count($childQuestions['letters']) + count($childQuestions['similar']);
            $answersToSave = [];

            foreach ($childQuestions['letters'] as $question) {
                $answer = $_POST[$question['key']] ?? '';
                $isCorrect = $answer === $question['answer'];
                $letterCorrect += $isCorrect ? 1 : 0;
                $answersToSave[] = ['section' => 'letter_recognition', 'question' => $question, 'answer' => $answer, 'score' => $isCorrect ? 1 : 0];
            }

            foreach ($childQuestions['similar'] as $question) {
                $answer = $_POST[$question['key']] ?? '';
                $isCorrect = $answer === $question['answer'];
                $letterCorrect += $isCorrect ? 1 : 0;
                $answersToSave[] = ['section' => 'similar_letters', 'question' => $question, 'answer' => $answer, 'score' => $isCorrect ? 1 : 0];
            }

            $letterScore = level_score_percent($letterCorrect, $letterTotal);
            $parentPayload = [
                'parent_observation' => trim($_POST['parent_observation'] ?? ''),
                'child_attention_notes' => trim($_POST['child_attention_notes'] ?? ''),
            ];
            $suggestedLevel = level_child_suggested_level($letterScore, $parentPayload);
            $recommended = level_recommended_first_lesson('child_literacy', $suggestedLevel);

            $attemptId = level_create_attempt_from_intake($intake, 'child_literacy', [
                'auto' => $letterScore,
                'letter' => $letterScore,
            ], $suggestedLevel, $recommended);

            level_save_answer($attemptId, 'parent_questions', 'parent_observation', 'Parent observation', $parentPayload['parent_observation'], null, null, $parentPayload);
            level_save_answer($attemptId, 'parent_questions', 'child_attention_notes', 'Child attention notes', $parentPayload['child_attention_notes'], null, null, $parentPayload);

            foreach ($answersToSave as $item) {
                level_save_answer($attemptId, $item['section'], $item['question']['key'], $item['question']['q'], $item['answer'], $item['question']['answer'], (float) $item['score'], ['options' => $item['question']['options']]);
            }

            level_store_upload($attemptId, 'reading_audio', 'reading_audio', 'audio');
            level_store_upload($attemptId, 'writing_upload', 'writing_upload', 'document');
            level_store_upload($attemptId, 'dictation_upload', 'dictation_upload', 'document');
        } else {
            $vocabCorrect = 0;
            $sentenceCorrect = 0;
            $readingCorrect = 0;
            $answersToSave = [];

            foreach ($adultQuestions['vocabulary'] as $question) {
                $answer = $_POST[$question['key']] ?? '';
                $isCorrect = $answer === $question['answer'];
                $vocabCorrect += $isCorrect ? 1 : 0;
                $answersToSave[] = ['section' => 'vocabulary', 'question' => $question, 'answer' => $answer, 'score' => $isCorrect ? 1 : 0];
            }

            foreach ($adultQuestions['sentence'] as $question) {
                $answer = $_POST[$question['key']] ?? '';
                $isCorrect = $answer === $question['answer'];
                $sentenceCorrect += $isCorrect ? 1 : 0;
                $answersToSave[] = ['section' => 'sentence_building', 'question' => $question, 'answer' => $answer, 'score' => $isCorrect ? 1 : 0];
            }

            foreach ($adultQuestions['reading']['questions'] as $question) {
                $answer = $_POST[$question['key']] ?? '';
                $isCorrect = $answer === $question['answer'];
                $readingCorrect += $isCorrect ? 1 : 0;
                $answersToSave[] = ['section' => 'reading', 'question' => $question, 'answer' => $answer, 'score' => $isCorrect ? 1 : 0];
            }

            $vocabScore = level_score_percent($vocabCorrect, count($adultQuestions['vocabulary']));
            $sentenceScore = level_score_percent($sentenceCorrect, count($adultQuestions['sentence']));
            $readingScore = level_score_percent($readingCorrect, count($adultQuestions['reading']['questions']));
            $autoScore = round(($vocabScore + $sentenceScore + $readingScore) / 3, 2);
            $suggestedLevel = level_adult_suggested_level($autoScore);
            $recommended = level_recommended_first_lesson('adult', $suggestedLevel);

            $attemptId = level_create_attempt_from_intake($intake, 'adult', [
                'auto' => $autoScore,
                'vocabulary' => $vocabScore,
                'sentence' => $sentenceScore,
                'reading' => $readingScore,
            ], $suggestedLevel, $recommended);

            level_save_answer($attemptId, 'self_assessment', 'self_assessment', 'Self assessment', $_POST['self_assessment'] ?? '', null, null);

            foreach ($answersToSave as $item) {
                level_save_answer($attemptId, $item['section'], $item['question']['key'], $item['question']['q'], $item['answer'], $item['question']['answer'], (float) $item['score'], ['options' => $item['question']['options']]);
            }

            level_save_answer($attemptId, 'reading_text', 'reading_text', $adultQuestions['reading']['text'], 'shown', null, null);
            level_save_answer($attemptId, 'writing', 'writing_answer', 'Write 5-7 sentences about yourself and why you want to learn Arabic.', trim($_POST['writing_answer'] ?? ''), null, null);
            level_store_upload($attemptId, 'speaking_audio', 'speaking_audio', 'audio');
        }

        level_mark_purchase_submitted((int) $intake['purchase_id']);
        audit_log(null, 'level_check_submitted', 'level_check_attempt', (string) $attemptId, [
            'intake_id' => $intakeId,
            'attempt_type' => $isChild ? 'child_literacy' : 'adult',
        ]);

        header('Location: /level-check-thank-you?attemptId=' . urlencode((string) $attemptId));
        exit;
    } catch (Throwable $exception) {
        $errors[] = $exception->getMessage();
    }
}

ob_start();
?>
<section class="py-5">
  <div class="container">
    <div class="foundation-card">
      <div class="badge text-bg-light border mb-3">Intake ID: <?= (int) $intakeId ?></div>
      <h1 class="hero-title mb-3"><?= $isChild ? 'Child literacy check' : 'Adult Arabic level check' ?></h1>
      <p class="hero-subtitle">Please complete the check carefully. Manual review by the Owner/Teacher decides the final level.</p>

      <?php if ($errors): ?>
        <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $error): ?><li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul></div>
      <?php endif; ?>

      <form method="post" enctype="multipart/form-data" class="row g-4">
        <?php if ($isChild): ?>
          <div class="col-12"><div class="status-box"><h2 class="h4 fw-bold">1. Parent questions</h2><label class="form-label">What should the tutor know about the child?</label><textarea class="form-control" name="parent_observation" rows="3" required></textarea><label class="form-label mt-3">Attention / learning style notes</label><textarea class="form-control" name="child_attention_notes" rows="3" required></textarea></div></div>

          <div class="col-12"><h2 class="h4 fw-bold">2. Letter recognition</h2></div>
          <?php foreach ($childQuestions['letters'] as $question): ?>
            <div class="col-md-6"><div class="border rounded-4 p-3 h-100"><label class="form-label fw-bold"><?= htmlspecialchars($question['q'], ENT_QUOTES, 'UTF-8') ?></label><?php foreach ($question['options'] as $option): ?><div class="form-check"><input class="form-check-input" type="radio" name="<?= $question['key'] ?>" value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>" required><label class="form-check-label fs-4"><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></label></div><?php endforeach; ?></div></div>
          <?php endforeach; ?>

          <div class="col-12"><h2 class="h4 fw-bold">3. Similar letters</h2></div>
          <?php foreach ($childQuestions['similar'] as $question): ?>
            <div class="col-md-6"><div class="border rounded-4 p-3 h-100"><label class="form-label fw-bold"><?= htmlspecialchars($question['q'], ENT_QUOTES, 'UTF-8') ?></label><?php foreach ($question['options'] as $option): ?><div class="form-check"><input class="form-check-input" type="radio" name="<?= $question['key'] ?>" value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>" required><label class="form-check-label fs-4"><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></label></div><?php endforeach; ?></div></div>
          <?php endforeach; ?>

          <div class="col-md-4"><label class="form-label">4. Reading audio upload</label><input class="form-control" type="file" name="reading_audio" accept=".mp3,.wav,.m4a,.webm" required><small class="text-muted">mp3, wav, m4a, webm up to 25MB</small></div>
          <div class="col-md-4"><label class="form-label">5. Writing upload/photo</label><input class="form-control" type="file" name="writing_upload" accept=".jpg,.jpeg,.png,.pdf" required><small class="text-muted">jpg, png, pdf up to 10MB</small></div>
          <div class="col-md-4"><label class="form-label">6. Dictation upload/photo</label><input class="form-control" type="file" name="dictation_upload" accept=".jpg,.jpeg,.png,.pdf" required><small class="text-muted">jpg, png, pdf up to 10MB</small></div>
        <?php else: ?>
          <div class="col-12"><div class="status-box"><h2 class="h4 fw-bold">1. Self assessment</h2><select class="form-select" name="self_assessment" required><option value="">Choose...</option><option>Complete beginner</option><option>I know some words</option><option>I understand but cannot speak well</option><option>I can speak simple sentences</option><option>I can have basic conversations</option></select></div></div>

          <div class="col-12"><h2 class="h4 fw-bold">2. Vocabulary MCQ</h2></div>
          <?php foreach ($adultQuestions['vocabulary'] as $question): ?>
            <div class="col-md-6"><div class="border rounded-4 p-3 h-100"><label class="form-label fw-bold"><?= htmlspecialchars($question['q'], ENT_QUOTES, 'UTF-8') ?></label><?php foreach ($question['options'] as $option): ?><div class="form-check"><input class="form-check-input" type="radio" name="<?= $question['key'] ?>" value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>" required><label class="form-check-label"><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></label></div><?php endforeach; ?></div></div>
          <?php endforeach; ?>

          <div class="col-12"><h2 class="h4 fw-bold">3. Sentence Building</h2></div>
          <?php foreach ($adultQuestions['sentence'] as $question): ?>
            <div class="col-md-6"><div class="border rounded-4 p-3 h-100"><label class="form-label fw-bold"><?= htmlspecialchars($question['q'], ENT_QUOTES, 'UTF-8') ?></label><?php foreach ($question['options'] as $option): ?><div class="form-check"><input class="form-check-input" type="radio" name="<?= $question['key'] ?>" value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>" required><label class="form-check-label"><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></label></div><?php endforeach; ?></div></div>
          <?php endforeach; ?>

          <div class="col-12"><div class="status-box"><h2 class="h4 fw-bold">4. Reading Comprehension</h2><p class="mb-0" dir="rtl"><?= htmlspecialchars($adultQuestions['reading']['text'], ENT_QUOTES, 'UTF-8') ?></p></div></div>
          <?php foreach ($adultQuestions['reading']['questions'] as $question): ?>
            <div class="col-md-4"><div class="border rounded-4 p-3 h-100"><label class="form-label fw-bold"><?= htmlspecialchars($question['q'], ENT_QUOTES, 'UTF-8') ?></label><?php foreach ($question['options'] as $option): ?><div class="form-check"><input class="form-check-input" type="radio" name="<?= $question['key'] ?>" value="<?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?>" required><label class="form-check-label"><?= htmlspecialchars($option, ENT_QUOTES, 'UTF-8') ?></label></div><?php endforeach; ?></div></div>
          <?php endforeach; ?>

          <div class="col-md-6"><label class="form-label">5. Writing</label><textarea class="form-control" name="writing_answer" rows="6" required placeholder="Write 5-7 sentences about yourself and why you want to learn Arabic."></textarea></div>
          <div class="col-md-6"><label class="form-label">6. Speaking audio upload</label><input class="form-control" type="file" name="speaking_audio" accept=".mp3,.wav,.m4a,.webm" required><small class="text-muted">mp3, wav, m4a, webm up to 25MB</small></div>
        <?php endif; ?>

        <div class="col-12"><button class="btn btn-brand btn-lg" type="submit">Submit level check</button></div>
      </form>
    </div>
  </div>
</section>
<?php
render_public_layout(($isChild ? 'Child Literacy Check' : 'Adult Level Check') . ' | Habiba Nabil Arabic Academy', 'Complete your Arabic level check.', ob_get_clean(), false);
