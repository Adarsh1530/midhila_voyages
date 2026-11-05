<?php
/**
 * MIDHILA VOYAGES — OpenStreetMap & Ollama Schema Migration
 * 
 * Sets up:
 * 1. Coordinates for all destinations
 * 2. external_places table (hotels, resorts, restaurants, cafes, attractions)
 * 3. osm_cache table for rate limiting and fast response
 * 4. ai_itineraries table for grounded AI travel proposals
 * 5. enquiries table columns for selected places and AI content
 * 6. default site settings for Ollama and OSM
 */

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/database.php';

$db = getDB();
$driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
echo "Running OSM & Ollama migration on driver: {$driver}\n";

// Helper for column check in SQLite / MySQL
function addColIfNotExists(PDO $db, string $table, string $column, string $typeDef, string $driver) {
    try {
        if ($driver === 'sqlite') {
            $cols = $db->query("PRAGMA table_info(`{$table}`)")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($cols as $c) {
                if ($c['name'] === $column) return;
            }
            $db->exec("ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$typeDef}");
            echo "Added column {$column} to {$table} (sqlite)\n";
        } else {
            $stmt = $db->query("SHOW COLUMNS FROM `{$table}` LIKE '{$column}'");
            if (!$stmt->fetch()) {
                $db->exec("ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$typeDef}");
                echo "Added column {$column} to {$table} (mysql)\n";
            }
        }
    } catch (Exception $e) {
        echo "Notice on {$table}.{$column}: " . $e->getMessage() . "\n";
    }
}

// 1. Update Destination Coordinates
$coords = [
    1  => [9.9312, 76.2673],   // Kerala Backwaters & Hills
    2  => [9.9195, 78.1193],   // Tamil Nadu Temple Trails
    3  => [25.3176, 82.9739],  // Varanasi
    4  => [26.7922, 82.1998],  // Ayodhya
    5  => [11.7401, 92.6586],  // Andaman Islands
    6  => [10.5667, 72.6417],  // Lakshadweep Islands
    7  => [32.2432, 77.1892],  // Kullu & Manali
    8  => [17.3616, 78.4747],  // Hyderabad
    9  => [25.2048, 55.2708],  // Dubai & Emirates
    10 => [-8.4095, 115.1889], // Bali
    11 => [1.3521, 103.8198],  // Singapore
    12 => [7.8804, 98.3923],   // Thailand
    13 => [3.1390, 101.6869],  // Malaysia
    14 => [3.2028, 73.2207],   // Maldives Atolls
    15 => [10.0889, 77.0595],  // Munnar
    16 => [11.6854, 76.1320],  // Wayanad
    17 => [9.6031, 77.1615],   // Thekkady
    18 => [9.6877, 76.9056],   // Vagamon
    19 => [8.7609, 77.1111],   // Ponmudi
    20 => [9.4981, 76.3388],   // Alleppey
    21 => [9.6175, 76.4301],   // Kumarakom
    22 => [10.5946, 76.0420],  // Guruvayur
    23 => [9.4344, 77.0817],   // Sabarimala
    24 => [8.4831, 76.9436],   // Padmanabhaswamy Temple
    25 => [8.4004, 76.9787],   // Kovalam
    26 => [8.7379, 76.7163],   // Varkala
    27 => [10.1416, 76.1783],  // Cherai Beach
];

$updateStmt = $db->prepare("UPDATE destinations SET latitude = ?, longitude = ? WHERE id = ?");
foreach ($coords as $id => $ll) {
    $updateStmt->execute([$ll[0], $ll[1], $id]);
}
echo "Destination coordinates updated.\n";

// 2. EXTERNAL_PLACES TABLE
$db->exec("
CREATE TABLE IF NOT EXISTS `external_places` (
    `id` " . ($driver === 'sqlite' ? "INTEGER PRIMARY KEY AUTOINCREMENT" : "INT UNSIGNED AUTO_INCREMENT PRIMARY KEY") . ",
    `destination_id` INT NOT NULL,
    `osm_type` VARCHAR(20) NOT NULL DEFAULT 'node',
    `osm_id` VARCHAR(50) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `name_ml` VARCHAR(255) DEFAULT NULL,
    `name_ta` VARCHAR(255) DEFAULT NULL,
    `category` VARCHAR(50) NOT NULL,
    `sub_type` VARCHAR(100) DEFAULT NULL,
    `latitude` DECIMAL(10,7) NOT NULL,
    `longitude` DECIMAL(10,7) NOT NULL,
    `address` TEXT DEFAULT NULL,
    `contact_phone` VARCHAR(100) DEFAULT NULL,
    `website` VARCHAR(255) DEFAULT NULL,
    `indicative_price` DECIMAL(10,2) DEFAULT 0.00,
    `price_unit` VARCHAR(50) DEFAULT 'per person / night',
    `room_categories` TEXT DEFAULT NULL,
    `facilities` TEXT DEFAULT NULL,
    `food_options` TEXT DEFAULT NULL,
    `description` TEXT DEFAULT NULL,
    `raw_tags_json` TEXT DEFAULT NULL,
    `is_approved` TINYINT(1) DEFAULT 1,
    `is_verified` TINYINT(1) DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(`osm_type`, `osm_id`)
)
");
echo "Table external_places verified.\n";

// 3. OSM_CACHE TABLE
$db->exec("
CREATE TABLE IF NOT EXISTS `osm_cache` (
    `id` " . ($driver === 'sqlite' ? "INTEGER PRIMARY KEY AUTOINCREMENT" : "INT UNSIGNED AUTO_INCREMENT PRIMARY KEY") . ",
    `cache_key` VARCHAR(150) NOT NULL UNIQUE,
    `destination_id` INT NOT NULL,
    `category` VARCHAR(50) NOT NULL,
    `response_json` MEDIUMTEXT DEFAULT NULL,
    `expires_at` DATETIME NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
)
");
echo "Table osm_cache verified.\n";

// 4. AI_ITINERARIES TABLE
$db->exec("
CREATE TABLE IF NOT EXISTS `ai_itineraries` (
    `id` " . ($driver === 'sqlite' ? "INTEGER PRIMARY KEY AUTOINCREMENT" : "INT UNSIGNED AUTO_INCREMENT PRIMARY KEY") . ",
    `reference_no` VARCHAR(60) NOT NULL UNIQUE,
    `destination_id` INT NOT NULL,
    `customer_name` VARCHAR(150) DEFAULT NULL,
    `customer_phone` VARCHAR(50) DEFAULT NULL,
    `customer_email` VARCHAR(150) DEFAULT NULL,
    `duration_days` INT DEFAULT 3,
    `budget_range` VARCHAR(80) DEFAULT NULL,
    `travel_type` VARCHAR(80) DEFAULT NULL,
    `selected_places_json` TEXT DEFAULT NULL,
    `itinerary_content` TEXT NOT NULL,
    `model_used` VARCHAR(80) DEFAULT 'ollama:llama3.2',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
)
");
echo "Table ai_itineraries verified.\n";

// 5. ENQUIRIES TABLE ENHANCEMENTS
addColIfNotExists($db, 'enquiries', 'selected_places_json', 'TEXT DEFAULT NULL', $driver);
addColIfNotExists($db, 'enquiries', 'itinerary_content', 'TEXT DEFAULT NULL', $driver);
addColIfNotExists($db, 'enquiries', 'ai_generated', 'TINYINT(1) DEFAULT 0', $driver);

// 6. DEFAULT SITE SETTINGS FOR OLLAMA & OSM
$settingsToAdd = [
    'ollama_api_url' => ['http://127.0.0.1:11434', 'ai'],
    'ollama_model'   => ['llama3.2', 'ai'],
    'osm_auto_approve' => ['1', 'osm'],
    'osm_search_radius_km' => ['10', 'osm'],
    'osm_user_agent' => ['MidhilaVoyages/2.0 (contact@midhilavoyages.com; tourism-portal)', 'osm'],
];

$checkSettingStmt = $db->prepare("SELECT id FROM site_settings WHERE setting_key = ?");
$insertSettingStmt = $db->prepare("INSERT INTO site_settings (setting_key, setting_value, setting_group) VALUES (?, ?, ?)");

foreach ($settingsToAdd as $k => $info) {
    $checkSettingStmt->execute([$k]);
    if (!$checkSettingStmt->fetch()) {
        $insertSettingStmt->execute([$k, $info[0], $info[1]]);
        echo "Inserted setting {$k} = {$info[0]}\n";
    }
}

echo "Migration finished successfully!\n";
