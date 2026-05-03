<?php
require_once __DIR__ . '/../../../backend/php/shared/PublicContent.php';
require_once __DIR__ . '/../../../web/components/layout/public_layout.php';

$page = page_content('privacy');
$seo = seo_text($page, 'Privacy Policy | Habiba Nabil Arabic Academy', 'Read the privacy policy for Habiba Nabil Arabic Academy.');

ob_start();
?>
<section class="py-5"><div class="container"><div class="foundation-card"><h1 class="hero-title mb-4"><?= htmlspecialchars($page['title_en'] ?? 'Privacy Policy', ENT_QUOTES, 'UTF-8') ?></h1><div class="text-muted" style="white-space: pre-wrap;"><?= htmlspecialchars($page['body_en'] ?? 'Privacy content coming soon.', ENT_QUOTES, 'UTF-8') ?></div></div></div></section>
<?php
$content = ob_get_clean();
render_public_layout($seo['title'], $seo['description'], $content, (bool) ($page['is_indexable'] ?? true));
