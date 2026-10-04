<?php
require_once __DIR__ . '/db.php';

function current_user(): ?array {
    if (!isset($_SESSION['user_id'])) return null;
    static $cache = null;
    if ($cache !== null) return $cache;
    $stmt = db()->prepare('SELECT id, name, email, avatar_color, is_admin, last_active_at FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $cache = $stmt->fetch() ?: null;
    if ($cache) {
        touch_activity((int) $cache['id']);
    }
    return $cache;
}

/**
 * Records that a user was just active, throttled so we're not writing
 * to the DB on every single request. The staleness check runs inside
 * MySQL (NOW() compared to NOW()) rather than comparing PHP's time()
 * against a stored MySQL timestamp — that avoids any bug from the web
 * server and DB server having different timezone settings, which would
 * otherwise make the throttle think every request is "stale."
 * This is the passive trigger — it fires on any page load. watch.php
 * additionally sends a periodic heartbeat (see heartbeat.php) since a
 * viewer can sit on one video for many minutes without a new page load.
 */
function touch_activity(int $userId): void {
    $stmt = db()->prepare(
        "UPDATE users SET last_active_at = NOW()
         WHERE id = ? AND (last_active_at IS NULL OR last_active_at < NOW() - INTERVAL 60 SECOND)"
    );
    $stmt->execute([$userId]);
}

function require_login(): void {
    if (!current_user()) {
        header('Location: ' . base_url('login.php?next=' . urlencode($_SERVER['REQUEST_URI'])));
        exit;
    }
}

function require_admin(): void {
    $u = current_user();
    if (!$u || !$u['is_admin']) {
        header('Location: ' . base_url('admin/login.php'));
        exit;
    }
}

function base_url(string $path = ''): string {
    return SITE_URL . '/' . ltrim($path, '/');
}

function login_user(int $userId): void {
    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;
}

function logout_user(): void {
    $_SESSION = [];
    session_destroy();
}

/**
 * Returns true if the given user currently has an active premium subscription.
 */
function has_active_subscription(int $userId): bool {
    $stmt = db()->prepare(
        "SELECT id FROM subscriptions
         WHERE user_id = ? AND status = 'active' AND expires_at > NOW()
         ORDER BY expires_at DESC LIMIT 1"
    );
    $stmt->execute([$userId]);
    return (bool) $stmt->fetch();
}

function get_active_subscription(int $userId): ?array {
    $stmt = db()->prepare(
        "SELECT * FROM subscriptions
         WHERE user_id = ? AND status = 'active' AND expires_at > NOW()
         ORDER BY expires_at DESC LIMIT 1"
    );
    $stmt->execute([$userId]);
    return $stmt->fetch() ?: null;
}

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_check(): void {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(403);
        die('Invalid request. Please refresh and try again.');
    }
}
