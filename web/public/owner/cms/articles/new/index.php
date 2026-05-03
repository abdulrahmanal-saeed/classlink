<?php
/**
 * /owner/cms/articles/new
 *
 * Creates a draft or published article. Editing existing articles will be
 * expanded later; Phase 3 acceptance focuses on create/publish basics.
 */

require_once __DIR__ . '/../../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../../backend/php/config/db.php';
require_once __DIR__ . '/../../../../../../backend/php/shared/AuditLogger.php';
require_once __DIR__ . '/../../../../../../backend/php/shared/PublicContent.php';
require_once __DIR__ . '/../../../../../../web/components/layout/dashboard_shell.php';

$user = require_role('owner_teacher');
$error = null;

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
        $statement = db()->prepare('INSERT INTO articles (title_en, title_ar, slug, excerpt_en, excerpt_ar, body_en, body_ar, seo_title, seo_description, status, published_at) VALUES (:title_en, :title_ar, :slug, :excerpt_en, :excerpt_ar, :body_en, :body_ar, :seo_title, :seo_description, :status, :published_at)');
        $statement->execute([
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
            ':published_at' => $status === 'published' ? date('Y-m-d H:i:s') : null,
        ]);
        $articleId = (int) db()->lastInsertId();
        audit_log((int) $user['id'], 'article_created', 'article', (string) $articleId, ['status' => $status]);
        header('Location: /owner/cms/articles');
        exit;
    }
}

ob_start();
?>
<p class="text-muted">Create a new article. It can stay draft until you are ready to publish.</p>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
<form method="post" class="row g-3">
  <div class="col-md-8"><label class="form-label">Title EN</label><input class="form-control" name="title_en" required></div>
  <div class="col-md-4"><label class="form-label">Slug</label><input class="form-control" name="slug" placeholder="article-slug"></div>
  <div class="col-md-12"><label class="form-label">Title AR</label><input class="form-control" name="title_ar"></div>
  <div class="col-md-6"><label class="form-label">Excerpt EN</label><textarea class="form-control" name="excerpt_en" rows="3"></textarea></div>
  <div class="col-md-6"><label class="form-label">Excerpt AR</label><textarea class="form-control" name="excerpt_ar" rows="3"></textarea></div>
  <div class="col-md-6"><label class="form-label">Body EN</label><textarea class="form-control" name="body_en" rows="8" required></textarea></div>
  <div class="col-md-6"><label class="form-label">Body AR</label><textarea class="form-control" name="body_ar" rows="8"></textarea></div>
  <div class="col-md-6"><label class="form-label">SEO Title</label><input class="form-control" name="seo_title"></div>
  <div class="col-md-6"><label class="form-label">SEO Description</label><input class="form-control" name="seo_description"></div>
  <div class="col-md-4"><label class="form-label">Status</label><select class="form-select" name="status"><option value="draft">Draft</option><option value="published">Published</option></select></div>
  <div class="col-12"><button class="btn btn-brand" type="submit">Save article</button> <a class="btn btn-outline-brand" href="/owner/cms/articles">Cancel</a></div>
</form>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'New Article', $content);
