<?php
/**
 * MIDHILA VOYAGES — Admin Logout
 */

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';

admin_logout();
header('Location: ' . ADMIN_URL . '/login.php');
exit;
