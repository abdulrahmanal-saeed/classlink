<?php
/**
 * Phase 0000 public landing placeholder.
 *
 * This is not the final website. It only proves that the bilingual web shell,
 * brand colors, and translation files are connected correctly before building
 * real public pages in later phases.
 */

$lang = $_GET['lang'] ?? 'en';
$lang = in_array($lang, ['ar', 'en'], true) ? $lang : 'en';
$isArabic = $lang === 'ar';
?>
<!doctype html>
<html lang="<?= htmlspecialchars($lang, ENT_QUOTES, 'UTF-8') ?>" dir="<?= $isArabic ? 'rtl' : 'ltr' ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Habiba Nabil Arabic Academy</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap<?= $isArabic ? '.rtl' : '' ?>.min.css" rel="stylesheet">
  <link href="assets/css/app.css" rel="stylesheet">
</head>
<body>
  <main class="app-shell">
    <section class="foundation-card">
      <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
        <div class="d-flex align-items-center gap-3">
          <div class="brand-mark">ض</div>
          <div>
            <div class="fw-bold" data-i18n="common.brandName">Habiba Nabil Arabic Academy</div>
            <div class="text-muted" data-i18n="common.tagline">Learn Arabic the Smart Way</div>
          </div>
        </div>
        <a class="btn btn-sm btn-outline-brand" href="?lang=<?= $isArabic ? 'en' : 'ar' ?>">
          <?= $isArabic ? 'English' : 'العربية' ?>
        </a>
      </div>

      <div class="row align-items-center g-4">
        <div class="col-lg-7">
          <div class="badge text-bg-light border mb-3" data-i18n="common.phase">Phase 0000 Foundation</div>
          <h1 class="hero-title display-5 mb-3" data-i18n="home.heroTitle">
            Build Arabic confidence with a smart learning platform
          </h1>
          <p class="hero-subtitle mb-4" data-i18n="home.heroSubtitle">
            A bilingual foundation for students, parents, academy partners, and the Owner/Teacher dashboard.
          </p>
          <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-brand" href="#" data-i18n="home.primaryCta">Start Foundation Check</a>
            <a class="btn btn-outline-brand" href="../../backend/php/api/public/health.php" data-i18n="home.secondaryCta">View API Health</a>
          </div>
        </div>
        <div class="col-lg-5">
          <div class="status-box">
            <h2 class="h4 fw-bold" data-i18n="home.statusTitle">Foundation is ready</h2>
            <p class="mb-0 text-muted" data-i18n="home.statusBody">
              The repository now has the first clean structure for PHP, MySQL, Flutter, Firebase support, and bilingual UI.
            </p>
          </div>
        </div>
      </div>
    </section>
  </main>

  <script src="assets/js/lang/<?= $isArabic ? 'ar' : 'en' ?>.js"></script>
  <script>
    /**
     * Simple translation binder.
     *
     * We keep this small for Phase 0000. Later phases can replace it with a
     * stronger i18n helper while keeping the same ar.js/en.js concept.
     */
    document.querySelectorAll('[data-i18n]').forEach((element) => {
      const path = element.getAttribute('data-i18n').split('.');
      let value = window.APP_LANG;

      path.forEach((key) => {
        value = value && value[key] ? value[key] : null;
      });

      if (value) {
        element.textContent = value;
      }
    });
  </script>
</body>
</html>
