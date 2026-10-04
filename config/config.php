<?php
/**
 * Weboflix — Site Configuration
 * Fully supports:
 *  1. Railway auto-deployment (reads MYSQL_URL, DATABASE_URL, or MYSQLHOST/MYSQLUSER/etc. automatically)
 *  2. Standard cPanel / Shared hosting / Localhost via manual values or .env
 */

// Helper to get environment variables with fallback
function wf_env(string $key, ?string $default = null): ?string {
    $val = getenv($key);
    if ($val !== false && $val !== '') return $val;
    if (isset($_ENV[$key]) && $_ENV[$key] !== '') return $_ENV[$key];
    if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') return $_SERVER[$key];
    return $default;
}

// ---- Database Connection Resolution ----
$dbUrl = wf_env('MYSQL_URL') ?: wf_env('DATABASE_URL');
if ($dbUrl) {
    // Railway MySQL Service URL: mysql://user:password@host:port/dbname
    $parts = parse_url($dbUrl);
    $dbHost = $parts['host'] ?? 'localhost';
    $dbPort = $parts['port'] ?? 3306;
    $dbUser = $parts['user'] ?? 'root';
    $dbPass = $parts['pass'] ?? '';
    $dbName = ltrim($parts['path'] ?? 'weboflix', '/');
} else {
    // Separate environment variables or cPanel fallback
    $dbHost = wf_env('MYSQLHOST') ?: wf_env('DB_HOST', 'localhost');
    $dbPort = wf_env('MYSQLPORT') ?: wf_env('DB_PORT', '3306');
    $dbUser = wf_env('MYSQLUSER') ?: wf_env('DB_USER', 'your_db_user');
    $dbPass = wf_env('MYSQLPASSWORD') ?: wf_env('DB_PASS', 'your_db_password');
    $dbName = wf_env('MYSQLDATABASE') ?: wf_env('DB_NAME', 'weboflix');
}

define('DB_HOST', $dbHost);
define('DB_PORT', (int) $dbPort);
define('DB_USER', $dbUser);
define('DB_PASS', $dbPass);
define('DB_NAME', $dbName);

// ---- Site URL Resolution ----
// If on Railway, RAILWAY_PUBLIC_DOMAIN is provided automatically
$autoDomain = wf_env('RAILWAY_PUBLIC_DOMAIN');
if ($autoDomain) {
    $defaultSiteUrl = 'https://' . $autoDomain;
} elseif (!empty($_SERVER['HTTP_HOST'])) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
               (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    $defaultSiteUrl = ($isHttps ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'];
} else {
    $defaultSiteUrl = 'https://weboflix.io';
}

define('SITE_NAME', wf_env('SITE_NAME', 'Weboflix'));
define('SITE_URL', rtrim(wf_env('SITE_URL', $defaultSiteUrl), '/'));

// ---- Flutterwave API Keys ----
define('FLW_PUBLIC_KEY', wf_env('FLW_PUBLIC_KEY', 'FLWPUBK-XXXXXXXXXXXXXXXXXXXXXXXX-X'));
define('FLW_SECRET_KEY', wf_env('FLW_SECRET_KEY', 'FLWSECK-XXXXXXXXXXXXXXXXXXXXXXXX-X'));
define('FLW_SECRET_HASH', wf_env('FLW_SECRET_HASH', 'your-webhook-secret-hash'));

// ---- Subscription pricing (in Naira) ----
define('PRICE_MONTHLY', (int) wf_env('PRICE_MONTHLY', '5000'));
define('PRICE_YEARLY', (int) wf_env('PRICE_YEARLY', '45000'));

// ---- Session & Security ----
ini_set('session.cookie_httponly', 1);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Africa/Lagos');
error_reporting(E_ALL);
ini_set('display_errors', wf_env('DISPLAY_ERRORS', '0'));
