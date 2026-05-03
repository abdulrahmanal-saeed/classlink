<?php
/**
 * /owner/cms/videos
 *
 * Owner-only video manager for Phase 3. Supports create and publish/draft basics
 * from one page to keep the phase small and testable.
 */

require_once __DIR__ . '/../../../../../backend/php/core/Auth.php';
require_once __DIR__ . '/../../../../../backend/php/config/db.php';
require_once __DIR__ . '/../../../../../backend/php/shared/AuditLogger.php';
require_once __DIR__ . '/../../../../../backend/php/shared/PublicContent.php';
require_once __DIR__ . '/../../../../../web/components/layout/dashboard_shell.php';

$user = require_role('owner_teacher');
$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formAction = $_POST['form_action'] ?? '';

    if ($formAction === 'create') {
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
            $statement = db()->prepare('INSERT INTO videos (title_en, title_ar, slug, description_en, description_ar, video_url, platform, seo_title, seo_description, status) VALUES (:title_en, :title_ar, :slug, :description_en, :description_ar, :video_url, :platform, :seo_title, :seo_description, :status)');
            $statement->execute([
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
            ]);
            $videoId = (int) db()->lastInsertId();
            audit_log((int) $user['id'], 'video_created', 'video', (string) $videoId, ['status' => $status]);
            $message = 'Video created successfully.';
        }
    }

    if ($formAction === 'status') {
        $videoId = (int) ($_POST['video_id'] ?? 0);
        $status = ($_POST['status'] ?? '') === 'published' ? 'published' : 'draft';

        if ($videoId > 0) {
            db()->prepare('UPDATE videos SET status = :status WHERE id = :id')->execute([':status' => $status, ':id' => $videoId]);
            audit_log((int) $user['id'], 'video_status_updated', 'video', (string) $videoId, ['status' => $status]);
            $message = 'Video status updated.';
        }
    }
}

$videos = db()->query('SELECT * FROM videos ORDER BY id DESC LIMIT 100')->fetchAll();

ob_start();
?>
<?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

<div class="row g-4">
  <div class="col-lg-5">
    <div class="status-box">
      <h2 class="h5 fw-bold">Add video</h2>
      <form method="post" class="mt-3">
        <input type="hidden" name="form_action" value="create">
        <div class="mb-3"><label class="form-label">Title EN</label><input class="form-control" name="title_en" required></div>
        <div class="mb-3"><label class="form-label">Title AR</label><input class="form-control" name="title_ar"></div>
        <div class="mb-3"><label class="form-label">Slug</label><input class="form-control" name="slug"></div>
        <div class="mb-3"><label class="form-label">Embed URL</label><input class="form-control" name="video_url" placeholder="https://www.youtube.com/embed/..." required></div>
        <div class="mb-3"><label class="form-label">Description EN</label><textarea class="form-control" name="description_en" rows="3"></textarea></div>
        <div class="mb-3"><label class="form-label">Description AR</label><textarea class="form-control" name="description_ar" rows="3"></textarea></div>
        <div class="mb-3"><label class="form-label">SEO Title</label><input class="form-control" name="seo_title"></div>
        <div class="mb-3"><label class="form-label">SEO Description</label><input class="form-control" name="seo_description"></div>
        <div class="mb-3"><label class="form-label">Status</label><select class="form-select" name="status"><option value="draft">Draft</option><option value="published">Published</option></select></div>
        <button class="btn btn-brand" type="submit">Save video</button>
      </form>
    </div>
  </div>
  <div class="col-lg-7">
    <h2 class="h5 fw-bold mb-3">Videos</h2>
    <?php if (!$videos): ?><div class="alert alert-light border">No videos yet.</div><?php else: ?>
      <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Title</th><th>Status</th><th>Slug</th><th>Actions</th></tr></thead><tbody>
        <?php foreach ($videos as $video): ?>
          <tr>
            <td><strong><?= htmlspecialchars($video['title_en'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong></td>
            <td><span class="badge text-bg-light border"><?= htmlspecialchars($video['status'], ENT_QUOTES, 'UTF-8') ?></span></td>
            <td><code><?= htmlspecialchars($video['slug'] ?? '', ENT_QUOTES, 'UTF-8') ?></code></td>
            <td><form method="post" class="d-inline"><input type="hidden" name="form_action" value="status"><input type="hidden" name="video_id" value="<?= (int) $video['id'] ?>"><input type="hidden" name="status" value="<?= $video['status'] === 'published' ? 'draft' : 'published' ?>"><button class="btn btn-sm btn-outline-brand" type="submit"><?= $video['status'] === 'published' ? 'Move to draft' : 'Publish' ?></button></form><?php if ($video['status'] === 'published' && !empty($video['slug'])): ?><a class="btn btn-sm btn-link" href="/videos/<?= urlencode($video['slug']) ?>" target="_blank">View</a><?php endif; ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody></table></div>
    <?php endif; ?>
  </div>
</div>
<?php
$content = ob_get_clean();
render_dashboard_shell($user, 'CMS Videos', $content);
