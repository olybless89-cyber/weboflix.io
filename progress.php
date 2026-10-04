<?php
require_once __DIR__ . '/includes/functions.php';
header('Content-Type: application/json');

$user = current_user();
if (!$user) { http_response_code(401); echo json_encode(['ok' => false]); exit; }

$lessonId = (int) ($_POST['lesson_id'] ?? 0);
$courseId = (int) ($_POST['course_id'] ?? 0);
$action = $_POST['action'] ?? '';

if (!$lessonId || !$courseId) { http_response_code(400); echo json_encode(['ok' => false]); exit; }

// Confirm the lesson actually belongs to the course, and the user can access it
$check = db()->prepare(
    "SELECT m.access_level FROM lessons l JOIN modules m ON m.id = l.module_id
     WHERE l.id = ? AND m.course_id = ?"
);
$check->execute([$lessonId, $courseId]);
$mod = $check->fetch();
if (!$mod || !can_access_module($mod, $user)) {
    http_response_code(403);
    echo json_encode(['ok' => false]);
    exit;
}

if ($action === 'complete') {
    $stmt = db()->prepare(
        "INSERT INTO lesson_progress (user_id, lesson_id, course_id, completed)
         VALUES (?, ?, ?, 1)
         ON DUPLICATE KEY UPDATE completed = 1, updated_at = CURRENT_TIMESTAMP"
    );
    $stmt->execute([$user['id'], $lessonId, $courseId]);
}

echo json_encode(['ok' => true]);
