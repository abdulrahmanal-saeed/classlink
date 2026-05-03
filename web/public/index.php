<?php
/**
 * Public homepage.
 *
 * Phase 3 turns the placeholder into a real marketing homepage. Sections are
 * controlled by settings so the Owner can hide/show parts later without code edits.
 */

require_once __DIR__ . '/../../backend/php/shared/PublicContent.php';
require_once __DIR__ . '/../../web/components/layout/public_layout.php';

$plans = public_plans();
$articles = public_setting_enabled('homepage.show_articles') ? published_articles(3) : [];
$videos = public_setting_enabled('homepage.show_videos') ? published_videos(3) : [];
$testimonials = public_setting_enabled('homepage.show_testimonials') ? approved_testimonials(3) : [];

ob_start();
?>
<section class="py-5">
  <div class="container">
    <div class="row align-items-center g-4">
      <div class="col-lg-7">
        <div class="badge text-bg-light border mb-3">Learn Arabic the Smart Way</div>
        <h1 class="hero-title display-4 mb-3">Personalized Arabic lessons for real-life speaking confidence</h1>
        <p class="hero-subtitle mb-4">Start from your real level, not automatically from zero. Learn practical Arabic with clear homework, speaking scenarios, and progress tracking.</p>
        <div class="d-flex flex-wrap gap-2">
          <a class="btn btn-brand btn-lg" href="/pricing">Start Now — Pay Securely</a>
          <a class="btn btn-outline-brand btn-lg" href="/articles">Read articles</a>
        </div>
      </div>
      <div class="col-lg-5">
        <div class="foundation-card">
          <div class="brand-mark mb-3">ض</div>
          <h2 class="h4 fw-bold">Arabic learning built around you</h2>
          <p class="text-muted mb-0">Speaking-first lessons, practical vocabulary, light grammar, and personalized feedback for adults, children, and academy partners.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="py-5 bg-white border-top border-bottom">
  <div class="container">
    <h2 class="h1 hero-title text-center mb-4">How it works</h2>
    <div class="row g-3">
      <?php foreach (['Choose your package', 'Pay securely', 'Complete your student form', 'Choose your lesson time', 'Start your personalized Arabic lesson'] as $index => $step): ?>
        <div class="col-md">
          <div class="status-box h-100">
            <div class="fw-bold mb-2">Step <?= $index + 1 ?></div>
            <div><?= htmlspecialchars($step, ENT_QUOTES, 'UTF-8') ?></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="py-5">
  <div class="container">
    <div class="d-flex justify-content-between align-items-end gap-3 flex-wrap mb-4">
      <div>
        <h2 class="h1 hero-title mb-1">Launch pricing</h2>
        <p class="text-muted mb-0">Limited-time prices for personalized 1-on-1 Arabic lessons.</p>
      </div>
      <a class="btn btn-outline-brand" href="/pricing">View all plans</a>
    </div>
    <div class="row g-4">
      <?php foreach (array_slice($plans, 0, 3) as $plan): ?>
        <div class="col-md-4">
          <div class="foundation-card h-100">
            <h3 class="h4 fw-bold"><?= htmlspecialchars($plan['name_en'], ENT_QUOTES, 'UTF-8') ?></h3>
            <div class="display-6 fw-bold my-3">AED <?= htmlspecialchars((string) (int) $plan['price_amount'], ENT_QUOTES, 'UTF-8') ?></div>
            <p class="text-muted"><?= htmlspecialchars($plan['description_en'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
            <a class="btn btn-brand w-100" href="/checkout?plan=<?= urlencode(strtolower(str_replace(' ', '_', $plan['name_en']))) ?>">Start Now — Pay Securely</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php if ($testimonials): ?>
<section class="py-5 bg-white border-top border-bottom">
  <div class="container">
    <h2 class="h1 hero-title mb-4">What learners say</h2>
    <div class="row g-3">
      <?php foreach ($testimonials as $testimonial): ?>
        <div class="col-md-4"><div class="status-box h-100"><p>“<?= htmlspecialchars($testimonial['body'], ENT_QUOTES, 'UTF-8') ?>”</p><strong><?= htmlspecialchars($testimonial['name'], ENT_QUOTES, 'UTF-8') ?></strong></div></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="py-5">
  <div class="container">
    <h2 class="h1 hero-title mb-4">FAQ</h2>
    <div class="row g-3">
      <?php foreach ([
        'Can I reschedule?' => 'Yes, cancellation and rescheduling rules will follow the lesson cancellation policy set by the academy.',
        'Do I need to know Arabic before starting?' => 'No. Every learner starts from their real level after a level check.',
        'Is this suitable for children?' => 'Yes. Child learners can follow a literacy-focused path with parent access.',
        'Will I get homework?' => 'Yes. Homework and speaking scenarios are part of the learning system.'
      ] as $question => $answer): ?>
        <div class="col-md-6"><div class="foundation-card h-100"><h3 class="h5 fw-bold"><?= htmlspecialchars($question, ENT_QUOTES, 'UTF-8') ?></h3><p class="text-muted mb-0"><?= htmlspecialchars($answer, ENT_QUOTES, 'UTF-8') ?></p></div></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php
$content = ob_get_clean();
render_public_layout('Habiba Nabil Arabic Academy | Learn Arabic the Smart Way', 'Personalized Arabic lessons for non-native speakers with speaking-first learning, homework, scenarios, and progress tracking.', $content, true);
