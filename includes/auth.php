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

    // Dynamically enforce module privilege check if viewing a registered console module
    $currentModule = get_current_admin_module();
    if ($currentModule !== null && !has_privilege_access($currentModule)) {
        $currentRole = get_admin_role();
        $roleLabel = get_role_label($currentRole);
        set_flash_message('error', "Access Restricted: Your account role ({$roleLabel}) does not have privilege access to this section.");

        if ($redirectUrl === null) {
            $redirectUrl = ADMIN_URL . '/dashboard.php';
        }
        header('Location: ' . $redirectUrl);
        exit;
    }

    if (!has_role($allowedRoles)) {
        $currentRole = get_admin_role();
        $roleLabel = get_role_label($currentRole);
        set_flash_message('error', "Access Restricted: Your account role ({$roleLabel}) does not have permission to access that section.");

        if ($redirectUrl === null) {
            if ($currentRole === 'payment_handle') {
                $redirectUrl = ADMIN_URL . '/bookings.php';
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
            'desc' => 'Manages passenger reservations, booking confirmations, vouchers, and guest personal details.',
            'color' => '#059669',
            'badge_bg' => '#ECFDF5',
            'badge_fg' => '#047857',
            'tag' => 'Bookings & Reservations'
        ]
    ];
}

/**
 * Return all 9 official admin console navigation modules matching sidebar
 */
function get_admin_modules(): array {
    return [
        'dashboard' => [
            'name' => 'Home / Dashboard',
            'file' => 'dashboard.php',
            'desc' => 'Overview KPI counts, analytics & quick action cards',
        ],
        'enquiries' => [
            'name' => 'Customer Inquiries',
            'file' => 'enquiries.php',
            'desc' => 'Trip inquiries & 1-tap direct WhatsApp communication',
        ],
        'bookings' => [
            'name' => 'Tour Bookings',
            'file' => 'bookings.php',
            'desc' => 'Guest reservations, traveller rosters & printable travel vouchers',
        ],
        'packages' => [
            'name' => 'Tour Packages',
            'file' => 'packages.php',
            'desc' => 'Create, edit & price holiday packages, day itineraries & stays',
        ],
        'destinations' => [
            'name' => 'Destinations',
            'file' => 'destinations.php',
            'desc' => 'Showcase destinations, highlights, best time to visit & spots',
        ],
        'offer_popup' => [
            'name' => 'Offer Popup Banner',
            'file' => 'offer-popup.php',
            'desc' => 'Seasonal promo flyer, deal headline & popup display status',
        ],
        'gallery' => [
            'name' => 'Photo Gallery',
            'file' => 'gallery.php',
            'desc' => 'Photo library showcase upload (≤ 500 KB compressed)',
        ],
        'users' => [
            'name' => 'Staff Accounts',
            'file' => 'users.php',
            'desc' => 'Create staff credentials, passwords & assign access roles',
        ],
        'privileges' => [
            'name' => 'Privilege Settings',
            'file' => 'privileges.php',
            'desc' => 'Manage role-based check-in & check-out access clearance',
        ],
    ];
}

/**
 * Default module privileges configuration
 */
function get_default_module_privileges(): array {
    return [
        'dashboard'    => ['super_admin', 'travel_manage', 'payment_handle'],
        'enquiries'    => ['super_admin', 'travel_manage', 'payment_handle'],
        'bookings'     => ['super_admin', 'travel_manage', 'payment_handle'],
        'packages'     => ['super_admin', 'travel_manage'],
        'destinations' => ['super_admin', 'travel_manage'],
        'offer_popup'  => ['super_admin', 'travel_manage'],
        'gallery'      => ['super_admin', 'travel_manage'],
        'users'        => ['super_admin'],
        'privileges'   => ['super_admin'],
    ];
}

/**
 * Retrieve current module privileges mapping from database or defaults
 */
function get_module_privileges(): array {
    static $cachedPrivs = null;
    if ($cachedPrivs !== null) {
        return $cachedPrivs;
    }

    $defaults = get_default_module_privileges();
    $raw = get_setting('module_privileges', '');

    if (!empty($raw)) {
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            foreach ($defaults as $m => $roles) {
                if (!isset($decoded[$m]) || !is_array($decoded[$m])) {
                    $decoded[$m] = $roles;
                }
                // Super admin always retains unrestricted clearance
                if (!in_array('super_admin', $decoded[$m], true)) {
                    $decoded[$m][] = 'super_admin';
                }
            }
            $cachedPrivs = $decoded;
            return $cachedPrivs;
        }
    }

    $cachedPrivs = $defaults;
    return $cachedPrivs;
}

/**
 * Check if the given role (or current logged-in role) has privilege access to a module
 */
function has_privilege_access(string $module, ?string $role = null): bool {
    if ($role === null) {
        if (!is_admin_logged_in()) {
            return false;
        }
        $role = get_admin_role();
    }

    // Super Admin always has 100% full clearance
    if ($role === 'super_admin') {
        return true;
    }

    // Support legacy role aliases
    if ($role === 'manager' || $role === 'editor') {
        $role = 'travel_manage';
    }

    $privs = get_module_privileges();
    $allowed = $privs[$module] ?? [];

    return in_array($role, $allowed, true);
}

/**
 * Detect the current admin module based on the active script filename
 */
function get_current_admin_module(): ?string {
    $script = basename($_SERVER['PHP_SELF'] ?? '');
    $map = [
        'dashboard.php'        => 'dashboard',
        'enquiries.php'        => 'enquiries',
        'enquiry-view.php'     => 'enquiries',
        'bookings.php'         => 'bookings',
        'packages.php'         => 'packages',
        'package-edit.php'     => 'packages',
        'destinations.php'     => 'destinations',
        'destination-edit.php' => 'destinations',
        'offer-popup.php'      => 'offer_popup',
        'gallery.php'          => 'gallery',
        'users.php'            => 'users',
        'privileges.php'       => 'privileges',
    ];
    return $map[$script] ?? null;
}
