<?php
/**
 * /level-check-intro?ref={checkoutReference}
 *
 * Placeholder intro after student form submission. Full level check flow will be
 * built in the level-check phase; this page confirms onboarding status progression.
 */

require_once __DIR__ . '/../../../backend/php/shared/Onboarding.php';
require_once __DIR__ . '/../../../web/components/layout/public_layout.php';

$reference = onboarding_clean_reference($_GET['ref'] ?? '');
$purchase = $reference ? onboarding_find_checkout($reference) : null;

if (!$purchase) {
    http_response_code(404);
    $content = '<section class="py-5"><div class="container"><div class="foundation-card"><h1 class="hero-title">Reference not found</h1><p class="text-muted">We could not find this onboarding reference.</p></div></div></section>';
    render_public_layout('Reference not found | Habiba Nabil Arabic Academy', 'Onboarding reference not found.', $content, false);
    exit;
}

ob_start();
?>
<section class="py-5">
  <div class="container">
    <div class="foundation-card" style="max-width: 820px; margin:auto;">
      <div class="badge text-bg-light border mb-3">Reference: <?= htmlspecialchars($reference, ENT_QUOTES, 'UTF-8') ?></div>
      <h1 class="hero-title mb-3">Next: Arabic level check</h1>
      <p class="hero-subtitle">Your student form was received. The full level check will be built in a later phase.</p>
      <div class="row g-3 my-4">
        <div class="col-md-6"><div class="status-box"><strong>Student form</strong><br><?= htmlspecialchars($purchase['student_form_status'] ?? 'submitted', ENT_QUOTES, 'UTF-8') ?></div></div>
        <div class="col-md-6"><div class="status-box"><strong>Level check</strong><br><?= htmlspecialchars($purchase['level_check_status'] ?? 'not_started', ENT_QUOTES, 'UTF-8') ?></div></div>
      </div>
      <div class="alert alert-light border">For now, the Owner can review your submission in the onboarding pipeline.</div>
      <a class="btn btn-brand" href="/">Back to homepage</a>
    </div>
  </div>
</section>
<?php
render_public_layout('Level Check Intro | Habiba Nabil Arabic Academy', 'Start your Arabic level check after onboarding.', ob_get_clean(), false);
