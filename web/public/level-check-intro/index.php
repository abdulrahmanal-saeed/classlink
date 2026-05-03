<?php
/**
 * /level-check-intro?intakeId={id}
 *
 * Intro page before adult level check or child literacy check.
 */

require_once __DIR__ . '/../../../backend/php/shared/LevelCheck.php';
require_once __DIR__ . '/../../../web/components/layout/public_layout.php';

$intakeId = (int) ($_GET['intakeId'] ?? 0);
$intake = $intakeId ? level_get_intake($intakeId) : null;

if (!$intake) {
    http_response_code(404);
    $content = '<section class="py-5"><div class="container"><div class="foundation-card"><h1 class="hero-title">Intake not found</h1><p class="text-muted">We could not find this student form.</p></div></div></section>';
    render_public_layout('Intake not found | Habiba Nabil Arabic Academy', 'Student form not found.', $content, false);
    exit;
}

$isChild = str_contains((string) $intake['learner_type'], 'child');
$checkTitle = $isChild ? 'Child literacy check' : 'Adult Arabic level check';

ob_start();
?>
<section class="py-5">
  <div class="container">
    <div class="foundation-card" style="max-width: 900px; margin:auto;">
      <div class="badge text-bg-light border mb-3">Intake ID: <?= (int) $intakeId ?></div>
      <h1 class="hero-title mb-3"><?= htmlspecialchars($checkTitle, ENT_QUOTES, 'UTF-8') ?></h1>
      <p class="hero-subtitle">This check helps the tutor prepare your personalized first lesson. The final level is confirmed by the Owner/Teacher after review.</p>

      <?php if ($isChild): ?>
        <div class="status-box mb-4">
          <strong>Child literacy check includes:</strong>
          <ol class="mb-0 mt-2">
            <li>Parent questions</li>
            <li>Letter recognition</li>
            <li>Similar letters</li>
            <li>Reading audio upload</li>
            <li>Writing upload/photo</li>
            <li>Dictation upload/photo</li>
          </ol>
        </div>
      <?php else: ?>
        <div class="status-box mb-4">
          <strong>Adult level check includes:</strong>
          <ol class="mb-0 mt-2">
            <li>Self assessment</li>
            <li>Vocabulary MCQ</li>
            <li>Sentence building</li>
            <li>Reading comprehension</li>
            <li>Writing</li>
            <li>Speaking audio upload</li>
          </ol>
        </div>
        <div class="alert alert-light border">Important: a high auto score with weak speaking should not over-place the student. Owner final review decides.</div>
      <?php endif; ?>

      <a class="btn btn-brand btn-lg" href="/level-check?intakeId=<?= (int) $intakeId ?>">Start check</a>
    </div>
  </div>
</section>
<?php
render_public_layout($checkTitle . ' | Habiba Nabil Arabic Academy', 'Start your Arabic level or literacy check.', ob_get_clean(), false);
