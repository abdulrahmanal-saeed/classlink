<?php
/**
 * /pricing
 *
 * Public pricing page. Buttons route to checkout with a plan parameter, not
 * directly to a payment provider, so the platform can handle verification safely.
 */

require_once __DIR__ . '/../../../backend/php/shared/PublicContent.php';
require_once __DIR__ . '/../../../web/components/layout/public_layout.php';

$plans = public_plans();
$checkoutSlugs = ['Single Session' => 'single', 'Monthly Plan' => 'monthly', '30-Hour Bundle' => 'bundle'];
$regularPrices = ['Single Session' => 120, 'Monthly Plan' => 960, '30-Hour Bundle' => 2400];

ob_start();
?>
<section class="py-5">
  <div class="container">
    <div class="text-center mb-5">
      <h1 class="hero-title display-4">Choose your Arabic learning package</h1>
      <p class="hero-subtitle">Launch pricing for personalized 90-minute Arabic lessons.</p>
    </div>
    <div class="row g-4">
      <?php foreach ($plans as $plan): ?>
        <?php $slug = $checkoutSlugs[$plan['name_en']] ?? strtolower(str_replace(' ', '_', $plan['name_en'])); ?>
        <div class="col-md-4">
          <div class="foundation-card h-100 d-flex flex-column">
            <h2 class="h4 fw-bold"><?= htmlspecialchars($plan['name_en'], ENT_QUOTES, 'UTF-8') ?></h2>
            <div class="my-3">
              <?php if (isset($regularPrices[$plan['name_en']])): ?>
                <div class="text-muted text-decoration-line-through">AED <?= $regularPrices[$plan['name_en']] ?></div>
              <?php endif; ?>
              <div class="display-5 fw-bold">AED <?= htmlspecialchars((string) (int) $plan['price_amount'], ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <p class="text-muted flex-grow-1"><?= htmlspecialchars($plan['description_en'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
            <ul class="small text-muted">
              <li><?= (int) $plan['included_sessions'] ?> session(s)</li>
              <li><?= (int) $plan['session_minutes'] ?> minutes per session</li>
              <?php if ($plan['name_en'] === '30-Hour Bundle'): ?><li>Never expires</li><?php endif; ?>
            </ul>
            <a class="btn btn-brand w-100" href="/checkout?plan=<?= urlencode($slug) ?>">Start Now — Pay Securely</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="py-5 bg-white border-top border-bottom">
  <div class="container">
    <h2 class="h1 hero-title text-center mb-4">How it works</h2>
    <div class="row g-3">
      <?php foreach (['Choose your package', 'Pay securely', 'Complete your student form', 'Choose your lesson time', 'Start your personalized Arabic lesson'] as $index => $step): ?>
        <div class="col-md"><div class="status-box h-100"><strong><?= $index + 1 ?>.</strong> <?= htmlspecialchars($step, ENT_QUOTES, 'UTF-8') ?></div></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="py-5">
  <div class="container">
    <h2 class="h1 hero-title mb-4">FAQ</h2>
    <div class="accordion" id="pricingFaq">
      <?php foreach ([
        'Can I reschedule?' => 'Yes, rescheduling will follow the lesson cancellation policy set by the academy.',
        'Do I need to know Arabic before starting?' => 'No. You start from your real level after a level check.',
        'Is this suitable for children?' => 'Yes. Children can follow a child literacy path with parent access.',
        'Will I get homework?' => 'Yes. Homework and speaking scenarios are part of the learning experience.'
      ] as $question => $answer): $id = 'faq' . md5($question); ?>
        <div class="accordion-item">
          <h3 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $id ?>"><?= htmlspecialchars($question, ENT_QUOTES, 'UTF-8') ?></button></h3>
          <div id="<?= $id ?>" class="accordion-collapse collapse" data-bs-parent="#pricingFaq"><div class="accordion-body"><?= htmlspecialchars($answer, ENT_QUOTES, 'UTF-8') ?></div></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php
$content = ob_get_clean();
render_public_layout('Pricing | Habiba Nabil Arabic Academy', 'Choose a launch-price Arabic lesson package and start securely through checkout.', $content, true);
