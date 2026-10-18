<?php
/**
 * MIDHILA VOYAGES — Vercel Serverless Gateway Router
 * Resolves all incoming Vercel requests to their corresponding PHP scripts or index.php
 */

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$uri = rawurldecode($uri);

// Strip leading slash
$path = ltrim($uri, '/');
if (empty($path) || $path === '/') {
    $path = 'index.php';
}

$root = dirname(__DIR__);
$target = $root . '/' . $path;

// If target is a directory, append /index.php (e.g. /admin -> /admin/index.php)
if (is_dir($target)) {
    $target = rtrim($target, '/') . '/index.php';
}

// If no extension given and file doesn't exist, try appending .php
if (!file_exists($target) && file_exists($target . '.php')) {
    $target .= '.php';
}

// If file exists and is a PHP file, execute it
if (file_exists($target) && is_file($target)) {
    $_SERVER['SCRIPT_FILENAME'] = $target;
    $_SERVER['SCRIPT_NAME'] = '/' . ltrim(str_replace($root, '', $target), '/\\');
    chdir(dirname($target));
    require $target;
} else {
    // Default fallback to homepage index.php
    $_SERVER['SCRIPT_FILENAME'] = $root . '/index.php';
    $_SERVER['SCRIPT_NAME'] = '/index.php';
    chdir($root);
    require $root . '/index.php';
}
