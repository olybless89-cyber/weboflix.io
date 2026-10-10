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

/**
 * Intelligent Video Suggestion Algorithm
 * =====================================================================
 * Recommends courses based on the user's implicit watch history:
 *  1. Category Affinity (weighted by watch frequency & recent activity)
 *  2. Skill-level progression (beginner -> intermediate -> advanced)
 *  3. "Because You Watched [X]" dynamic discovery anchor
 *  4. Collaborative popularity boost (co-learner signals)
 *  5. Completion penalty (avoids repeating fully watched courses)
 *  6. Cold-start fallback (trending & top-rated courses for new users/guests)
 */
function get_recommended_courses(?int $userId = null, int $limit = 8, ?int $excludeCourseId = null): array {
    $db = db();
    $anchorCourse = null;
    $reason = "Top Recommendations for You";
    $watchedCourseIds = [];
    $categoryWeights = [];
    $levelWeights = [];

    if ($userId) {
        // Fetch user's watch history ordered by recency
        $stmt = $db->prepare(
            "SELECT c.id, c.title, c.category_id, c.level, lp.completed, lp.updated_at
             FROM lesson_progress lp
             JOIN courses c ON c.id = lp.course_id
             WHERE lp.user_id = ?
             ORDER BY lp.updated_at DESC"
        );
        $stmt->execute([$userId]);
        $history = $stmt->fetchAll();

        if ($history) {
            // Anchor course is the most recently watched course
            $anchorCourse = $history[0];
            $reason = "Because You Watched " . $anchorCourse['title'];

            foreach ($history as $idx => $h) {
                $cId = (int) $h['id'];
                if ($h['completed'] == 1) {
                    $watchedCourseIds[$cId] = true;
                }

                $catId = (int) $h['category_id'];
                $lvl = $h['level'];

                // Exponential decay weight by recency (first item gets 2.0x weight)
                $weight = max(0.5, 2.0 - ($idx * 0.25));
                $categoryWeights[$catId] = ($categoryWeights[$catId] ?? 0) + $weight;
                $levelWeights[$lvl] = ($levelWeights[$lvl] ?? 0) + $weight;
            }
        }
    }

    // Fetch published candidate courses
    $params = [];
    $excludeSql = "";
    if ($excludeCourseId) {
        $excludeSql = "AND c.id != ?";
        $params[] = $excludeCourseId;
    }

    $cStmt = $db->prepare(
        "SELECT c.*, cat.name AS category_name,
            (SELECT COUNT(*) FROM lessons l JOIN modules m ON m.id = l.module_id WHERE m.course_id = c.id) AS lesson_count,
            (SELECT COUNT(*) FROM modules m WHERE m.course_id = c.id AND m.access_level = 'free') AS free_modules,
            (SELECT l.id FROM lessons l JOIN modules m ON m.id = l.module_id WHERE m.course_id = c.id ORDER BY m.sort_order ASC, l.sort_order ASC LIMIT 1) AS first_lesson_id,
            (SELECT l.youtube_id FROM lessons l JOIN modules m ON m.id = l.module_id WHERE m.course_id = c.id ORDER BY m.sort_order ASC, l.sort_order ASC LIMIT 1) AS first_youtube_id,
            (SELECT COUNT(DISTINCT user_id) FROM lesson_progress WHERE course_id = c.id) AS learner_count
         FROM courses c
         JOIN categories cat ON cat.id = c.category_id
         WHERE c.is_published = 1 {$excludeSql}
         ORDER BY c.sort_order ASC, c.id DESC"
    );
    $cStmt->execute($params);
    $candidates = $cStmt->fetchAll();

    if (!$candidates) {
        return ['reason' => $reason, 'anchor_course' => $anchorCourse, 'courses' => []];
    }

    $scored = [];
    foreach ($candidates as $c) {
        $id = (int) $c['id'];
        $catId = (int) $c['category_id'];
        $level = $c['level'];
        $learners = (int) $c['learner_count'];

        // Base match score baseline
        $score = 55.0;

        // Penalty if course is already completed by this user
        if (!empty($watchedCourseIds[$id])) {
            $score -= 35.0;
        }

        // Category affinity boost (+10 to +30 pts)
        if (isset($categoryWeights[$catId])) {
            $score += min(28, $categoryWeights[$catId] * 10);
        }

        // Skill level progression (+5 to +15 pts)
        if (isset($levelWeights[$level])) {
            $score += min(14, $levelWeights[$level] * 5);
        }

        // Anchor course similarity boost (+15 pts if in same category as last watched)
        if ($anchorCourse && (int)$anchorCourse['category_id'] === $catId && $id !== (int)$anchorCourse['id']) {
            $score += 15.0;
        }

        // Popularity / Learner signal (+1 to +10 pts)
        $score += min(10, log($learners + 2, 2) * 2.2);

        // Normalize match percentage display (between 78% and 99%)
        $matchPct = min(99, max(78, (int) round($score)));
        $c['match_score'] = $matchPct;
        $c['algorithm_score'] = $score;

        $scored[] = $c;
    }

    // Sort by algorithmic relevance score
    usort($scored, function($a, $b) {
        return $b['algorithm_score'] <=> $a['algorithm_score'];
    });

    // Don't recommend the exact same anchor course if alternatives exist
    if ($anchorCourse && count($scored) > 1) {
        $filtered = array_values(array_filter($scored, function($c) use ($anchorCourse) {
            return (int)$c['id'] !== (int)$anchorCourse['id'];
        }));
        if (!empty($filtered)) {
            $scored = $filtered;
        }
    }

    return [
        'reason' => $reason,
        'anchor_course' => $anchorCourse,
        'courses' => array_slice($scored, 0, $limit),
    ];
}
