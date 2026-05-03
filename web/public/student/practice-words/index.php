<?php
/**
 * /student/practice-words
 * Student practice words and flashcard due status. Own data only.
 */

require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../backend/php/shared/StudentPortal.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';

$user = require_role('student');
$words = student_portal_practice_words((int) $user['id']);

ob_start();
?>
<p class="text-muted">Words your teacher added for review and speaking practice.</p>
<?php if (!$words): ?>
  <div class="alert alert-light border">No practice words yet.</div>
<?php else: ?>
  <div class="row g-3">
    <?php foreach ($words as $word): ?>
      <div class="col-md-6 col-lg-4">
        <div class="status-box h-100">
          <h2 class="h4 fw-bold" dir="rtl"><?= htmlspecialchars($word['word_ar'], ENT_QUOTES, 'UTF-8') ?></h2>
          <div class="text-muted mb-2"><?= htmlspecialchars($word['word_en'] ?? '-', ENT_QUOTES, 'UTF-8') ?></div>
          <?php if (!empty($word['example_sentence_ar'])): ?><div dir="rtl"><?= htmlspecialchars($word['example_sentence_ar'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
          <div class="small text-muted mt-2">Mastery: <?= (int) $word['mastery_level'] ?>/5</div>
          <div class="small text-muted">Next review: <?= htmlspecialchars($word['next_review_at'] ?? 'Due now', ENT_QUOTES, 'UTF-8') ?></div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Practice Words', $content);
