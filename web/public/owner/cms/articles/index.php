<?php
/**
 * /owner/cms/articles
 *
 * Owner-only article manager. Phase 3 keeps this simple: list articles,
 * publish/unpublish, and link to the create page.
 */

require_once __DIR__ . '/../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../backend/php/config/db.php';
require_once __DIR__ . '/../../../../../backend/php/shared/AuditLogger.php';
require_once __DIR__ . '/../../../../../web/components/layout/dashboard_shell.php';

$user = require_role('owner_teacher');
$message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $articleId = (int) ($_POST['article_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($articleId > 0 && in_array($action, ['publish', 'draft'], true)) {
        $status = $action === 'publish' ? 'published' : 'draft';
        $publishedAtSql = $status === 'published' ? ', published_at = COALESCE(published_at, NOW())' : '';
        db()->prepare("UPDATE articles SET status = :status {$publishedAtSql} WHERE id = :id")->execute([
            ':status' => $status,
            ':id' => $articleId,
        ]);
        audit_log((int) $user['id'], 'article_status_updated', 'article', (string) $articleId, ['status' => $status]);
        $message = 'Article status updated.';
    }
}

$articles = db()->query('SELECT * FROM articles ORDER BY id DESC LIMIT 100')->fetchAll();

ob_start();
?>
<div class="d-flex justify-content-between align-items-center gap-3 flex-wrap mb-4">
  <p class="text-muted mb-0">Create, review, and publish Arabic learning articles.</p>
  <a class="btn btn-brand" href="/owner/cms/articles/new">New article</a>
</div>

<?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

<?php if (!$articles): ?>
  <div class="alert alert-light border">No articles yet.</div>
<?php else: ?>
  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead><tr><th>Title</th><th>Slug</th><th>Status</th><th>SEO</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($articles as $article): ?>
          <tr>
            <td><strong><?= htmlspecialchars($article['title_en'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong></td>
            <td><code><?= htmlspecialchars($article['slug'] ?? '', ENT_QUOTES, 'UTF-8') ?></code></td>
            <td><span class="badge text-bg-light border"><?= htmlspecialchars($article['status'], ENT_QUOTES, 'UTF-8') ?></span></td>
            <td class="small text-muted"><?= htmlspecialchars($article['seo_title'] ?? 'No SEO title', ENT_QUOTES, 'UTF-8') ?></td>
            <td>
              <form method="post" class="d-inline">
                <input type="hidden" name="article_id" value="<?= (int) $article['id'] ?>">
                <input type="hidden" name="action" value="<?= $article['status'] === 'published' ? 'draft' : 'publish' ?>">
                <button class="btn btn-sm btn-outline-brand" type="submit"><?= $article['status'] === 'published' ? 'Move to draft' : 'Publish' ?></button>
              </form>
              <?php if ($article['status'] === 'published'): ?><a class="btn btn-sm btn-link" href="/articles/<?= urlencode($article['slug']) ?>" target="_blank">View</a><?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'CMS Articles', $content);
