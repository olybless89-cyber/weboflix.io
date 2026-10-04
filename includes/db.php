<?php
require_once __DIR__ . '/../config/config.php';

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            $pdo = new PDO(
                $dsn,
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );

            // Auto-initialize tables and migrations if needed (zero-stress deployment)
            wf_auto_init_database($pdo);

        } catch (PDOException $e) {
            http_response_code(500);
            die('Database connection failed. Detail: ' . htmlspecialchars($e->getMessage()));
        }
    }
    return $pdo;
}

/**
 * Automatically creates tables and seeds courses if the database is newly created (e.g. fresh Railway MySQL)
 */
function wf_auto_init_database(PDO $pdo): void {
    try {
        // Check if users table exists
        $test = $pdo->query("SHOW TABLES LIKE 'users'")->fetchColumn();
        if (!$test) {
            $schemaFile = __DIR__ . '/../database/schema.sql';
            if (file_exists($schemaFile)) {
                $sql = file_get_contents($schemaFile);
                execute_multi_query($pdo, $sql);
            }
            return;
        }

        // Check if is_featured column exists in courses table
        $hasFeatured = $pdo->query("SHOW COLUMNS FROM courses LIKE 'is_featured'")->fetchColumn();
        if (!$hasFeatured) {
            $pdo->exec("ALTER TABLE courses ADD COLUMN is_featured TINYINT(1) DEFAULT 0 AFTER is_published");
        }

        // Check if the real YouTube courses exist
        $hasEcommerce = $pdo->query("SELECT 1 FROM courses WHERE slug = 'build-ecommerce-website-with-ai'")->fetchColumn();
        if (!$hasEcommerce) {
            $migFile = __DIR__ . '/../database/migration_real_youtube_courses.sql';
            if (file_exists($migFile)) {
                $sql = file_get_contents($migFile);
                execute_multi_query($pdo, $sql);
            }
        }
    } catch (Exception $e) {
        // Log error silently without crashing the app if already initialized
        error_log('Database auto-init warning: ' . $e->getMessage());
    }
}

function execute_multi_query(PDO $pdo, string $sql): void {
    // Remove multi-line and single-line SQL comments
    $sql = preg_replace('/--.*$/m', '', $sql);
    $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);

    // Split on semicolons
    $statements = explode(';', $sql);
    foreach ($statements as $stmt) {
        $stmt = trim($stmt);
        if ($stmt !== '') {
            $pdo->exec($stmt);
        }
    }
}
