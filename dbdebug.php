<?php
// TEMPORARY DEBUG — remove after diagnosis
header('Content-Type: text/plain');
$keys = ['MYSQL_URL','MYSQLHOST','MYSQLPORT','MYSQLUSER','MYSQLDATABASE','DISPLAY_ERRORS'];
foreach ($keys as $k) {
    $g = getenv($k);
    $e = $_ENV[$k] ?? '(not in $_ENV)';
    $s = $_SERVER[$k] ?? '(not in $_SERVER)';
    echo "$k:\n  getenv = " . ($g === false ? '(false)' : $g) . "\n  \$_ENV  = $e\n  \$_SERVER = $s\n\n";
}
