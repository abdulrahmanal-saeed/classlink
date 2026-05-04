<?php
/**
 * /student/badges
 * Student badges page.
 */

require_once __DIR__ . '/../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../backend/php/shared/LearningEngagement.php';
require_once __DIR__ . '/../../../../web/components/layout/dashboard_shell.php';

$user = require_role('student');
engagement_award_badges((int) $user['id']);
$badges = student_portal_badges((int) $user['id']);
$definitions = engagement_badge_definitions();
$earnedIds = array_column($badges, 'id');

ob_start();
?>
<p class="text-muted">Badges you earned and active badges you can unlock.</p>
<div class="row g-3">
  <?php foreach ($definitions as $badge): ?>
    <?php if ($badge['visibility'] === 'owner_only') continue; ?>
    <?php $earned = in_array($badge['id'], $earnedIds); ?>
    <div class="col-md-6 col-lg-4">
      <div class="status-box h-100 <?= $earned ? '' : 'opacity-75' ?>">
        <div class="display-6"><?= htmlspecialchars($badge['icon'] ?: '🏅', ENT_QUOTES, 'UTF-8') ?></div>
        <h2 class="h5 fw-bold"><?= htmlspecialchars($badge['name_en'], ENT_QUOTES, 'UTF-8') ?></h2>
        <p class="text-muted mb-2"><?= htmlspecialchars($badge['description_en'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
        <span class="badge <?= $earned ? 'text-bg-success' : 'text-bg-light border' ?>"><?= $earned ? 'Earned' : 'Locked' ?></span>
        <div class="small text-muted mt-2">Required: <?= htmlspecialchars($badge['trigger_type'], ENT_QUOTES, 'UTF-8') ?> × <?= (int) $badge['required_value'] ?></div>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'My Badges', $content);
