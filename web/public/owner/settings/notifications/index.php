<?php
/** /owner/settings/notifications - push notification preferences. */
require_once __DIR__ . '/../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../backend/php/shared/PushNotifications.php';
require_once __DIR__ . '/../../../../../web/components/layout/dashboard_shell.php';

$user = require_role('owner_teacher');
$message = null;
$error = null;
$events = push_owner_event_keys();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        db()->prepare('UPDATE settings SET setting_value = :value, updated_by_user_id = :user WHERE setting_key = "push_enabled"')
            ->execute([':value' => isset($_POST['push_enabled']) ? '1' : '0', ':user' => (int) $user['id']]);
        db()->prepare('UPDATE settings SET setting_value = :value, updated_by_user_id = :user WHERE setting_key = "firebase_project_id"')
            ->execute([':value' => trim($_POST['firebase_project_id'] ?? ''), ':user' => (int) $user['id']]);
        foreach ($events as $key => $label) push_set_preference((int) $user['id'], $key, isset($_POST['event'][$key]));
        audit_log((int) $user['id'], 'push_notification_settings_updated', 'settings', 'push', []);
        $message = 'Notification settings updated.';
    } catch (Throwable $e) { $error = $e->getMessage(); }
}

$preferences = [];
foreach (push_get_preferences((int) $user['id']) as $pref) $preferences[$pref['event_key']] = (int) $pref['is_enabled'];
$pushEnabled = push_setting('push_enabled', '1') === '1';
$projectId = push_setting('firebase_project_id', '');
$jsonEnv = push_setting('firebase_service_account_json_env', 'FIREBASE_SERVICE_ACCOUNT_JSON');
$pathEnv = push_setting('firebase_service_account_path_env', 'GOOGLE_APPLICATION_CREDENTIALS');

ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
  <div><p class="text-muted mb-1">Manage Owner push notification preferences. Firebase secrets must stay in environment variables.</p></div>
  <a class="btn btn-outline-brand" href="/owner/notifications">Notification logs</a>
</div>
<?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
<form method="post" class="foundation-card">
  <div class="row g-3">
    <div class="col-md-4"><label class="form-label">Push enabled</label><div class="form-check mt-2"><input class="form-check-input" type="checkbox" name="push_enabled" <?= $pushEnabled ? 'checked' : '' ?>><label class="form-check-label">Enabled</label></div></div>
    <div class="col-md-8"><label class="form-label">Firebase Project ID</label><input class="form-control" name="firebase_project_id" value="<?= htmlspecialchars((string)$projectId, ENT_QUOTES, 'UTF-8') ?>"><small class="text-muted">Can also be read from service account JSON if blank.</small></div>
    <div class="col-12"><div class="alert alert-light border mb-0"><strong>Secret env names:</strong><br><code><?= htmlspecialchars((string)$jsonEnv, ENT_QUOTES, 'UTF-8') ?></code> or <code><?= htmlspecialchars((string)$pathEnv, ENT_QUOTES, 'UTF-8') ?></code><br>Do not put service account JSON in GitHub.</div></div>
    <div class="col-12"><h2 class="h5 fw-bold mt-2">Owner push events</h2></div>
    <?php foreach ($events as $key => $label): ?>
      <div class="col-md-6"><div class="form-check border rounded-4 p-3 ps-5 h-100"><input class="form-check-input" type="checkbox" name="event[<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>]" <?= ($preferences[$key] ?? 1) ? 'checked' : '' ?>><label class="form-check-label"><strong><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></strong><br><small class="text-muted"><code><?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?></code></small></label></div></div>
    <?php endforeach; ?>
    <div class="col-12"><button class="btn btn-brand" type="submit">Save notification settings</button></div>
  </div>
</form>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Notification Settings', $content);
