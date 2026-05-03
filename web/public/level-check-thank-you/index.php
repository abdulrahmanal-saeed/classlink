<?php
/**
 * /level-check-thank-you?attemptId={id}
 *
 * Shows submitted level check summary and reminds learner that Owner/Teacher
 * final review decides the real final level.
 */

require_once __DIR__ . '/../../../backend/php/shared/LevelCheck.php';
require_once __DIR__ . '/../../../web/components/layout/public_layout.php';

$attemptId = (int) ($_GET['attemptId'] ?? 0);
$attempt = $attemptId ? level_attempt_detail($attemptId) : null;

if (!$attempt) {
    http_response_code(404);
    $content = '<section class="py-5"><div class="container"><div class="foundation-card"><h1 class="hero-title">Attempt not found</h1><p class="text-muted">We could not find this level check attempt.</p></div></div></section>';
    render_public_layout('Level Check Not Found | Habiba Nabil Arabic Academy', 'Level check attempt not found.', $content, false);
    exit;
}

ob_start();
?>
<section class="py-5">
  <div class="container">
    <div class="foundation-card" style="max-width: 900px; margin:auto;">
      <div class="badge text-bg-light border mb-3">Attempt ID: <?= (int) $attemptId ?></div>
      <h1 class="hero-title mb-3">Thank you — your level check was submitted</h1>
      <p class="hero-subtitle">The Owner/Teacher will review your answers, writing, and recording before confirming the final level.</p>

      <div class="row g-3 my-4">
        <div class="col-md-4"><div class="status-box h-100"><strong>Attempt type</strong><br><?= htmlspecialchars($attempt['attempt_type'], ENT_QUOTES, 'UTF-8') ?></div></div>
        <div class="col-md-4"><div class="status-box h-100"><strong>Auto score</strong><br><?= htmlspecialchars((string) ($attempt['auto_score'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>%</div></div>
        <div class="col-md-4"><div class="status-box h-100"><strong>Suggested level</strong><br><?= htmlspecialchars($attempt['suggested_level'] ?? '-', ENT_QUOTES, 'UTF-8') ?></div></div>
      </div>

      <div class="alert alert-light border">
        Important: a high auto score with weak speaking should not over-place the student. Owner final review decides.
      </div>

      <h2 class="h5 fw-bold">Recommended first lesson</h2>
      <p class="text-muted"><?= htmlspecialchars($attempt['recommended_first_lesson'] ?? 'The tutor will prepare your first lesson after review.', ENT_QUOTES, 'UTF-8') ?></p>

      <a class="btn btn-brand" href="/">Back to homepage</a>
    </div>
  </div>
</section>
<?php
render_public_layout('Level Check Submitted | Habiba Nabil Arabic Academy', 'Your Arabic level check was submitted.', ob_get_clean(), false);
