<?php
/**
 * /owner/cms/videos/edit?id=...
 *
 * Owner-only video editor. This completes Phase 3 video create/edit/publish basics.
 */

require_once __DIR__ . '/../../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../../backend/php/config/db.php';
require_once __DIR__ . '/../../../../../../backend/php/shared/AuditLogger.php';
require_once __DIR__ . '/../../../../../../backend/php/shared/PublicContent.php';
require_once __DIR__ . '/../../../../../../web/components/layout/dashboard_shell.php';

$user = require_role('owner_teacher');
$videoId = (int) ($_GET['id'] ?? 0);
$error = null;

$statement = db()->prepare('SELECT * FROM videos WHERE id = :id LIMIT 1');
$statement->execute([':id' => $videoId]);
$video = $statement->fetch();

if (!$video) {
    http_response_code(404);
    $content = '<div class="alert alert-danger">Video not found.</div><a class="btn btn-outline-brand" href="/owner/cms/videos">Back</a>';
    render_dashboard_shell($user, 'Edit Video', $content);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titleEn = trim($_POST['title_en'] ?? '');
    $titleAr = trim($_POST['title_ar'] ?? '');
    $slug = trim($_POST['slug'] ?? '') ?: slugify($titleEn);
    $videoUrl = trim($_POST['video_url'] ?? '');
    $descriptionEn = trim($_POST['description_en'] ?? '');
    $descriptionAr = trim($_POST['description_ar'] ?? '');
    $seoTitle = trim($_POST['seo_title'] ?? '');
    $seoDescription = trim($_POST['seo_description'] ?? '');
    $status = in_array($_POST['status'] ?? 'draft', ['draft', 'published'], true) ? $_POST['status'] : 'draft';

    if ($titleEn === '' || $videoUrl === '') {
        $error = 'English title and video URL are required.';
    } else {
        $update = db()->prepare('UPDATE videos SET title_en = :title_en, title_ar = :title_ar, slug = :slug, description_en = :description_en, description_ar = :description_ar, video_url = :video_url, platform = :platform, seo_title = :seo_title, seo_description = :seo_description, status = :status WHERE id = :id');
        $update->execute([
            ':title_en' => $titleEn,
            ':title_ar' => $titleAr ?: null,
            ':slug' => $slug,
            ':description_en' => $descriptionEn ?: null,
            ':description_ar' => $descriptionAr ?: null,
            ':video_url' => $videoUrl,
            ':platform' => str_contains($videoUrl, 'youtube') || str_contains($videoUrl, 'youtu.be') ? 'youtube' : 'external',
            ':seo_title' => $seoTitle ?: $titleEn . ' | Habiba Nabil Arabic Academy',
            ':seo_description' => $seoDescription ?: $descriptionEn,
            ':status' => $status,
            ':id' => $videoId,
        ]);
        audit_log((int) $user['id'], 'video_updated', 'video', (string) $videoId, ['status' => $status]);
        header('Location: /owner/cms/videos');
        exit;
    }
}

ob_start();
?>
<p class="text-muted">Edit video content, SEO, slug, embed URL, and publishing status.</p>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
<form method="post" class="row g-3">
  <div class="col-md-8"><label class="form-label">Title EN</label><input class="form-control" name="title_en" value="<?= htmlspecialchars($video['title_en'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required></div>
  <div class="col-md-4"><label class="form-label">Slug</label><input class="form-control" name="slug" value="<?= htmlspecialchars($video['slug'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></div>
  <div class="col-md-12"><label class="form-label">Title AR</label><input class="form-control" name="title_ar" value="<?= htmlspecialchars($video['title_ar'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></div>
  <div class="col-md-12"><label class="form-label">Embed URL</label><input class="form-control" name="video_url" value="<?= htmlspecialchars($video['video_url'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required></div>
  <div class="col-md-6"><label class="form-label">Description EN</label><textarea class="form-control" name="description_en" rows="4"><?= htmlspecialchars($video['description_en'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea></div>
  <div class="col-md-6"><label class="form-label">Description AR</label><textarea class="form-control" name="description_ar" rows="4"><?= htmlspecialchars($video['description_ar'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea></div>
  <div class="col-md-6"><label class="form-label">SEO Title</label><input class="form-control" name="seo_title" value="<?= htmlspecialchars($video['seo_title'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></div>
  <div class="col-md-6"><label class="form-label">SEO Description</label><input class="form-control" name="seo_description" value="<?= htmlspecialchars($video['seo_description'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></div>
  <div class="col-md-4"><label class="form-label">Status</label><select class="form-select" name="status"><option value="draft" <?= $video['status'] === 'draft' ? 'selected' : '' ?>>Draft</option><option value="published" <?= $video['status'] === 'published' ? 'selected' : '' ?>>Published</option></select></div>
  <div class="col-12"><button class="btn btn-brand" type="submit">Update video</button> <a class="btn btn-outline-brand" href="/owner/cms/videos">Cancel</a></div>
</form>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'Edit Video', $content);
