<?php
/**
 * /owner/cms/articles/edit?id=...
 *
 * Owner-only article editor. This completes Phase 3's create/edit/publish
 * acceptance criteria while keeping moderation and publishing server-side.
 */

require_once __DIR__ . '/../../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../../backend/php/config/db.php';
require_once __DIR__ . '/../../../../../../backend/php/shared/AuditLogger.php';
require_once __DIR__ . '/../../../../../../backend/php/shared/PublicContent.php';
require_once __DIR__ . '/../../../../../../web/components/layout/dashboard_shell.php';

$user = require_role('owner_teacher');
$articleId = (int) ($_GET['id'] ?? 0);
$error = null;

$statement = db()->prepare('SELECT * FROM articles WHERE id = :id LIMIT 1');
$statement->execute([':id' => $articleId]);
$article = $statement->fetch();

if (!$article) {
    http_response_code(404);
    $content = '<div class="alert alert-danger">Article not found.</div><a class="btn btn-outline-brand" href="/owner/cms/articles">Back</a>';
    render_dashboard_shell($user, 'Edit Article', $content);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titleEn = trim($_POST['title_en'] ?? '');
    $titleAr = trim($_POST['title_ar'] ?? '');
    $slug = trim($_POST['slug'] ?? '') ?: slugify($titleEn);
    $excerptEn = trim($_POST['excerpt_en'] ?? '');
    $excerptAr = trim($_POST['excerpt_ar'] ?? '');
    $bodyEn = trim($_POST['body_en'] ?? '');
    $bodyAr = trim($_POST['body_ar'] ?? '');
    $seoTitle = trim($_POST['seo_title'] ?? '');
    $seoDescription = trim($_POST['seo_description'] ?? '');
    $status = in_array($_POST['status'] ?? 'draft', ['draft', 'published'], true) ? $_POST['status'] : 'draft';

    if ($titleEn === '' || $bodyEn === '') {
        $error = 'English title and body are required.';
    } else {
        $publishedAtSql = $status === 'published' ? ', published_at = COALESCE(published_at, NOW())' : '';
        $update = db()->prepare("UPDATE articles SET title_en = :title_en, title_ar = :title_ar, slug = :slug, excerpt_en = :excerpt_en, excerpt_ar = :excerpt_ar, body_en = :body_en, body_ar = :body_ar, seo_title = :seo_title, seo_description = :seo_description, status = :status {$publishedAtSql} WHERE id = :id");
        $update->execute([
            ':title_en' => $titleEn,
            ':title_ar' => $titleAr ?: null,
            ':slug' => $slug,
            ':excerpt_en' => $excerptEn ?: null,
            ':excerpt_ar' => $excerptAr ?: null,
            ':body_en' => $bodyEn,
            ':body_ar' => $bodyAr ?: null,
            ':seo_title' => $seoTitle ?: $titleEn . ' | Habiba Nabil Arabic Academy',
            ':seo_description' => $seoDescription ?: $excerptEn,
            ':status' => $status,
            ':id' => $articleId,
        ]);
        audit_log((int) $user['id'], 'article_updated', 'article', (string) $articleId, ['status' => $status]);
        header('Location: /owner/cms/articles');
        exit;
    }
}

ob_start();
?>
<p class="text-muted">Edit article content, SEO, slug, and status.</p>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
<form method="post" class="row g-3">
  <div class="col-md-8"><label class="form-label">Title EN</label><input class="form-control" name="title_en" value="<?= htmlspecialchars($article['title_en'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required></div>
  <div class="col-md-4"><label class="form-label">Slug</label><input class="form-control" name="slug" value="<?= htmlspecialchars($article['slug'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></div>
  <div class="col-md-12"><label class="form-label">Title AR</label><input class="form-control" name="title_ar" value="<?= htmlspecialchars($article['title_ar'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></div>
  <div class="col-md-6"><label class="form-label">Excerpt EN</label><textarea class="form-control" name="excerpt_en" rows="3"><?= htmlspecialchars($article['excerpt_en'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea></div>
  <div class="col-md-6"><label class="form-label">Excerpt AR</label><textarea class="form-control" name="excerpt_ar" rows="3"><?= htmlspecialchars($article['excerpt_ar'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea></div>
  <div class="col-md-6"><label class="form-label">Body EN</label><textarea class="form-control" name="body_en" rows="8" required><?= htmlspecialchars($article['body_en'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea></div>
  <div class="col-md-6"><label class="form-label">Body AR</label><textarea class="form-control" name="body_ar" rows="8"><?= htmlspecialchars($article['body_ar'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea></div>
  <div class="col-md-6"><label class="form-label">SEO Title</label><input class="form-control" name="seo_title" value="<?= htmlspecialchars($article['seo_title'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></div>
  <div class="col-md-6"><label class="form-label">SEO Description</label><input class="form-control" name="seo_description" value="<?= htmlspecialchars($article['seo_description'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></div>
  <div class="col-md-4"><label class="form-label">Status</label><select class="form-select" name="status"><option value="draft" <?= $article['status'] === 'draft' ? 'selected' : '' ?>>Draft</option><option value="published" <?= $article['status'] === 'published' ? 'selected' : '' ?>>Published</option></select></div>
  <div class="col-12"><button class="btn btn-brand" type="submit">Update article</button> <a class="btn btn-outline-brand" href="/owner/cms/articles">Cancel</a></div>
</form>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Edit Article', $content);
