<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth_actions.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['ok' => false, 'error' => 'Method not allowed']); exit; }

if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Your session expired — please refresh the page and try again.']);
    exit;
}

$result = attempt_login($_POST['email'] ?? '', $_POST['password'] ?? '');
if ($result['ok']) {
    login_user($result['user_id']);
}
echo json_encode(['ok' => $result['ok'], 'error' => $result['error']]);
