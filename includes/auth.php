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
    try {
        $now = date('Y-m-d H:i:s');
        $upd = $db->prepare("UPDATE `admins` SET `last_login` = ? WHERE `id` = ?");
        $upd->execute([$now, $admin['id']]);
    } catch (Exception $e) {
        error_log("Failed to update admin last_login: " . $e->getMessage());
    }

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

/**
 * Retrieve current admin role string
 */
function get_admin_role(): string {
    if (!empty($_SESSION['admin_role'])) {
        return (string)$_SESSION['admin_role'];
    }
    $admin = get_logged_in_admin();
    return $admin['role'] ?? 'super_admin';
}

/**
 * Check if logged in admin has one of the allowed roles
 * Super Admin always has full access.
 */
function has_role($allowedRoles): bool {
    if (!is_admin_logged_in()) {
        return false;
    }
    $currentRole = get_admin_role();

    // Super admin always has unrestricted clearance
    if ($currentRole === 'super_admin') {
        return true;
    }

    if (is_string($allowedRoles)) {
        $allowedRoles = [$allowedRoles];
    }

    // Support legacy role aliases
    $normalized = $currentRole;
    if ($currentRole === 'manager' || $currentRole === 'editor') {
        $normalized = 'travel_manage';
    }

    return in_array($normalized, $allowedRoles, true) || in_array($currentRole, $allowedRoles, true);
}

/**
 * Check specific role helpers
 */
function is_super_admin(): bool {
    return get_admin_role() === 'super_admin';
}

function is_travel_manager(): bool {
    return has_role(['travel_manage', 'super_admin']);
}

function is_payment_handler(): bool {
    return has_role(['payment_handle', 'super_admin']);
}

/**
 * Enforce role permissions on protected pages
 */
function require_role($allowedRoles, ?string $redirectUrl = null): void {
    require_admin_login();

    if (!has_role($allowedRoles)) {
        $currentRole = get_admin_role();
        $roleLabel = get_role_label($currentRole);
        set_flash_message('error', "Access Restricted: Your account role ({$roleLabel}) does not have permission to access that section.");

        if ($redirectUrl === null) {
            if ($currentRole === 'payment_handle') {
                $redirectUrl = ADMIN_URL . '/payments.php';
            } else {
                $redirectUrl = ADMIN_URL . '/dashboard.php';
            }
        }
        header('Location: ' . $redirectUrl);
        exit;
    }
}

/**
 * Get human-readable role label
 */
function get_role_label(string $role): string {
    switch ($role) {
        case 'super_admin':
            return 'Super Admin';
        case 'travel_manage':
        case 'manager':
        case 'editor':
            return 'Travel Manage';
        case 'payment_handle':
            return 'Payment Handle';
        default:
            return ucwords(str_replace('_', ' ', $role));
    }
}

/**
 * Return all defined system roles and metadata
 */
function get_all_roles(): array {
    return [
        'super_admin' => [
            'label' => 'Super Admin',
            'badge' => 'SUPER ADMIN',
            'desc' => 'Full unrestricted access to all modules, financial audits, tour catalog, and user credentials.',
            'color' => '#0F172A',
            'badge_bg' => '#0F172A',
            'badge_fg' => '#FFFFFF',
            'tag' => 'Full System Control'
        ],
        'travel_manage' => [
            'label' => 'Travel Manage',
            'badge' => 'TRAVEL MANAGE',
            'desc' => 'Manages tour packages, itineraries, destinations, categories, photo gallery, offer popup, inquiries, and customer bookings.',
            'color' => '#2563EB',
            'badge_bg' => '#EFF6FF',
            'badge_fg' => '#1D4ED8',
            'tag' => 'Tours, Bookings & Inquiries'
        ],
        'payment_handle' => [
            'label' => 'Payment Handle',
            'badge' => 'PAYMENT HANDLE',
            'desc' => 'Audits Razorpay payment transactions, captured revenue, payment reconciliations, and booking payment statuses.',
            'color' => '#059669',
            'badge_bg' => '#ECFDF5',
            'badge_fg' => '#047857',
            'tag' => 'Finance & Payment Auditing'
        ]
    ];
}
