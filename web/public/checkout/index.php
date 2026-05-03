<?php
/**
 * /checkout?plan=single|monthly|bundle
 *
 * Collects pre-payment learner/contact data and creates a secure checkout
 * reference. It does not mark any payment as paid.
 */

require_once __DIR__ . '/../../../backend/php/shared/CheckoutFlow.php';
require_once __DIR__ . '/../../../web/components/layout/public_layout.php';

$planSlug = preg_replace('/[^a-z0-9_\-]/', '', strtolower($_GET['plan'] ?? ''));
$plan = checkout_find_plan($planSlug);
$error = null;
$setupMessage = null;

if (!$plan) {
    http_response_code(404);
    $content = '<section class="py-5"><div class="container"><div class="foundation-card"><h1 class="hero-title">Plan not found</h1><p class="text-muted">Please choose a valid package from the pricing page.</p><a class="btn btn-brand" href="/pricing">Back to pricing</a></div></div></section>';
    render_public_layout('Plan not found | Habiba Nabil Arabic Academy', 'Choose a valid Arabic lesson package.', $content, false);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'full_name' => trim($_POST['full_name'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'whatsapp' => trim($_POST['whatsapp'] ?? ''),
        'student_age' => (int) ($_POST['student_age'] ?? 0),
        'learner_type' => $_POST['learner_type'] ?? '',
        'main_goal' => $_POST['main_goal'] ?? '',
        'preferred_contact_method' => $_POST['preferred_contact_method'] ?? '',
    ];

    $allowedLearnerTypes = ['adult', 'child', 'someone_else'];
    $allowedGoals = ['Speaking', 'Reading & Writing', 'Work Arabic', 'Kids Arabic', 'Emirati Dialect', 'Not sure yet'];
    $allowedContact = ['whatsapp', 'email'];

    if ($data['full_name'] === '' || !filter_var($data['email'], FILTER_VALIDATE_EMAIL) || $data['whatsapp'] === '') {
        $error = 'Please enter your name, valid email, and WhatsApp number.';
    } elseif (!in_array($data['learner_type'], $allowedLearnerTypes, true)) {
        $error = 'Please choose who the learner is.';
    } elseif (!in_array($data['main_goal'], $allowedGoals, true)) {
        $error = 'Please choose your main goal.';
    } elseif (!in_array($data['preferred_contact_method'], $allowedContact, true)) {
        $error = 'Please choose a preferred contact method.';
    } elseif (empty($_POST['policy_agreement'])) {
        $error = 'You must agree to the policies before continuing.';
    } else {
        $checkout = checkout_create_purchase($data, $plan);
        $paymentLink = trim((string) setting_get('payment.ziina_link', ''));

        if ($paymentLink !== '') {
            $separator = str_contains($paymentLink, '?') ? '&' : '?';
            header('Location: ' . $paymentLink . $separator . 'ref=' . urlencode($checkout['reference']));
            exit;
        }

        $setupMessage = 'Payment link is not configured yet. The order was created as pending. Continue to thank-you for testing and Owner manual review.';
        header('Location: /thank-you?ref=' . urlencode($checkout['reference']) . '&setup=missing_payment_link');
        exit;
    }
}

ob_start();
?>
<section class="py-5">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-5">
        <div class="foundation-card h-100">
          <div class="badge text-bg-light border mb-3">Selected package</div>
          <h1 class="hero-title h2"><?= htmlspecialchars($plan['name_en'], ENT_QUOTES, 'UTF-8') ?></h1>
          <div class="display-5 fw-bold my-3">AED <?= htmlspecialchars((string) (int) $plan['price_amount'], ENT_QUOTES, 'UTF-8') ?></div>
          <p class="text-muted"><?= htmlspecialchars($plan['description_en'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
          <ul class="text-muted small">
            <li><?= (int) $plan['included_sessions'] ?> session(s)</li>
            <li><?= (int) $plan['session_minutes'] ?> minutes per session</li>
          </ul>
          <div class="alert alert-light border small mb-0">Payment status starts as pending. It is not marked paid until verified or manually approved by the Owner.</div>
        </div>
      </div>
      <div class="col-lg-7">
        <div class="foundation-card">
          <h2 class="h4 fw-bold mb-3">Checkout details</h2>
          <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
          <?php if ($setupMessage): ?><div class="alert alert-warning"><?= htmlspecialchars($setupMessage, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
          <form method="post" class="row g-3">
            <div class="col-md-6"><label class="form-label">Full name</label><input class="form-control" name="full_name" required></div>
            <div class="col-md-6"><label class="form-label">Email</label><input class="form-control" type="email" name="email" required></div>
            <div class="col-md-6"><label class="form-label">WhatsApp</label><input class="form-control" name="whatsapp" required></div>
            <div class="col-md-6"><label class="form-label">Student age</label><input class="form-control" type="number" min="1" max="100" name="student_age"></div>
            <div class="col-md-6"><label class="form-label">Learner type</label><select class="form-select" name="learner_type" required><option value="">Choose...</option><option value="adult">Adult</option><option value="child">Child</option><option value="someone_else">Someone else</option></select></div>
            <div class="col-md-6"><label class="form-label">Main goal</label><select class="form-select" name="main_goal" required><option value="">Choose...</option><option>Speaking</option><option>Reading & Writing</option><option>Work Arabic</option><option>Kids Arabic</option><option>Emirati Dialect</option><option>Not sure yet</option></select></div>
            <div class="col-md-12"><label class="form-label">Preferred contact method</label><select class="form-select" name="preferred_contact_method" required><option value="whatsapp">WhatsApp</option><option value="email">Email</option></select></div>
            <div class="col-12">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="policy_agreement" id="policyAgreement" required>
                <label class="form-check-label" for="policyAgreement">I agree to the Terms of Service, Refund Policy, Cancellation Policy, and Privacy Policy.</label>
              </div>
              <div class="small mt-2"><a href="/terms" target="_blank">Terms</a> · <a href="/refund" target="_blank">Refund Policy</a> · <a href="/privacy" target="_blank">Privacy Policy</a></div>
            </div>
            <div class="col-12"><button class="btn btn-brand btn-lg" type="submit">Continue to payment</button></div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>
<?php
render_public_layout('Checkout | Habiba Nabil Arabic Academy', 'Secure checkout preparation for your Arabic lesson package.', ob_get_clean(), true);
