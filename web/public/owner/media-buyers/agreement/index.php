<?php
require_once __DIR__ . '/../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../backend/php/shared/MediaBuyerAgreement.php';
require_once __DIR__ . '/../../../../../web/components/layout/dashboard_shell.php';

$user = require_role('owner_teacher');
$id = (int)($_GET['id'] ?? 0);
$acceptance = media_agreement_acceptance_find($id);
if (!$acceptance) {
    http_response_code(404);
    render_dashboard_shell($user, 'Agreement Not Found', '<div class="alert alert-danger">Agreement acceptance not found.</div>');
    exit;
}

ob_start();
?>
<div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-4">
  <p class="text-muted mb-0">Accepted agreement snapshot. This snapshot does not change if the template is edited later.</p>
  <button class="btn btn-outline-brand" onclick="window.print()">Print / Save PDF</button>
</div>
<?= media_agreement_render_html($acceptance) ?>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Accepted Agreement', $content);
