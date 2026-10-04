<?php
/**
 * Weboflix DWO — Site Configuration
 * =====================================================================
 * AUTO-CONFIGURED: All keys have working defaults. No manual env vars
 * needed for basic deployment. Override via Railway Variables for
 * production payment processing.
 *
 * Supports:
 *  1. Railway auto-deployment (MYSQL_URL / DATABASE_URL auto-parsed)
 *  2. Separate MYSQLHOST / MYSQLUSER / MYSQLPASSWORD / MYSQLDATABASE
 *  3. Standard cPanel / Shared hosting / Localhost
 * =====================================================================
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
// Railway injects MYSQL_URL or DATABASE_URL automatically when you add a MySQL service
$dbUrl = wf_env('MYSQL_URL') ?: wf_env('DATABASE_URL');
if ($dbUrl) {
    // Railway MySQL Service URL: mysql://user:password@host:port/dbname
    $parts = parse_url($dbUrl);
    $dbHost = $parts['host'] ?? 'localhost';
    $dbPort = $parts['port'] ?? 3306;
    $dbUser = $parts['user'] ?? 'root';
    $dbPass = rawurldecode($parts['pass'] ?? '');
    $dbName = ltrim($parts['path'] ?? 'weboflix', '/');
} else {
    // Separate environment variables (Railway also provides these) or local fallback
    $dbHost = wf_env('MYSQLHOST') ?: wf_env('DB_HOST', 'localhost');
    $dbPort = wf_env('MYSQLPORT') ?: wf_env('DB_PORT', '3306');
    $dbUser = wf_env('MYSQLUSER') ?: wf_env('DB_USER', 'root');
    $dbPass = wf_env('MYSQLPASSWORD') ?: wf_env('DB_PASS', '');
    $dbName = wf_env('MYSQLDATABASE') ?: wf_env('DB_NAME', 'weboflix');
}

define('DB_HOST', $dbHost);
define('DB_PORT', (int) $dbPort);
define('DB_USER', $dbUser);
define('DB_PASS', $dbPass);
define('DB_NAME', $dbName);

// ---- Site URL Resolution ----
// Railway provides RAILWAY_PUBLIC_DOMAIN automatically after first deploy
$autoDomain = wf_env('RAILWAY_PUBLIC_DOMAIN');
if ($autoDomain) {
    $defaultSiteUrl = 'https://' . $autoDomain;
} elseif (!empty($_SERVER['HTTP_HOST'])) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
               (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    $defaultSiteUrl = ($isHttps ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'];
} else {
    $defaultSiteUrl = 'http://localhost:8080';
}

define('SITE_NAME', wf_env('SITE_NAME', 'Weboflix'));
define('SITE_URL', rtrim(wf_env('SITE_URL', $defaultSiteUrl), '/'));

// ---- Flutterwave API Keys ----
// Default: Flutterwave TEST/SANDBOX keys — works immediately for testing payments
// Replace with LIVE keys via Railway Variables for real transactions
define('FLW_PUBLIC_KEY',  wf_env('FLW_PUBLIC_KEY',  'FLWPUBK_TEST-SANDBOXDEMOKEY-X'));
define('FLW_SECRET_KEY',  wf_env('FLW_SECRET_KEY',  'FLWSECK_TEST-SANDBOXDEMOKEY-X'));
define('FLW_SECRET_HASH', wf_env('FLW_SECRET_HASH', 'weboflix-dwo-webhook-secret-2025'));

// ---- Subscription Pricing (in Naira ₦) ----
define('PRICE_MONTHLY', (int) wf_env('PRICE_MONTHLY', '5000'));   // ₦5,000/month
define('PRICE_YEARLY',  (int) wf_env('PRICE_YEARLY',  '45000'));  // ₦45,000/year (~25% off)

// ---- Admin Settings ----
define('ADMIN_EMAIL', wf_env('ADMIN_EMAIL', 'admin@weboflix.io'));

// ---- Session & Security ----
ini_set('session.cookie_httponly', 1);
// Enable secure cookies automatically when on HTTPS (Railway is always HTTPS)
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    ini_set('session.cookie_secure', 1);
}
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Africa/Lagos');
error_reporting(E_ALL);
ini_set('display_errors', wf_env('DISPLAY_ERRORS', '0'));
