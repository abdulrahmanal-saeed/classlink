<?php
/**
 * /owner/free-level-test/settings
 * Owner settings and audio availability dashboard for free public level tests.
 */

require_once __DIR__ . '/../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../backend/php/shared/FreeLevelTest.php';
require_once __DIR__ . '/../../../../../web/components/layout/dashboard_shell.php';

$user = require_role('owner_teacher');
flt_seed_defaults();
$message = null;

$definitions = flt_defaults();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($definitions as $key => [$default, $type]) {
        $value = $_POST[$key] ?? ($type === 'boolean' ? '0' : $default);
        if ($type === 'boolean') $value = isset($_POST[$key]) ? '1' : '0';
        flt_set_setting($key, (string) $value, $type, (int) $user['id']);
    }
    $message = 'Free level test settings saved.';
}

$availability = flt_audio_availability();

ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
  <div>
    <p class="text-muted mb-1">Control free public level tests, randomization, retakes, and follow-up copy.</p>
    <small class="text-muted">This is separate from paid onboarding level checks.</small>
  </div>
  <a class="btn btn-outline-brand" href="/owner/free-level-test/attempts">View free test attempts</a>
</div>
<?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

<div class="row g-4">
  <div class="col-lg-7">
    <div class="foundation-card">
      <h2 class="h5 fw-bold mb-3">Settings</h2>
      <form method="post" class="row g-3">
        <?php foreach ($definitions as $key => [$default, $type]): ?>
          <?php $current = flt_setting($key, $default); ?>
          <div class="col-md-6">
            <?php if ($type === 'boolean'): ?>
              <div class="form-check form-switch mt-4">
                <input class="form-check-input" type="checkbox" name="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>" id="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>" <?= $current ? 'checked' : '' ?>>
                <label class="form-check-label" for="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(str_replace('_', ' ', $key), ENT_QUOTES, 'UTF-8') ?></label>
              </div>
            <?php elseif ($type === 'text'): ?>
              <label class="form-label"><?= htmlspecialchars(str_replace('_', ' ', $key), ENT_QUOTES, 'UTF-8') ?></label>
              <textarea class="form-control" name="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>" rows="3"><?= htmlspecialchars((string) $current, ENT_QUOTES, 'UTF-8') ?></textarea>
            <?php else: ?>
              <label class="form-label"><?= htmlspecialchars(str_replace('_', ' ', $key), ENT_QUOTES, 'UTF-8') ?></label>
              <input class="form-control" type="number" name="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>" value="<?= htmlspecialchars((string) $current, ENT_QUOTES, 'UTF-8') ?>">
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
        <div class="col-12"><button class="btn btn-brand" type="submit">Save settings</button></div>
      </form>
    </div>
  </div>
  <div class="col-lg-5">
    <div class="status-box">
      <h2 class="h5 fw-bold mb-3">Audio availability</h2>
      <?php foreach ($availability as $level => $row): ?>
        <div class="d-flex justify-content-between border-bottom py-2">
          <div><strong><?= htmlspecialchars($level, ENT_QUOTES, 'UTF-8') ?></strong><br><small class="text-muted">Required per test: <?= (int) $row['required'] ?></small></div>
          <div class="text-end"><span class="badge text-bg-light border"><?= (int) $row['available'] ?> available</span><br><small class="<?= $row['status'] === 'OK' ? 'text-success' : 'text-danger' ?>"><?= htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8') ?></small></div>
        </div>
      <?php endforeach; ?>
      <div class="alert alert-light border mt-3 small">A2 may have 9 files only. This is OK because default selection needs 2.</div>
    </div>
  </div>
</div>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Free Level Test Settings', $content);
