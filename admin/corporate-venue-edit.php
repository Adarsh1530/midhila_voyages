<?php
/**
 * MIDHILA VOYAGES — Add / Edit Corporate Venue Form
 * Manages Conference Halls, Seminar Halls, and Convention Centres with seating, AV, and catering specifications.
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
$venue = null;

if ($id > 0) {
    $stmt = $db->prepare("SELECT * FROM `corporate_venues` WHERE id = ?");
    $stmt->execute([$id]);
    $venue = $stmt->fetch();
    if (!$venue) {
        set_flash_message('error', 'Venue record not found.');
        header('Location: ' . ADMIN_URL . '/corporate-venues.php');
        exit;
    }
}

$destinations = $db->query("SELECT id, name FROM `destinations` WHERE is_published = 1 ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf_token()) {
        $venueType = trim($_POST['venue_type'] ?? 'conference_hall');
        $destinationId = !empty($_POST['destination_id']) ? (int)$_POST['destination_id'] : null;
        $nameEn = trim($_POST['name_en'] ?? '');
        $nameMl = trim($_POST['name_ml'] ?? '');
        $nameTa = trim($_POST['name_ta'] ?? '');
        $city = trim($_POST['city'] ?? 'Kochi');
        $locationAddress = trim($_POST['location_address'] ?? '');
        $contactPerson = trim($_POST['contact_person'] ?? '');
        $contactPhone = trim($_POST['contact_phone'] ?? '');
        $contactEmail = trim($_POST['contact_email'] ?? '');
        $capacityTheater = (int)($_POST['capacity_theater'] ?? 100);
        $capacityClassroom = (int)($_POST['capacity_classroom'] ?? 60);
        $capacityRoundTable = (int)($_POST['capacity_round_table'] ?? 50);
        $avSpecs = trim($_POST['av_equipment_specs'] ?? '');
        $cateringOptions = trim($_POST['catering_options'] ?? '');
        $indicativePrice = (float)($_POST['indicative_price'] ?? 0.00);
        $priceUnit = trim($_POST['price_unit'] ?? 'per day');
        $featuredImage = trim($_POST['featured_image'] ?? '');
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if (empty($nameEn)) {
            set_flash_message('error', 'Venue Name (English) is required.');
        } else {
            if ($id > 0) {
                $upd = $db->prepare("
                    UPDATE `corporate_venues` SET
                        `venue_type` = ?, `destination_id` = ?, `name_en` = ?, `name_ml` = ?, `name_ta` = ?,
                        `city` = ?, `location_address` = ?, `contact_person` = ?, `contact_phone` = ?, `contact_email` = ?,
                        `capacity_theater` = ?, `capacity_classroom` = ?, `capacity_round_table` = ?,
                        `av_equipment_specs` = ?, `catering_options` = ?, `indicative_price` = ?, `price_unit` = ?,
                        `featured_image` = ?, `is_active` = ?, `updated_at` = ?
                    WHERE `id` = ?
                ");
                $upd->execute([
                    $venueType, $destinationId, $nameEn, $nameMl, $nameTa,
                    $city, $locationAddress, $contactPerson, $contactPhone, $contactEmail,
                    $capacityTheater, $capacityClassroom, $capacityRoundTable,
                    $avSpecs, $cateringOptions, $indicativePrice, $priceUnit,
                    $featuredImage, $isActive, date('Y-m-d H:i:s'), $id
                ]);
                set_flash_message('success', 'Corporate venue updated successfully.');
            } else {
                $ins = $db->prepare("
                    INSERT INTO `corporate_venues`
                    (`venue_type`, `destination_id`, `name_en`, `name_ml`, `name_ta`,
                     `city`, `location_address`, `contact_person`, `contact_phone`, `contact_email`,
                     `capacity_theater`, `capacity_classroom`, `capacity_round_table`,
                     `av_equipment_specs`, `catering_options`, `indicative_price`, `price_unit`,
                     `featured_image`, `is_active`, `created_at`, `updated_at`)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $now = date('Y-m-d H:i:s');
                $ins->execute([
                    $venueType, $destinationId, $nameEn, $nameMl, $nameTa,
                    $city, $locationAddress, $contactPerson, $contactPhone, $contactEmail,
                    $capacityTheater, $capacityClassroom, $capacityRoundTable,
                    $avSpecs, $cateringOptions, $indicativePrice, $priceUnit,
                    $featuredImage, $isActive, $now, $now
                ]);
                set_flash_message('success', 'Corporate venue registered successfully.');
            }
            header('Location: ' . ADMIN_URL . '/corporate-venues.php');
            exit;
        }
    }
}

$adminTitle = ($id > 0 ? 'Edit Corporate Venue' : 'Register Corporate Venue') . ' | MIDHILA VOYAGES';
$adminHeading = $id > 0 ? ('Edit Venue: ' . e($venue['name_en'])) : 'Register New Corporate Venue';

require_once __DIR__ . '/includes/admin-header.php';
?>

<div style="margin-bottom:20px;">
  <a href="<?= ADMIN_URL ?>/corporate-venues.php" class="admin-btn admin-btn-outline">&larr; Back to Corporate Venues</a>
</div>

<div class="admin-card" style="max-width:960px;">
  <form method="POST" action="">
    <?= csrf_field() ?>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
      <div>
        <label>Venue Name (English) *</label>
        <input type="text" name="name_en" required value="<?= e($venue['name_en'] ?? '') ?>" placeholder="e.g. Grand Horizon Convention Centre">
      </div>
      <div>
        <label>Venue Category *</label>
        <select name="venue_type" required>
          <option value="convention_centre" <?= (($venue['venue_type'] ?? '') === 'convention_centre') ? 'selected' : '' ?>>Convention Centre (Large Scale)</option>
          <option value="conference_hall" <?= (($venue['venue_type'] ?? '') === 'conference_hall') ? 'selected' : '' ?>>Conference Hall (Corporate Boardroom / Summit)</option>
          <option value="seminar_hall" <?= (($venue['venue_type'] ?? '') === 'seminar_hall') ? 'selected' : '' ?>>Seminar Hall (Workshops &amp; Academic)</option>
        </select>
      </div>
    </div>

    <!-- Multilingual Names -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
      <div>
        <label>Venue Name (Malayalam / മലയാളം)</label>
        <input type="text" name="name_ml" value="<?= e($venue['name_ml'] ?? '') ?>" placeholder="മലയാളം നാമം">
      </div>
      <div>
        <label>Venue Name (Tamil / தமிழ்)</label>
        <input type="text" name="name_ta" value="<?= e($venue['name_ta'] ?? '') ?>" placeholder="தமிழ் பெயர்">
      </div>
    </div>

    <!-- Location & City -->
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px;margin-bottom:20px;">
      <div>
        <label>City / Hub</label>
        <input type="text" name="city" value="<?= e($venue['city'] ?? 'Kochi') ?>" placeholder="e.g. Kochi, Trivandrum, Kozhikode">
      </div>
      <div>
        <label>Nearby Destination Linking</label>
        <select name="destination_id">
          <option value="">None / Regional</option>
          <?php foreach ($destinations as $dst): ?>
            <option value="<?= (int)$dst['id'] ?>" <?= ((int)($venue['destination_id'] ?? 0) === (int)$dst['id']) ? 'selected' : '' ?>>
              <?= e($dst['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label>Base Price (₹) &amp; Unit</label>
        <div style="display:flex;gap:8px;">
          <input type="number" step="0.01" name="indicative_price" value="<?= e($venue['indicative_price'] ?? '25000') ?>" placeholder="25000">
          <input type="text" name="price_unit" value="<?= e($venue['price_unit'] ?? 'per day') ?>" placeholder="per day" style="width:110px;">
        </div>
      </div>
    </div>

    <div style="margin-bottom:20px;">
      <label>Full Venue Address</label>
      <input type="text" name="location_address" value="<?= e($venue['location_address'] ?? '') ?>" placeholder="e.g. Marine Drive, Ernakulam, Kerala 682031">
    </div>

    <!-- Capacity Specs -->
    <div style="background:#F8FAFC;padding:20px;border-radius:10px;border:1px solid #E2E8F0;margin-bottom:20px;">
      <h4 style="font-size:0.95rem;font-weight:700;color:#0F172A;margin-bottom:14px;">Seating Capacities &amp; Audio-Visual Capabilities</h4>
      
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:18px;margin-bottom:16px;">
        <div>
          <label>Theater Capacity (Seats)</label>
          <input type="number" name="capacity_theater" value="<?= (int)($venue['capacity_theater'] ?? 300) ?>" min="10">
        </div>
        <div>
          <label>Classroom Capacity (Seats)</label>
          <input type="number" name="capacity_classroom" value="<?= (int)($venue['capacity_classroom'] ?? 150) ?>" min="10">
        </div>
        <div>
          <label>Round Table / Banquet (Seats)</label>
          <input type="number" name="capacity_round_table" value="<?= (int)($venue['capacity_round_table'] ?? 120) ?>" min="10">
        </div>
      </div>

      <div style="margin-bottom:16px;">
        <label>Audio-Visual Equipment &amp; Technology Specs</label>
        <textarea name="av_equipment_specs" rows="3" placeholder="Dual 4K Laser Projectors, Motorized Screens, JBL Professional Sound System, Cordless &amp; Collar Mics, Stage Lighting, High-speed Wi-Fi, Hybrid Zoom Bridge"><?= e($venue['av_equipment_specs'] ?? 'Dual 4K Laser Projectors, Professional Line Array PA System, Wireless Microphones, Stage Podiums, High-speed Dedicated Fiber Wi-Fi') ?></textarea>
      </div>

      <div>
        <label>Catering &amp; Dining Facilities</label>
        <input type="text" name="catering_options" value="<?= e($venue['catering_options'] ?? 'In-house multi-cuisine buffet dining, Dedicated banquet lawn, Live refreshment stalls') ?>" placeholder="In-house buffet, Live counters, Hi-Tea catering">
      </div>
    </div>

    <!-- Contact Person -->
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px;margin-bottom:20px;">
      <div>
        <label>Venue Coordinator</label>
        <input type="text" name="contact_person" value="<?= e($venue['contact_person'] ?? '') ?>" placeholder="e.g. Renjith Kumar">
      </div>
      <div>
        <label>Coordinator Phone</label>
        <input type="tel" name="contact_phone" value="<?= e($venue['contact_phone'] ?? '') ?>" placeholder="+91 80750 21427">
      </div>
      <div>
        <label>Coordinator Email</label>
        <input type="email" name="contact_email" value="<?= e($venue['contact_email'] ?? '') ?>" placeholder="events@midhilavoyages.com">
      </div>
    </div>

    <div style="margin-bottom:20px;">
      <label>Featured Image URL</label>
      <input type="text" name="featured_image" value="<?= e($venue['featured_image'] ?? '') ?>" placeholder="https://images.unsplash.com/...">
    </div>

    <div style="margin-bottom:24px;">
      <label style="display:inline-flex;align-items:center;gap:8px;font-weight:600;cursor:pointer;">
        <input type="checkbox" name="is_active" value="1" <?= (!isset($venue['is_active']) || $venue['is_active']) ? 'checked' : '' ?>>
        <span>Publish this Venue on Public Website</span>
      </label>
    </div>

    <div style="display:flex;gap:12px;">
      <button type="submit" class="admin-btn admin-btn-primary">
        <?= $id > 0 ? 'Update Corporate Venue' : 'Register Corporate Venue' ?>
      </button>
      <a href="<?= ADMIN_URL ?>/corporate-venues.php" class="admin-btn admin-btn-outline">Cancel</a>
    </div>

  </form>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
