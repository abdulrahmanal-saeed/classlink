<?php
/**
 * /owner/dev/seed-data
 *
 * Development-only seed data control. This page is blocked outside local or
 * development environments so it cannot be used accidentally in production.
 */

require_once __DIR__ . '/../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../backend/php/config/db.php';
require_once __DIR__ . '/../../../../../backend/php/shared/AuditLogger.php';
require_once __DIR__ . '/../../../../../web/components/layout/dashboard_shell.php';

$user = require_role('owner_teacher');
$appEnv = getenv('APP_ENV') ?: 'local';
$message = null;
$error = null;

if (!in_array($appEnv, ['local', 'development'], true)) {
    http_response_code(403);
    $content = '<div class="alert alert-danger">Seed data controls are disabled outside local/development environments.</div>';
    render_dashboard_shell($user, 'Development Seed Data', $content);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        ob_start();
        require __DIR__ . '/../../../../../backend/php/database/seeds/seed_phase_2_demo_data.php';
        $output = trim(ob_get_clean());
        $message = $output ?: 'Seed data created successfully.';
    } catch (Throwable $exception) {
        if (ob_get_level() > 0) {
            ob_end_clean();
        }
        $error = $exception->getMessage();
        audit_log((int) $user['id'], 'phase_2_demo_seed_failed', 'system', null, ['error' => $error]);
    }
}

ob_start();
?>
<p class="text-muted">Create safe demo data for development testing. This page is available only when <code>APP_ENV</code> is <code>local</code> or <code>development</code>.</p>

<?php if ($message): ?>
  <div class="alert alert-success"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<?php if ($error): ?>
  <div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div class="status-box">
  <h2 class="h5 fw-bold">Seed demo data</h2>
  <p class="text-muted">This will create demo profiles, plans, templates, badge definitions, settings, and sample pending purchase records.</p>
  <form method="post">
    <button class="btn btn-brand" type="submit">Run Phase 2 seed data</button>
  </form>
</div>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Development Seed Data', $content);
