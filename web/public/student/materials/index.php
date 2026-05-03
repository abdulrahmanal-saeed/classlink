<?php
/**
 * /student/materials
 * Student course materials filtered by active/global/current level.
 */

require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../backend/php/shared/StudentPortal.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';

$user = require_role('student');
$materials = student_portal_materials((int) $user['id']);

ob_start();
?>
<p class="text-muted">Course materials selected for your level or available to all students.</p>
<?php if (!$materials): ?>
  <div class="alert alert-light border">No materials available yet.</div>
<?php else: ?>
  <div class="row g-3">
    <?php foreach ($materials as $material): ?>
      <div class="col-md-6">
        <div class="status-box h-100">
          <h2 class="h5 fw-bold"><?= htmlspecialchars($material['title'], ENT_QUOTES, 'UTF-8') ?></h2>
          <div class="small text-muted mb-2"><?= htmlspecialchars($material['material_type'], ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars($material['level'] ?? 'All levels', ENT_QUOTES, 'UTF-8') ?></div>
          <?php if (!empty($material['content'])): ?><p><?= nl2br(htmlspecialchars($material['content'], ENT_QUOTES, 'UTF-8')) ?></p><?php endif; ?>
          <?php if (!empty($material['file_path'])): ?><a class="btn btn-sm btn-outline-brand" href="<?= htmlspecialchars($material['file_path'], ENT_QUOTES, 'UTF-8') ?>" target="_blank">Open material</a><?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'My Materials', $content);
