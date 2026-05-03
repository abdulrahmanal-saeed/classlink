<?php
/**
 * /videos and /videos/{slug}
 */

require_once __DIR__ . '/../../../backend/php/shared/PublicContent.php';
require_once __DIR__ . '/../../../web/components/layout/public_layout.php';

$slug = trim($_GET['slug'] ?? '');

if ($slug !== '') {
    $video = video_by_slug($slug);

    if (!$video) {
        http_response_code(404);
        $content = '<section class="py-5"><div class="container"><div class="foundation-card"><h1 class="hero-title">Video not found</h1><p class="text-muted">This video is not available.</p></div></div></section>';
        render_public_layout('Video not found | Habiba Nabil Arabic Academy', 'Video not found.', $content, false);
        exit;
    }

    $seo = seo_text($video, ($video['title_en'] ?? 'Video') . ' | Habiba Nabil Arabic Academy', $video['seo_description'] ?? 'Arabic learning video.');
    ob_start();
    ?>
    <section class="py-5"><div class="container"><div class="foundation-card"><h1 class="hero-title mb-3"><?= htmlspecialchars($video['title_en'] ?? 'Video', ENT_QUOTES, 'UTF-8') ?></h1><p class="hero-subtitle"><?= htmlspecialchars($video['description_en'] ?? '', ENT_QUOTES, 'UTF-8') ?></p><div class="ratio ratio-16x9 bg-light rounded-4 overflow-hidden"><iframe src="<?= htmlspecialchars($video['video_url'], ENT_QUOTES, 'UTF-8') ?>" title="Video" allowfullscreen></iframe></div></div></div></section>
    <?php
    render_public_layout($seo['title'], $seo['description'], ob_get_clean(), true);
    exit;
}

$videos = published_videos(50);
ob_start();
?>
<section class="py-5"><div class="container"><h1 class="hero-title display-4 mb-4">Arabic learning videos</h1><?php if (!$videos): ?><div class="alert alert-light border">No published videos yet.</div><?php else: ?><div class="row g-4"><?php foreach ($videos as $video): ?><div class="col-md-6"><div class="foundation-card h-100"><h2 class="h4 fw-bold"><?= htmlspecialchars($video['title_en'] ?? 'Video', ENT_QUOTES, 'UTF-8') ?></h2><p class="text-muted"><?= htmlspecialchars($video['description_en'] ?? '', ENT_QUOTES, 'UTF-8') ?></p><a class="btn btn-outline-brand" href="/videos/<?= urlencode($video['slug'] ?? '') ?>">Watch video</a></div></div><?php endforeach; ?></div><?php endif; ?></div></section>
<?php
render_public_layout('Videos | Habiba Nabil Arabic Academy', 'Watch Arabic learning videos for non-native speakers.', ob_get_clean(), true);
