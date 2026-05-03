<?php
/**
 * /level-test/quick-result?token=...
 */

require_once __DIR__ . '/../../../../backend/php/shared/FreeLevelTest.php';
require_once __DIR__ . '/../../../../web/components/layout/public_layout.php';

$token = preg_replace('/[^a-f0-9]/', '', $_GET['token'] ?? '');
$attempt = $token ? flt_attempt_by_token($token) : null;

if (!$attempt || $attempt['test_type'] !== 'quick') {
    http_response_code(404);
    render_public_layout('Quick Result Not Found', 'Quick check result not found.', '<section class="py-5"><div class="container"><div class="foundation-card"><h1 class="hero-title">Result not found</h1></div></div></section>', false);
    exit;
}

$level = $attempt['preliminary_level'] ?: 'A1';
$score = (float) ($attempt['reading_score'] ?? 0);

$descriptions = [
    'A1' => ['You may understand a few simple words and phrases.', 'قد تفهم بعض الكلمات والجمل البسيطة.'],
    'A2' => ['You can understand simple everyday Arabic texts.', 'يمكنك فهم نصوص عربية يومية بسيطة.'],
    'B1' => ['You can understand familiar topics with some detail.', 'يمكنك فهم موضوعات مألوفة ببعض التفاصيل.'],
    'B2' => ['You can understand longer texts and opinions.', 'يمكنك فهم نصوص أطول وآراء أوضح.'],
    'C1' => ['You can understand complex texts well.', 'يمكنك فهم النصوص المعقدة بدرجة جيدة.'],
    'C2' => ['You show very strong reading comprehension.', 'لديك فهم قوي جدًا في القراءة.'],
];

ob_start();
?>
<section class="py-5">
  <div class="container">
    <div class="foundation-card" style="max-width: 860px; margin:auto;">
      <h1 class="hero-title mb-3">Preliminary Result</h1>
      <div class="display-4 fw-bold mb-3"><?= htmlspecialchars($level, ENT_QUOTES, 'UTF-8') ?></div>
      <p class="hero-subtitle">Score: <?= htmlspecialchars((string) $score, ENT_QUOTES, 'UTF-8') ?>%</p>
      <div class="row g-3 my-4">
        <div class="col-md-6"><div class="status-box h-100"><strong>English</strong><br><?= htmlspecialchars($descriptions[$level][0] ?? $descriptions['A1'][0], ENT_QUOTES, 'UTF-8') ?></div></div>
        <div class="col-md-6"><div class="status-box h-100" dir="rtl"><strong>العربية</strong><br><?= htmlspecialchars($descriptions[$level][1] ?? $descriptions['A1'][1], ENT_QUOTES, 'UTF-8') ?></div></div>
      </div>
      <div class="alert alert-warning">This is a preliminary estimate based on reading only. For your official CEFR placement, take the full assessment — it includes listening, writing, and speaking reviewed by Habiba.</div>
      <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-brand" href="/level-test/entry">Start full assessment</a>
        <a class="btn btn-outline-brand" href="/pricing">View pricing</a>
        <a class="btn btn-outline-brand" href="https://wa.me/" target="_blank">WhatsApp contact</a>
      </div>
    </div>
  </div>
</section>
<?php
render_public_layout('Quick Arabic Level Result | Habiba Nabil Arabic Academy', 'Your preliminary Arabic reading level result.', ob_get_clean(), true);
