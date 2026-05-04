<?php
/**
 * /owner/materials
 * Central owner course materials overview.
 */

require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../backend/php/shared/OwnerDashboard.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';

$user = require_role('owner_teacher');
$rows = owner_all_materials();

ob_start();
?>
<p class="text-muted">Course materials visible to students by level/global availability.</p>
<?php if (!$rows): ?>
  <div class="alert alert-light border">No course materials yet.</div>
<?php else: ?>
  <div class="row g-3">
    <?php foreach ($rows as $row): ?>
      <div class="col-md-6">
        <div class="status-box h-100">
          <h2 class="h5 fw-bold"><?= htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8') ?></h2>
          <div class="small text-muted mb-2"><?= htmlspecialchars($row['material_type'], ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars($row['level'] ?: 'All levels', ENT_QUOTES, 'UTF-8') ?></div>
          <span class="badge text-bg-light border"><?= ((int) $row['is_active']) === 1 ? 'active' : 'inactive' ?></span>
          <?php if (!empty($row['file_path'])): ?><div class="mt-2"><a href="<?= htmlspecialchars($row['file_path'], ENT_QUOTES, 'UTF-8') ?>" target="_blank">Open file/link</a></div><?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Owner Materials', $content);
