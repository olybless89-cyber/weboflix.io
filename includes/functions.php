<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

/**
 * Truncates text to roughly $length characters. Uses mb_* functions when
 * the mbstring extension is available (not guaranteed on every host),
 * falling back to plain substr() otherwise so this never fatals.
 */
function truncate_text(string $text, int $length = 110, string $suffix = '…'): string {
    $text = trim($text);
    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
        return mb_strlen($text) <= $length ? $text : mb_substr($text, 0, $length) . $suffix;
    }
    return strlen($text) <= $length ? $text : substr($text, 0, $length) . $suffix;
}

function h(?string $s): string {
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}

function slugify(string $text): string {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

function format_duration(int $seconds): string {
    $h = intdiv($seconds, 3600);
    $m = intdiv($seconds % 3600, 60);
    if ($h > 0) return sprintf('%dh %dm', $h, $m);
    return sprintf('%dm', max($m, 1));
}

function course_lesson_count(int $courseId): int {
    $stmt = db()->prepare(
        "SELECT COUNT(*) FROM lessons l
         JOIN modules m ON m.id = l.module_id
         WHERE m.course_id = ?"
    );
    $stmt->execute([$courseId]);
    return (int) $stmt->fetchColumn();
}

function course_total_duration(int $courseId): int {
    $stmt = db()->prepare(
        "SELECT COALESCE(SUM(l.duration_seconds),0) FROM lessons l
         JOIN modules m ON m.id = l.module_id
         WHERE m.course_id = ?"
    );
    $stmt->execute([$courseId]);
    return (int) $stmt->fetchColumn();
}

/**
 * Percentage (0-100) of a course a user has completed.
 */
function course_progress_percent(int $userId, int $courseId): int {
    $total = course_lesson_count($courseId);
    if ($total === 0) return 0;
    $stmt = db()->prepare(
        "SELECT COUNT(*) FROM lesson_progress
         WHERE user_id = ? AND course_id = ? AND completed = 1"
    );
    $stmt->execute([$userId, $courseId]);
    $done = (int) $stmt->fetchColumn();
    return (int) round(($done / $total) * 100);
}

/**
 * Determine whether a given user can access a specific module's lessons.
 */
function can_access_module(array $module, ?array $user): bool {
    if ($module['access_level'] === 'free') return true;
    if (!$user) return false;
    return has_active_subscription((int) $user['id']);
}

function get_last_watched_lesson(int $userId, int $courseId): ?array {
    $stmt = db()->prepare(
        "SELECT lp.*, l.title AS lesson_title, l.module_id
         FROM lesson_progress lp
         JOIN lessons l ON l.id = lp.lesson_id
         WHERE lp.user_id = ? AND lp.course_id = ?
         ORDER BY lp.updated_at DESC LIMIT 1"
    );
    $stmt->execute([$userId, $courseId]);
    return $stmt->fetch() ?: null;
}

/**
 * $secondsAgo should come from a MySQL TIMESTAMPDIFF(SECOND, col, NOW())
 * in the query, not computed in PHP — comparing PHP's time() against a
 * MySQL-generated timestamp string breaks the moment the web server and
 * DB server have different timezone settings (a very common hosting
 * setup), silently making everything look off by whatever that offset is.
 * $datetime is only used for the "more than a week ago" absolute-date fallback.
 */
function time_ago(?string $datetime, ?int $secondsAgo = null): string {
    if (!$datetime || $secondsAgo === null) return 'never';
    if ($secondsAgo < 60) return 'just now';
    if ($secondsAgo < 3600) return floor($secondsAgo / 60) . 'm ago';
    if ($secondsAgo < 86400) return floor($secondsAgo / 3600) . 'h ago';
    if ($secondsAgo < 604800) return floor($secondsAgo / 86400) . 'd ago';
    return date('M j, Y', strtotime($datetime));
}
function flash_set(string $key, string $msg): void {
    $_SESSION['flash'][$key] = $msg;
}

function flash_get(string $key): ?string {
    if (!empty($_SESSION['flash'][$key])) {
        $msg = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
    return null;
}
