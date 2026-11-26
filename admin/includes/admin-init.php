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

// Enforce dynamic privilege clearance for the current console module
$currentModule = get_current_admin_module();
if ($currentModule !== null && !has_privilege_access($currentModule)) {
    $currentRole = get_admin_role();
    $roleLabel = get_role_label($currentRole);
    set_flash_message('error', "Access Restricted: Your role ({$roleLabel}) does not have privilege clearance to access this module.");
    if ($currentModule !== 'dashboard') {
        header('Location: ' . ADMIN_URL . '/dashboard.php');
        exit;
    }
}

// Prevent browser caching of dynamic admin management views
header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

$currentAdmin = get_logged_in_admin();
$db = getDB();
