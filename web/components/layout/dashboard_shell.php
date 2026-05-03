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
        'owner_teacher' => [
            ['/owner/dashboard', 'Owner Dashboard', 'لوحة المالك'],
            ['/owner/payments', 'Payments', 'المدفوعات'],
            ['/owner/onboarding', 'Onboarding', 'المتابعة بعد الدفع'],
            ['/owner/packages', 'Packages & Credits', 'الباقات والأرصدة'],
            ['/owner/students', 'Students', 'الطلاب'],
            ['/owner/parents', 'Parents', 'أولياء الأمور'],
            ['/owner/level-checks', 'Paid Level Checks', 'اختبارات المستوى المدفوعة'],
            ['/owner/free-level-test/attempts', 'Free Level Tests', 'اختبارات المستوى المجانية'],
            ['/owner/free-level-test/settings', 'Free Test Settings', 'إعدادات الاختبار المجاني'],
            ['/owner/settings', 'Settings Center', 'مركز الإعدادات'],
            ['/owner/settings/public-website', 'Public Website Settings', 'إعدادات الموقع العام'],
            ['/owner/cms/articles', 'CMS Articles', 'إدارة المقالات'],
            ['/owner/cms/videos', 'CMS Videos', 'إدارة الفيديوهات'],
            ['/owner/cms/testimonials', 'CMS Testimonials', 'إدارة التقييمات'],
            ['/owner/audit-log', 'Audit Log', 'سجل المراجعة'],
            ['/owner/dev/seed-data', 'Dev Seed Data', 'بيانات تجريبية'],
        ],
        'student' => [
            ['/student/dashboard', 'Student Dashboard', 'لوحة الطالب'],
            ['/student/balance', 'My Balance', 'رصيدي'],
        ],
        'parent' => [
            ['/parent/dashboard', 'Parent Dashboard', 'لوحة ولي الأمر'],
        ],
        'academy_partner' => [
            ['/academy/dashboard', 'Academy Dashboard', 'لوحة شريك الأكاديمية'],
        ],
    ];

    $currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

    ?>
    <!doctype html>
    <html lang="<?= htmlspecialchars($lang, ENT_QUOTES, 'UTF-8') ?>" dir="<?= $isArabic ? 'rtl' : 'ltr' ?>">
    <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?> | Habiba Nabil</title>
      <meta name="robots" content="noindex, nofollow">
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
                <?php foreach (($items[$user['role']] ?? []) as $item): ?>
                  <?php $isActive = $currentPath === $item[0] || str_starts_with($currentPath, $item[0] . '/'); ?>
                  <a class="list-group-item list-group-item-action <?= $isActive ? 'active' : '' ?>" href="<?= $item[0] ?>">
                    <?= $isArabic ? $item[2] : $item[1] ?>
                  </a>
                <?php endforeach; ?>
              </div>
            </div>
          </aside>
          <main class="col-lg-9">
            <div class="foundation-card">
              <div class="badge text-bg-light border mb-3">Phase 8</div>
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
