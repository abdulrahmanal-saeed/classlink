<?php
/**
 * /student/flashcards
 * Flashcard review system using Phase 15 spaced repetition basics.
 */

require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../backend/php/shared/LearningEngagement.php';
require_once __DIR__ . '/../../../../backend/php/shared/Analytics.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';

$user = require_role('student');
$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        engagement_review_flashcard((int) $user['id'], (int) $_POST['word_id'], $_POST['rating'] ?? 'almost');
        analytics_track('flashcard_review', ['role' => 'student', 'entity_type' => 'practice_word', 'entity_id' => (int) $_POST['word_id'], 'metadata' => ['rating' => $_POST['rating'] ?? 'almost']], (int) $user['id']);
        $message = 'Flashcard reviewed. Next review date updated.';
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
    }
}

$due = engagement_due_flashcards((int) $user['id']);

ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
  <div>
    <p class="text-muted mb-1">Review due practice words.</p>
    <small class="text-muted">Got it → 3 days. Almost → 1 day. Missed it → today/tomorrow.</small>
  </div>
  <a class="btn btn-outline-brand" href="/student/practice-words">All practice words</a>
</div>
<?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

<?php if (!$due): ?>
  <div class="alert alert-light border">No flashcards due right now. Great job!</div>
<?php else: ?>
  <div class="row g-3">
    <?php foreach ($due as $word): ?>
      <div class="col-md-6">
        <div class="foundation-card h-100">
          <h2 class="display-6 fw-bold" dir="rtl"><?= htmlspecialchars($word['word_ar'], ENT_QUOTES, 'UTF-8') ?></h2>
          <p class="text-muted mb-2"><?= htmlspecialchars($word['word_en'] ?? '-', ENT_QUOTES, 'UTF-8') ?></p>
          <?php if (!empty($word['example_sentence_ar'])): ?><div dir="rtl" class="mb-3"><?= htmlspecialchars($word['example_sentence_ar'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
          <div class="small text-muted mb-3">Mastery: <?= (int) $word['mastery_level'] ?>/5</div>
          <form method="post" class="d-flex gap-2 flex-wrap">
            <input type="hidden" name="word_id" value="<?= (int) $word['id'] ?>">
            <button class="btn btn-sm btn-success" name="rating" value="got_it">Got it</button>
            <button class="btn btn-sm btn-warning" name="rating" value="almost">Almost</button>
            <button class="btn btn-sm btn-outline-danger" name="rating" value="missed">Missed it</button>
          </form>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Flashcards', $content);
