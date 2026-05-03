<?php
/**
 * /owner/settings/public-website
 *
 * Owner-only public website settings. Phase 3 focuses on homepage section
 * toggles so marketing sections can be shown/hidden without code changes.
 */

require_once __DIR__ . '/../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../backend/php/shared/Settings.php';
require_once __DIR__ . '/../../../../../web/components/layout/dashboard_shell.php';

$user = require_role('owner_teacher');
$message = null;

$toggles = [
    'homepage.show_articles' => 'Show articles on homepage',
    'homepage.show_videos' => 'Show videos on homepage',
    'homepage.show_testimonials' => 'Show testimonials on homepage',
    'homepage.show_pricing' => 'Show pricing preview on homepage',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($toggles as $key => $label) {
        setting_set($key, isset($_POST[$key]) ? '1' : '0', 'public_website', (int) $user['id'], 'boolean');
    }

    $message = 'Public website settings saved.';
}

ob_start();
?>
<p class="text-muted">Control public homepage sections. Each update is saved in the audit log.</p>

<?php if ($message): ?>
  <div class="alert alert-success"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div class="status-box">
  <form method="post">
    <?php foreach ($toggles as $key => $label): ?>
      <?php $checked = setting_get($key, '1') === '1'; ?>
      <div class="form-check form-switch mb-3">
        <input class="form-check-input" type="checkbox" role="switch" id="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>" name="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>" <?= $checked ? 'checked' : '' ?>>
        <label class="form-check-label" for="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>">
          <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
        </label>
      </div>
    <?php endforeach; ?>
    <button class="btn btn-brand" type="submit">Save public website settings</button>
  </form>
</div>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Public Website Settings', $content);
