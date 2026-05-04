<?php
/**
 * Public website layout for Phase 3.
 *
 * Public pages share this layout so SEO tags, responsive navigation, CTAs, and
 * brand styling stay consistent across homepage, pricing, articles, and legal pages.
 */

function render_public_layout(string $title, string $description, string $content, bool $indexable = true): void
{
    ?>
    <!doctype html>
    <html lang="en" dir="ltr">
    <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></title>
      <meta name="description" content="<?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?>">
      <meta name="robots" content="<?= $indexable ? 'index, follow' : 'noindex, nofollow' ?>">
      <link rel="preconnect" href="https://fonts.googleapis.com">
      <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
      <link rel="dns-prefetch" href="//cdn.jsdelivr.net">
      <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
      <link href="/assets/css/app.css" rel="stylesheet">
    </head>
    <body>
      <nav class="navbar navbar-expand-lg bg-white border-bottom sticky-top">
        <div class="container">
          <a class="navbar-brand d-flex align-items-center gap-2 fw-bold" href="/">
            <span class="brand-mark" style="width:42px;height:42px;font-size:24px;border-radius:14px;">ض</span>
            <span>Habiba Nabil</span>
          </a>
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#publicNav">
            <span class="navbar-toggler-icon"></span>
          </button>
          <div class="collapse navbar-collapse" id="publicNav">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center gap-lg-2">
              <li class="nav-item"><a class="nav-link" href="/pricing">Pricing</a></li>
              <li class="nav-item"><a class="nav-link" href="/articles">Articles</a></li>
              <li class="nav-item"><a class="nav-link" href="/videos">Videos</a></li>
              <li class="nav-item"><a class="nav-link" href="/testimonials">Testimonials</a></li>
              <li class="nav-item"><a class="btn btn-brand btn-sm" href="/pricing">Start Now</a></li>
              <li class="nav-item"><a class="nav-link" href="/login">Login</a></li>
            </ul>
          </div>
        </div>
      </nav>

      <?= $content ?>

      <footer class="bg-white border-top mt-5 py-4">
        <div class="container d-flex flex-wrap justify-content-between gap-3 small text-muted">
          <div>© <?= date('Y') ?> Habiba Nabil Arabic Academy</div>
          <div class="d-flex gap-3">
            <a class="text-muted" href="/terms">Terms</a>
            <a class="text-muted" href="/privacy">Privacy</a>
            <a class="text-muted" href="/refund">Refund</a>
          </div>
        </div>
      </footer>
      <script src="/assets/js/media-tracking.js" defer></script>
      <script src="/assets/js/motion-polish.js" defer></script>
      <script src="/assets/js/ux-polish.js" defer></script>
      <script src="/assets/js/performance.js" defer></script>
      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
    </body>
    </html>
    <?php
}
