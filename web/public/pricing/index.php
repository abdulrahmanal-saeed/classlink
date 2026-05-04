<?php
/**
 * /pricing
 *
 * Phase 30 improves pricing copy so the page explains value, next steps,
 * objections, and CTA clarity before checkout.
 */

require_once __DIR__ . '/../../../backend/php/shared/PublicContent.php';
require_once __DIR__ . '/../../../backend/php/shared/SalesFunnelCopy.php';
require_once __DIR__ . '/../../../web/components/layout/public_layout.php';

$plans = public_plans();
$checkoutSlugs = ['Single Session' => 'single', 'Monthly Plan' => 'monthly', '30-Hour Bundle' => 'bundle'];
$regularPrices = ['Single Session' => 120, 'Monthly Plan' => 960, '30-Hour Bundle' => 2400];
$planValue = [
    'Single Session' => ['Best for trying the academy, getting a first diagnosis, or starting with one focused lesson.', 'Includes one 90-minute live 1-on-1 lesson', 'Good for adults or children', 'Personalized first-step recommendation'],
    'Monthly Plan' => ['Best for steady weekly progress with regular practice, homework, and feedback.', '8 live sessions / 12 learning hours', 'Consistent schedule and progress tracking', 'Homework and speaking practice between lessons'],
    '30-Hour Bundle' => ['Best value for learners who want a longer learning path without rushing.', '20 live sessions / 30 learning hours', 'Flexible long-term learning path', 'Never expires'],
];
$ctas = sales_funnel_ctas();
$faq = sales_funnel_faq();

ob_start();
?>
<section class="py-5">
  <div class="container">
    <div class="text-center mb-5">
      <div class="badge text-bg-light border mb-3">Limited-time launch prices</div>
      <h1 class="hero-title display-4">Choose the Arabic learning package that fits your goal</h1>
      <p class="hero-subtitle">Every package starts with a clear next step: payment, student form, level check if needed, lesson time, then a personalized 1-on-1 Arabic lesson.</p>
    </div>
    <div class="row g-4">
      <?php foreach ($plans as $plan): ?>
        <?php $slug = $checkoutSlugs[$plan['name_en']] ?? strtolower(str_replace(' ', '_', $plan['name_en'])); ?>
        <?php $value = $planValue[$plan['name_en']] ?? [$plan['description_en'] ?? 'Personalized Arabic learning package.']; ?>
        <div class="col-md-4">
          <div class="foundation-card h-100 d-flex flex-column">
            <?php if ($plan['name_en'] === 'Monthly Plan'): ?><div class="badge text-bg-light border mb-2 align-self-start">Most consistent</div><?php endif; ?>
            <?php if ($plan['name_en'] === '30-Hour Bundle'): ?><div class="badge text-bg-light border mb-2 align-self-start">Best value</div><?php endif; ?>
            <h2 class="h4 fw-bold"><?= htmlspecialchars($plan['name_en'], ENT_QUOTES, 'UTF-8') ?></h2>
            <p class="text-muted"><?= htmlspecialchars($value[0], ENT_QUOTES, 'UTF-8') ?></p>
            <div class="my-3">
              <?php if (isset($regularPrices[$plan['name_en']])): ?>
                <div class="text-muted text-decoration-line-through">AED <?= $regularPrices[$plan['name_en']] ?></div>
              <?php endif; ?>
              <div class="display-5 fw-bold">AED <?= htmlspecialchars((string) (int) $plan['price_amount'], ENT_QUOTES, 'UTF-8') ?></div>
              <div class="small text-muted">Launch price</div>
            </div>
            <ul class="small text-muted flex-grow-1">
              <?php foreach (array_slice($value, 1) as $point): ?><li><?= htmlspecialchars($point, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?>
              <li><?= (int) $plan['session_minutes'] ?> minutes per session</li>
            </ul>
            <a class="btn btn-brand w-100" href="/checkout?plan=<?= urlencode($slug) ?>"><?= htmlspecialchars($ctas['primary'], ENT_QUOTES, 'UTF-8') ?></a>
            <div class="small text-muted mt-2">You will not be marked as paid until payment is verified.</div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="py-5 bg-white border-top border-bottom">
  <div class="container">
    <div class="row g-4 align-items-center">
      <div class="col-lg-5">
        <h2 class="h1 hero-title">What happens after you choose a plan?</h2>
        <p class="text-muted">The checkout is only the start. The platform then collects the information needed to prepare the right lesson instead of giving everyone the same class.</p>
      </div>
      <div class="col-lg-7">
        <div class="row g-3">
          <?php foreach (['Pay securely', 'Complete the student form', 'Take a level check if needed', 'Choose or request lesson time', 'Teacher prepares your personalized first lesson', 'Start learning with homework and progress tracking'] as $index => $step): ?>
            <div class="col-md-6"><div class="status-box h-100"><strong><?= $index + 1 ?>.</strong> <?= htmlspecialchars($step, ENT_QUOTES, 'UTF-8') ?></div></div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="py-5">
  <div class="container">
    <h2 class="h1 hero-title text-center mb-4">Which package should I choose?</h2>
    <div class="row g-4">
      <div class="col-md-4"><div class="foundation-card h-100"><h3 class="h5 fw-bold">Choose Single Session if...</h3><p class="text-muted mb-0">You want to try the teacher, understand your level, or solve one focused speaking/reading problem first.</p></div></div>
      <div class="col-md-4"><div class="foundation-card h-100"><h3 class="h5 fw-bold">Choose Monthly Plan if...</h3><p class="text-muted mb-0">You want a consistent routine with lessons, homework, feedback, and visible progress over the month.</p></div></div>
      <div class="col-md-4"><div class="foundation-card h-100"><h3 class="h5 fw-bold">Choose 30-Hour Bundle if...</h3><p class="text-muted mb-0">You want a longer learning path for speaking confidence, work Arabic, child literacy, or steady development.</p></div></div>
    </div>
  </div>
</section>

<section class="py-5 bg-white border-top border-bottom">
  <div class="container">
    <h2 class="h1 hero-title mb-4">Common questions before payment</h2>
    <div class="accordion" id="pricingFaq">
      <?php foreach ($faq as $question => $answer): $id = 'faq' . md5($question); ?>
        <div class="accordion-item">
          <h3 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $id ?>"><?= htmlspecialchars($question, ENT_QUOTES, 'UTF-8') ?></button></h3>
          <div id="<?= $id ?>" class="accordion-collapse collapse" data-bs-parent="#pricingFaq"><div class="accordion-body"><?= htmlspecialchars($answer, ENT_QUOTES, 'UTF-8') ?></div></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="py-5">
  <div class="container text-center">
    <h2 class="h1 hero-title">Ready to start?</h2>
    <p class="hero-subtitle">Choose a plan now. The next steps will help us personalize your Arabic learning path.</p>
    <a class="btn btn-brand btn-lg" href="#top" onclick="window.scrollTo({top:0,behavior:'smooth'});return false;">Choose a package</a>
  </div>
</section>
<?php
$content = ob_get_clean();
render_public_layout('Arabic Lesson Pricing | Habiba Nabil Arabic Academy', 'Choose a personalized Arabic lesson package with launch pricing, clear next steps, level check, homework, and progress tracking.', $content, true);
