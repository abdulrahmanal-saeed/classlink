<?php
/**
 * /owner/settings
 *
 * Owner-only Settings Center foundation. This page is intentionally simple in
 * Phase 2: it proves settings can be edited, saved, and audited securely.
 */

require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../backend/php/shared/Settings.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';

$user = require_role('owner_teacher');
$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $group = $_POST['setting_group'] ?? 'general';
    $key = trim($_POST['setting_key'] ?? '');
    $value = trim($_POST['setting_value'] ?? '');

    if (!array_key_exists($group, settings_sections())) {
        $error = 'Invalid settings section.';
    } elseif ($key === '') {
        $error = 'Setting key is required.';
    } else {
        $safeKey = strtolower(preg_replace('/[^a-zA-Z0-9_\.\-]/', '_', $key));
        setting_set($safeKey, $value, $group, (int) $user['id']);
        $message = 'Setting saved successfully.';
    }
}

$sections = settings_sections();
$groupedSettings = settings_group_values();

ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
  <div>
    <p class="text-muted mb-1">Manage platform-level settings. More detailed forms will be added in later phases.</p>
    <small class="text-muted">Any setting update is recorded in the audit log.</small>
  </div>
  <a class="btn btn-outline-brand" href="/owner/audit-log">View audit log</a>
</div>

<?php if ($message): ?>
  <div class="alert alert-success"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<?php if ($error): ?>
  <div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div class="row g-4">
  <div class="col-lg-5">
    <div class="status-box">
      <h2 class="h5 fw-bold">Add or update setting</h2>
      <form method="post" class="mt-3">
        <div class="mb-3">
          <label class="form-label" for="setting_group">Section</label>
          <select class="form-select" id="setting_group" name="setting_group" required>
            <?php foreach ($sections as $sectionKey => $sectionLabel): ?>
              <option value="<?= htmlspecialchars($sectionKey, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($sectionLabel, ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label" for="setting_key">Setting key</label>
          <input class="form-control" id="setting_key" name="setting_key" placeholder="pricing.single_session_price" required>
        </div>
        <div class="mb-3">
          <label class="form-label" for="setting_value">Value</label>
          <textarea class="form-control" id="setting_value" name="setting_value" rows="4" placeholder="AED 80"></textarea>
        </div>
        <button class="btn btn-brand" type="submit">Save setting</button>
      </form>
    </div>
  </div>

  <div class="col-lg-7">
    <h2 class="h5 fw-bold mb-3">Settings sections foundation</h2>
    <div class="row g-2 mb-4">
      <?php foreach ($sections as $sectionKey => $sectionLabel): ?>
        <div class="col-md-6">
          <div class="border rounded-4 p-3 bg-white h-100">
            <div class="fw-bold"><?= htmlspecialchars($sectionLabel, ENT_QUOTES, 'UTF-8') ?></div>
            <small class="text-muted"><?= htmlspecialchars($sectionKey, ENT_QUOTES, 'UTF-8') ?></small>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <h2 class="h5 fw-bold mb-3">Saved settings</h2>
    <?php if (!$groupedSettings): ?>
      <div class="alert alert-light border">No settings saved yet.</div>
    <?php else: ?>
      <?php foreach ($groupedSettings as $group => $settings): ?>
        <div class="mb-3">
          <div class="fw-bold mb-2"><?= htmlspecialchars($group, ENT_QUOTES, 'UTF-8') ?></div>
          <div class="table-responsive">
            <table class="table table-sm align-middle">
              <thead><tr><th>Key</th><th>Value</th></tr></thead>
              <tbody>
                <?php foreach ($settings as $key => $value): ?>
                  <tr>
                    <td><code><?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?></code></td>
                    <td><?= nl2br(htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8')) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Owner Settings Center', $content);
