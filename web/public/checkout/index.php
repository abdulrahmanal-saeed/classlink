<?php
/**
 * /checkout?plan=...
 *
 * Phase 3 safe checkout placeholder. Pricing CTAs route here instead of going
 * directly to payment, so payment verification can be built in a later phase.
 */

require_once __DIR__ . '/../../../web/components/layout/public_layout.php';

$plan = preg_replace('/[^a-z0-9_\-]/', '', strtolower($_GET['plan'] ?? ''));

ob_start();
?>
<section class="py-5">
  <div class="container">
    <div class="foundation-card" style="max-width:720px;margin:auto;">
      <h1 class="hero-title mb-3">Checkout preparation</h1>
      <p class="text-muted">You selected: <strong><?= htmlspecialchars($plan ?: 'unknown', ENT_QUOTES, 'UTF-8') ?></strong></p>
      <div class="alert alert-light border">Secure payment and post-payment onboarding will be implemented in the checkout phase. No payment is collected here yet.</div>
      <a class="btn btn-brand" href="/pricing">Back to pricing</a>
    </div>
  </div>
</section>
<?php
render_public_layout('Checkout | Habiba Nabil Arabic Academy', 'Prepare your Arabic lesson package checkout securely.', ob_get_clean(), true);
