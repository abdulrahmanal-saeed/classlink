<?php
/**
 * /owner/cms/testimonials
 *
 * Owner-only testimonial moderation. Public submissions stay pending until
 * approval, which protects the public website from unreviewed content.
 */

require_once __DIR__ . '/../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../backend/php/config/db.php';
require_once __DIR__ . '/../../../../../backend/php/shared/AuditLogger.php';
require_once __DIR__ . '/../../../../../web/components/layout/dashboard_shell.php';

$user = require_role('owner_teacher');
$message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $testimonialId = (int) ($_POST['testimonial_id'] ?? 0);
    $status = $_POST['status'] ?? '';

    if ($testimonialId > 0 && in_array($status, ['approved', 'rejected', 'pending'], true)) {
        db()->prepare('UPDATE testimonials SET status = :status WHERE id = :id')->execute([
            ':status' => $status,
            ':id' => $testimonialId,
        ]);
        audit_log((int) $user['id'], 'testimonial_status_updated', 'testimonial', (string) $testimonialId, ['status' => $status]);
        $message = 'Testimonial status updated.';
    }
}

$testimonials = db()->query('SELECT * FROM testimonials ORDER BY id DESC LIMIT 100')->fetchAll();

ob_start();
?>
<p class="text-muted">Approve, reject, or keep testimonials pending. Only approved testimonials appear publicly.</p>
<?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

<?php if (!$testimonials): ?>
  <div class="alert alert-light border">No testimonials submitted yet.</div>
<?php else: ?>
  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead><tr><th>Name</th><th>Body</th><th>Rating</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($testimonials as $testimonial): ?>
          <tr>
            <td><strong><?= htmlspecialchars($testimonial['name'], ENT_QUOTES, 'UTF-8') ?></strong><br><small class="text-muted"><?= htmlspecialchars($testimonial['role_label'] ?? '', ENT_QUOTES, 'UTF-8') ?></small></td>
            <td><?= htmlspecialchars(mb_strimwidth($testimonial['body'], 0, 160, '...'), ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars((string) ($testimonial['rating'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
            <td><span class="badge text-bg-light border"><?= htmlspecialchars($testimonial['status'], ENT_QUOTES, 'UTF-8') ?></span></td>
            <td>
              <?php foreach (['approved' => 'Approve', 'rejected' => 'Reject', 'pending' => 'Pending'] as $status => $label): ?>
                <form method="post" class="d-inline">
                  <input type="hidden" name="testimonial_id" value="<?= (int) $testimonial['id'] ?>">
                  <input type="hidden" name="status" value="<?= $status ?>">
                  <button class="btn btn-sm btn-outline-brand" type="submit"><?= $label ?></button>
                </form>
              <?php endforeach; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'CMS Testimonials', $content);
