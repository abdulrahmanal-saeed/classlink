<?php
require_once __DIR__ . '/../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../backend/php/shared/MediaBuyerAgreement.php';
require_once __DIR__ . '/../../../../../web/components/layout/dashboard_shell.php';

$user = require_role('owner_teacher');
$template = media_agreement_active_template();
$acceptances = media_agreement_acceptances();
$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        media_agreement_save_template($_POST, $user);
        $template = media_agreement_active_template();
        $message = 'Agreement template saved.';
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

ob_start();
?>
<div class="alert alert-warning">This is not legal advice. Ask a qualified lawyer to review the agreement before real use.</div>
<?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
<form method="post" class="foundation-card mb-4">
  <h2 class="h5 fw-bold">Active Agreement Template</h2>
  <div class="row g-3">
    <div class="col-md-8"><label class="form-label">Title</label><input class="form-control" name="title" value="<?= htmlspecialchars($template['title'] ?? 'Media Buyer / Marketing Partner Agreement', ENT_QUOTES, 'UTF-8') ?>" required></div>
    <div class="col-md-4"><label class="form-label">Version</label><input class="form-control" name="version" value="<?= htmlspecialchars($template['version'] ?? '1.0', ENT_QUOTES, 'UTF-8') ?>" required></div>
    <div class="col-12"><label class="form-label">Agreement content</label><textarea class="form-control" name="content" rows="18" required><?= htmlspecialchars($template['content'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea></div>
    <div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" name="requires_reacceptance" value="1"><label class="form-check-label">Require all media buyers to re-accept this new version</label></div></div>
    <div class="col-12"><button class="btn btn-brand">Save New Active Template</button></div>
  </div>
</form>
<div class="foundation-card">
  <h2 class="h5 fw-bold">Agreement Acceptances</h2>
  <?php if(!$acceptances): ?><div class="alert alert-light border">No agreement acceptances yet.</div><?php else: ?>
    <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Partner</th><th>Version</th><th>Typed Name</th><th>Accepted At</th><th>Action</th></tr></thead><tbody>
    <?php foreach($acceptances as $a): ?><tr><td><?= htmlspecialchars($a['display_name'], ENT_QUOTES, 'UTF-8') ?><br><small class="ltr-safe"><?= htmlspecialchars($a['partner_code'], ENT_QUOTES, 'UTF-8') ?></small></td><td><?= htmlspecialchars($a['template_version'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($a['typed_name'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($a['accepted_at'], ENT_QUOTES, 'UTF-8') ?></td><td><a class="btn btn-sm btn-outline-brand" href="/owner/media-buyers/agreement?id=<?= (int)$a['id'] ?>">View</a></td></tr><?php endforeach; ?>
    </tbody></table></div>
  <?php endif; ?>
</div>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Media Buyer Agreements', $content);
