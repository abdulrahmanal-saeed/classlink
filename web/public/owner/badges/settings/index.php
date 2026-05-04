<?php
/**
 * /owner/badges/settings
 * Owner manages badge definitions and trigger settings.
 */

require_once __DIR__ . '/../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../backend/php/shared/LearningEngagement.php';
require_once __DIR__ . '/../../../../../web/components/layout/dashboard_shell.php';

$user = require_role('owner_teacher');
$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        engagement_update_badge((int) $user['id'], (int) $_POST['badge_id'], $_POST);
        $message = 'Badge setting updated.';
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
    }
}

$badges = engagement_badge_definitions();
$triggerTypes = ['manual','activity_count','streak_days','sessions_completed','practice_words_mastered','scenarios_submitted','homework_submitted','level_check_completed'];
$visibility = ['public','student_parent','owner_only'];

ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
  <div>
    <p class="text-muted mb-1">Manage badge names, triggers, required values, visibility, display order, and active status.</p>
    <small class="text-muted">Disabled badges will not be awarded by the automatic badge engine.</small>
  </div>
</div>
<?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

<?php if (!$badges): ?>
  <div class="alert alert-light border">No badge definitions found. Run migration 015.</div>
<?php else: ?>
  <?php foreach ($badges as $badge): ?>
    <form method="post" class="foundation-card mb-3">
      <input type="hidden" name="badge_id" value="<?= (int) $badge['id'] ?>">
      <div class="row g-3 align-items-end">
        <div class="col-md-1"><label class="form-label">Icon</label><input class="form-control" name="icon" value="<?= htmlspecialchars($badge['icon'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></div>
        <div class="col-md-3"><label class="form-label">Name EN</label><input class="form-control" name="name_en" value="<?= htmlspecialchars($badge['name_en'], ENT_QUOTES, 'UTF-8') ?>"></div>
        <div class="col-md-3"><label class="form-label">Name AR</label><input class="form-control" name="name_ar" value="<?= htmlspecialchars($badge['name_ar'], ENT_QUOTES, 'UTF-8') ?>"></div>
        <div class="col-md-2"><label class="form-label">Trigger</label><select class="form-select" name="trigger_type"><?php foreach ($triggerTypes as $type): ?><option value="<?= $type ?>" <?= $badge['trigger_type'] === $type ? 'selected' : '' ?>><?= $type ?></option><?php endforeach; ?></select></div>
        <div class="col-md-1"><label class="form-label">Value</label><input class="form-control" type="number" name="required_value" value="<?= (int) $badge['required_value'] ?>"></div>
        <div class="col-md-1"><label class="form-label">Order</label><input class="form-control" type="number" name="display_order" value="<?= (int) $badge['display_order'] ?>"></div>
        <div class="col-md-1"><label class="form-label">Active</label><div class="form-check"><input class="form-check-input" type="checkbox" name="is_active" <?= ((int) $badge['is_active']) === 1 ? 'checked' : '' ?>></div></div>
        <div class="col-md-4"><label class="form-label">Description EN</label><textarea class="form-control" name="description_en" rows="2"><?= htmlspecialchars($badge['description_en'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea></div>
        <div class="col-md-4"><label class="form-label">Description AR</label><textarea class="form-control" name="description_ar" rows="2"><?= htmlspecialchars($badge['description_ar'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea></div>
        <div class="col-md-2"><label class="form-label">Visibility</label><select class="form-select" name="visibility"><?php foreach ($visibility as $v): ?><option value="<?= $v ?>" <?= $badge['visibility'] === $v ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?></select></div>
        <div class="col-md-1"><label class="form-label">Style</label><input class="form-control" name="color_style" value="<?= htmlspecialchars($badge['color_style'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></div>
        <div class="col-md-1"><button class="btn btn-brand w-100" type="submit">Save</button></div>
      </div>
    </form>
  <?php endforeach; ?>
<?php endif; ?>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Badge Settings', $content);
