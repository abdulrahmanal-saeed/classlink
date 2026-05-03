<?php
/**
 * /level-test/thank-you?token=...
 */

require_once __DIR__ . '/../../../../backend/php/shared/FreeLevelTest.php';
require_once __DIR__ . '/../../../../web/components/layout/public_layout.php';

$token = preg_replace('/[^a-f0-9]/', '', $_GET['token'] ?? '');
$attempt = $token ? flt_attempt_by_token($token) : null;

if (!$attempt || $attempt['test_type'] !== 'full') {
    http_response_code(404);
    render_public_layout('Test Not Found', 'Placement test attempt not found.', '<section class="py-5"><div class="container"><div class="foundation-card"><h1 class="hero-title">Attempt not found</h1></div></div></section>', false);
    exit;
}

ob_start();
?>
<section class="py-5">
  <div class="container">
    <div class="foundation-card" style="max-width: 860px; margin:auto;">
      <div class="badge text-bg-light border mb-3">Attempt received</div>
      <h1 class="hero-title mb-3">Thank you — your free placement test was submitted</h1>
      <p class="hero-subtitle">Writing + speaking are reviewed by the teacher. Your result and next step will be sent within 48 hours by WhatsApp/email.</p>
      <div class="row g-3 my-4">
        <div class="col-md-4"><div class="status-box h-100"><strong>Listening estimate</strong><br><?= htmlspecialchars($attempt['listening_estimated_level'] ?? '-', ENT_QUOTES, 'UTF-8') ?></div></div>
        <div class="col-md-4"><div class="status-box h-100"><strong>Reading estimate</strong><br><?= htmlspecialchars($attempt['reading_estimated_level'] ?? '-', ENT_QUOTES, 'UTF-8') ?></div></div>
        <div class="col-md-4"><div class="status-box h-100"><strong>Auto estimate</strong><br><?= htmlspecialchars($attempt['auto_estimated_level'] ?? '-', ENT_QUOTES, 'UTF-8') ?></div></div>
      </div>
      <div class="alert alert-light border">Final level is not automatic. Owner/Teacher confirms final level after writing and speaking review.</div>
      <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-brand" href="/pricing">View pricing</a>
        <a class="btn btn-outline-brand" href="/">Back to homepage</a>
      </div>
    </div>
  </div>
</section>
<?php
render_public_layout('Free Placement Test Submitted | Habiba Nabil Arabic Academy', 'Your free Arabic placement test was submitted.', ob_get_clean(), false);
