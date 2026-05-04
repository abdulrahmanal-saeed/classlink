<?php
/**
 * /parent/child/badges?id={childUserId}
 * Parent can see badges for linked child only.
 */

require_once __DIR__ . '/../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../backend/php/shared/LearningEngagement.php';
require_once __DIR__ . '/../../../../../web/components/layout/dashboard_shell.php';

$user = require_role('parent');
$childId = (int) ($_GET['id'] ?? 0);
$profile = parent_portal_child_profile((int) $user['id'], $childId);
engagement_award_badges($childId);
$badges = student_portal_badges($childId);
$definitions = engagement_badge_definitions();
$earnedIds = array_column($badges, 'id');

ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
  <div>
    <p class="text-muted mb-1">Badges for <?= htmlspecialchars($profile['display_name'] ?? 'Child learner', ENT_QUOTES, 'UTF-8') ?>.</p>
    <small class="text-muted">Only badges visible to students/parents are displayed.</small>
  </div>
  <a class="btn btn-outline-brand" href="/parent/child/progress?id=<?= (int) $childId ?>">Back to progress</a>
</div>

<div class="row g-3">
  <?php foreach ($definitions as $badge): ?>
    <?php if (!in_array($badge['visibility'], ['public','student_parent'], true)) continue; ?>
    <?php $earned = in_array($badge['id'], $earnedIds); ?>
    <div class="col-md-6 col-lg-4">
      <div class="status-box h-100 <?= $earned ? '' : 'opacity-75' ?>">
        <div class="display-6"><?= htmlspecialchars($badge['icon'] ?: '🏅', ENT_QUOTES, 'UTF-8') ?></div>
        <h2 class="h5 fw-bold"><?= htmlspecialchars($badge['name_en'], ENT_QUOTES, 'UTF-8') ?></h2>
        <p class="text-muted mb-2"><?= htmlspecialchars($badge['description_en'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
        <span class="badge <?= $earned ? 'text-bg-success' : 'text-bg-light border' ?>"><?= $earned ? 'Earned' : 'Locked' ?></span>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Child Badges', $content);
