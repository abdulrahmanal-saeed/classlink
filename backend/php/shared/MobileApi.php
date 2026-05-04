<?php
/**
 * Phase 22 Flutter Mobile API helper.
 * Uses bearer tokens for mobile without changing web PHP session auth.
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/AuditLogger.php';

function mobile_setting(string $key, $default = null)
{
    $s = db()->prepare('SELECT setting_value FROM settings WHERE setting_key = :key LIMIT 1');
    $s->execute([':key' => $key]);
    $value = $s->fetchColumn();
    return $value === false ? $default : $value;
}

function mobile_enabled(): bool
{
    return mobile_setting('mobile_api_enabled', '1') === '1';
}

function mobile_json($data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function mobile_input(): array
{
    $raw = file_get_contents('php://input') ?: '';
    $json = json_decode($raw, true);
    return is_array($json) ? $json : $_POST;
}

function mobile_create_token(int $userId, string $platform = 'unknown', ?string $deviceLabel = null): string
{
    $ttlDays = (int) mobile_setting('mobile_token_ttl_days', 30);
    $token = bin2hex(random_bytes(32));
    $hash = hash('sha256', $token);
    $platform = in_array($platform, ['flutter_android','flutter_ios','flutter_web','unknown'], true) ? $platform : 'unknown';
    db()->prepare('INSERT INTO mobile_auth_tokens (user_id, token_hash, platform, device_label, expires_at) VALUES (:user, :hash, :platform, :label, DATE_ADD(NOW(), INTERVAL :days DAY))')
        ->execute([':user' => $userId, ':hash' => $hash, ':platform' => $platform, ':label' => $deviceLabel, ':days' => $ttlDays]);
    return $token;
}

function mobile_login(string $email, string $password, string $platform = 'unknown', ?string $deviceLabel = null): array
{
    if (!mobile_enabled()) throw new RuntimeException('Mobile API is disabled.');
    $s = db()->prepare('SELECT id, email, password_hash, role, status, display_name FROM users WHERE email = :email LIMIT 1');
    $s->execute([':email' => strtolower(trim($email))]);
    $user = $s->fetch();
    if (!$user || $user['status'] !== 'active' || !password_verify($password, $user['password_hash'])) {
        audit_log($user ? (int)$user['id'] : null, 'mobile_login_failed', 'user', $email);
        throw new RuntimeException('Invalid email or password.');
    }
    $token = mobile_create_token((int)$user['id'], $platform, $deviceLabel);
    audit_log((int)$user['id'], 'mobile_login_success', 'user', (string)$user['id'], ['platform' => $platform]);
    unset($user['password_hash']);
    return ['token' => $token, 'user' => $user];
}

function mobile_bearer_token(): ?string
{
    $header = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
    if (preg_match('/Bearer\s+(.*)$/i', $header, $m)) return trim($m[1]);
    return $_GET['token'] ?? null;
}

function mobile_user(): ?array
{
    $token = mobile_bearer_token();
    if (!$token) return null;
    $hash = hash('sha256', $token);
    $s = db()->prepare('SELECT mat.id AS token_id, u.id, u.email, u.role, u.status, u.display_name FROM mobile_auth_tokens mat JOIN users u ON u.id = mat.user_id WHERE mat.token_hash = :hash AND mat.status = "active" AND u.status = "active" AND (mat.expires_at IS NULL OR mat.expires_at > NOW()) LIMIT 1');
    $s->execute([':hash' => $hash]);
    $user = $s->fetch();
    if (!$user) return null;
    db()->prepare('UPDATE mobile_auth_tokens SET last_used_at = NOW() WHERE id = :id')->execute([':id' => (int)$user['token_id']]);
    return $user;
}

function mobile_require_user(): array
{
    $user = mobile_user();
    if (!$user) mobile_json(['ok' => false, 'error' => 'Unauthorized.'], 401);
    return $user;
}

function mobile_logout(): void
{
    $token = mobile_bearer_token();
    if (!$token) return;
    db()->prepare('UPDATE mobile_auth_tokens SET status = "revoked", revoked_at = NOW() WHERE token_hash = :hash')
        ->execute([':hash' => hash('sha256', $token)]);
}

function mobile_owner_dashboard(int $userId): array
{
    $todayLessons = db()->query('SELECT ls.id, ls.student_user_id, ls.start_at, ls.end_at, ls.status, u.display_name AS student_name FROM lesson_sessions ls LEFT JOIN users u ON u.id = ls.student_user_id WHERE DATE(ls.start_at) = CURDATE() ORDER BY ls.start_at ASC LIMIT 50')->fetchAll();
    $homework = db()->query('SELECT COUNT(*) FROM homework_submissions WHERE status IN ("submitted","needs_correction")')->fetchColumn();
    $scenarios = db()->query('SELECT COUNT(*) FROM scenario_submissions WHERE status IN ("submitted","needs_review")')->fetchColumn();
    return ['today_lessons' => $todayLessons, 'homework_submissions_pending' => (int)$homework, 'scenario_submissions_pending' => (int)$scenarios];
}

function mobile_student_dashboard(int $userId): array
{
    $lesson = db()->prepare('SELECT * FROM lesson_sessions WHERE student_user_id = :student AND status IN ("planned","confirmed") AND start_at >= NOW() ORDER BY start_at ASC LIMIT 1');
    $lesson->execute([':student' => $userId]);
    $balance = db()->prepare('SELECT COALESCE(SUM(remaining_credits),0) FROM lesson_packages WHERE student_user_id = :student AND status = "active"');
    $balance->execute([':student' => $userId]);
    $homework = db()->prepare('SELECT id, title, due_at, status FROM homeworks WHERE student_user_id = :student AND status = "published" ORDER BY due_at ASC LIMIT 20');
    $homework->execute([':student' => $userId]);
    return ['upcoming_lesson' => $lesson->fetch() ?: null, 'balance' => (float)$balance->fetchColumn(), 'homework' => $homework->fetchAll()];
}

function mobile_parent_dashboard(int $userId): array
{
    $children = db()->prepare('SELECT pcl.student_user_id AS id, u.display_name FROM parent_child_links pcl JOIN users u ON u.id = pcl.student_user_id WHERE pcl.parent_user_id = :parent');
    $children->execute([':parent' => $userId]);
    return ['children' => $children->fetchAll()];
}

function mobile_dashboard(array $user): array
{
    return match ($user['role']) {
        'owner_teacher' => mobile_owner_dashboard((int)$user['id']),
        'student' => mobile_student_dashboard((int)$user['id']),
        'parent' => mobile_parent_dashboard((int)$user['id']),
        default => [],
    };
}
