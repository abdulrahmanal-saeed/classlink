<?php
/**
 * Shared dashboard shell.
 *
 * Phase 23 adds central localization, persistent language preference,
 * Arabic RTL and English LTR support, and a language switcher.
 */

require_once __DIR__ . '/../../../backend/php/shared/Localization.php';

function render_dashboard_shell(array $user, string $title, string $content): void
{
    $lang = l10n_current_language($user);
    $isArabic = l10n_is_arabic($lang);

    $items = [
        'owner_teacher' => [
            ['/owner/dashboard', 'Owner Dashboard', 'لوحة المالك'],
            ['/owner/media-buyers', 'Media Buyers', 'شركاء التسويق'],
            ['/owner/media-buyers/new', 'Create Media Buyer', 'إنشاء شريك تسويق'],
            ['/owner/media-commissions', 'Media Commissions', 'عمولات التسويق'],
            ['/owner/materials', 'Materials Library', 'مكتبة المواد'],
            ['/owner/materials/new', 'New Material', 'مادة جديدة'],
            ['/owner/materials/categories', 'Material Categories', 'تصنيفات المواد'],
            ['/owner/materials/analytics', 'Material Analytics', 'تحليلات المواد'],
            ['/owner/testimonials', 'Testimonials', 'التقييمات'],
            ['/owner/testimonials/pending', 'Pending Testimonials', 'تقييمات قيد المراجعة'],
            ['/owner/settings/testimonials', 'Testimonial Settings', 'إعدادات التقييمات'],
            ['/owner/jobs', 'Scheduled Jobs', 'المهام المجدولة'],
            ['/owner/jobs/logs', 'Job Logs', 'سجلات المهام'],
            ['/owner/settings/cron', 'Cron Settings', 'إعدادات كرون'],
            ['/owner/analytics', 'Analytics', 'التحليلات'],
            ['/owner/analytics/marketing', 'Marketing Analytics', 'تحليلات التسويق'],
            ['/owner/analytics/students', 'Student Analytics', 'تحليلات الطلاب'],
            ['/owner/analytics/revenue', 'Revenue Analytics', 'تحليلات الإيرادات'],
            ['/owner/analytics/content', 'Content Analytics', 'تحليلات المحتوى'],
            ['/owner/referrals', 'Referrals', 'الترشيحات'],
            ['/owner/settings/referrals', 'Referral Settings', 'إعدادات الترشيحات'],
            ['/owner/communication', 'Communication Center', 'مركز التواصل'],
            ['/owner/settings/notifications', 'Notification Settings', 'إعدادات الإشعارات'],
            ['/owner/settings/email-templates', 'Email Templates', 'قوالب البريد'],
            ['/owner/settings/whatsapp-templates', 'WhatsApp Templates', 'قوالب واتساب'],
            ['/owner/email-logs', 'Email Logs', 'سجلات البريد'],
            ['/owner/ai', 'AI Tools', 'أدوات الذكاء الاصطناعي'],
            ['/owner/weekly-summaries', 'Weekly Summaries', 'الملخصات الأسبوعية'],
            ['/owner/calendar', 'Calendar', 'التقويم'],
            ['/owner/availability', 'Availability', 'أوقات التوفر'],
            ['/owner/bookings', 'Bookings', 'الحجوزات'],
            ['/owner/payments', 'Payments', 'المدفوعات'],
            ['/owner/onboarding', 'Onboarding', 'المتابعة بعد الدفع'],
            ['/owner/academy-briefs', 'Academy Briefs', 'طلبات الأكاديميات'],
            ['/owner/packages', 'Packages & Credits', 'الباقات والأرصدة'],
            ['/owner/students', 'Students', 'الطلاب'],
            ['/owner/parents', 'Parents', 'أولياء الأمور'],
            ['/owner/homework', 'Homework', 'الواجبات'],
            ['/owner/scenarios', 'Scenarios', 'المواقف الكلامية'],
            ['/owner/reviews', 'Reviews', 'المراجعات'],
            ['/owner/badges/settings', 'Badge Settings', 'إعدادات الشارات'],
            ['/owner/notifications', 'Notifications', 'الإشعارات'],
            ['/owner/level-checks', 'Paid Level Checks', 'اختبارات المستوى المدفوعة'],
            ['/owner/free-level-test/attempts', 'Free Level Tests', 'اختبارات المستوى المجانية'],
            ['/owner/free-level-test/settings', 'Free Test Settings', 'إعدادات الاختبار المجاني'],
            ['/owner/settings', 'Settings Center', 'مركز الإعدادات'],
            ['/owner/settings/public-website', 'Public Website Settings', 'إعدادات الموقع العام'],
            ['/owner/cms/articles', 'CMS Articles', 'إدارة المقالات'],
            ['/owner/cms/articles/generate', 'Generate Article', 'توليد مقال'],
            ['/owner/cms/videos', 'CMS Videos', 'إدارة الفيديوهات'],
            ['/owner/cms/testimonials', 'CMS Testimonials', 'إدارة التقييمات'],
            ['/owner/ai/logs', 'AI Logs', 'سجلات الذكاء الاصطناعي'],
            ['/owner/audit-log', 'Audit Log', 'سجل المراجعة'],
            ['/owner/dev/seed-data', 'Dev Seed Data', 'بيانات تجريبية'],
        ],
        'student' => [
            ['/student/dashboard', 'Dashboard', 'لوحة الطالب'],
            ['/student/materials', 'Materials', 'المواد التعليمية'],
            ['/student/testimonial', 'Leave a Testimonial', 'اترك تقييمًا'],
            ['/student/profile', 'Profile', 'الملف الشخصي'],
            ['/student/progress', 'Progress', 'التقدم'],
            ['/student/flashcards', 'Flashcards', 'بطاقات المراجعة'],
            ['/student/badges', 'Badges', 'الشارات'],
            ['/student/book', 'Book Lesson', 'حجز حصة'],
            ['/student/lessons', 'Lessons', 'حصصي'],
            ['/student/balance', 'Balance', 'رصيدي'],
            ['/student/homework', 'Homework', 'الواجبات'],
            ['/student/scenarios', 'Scenarios', 'المواقف الكلامية'],
            ['/student/reviews', 'Reviews', 'المراجعات'],
            ['/student/practice-words', 'Practice Words', 'كلمات للمراجعة'],
            ['/student/session-notes', 'Session Notes', 'ملاحظات الحصص'],
            ['/student/notifications', 'Notifications', 'الإشعارات'],
            ['/student/referrals', 'Referrals', 'الترشيحات'],
        ],
        'parent' => [
            ['/parent/dashboard', 'Dashboard', 'لوحة ولي الأمر'],
            ['/parent/testimonial', 'Leave Parent Testimonial', 'اترك تقييم ولي أمر'],
            ['/parent/children', 'Children', 'الأطفال'],
            ['/parent/book', 'Book for Child', 'حجز للطفل'],
            ['/parent/referrals', 'Referrals', 'الترشيحات'],
            ['/parent/notifications', 'Notifications', 'الإشعارات'],
            ['/parent/contact', 'Contact Teacher', 'التواصل مع المعلم'],
        ],
        'academy_partner' => [
            ['/academy/dashboard', 'Academy Dashboard', 'لوحة شريك الأكاديمية'],
            ['/academy/briefs', 'Student Briefs', 'ملفات الطلاب'],
            ['/academy/briefs/new', 'Submit Brief', 'إرسال ملف طالب'],
        ],
        'media_buyer' => [
            ['/media/dashboard', 'Media Dashboard', 'لوحة التسويق'],
            ['/media/campaigns', 'Campaigns', 'الحملات'],
            ['/media/links', 'Tracking Links', 'روابط التتبع'],
            ['/media/orders', 'Attributed Orders', 'الطلبات المنسوبة'],
            ['/media/commissions', 'Commissions', 'العمولات'],
            ['/media/payouts', 'Payouts', 'المدفوعات'],
            ['/media/settings', 'Partner Settings', 'إعدادات الشريك'],
        ],
    ];

    $currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $localizedTitle = $title;

    ?>
    <!doctype html>
    <html lang="<?= htmlspecialchars($lang, ENT_QUOTES, 'UTF-8') ?>" dir="<?= l10n_dir($lang) ?>">
    <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <title><?= htmlspecialchars($localizedTitle, ENT_QUOTES, 'UTF-8') ?> | <?= htmlspecialchars(__('brand', $lang), ENT_QUOTES, 'UTF-8') ?></title>
      <meta name="robots" content="noindex, nofollow">
      <link rel="preconnect" href="https://fonts.googleapis.com">
      <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
      <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap<?= $isArabic ? '.rtl' : '' ?>.min.css" rel="stylesheet">
      <link href="/assets/css/app.css" rel="stylesheet">
      <script src="/assets/js/i18n/en.js" defer></script>
      <script src="/assets/js/i18n/ar.js" defer></script>
      <script src="/assets/js/i18n/apply-i18n.js" defer></script>
    </head>
    <body class="<?= $isArabic ? 'is-rtl' : 'is-ltr' ?>">
      <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4 gap-3 flex-wrap">
          <div class="d-flex align-items-center gap-3">
            <div class="brand-mark">ض</div>
            <div>
              <div class="fw-bold"><?= htmlspecialchars(__('brand', $lang), ENT_QUOTES, 'UTF-8') ?></div>
              <div class="text-muted small"><?= htmlspecialchars(__('role_based_dashboard', $lang), ENT_QUOTES, 'UTF-8') ?></div>
            </div>
          </div>
          <div class="d-flex gap-2 align-items-center flex-wrap">
            <?= l10n_language_switcher($lang) ?>
            <div class="dropdown">
              <button class="btn btn-outline-brand dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <?= htmlspecialchars($user['display_name'], ENT_QUOTES, 'UTF-8') ?>
              </button>
              <ul class="dropdown-menu">
                <li><span class="dropdown-item-text small text-muted ltr-safe"><?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?></span></li>
                <li><span class="dropdown-item-text small"><?= htmlspecialchars(__('role', $lang), ENT_QUOTES, 'UTF-8') ?>: <?= htmlspecialchars($user['role'], ENT_QUOTES, 'UTF-8') ?></span></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="/logout"><?= htmlspecialchars(__('logout', $lang), ENT_QUOTES, 'UTF-8') ?></a></li>
              </ul>
            </div>
          </div>
        </div>

        <div class="row g-4">
          <aside class="col-lg-3">
            <div class="foundation-card p-3">
              <div class="fw-bold mb-3"><?= htmlspecialchars(__('navigation', $lang), ENT_QUOTES, 'UTF-8') ?></div>
              <div class="list-group list-group-flush">
                <?php foreach (($items[$user['role']] ?? []) as $item): ?>
                  <?php $isActive = $currentPath === $item[0] || str_starts_with($currentPath, $item[0] . '/'); ?>
                  <a class="list-group-item list-group-item-action <?= $isActive ? 'active' : '' ?>" href="<?= $item[0] ?>?lang=<?= htmlspecialchars($lang, ENT_QUOTES, 'UTF-8') ?>">
                    <?= $isArabic ? $item[2] : $item[1] ?>
                  </a>
                <?php endforeach; ?>
              </div>
            </div>
          </aside>
          <main class="col-lg-9">
            <div class="foundation-card">
              <div class="badge text-bg-light border mb-3"><?= htmlspecialchars(__('phase', $lang), ENT_QUOTES, 'UTF-8') ?></div>
              <h1 class="hero-title h2 mb-3"><?= htmlspecialchars($localizedTitle, ENT_QUOTES, 'UTF-8') ?></h1>
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
