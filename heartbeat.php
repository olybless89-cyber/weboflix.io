<?php
/**
 * Called periodically from watch.php while a lesson is open, so someone
 * mid-video for 10+ minutes still shows as "active now" in admin metrics
 * instead of falling off the moment their last page load ages out.
 */
require_once __DIR__ . '/includes/functions.php';
header('Content-Type: application/json');

$user = current_user();
if (!$user) { http_response_code(401); echo json_encode(['ok' => false]); exit; }

// current_user() already throttle-updates last_active_at, so this alone
// covers it — but force it here too in case the page has been open long
// enough that the throttle window would otherwise skip an update.
$stmt = db()->prepare('UPDATE users SET last_active_at = NOW() WHERE id = ?');
$stmt->execute([$user['id']]);

echo json_encode(['ok' => true]);
