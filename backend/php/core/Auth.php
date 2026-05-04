<?php
/**
 * Authentication helper for Phase 1.
 *
 * Phase 34 hardens sessions, login error behavior, and login rate limiting.
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../shared/AuditLogger.php';
require_once __DIR__ . '/../shared/Security.php';

function auth_start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $isHttps = security_is_https();

    session_name(getenv('SESSION_NAME') ?: 'hn_academy_session');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_httponly', '1');
    if ($isHttps) ini_set('session.cookie_secure', '1');

    session_start();
}

function auth_user(): ?array
{
    auth_start_session();

    if (empty($_SESSION['user_id'])) {
        return null;
    }

    $statement = db()->prepare(
        'SELECT id, email, role, status, display_name FROM users WHERE id = :id AND status = "active" LIMIT 1'
    );
    $statement->execute([':id' => (int) $_SESSION['user_id']]);
    $user = $statement->fetch();

    return $user ?: null;
}

function auth_attempt(string $email, string $password): array
{
    auth_start_session();

    $normalizedEmail = strtolower(trim($email));
    $fingerprint = security_client_fingerprint();
    $rateIdentity = $normalizedEmail . '|' . $fingerprint;

    if (!security_rate_limit_check('login', $rateIdentity, (int)(getenv('LOGIN_MAX_ATTEMPTS') ?: 8), (int)(getenv('LOGIN_WINDOW_SECONDS') ?: 900))) {
        audit_log(null, 'login_rate_limited', 'user', $normalizedEmail);
        return [false, null, 'Invalid email or password.'];
    }

    $statement = db()->prepare(
        'SELECT id, email, password_hash, role, status, display_name FROM users WHERE email = :email LIMIT 1'
    );
    $statement->execute([':email' => $normalizedEmail]);
    $user = $statement->fetch();

    if (!$user || $user['status'] !== 'active' || !password_verify($password, $user['password_hash'])) {
        security_rate_limit_record('login', $rateIdentity, (int)(getenv('LOGIN_WINDOW_SECONDS') ?: 900));
        audit_log($user ? (int) $user['id'] : null, 'login_failed', 'user', $normalizedEmail);
        return [false, null, 'Invalid email or password.'];
    }

    security_rate_limit_clear('login', $rateIdentity);
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['login_at'] = time();

    audit_log((int) $user['id'], 'login_success', 'user', (string) $user['id']);

    unset($user['password_hash']);
    return [true, $user, 'Login successful.'];
}

function auth_logout(): void
{
    auth_start_session();

    $userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;

    if ($userId) {
        audit_log($userId, 'logout_success', 'user', (string) $userId);
    }

    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    session_destroy();
}

function role_home_path(string $role): string
{
    return match ($role) {
        'owner_teacher' => '/owner/dashboard',
        'student' => '/student/dashboard',
        'parent' => '/parent/dashboard',
        'academy_partner' => '/academy/dashboard',
        'media_buyer' => '/media/dashboard',
        default => '/unauthorized',
    };
}

function require_role(string $role): array
{
    security_headers(false);
    $user = auth_user();

    if (!$user) {
        header('Location: /login');
        exit;
    }

    if ($user['role'] !== $role) {
        audit_log((int) $user['id'], 'unauthorized_access', 'route', $_SERVER['REQUEST_URI'] ?? null, [
            'required_role' => $role,
            'actual_role' => $user['role'],
        ]);
        header('Location: /unauthorized');
        exit;
    }

    if ($role === 'media_buyer') {
        $agreementHelper = __DIR__ . '/../shared/MediaBuyerAgreement.php';
        if (file_exists($agreementHelper)) {
            require_once $agreementHelper;
            media_agreement_redirect_if_needed($user);
        }
    }

    return $user;
}

function require_any_role(array $roles): array
{
    security_headers(false);
    $user = auth_user();
    if (!$user) {
        header('Location: /login');
        exit;
    }
    if (!in_array($user['role'], $roles, true)) {
        audit_log((int)$user['id'], 'unauthorized_access', 'route', $_SERVER['REQUEST_URI'] ?? null, [
            'required_any_role' => $roles,
            'actual_role' => $user['role'],
        ]);
        header('Location: /unauthorized');
        exit;
    }
    return $user;
}
