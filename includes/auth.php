<?php
/**
 * MIDHILA VOYAGES — Admin Authentication & Authorization
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/functions.php';

/**
 * Check if an administrator is currently logged in
 */
function is_admin_logged_in(): bool {
    return !empty($_SESSION['admin_user_id']) && !empty($_SESSION['admin_logged_in']);
}

/**
 * Retrieve current logged-in admin data
 */
function get_logged_in_admin(): ?array {
    if (!is_admin_logged_in()) {
        return null;
    }
    static $adminData = null;
    if ($adminData === null) {
        try {
            $db = getDB();
            $stmt = $db->prepare("SELECT `id`, `username`, `full_name`, `email`, `role`, `last_login` FROM `admins` WHERE `id` = ? AND `is_active` = 1 LIMIT 1");
            $stmt->execute([$_SESSION['admin_user_id']]);
            $adminData = $stmt->fetch() ?: null;
        } catch (Exception $e) {
            error_log("Error fetching admin user: " . $e->getMessage());
            return null;
        }
    }
    return $adminData;
}

/**
 * Require admin authentication on protected pages
 */
function require_admin_login(): void {
    if (!is_admin_logged_in()) {
        $_SESSION['admin_redirect_to'] = $_SERVER['REQUEST_URI'] ?? (ADMIN_URL . '/dashboard.php');
        header('Location: ' . ADMIN_URL . '/login.php');
        exit;
    }
}

/**
 * Authenticate admin with username & password
 */
function admin_login(string $username, string $password): array {
    $db = getDB();

    // Basic rate-limiting check by IP to mitigate brute-force
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $throttleKey = 'login_attempts_' . md5($ip);
    $attempts = $_SESSION[$throttleKey]['count'] ?? 0;
    $lastAttempt = $_SESSION[$throttleKey]['time'] ?? 0;

    if ($attempts >= 5 && (time() - $lastAttempt) < 300) {
        $remaining = 300 - (time() - $lastAttempt);
        return [
            'success' => false,
            'message' => "Too many failed login attempts. Please wait {$remaining} seconds before trying again."
        ];
    }

    $stmt = $db->prepare("SELECT * FROM `admins` WHERE `username` = ? AND `is_active` = 1 LIMIT 1");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if (!$admin || !password_verify($password, $admin['password_hash'])) {
        // Record failed attempt
        $_SESSION[$throttleKey] = [
            'count' => $attempts + 1,
            'time' => time()
        ];
        return ['success' => false, 'message' => 'Invalid administrative credentials.'];
    }

    // Reset throttle on success
    unset($_SESSION[$throttleKey]);

    // Regenerate session ID to prevent session fixation
    session_regenerate_id(true);

    $_SESSION['admin_user_id'] = $admin['id'];
    $_SESSION['admin_username'] = $admin['username'];
    $_SESSION['admin_full_name'] = $admin['full_name'];
    $_SESSION['admin_role'] = $admin['role'];
    $_SESSION['admin_logged_in'] = true;

    // Update last login
    $upd = $db->prepare("UPDATE `admins` SET `last_login` = NOW() WHERE `id` = ?");
    $upd->execute([$admin['id']]);

    log_activity($admin['id'], 'admin_login', "Administrator '{$admin['username']}' logged in successfully.");

    return ['success' => true, 'message' => 'Login successful.'];
}

/**
 * Log out administrator and terminate session
 */
function admin_logout(): void {
    if (is_admin_logged_in()) {
        log_activity($_SESSION['admin_user_id'] ?? null, 'admin_logout', 'Administrator logged out.');
    }
    unset(
        $_SESSION['admin_user_id'],
        $_SESSION['admin_username'],
        $_SESSION['admin_full_name'],
        $_SESSION['admin_role'],
        $_SESSION['admin_logged_in']
    );
}
