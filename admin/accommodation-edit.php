<?php
/**
 * MIDHILA VOYAGES — Add / Edit Accommodation Form
 * Supports destination linking, room categories, room views, facilities, food types, and pricing.
 */

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/database.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/csrf.php';

require_admin_login();
require_role(['super_admin', 'travel_manage']);

$db = getDB();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$accommodation = null;

if ($id > 0) {
    $stmt = $db->prepare("SELECT * FROM `accommodations` WHERE id = ?");
    $stmt->execute([$id]);
    $accommodation = $stmt->fetch();
    if (!$accommodation) {
        set_flash_message('error', 'Accommodation record not found.');
        header('Location: ' . ADMIN_URL . '/accommodations.php');
        exit;
    }
}

// Destinations list
$destinations = $db->query("SELECT id, name FROM `destinations` WHERE is_published = 1 ORDER BY name ASC")->fetchAll();
$tourismCategories = $db->query("SELECT id, name_en FROM `tourism_categories` WHERE is_active = 1 ORDER BY display_order ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf_token()) {
        $destinationId = (int)($_POST['destination_id'] ?? 0);
        $tourismCategoryId = !empty($_POST['tourism_category_id']) ? (int)$_POST['tourism_category_id'] : null;
        $nameEn = trim($_POST['name_en'] ?? '');
        $nameMl = trim($_POST['name_ml'] ?? '');
        $nameTa = trim($_POST['name_ta'] ?? '');
        $category = trim($_POST['category'] ?? 'Resort');
        $locationAddress = trim($_POST['location_address'] ?? '');
        $contactPerson = trim($_POST['contact_person'] ?? '');
        $contactPhone = trim($_POST['contact_phone'] ?? '');
        $contactEmail = trim($_POST['contact_email'] ?? '');
        $roomCategories = trim($_POST['room_categories'] ?? '');
        $roomViews = trim($_POST['room_views'] ?? '');
        $roomFacilities = trim($_POST['room_facilities'] ?? '');
        $foodAvailability = trim($_POST['food_availability'] ?? 'with_food');
        $foodTypes = trim($_POST['food_types'] ?? '');
        $sightseeingServices = trim($_POST['sightseeing_services'] ?? 'with_sightseeing');
        $additionalFacilities = trim($_POST['additional_facilities'] ?? '');
        $indicativePrice = (float)($_POST['indicative_price'] ?? 0.00);
        $priceUnit = trim($_POST['price_unit'] ?? 'per night');
        $featuredImage = trim($_POST['featured_image'] ?? '');
        $specialInstructions = trim($_POST['special_instructions'] ?? '');
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if (empty($nameEn)) {
            set_flash_message('error', 'Accommodation Name (English) is required.');
        } else {
            if ($id > 0) {
                // Update
                $upd = $db->prepare("
                    UPDATE `accommodations` SET
                        `destination_id` = ?, `tourism_category_id` = ?, `name_en` = ?, `name_ml` = ?, `name_ta` = ?,
                        `category` = ?, `location_address` = ?, `contact_person` = ?, `contact_phone` = ?, `contact_email` = ?,
                        `room_categories` = ?, `room_views` = ?, `room_facilities` = ?, `food_availability` = ?, `food_types` = ?,
                        `sightseeing_services` = ?, `additional_facilities` = ?, `indicative_price` = ?, `price_unit` = ?,
                        `featured_image` = ?, `special_instructions` = ?, `is_active` = ?, `updated_at` = ?
                    WHERE `id` = ?
                ");
                $upd->execute([
                    $destinationId, $tourismCategoryId, $nameEn, $nameMl, $nameTa,
                    $category, $locationAddress, $contactPerson, $contactPhone, $contactEmail,
                    $roomCategories, $roomViews, $roomFacilities, $foodAvailability, $foodTypes,
                    $sightseeingServices, $additionalFacilities, $indicativePrice, $priceUnit,
                    $featuredImage, $specialInstructions, $isActive, date('Y-m-d H:i:s'), $id
                ]);
                set_flash_message('success', 'Accommodation updated successfully.');
            } else {
                // Insert
                $ins = $db->prepare("
                    INSERT INTO `accommodations` 
                    (`destination_id`, `tourism_category_id`, `name_en`, `name_ml`, `name_ta`,
                     `category`, `location_address`, `contact_person`, `contact_phone`, `contact_email`,
                     `room_categories`, `room_views`, `room_facilities`, `food_availability`, `food_types`,
                     `sightseeing_services`, `additional_facilities`, `indicative_price`, `price_unit`,
                     `featured_image`, `special_instructions`, `is_active`, `created_at`, `updated_at`)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $now = date('Y-m-d H:i:s');
                $ins->execute([
                    $destinationId, $tourismCategoryId, $nameEn, $nameMl, $nameTa,
                    $category, $locationAddress, $contactPerson, $contactPhone, $contactEmail,
                    $roomCategories, $roomViews, $roomFacilities, $foodAvailability, $foodTypes,
                    $sightseeingServices, $additionalFacilities, $indicativePrice, $priceUnit,
                    $featuredImage, $specialInstructions, $isActive, $now, $now
                ]);
                set_flash_message('success', 'Accommodation registered successfully.');
            }
            header('Location: ' . ADMIN_URL . '/accommodations.php');
            exit;
        }
    }
}

$adminTitle = ($id > 0 ? 'Edit Accommodation' : 'Register Accommodation') . ' | MIDHILA VOYAGES';
$adminHeading = $id > 0 ? ('Edit Accommodation: ' . e($accommodation['name_en'])) : 'Register New Accommodation';

require_once __DIR__ . '/includes/admin-header.php';
?>

<div style="margin-bottom:20px;">
  <a href="<?= ADMIN_URL ?>/accommodations.php" class="admin-btn admin-btn-outline">&larr; Back to Accommodations</a>
</div>

<div class="admin-card" style="max-width:960px;">
  <form method="POST" action="">
    <?= csrf_field() ?>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
      <div>
        <label>Property Name (English) *</label>
        <input type="text" name="name_en" required value="<?= e($accommodation['name_en'] ?? '') ?>" placeholder="e.g. Blankate Tea Plantation Resort">
      </div>
      <div>
        <label>Destination Location *</label>
        <select name="destination_id" required>
          <option value="">Select Destination</option>
          <?php foreach ($destinations as $dst): ?>
            <option value="<?= (int)$dst['id'] ?>" <?= ((int)($accommodation['destination_id'] ?? 0) === (int)$dst['id']) ? 'selected' : '' ?>>
              <?= e($dst['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <!-- Multilingual Names -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
      <div>
        <label>Property Name (Malayalam / മലയാളം)</label>
        <input type="text" name="name_ml" value="<?= e($accommodation['name_ml'] ?? '') ?>" placeholder="മലയാളം നാമം">
      </div>
      <div>
        <label>Property Name (Tamil / தமிழ்)</label>
        <input type="text" name="name_ta" value="<?= e($accommodation['name_ta'] ?? '') ?>" placeholder="தமிழ் பெயர்">
      </div>
    </div>

    <!-- Category & Tourism Category -->
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px;margin-bottom:20px;">
      <div>
        <label>Property Category</label>
        <select name="category">
          <?php 
            $cats = ['Resort', 'Hotel', 'Premium Houseboat', 'Ayurveda Retreat', 'Homestay', 'Eco Lodge'];
            $curCat = $accommodation['category'] ?? 'Resort';
            foreach ($cats as $c):
          ?>
            <option value="<?= e($c) ?>" <?= ($curCat === $c) ? 'selected' : '' ?>><?= e($c) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label>Kerala Tourism Category</label>
        <select name="tourism_category_id">
          <option value="">Optional Tourism Category</option>
          <?php foreach ($tourismCategories as $tc): ?>
            <option value="<?= (int)$tc['id'] ?>" <?= ((int)($accommodation['tourism_category_id'] ?? 0) === (int)$tc['id']) ? 'selected' : '' ?>>
              <?= e($tc['name_en']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label>Indicative Price (₹) &amp; Unit</label>
        <div style="display:flex;gap:8px;">
          <input type="number" step="0.01" name="indicative_price" value="<?= e($accommodation['indicative_price'] ?? '0.00') ?>" placeholder="4500">
          <input type="text" name="price_unit" value="<?= e($accommodation['price_unit'] ?? 'per night') ?>" placeholder="per night" style="width:120px;">
        </div>
      </div>
    </div>

    <!-- Address & Contacts -->
    <div style="margin-bottom:20px;">
      <label>Full Address / Location Coordinates</label>
      <input type="text" name="location_address" value="<?= e($accommodation['location_address'] ?? '') ?>" placeholder="e.g. Tea County Road, Munnar, Idukki, Kerala 685612">
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px;margin-bottom:20px;">
      <div>
        <label>Contact Person / Manager</label>
        <input type="text" name="contact_person" value="<?= e($accommodation['contact_person'] ?? '') ?>" placeholder="e.g. Sreekumar">
      </div>
      <div>
        <label>Contact Phone</label>
        <input type="tel" name="contact_phone" value="<?= e($accommodation['contact_phone'] ?? '') ?>" placeholder="+91 98470 12345">
      </div>
      <div>
        <label>Contact Email</label>
        <input type="email" name="contact_email" value="<?= e($accommodation['contact_email'] ?? '') ?>" placeholder="stay@example.com">
      </div>
    </div>

    <!-- Rooms, Views & Facilities -->
    <div style="background:#F8FAFC;padding:20px;border-radius:10px;border:1px solid #E2E8F0;margin-bottom:20px;">
      <h4 style="font-size:0.95rem;font-weight:700;color:#0F172A;margin-bottom:14px;">Rooms, Views &amp; Amenities Configuration</h4>
      
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:16px;">
        <div>
          <label>Available Room Views (comma-separated)</label>
          <input type="text" name="room_views" value="<?= e($accommodation['room_views'] ?? 'Mountain-view, Tea plantation-view, Valley-view') ?>" placeholder="Mountain-view, Valley-view, Tea plantation-view, Lake-view, Beach-view">
        </div>
        <div>
          <label>Room Categories (comma-separated)</label>
          <input type="text" name="room_categories" value="<?= e($accommodation['room_categories'] ?? 'Standard, Deluxe, Premium Suite, Luxury Villa') ?>" placeholder="Standard, Deluxe, Premium, Luxury Villa">
        </div>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:16px;">
        <div>
          <label>Food Availability</label>
          <select name="food_availability">
            <option value="with_food" <?= (($accommodation['food_availability'] ?? '') === 'with_food') ? 'selected' : '' ?>>With Food (All Meals)</option>
            <option value="breakfast_included" <?= (($accommodation['food_availability'] ?? '') === 'breakfast_included') ? 'selected' : '' ?>>Breakfast Only</option>
            <option value="breakfast_dinner" <?= (($accommodation['food_availability'] ?? '') === 'breakfast_dinner') ? 'selected' : '' ?>>Breakfast and Dinner</option>
            <option value="without_food" <?= (($accommodation['food_availability'] ?? '') === 'without_food') ? 'selected' : '' ?>>Room Only (Without Food)</option>
          </select>
        </div>
        <div>
          <label>Food Types Available</label>
          <input type="text" name="food_types" value="<?= e($accommodation['food_types'] ?? 'Vegetarian, Non-vegetarian, Jain on request') ?>" placeholder="Vegetarian, Non-vegetarian, Vegan">
        </div>
      </div>

      <div style="margin-bottom:16px;">
        <label>Property Facilities &amp; Amenities (comma-separated)</label>
        <input type="text" name="additional_facilities" value="<?= e($accommodation['additional_facilities'] ?? 'Free Wi-Fi, Air conditioning, Private Balcony, Swimming Pool, Parking, In-house Restaurant, Room Service') ?>" placeholder="Wi-Fi, Swimming Pool, Balcony, Spa, AC, Restaurant">
      </div>
    </div>

    <!-- Featured Image & Special Instructions -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
      <div>
        <label>Featured Image URL</label>
        <input type="text" name="featured_image" value="<?= e($accommodation['featured_image'] ?? '') ?>" placeholder="https://images.unsplash.com/...">
      </div>
      <div>
        <label>Special Instructions / Booking Policies</label>
        <input type="text" name="special_instructions" value="<?= e($accommodation['special_instructions'] ?? '') ?>" placeholder="e.g. Check-in 1:00 PM, Check-out 11:00 AM">
      </div>
    </div>

    <div style="margin-bottom:24px;">
      <label style="display:inline-flex;align-items:center;gap:8px;font-weight:600;cursor:pointer;">
        <input type="checkbox" name="is_active" value="1" <?= (!isset($accommodation['is_active']) || $accommodation['is_active']) ? 'checked' : '' ?>>
        <span>Publish this Accommodation on Public Website</span>
      </label>
    </div>

    <div style="display:flex;gap:12px;">
      <button type="submit" class="admin-btn admin-btn-primary">
        <?= $id > 0 ? 'Update Accommodation' : 'Register Accommodation' ?>
      </button>
      <a href="<?= ADMIN_URL ?>/accommodations.php" class="admin-btn admin-btn-outline">Cancel</a>
    </div>

  </form>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
