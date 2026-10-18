<?php
/**
 * MIDHILA VOYAGES — Application Configuration
 */

// Ensure output buffering is active application-wide so headers/redirects never fail
if (!ob_get_level()) {
    ob_start();
}

// Strict error reporting in development, safe in production
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

// Set default timezone
date_default_timezone_set('Asia/Kolkata');

// Secure Session Configuration
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_samesite', 'Lax');
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        ini_set('session.cookie_secure', '1');
    }
    session_start();
}

// Database Credentials (supports environment variables for Vercel / Cloud MySQL)
define('DB_HOST', getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? '127.0.0.1'));
define('DB_PORT', getenv('DB_PORT') ?: ($_ENV['DB_PORT'] ?? '3306'));
define('DB_NAME', getenv('DB_NAME') ?: ($_ENV['DB_NAME'] ?? 'midhila_voyages'));
define('DB_USER', getenv('DB_USER') ?: ($_ENV['DB_USER'] ?? 'root'));
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : ($_ENV['DB_PASS'] ?? ''));
define('DB_CHARSET', 'utf8mb4');

// Project Directory & URL Path
define('ROOT_PATH', dirname(__DIR__));
define('UPLOADS_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'uploads');

// Auto-detect base URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
// Clean scriptDir to project root
$parts = explode('/', trim($scriptDir, '/\\'));
$baseFolder = '';
if (count($parts) > 0 && !empty($parts[0])) {
    $baseFolder = '/' . $parts[0];
}
// If inside /admin or /api, adjust baseFolder
if (preg_match('#/(admin|api)#', $scriptDir)) {
    $baseFolder = preg_replace('#/(admin|api).*$#', '', $scriptDir);
}
define('BASE_URL', rtrim($protocol . $host . $baseFolder, '/'));
define('ASSETS_URL', BASE_URL . '/assets');
define('UPLOADS_URL', BASE_URL . '/uploads');
define('ADMIN_URL', BASE_URL . '/admin');

// Ensure upload directories exist
$uploadDirs = [
    UPLOADS_PATH,
    UPLOADS_PATH . DIRECTORY_SEPARATOR . 'destinations',
    UPLOADS_PATH . DIRECTORY_SEPARATOR . 'packages',
    UPLOADS_PATH . DIRECTORY_SEPARATOR . 'gallery'
];
foreach ($uploadDirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}
