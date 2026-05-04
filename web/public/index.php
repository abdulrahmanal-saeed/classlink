<?php
/**
 * Public homepage.
 *
 * Phase 30 upgrades the homepage from an information page into a clearer sales
 * funnel: promise, problem, personalized solution, audience, outcomes, pricing,
 * trust, FAQ, and CTA.
 */

require_once __DIR__ . '/../../backend/php/shared/PublicContent.php';
require_once __DIR__ . '/../../backend/php/shared/SalesFunnelCopy.php';
require_once __DIR__ . '/../../web/components/layout/public_layout.php';

$plans = public_plans();
$articles = public_setting_enabled('homepage.show_articles') ? published_articles(3) : [];
$videos = public_setting_enabled('homepage.show_videos') ? published_videos(3) : [];
$testimonials = public_setting_enabled('homepage.show_testimonials') ? approved_testimonials(3) : [];
$ctas = sales_funnel_ctas();
$faq = sales_funnel_faq();

ob_start();
?>
<section class="py-5">
  <div class="container">
    <div class="row align-items-center g-4">
      <div class="col-lg-7">
        <div class="badge text-bg-light border mb-3">Personalized 1-on-1 Arabic lessons online</div>
        <h1 class="hero-title display-4 mb-3">Speak Arabic with more confidence — from your real level</h1>
        <p class="hero-subtitle mb-4">Practical Arabic lessons for adults and children who want real progress: speaking, reading, writing, work Arabic, daily life Arabic, and child literacy support.</p>
        <div class="d-flex flex-wrap gap-2 mb-3">
          <a class="btn btn-brand btn-lg" href="/pricing"><?= htmlspecialchars($ctas['primary'], ENT_QUOTES, 'UTF-8') ?></a>
          <a class="btn btn-outline-brand btn-lg" href="/level-test/entry">Start with a Level Check</a>
        </div>
        <p class="small text-muted mb-0">Limited-time launch pricing. No unrealistic fluency promises — just a clear, personalized path and consistent practice.</p>
      </div>
      <div class="col-lg-5">
        <div class="foundation-card">
          <div class="brand-mark mb-3">ض</div>
          <h2 class="h4 fw-bold">Not a generic Arabic course</h2>
          <p class="text-muted">You start with your real level, your goal, and your situation. Lessons are built around what you actually need to say, read, or write.</p>
          <ul class="mb-0 text-muted">
            <li>Speaking-first practical lessons</li>
            <li>Adult and child learning paths</li>
            <li>Homework, materials, and progress tracking</li>
            <li>Online live 90-minute sessions</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="py-5 bg-white border-top border-bottom">
  <div class="container">
    <div class="text-center mb-4">
      <h2 class="h1 hero-title">Does this sound familiar?</h2>
      <p class="text-muted">These are the exact problems the learning path is designed to solve.</p>
    </div>
    <div class="row g-3">
      <?php foreach ([
        'I understand Arabic, but I cannot speak confidently.',
        'I need Arabic for work, customers, or daily life in the UAE/Gulf.',
        'My child speaks Arabic, but cannot read or write properly.',
        'I do not know if I should learn MSA, Emirati, Egyptian, or a mix.',
        'I am afraid of making mistakes when I speak.',
        'I tried apps or videos, but I need a teacher and a plan.'
      ] as $problem): ?>
        <div class="col-md-4"><div class="status-box h-100">✓ <?= htmlspecialchars($problem, ENT_QUOTES, 'UTF-8') ?></div></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="py-5">
  <div class="container">
    <div class="row g-4 align-items-center">
      <div class="col-lg-6">
        <h2 class="h1 hero-title">A personalized Arabic path, not random lessons</h2>
        <p class="text-muted">Before your first lesson, we collect the right information about your level, goal, age, learning reason, preferred Arabic type, and schedule. Then the teacher prepares a lesson that fits you.</p>
        <a class="btn btn-brand" href="/pricing"><?= htmlspecialchars($ctas['book_lesson'], ENT_QUOTES, 'UTF-8') ?></a>
      </div>
      <div class="col-lg-6">
        <div class="row g-3">
          <?php foreach ([
            'Level check before placement',
            'Clear first lesson recommendation',
            'Speaking scenarios for real life',
            'Homework and teacher feedback',
            'Materials and practice words',
            'Progress and package balance tracking'
          ] as $item): ?>
            <div class="col-sm-6"><div class="foundation-card h-100"><?= htmlspecialchars($item, ENT_QUOTES, 'UTF-8') ?></div></div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="py-5 bg-white border-top border-bottom">
  <div class="container">
    <h2 class="h1 hero-title text-center mb-4">Who this is for</h2>
    <div class="row g-4">
      <?php foreach ([
        ['Adults who understand Arabic but cannot speak', 'Build sentence confidence, daily conversation, and useful speaking habits.'],
        ['Adults who need Arabic for work or life in the Gulf', 'Practice workplace, customer, and daily-life Arabic with the right mix of MSA and dialect.'],
        ['Parents of children who need reading and writing', 'Support Arabic literacy, letters, reading, writing, and confidence with simple texts.'],
        ['New non-native learners', 'Start from zero or near-zero with a safe, structured path.'],
        ['Academies and partners', 'Refer learners and track briefs through a clear partner flow.']
      ] as $audience): ?>
        <div class="col-md-4"><div class="foundation-card h-100"><h3 class="h5 fw-bold"><?= htmlspecialchars($audience[0], ENT_QUOTES, 'UTF-8') ?></h3><p class="text-muted mb-0"><?= htmlspecialchars($audience[1], ENT_QUOTES, 'UTF-8') ?></p></div></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="py-5">
  <div class="container">
    <h2 class="h1 hero-title text-center mb-4">How it works</h2>
    <div class="row g-3">
      <?php foreach (['Choose your package', 'Pay securely', 'Complete your student form', 'Complete level check if needed', 'Choose your lesson time', 'Start your personalized Arabic lesson'] as $index => $step): ?>
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

<section class="py-5 bg-white border-top border-bottom">
  <div class="container">
    <div class="d-flex justify-content-between align-items-end gap-3 flex-wrap mb-4">
      <div>
        <h2 class="h1 hero-title mb-1">Launch pricing</h2>
        <p class="text-muted mb-0">Limited-time prices for personalized 1-on-1 Arabic lessons.</p>
      </div>
      <a class="btn btn-outline-brand" href="/pricing">Compare plans</a>
    </div>
    <div class="row g-4">
      <?php foreach (array_slice($plans, 0, 3) as $plan): ?>
        <div class="col-md-4">
          <div class="foundation-card h-100 d-flex flex-column">
            <h3 class="h4 fw-bold"><?= htmlspecialchars($plan['name_en'], ENT_QUOTES, 'UTF-8') ?></h3>
            <div class="display-6 fw-bold my-3">AED <?= htmlspecialchars((string) (int) $plan['price_amount'], ENT_QUOTES, 'UTF-8') ?></div>
            <p class="text-muted flex-grow-1"><?= htmlspecialchars($plan['description_en'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
            <a class="btn btn-brand w-100" href="/checkout?plan=<?= urlencode(strtolower(str_replace(' ', '_', $plan['name_en']))) ?>"><?= htmlspecialchars($ctas['primary'], ENT_QUOTES, 'UTF-8') ?></a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="py-5">
  <div class="container">
    <h2 class="h1 hero-title text-center mb-4">What you can expect</h2>
    <div class="row g-3">
      <?php foreach ([
        'A teacher who starts from your real level',
        'Practical sentences you can use outside the lesson',
        'Gentle correction when you make mistakes',
        'Homework and feedback so progress does not stop after class',
        'A clear difference between speaking, reading, writing, and dialect goals',
        'Parent visibility for child learners'
      ] as $outcome): ?>
        <div class="col-md-4"><div class="status-box h-100"><?= htmlspecialchars($outcome, ENT_QUOTES, 'UTF-8') ?></div></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php if ($testimonials): ?>
<section class="py-5 bg-white border-top border-bottom">
  <div class="container">
    <h2 class="h1 hero-title mb-4">What learners and parents say</h2>
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
    <h2 class="h1 hero-title mb-4">Questions before you start?</h2>
    <div class="row g-3">
      <?php foreach (array_slice($faq, 0, 8) as $question => $answer): ?>
        <div class="col-md-6"><div class="foundation-card h-100"><h3 class="h5 fw-bold"><?= htmlspecialchars($question, ENT_QUOTES, 'UTF-8') ?></h3><p class="text-muted mb-0"><?= htmlspecialchars($answer, ENT_QUOTES, 'UTF-8') ?></p></div></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="py-5 bg-white border-top">
  <div class="container text-center">
    <h2 class="h1 hero-title">Ready to start your Arabic learning path?</h2>
    <p class="hero-subtitle">Choose a package, complete the form, and let the teacher prepare your first personalized lesson.</p>
    <div class="d-flex justify-content-center gap-2 flex-wrap">
      <a class="btn btn-brand btn-lg" href="/pricing"><?= htmlspecialchars($ctas['primary'], ENT_QUOTES, 'UTF-8') ?></a>
      <a class="btn btn-outline-brand btn-lg" href="/help/student">How does it work?</a>
    </div>
  </div>
</section>
<?php
$content = ob_get_clean();
render_public_layout('Personalized Arabic Lessons Online | Habiba Nabil Arabic Academy', 'Personalized 1-on-1 Arabic lessons for adults, children, work Arabic, daily life Arabic, speaking confidence, and child literacy.', $content, true);
