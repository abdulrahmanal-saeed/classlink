<?php
/**
 * Phase 19 Advanced Analytics helper.
 * Privacy-first public, marketing, revenue, content, referral, and learning analytics.
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/LearningEngagement.php';

function analytics_setting(string $key, $default = null)
{
    $s = db()->prepare('SELECT setting_value FROM settings WHERE setting_key = :key LIMIT 1');
    $s->execute([':key' => $key]);
    $value = $s->fetchColumn();
    return $value === false ? $default : $value;
}

function analytics_safe_event_names(): array
{
    return [
        'page_view','pricing_view','checkout_start','checkout_submit','payment_pending','student_form_submit','level_check_start','level_check_submit','booking_request','article_open','video_play','testimonial_submit',
        'login','homework_submit','scenario_submit','review_submit','flashcard_review','session_completed','badge_earned'
    ];
}

function analytics_event_category(string $eventName): string
{
    return match ($eventName) {
        'page_view','pricing_view','checkout_start','checkout_submit','payment_pending','student_form_submit','level_check_start','level_check_submit','booking_request' => 'public',
        'article_open','video_play','testimonial_submit' => 'content',
        'login','homework_submit','scenario_submit','review_submit','flashcard_review','session_completed','badge_earned' => 'learning',
        default => 'system',
    };
}

function analytics_visitor_id(): string
{
    if (empty($_COOKIE['hn_visitor_id'])) {
        $id = bin2hex(random_bytes(16));
        setcookie('hn_visitor_id', $id, time() + 31536000, '/', '', false, true);
        $_COOKIE['hn_visitor_id'] = $id;
    }
    return $_COOKIE['hn_visitor_id'];
}

function analytics_session_id(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) @session_start();
    if (empty($_SESSION['hn_analytics_session_id'])) $_SESSION['hn_analytics_session_id'] = bin2hex(random_bytes(12));
    return $_SESSION['hn_analytics_session_id'];
}

function analytics_ip_hash(): ?string
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    if (!$ip) return null;
    $salt = analytics_setting('analytics_ip_hash_salt', 'change-this-salt-in-production');
    return hash('sha256', $salt . '|' . $ip);
}

function analytics_device_type(): string
{
    $ua = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');
    if (str_contains($ua, 'mobile') || str_contains($ua, 'android') || str_contains($ua, 'iphone')) return 'mobile';
    if (str_contains($ua, 'tablet') || str_contains($ua, 'ipad')) return 'tablet';
    return 'desktop';
}

function analytics_track(string $eventName, array $data = [], ?int $userId = null): void
{
    if (analytics_setting('analytics_enabled', '1') !== '1') return;
    if (!in_array($eventName, analytics_safe_event_names(), true)) return;

    $pageUrl = $data['page_url'] ?? ($_SERVER['REQUEST_URI'] ?? null);
    $metadata = $data['metadata'] ?? [];
    unset($metadata['password'], $metadata['token'], $metadata['api_key']);

    db()->prepare(
        'INSERT INTO analytics_events
         (user_id, session_id, visitor_id, role, event_name, event_category, page_url, entity_type, entity_id, referrer_url, utm_source, utm_medium, utm_campaign, device_type, ip_hash, metadata)
         VALUES
         (:user, :session, :visitor, :role, :event, :category, :page_url, :entity_type, :entity_id, :referrer, :utm_source, :utm_medium, :utm_campaign, :device, :ip_hash, :metadata)'
    )->execute([
        ':user' => $userId,
        ':session' => analytics_session_id(),
        ':visitor' => analytics_visitor_id(),
        ':role' => $data['role'] ?? null,
        ':event' => $eventName,
        ':category' => analytics_event_category($eventName),
        ':page_url' => $pageUrl,
        ':entity_type' => $data['entity_type'] ?? null,
        ':entity_id' => isset($data['entity_id']) ? (string) $data['entity_id'] : null,
        ':referrer' => $_SERVER['HTTP_REFERER'] ?? null,
        ':utm_source' => $_GET['utm_source'] ?? ($data['utm_source'] ?? null),
        ':utm_medium' => $_GET['utm_medium'] ?? ($data['utm_medium'] ?? null),
        ':utm_campaign' => $_GET['utm_campaign'] ?? ($data['utm_campaign'] ?? null),
        ':device' => analytics_device_type(),
        ':ip_hash' => analytics_ip_hash(),
        ':metadata' => json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    ]);
}

function analytics_count_events(?string $eventName = null, ?string $category = null, int $days = 30): int
{
    $sql = 'SELECT COUNT(*) FROM analytics_events WHERE created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)';
    $params = [':days' => $days];
    if ($eventName) { $sql .= ' AND event_name = :event'; $params[':event'] = $eventName; }
    if ($category) { $sql .= ' AND event_category = :category'; $params[':category'] = $category; }
    $s = db()->prepare($sql);
    foreach ($params as $k => $v) $s->bindValue($k, $v, $k === ':days' ? PDO::PARAM_INT : PDO::PARAM_STR);
    $s->execute();
    return (int) $s->fetchColumn();
}

function analytics_unique_visitors(int $days = 30): int
{
    $s = db()->prepare('SELECT COUNT(DISTINCT visitor_id) FROM analytics_events WHERE visitor_id IS NOT NULL AND created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)');
    $s->bindValue(':days', $days, PDO::PARAM_INT);
    $s->execute();
    return (int) $s->fetchColumn();
}

function analytics_funnel(int $days = 30): array
{
    $steps = ['page_view','pricing_view','checkout_start','checkout_submit','payment_pending','student_form_submit','level_check_start','level_check_submit','booking_request'];
    $out = [];
    foreach ($steps as $step) $out[] = ['event' => $step, 'count' => analytics_count_events($step, null, $days)];
    return $out;
}

function analytics_payment_breakdown(): array
{
    return db()->query('SELECT status, COUNT(*) AS count, COALESCE(SUM(amount),0) AS amount FROM payment_records GROUP BY status ORDER BY count DESC')->fetchAll();
}

function analytics_revenue_by_plan(): array
{
    return db()->query('SELECT COALESCE(plans.name_en, "Unknown") AS plan_name, purchases.currency, COUNT(*) AS purchases_count, COALESCE(SUM(purchases.amount),0) AS revenue FROM purchases LEFT JOIN plans ON plans.id = purchases.plan_id WHERE purchases.status = "paid" GROUP BY plans.name_en, purchases.currency ORDER BY revenue DESC')->fetchAll();
}

function analytics_student_engagement(): array
{
    return db()->query('SELECT users.id, users.display_name, users.email, COUNT(learning_activity_logs.id) AS activity_count, MAX(learning_activity_logs.created_at) AS last_activity FROM users LEFT JOIN learning_activity_logs ON learning_activity_logs.student_user_id = users.id AND learning_activity_logs.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) WHERE users.role = "student" GROUP BY users.id ORDER BY activity_count DESC, last_activity DESC LIMIT 100')->fetchAll();
}

function analytics_low_activity_students(): array
{
    return db()->query('SELECT users.id, users.display_name, users.email, MAX(learning_activity_logs.created_at) AS last_activity, COUNT(learning_activity_logs.id) AS activity_count FROM users LEFT JOIN learning_activity_logs ON learning_activity_logs.student_user_id = users.id AND learning_activity_logs.created_at >= DATE_SUB(NOW(), INTERVAL 14 DAY) WHERE users.role = "student" GROUP BY users.id HAVING activity_count = 0 OR last_activity < DATE_SUB(NOW(), INTERVAL 7 DAY) ORDER BY last_activity ASC LIMIT 50')->fetchAll();
}

function analytics_content_performance(): array
{
    $articles = db()->query('SELECT entity_id, COUNT(*) AS opens FROM analytics_events WHERE event_name = "article_open" GROUP BY entity_id ORDER BY opens DESC LIMIT 50')->fetchAll();
    $videos = db()->query('SELECT entity_id, COUNT(*) AS plays FROM analytics_events WHERE event_name = "video_play" GROUP BY entity_id ORDER BY plays DESC LIMIT 50')->fetchAll();
    return ['articles' => $articles, 'videos' => $videos];
}

function analytics_referral_performance(): array
{
    return db()->query('SELECT referral_codes.code, users.display_name, referral_codes.landing_count, COUNT(referrals.id) AS referrals_count, SUM(CASE WHEN referrals.status = "reward_applied" THEN 1 ELSE 0 END) AS rewards_applied FROM referral_codes LEFT JOIN users ON users.id = referral_codes.owner_user_id LEFT JOIN referrals ON referrals.referral_code_id = referral_codes.id GROUP BY referral_codes.id ORDER BY referrals_count DESC, landing_count DESC LIMIT 100')->fetchAll();
}

function analytics_dashboard_summary(): array
{
    return [
        'visitors_30d' => analytics_unique_visitors(30),
        'page_views_30d' => analytics_count_events('page_view', null, 30),
        'checkout_starts_30d' => analytics_count_events('checkout_start', null, 30),
        'checkout_submits_30d' => analytics_count_events('checkout_submit', null, 30),
        'active_students_30d' => (int) db()->query('SELECT COUNT(DISTINCT student_user_id) FROM learning_activity_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)')->fetchColumn(),
        'homework_submits_30d' => (int) db()->query('SELECT COUNT(*) FROM homework_submissions WHERE submitted_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)')->fetchColumn(),
        'sessions_completed_30d' => (int) db()->query('SELECT COUNT(*) FROM lesson_sessions WHERE status = "completed" AND start_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)')->fetchColumn(),
        'paid_revenue_total' => (float) db()->query('SELECT COALESCE(SUM(amount),0) FROM purchases WHERE status = "paid"')->fetchColumn(),
    ];
}
