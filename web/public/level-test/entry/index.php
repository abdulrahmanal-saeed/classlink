<?php
/**
 * /level-test/entry
 * Free public placement test entry page.
 */

require_once __DIR__ . '/../../../../backend/php/shared/FreeLevelTest.php';
require_once __DIR__ . '/../../../../web/components/layout/public_layout.php';

flt_seed_defaults();

ob_start();
?>
<section class="py-5">
  <div class="container">
    <div class="text-center mb-5">
      <h1 class="hero-title display-4">Free Arabic Level Test</h1>
      <p class="hero-subtitle">Choose a quick reading check or a full free placement test reviewed by the teacher.</p>
    </div>
    <div class="row g-4">
      <div class="col-lg-6">
        <div class="foundation-card h-100">
          <h2 class="h3 fw-bold">Existing Student</h2>
          <p class="text-muted">Enter your student code. If your profile is incomplete, complete it first before the test.</p>
          <form method="get" action="/level-test/register" class="row g-3">
            <input type="hidden" name="type" value="existing_student">
            <div class="col-12"><label class="form-label">Student code</label><input class="form-control" name="student_code" placeholder="MD-3718" required></div>
            <div class="col-12"><button class="btn btn-brand" type="submit">Continue as existing student</button></div>
          </form>
          <hr>
          <div class="small text-muted">Required profile fields: full name, email if available, WhatsApp, age, country.</div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="foundation-card h-100">
          <h2 class="h3 fw-bold">New Student / New Applicant</h2>
          <p class="text-muted">Start a free placement test. No payment. No paid account or package is created here.</p>
          <ul class="text-muted">
            <li>Around 30 minutes</li>
            <li>Listening → Reading → Writing → Speaking</li>
            <li>Prepare headphones</li>
            <li>Find a quiet place</li>
            <li>Writing and speaking are reviewed by the teacher</li>
          </ul>
          <a class="btn btn-brand btn-lg" href="/level-test/register">Start Free Placement Test</a>
        </div>
      </div>
    </div>
    <div class="text-center mt-4">
      <a class="btn btn-outline-brand" href="/level-test/quick">Try Quick Reading Check instead</a>
    </div>
  </div>
</section>
<?php
render_public_layout('Free Arabic Level Test | Habiba Nabil Arabic Academy', 'Start a free Arabic placement test or quick reading check.', ob_get_clean(), true);
