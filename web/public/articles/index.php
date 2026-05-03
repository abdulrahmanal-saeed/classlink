<?php
/**
 * /articles and /articles/{slug}
 *
 * Supports listing and detail from one file. Apache rewrites pretty article URLs
 * into this file with a slug query parameter.
 */

require_once __DIR__ . '/../../../backend/php/shared/PublicContent.php';
require_once __DIR__ . '/../../../web/components/layout/public_layout.php';

$slug = trim($_GET['slug'] ?? '');

if ($slug !== '') {
    $article = article_by_slug($slug);

    if (!$article) {
        http_response_code(404);
        $content = '<section class="py-5"><div class="container"><div class="foundation-card"><h1 class="hero-title">Article not found</h1><p class="text-muted">This article is not available.</p></div></div></section>';
        render_public_layout('Article not found | Habiba Nabil Arabic Academy', 'Article not found.', $content, false);
        exit;
    }

    $seo = seo_text($article, $article['title_en'] . ' | Habiba Nabil Arabic Academy', $article['excerpt_en'] ?? 'Arabic learning article.');
    ob_start();
    ?>
    <article class="py-5"><div class="container"><div class="foundation-card"><h1 class="hero-title mb-3"><?= htmlspecialchars($article['title_en'], ENT_QUOTES, 'UTF-8') ?></h1><p class="hero-subtitle"><?= htmlspecialchars($article['excerpt_en'] ?? '', ENT_QUOTES, 'UTF-8') ?></p><hr><div style="white-space: pre-wrap;"><?= htmlspecialchars($article['body_en'] ?? '', ENT_QUOTES, 'UTF-8') ?></div></div></div></article>
    <?php
    render_public_layout($seo['title'], $seo['description'], ob_get_clean(), true);
    exit;
}

$articles = published_articles(50);
ob_start();
?>
<section class="py-5"><div class="container"><h1 class="hero-title display-4 mb-4">Arabic learning articles</h1><?php if (!$articles): ?><div class="alert alert-light border">No published articles yet.</div><?php else: ?><div class="row g-4"><?php foreach ($articles as $article): ?><div class="col-md-6"><div class="foundation-card h-100"><h2 class="h4 fw-bold"><?= htmlspecialchars($article['title_en'], ENT_QUOTES, 'UTF-8') ?></h2><p class="text-muted"><?= htmlspecialchars($article['excerpt_en'] ?? '', ENT_QUOTES, 'UTF-8') ?></p><a class="btn btn-outline-brand" href="/articles/<?= urlencode($article['slug']) ?>">Read article</a></div></div><?php endforeach; ?></div><?php endif; ?></div></section>
<?php
render_public_layout('Articles | Habiba Nabil Arabic Academy', 'Read Arabic learning articles for non-native speakers.', ob_get_clean(), true);
