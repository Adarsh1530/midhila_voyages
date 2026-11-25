<?php
/**
 * MIDHILA VOYAGES — Admin Initialization Bootstrap
 * Loads configuration, database, authentication, and ensures admin login
 * WITHOUT rendering any HTML output so headers/redirects work cleanly.
 */

if (!ob_get_level()) {
    ob_start();
}

require_once dirname(__DIR__, 2) . '/includes/config.php';
require_once dirname(__DIR__, 2) . '/includes/database.php';
require_once dirname(__DIR__, 2) . '/includes/functions.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/csrf.php';

require_admin_login();

// Prevent browser caching of dynamic admin management views
header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

$currentAdmin = get_logged_in_admin();
$db = getDB();
