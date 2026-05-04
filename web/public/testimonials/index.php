<?php
/** /testimonials - public approved testimonials only. */
require_once __DIR__ . '/../../../backend/php/shared/Testimonials.php';
require_once __DIR__ . '/../../../web/components/layout/public_layout.php';

$testimonials = testimonial_public(false);

ob_start();
?>
<section class="py-5">
  <div class="container">
    <div class="d-flex justify-content-between align-items-end gap-3 flex-wrap mb-4">
      <div><h1 class="hero-title display-4 mb-2">Student testimonials</h1><p class="hero-subtitle mb-0">Real approved stories from Arabic learners and parents.</p></div>
      <a class="btn btn-brand" href="/submit-testimonial">Submit testimonial</a>
    </div>
    <?php if (!testimonial_bool('testimonials_show_page', true)): ?>
      <div class="alert alert-light border">Testimonials page is currently unavailable.</div>
    <?php elseif (!$testimonials): ?>
      <div class="alert alert-light border">No approved testimonials yet.</div>
    <?php else: ?>
      <div class="row g-4">
        <?php foreach ($testimonials as $testimonial): ?>
          <div class="col-md-4"><div class="foundation-card h-100">
            <?php if($testimonial['featured']): ?><span class="badge text-bg-warning mb-2">Featured</span><?php endif; ?>
            <div class="text-warning mb-2"><?= str_repeat('★', (int)$testimonial['rating']) ?></div>
            <?php $text = testimonial_public_text($testimonial); if($text): ?><p>“<?= nl2br(htmlspecialchars($text, ENT_QUOTES, 'UTF-8')) ?>”</p><?php endif; ?>
            <?php if (!empty($testimonial['audio_url'])): ?><audio controls preload="metadata" class="w-100 mb-3" src="<?= htmlspecialchars($testimonial['audio_url'], ENT_QUOTES, 'UTF-8') ?>"></audio><?php endif; ?>
            <?php if (!empty($testimonial['video_url'])): ?><video controls preload="metadata" class="w-100 rounded-4 mb-3" src="<?= htmlspecialchars($testimonial['video_url'], ENT_QUOTES, 'UTF-8') ?>"></video><?php endif; ?>
            <strong><?= htmlspecialchars(testimonial_public_name($testimonial), ENT_QUOTES, 'UTF-8') ?></strong>
            <div class="small text-muted"><?= htmlspecialchars($testimonial['submitter_type'], ENT_QUOTES, 'UTF-8') ?><?= $testimonial['level'] ? ' · '.htmlspecialchars($testimonial['level'], ENT_QUOTES, 'UTF-8') : '' ?><?= $testimonial['learning_goal'] ? ' · '.htmlspecialchars($testimonial['learning_goal'], ENT_QUOTES, 'UTF-8') : '' ?></div>
          </div></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php
render_public_layout('Testimonials | Habiba Nabil Arabic Academy', 'Read approved testimonials from Arabic learners.', ob_get_clean(), true);
