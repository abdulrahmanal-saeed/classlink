<?php
/**
 * /thank-you?ref={checkoutReference}&intent_id={paymentIntentId}
 *
 * Reaching this page never means payment is paid. If Ziina Payment Intent API
 * is configured, the page checks the intent status server-side. Only completed
 * becomes paid. Otherwise it stays pending_verification.
 */

require_once __DIR__ . '/../../../backend/php/shared/CheckoutFlow.php';
require_once __DIR__ . '/../../../web/components/layout/public_layout.php';

$reference = preg_replace('/[^A-Z0-9\-]/', '', strtoupper($_GET['ref'] ?? ''));
$intentId = preg_replace('/[^a-zA-Z0-9_\-]/', '', $_GET['intent_id'] ?? '');

if ($reference === '') {
    http_response_code(404);
    $content = '<section class="py-5"><div class="container"><div class="foundation-card"><h1 class="hero-title">Reference missing</h1><p class="text-muted">We could not find your checkout reference.</p><a class="btn btn-brand" href="/pricing">Back to pricing</a></div></div></section>';
    render_public_layout('Reference missing | Habiba Nabil Arabic Academy', 'Checkout reference missing.', $content, false);
    exit;
}

$verifiedStatus = checkout_verify_ziina_status($reference, $intentId ?: null);
$purchase = checkout_find_purchase_by_reference($reference);

if (!$purchase) {
    http_response_code(404);
    $content = '<section class="py-5"><div class="container"><div class="foundation-card"><h1 class="hero-title">Checkout not found</h1><p class="text-muted">This checkout reference was not found.</p><a class="btn btn-brand" href="/pricing">Back to pricing</a></div></div></section>';
    render_public_layout('Checkout not found | Habiba Nabil Arabic Academy', 'Checkout not found.', $content, false);
    exit;
}

$setupMissing = ($_GET['setup'] ?? '') === 'missing_payment_setup';
$statusLabel = $verifiedStatus ?: $purchase['status'];

ob_start();
?>
<section class="py-5">
  <div class="container">
    <div class="foundation-card" style="max-width: 900px; margin:auto;">
      <div class="badge text-bg-light border mb-3">Reference: <?= htmlspecialchars($reference, ENT_QUOTES, 'UTF-8') ?></div>
      <h1 class="hero-title display-5 mb-3">Thank you for your payment!</h1>
      <p class="hero-subtitle">Your Arabic learning journey has started 🎉</p>

      <?php if ($setupMissing): ?>
        <div class="alert alert-warning">Payment setup is not configured yet. Your checkout was saved for Owner review and testing.</div>
      <?php endif; ?>

      <?php if ($statusLabel === 'paid'): ?>
        <div class="alert alert-success">Your payment was verified successfully.</div>
      <?php elseif ($statusLabel === 'failed'): ?>
        <div class="alert alert-danger">The payment was not completed. Please contact us or try again.</div>
      <?php else: ?>
        <div class="alert alert-light border">Your payment may still be pending verification. Reaching this page does not automatically mark the payment as paid.</div>
      <?php endif; ?>

      <div class="row g-3 my-4">
        <div class="col-md-6"><div class="status-box h-100"><strong>Package</strong><br><?= htmlspecialchars($purchase['plan_name'] ?? 'Selected package', ENT_QUOTES, 'UTF-8') ?></div></div>
        <div class="col-md-6"><div class="status-box h-100"><strong>Status</strong><br><?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8') ?></div></div>
      </div>

      <h2 class="h4 fw-bold mb-3">Next steps</h2>
      <ol class="text-muted">
        <li>Complete student form</li>
        <li>Complete level check if required</li>
        <li>Choose lesson time</li>
        <li>Tutor prepares personalized first lesson</li>
      </ol>

      <div class="d-flex flex-wrap gap-2 mt-4">
        <a class="btn btn-brand" href="/student-form?ref=<?= urlencode($reference) ?>">Complete Student Form</a>
        <button class="btn btn-outline-brand" type="button" disabled>Choose Lesson Time</button>
      </div>

      <div class="mt-4 p-4 bg-light rounded-4 text-center text-muted">
        Optional welcome video placeholder
      </div>
    </div>
  </div>
</section>
<?php
render_public_layout('Thank You | Habiba Nabil Arabic Academy', 'Thank you for starting your Arabic learning journey.', ob_get_clean(), false);
