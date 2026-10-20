<?php
/**
 * MIDHILA VOYAGES — Core Utility & Helper Functions
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/csrf.php';

/**
 * Safe HTML string escaping
 */
function e(?string $string): string {
    return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
}

/**
 * Fetch a site setting by key with caching
 */
function get_setting(string $key, string $default = ''): string {
    static $settingsCache = null;

    if ($settingsCache === null) {
        $settingsCache = [];
        try {
            $db = getDB();
            $stmt = $db->query("SELECT `setting_key`, `setting_value` FROM `site_settings`");
            while ($row = $stmt->fetch()) {
                $settingsCache[$row['setting_key']] = $row['setting_value'];
            }
        } catch (Exception $e) {
            error_log("Failed to load site settings: " . $e->getMessage());
        }
    }

    return $settingsCache[$key] ?? $default;
}

/**
 * Update or insert a site setting
 */
function update_setting(string $key, ?string $value, string $group = 'general'): bool {
    try {
        $db = getDB();
        $stmt = $db->prepare("
            INSERT INTO `site_settings` (`setting_key`, `setting_value`, `setting_group`)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`), `updated_at` = NOW()
        ");
        return $stmt->execute([$key, $value, $group]);
    } catch (Exception $e) {
        error_log("Failed to update setting {$key}: " . $e->getMessage());
        return false;
    }
}

/**
 * Format currency price (e.g. ₹24,500)
 */
function format_price(?float $amount, string $currency = 'INR'): string {
    if ($amount === null || $amount <= 0) {
        return 'Request a Quote';
    }
    $symbol = ($currency === 'INR') ? '₹' : ($currency . ' ');
    // Indian number format style (lakhs & crores or standard thousands)
    $formatted = number_format($amount, 0, '.', ',');
    return $symbol . $formatted;
}

/**
 * Slugify text
 */
function slugify(string $text): string {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return empty($text) ? 'n-a' : $text;
}

/**
 * Generate a unique memorable reference number
 * e.g. MV-ENQ-2026-X9A2 or MV-BKG-2026-B87C
 */
function generate_reference(string $prefix = 'MV-ENQ'): string {
    $year = date('Y');
    $random = strtoupper(bin2hex(random_bytes(3))); // 6 hex chars
    return "{$prefix}-{$year}-{$random}";
}

/**
 * Build official WhatsApp URL with prefilled message
 */
function build_whatsapp_url(string $phone, string $message): string {
    // Sanitize phone number (remove +, spaces, dashes)
    $cleanedPhone = preg_replace('/[^0-9]/', '', $phone);
    // If starts with 0 or has 10 digits without country code, default to 91
    if (strlen($cleanedPhone) === 10) {
        $cleanedPhone = '91' . $cleanedPhone;
    }
    $encodedMessage = rawurlencode($message);
    return "https://wa.me/{$cleanedPhone}?text={$encodedMessage}";
}

/**
 * Flash message helpers
 */
function set_flash_message(string $type, string $message): void {
    if (!isset($_SESSION['flash_messages'])) {
        $_SESSION['flash_messages'] = [];
    }
    $_SESSION['flash_messages'][] = [
        'type' => $type, // 'success', 'error', 'info', 'warning'
        'message' => $message
    ];
}

function get_flash_messages(): array {
    $messages = $_SESSION['flash_messages'] ?? [];
    unset($_SESSION['flash_messages']);
    return $messages;
}

function render_flash_messages(): string {
    $messages = get_flash_messages();
    if (empty($messages)) {
        return '';
    }
    $html = '<div class="flash-messages-container" role="alert">';
    foreach ($messages as $msg) {
        $type = e($msg['type']);
        $text = e($msg['message']);
        $icon = $type === 'success' ? '✓' : ($type === 'error' ? '⚠' : 'ℹ');
        $html .= "<div class=\"flash-toast flash-toast--{$type}\">";
        $html .= "<span class=\"flash-toast__icon\">{$icon}</span>";
        $html .= "<div class=\"flash-toast__body\">{$text}</div>";
        $html .= "<button type=\"button\" class=\"flash-toast__close\" onclick=\"this.parentElement.remove()\" aria-label=\"Close\">&times;</button>";
        $html .= "</div>";
    }
    $html .= '</div>';
    return $html;
}

/**
 * Log administrative activity
 */
function log_activity(?int $admin_id, string $action_type, string $description): void {
    try {
        $db = getDB();
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $stmt = $db->prepare("INSERT INTO `activity_logs` (`admin_id`, `action_type`, `description`, `ip_address`) VALUES (?, ?, ?, ?)");
        $stmt->execute([$admin_id, $action_type, $description, $ip]);
    } catch (Exception $e) {
        error_log("Failed to log activity: " . $e->getMessage());
    }
}

/**
 * Secure Image File Uploader
 * Validates MIME type, extension, size, and generates unguessable filename
 *
 * @param array $file $_FILES['input_name']
 * @param string $subDir 'destinations', 'packages', 'gallery'
 * @param int $maxSizeMb Max file size in megabytes
 * @return array ['success' => bool, 'path' => string|null, 'error' => string|null]
 */
function upload_image(array $file, string $subDir = 'general', int $maxSizeMb = 5): array {
    if (!isset($file['error']) || is_array($file['error'])) {
        return ['success' => false, 'path' => null, 'error' => 'Invalid file parameter.'];
    }

    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['success' => false, 'path' => null, 'error' => 'No file uploaded.'];
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'path' => null, 'error' => 'Upload error code: ' . $file['error']];
    }

    $maxBytes = $maxSizeMb * 1024 * 1024;
    if ($file['size'] > $maxBytes) {
        return ['success' => false, 'path' => null, 'error' => "File size exceeds the {$maxSizeMb}MB limit."];
    }

    // Inspect real MIME type using Fileinfo
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);

    $allowedMimes = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp'
    ];

    if (!isset($allowedMimes[$mimeType])) {
        return ['success' => false, 'path' => null, 'error' => 'Invalid file format. Only JPG, PNG, and WebP images are allowed.'];
    }

    $extension = $allowedMimes[$mimeType];
    $randomName = bin2hex(random_bytes(16)) . '.' . $extension;

    $targetDirectory = UPLOADS_PATH . DIRECTORY_SEPARATOR . $subDir;
    if (!is_dir($targetDirectory)) {
        @mkdir($targetDirectory, 0755, true);
    }

    $destinationPath = $targetDirectory . DIRECTORY_SEPARATOR . $randomName;

    if (!move_uploaded_file($file['tmp_name'], $destinationPath)) {
        return ['success' => false, 'path' => null, 'error' => 'Failed to save uploaded file.'];
    }

    // Relative web-accessible path
    $webPath = 'uploads/' . $subDir . '/' . $randomName;
    return ['success' => true, 'path' => $webPath, 'error' => null];
}

/**
 * Format image URL (handles external URLs or local upload paths)
 */
function get_image_url(?string $path, string $placeholder = ''): string {
    if (empty($path)) {
        return $placeholder ?: (ASSETS_URL . '/images/placeholder.svg');
    }
    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
        return $path;
    }
    return BASE_URL . '/' . ltrim($path, '/');
}

/**
 * Truncate a string by number of words
 */
function truncate_words(?string $text, int $limit = 20, string $ellipsis = '...'): string {
    if (empty($text)) {
        return '';
    }
    $text = strip_tags($text);
    $words = preg_split('/\s+/u', trim($text), $limit + 1);
    if (count($words) > $limit) {
        array_pop($words);
        return implode(' ', $words) . $ellipsis;
    }
    return implode(' ', $words);
}
