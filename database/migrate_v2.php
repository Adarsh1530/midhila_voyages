<?php
/**
 * MIDHILA VOYAGES — Database Migration V2
 * Creates and updates tables for:
 * 1. Multilingual Support
 * 2. Dedicated Kerala Tourism Module (5 Categories)
 * 3. Accommodations & Destination-based Linking
 * 4. Customised Tour Enquiries
 * 5. Corporate & Business Events (Conference, Seminar, Convention)
 * 6. Centralised Booking Workflow & Availability Checks
 * 7. Customer Registry & Communications Log
 * 8. Translations & Email Logs
 */

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/database.php';

$db = getDB();
$driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
echo "Running migration on driver: {$driver}\n";

// Helper for column check in SQLite / MySQL
function addColumnIfNotExists(PDO $db, string $table, string $column, string $typeDef, string $driver) {
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

// -------------------------------------------------------------
// 1. TOURISM CATEGORIES (Dedicated Kerala Tourism Focus)
// -------------------------------------------------------------
$db->exec("
CREATE TABLE IF NOT EXISTS `tourism_categories` (
    `id` " . ($driver === 'sqlite' ? "INTEGER PRIMARY KEY AUTOINCREMENT" : "INT UNSIGNED AUTO_INCREMENT PRIMARY KEY") . ",
    `slug` VARCHAR(80) NOT NULL UNIQUE,
    `name_en` VARCHAR(150) NOT NULL,
    `name_ml` VARCHAR(255) DEFAULT NULL,
    `name_ta` VARCHAR(255) DEFAULT NULL,
    `tagline_en` VARCHAR(255) DEFAULT NULL,
    `tagline_ml` VARCHAR(255) DEFAULT NULL,
    `tagline_ta` VARCHAR(255) DEFAULT NULL,
    `description_en` TEXT DEFAULT NULL,
    `description_ml` TEXT DEFAULT NULL,
    `description_ta` TEXT DEFAULT NULL,
    `icon_svg` VARCHAR(50) DEFAULT 'compass',
    `featured_image` VARCHAR(255) DEFAULT NULL,
    `is_kerala_focus` TINYINT(1) DEFAULT 1,
    `display_order` INT DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP
)
");
echo "Table tourism_categories verified.\n";

// -------------------------------------------------------------
// 2. ACCOMMODATIONS (Hotels, Resorts, Houseboats, Homestays)
// -------------------------------------------------------------
$db->exec("
CREATE TABLE IF NOT EXISTS `accommodations` (
    `id` " . ($driver === 'sqlite' ? "INTEGER PRIMARY KEY AUTOINCREMENT" : "INT UNSIGNED AUTO_INCREMENT PRIMARY KEY") . ",
    `destination_id` INT DEFAULT NULL,
    `tourism_category_id` INT DEFAULT NULL,
    `name_en` VARCHAR(200) NOT NULL,
    `name_ml` VARCHAR(255) DEFAULT NULL,
    `name_ta` VARCHAR(255) DEFAULT NULL,
    `category` VARCHAR(80) DEFAULT 'Resort',
    `location_address` TEXT DEFAULT NULL,
    `contact_person` VARCHAR(150) DEFAULT NULL,
    `contact_phone` VARCHAR(60) DEFAULT NULL,
    `contact_email` VARCHAR(120) DEFAULT NULL,
    `room_categories` TEXT DEFAULT NULL,
    `room_views` TEXT DEFAULT NULL,
    `room_facilities` TEXT DEFAULT NULL,
    `food_availability` VARCHAR(100) DEFAULT 'with_food',
    `food_types` TEXT DEFAULT NULL,
    `sightseeing_services` VARCHAR(100) DEFAULT 'with_sightseeing',
    `additional_facilities` TEXT DEFAULT NULL,
    `indicative_price` DECIMAL(10,2) DEFAULT 0.00,
    `price_unit` VARCHAR(50) DEFAULT 'per night',
    `featured_image` VARCHAR(255) DEFAULT NULL,
    `photos_json` TEXT DEFAULT NULL,
    `special_instructions` TEXT DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP
)
");
echo "Table accommodations verified.\n";

// -------------------------------------------------------------
// 3. CORPORATE VENUES (Conference, Seminar, Convention)
// -------------------------------------------------------------
$db->exec("
CREATE TABLE IF NOT EXISTS `corporate_venues` (
    `id` " . ($driver === 'sqlite' ? "INTEGER PRIMARY KEY AUTOINCREMENT" : "INT UNSIGNED AUTO_INCREMENT PRIMARY KEY") . ",
    `destination_id` INT DEFAULT NULL,
    `venue_type` VARCHAR(60) NOT NULL DEFAULT 'conference_hall',
    `name_en` VARCHAR(200) NOT NULL,
    `name_ml` VARCHAR(255) DEFAULT NULL,
    `name_ta` VARCHAR(255) DEFAULT NULL,
    `location_address` TEXT DEFAULT NULL,
    `capacity_min` INT DEFAULT 20,
    `capacity_max` INT DEFAULT 500,
    `seating_arrangements` TEXT DEFAULT NULL,
    `audio_visual_facilities` TEXT DEFAULT NULL,
    `catering_options` TEXT DEFAULT NULL,
    `accommodation_available` TINYINT(1) DEFAULT 1,
    `accommodation_details` TEXT DEFAULT NULL,
    `parking_capacity` INT DEFAULT 50,
    `starting_price` DECIMAL(10,2) DEFAULT 0.00,
    `featured_image` VARCHAR(255) DEFAULT NULL,
    `photos_json` TEXT DEFAULT NULL,
    `description_en` TEXT DEFAULT NULL,
    `description_ml` TEXT DEFAULT NULL,
    `description_ta` TEXT DEFAULT NULL,
    `contact_phone` VARCHAR(60) DEFAULT NULL,
    `contact_email` VARCHAR(120) DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP
)
");
echo "Table corporate_venues verified.\n";

// -------------------------------------------------------------
// 4. CUSTOMERS REGISTRY
// -------------------------------------------------------------
$db->exec("
CREATE TABLE IF NOT EXISTS `customers` (
    `id` " . ($driver === 'sqlite' ? "INTEGER PRIMARY KEY AUTOINCREMENT" : "INT UNSIGNED AUTO_INCREMENT PRIMARY KEY") . ",
    `full_name` VARCHAR(150) NOT NULL,
    `email` VARCHAR(120) DEFAULT NULL,
    `phone` VARCHAR(50) NOT NULL,
    `whatsapp` VARCHAR(50) DEFAULT NULL,
    `city` VARCHAR(100) DEFAULT NULL,
    `state` VARCHAR(100) DEFAULT NULL,
    `country` VARCHAR(100) DEFAULT 'India',
    `preferred_language` VARCHAR(10) DEFAULT 'en',
    `total_enquiries` INT DEFAULT 1,
    `total_bookings` INT DEFAULT 0,
    `internal_notes` TEXT DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP
)
");
echo "Table customers verified.\n";

// -------------------------------------------------------------
// 5. BOOKING AVAILABILITY CHECKS
// -------------------------------------------------------------
$db->exec("
CREATE TABLE IF NOT EXISTS `availability_checks` (
    `id` " . ($driver === 'sqlite' ? "INTEGER PRIMARY KEY AUTOINCREMENT" : "INT UNSIGNED AUTO_INCREMENT PRIMARY KEY") . ",
    `enquiry_id` INT NOT NULL,
    `admin_id` INT DEFAULT NULL,
    `accommodation_id` INT DEFAULT NULL,
    `provider_name` VARCHAR(200) NOT NULL,
    `contact_person` VARCHAR(120) DEFAULT NULL,
    `contacted_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `response_status` VARCHAR(50) NOT NULL DEFAULT 'pending',
    `available_room_category` VARCHAR(120) DEFAULT NULL,
    `confirmed_room_quantity` INT DEFAULT 0,
    `quoted_price` DECIMAL(10,2) DEFAULT NULL,
    `provider_remarks` TEXT DEFAULT NULL,
    `admin_remarks` TEXT DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
)
");
echo "Table availability_checks verified.\n";

// -------------------------------------------------------------
// 6. CUSTOMER COMMUNICATIONS LOG
// -------------------------------------------------------------
$db->exec("
CREATE TABLE IF NOT EXISTS `customer_communications` (
    `id` " . ($driver === 'sqlite' ? "INTEGER PRIMARY KEY AUTOINCREMENT" : "INT UNSIGNED AUTO_INCREMENT PRIMARY KEY") . ",
    `customer_id` INT DEFAULT NULL,
    `enquiry_id` INT DEFAULT NULL,
    `admin_id` INT DEFAULT NULL,
    `channel` VARCHAR(30) NOT NULL DEFAULT 'whatsapp',
    `subject` VARCHAR(255) DEFAULT NULL,
    `message` TEXT NOT NULL,
    `status` VARCHAR(40) DEFAULT 'sent',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
)
");
echo "Table customer_communications verified.\n";

// -------------------------------------------------------------
// 7. STATUS HISTORY AUDIT LOG
// -------------------------------------------------------------
$db->exec("
CREATE TABLE IF NOT EXISTS `booking_status_history` (
    `id` " . ($driver === 'sqlite' ? "INTEGER PRIMARY KEY AUTOINCREMENT" : "INT UNSIGNED AUTO_INCREMENT PRIMARY KEY") . ",
    `enquiry_id` INT NOT NULL,
    `admin_id` INT DEFAULT NULL,
    `old_status` VARCHAR(50) DEFAULT NULL,
    `new_status` VARCHAR(50) NOT NULL,
    `notes` TEXT DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
)
");
echo "Table booking_status_history verified.\n";

// -------------------------------------------------------------
// 8. EMAIL LOGS
// -------------------------------------------------------------
$db->exec("
CREATE TABLE IF NOT EXISTS `email_logs` (
    `id` " . ($driver === 'sqlite' ? "INTEGER PRIMARY KEY AUTOINCREMENT" : "INT UNSIGNED AUTO_INCREMENT PRIMARY KEY") . ",
    `recipient_email` VARCHAR(150) NOT NULL,
    `recipient_name` VARCHAR(150) DEFAULT NULL,
    `subject` VARCHAR(255) NOT NULL,
    `message_body` TEXT NOT NULL,
    `enquiry_id` INT DEFAULT NULL,
    `status` VARCHAR(30) DEFAULT 'sent',
    `sent_at` DATETIME DEFAULT CURRENT_TIMESTAMP
)
");
echo "Table email_logs verified.\n";

// -------------------------------------------------------------
// 9. TRANSLATIONS DICTIONARY
// -------------------------------------------------------------
$db->exec("
CREATE TABLE IF NOT EXISTS `translations` (
    `id` " . ($driver === 'sqlite' ? "INTEGER PRIMARY KEY AUTOINCREMENT" : "INT UNSIGNED AUTO_INCREMENT PRIMARY KEY") . ",
    `translation_key` VARCHAR(120) NOT NULL UNIQUE,
    `group_name` VARCHAR(60) DEFAULT 'general',
    `en_text` TEXT NOT NULL,
    `ml_text` TEXT DEFAULT NULL,
    `ta_text` TEXT DEFAULT NULL,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP
)
");
echo "Table translations verified.\n";

// -------------------------------------------------------------
// UPGRADE EXISTING TABLES WITH NEW COLUMNS
// -------------------------------------------------------------
// destinations
addColumnIfNotExists($db, 'destinations', 'tourism_category_id', 'INT DEFAULT NULL', $driver);
addColumnIfNotExists($db, 'destinations', 'name_ml', 'VARCHAR(255) DEFAULT NULL', $driver);
addColumnIfNotExists($db, 'destinations', 'name_ta', 'VARCHAR(255) DEFAULT NULL', $driver);
addColumnIfNotExists($db, 'destinations', 'description_ml', 'TEXT DEFAULT NULL', $driver);
addColumnIfNotExists($db, 'destinations', 'description_ta', 'TEXT DEFAULT NULL', $driver);
addColumnIfNotExists($db, 'destinations', 'highlights_ml', 'TEXT DEFAULT NULL', $driver);
addColumnIfNotExists($db, 'destinations', 'highlights_ta', 'TEXT DEFAULT NULL', $driver);
addColumnIfNotExists($db, 'destinations', 'is_kerala_focus', 'TINYINT(1) DEFAULT 0', $driver);

// packages
addColumnIfNotExists($db, 'packages', 'tourism_category_id', 'INT DEFAULT NULL', $driver);
addColumnIfNotExists($db, 'packages', 'title_ml', 'VARCHAR(255) DEFAULT NULL', $driver);
addColumnIfNotExists($db, 'packages', 'title_ta', 'VARCHAR(255) DEFAULT NULL', $driver);
addColumnIfNotExists($db, 'packages', 'short_description_ml', 'TEXT DEFAULT NULL', $driver);
addColumnIfNotExists($db, 'packages', 'short_description_ta', 'TEXT DEFAULT NULL', $driver);
addColumnIfNotExists($db, 'packages', 'overview_ml', 'TEXT DEFAULT NULL', $driver);
addColumnIfNotExists($db, 'packages', 'overview_ta', 'TEXT DEFAULT NULL', $driver);
addColumnIfNotExists($db, 'packages', 'inclusions_ml', 'TEXT DEFAULT NULL', $driver);
addColumnIfNotExists($db, 'packages', 'inclusions_ta', 'TEXT DEFAULT NULL', $driver);
addColumnIfNotExists($db, 'packages', 'exclusions_ml', 'TEXT DEFAULT NULL', $driver);
addColumnIfNotExists($db, 'packages', 'exclusions_ta', 'TEXT DEFAULT NULL', $driver);
addColumnIfNotExists($db, 'packages', 'accommodation_details', 'TEXT DEFAULT NULL', $driver);
addColumnIfNotExists($db, 'packages', 'sightseeing_details', 'TEXT DEFAULT NULL', $driver);
addColumnIfNotExists($db, 'packages', 'is_kerala_focus', 'TINYINT(1) DEFAULT 0', $driver);

// enquiries
addColumnIfNotExists($db, 'enquiries', 'enquiry_type', "VARCHAR(50) DEFAULT 'general_tour'", $driver);
addColumnIfNotExists($db, 'enquiries', 'language', "VARCHAR(10) DEFAULT 'en'", $driver);
addColumnIfNotExists($db, 'enquiries', 'starting_location', 'VARCHAR(150) DEFAULT NULL', $driver);
addColumnIfNotExists($db, 'enquiries', 'travel_type', 'VARCHAR(80) DEFAULT NULL', $driver);
addColumnIfNotExists($db, 'enquiries', 'rooms_count', 'INT DEFAULT 1', $driver);
addColumnIfNotExists($db, 'enquiries', 'duration_days', 'INT DEFAULT 1', $driver);
addColumnIfNotExists($db, 'enquiries', 'accommodation_id', 'INT DEFAULT NULL', $driver);
addColumnIfNotExists($db, 'enquiries', 'accommodation_name', 'VARCHAR(200) DEFAULT NULL', $driver);
addColumnIfNotExists($db, 'enquiries', 'preferred_room_types', 'TEXT DEFAULT NULL', $driver);
addColumnIfNotExists($db, 'enquiries', 'food_preference', 'VARCHAR(80) DEFAULT NULL', $driver);
addColumnIfNotExists($db, 'enquiries', 'food_type', 'VARCHAR(80) DEFAULT NULL', $driver);
addColumnIfNotExists($db, 'enquiries', 'food_special_instructions', 'TEXT DEFAULT NULL', $driver);
addColumnIfNotExists($db, 'enquiries', 'sightseeing_preference', 'VARCHAR(80) DEFAULT NULL', $driver);
addColumnIfNotExists($db, 'enquiries', 'sightseeing_places', 'TEXT DEFAULT NULL', $driver);
addColumnIfNotExists($db, 'enquiries', 'required_facilities', 'TEXT DEFAULT NULL', $driver);
addColumnIfNotExists($db, 'enquiries', 'corporate_organisation_name', 'VARCHAR(200) DEFAULT NULL', $driver);
addColumnIfNotExists($db, 'enquiries', 'corporate_event_type', 'VARCHAR(100) DEFAULT NULL', $driver);
addColumnIfNotExists($db, 'enquiries', 'corporate_venue_type', 'VARCHAR(80) DEFAULT NULL', $driver);
addColumnIfNotExists($db, 'enquiries', 'corporate_venue_id', 'INT DEFAULT NULL', $driver);
addColumnIfNotExists($db, 'enquiries', 'corporate_participants_count', 'INT DEFAULT NULL', $driver);
addColumnIfNotExists($db, 'enquiries', 'corporate_seating_arrangement', 'VARCHAR(100) DEFAULT NULL', $driver);
addColumnIfNotExists($db, 'enquiries', 'corporate_catering_needs', 'TEXT DEFAULT NULL', $driver);
addColumnIfNotExists($db, 'enquiries', 'corporate_av_needs', 'TEXT DEFAULT NULL', $driver);
addColumnIfNotExists($db, 'enquiries', 'corporate_accommodation_needed', 'TINYINT(1) DEFAULT 0', $driver);
addColumnIfNotExists($db, 'enquiries', 'corporate_transport_needed', 'TINYINT(1) DEFAULT 0', $driver);
addColumnIfNotExists($db, 'enquiries', 'workflow_status', "VARCHAR(50) DEFAULT 'new'", $driver);
addColumnIfNotExists($db, 'enquiries', 'availability_provider_name', 'VARCHAR(200) DEFAULT NULL', $driver);
addColumnIfNotExists($db, 'enquiries', 'availability_contacted_at', 'DATETIME DEFAULT NULL', $driver);
addColumnIfNotExists($db, 'enquiries', 'availability_response', 'VARCHAR(80) DEFAULT NULL', $driver);
addColumnIfNotExists($db, 'enquiries', 'availability_room_category', 'VARCHAR(120) DEFAULT NULL', $driver);
addColumnIfNotExists($db, 'enquiries', 'availability_room_quantity', 'INT DEFAULT NULL', $driver);
addColumnIfNotExists($db, 'enquiries', 'availability_quoted_price', 'DECIMAL(10,2) DEFAULT NULL', $driver);
addColumnIfNotExists($db, 'enquiries', 'availability_remarks', 'TEXT DEFAULT NULL', $driver);
addColumnIfNotExists($db, 'enquiries', 'admin_internal_notes', 'TEXT DEFAULT NULL', $driver);
addColumnIfNotExists($db, 'enquiries', 'assigned_staff_id', 'INT DEFAULT NULL', $driver);

// -------------------------------------------------------------
// 10. SEED THE 5 KERALA TOURISM CATEGORIES
// -------------------------------------------------------------
$keralaCategories = [
    [
        'slug' => 'pilgrimage',
        'name_en' => 'Pilgrimage Tourism',
        'name_ml' => 'തീർത്ഥാടന ടൂറിസം',
        'name_ta' => 'ஆன்மீக யாத்திரை சுற்றுலா',
        'tagline_en' => 'Sacred Vedic Trails, Temple Darshans & Divine Serenity',
        'tagline_ml' => 'ക്ഷേത്ര ദർശനങ്ങളും ആത്മീയ പാതകളും',
        'tagline_ta' => 'புனித தலங்கள் மற்றும் ஆன்மீக தரிசனங்கள்',
        'description_en' => 'Revered spiritual journeys across Kerala including Guruvayur Sri Krishna Temple, Sabarimala, Sree Padmanabhaswamy Temple, Chottanikkara, and divine Vedic circuits with verified accommodations and elderly-first ground coordination.',
        'description_ml' => 'ഗുരുവായൂർ, ശബരിമല, പത്മനാഭസ്വാമി ക്ഷേത്രം, ചോറ്റാനിക്കര തുടങ്ങിയ പുണ്യക്ഷേത്രങ്ങളിലേക്കുള്ള ആത്മീയ യാത്രകൾ.',
        'description_ta' => 'குருவாயூர், சபரிமலை, பத்மநாபசுவாமி கோவில் உள்ளிட்ட கேரளாவின் புகழ்பெற்ற புனித தல யாத்திரைகள்.',
        'icon_svg' => 'temple',
        'featured_image' => 'https://images.unsplash.com/photo-1590050752117-238cb0fb12b1?auto=format&fit=crop&w=1200&q=80',
        'display_order' => 1
    ],
    [
        'slug' => 'hill-stations',
        'name_en' => 'Hill Station Tourism',
        'name_ml' => 'ഹിൽ സ്റ്റേഷൻ ടൂറിസം',
        'name_ta' => 'மலை வாசஸ்தல சுற்றுலா',
        'tagline_en' => 'Misty Horizons, Tea Plantations & Verdant Highlands',
        'tagline_ml' => 'മൂടൽമഞ്ഞണിഞ്ഞ മലനിരകളും തേയിലത്തോട്ടങ്ങളും',
        'tagline_ta' => 'பனி மூடிய மலைகள் மற்றும் தேயிலைத் தோட்டங்கள்',
        'description_en' => 'Breathtaking highland retreats across Munnar, Wayanad, Thekkady, Vagamon, and Ponmudi with private chauffeurs, plantation view chalets, and unhurried mountain pacing.',
        'description_ml' => 'മൂന്നാർ, വയനാട്, തേക്കടി, വാഗമൺ, പൊൻമുടി തുടങ്ങിയ സുഖവാസ മലനിരകളിലേക്ക് സുഖകരമായ യാത്രകൾ.',
        'description_ta' => 'மூணார், வயநாடு, தேக்கடி, வாகமண் மற்றும் பொன்முடி உள்ளிட்ட அழகிய மலை வாசஸ்தலப் பயணங்கள்.',
        'icon_svg' => 'mountain',
        'featured_image' => 'https://images.unsplash.com/photo-1596176530529-78163a4f7af2?auto=format&fit=crop&w=1200&q=80',
        'display_order' => 2
    ],
    [
        'slug' => 'backwaters',
        'name_en' => 'Backwater Tourism',
        'name_ml' => 'കായൽ ടൂറിസം',
        'name_ta' => 'காயல் சுற்றுலா',
        'tagline_en' => 'Private Houseboats, Palm-fringed Canals & Waterfront Stays',
        'tagline_ml' => 'ആലപ്പുഴ, കുമരകം ഹൗസ്ബോട്ടുകളും കായൽ കാഴ്ചകളും',
        'tagline_ta' => 'அலெப்பி, குமரகம் படகு வீடுகள் மற்றும் காயல் உலா',
        'description_en' => 'Serene aquatic voyages across Alleppey, Kumarakom, and Vembanad Lake featuring premium private houseboats, authentic culinary onboard service, and tranquil lakeside resorts.',
        'description_ml' => 'ആലപ്പുഴ, കുമരകം എന്നിവിടങ്ങളിലെ പ്രീമിയം ഹൗസ്ബോട്ടുകളും കായലോര വിശ്രമ കേന്ദ്രങ്ങളും.',
        'description_ta' => 'அலெப்பி மற்றும் குமரகம் காயல்களில் பாரம்பரிய படகு வீடு மற்றும் சொகுசு தங்கும் அனுபவங்கள்.',
        'icon_svg' => 'anchor',
        'featured_image' => 'https://images.unsplash.com/photo-1602216056096-3b40cc0c9944?auto=format&fit=crop&w=1200&q=80',
        'display_order' => 3
    ],
    [
        'slug' => 'beaches',
        'name_en' => 'Beach Tourism',
        'name_ml' => 'ബീച്ച് ടൂറിസം',
        'name_ta' => 'கடற்கரை சுற்றுலா',
        'tagline_en' => 'Golden Sands, Arabian Sea Sunsets & Coastal Retreats',
        'tagline_ml' => 'കോവളം, വർക്കല, ചെറായി തീരദേശ അനുഭവങ്ങൾ',
        'tagline_ta' => 'கோவளம், வர்கலா மற்றும் செராய் கடற்கரைகள்',
        'description_en' => 'Golden coastal sanctuaries along Kovalam, Varkala Cliff, Cherai, Bekal, and Marari featuring oceanfront luxury chalets, sunset view suites, and relaxed coastal exploration.',
        'description_ml' => 'കോവളം, വർക്കല, ചെറായി, ബേക്കൽ തുടങ്ങിയ മനോഹരമായ കേരള തീരദേശങ്ങളിലേക്കുള്ള യാത്രകൾ.',
        'description_ta' => 'கோவளம் மற்றும் வர்கலா போன்ற புகழ்பெற்ற கடற்கரைகளில் அமைதியான ஓய்வு மற்றும் அனுபவங்கள்.',
        'icon_svg' => 'sun',
        'featured_image' => 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?auto=format&fit=crop&w=1200&q=80',
        'display_order' => 4
    ],
    [
        'slug' => 'ayurveda-wellness',
        'name_en' => 'Ayurveda & Wellness Tourism',
        'name_ml' => 'ആയുർവേദ & വെൽനസ് ടൂറിസം',
        'name_ta' => 'ஆயுர்வேதம் & நல்வாழ்வு சுற்றுலா',
        'tagline_en' => 'Ancient Healing, Panchakarma & Rejuvenating Sanctuary Stays',
        'tagline_ml' => 'പരമ്പരാഗത ആയുർവേദ ചികിത്സകളും പുനരുജ്ജീവന പാക്കേജുകളും',
        'tagline_ta' => 'பாரம்பரிய ஆயுர்வேத சிகிச்சைகள் மற்றும் புத்துணர்ச்சி திட்டங்கள்',
        'description_en' => 'Holistic authentic Ayurvedic rejuvenation packages, doctor-guided wellness therapy, Panchakarma detoxification, yoga retreats, and serene spa sanctuaries.',
        'description_ml' => 'ആയുർവേദ പുനരുജ്ജീവന ചികിത്സകൾ, വെൽനസ് റിസോർട്ടുകൾ, യോഗാ കേന്ദ്രങ്ങൾ എന്നിവ സമന്വയിപ്പിച്ച പാക്കേജുകൾ.',
        'description_ta' => 'முழுமையான ஆயுர்வேத புத்துணர்ச்சி சிகிச்சைகள், யோகா மற்றும் இயற்கை எழில் கொஞ்சும் ஓய்வு இல்லங்கள்.',
        'icon_svg' => 'heart',
        'featured_image' => 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=1200&q=80',
        'display_order' => 5
    ]
];

foreach ($keralaCategories as $cat) {
    $chk = $db->prepare("SELECT id FROM tourism_categories WHERE slug = ?");
    $chk->execute([$cat['slug']]);
    if (!$chk->fetch()) {
        $ins = $db->prepare("
            INSERT INTO tourism_categories 
            (slug, name_en, name_ml, name_ta, tagline_en, tagline_ml, tagline_ta, description_en, description_ml, description_ta, icon_svg, featured_image, is_kerala_focus, display_order, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, 1)
        ");
        $ins->execute([
            $cat['slug'], $cat['name_en'], $cat['name_ml'], $cat['name_ta'],
            $cat['tagline_en'], $cat['tagline_ml'], $cat['tagline_ta'],
            $cat['description_en'], $cat['description_ml'], $cat['description_ta'],
            $cat['icon_svg'], $cat['featured_image'], $cat['display_order']
        ]);
        echo "Seeded category: {$cat['slug']}\n";
    }
}

// -------------------------------------------------------------
// 11. REGISTER KERALA DESTINATIONS
// -------------------------------------------------------------
$keralaDestinations = [
    // Hill Stations
    [
        'name' => 'Munnar',
        'slug' => 'munnar',
        'category_slug' => 'hill-stations',
        'region_state' => 'Kerala',
        'tagline' => 'Highland Tea Mist & Rolling Emerald Canopies',
        'name_ml' => 'മൂന്നാർ',
        'name_ta' => 'மூணார்',
        'description_en' => 'Perched at 1,600 metres above sea level in the Western Ghats, Munnar is Kerala’s premier hill sanctuary of rolling tea plantations, mist-clad valleys, and pristine mountain air.',
        'description_ml' => 'പശ്ചിമഘട്ടത്തിലെ പ്രകൃതിരമണീയമായ മൂന്നാർ തേയിലത്തോട്ടങ്ങൾക്കും തണുത്ത കാലാവസ്ഥയ്ക്കും പേരുകേട്ടതാണ്.',
        'description_ta' => 'மேற்கு தொடர்ச்சி மலையில் அமைந்துள்ள மூணார் தேயிலைத் தோட்டங்களுக்கும் பனி மூடிய பள்ளத்தாக்குகளுக்கும் புகழ் பெற்றது.',
        'highlights' => 'Tea Museum, Eravikulam National Park, Mattupetty Dam, Top Station, Anamudi Peak',
        'featured_image' => 'https://images.unsplash.com/photo-1596176530529-78163a4f7af2?auto=format&fit=crop&w=1200&q=80',
        'is_featured' => 1
    ],
    [
        'name' => 'Wayanad',
        'slug' => 'wayanad',
        'category_slug' => 'hill-stations',
        'region_state' => 'Kerala',
        'tagline' => 'Pristine Rainforests, Waterfalls & Ancient Caves',
        'name_ml' => 'വയനാട്',
        'name_ta' => 'வயநாடு',
        'description_en' => 'Famed for sub-tropical savannas, spice orchards, prehistoric Edakkal caves, and cascading mountain falls.',
        'description_ml' => 'പ്രകൃതി സൗന്ദര്യവും വനങ്ങളും മനോഹരമായ വെള്ളച്ചാട്ടങ്ങളും നിറഞ്ഞ ഹരിതഭൂമി.',
        'description_ta' => 'அழகிய காடுகள், நீர்வீழ்ச்சிகள் மற்றும் வரலாற்று சிறப்புமிக்க குகைகள் கொண்ட மலைப் பிரதேசம்.',
        'highlights' => 'Banasura Sagar Dam, Edakkal Caves, Chembra Peak, Soochipara Falls, Pookode Lake',
        'featured_image' => 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?auto=format&fit=crop&w=1200&q=80',
        'is_featured' => 1
    ],
    [
        'name' => 'Thekkady',
        'slug' => 'thekkady',
        'category_slug' => 'hill-stations',
        'region_state' => 'Kerala',
        'tagline' => 'Periyar Tiger Sanctuary & Aromatic Spice Trails',
        'name_ml' => 'തേക്കടി',
        'name_ta' => 'தேக்கடி',
        'description_en' => 'Home to Periyar Wildlife Sanctuary with scenic boat safaris, cardamon plantations, and tribal trails.',
        'description_ml' => 'പെരിയാർ വന്യജീവി സങ്കേതവും സുഗന്ധവ്യഞ്ജന തോട്ടങ്ങളും.',
        'description_ta' => 'பெரியார் வனவிலங்கு சரணாலயம் மற்றும் நறுமண மசாலா தோட்டங்கள்.',
        'highlights' => 'Periyar Boat Cruise, Spice Plantation Walk, Elephant Junction, Bamboo Rafting',
        'featured_image' => 'https://images.unsplash.com/photo-1588598198321-9735fd52455b?auto=format&fit=crop&w=1200&q=80',
        'is_featured' => 1
    ],
    [
        'name' => 'Vagamon',
        'slug' => 'vagamon',
        'category_slug' => 'hill-stations',
        'region_state' => 'Kerala',
        'tagline' => 'Quiet Pine Forests, Green Meadows & Pine Hills',
        'name_ml' => 'വാഗമൺ',
        'name_ta' => 'வாகமண்',
        'description_en' => 'An uncrowded, serene hill station known for rolling grassy knolls, pine forests, and misty chill.',
        'description_ml' => 'പൈൻ കാടുകളും പുൽമേടുകളും നിറഞ്ഞ ശാന്തമായ മലയോരം.',
        'description_ta' => 'பைன் காடுகள் மற்றும் பசுமையான புல்வெளிகள் நிறைந்த அமைதியான பகுதி.',
        'highlights' => 'Vagamon Pine Forest, Kurisumala Ashram, Marmala Waterfalls, Vagamon Lake',
        'featured_image' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1200&q=80',
        'is_featured' => 0
    ],
    [
        'name' => 'Ponmudi',
        'slug' => 'ponmudi',
        'category_slug' => 'hill-stations',
        'region_state' => 'Kerala',
        'tagline' => 'Golden Peak & 22 Hairpin Twisting Roads',
        'name_ml' => 'പൊൻമുടി',
        'name_ta' => 'பொன்முடி',
        'description_en' => 'A picturesque hill station in Thiruvananthapuram district with winding hairpin turns, tea gardens, and fresh streams.',
        'description_ml' => 'തിരുവനന്തപുരത്തെ മനോഹരമായ മലനിരകളും 22 ഹെയർപിൻ വളവുകളും.',
        'description_ta' => 'திருவனந்தபுரத்தின் அழகிய மலைச் சிகரங்கள் மற்றும் இயற்கை காட்சிகள்.',
        'highlights' => 'Golden Valley, Peppara Wildlife Sanctuary, Meenmutty Falls, Echo Point',
        'featured_image' => 'https://images.unsplash.com/photo-1470071459604-3b5ec3a7fe05?auto=format&fit=crop&w=1200&q=80',
        'is_featured' => 0
    ],

    // Backwaters
    [
        'name' => 'Alleppey (Alappuzha)',
        'slug' => 'alleppey',
        'category_slug' => 'backwaters',
        'region_state' => 'Kerala',
        'tagline' => 'Venice of the East & Luxury Private Houseboats',
        'name_ml' => 'ആലപ്പുഴ',
        'name_ta' => 'அலெப்பி',
        'description_en' => 'World-renowned network of palm-shaded lagoons, quiet canal villages, and handcrafted luxury wooden houseboats.',
        'description_ml' => 'കായൽ സൗന്ദര്യവും പരമ്പരാഗത കെട്ടുവള്ളങ്ങളും കണ്ട് ആസ്വദിക്കാൻ ആലപ്പുഴ.',
        'description_ta' => 'காயல்களின் தாய்மடி, படகு வீடு பயணங்களுக்கு உலகப் புகழ்பெற்ற இடம்.',
        'highlights' => 'Private Houseboat Cruise, Kuttanad Paddy Fields, Punnamada Lake, Marari Beach nearby',
        'featured_image' => 'https://images.unsplash.com/photo-1602216056096-3b40cc0c9944?auto=format&fit=crop&w=1200&q=80',
        'is_featured' => 1
    ],
    [
        'name' => 'Kumarakom',
        'slug' => 'kumarakom',
        'category_slug' => 'backwaters',
        'region_state' => 'Kerala',
        'tagline' => 'Vembanad Lake Sanctuary & Waterfront Resorts',
        'name_ml' => 'കുമരകം',
        'name_ta' => 'குமரகம்',
        'description_en' => 'A cluster of small islands on Vembanad Lake known for migratory bird sanctuaries, luxury heritage resorts, and sunset canoe rides.',
        'description_ml' => 'വേമ്പനാട്ട് കായലിന്റെ ഓരത്തുള്ള പക്ഷി സങ്കേതവും പഞ്ചനക്ഷത്ര റിസോർട്ടുകളും.',
        'description_ta' => 'வேம்பநாடு ஏரிக்கரையில் அமைந்த பறவைகள் சரணாலயம் மற்றும் சொகுசு விடுதிகள்.',
        'highlights' => 'Kumarakom Bird Sanctuary, Vembanad Lake, Bay Island Driftwood Museum, Village Crafts Walk',
        'featured_image' => 'https://images.unsplash.com/photo-1590050752117-238cb0fb12b1?auto=format&fit=crop&w=1200&q=80',
        'is_featured' => 1
    ],

    // Pilgrimage
    [
        'name' => 'Guruvayur',
        'slug' => 'guruvayur',
        'category_slug' => 'pilgrimage',
        'region_state' => 'Kerala',
        'tagline' => 'Bhuloka Vaikunta • Sacred Krishna Darshan',
        'name_ml' => 'ഗുരുവായൂർ',
        'name_ta' => 'குருவாயூர்',
        'description_en' => 'One of India’s most revered pilgrimage shrines dedicated to Lord Krishna, attracting millions for soulful darshan and divine blessings.',
        'description_ml' => 'ഭൂലോക വൈകുണ്ഠമായ ഗുരുവായൂർ ശ്രീകൃഷ്ണസ്വാമി ക്ഷേത്രം.',
        'description_ta' => 'பூலோக வைகுண்டம் என்று போற்றப்படும் புனித குருவாயூர் கிருஷ்ணன் கோவில்.',
        'highlights' => 'Sri Krishna Temple, Punnathur Kotta Elephant Sanctuary, Mammiyoor Shiva Temple',
        'featured_image' => 'https://images.unsplash.com/photo-1621847468516-1ed5d0df56fe?auto=format&fit=crop&w=1200&q=80',
        'is_featured' => 1
    ],
    [
        'name' => 'Sabarimala',
        'slug' => 'sabarimala',
        'category_slug' => 'pilgrimage',
        'region_state' => 'Kerala',
        'tagline' => 'Swamiye Saranam Ayyappa • Forest Shrine of Truth',
        'name_ml' => 'ശബരിമല',
        'name_ta' => 'சபரிமலை',
        'description_en' => 'The revered hill shrine of Lord Ayyappa surrounded by 18 hills in the dense Periyar Tiger Reserve, famed for Mandala-Makaravilakku pilgrimage.',
        'description_ml' => 'സ്വാമിയേ ശരണമയ്യപ്പാ! പമ്പാ നദിക്കരയിലൂടെയുള്ള പുണ്യ തീർത്ഥാടനം.',
        'description_ta' => 'சுவாமியே சரணம் ஐயப்பா! 18 படிகள் கொண்ட புனித மலைக் கோவில்.',
        'highlights' => 'Sannidhanam 18 Steps, Pamba River, Erumely Petta Thullal, Nilakkal Base Camp',
        'featured_image' => 'https://images.unsplash.com/photo-1590050752117-238cb0fb12b1?auto=format&fit=crop&w=1200&q=80',
        'is_featured' => 1
    ],
    [
        'name' => 'Padmanabhaswamy Temple (Thiruvananthapuram)',
        'slug' => 'padmanabhaswamy',
        'category_slug' => 'pilgrimage',
        'region_state' => 'Kerala',
        'tagline' => 'Ananthasayanam • World’s Richest Temple Heritage',
        'name_ml' => 'ശ്രീ പത്മനാഭസ്വാമി ക്ഷേത്രം',
        'name_ta' => 'ஸ்ரீ பத்மநாபசுவாமி கோவில்',
        'description_en' => 'Magnificent Dravidian and Kerala style temple dedicated to Lord Vishnu in eternal Ananthasayana posture on Adi Shesha.',
        'description_ml' => 'അനന്തശയന ഭാവത്തിലുള്ള ഭഗവാൻ വിഷ്ണുവിന്റെ പുണ്യസങ്കേതം.',
        'description_ta' => 'அனந்த சயன கோலத்தில் ஸ்ரீ மகாவிஷ்ணு அருள்பாலிக்கும் வரலாற்றுப் புகழ்மிக்க திருத்தலம்.',
        'highlights' => 'Ottakkal Mandapam, Temple Corridor with 365 Pillars, Kuthiramalika Palace',
        'featured_image' => 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?auto=format&fit=crop&w=1200&q=80',
        'is_featured' => 1
    ],

    // Beaches
    [
        'name' => 'Kovalam',
        'slug' => 'kovalam',
        'category_slug' => 'beaches',
        'region_state' => 'Kerala',
        'tagline' => 'Crescent Coast, Iconic Lighthouse & Sunset Promenade',
        'name_ml' => 'കോവളം',
        'name_ta' => 'கோவளம்',
        'description_en' => 'Famous worldwide for its three crescent beaches separated by rocky promontories, featuring safe swimming waters and authentic Ayurvedic centers.',
        'description_ml' => 'ലൈറ്റ് ഹൗസും അസ്തമയ ഭംഗിയും നിറഞ്ഞ പ്രശസ്തമായ കോവളം ബീച്ച്.',
        'description_ta' => 'கலங்கரை விளக்கம் மற்றும் தங்க மணற்பரப்பு கொண்ட புகழ்பெற்ற கடற்கரை.',
        'highlights' => 'Lighthouse Beach, Hawah Beach, Samudra Beach, Halcyon Castle',
        'featured_image' => 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?auto=format&fit=crop&w=1200&q=80',
        'is_featured' => 1
    ],
    [
        'name' => 'Varkala',
        'slug' => 'varkala',
        'category_slug' => 'beaches',
        'region_state' => 'Kerala',
        'tagline' => 'Red Sedimentary Cliffs & Papanasam Healing Waters',
        'name_ml' => 'വർക്കല',
        'name_ta' => 'வர்கலா',
        'description_en' => 'Stunning red sandstone cliffs bordering the Arabian Sea, with mineral springs and the ancient 2,000-year-old Janardhana Swamy Temple.',
        'description_ml' => 'ചുവന്ന പാറക്കെട്ടുകളും പാപനാശം കടൽത്തീരവും.',
        'description_ta' => 'செங்குத்தான பாறைகள் மற்றும் பாபநாசம் கடற்கரை கொண்ட இயற்கை எழில் தலம்.',
        'highlights' => 'Varkala North Cliff Promenade, Papanasam Beach, Janardhana Swamy Temple, Kappil Beach',
        'featured_image' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1200&q=80',
        'is_featured' => 1
    ],
    [
        'name' => 'Cherai Beach',
        'slug' => 'cherai',
        'category_slug' => 'beaches',
        'region_state' => 'Kerala',
        'tagline' => 'Princess of the Arabian Sea • Calm Waves & Dolphins',
        'name_ml' => 'ചെറായി',
        'name_ta' => 'செராய்',
        'description_en' => 'Located on Vypin Island near Kochi, Cherai is famous for gentle shallow tides, dolphin sightings, and picturesque backwaters meeting the sea.',
        'description_ml' => 'കൊച്ചിക്ക് സമീപമുള്ള ശാന്തമായ കടൽത്തീരവും കായലും കടലും ചേരുന്ന കാഴ്ചയും.',
        'description_ta' => 'அமைதியான அலைகள் மற்றும் காயலும் கடலும் இணையும் அழகிய கடற்கரை.',
        'highlights' => 'Swimming Beach, Water Sports, Chinese Fishing Nets, Munambam Breakwater',
        'featured_image' => 'https://images.unsplash.com/photo-1510414842594-a61c69b5ae57?auto=format&fit=crop&w=1200&q=80',
        'is_featured' => 0
    ]
];

foreach ($keralaDestinations as $dest) {
    // Get category id
    $catId = $db->query("SELECT id FROM tourism_categories WHERE slug = '{$dest['category_slug']}'")->fetchColumn();
    
    $chk = $db->prepare("SELECT id FROM destinations WHERE slug = ?");
    $chk->execute([$dest['slug']]);
    $existing = $chk->fetch();

    if ($existing) {
        $upd = $db->prepare("
            UPDATE destinations 
            SET tourism_category_id = ?, region_state = 'Kerala', name_ml = ?, name_ta = ?, 
                description_ml = ?, description_ta = ?, is_kerala_focus = 1
            WHERE id = ?
        ");
        $upd->execute([
            $catId, $dest['name_ml'], $dest['name_ta'],
            $dest['description_ml'], $dest['description_ta'], $existing['id']
        ]);
    } else {
        $ins = $db->prepare("
            INSERT INTO destinations
            (name, slug, category, tourism_category_id, region_state, country, tagline, description, highlights, featured_image, is_featured, is_pilgrimage, is_published, display_order, name_ml, name_ta, description_ml, description_ta, is_kerala_focus)
            VALUES (?, ?, ?, ?, 'Kerala', 'India', ?, ?, ?, ?, ?, ?, 1, 1, ?, ?, ?, ?, 1)
        ");
        $isPilgrim = ($dest['category_slug'] === 'pilgrimage') ? 1 : 0;
        $ins->execute([
            $dest['name'], $dest['slug'], 1, $catId, $dest['tagline'], $dest['description_en'],
            $dest['highlights'], $dest['featured_image'], $dest['is_featured'], $isPilgrim,
            $dest['name_ml'], $dest['name_ta'], $dest['description_ml'], $dest['description_ta']
        ]);
        echo "Inserted destination: {$dest['name']}\n";
    }
}

// -------------------------------------------------------------
// 12. SEED ACCOMMODATIONS (Destination-Linked Options)
// -------------------------------------------------------------
$accommodationsSeed = [
    [
        'destination_slug' => 'munnar',
        'name_en' => 'Munnar Misty Mountain Tea Chalet',
        'name_ml' => 'മൂന്നാർ മിസ്റ്റി മൗണ്ടൻ ടീ ഷാലെറ്റ്',
        'name_ta' => 'மூணார் மிஸ்டி மவுண்டன் தேயிலை இல்லம்',
        'category' => 'Luxury Resort',
        'location_address' => 'Chithirapuram, Munnar, Idukki, Kerala 685565',
        'room_categories' => 'Mountain-view room, Tea plantation-view room, Valley-view room, Deluxe room, Luxury suite',
        'room_views' => 'Mountain-view, Tea plantation-view, Valley-view',
        'room_facilities' => 'Wi-Fi, Air conditioning, Balcony, Room service, Family rooms, Hot water, Electric kettle',
        'food_availability' => 'all_meals',
        'food_types' => 'Vegetarian, Non-vegetarian, Both vegetarian and non-vegetarian, Traditional Kerala Sadya',
        'sightseeing_services' => 'customised_sightseeing',
        'additional_facilities' => 'Campfire & BBQ, Plantation trek guide, Ayurvedic spa, Free parking, Travel desk',
        'indicative_price' => 4800.00,
        'featured_image' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1200&q=80',
        'special_instructions' => 'Complimentary high-altitude tea tasting every evening.'
    ],
    [
        'destination_slug' => 'munnar',
        'name_en' => 'Cloud Valley Heritage Plantation Resort',
        'name_ml' => 'ക്ലൗഡ് വാലി ഹെറിറ്റേജ് പ്ലാന്റേഷൻ റിസോർട്ട്',
        'name_ta' => 'கிளவுட் வேலி பாரம்பரிய தேயிலை ரிசார்ட்',
        'category' => 'Boutique Resort',
        'location_address' => 'Pallivasal, Munnar, Kerala 685612',
        'room_categories' => 'Tea plantation-view room, Valley-view room, Premium cottage, Standard room',
        'room_views' => 'Tea plantation-view, Valley-view',
        'room_facilities' => 'Wi-Fi, Balcony, Family rooms, Room service, Restaurant, Parking',
        'food_availability' => 'breakfast_dinner',
        'food_types' => 'Vegetarian, Non-vegetarian, Special dietary requirements upon request',
        'sightseeing_services' => 'private_vehicle',
        'additional_facilities' => 'Kids play zone, Nature walk trail, Indoor games, Private car park',
        'indicative_price' => 3600.00,
        'featured_image' => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=1200&q=80',
        'special_instructions' => 'Ideal for families seeking calm, quiet plantation serenity.'
    ],
    [
        'destination_slug' => 'alleppey',
        'name_en' => 'Punnamada Serene Backwater Palace Resort',
        'name_ml' => 'പുന്നമട ബാക്ക്വാട്ടർ പാലസ് റിസോർട്ട്',
        'name_ta' => 'புன்னமடா சொகுசு காயல் அரண்மனை ரிசார்ட்',
        'category' => 'Heritage Waterfront Resort',
        'location_address' => 'Punnamada, Alappuzha, Kerala 688006',
        'room_categories' => 'Lake-view room, Backwater-view room, Luxury heritage villa, Premium room',
        'room_views' => 'Lake-view, Backwater-view',
        'room_facilities' => 'Wi-Fi, Air conditioning, Swimming pool, Balcony, Restaurant, Room service',
        'food_availability' => 'all_meals',
        'food_types' => 'Vegetarian, Non-vegetarian, Fresh Karimeen Pollichathu, Jain options',
        'sightseeing_services' => 'private_vehicle',
        'additional_facilities' => 'Ayurvedic wellness center, Sunset Shikara ride, Speedboat cruise, Swimming pool',
        'indicative_price' => 6200.00,
        'featured_image' => 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=1200&q=80',
        'special_instructions' => 'Private jetty with direct boat transfers to houseboat terminals.'
    ],
    [
        'destination_slug' => 'alleppey',
        'name_en' => 'Emerald Symphony Private Luxury Houseboat',
        'name_ml' => 'എമറാൾഡ് സിംഫണി ലക്ഷ്വറി ഹൗസ്ബോട്ട്',
        'name_ta' => 'எமரால்டு சிம்பொனி சொகுசு ஹவுஸ்போட்',
        'category' => 'Private Houseboat',
        'location_address' => 'Finishing Point Jetty, Alappuzha, Kerala 688013',
        'room_categories' => 'Backwater-view room, Deluxe floating suite, Glass-covered AC bedroom',
        'room_views' => 'Backwater-view',
        'room_facilities' => 'Air conditioning, Sun deck, En-suite bathroom, Living lounge, Dining deck',
        'food_availability' => 'all_meals',
        'food_types' => 'Both vegetarian and non-vegetarian, Freshly caught fish, Traditional Kerala feast',
        'sightseeing_services' => 'customised_sightseeing',
        'additional_facilities' => 'Dedicated private chef, Experienced captain, Village exploration halt',
        'indicative_price' => 8500.00,
        'featured_image' => 'https://images.unsplash.com/photo-1602216056096-3b40cc0c9944?auto=format&fit=crop&w=1200&q=80',
        'special_instructions' => 'Full-day and overnight backwater cruise from 12 PM to 9 AM.'
    ],
    [
        'destination_slug' => 'kumarakom',
        'name_en' => 'Vembanad Lakeside Luxury Sanctuary',
        'name_ml' => 'വേമ്പനാട് ലേക്സൈഡ് ലക്ഷ്വറി സാങ്ച്വറി',
        'name_ta' => 'வேம்பநாடு ஏரிக்கரை சொகுசு தங்குமிடம்',
        'category' => '5-Star Resort',
        'location_address' => 'Vechoor, Kumarakom, Kottayam, Kerala 686144',
        'room_categories' => 'Lake-view room, Private pool villa, Heritage cottage, Deluxe room',
        'room_views' => 'Lake-view',
        'room_facilities' => 'Wi-Fi, Air conditioning, Swimming pool, Room service, Restaurant, Accessibility facilities',
        'food_availability' => 'all_meals',
        'food_types' => 'Vegetarian, Non-vegetarian, International cuisine, Detox wellness diet',
        'sightseeing_services' => 'customised_sightseeing',
        'additional_facilities' => 'Full-service Ayurvedic hospital, Infinity pool, Bird-watching tower, Yoga hall',
        'indicative_price' => 9800.00,
        'featured_image' => 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=1200&q=80',
        'special_instructions' => 'Complimentary sunrise yoga and bird sanctuary canoe tour.'
    ],
    [
        'destination_slug' => 'kovalam',
        'name_en' => 'Somatheeram Holistic Ayurvedic Beach Resort',
        'name_ml' => 'സോമതീരം ആയുർവേദിക് ബീച്ച് റിസോർട്ട്',
        'name_ta' => 'சோமதீரம் ஆயுர்வேத கடற்கரை ரிசார்ட்',
        'category' => 'Ayurvedic Retreat',
        'location_address' => 'Chowara Beach, Kovalam, Thiruvananthapuram, Kerala 695501',
        'room_categories' => 'Beach-view room, Ayurvedic wooden cottage, Kerala heritage palace room',
        'room_views' => 'Beach-view, Ocean-view',
        'room_facilities' => 'Wi-Fi, Air conditioning, Balcony, Doctor consultation room, Herbal garden',
        'food_availability' => 'all_meals',
        'food_types' => 'Vegetarian, Ayurvedic dosha-specific diet, Fresh herbal juices',
        'sightseeing_services' => 'with_sightseeing',
        'additional_facilities' => 'Panchakarma center, Daily yoga & meditation, Beach pathway, Doctor consultations',
        'indicative_price' => 7500.00,
        'featured_image' => 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=1200&q=80',
        'special_instructions' => 'Doctor consultation and customized wellness itinerary included.'
    ],
    [
        'destination_slug' => 'guruvayur',
        'name_en' => 'Sri Krishna Divine Residency & Pilgrim Suites',
        'name_ml' => 'ശ്രീകൃഷ്ണ ഡിവൈൻ റെസിഡൻസി',
        'name_ta' => 'ஸ்ரீ கிருஷ்ணா டிவைன் ரெசிடென்சி',
        'category' => 'Pilgrim Hotel',
        'location_address' => 'East Nada, Guruvayur, Thrissur, Kerala 680101',
        'room_categories' => 'Standard room, Deluxe room, Family quad room, Senior citizen accessibility room',
        'room_views' => 'Temple-view, City-view',
        'room_facilities' => 'Wi-Fi, Air conditioning, Accessibility facilities, Family rooms, 24/7 hot water, Elevator',
        'food_availability' => 'breakfast_only',
        'food_types' => 'Strictly vegetarian (Pure Veg Restaurant on premises), Jain food available',
        'sightseeing_services' => 'with_sightseeing',
        'additional_facilities' => 'Elderly wheelchair assistance, Cloak room, 2-minute walk to East Nada',
        'indicative_price' => 2400.00,
        'featured_image' => 'https://images.unsplash.com/photo-1621847468516-1ed5d0df56fe?auto=format&fit=crop&w=1200&q=80',
        'special_instructions' => 'Just 120 metres from Guruvayur Temple East Nada. Priority darshan assistance.'
    ],
    [
        'destination_slug' => 'wayanad',
        'name_en' => 'Rainforest Canopy Treehouse & Eco Resort',
        'name_ml' => 'റെയിൻഫോറസ്റ്റ് കനോപ്പി ട്രീഹൗസ് റിസോർട്ട്',
        'name_ta' => 'ரெய்ன்பாரஸ்ட் கேனோபி மர வீடு ரிசார்ட்',
        'category' => 'Eco Resort',
        'location_address' => 'Lakkidi, Wayanad, Kerala 673576',
        'room_categories' => 'Valley-view room, Treehouse suite, Luxury wooden cottage, Family villa',
        'room_views' => 'Valley-view, Forest-view',
        'room_facilities' => 'Wi-Fi, Balcony, Family rooms, Restaurant, Nature trail guide',
        'food_availability' => 'all_meals',
        'food_types' => 'Vegetarian, Non-vegetarian, Organic farm-to-table cuisine',
        'sightseeing_services' => 'customised_sightseeing',
        'additional_facilities' => 'Natural stream bath, Birdwatching hides, Campfire, 4x4 Jeep transfers',
        'indicative_price' => 5400.00,
        'featured_image' => 'https://images.unsplash.com/photo-1470071459604-3b5ec3a7fe05?auto=format&fit=crop&w=1200&q=80',
        'special_instructions' => 'Surrounded by evergreen rainforest with mountain streams.'
    ]
];

foreach ($accommodationsSeed as $acc) {
    $destId = $db->query("SELECT id FROM destinations WHERE slug = '{$acc['destination_slug']}'")->fetchColumn();
    if ($destId) {
        $chk = $db->prepare("SELECT id FROM accommodations WHERE name_en = ?");
        $chk->execute([$acc['name_en']]);
        if (!$chk->fetch()) {
            $ins = $db->prepare("
                INSERT INTO accommodations
                (destination_id, name_en, name_ml, name_ta, category, location_address, room_categories, room_views, room_facilities, food_availability, food_types, sightseeing_services, additional_facilities, indicative_price, featured_image, special_instructions, is_active)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
            ");
            $ins->execute([
                $destId, $acc['name_en'], $acc['name_ml'], $acc['name_ta'], $acc['category'],
                $acc['location_address'], $acc['room_categories'], $acc['room_views'],
                $acc['room_facilities'], $acc['food_availability'], $acc['food_types'],
                $acc['sightseeing_services'], $acc['additional_facilities'],
                $acc['indicative_price'], $acc['featured_image'], $acc['special_instructions']
            ]);
            echo "Seeded accommodation: {$acc['name_en']}\n";
        }
    }
}

// -------------------------------------------------------------
// 13. SEED CORPORATE VENUES (Conference, Seminar, Convention)
// -------------------------------------------------------------
$venuesSeed = [
    [
        'venue_type' => 'convention_centre',
        'name_en' => 'Grand Travancore International Convention Centre',
        'name_ml' => 'ഗ്രാൻഡ് ട്രാവൻകൂർ ഇന്റർനാഷണൽ കൺവെൻഷൻ സെന്റർ',
        'name_ta' => 'கிராண்ட் திருவிதாங்கூர் சர்வதேச மாநாட்டு மையம்',
        'location_address' => 'NH-66 Bypass, Thiruvananthapuram / Ernakulam Corridor, Kerala',
        'capacity_min' => 200,
        'capacity_max' => 2500,
        'seating_arrangements' => 'Theater, Classroom, Banquet, U-Shape, Boardroom',
        'audio_visual_facilities' => '4K Ultra-HD LED Video Wall (32ft x 14ft), Line Array Acoustics, Wireless Shure Mics, Simultaneous Multi-language Translation System, High-speed Gigabit Fiber, Stage Lighting',
        'catering_options' => 'Full-service banquet kitchen, Executive delegate dining, Hi-Tea lounges, Multi-cuisine live counters',
        'accommodation_available' => 1,
        'accommodation_details' => '80 luxury rooms & executive suites directly adjacent to the convention hall.',
        'parking_capacity' => 600,
        'starting_price' => 75000.00,
        'featured_image' => 'https://images.unsplash.com/photo-1511795409834-ef04bbd61622?auto=format&fit=crop&w=1200&q=80',
        'description_en' => 'Kerala’s flagship venue for annual corporate summits, mega dealer conventions, international symposiums, and luxury industrial expos.'
    ],
    [
        'venue_type' => 'conference_hall',
        'name_en' => 'Munnar Highland Cloud Executive Conference Hall',
        'name_ml' => 'മൂന്നാർ ഹൈലാൻഡ് എക്സിക്യൂട്ടീവ് കോൺഫറൻസ് ഹാൾ',
        'name_ta' => 'மூணார் ஹைலேண்ட் எக்ஸிகியூட்டிவ் மாநாட்டு அரங்கம்',
        'location_address' => 'Chithirapuram Summit Ridge, Munnar, Kerala',
        'capacity_min' => 30,
        'capacity_max' => 250,
        'seating_arrangements' => 'Classroom, U-Shape, Boardroom, Theater, Cluster',
        'audio_visual_facilities' => 'Interactive Smart Touch Displays, Dual Laser HD Projectors, Wireless Podiums, Hybrid Zoom/Teams Video Conferencing Bar, Soundproof Acoustics',
        'catering_options' => 'Curated executive buffet lunch, Continuous plantation tea/coffee service, Gala dinner with barbecue',
        'accommodation_available' => 1,
        'accommodation_details' => '55 valley-view chalets available for residential corporate leadership offsites.',
        'parking_capacity' => 80,
        'starting_price' => 35000.00,
        'featured_image' => 'https://images.unsplash.com/photo-1517457373958-b7bdd4587205?auto=format&fit=crop&w=1200&q=80',
        'description_en' => 'An inspiring mountain altitude venue tailored for corporate leadership summits, annual review offsites, and board retreats.'
    ],
    [
        'venue_type' => 'seminar_hall',
        'name_en' => 'Kochi Marina Academic & Corporate Seminar Hall',
        'name_ml' => 'കൊച്ചി മറീന സെമിനാർ ഹാൾ',
        'name_ta' => 'கொச்சி மெரினா கருத்தரங்கு அரங்கம்',
        'location_address' => 'Marine Drive, Kochi, Ernakulam, Kerala 682031',
        'capacity_min' => 20,
        'capacity_max' => 180,
        'seating_arrangements' => 'Classroom, Theater, U-Shape, Interactive Workshop Circle',
        'audio_visual_facilities' => 'Motorized Projection Screen, Dolby Surround, Wireless Lapel Microphones, Dual Display Monitors for Presenters, High-speed Wi-Fi',
        'catering_options' => 'Morning snacks & fresh South Indian filter coffee, Buffet corporate lunch, Evening refreshments',
        'accommodation_available' => 0,
        'accommodation_details' => 'Tie-ups with premium 4-star hotels within 500 metres.',
        'parking_capacity' => 50,
        'starting_price' => 18000.00,
        'featured_image' => 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?auto=format&fit=crop&w=1200&q=80',
        'description_en' => 'Centrally positioned in Kochi for corporate training workshops, technical seminars, investor pitch decks, and executive masterclasses.'
    ]
];

foreach ($venuesSeed as $v) {
    $chk = $db->prepare("SELECT id FROM corporate_venues WHERE name_en = ?");
    $chk->execute([$v['name_en']]);
    if (!$chk->fetch()) {
        $ins = $db->prepare("
            INSERT INTO corporate_venues
            (venue_type, name_en, name_ml, name_ta, location_address, capacity_min, capacity_max, seating_arrangements, audio_visual_facilities, catering_options, accommodation_available, accommodation_details, parking_capacity, starting_price, featured_image, description_en, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
        ");
        $ins->execute([
            $v['venue_type'], $v['name_en'], $v['name_ml'], $v['name_ta'], $v['location_address'],
            $v['capacity_min'], $v['capacity_max'], $v['seating_arrangements'],
            $v['audio_visual_facilities'], $v['catering_options'], $v['accommodation_available'],
            $v['accommodation_details'], $v['parking_capacity'], $v['starting_price'],
            $v['featured_image'], $v['description_en']
        ]);
        echo "Seeded corporate venue: {$v['name_en']}\n";
    }
}

// -------------------------------------------------------------
// 14. SYNC TO SQLITE FALLBACK DATABASE (if exists and different)
// -------------------------------------------------------------
$sqliteFile = dirname(__DIR__) . '/database/midhila_fallback.sqlite';
if ($driver !== 'sqlite' && file_exists($sqliteFile)) {
    echo "Syncing schema to fallback SQLite database: {$sqliteFile}\n";
    try {
        $sqldb = new PDO("sqlite:" . $sqliteFile);
        $sqldb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Re-run create table statements on sqlite
        $sqldb->exec("CREATE TABLE IF NOT EXISTS `tourism_categories` (`id` INTEGER PRIMARY KEY AUTOINCREMENT, `slug` TEXT UNIQUE, `name_en` TEXT, `name_ml` TEXT, `name_ta` TEXT, `tagline_en` TEXT, `tagline_ml` TEXT, `tagline_ta` TEXT, `description_en` TEXT, `description_ml` TEXT, `description_ta` TEXT, `icon_svg` TEXT, `featured_image` TEXT, `is_kerala_focus` INTEGER, `display_order` INTEGER, `is_active` INTEGER, `created_at` TEXT, `updated_at` TEXT)");
        $sqldb->exec("CREATE TABLE IF NOT EXISTS `accommodations` (`id` INTEGER PRIMARY KEY AUTOINCREMENT, `destination_id` INTEGER, `tourism_category_id` INTEGER, `name_en` TEXT, `name_ml` TEXT, `name_ta` TEXT, `category` TEXT, `location_address` TEXT, `contact_person` TEXT, `contact_phone` TEXT, `contact_email` TEXT, `room_categories` TEXT, `room_views` TEXT, `room_facilities` TEXT, `food_availability` TEXT, `food_types` TEXT, `sightseeing_services` TEXT, `additional_facilities` TEXT, `indicative_price` REAL, `price_unit` TEXT, `featured_image` TEXT, `photos_json` TEXT, `special_instructions` TEXT, `is_active` INTEGER, `created_at` TEXT, `updated_at` TEXT)");
        $sqldb->exec("CREATE TABLE IF NOT EXISTS `corporate_venues` (`id` INTEGER PRIMARY KEY AUTOINCREMENT, `destination_id` INTEGER, `venue_type` TEXT, `name_en` TEXT, `name_ml` TEXT, `name_ta` TEXT, `location_address` TEXT, `capacity_min` INTEGER, `capacity_max` INTEGER, `seating_arrangements` TEXT, `audio_visual_facilities` TEXT, `catering_options` TEXT, `accommodation_available` INTEGER, `accommodation_details` TEXT, `parking_capacity` INTEGER, `starting_price` REAL, `featured_image` TEXT, `photos_json` TEXT, `description_en` TEXT, `description_ml` TEXT, `description_ta` TEXT, `contact_phone` TEXT, `contact_email` TEXT, `is_active` INTEGER, `created_at` TEXT, `updated_at` TEXT)");
        $sqldb->exec("CREATE TABLE IF NOT EXISTS `customers` (`id` INTEGER PRIMARY KEY AUTOINCREMENT, `full_name` TEXT, `email` TEXT, `phone` TEXT, `whatsapp` TEXT, `city` TEXT, `state` TEXT, `country` TEXT, `preferred_language` TEXT, `total_enquiries` INTEGER, `total_bookings` INTEGER, `internal_notes` TEXT, `created_at` TEXT, `updated_at` TEXT)");
        $sqldb->exec("CREATE TABLE IF NOT EXISTS `availability_checks` (`id` INTEGER PRIMARY KEY AUTOINCREMENT, `enquiry_id` INTEGER, `admin_id` INTEGER, `accommodation_id` INTEGER, `provider_name` TEXT, `contact_person` TEXT, `contacted_at` TEXT, `response_status` TEXT, `available_room_category` TEXT, `confirmed_room_quantity` INTEGER, `quoted_price` REAL, `provider_remarks` TEXT, `admin_remarks` TEXT, `created_at` TEXT)");
        $sqldb->exec("CREATE TABLE IF NOT EXISTS `customer_communications` (`id` INTEGER PRIMARY KEY AUTOINCREMENT, `customer_id` INTEGER, `enquiry_id` INTEGER, `admin_id` INTEGER, `channel` TEXT, `subject` TEXT, `message` TEXT, `status` TEXT, `created_at` TEXT)");
        $sqldb->exec("CREATE TABLE IF NOT EXISTS `booking_status_history` (`id` INTEGER PRIMARY KEY AUTOINCREMENT, `enquiry_id` INTEGER, `admin_id` INTEGER, `old_status` TEXT, `new_status` TEXT, `notes` TEXT, `created_at` TEXT)");
        $sqldb->exec("CREATE TABLE IF NOT EXISTS `email_logs` (`id` INTEGER PRIMARY KEY AUTOINCREMENT, `recipient_email` TEXT, `recipient_name` TEXT, `subject` TEXT, `message_body` TEXT, `enquiry_id` INTEGER, `status` TEXT, `sent_at` TEXT)");
        $sqldb->exec("CREATE TABLE IF NOT EXISTS `translations` (`id` INTEGER PRIMARY KEY AUTOINCREMENT, `translation_key` TEXT UNIQUE, `group_name` TEXT, `en_text` TEXT, `ml_text` TEXT, `ta_text` TEXT, `updated_at` TEXT)");
        
        echo "SQLite fallback tables verified successfully.\n";
    } catch (Exception $sqle) {
        echo "SQLite fallback sync notice: " . $sqle->getMessage() . "\n";
    }
}

echo "\nMigration V2 completed successfully!\n";
