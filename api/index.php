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

// If file exists, check whether it is a static asset or a PHP file
if (file_exists($target) && is_file($target)) {
    $ext = strtolower(pathinfo($target, PATHINFO_EXTENSION));
    $mimes = [
        'css'   => 'text/css; charset=utf-8',
        'js'    => 'application/javascript; charset=utf-8',
        'json'  => 'application/json; charset=utf-8',
        'svg'   => 'image/svg+xml',
        'png'   => 'image/png',
        'jpg'   => 'image/jpeg',
        'jpeg'  => 'image/jpeg',
        'webp'  => 'image/webp',
        'gif'   => 'image/gif',
        'woff'  => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf'   => 'font/ttf',
        'ico'   => 'image/x-icon',
    ];

    if (isset($mimes[$ext])) {
        header('Content-Type: ' . $mimes[$ext]);
        header('Cache-Control: public, max-age=31536000, immutable');
        readfile($target);
        exit;
    }

    if ($ext === 'php') {
        $_SERVER['SCRIPT_FILENAME'] = $target;
        $_SERVER['SCRIPT_NAME'] = '/' . ltrim(str_replace($root, '', $target), '/\\');
        chdir(dirname($target));
        require $target;
        exit;
    }
}

// Default fallback to homepage index.php
$_SERVER['SCRIPT_FILENAME'] = $root . '/index.php';
$_SERVER['SCRIPT_NAME'] = '/index.php';
chdir($root);
require $root . '/index.php';
