<?php
/**
 * /testimonials
 *
 * Shows approved testimonials only. Pending submissions are never displayed
 * until Owner approval.
 */

require_once __DIR__ . '/../../../backend/php/shared/PublicContent.php';
require_once __DIR__ . '/../../../web/components/layout/public_layout.php';

$testimonials = approved_testimonials(50);

ob_start();
?>
<section class="py-5">
  <div class="container">
    <div class="d-flex justify-content-between align-items-end gap-3 flex-wrap mb-4">
      <div>
        <h1 class="hero-title display-4 mb-2">Student testimonials</h1>
        <p class="hero-subtitle mb-0">Real feedback from Arabic learners after approval.</p>
      </div>
      <a class="btn btn-brand" href="/submit-testimonial">Submit testimonial</a>
    </div>

    <?php if (!$testimonials): ?>
      <div class="alert alert-light border">No approved testimonials yet.</div>
    <?php else: ?>
      <div class="row g-4">
        <?php foreach ($testimonials as $testimonial): ?>
          <div class="col-md-4">
            <div class="foundation-card h-100">
              <p>“<?= htmlspecialchars($testimonial['body'], ENT_QUOTES, 'UTF-8') ?>”</p>
              <strong><?= htmlspecialchars($testimonial['name'], ENT_QUOTES, 'UTF-8') ?></strong>
              <?php if (!empty($testimonial['role_label'])): ?><div class="small text-muted"><?= htmlspecialchars($testimonial['role_label'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
              <?php if (!empty($testimonial['rating'])): ?><div class="small text-muted mt-2">Rating: <?= (int) $testimonial['rating'] ?>/5</div><?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php
render_public_layout('Testimonials | Habiba Nabil Arabic Academy', 'Read approved testimonials from Arabic learners.', ob_get_clean(), true);
