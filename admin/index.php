<?php
/**
 * MIDHILA VOYAGES — Admin Index Router
 */

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';

if (is_admin_logged_in()) {
    header('Location: ' . ADMIN_URL . '/dashboard.php');
} else {
    header('Location: ' . ADMIN_URL . '/login.php');
}
exit;
