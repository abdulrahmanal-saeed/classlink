<?php
/**
 * Phase 23 Localization helper.
 * Central Arabic/English language handling, RTL/LTR support, and persistent preference.
 */

require_once __DIR__ . '/../config/db.php';

function l10n_supported_languages(): array
{
    return ['ar', 'en'];
}

function l10n_default_language(): string
{
    $s = db()->prepare('SELECT setting_value FROM settings WHERE setting_key = "platform_default_language" LIMIT 1');
    $s->execute();
    $value = $s->fetchColumn() ?: 'en';
    return in_array($value, l10n_supported_languages(), true) ? $value : 'en';
}

function l10n_is_arabic(string $lang): bool
{
    return $lang === 'ar';
}

function l10n_dir(string $lang): string
{
    return l10n_is_arabic($lang) ? 'rtl' : 'ltr';
}

function l10n_normalize(?string $lang): string
{
    $lang = strtolower(trim((string) $lang));
    if (str_starts_with($lang, 'ar')) return 'ar';
    if (str_starts_with($lang, 'en')) return 'en';
    return l10n_default_language();
}

function l10n_current_language(?array $user = null): string
{
    if (!empty($_GET['lang'])) {
        return l10n_set_language(l10n_normalize($_GET['lang']), $user);
    }
    if ($user && !empty($user['preferred_language'])) return l10n_normalize($user['preferred_language']);
    if (!empty($_COOKIE['hn_lang'])) return l10n_normalize($_COOKIE['hn_lang']);
    $accept = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
    return l10n_normalize($accept ?: l10n_default_language());
}

function l10n_set_language(string $lang, ?array $user = null): string
{
    $lang = l10n_normalize($lang);
    $days = 365;
    try {
        $s = db()->prepare('SELECT setting_value FROM settings WHERE setting_key = "platform_language_cookie_days" LIMIT 1');
        $s->execute();
        $days = (int) ($s->fetchColumn() ?: 365);
    } catch (Throwable $e) {}
    setcookie('hn_lang', $lang, time() + ($days * 86400), '/', '', false, false);
    $_COOKIE['hn_lang'] = $lang;
    if ($user && !empty($user['id'])) {
        try {
            db()->prepare('UPDATE users SET preferred_language = :lang WHERE id = :id')->execute([':lang' => $lang, ':id' => (int) $user['id']]);
        } catch (Throwable $e) {}
    }
    return $lang;
}

function l10n_messages(): array
{
    return [
        'en' => [
            'brand' => 'Habiba Nabil Arabic Academy',
            'role_based_dashboard' => 'Role-based dashboard',
            'navigation' => 'Navigation',
            'language' => 'Language',
            'arabic' => 'Arabic',
            'english' => 'English',
            'logout' => 'Logout',
            'role' => 'Role',
            'phase' => 'Localization QA',
            'save' => 'Save',
            'cancel' => 'Cancel',
            'edit' => 'Edit',
            'view' => 'View',
            'open' => 'Open',
            'submit' => 'Submit',
            'continue' => 'Continue',
            'back' => 'Back',
            'search' => 'Search',
            'filter' => 'Filter',
            'status' => 'Status',
            'actions' => 'Actions',
            'date' => 'Date',
            'student' => 'Student',
            'parent' => 'Parent',
            'owner' => 'Owner',
            'academy_partner' => 'Academy Partner',
            'dashboard' => 'Dashboard',
            'settings' => 'Settings',
            'notifications' => 'Notifications',
            'homework' => 'Homework',
            'scenarios' => 'Speaking Scenarios',
            'reviews' => 'Reviews',
            'materials' => 'Materials',
            'analytics' => 'Analytics',
            'payments' => 'Payments',
            'bookings' => 'Bookings',
            'calendar' => 'Calendar',
            'students' => 'Students',
            'parents' => 'Parents',
            'referrals' => 'Referrals',
            'communication' => 'Communication Center',
            'empty_state' => 'Nothing to show yet.',
            'loading' => 'Loading...',
            'error' => 'Something went wrong. Please try again.',
        ],
        'ar' => [
            'brand' => 'أكاديمية حبيبة نبيل لتعليم العربية',
            'role_based_dashboard' => 'لوحة تحكم حسب الدور',
            'navigation' => 'التنقل',
            'language' => 'اللغة',
            'arabic' => 'العربية',
            'english' => 'الإنجليزية',
            'logout' => 'تسجيل الخروج',
            'role' => 'الدور',
            'phase' => 'مراجعة اللغة والتعريب',
            'save' => 'حفظ',
            'cancel' => 'إلغاء',
            'edit' => 'تعديل',
            'view' => 'عرض',
            'open' => 'فتح',
            'submit' => 'إرسال',
            'continue' => 'متابعة',
            'back' => 'رجوع',
            'search' => 'بحث',
            'filter' => 'تصفية',
            'status' => 'الحالة',
            'actions' => 'الإجراءات',
            'date' => 'التاريخ',
            'student' => 'طالب',
            'parent' => 'ولي الأمر',
            'owner' => 'المالك',
            'academy_partner' => 'شريك الأكاديمية',
            'dashboard' => 'لوحة التحكم',
            'settings' => 'الإعدادات',
            'notifications' => 'الإشعارات',
            'homework' => 'الواجبات',
            'scenarios' => 'المواقف الكلامية',
            'reviews' => 'المراجعات',
            'materials' => 'المواد التعليمية',
            'analytics' => 'التحليلات',
            'payments' => 'المدفوعات',
            'bookings' => 'الحجوزات',
            'calendar' => 'التقويم',
            'students' => 'الطلاب',
            'parents' => 'أولياء الأمور',
            'referrals' => 'الترشيحات',
            'communication' => 'مركز التواصل',
            'empty_state' => 'لا توجد بيانات لعرضها حاليًا.',
            'loading' => 'جاري التحميل...',
            'error' => 'حدث خطأ. حاول مرة أخرى.',
        ],
    ];
}

function __(string $key, ?string $lang = null): string
{
    $lang = $lang ?: l10n_current_language();
    $messages = l10n_messages();
    return $messages[$lang][$key] ?? $messages['en'][$key] ?? $key;
}

function l10n_url_with_language(string $lang): string
{
    $lang = l10n_normalize($lang);
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $query = $_GET;
    $query['lang'] = $lang;
    return $path . '?' . http_build_query($query);
}

function l10n_language_switcher(string $lang): string
{
    $arActive = $lang === 'ar' ? 'active' : '';
    $enActive = $lang === 'en' ? 'active' : '';
    $arUrl = htmlspecialchars(l10n_url_with_language('ar'), ENT_QUOTES, 'UTF-8');
    $enUrl = htmlspecialchars(l10n_url_with_language('en'), ENT_QUOTES, 'UTF-8');
    return '<div class="btn-group btn-group-sm" role="group" aria-label="Language switcher">'
        . '<a class="btn btn-outline-brand ' . $enActive . '" href="' . $enUrl . '">EN</a>'
        . '<a class="btn btn-outline-brand ' . $arActive . '" href="' . $arUrl . '">AR</a>'
        . '</div>';
}
