<?php
// Simple health probe — no auth, no DB — just proves Apache+PHP are alive
http_response_code(200);
header('Content-Type: application/json');
echo json_encode(['status' => 'ok', 'service' => 'weboflix']);
