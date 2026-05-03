<?php
/**
 * Shared dashboard shell.
 *
 * All role dashboards use this layout so navigation, profile dropdown,
 * language direction, and brand styling stay consistent.
 */

function render_dashboard_shell(array $user, string $title, string $content): void
{
    $lang = $_GET['lang'] ?? 'en';
    $lang = in_array($lang, ['ar', 'en'], true) ? $lang : 'en';
    $isArabic = $lang === 'ar';

    $items = [
        'owner_teacher' => ['/owner/dashboard', 'Owner Dashboard', 'لوحة المالك'],
        'student' => ['/student/dashboard', 'Student Dashboard', 'لوحة الطالب'],
        'parent' => ['/parent/dashboard', 'Parent Dashboard', 'لوحة ولي الأمر'],
        'academy_partner' => ['/academy/dashboard', 'Academy Dashboard', 'لوحة شريك الأكاديمية'],
    ];

    ?>
    <!doctype html>
    <html lang="<?= htmlspecialchars($lang, ENT_QUOTES, 'UTF-8') ?>" dir="<?= $isArabic ? 'rtl' : 'ltr' ?>">
    <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?> | Habiba Nabil</title>
      <link rel="preconnect" href="https://fonts.googleapis.com">
      <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
      <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap<?= $isArabic ? '.rtl' : '' ?>.min.css" rel="stylesheet">
      <link href="/assets/css/app.css" rel="stylesheet">
    </head>
    <body>
      <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div class="d-flex align-items-center gap-3">
            <div class="brand-mark">ض</div>
            <div>
              <div class="fw-bold">Habiba Nabil Arabic Academy</div>
              <div class="text-muted small">Role-based dashboard shell</div>
            </div>
          </div>
          <div class="dropdown">
            <button class="btn btn-outline-brand dropdown-toggle" type="button" data-bs-toggle="dropdown">
              <?= htmlspecialchars($user['display_name'], ENT_QUOTES, 'UTF-8') ?>
            </button>
            <ul class="dropdown-menu">
              <li><span class="dropdown-item-text small text-muted"><?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?></span></li>
              <li><span class="dropdown-item-text small">Role: <?= htmlspecialchars($user['role'], ENT_QUOTES, 'UTF-8') ?></span></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="/logout">Logout</a></li>
            </ul>
          </div>
        </div>

        <div class="row g-4">
          <aside class="col-lg-3">
            <div class="foundation-card p-3">
              <div class="fw-bold mb-3">Navigation</div>
              <div class="list-group list-group-flush">
                <?php foreach ($items as $role => $item): ?>
                  <?php if ($role === $user['role']): ?>
                    <a class="list-group-item list-group-item-action active" href="<?= $item[0] ?>">
                      <?= $isArabic ? $item[2] : $item[1] ?>
                    </a>
                  <?php endif; ?>
                <?php endforeach; ?>
              </div>
            </div>
          </aside>
          <main class="col-lg-9">
            <div class="foundation-card">
              <div class="badge text-bg-light border mb-3">Phase 1</div>
              <h1 class="hero-title h2 mb-3"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h1>
              <?= $content ?>
            </div>
          </main>
        </div>
      </div>
      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
    <?php
}
