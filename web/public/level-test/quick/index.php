<?php
/**
 * /level-test/quick
 * Public quick reading-only Arabic level check. No payment. No account.
 */

require_once __DIR__ . '/../../../../backend/php/shared/FreeLevelTest.php';
require_once __DIR__ . '/../../../../web/components/layout/public_layout.php';

if (!flt_setting('enable_quick_check', true)) {
    http_response_code(403);
    render_public_layout('Quick Check Disabled | Habiba Nabil Arabic Academy', 'Quick Arabic level check is disabled.', '<section class="py-5"><div class="container"><div class="foundation-card"><h1 class="hero-title">Quick Check is currently unavailable</h1></div></div></section>', false);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['attempt_token'])) {
    $snapshot = flt_generate_quick_snapshot();
    $attempt = flt_create_attempt('quick', null, null, $snapshot, 'quick');
    $token = $attempt['token'];
} else {
    $token = preg_replace('/[^a-f0-9]/', '', $_POST['attempt_token']);
    $attempt = flt_attempt_by_token($token);
    if (!$attempt) {
        http_response_code(404);
        render_public_layout('Attempt not found', 'Quick check attempt not found.', '<section class="py-5"><div class="container"><div class="foundation-card"><h1>Attempt not found</h1></div></div></section>', false);
        exit;
    }
    $snapshot = json_decode($attempt['snapshot_json'], true);
    $result = flt_grade_reading_answers($snapshot, $_POST, (int) $attempt['id'], 'quick');
    $level = flt_level_percent_to_cefr((float) $result['percent']);
    db()->prepare('UPDATE free_level_test_attempts SET status = "submitted", current_step = "submitted", reading_score = :score, preliminary_level = :level, submitted_at = NOW() WHERE id = :id')
        ->execute([':score' => $result['percent'], ':level' => $level, ':id' => (int) $attempt['id']]);
    audit_log(null, 'quick_free_level_check_submitted', 'free_level_test_attempt', (string) $attempt['id'], ['level' => $level, 'score' => $result['percent']]);
    header('Location: /level-test/quick-result?token=' . urlencode($token));
    exit;
}

ob_start();
?>
<section class="py-5">
  <div class="container">
    <div class="foundation-card">
      <h1 class="hero-title mb-3">Quick Arabic Level Check</h1>
      <p class="hero-subtitle">Reading-only preliminary check. Around 12 minutes. No registration required.</p>
      <div class="alert alert-light border">This is a preliminary estimate based on reading only.</div>
      <?php if (!empty($snapshot['warnings'])): ?><div class="alert alert-warning"><?= htmlspecialchars(implode(' ', $snapshot['warnings']), ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
      <form method="post" class="row g-4">
        <input type="hidden" name="attempt_token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
        <?php foreach ($snapshot['reading_texts'] as $blockIndex => $block): ?>
          <div class="col-12">
            <div class="status-box">
              <h2 class="h4 fw-bold"><?= htmlspecialchars($block['text']['title'], ENT_QUOTES, 'UTF-8') ?></h2>
              <p dir="rtl" class="mb-0"><?= nl2br(htmlspecialchars($block['text']['passage_text'], ENT_QUOTES, 'UTF-8')) ?></p>
            </div>
          </div>
          <?php foreach ($block['questions'] as $q): ?>
            <div class="col-md-6">
              <div class="border rounded-4 p-3 h-100">
                <label class="form-label fw-bold"><?= htmlspecialchars($q['question_text'], ENT_QUOTES, 'UTF-8') ?></label>
                <?php foreach (['A','B','C','D'] as $opt): $field = 'option_' . strtolower($opt); ?>
                  <div class="form-check"><input class="form-check-input" type="radio" name="q_<?= (int) $q['id'] ?>" value="<?= $opt ?>" required><label class="form-check-label"><?= $opt ?>. <?= htmlspecialchars($q[$field], ENT_QUOTES, 'UTF-8') ?></label></div>
                <?php endforeach; ?>
                <div class="form-check"><input class="form-check-input" type="radio" name="q_<?= (int) $q['id'] ?>" value="X" required><label class="form-check-label">X. I don’t know</label></div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endforeach; ?>
        <div class="col-12"><button class="btn btn-brand btn-lg" type="submit">See preliminary result</button></div>
      </form>
    </div>
  </div>
</section>
<?php
render_public_layout('Quick Arabic Level Check | Habiba Nabil Arabic Academy', 'Free quick Arabic reading-only level estimate.', ob_get_clean(), true);
