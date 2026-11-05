<?php
/**
 * MIDHILA VOYAGES — Customised Tour Planner & Enquiry Management
 * Bespoke Personalized Travel Route & Package Builder
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';

$db = getDB();
$activeLang = current_lang();

// Get preselected destination if passed
$preDestId = isset($_GET['destination_id']) ? (int)$_GET['destination_id'] : 0;

$bookingSuccess = false;
$generatedRef = '';

// Handle Custom Tour Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_custom_tour'])) {
    if (verify_csrf_token()) {
        $fullName     = trim($_POST['full_name'] ?? '');
        $phone        = trim($_POST['phone'] ?? '');
        $email        = trim($_POST['email'] ?? '');
        $startLoc     = trim($_POST['starting_location'] ?? '');
        $destId       = (int)($_POST['destination_id'] ?? 0);
        $destName     = trim($_POST['destination_name'] ?? '');
        $startDate    = trim($_POST['departure_date'] ?? '');
        $durationDays = max(1, (int)($_POST['duration_days'] ?? 3));
        $adults       = max(1, (int)($_POST['adults_count'] ?? 2));
        $children     = max(0, (int)($_POST['children_count'] ?? 0));
        $travelType   = trim($_POST['travel_type'] ?? 'family');
        $transport    = trim($_POST['preferred_transport'] ?? 'Innova Crysta AC');
        $accPref      = trim($_POST['accommodation_preference'] ?? '4-Star Resort');
        $foodPref     = trim($_POST['food_preference'] ?? 'all_meals');
        $foodType     = trim($_POST['food_type'] ?? 'both');
        $sightPref    = trim($_POST['sightseeing_preference'] ?? 'private_vehicle');
        $budgetRange  = trim($_POST['budget_range'] ?? '₹30,000 - ₹50,000');
        $specialReqs  = trim($_POST['travel_requirements'] ?? '');
        $selectedPlacesJson = !empty($_POST['selected_places_json']) ? trim($_POST['selected_places_json']) : null;
        $itineraryContent   = !empty($_POST['itinerary_content']) ? trim($_POST['itinerary_content']) : null;
        $aiGenerated        = !empty($itineraryContent) ? 1 : 0;

        if (!empty($fullName) && !empty($phone)) {
            if ($destId > 0 && empty($destName)) {
                $dStmt = $db->prepare("SELECT name FROM destinations WHERE id = ?");
                $dStmt->execute([$destId]);
                $destName = $dStmt->fetchColumn() ?: 'Custom Route';
            }
            if (empty($destName)) {
                $destName = 'Kerala Custom Circuit';
            }

            // Generate unique reference
            $year = date('Y');
            $refCount = (int)$db->query("SELECT COUNT(*) FROM enquiries WHERE reference_no LIKE 'MV-{$year}-%'")->fetchColumn() + 1;
            $generatedRef = sprintf("MV-%s-%04d", $year, $refCount);

            $combinedReqs = "Preferred Transport: {$transport}\n"
                          . "Accommodation Category: {$accPref}\n"
                          . "Special Instructions: {$specialReqs}";

            $insStmt = $db->prepare("
                INSERT INTO enquiries
                (reference_no, full_name, email, phone, whatsapp, destination_id, destination_name,
                 enquiry_type, language, starting_location, departure_date, duration_days,
                 adults_count, children_count, travel_type, budget_range,
                 accommodation_preference, food_preference, food_type, sightseeing_preference,
                 travel_requirements, selected_places_json, itinerary_content, ai_generated,
                 status, workflow_status, created_at, updated_at)
                VALUES
                (?, ?, ?, ?, ?, ?, ?, 'custom_tour', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'new', 'new', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
            ");

            $insStmt->execute([
                $generatedRef, $fullName, $email, $phone, $phone, $destId ?: null, $destName,
                $activeLang, $startLoc, $startDate, $durationDays,
                $adults, $children, $travelType, $budgetRange,
                $accPref, $foodPref, $foodType, $sightPref,
                $combinedReqs, $selectedPlacesJson, $itineraryContent, $aiGenerated
            ]);
            $enquiryId = (int)$db->lastInsertId();

            // Customer registry
            try {
                $custCheck = $db->prepare("SELECT id FROM customers WHERE phone = ? LIMIT 1");
                $custCheck->execute([$phone]);
                $cust = $custCheck->fetch(PDO::FETCH_ASSOC);

                if ($cust) {
                    $db->prepare("UPDATE customers SET total_enquiries = total_enquiries + 1, updated_at = CURRENT_TIMESTAMP WHERE id = ?")
                       ->execute([$cust['id']]);
                } else {
                    $db->prepare("INSERT INTO customers (full_name, email, phone, whatsapp, preferred_language, total_enquiries, created_at) VALUES (?, ?, ?, ?, ?, 1, CURRENT_TIMESTAMP)")
                       ->execute([$fullName, $email, $phone, $phone, $activeLang]);
                }
            } catch (Exception $ce) {}

            // Status history
            try {
                $db->prepare("INSERT INTO booking_status_history (enquiry_id, new_status, notes, created_at) VALUES (?, 'new', 'Custom Tour Request received', CURRENT_TIMESTAMP)")
                   ->execute([$enquiryId]);
            } catch (Exception $he) {}

            // Customer acknowledgement email log
            $emailBody = "Dear " . htmlspecialchars($fullName) . ",\n\n"
                       . "Your customised tour enquiry (#{$generatedRef}) has been received successfully.\n\n"
                       . "Proposed Destination: {$destName}\n"
                       . "Duration: {$durationDays} Days for {$adults} Adults, {$children} Children\n"
                       . "Transport: {$transport}\n\n"
                       . "IMPORTANT NOTICE:\n"
                       . "Our team will verify the availability of your requested services and contact you shortly. This acknowledgement does not represent a confirmed booking.\n\n"
                       . "Warm Regards,\nMidhila Voyages Management Team";

            try {
                $db->prepare("INSERT INTO email_logs (recipient_email, recipient_name, subject, message_body, enquiry_id, status, sent_at) VALUES (?, ?, ?, ?, ?, 'sent', CURRENT_TIMESTAMP)")
                   ->execute([$email ?: 'guest@midhilavoyages.com', $fullName, "Custom Tour Enquiry Acknowledgement - #{$generatedRef}", $emailBody, $enquiryId]);
            } catch (Exception $ee) {}

            $bookingSuccess = true;
        } else {
            set_flash_message('error', 'Please fill in your name and phone number.');
        }
    }
}

// Fetch published destinations for dropdown
$destinations = $db->query("SELECT id, name, name_ml, name_ta, region_state FROM destinations WHERE is_published = 1 ORDER BY (region_state = 'Kerala') DESC, name ASC")->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = __t('nav_custom_tour') . ' Planner | Midhila Voyages';
$pageDescription = 'Craft your bespoke family holiday, elderly pilgrimage yatra, or honeymoon escape with private AC vehicles, tailored meal plans, and handpicked stays.';

require_once __DIR__ . '/includes/header.php';
?>

<!-- Leaflet Interactive Maps (OpenStreetMap Permitted Tiles) -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
.osm-cat-btn {
  padding: 6px 14px;
  font-size: 0.8rem;
  font-weight: 600;
  border-radius: 20px;
  border: 1px solid #CBD5E1;
  background: #FFFFFF;
  color: #475569;
  cursor: pointer;
  transition: all 0.2s ease;
}
.osm-cat-btn:hover, .osm-cat-btn.is-active {
  background: #0F172A;
  color: #FFFFFF;
  border-color: #0F172A;
}
.place-card-item {
  background: #FFFFFF;
  border: 1px solid #E2E8F0;
  border-radius: 8px;
  padding: 10px 12px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 8px;
}
.place-card-item:hover {
  border-color: #CBD5E1;
}
</style>

<!-- ACKNOWLEDGEMENT MODAL -->
<?php if ($bookingSuccess): ?>
<div style="position:fixed;inset:0;background:rgba(15,23,42,0.7);backdrop-filter:blur(6px);z-index:9999;display:flex;align-items:center;justify-content:center;padding:20px;">
  <div style="background:#FFFFFF;border-radius:16px;max-width:540px;width:100%;padding:36px;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);border:1px solid #E2E8F0;text-align:center;">
    <div style="width:64px;height:64px;border-radius:50%;background:#ECFDF5;color:#059669;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:2rem;">
      ✓
    </div>
    <h3 style="font-family:'Cinzel',serif;font-size:1.45rem;color:#0F172A;margin-bottom:8px;">
      <?= __t('ack_modal_title') ?>
    </h3>
    <div style="background:#F1F5F9;border:1px dashed #CBD5E1;border-radius:8px;padding:12px;margin-bottom:18px;">
      <span style="font-size:0.75rem;text-transform:uppercase;color:#64748B;font-weight:700;letter-spacing:0.06em;display:block;">
        <?= __t('ack_ref_label') ?>
      </span>
      <strong style="font-family:monospace;font-size:1.25rem;color:#0F172A;letter-spacing:0.05em;"><?= e($generatedRef) ?></strong>
    </div>
    <p style="font-size:0.9rem;color:#475569;line-height:1.65;margin-bottom:24px;text-align:left;background:#FFFBEB;border-left:4px solid #D97706;padding:12px 14px;border-radius:4px;">
      <strong>Notice:</strong> <?= __t('ack_modal_text') ?>
    </p>
    <a href="<?= BASE_URL ?>/index.php" class="btn-cinematic-primary" style="width:100%;justify-content:center;">
      <span>Back to Home</span>
    </a>
  </div>
</div>
<?php endif; ?>

<!-- HERO -->
<section style="background:#0F172A;color:#FFFFFF;padding:calc(var(--header-height, 86px) + 48px) 0 60px;text-align:center;position:relative;">
  <div class="container" style="max-width:820px;position:relative;z-index:2;">
    <span style="font-size:0.8rem;text-transform:uppercase;letter-spacing:0.12em;color:#C5A059;font-weight:700;display:block;margin-bottom:12px;">
      Tailored Itineraries &amp; Dedicated Concierge
    </span>
    <h1 style="font-family:'Cinzel',serif;font-size:clamp(2rem, 4vw, 3rem);color:#FFFFFF;margin-bottom:14px;">
      <?= __t('nav_custom_tour') ?>
    </h1>
    <p style="font-size:0.95rem;color:#CBD5E1;line-height:1.7;margin:0 auto 20px;">
      Don't want a rigid off-the-shelf package? Tell us your preferred dates, family size, dietary preferences, and travel rhythm. Our directors will craft a bespoke itinerary tailored just for you.
    </p>
    <div style="display:inline-flex;align-items:center;gap:6px;background:rgba(217,119,6,0.15);border:1px solid #D97706;padding:6px 16px;border-radius:20px;font-size:0.78rem;color:#FDE68A;">
      <span>ℹ️</span>
      <span><?= __t('disclaimer_notice') ?></span>
    </div>
  </div>
</section>

<!-- FORM WORKSPACE -->
<section style="padding:60px 0 90px;background:#FAFAFA;">
  <div class="container" style="max-width:860px;">
    
    <div style="background:#FFFFFF;border-radius:18px;padding:38px 42px;border:1px solid #E2E8F0;box-shadow:0 10px 35px rgba(15,23,42,0.06);">
      
      <form method="POST" action="<?= BASE_URL ?>/custom-tour.php">
        <?= csrf_field() ?>
        <input type="hidden" name="submit_custom_tour" value="1">

        <!-- 1. Customer Details -->
        <div style="margin-bottom:28px;">
          <h3 style="font-size:1.05rem;color:#0F172A;font-weight:700;margin-bottom:16px;border-bottom:1px solid #F1F5F9;padding-bottom:8px;">
            1. Your Contact Details
          </h3>
          
          <div class="form-grid-2col" style="margin-bottom:14px;">
            <div>
              <label style="display:block;font-size:0.82rem;font-weight:700;color:#334155;margin-bottom:4px;"><?= __t('form_full_name') ?></label>
              <input type="text" name="full_name" required placeholder="e.g. Sreejith Menon" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.9rem;">
            </div>
            <div>
              <label style="display:block;font-size:0.82rem;font-weight:700;color:#334155;margin-bottom:4px;"><?= __t('form_phone') ?></label>
              <input type="tel" name="phone" required placeholder="+91 9446154048" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.9rem;">
            </div>
          </div>

          <div class="form-grid-2col">
            <div>
              <label style="display:block;font-size:0.82rem;font-weight:700;color:#334155;margin-bottom:4px;"><?= __t('form_email') ?></label>
              <input type="email" name="email" placeholder="menon@example.com" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.9rem;">
            </div>
            <div>
              <label style="display:block;font-size:0.82rem;font-weight:700;color:#334155;margin-bottom:4px;"><?= __t('form_starting_location') ?></label>
              <input type="text" name="starting_location" placeholder="e.g. Cochin Airport / Bangalore / Chennai" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.9rem;">
            </div>
          </div>
        </div>

        <!-- 2. Destination & Dates -->
        <div style="margin-bottom:28px;">
          <h3 style="font-size:1.05rem;color:#0F172A;font-weight:700;margin-bottom:16px;border-bottom:1px solid #F1F5F9;padding-bottom:8px;">
            2. Destination &amp; Journey Dates
          </h3>

          <div class="form-grid-3col" style="margin-bottom:14px;">
            <div>
              <label style="display:block;font-size:0.82rem;font-weight:700;color:#334155;margin-bottom:4px;"><?= __t('form_select_destination') ?></label>
              <select name="destination_id" id="customTourDestSelect" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.9rem;background:#FFF;">
                <option value="0">Custom Multi-Destination Route</option>
                <?php foreach ($destinations as $d): ?>
                  <option value="<?= (int)$d['id'] ?>" <?= ((int)$d['id'] === $preDestId) ? 'selected' : '' ?>>
                    <?= loc($d, 'name') ?> (<?= e($d['region_state']) ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label style="display:block;font-size:0.82rem;font-weight:700;color:#334155;margin-bottom:4px;"><?= __t('form_start_date') ?></label>
              <input type="date" name="departure_date" required min="<?= date('Y-m-d') ?>" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.9rem;background:#FFF;">
            </div>
            <div>
              <label style="display:block;font-size:0.82rem;font-weight:700;color:#334155;margin-bottom:4px;"><?= __t('form_days') ?></label>
              <input type="number" name="duration_days" value="4" min="1" max="45" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.9rem;text-align:center;">
            </div>
          </div>

          <div class="form-grid-3col">
            <div>
              <label style="display:block;font-size:0.82rem;font-weight:700;color:#334155;margin-bottom:4px;"><?= __t('form_adults') ?></label>
              <input type="number" name="adults_count" value="2" min="1" max="100" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.9rem;text-align:center;">
            </div>
            <div>
              <label style="display:block;font-size:0.82rem;font-weight:700;color:#334155;margin-bottom:4px;"><?= __t('form_children') ?></label>
              <input type="number" name="children_count" value="0" min="0" max="50" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.9rem;text-align:center;">
            </div>
            <div>
              <label style="display:block;font-size:0.82rem;font-weight:700;color:#334155;margin-bottom:4px;"><?= __t('form_travel_type') ?></label>
              <select name="travel_type" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.9rem;background:#FFF;">
                <option value="family" selected>Family Holiday</option>
                <option value="pilgrimage">Pilgrimage / Temple Yatra</option>
                <option value="couple">Honeymoon / Couple</option>
                <option value="elderly">Senior Citizen First Care</option>
                <option value="group">Friends / Group</option>
                <option value="corporate">Corporate Offsite</option>
              </select>
            </div>
          </div>
        </div>

        <input type="hidden" name="selected_places_json" id="selectedPlacesInput" value="">
        <input type="hidden" name="itinerary_content" id="itineraryContentInput" value="">

        <!-- 2.5. Interactive OpenStreetMap Explorer & Mapped Places -->
        <div id="mapSection" style="margin-bottom:32px;background:#F8FAFC;border:1.5px solid #E2E8F0;border-radius:14px;padding:24px;">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;flex-wrap:wrap;gap:10px;">
            <div>
              <h3 style="font-size:1.05rem;color:#0F172A;font-weight:700;margin:0;display:flex;align-items:center;gap:6px;">
                <span>🗺️</span>
                <span>Destination Map &amp; Verified Places Explorer</span>
              </h3>
              <p style="font-size:0.8rem;color:#64748B;margin:2px 0 0;">
                Click any pin on the map or select below to customize your route.
              </p>
            </div>
            <div style="display:flex;gap:6px;flex-wrap:wrap;">
              <button type="button" class="osm-cat-btn is-active" onclick="filterMapCategory('all', this)">All</button>
              <button type="button" class="osm-cat-btn" onclick="filterMapCategory('hotel_resort', this)">Hotels &amp; Resorts</button>
              <button type="button" class="osm-cat-btn" onclick="filterMapCategory('dining', this)">Dining &amp; Cafes</button>
              <button type="button" class="osm-cat-btn" onclick="filterMapCategory('attraction', this)">Attractions</button>
            </div>
          </div>

          <!-- Leaflet Interactive Canvas -->
          <div id="osmMapContainer" style="height:340px;width:100%;border-radius:10px;border:1px solid #CBD5E1;box-shadow:inset 0 1px 3px rgba(0,0,0,0.05);position:relative;z-index:1;margin-bottom:16px;"></div>

          <!-- Selected Spots Chips -->
          <div id="selectedSpotsContainer" style="background:#FFFFFF;border:1px dashed #CBD5E1;border-radius:8px;padding:12px;margin-bottom:14px;display:none;">
            <div style="font-size:0.75rem;text-transform:uppercase;color:#64748B;font-weight:700;margin-bottom:6px;">
              Your Selected Places (<span id="selectedSpotsCount">0</span>):
            </div>
            <div id="selectedSpotsChips" style="display:flex;gap:6px;flex-wrap:wrap;"></div>
          </div>

          <!-- Grounded AI Itinerary Generator Trigger -->
          <div style="background:#FFFFFF;border:1px solid #E2E8F0;border-radius:10px;padding:18px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:14px;">
            <div>
              <div style="font-weight:700;color:#0F172A;font-size:0.92rem;display:flex;align-items:center;gap:6px;">
                <span>✨</span>
                <span>AI Travel Assistant: Grounded Route &amp; Day-by-Day Schedule</span>
              </div>
              <div style="font-size:0.78rem;color:#64748B;margin-top:2px;">
                Generates a realistic day-by-day plan strictly using verified accommodations and approved spots.
              </div>
            </div>
            <button type="button" id="btnGenerateAiItinerary" class="btn-cinematic-primary" style="padding:10px 18px;font-size:0.86rem;">
              <span>Generate AI Proposal</span>
            </button>
          </div>

          <!-- Live AI Itinerary Result Preview Box -->
          <div id="aiItineraryResultBox" style="display:none;margin-top:16px;background:#FFFFFF;border:1.5px solid #C5A059;border-radius:10px;padding:20px;position:relative;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;border-bottom:1px solid #F1F5F9;padding-bottom:10px;">
              <div style="font-weight:700;color:#0F172A;font-size:0.95rem;display:flex;align-items:center;gap:6px;">
                <span style="color:#C5A059;">★</span>
                <span>Tailored Grounded Itinerary Preview (<span id="aiModelBadge" style="font-size:0.75rem;font-weight:600;color:#64748B;"></span>)</span>
              </div>
              <button type="button" onclick="document.getElementById('aiItineraryResultBox').style.display='none'" style="background:none;border:none;cursor:pointer;color:#94A3B8;font-size:1.2rem;">&times;</button>
            </div>
            <div id="aiItineraryText" style="font-size:0.86rem;line-height:1.75;color:#334155;white-space:pre-wrap;max-height:360px;overflow-y:auto;"></div>
            <div style="margin-top:14px;background:#FFFBEB;border:1px solid #FDE68A;padding:10px 12px;border-radius:6px;font-size:0.78rem;color:#92400E;">
              <strong>Verification Policy:</strong> All hotel rooms, room categories, and final itinerary rates are strictly subject to manual verification and confirmation by the Midhila Voyages management team before digital booking confirmation.
            </div>
          </div>

        </div>

        <!-- 3. Preferences (Transport, Stays, Food, Sightseeing) -->
        <div style="margin-bottom:28px;">
          <h3 style="font-size:1.05rem;color:#0F172A;font-weight:700;margin-bottom:16px;border-bottom:1px solid #F1F5F9;padding-bottom:8px;">
            3. Travel &amp; Accommodation Preferences
          </h3>

          <div class="form-grid-2col" style="margin-bottom:14px;">
            <div>
              <label style="display:block;font-size:0.82rem;font-weight:700;color:#334155;margin-bottom:4px;">Preferred Private Vehicle</label>
              <select name="preferred_transport" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.9rem;background:#FFF;">
                <option value="Toyota Innova Crysta AC" selected>Toyota Innova Crysta AC (6-7 Seats)</option>
                <option value="Sedan (Dzire / Etios AC)">AC Sedan (Dzire / Etios - 1 to 3 Pax)</option>
                <option value="Tempo Traveller AC (12-17 Seats)">Tempo Traveller AC (12 to 17 Seats)</option>
                <option value="Urbania Luxury Van">Force Urbania Luxury Van (10-14 Seats)</option>
                <option value="Luxury Coach (26-45 Seats)">Luxury AC Bus / Coach (26 to 45 Seats)</option>
                <option value="No Vehicle Needed">Only Hotels &amp; Itinerary (No Transport)</option>
              </select>
            </div>
            <div>
              <label style="display:block;font-size:0.82rem;font-weight:700;color:#334155;margin-bottom:4px;">Accommodation Comfort Level</label>
              <select name="accommodation_preference" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.9rem;background:#FFF;">
                <option value="4-Star Luxury Resort" selected>4-Star / Premium Resort</option>
                <option value="5-Star Palace / Heritage Sanctuary">5-Star Palace &amp; Heritage Sanctuary</option>
                <option value="3-Star Clean Sanitised Hotel">3-Star Clean Sanitised Hotel</option>
                <option value="Private Houseboat Stay">Private Luxury Houseboat (Alleppey/Kumarakom)</option>
                <option value="Ayurvedic Wellness Retreat">Ayurvedic Wellness &amp; Rejuvenation Center</option>
                <option value="Plantation Homestay">Authentic Plantation Homestay</option>
              </select>
            </div>
          </div>

          <div class="form-grid-3col">
            <div>
              <label style="display:block;font-size:0.82rem;font-weight:700;color:#334155;margin-bottom:4px;"><?= __t('pref_food_plan') ?></label>
              <select name="food_preference" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.88rem;background:#FFF;">
                <option value="all_meals" selected><?= __t('food_all_meals') ?></option>
                <option value="breakfast_dinner"><?= __t('food_breakfast_dinner') ?></option>
                <option value="breakfast_only"><?= __t('food_breakfast_only') ?></option>
                <option value="without_food"><?= __t('food_without') ?></option>
              </select>
            </div>
            <div>
              <label style="display:block;font-size:0.82rem;font-weight:700;color:#334155;margin-bottom:4px;"><?= __t('pref_food_type') ?></label>
              <select name="food_type" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.88rem;background:#FFF;">
                <option value="both" selected><?= __t('food_type_both') ?></option>
                <option value="vegetarian"><?= __t('food_type_veg') ?></option>
                <option value="non_vegetarian"><?= __t('food_type_non_veg') ?></option>
                <option value="special_dietary"><?= __t('food_type_special') ?></option>
              </select>
            </div>
            <div>
              <label style="display:block;font-size:0.82rem;font-weight:700;color:#334155;margin-bottom:4px;"><?= __t('form_budget_range') ?></label>
              <select name="budget_range" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.88rem;background:#FFF;">
                <option value="₹20,000 - ₹35,000">₹20,000 - ₹35,000</option>
                <option value="₹35,000 - ₹60,000" selected>₹35,000 - ₹60,000</option>
                <option value="₹60,000 - ₹1,00,000">₹60,000 - ₹1,00,000</option>
                <option value="₹1,00,000+ Luxury">₹1,00,000+ Luxury</option>
              </select>
            </div>
          </div>
        </div>

        <!-- 4. Special Requirements -->
        <div style="margin-bottom:30px;">
          <h3 style="font-size:1.05rem;color:#0F172A;font-weight:700;margin-bottom:12px;border-bottom:1px solid #F1F5F9;padding-bottom:8px;">
            4. Special Wishes &amp; Sightseeing Interests
          </h3>
          <textarea name="travel_requirements" rows="4" placeholder="Tell us about temple darshans (e.g. Guruvayur Nirmalyam), specific boat cruise routes, elderly accessibility needs, or special celebration arrangements..." style="width:100%;padding:12px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.9rem;outline:none;line-height:1.6;"></textarea>
        </div>

        <button type="submit" class="btn-cinematic-primary" style="width:100%;justify-content:center;padding:15px;font-size:1.05rem;">
          <span>Submit Customised Tour Request &rarr;</span>
        </button>

      </form>

    </div>

  </div>
</section>

<!-- Leaflet & OSM Interactive Scripts -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="<?= ASSETS_URL ?>/js/osm-leaflet-map.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  var destSelect = document.getElementById('customTourDestSelect');
  var selectedPlaces = [];
  var mapInstance = null;

  function initMapForCurrentDest() {
    var destId = parseInt(destSelect.value, 10);
    if (destId <= 0) {
      document.getElementById('mapSection').style.display = 'none';
      return;
    }
    document.getElementById('mapSection').style.display = 'block';

    if (!mapInstance) {
      mapInstance = new MidhilaMap({
        containerId: 'osmMapContainer',
        destinationId: destId,
        onSelectPlace: function(place, allIds) {
          syncSelectedPlaces(place, allIds);
        }
      });
    } else {
      mapInstance.selectedPlaceIds = selectedPlaces.map(function(p) { return p.id; });
      mapInstance.loadPlaces(destId);
    }
  }

  function syncSelectedPlaces(place, allIds) {
    var existsIdx = selectedPlaces.findIndex(function(p) { return p.id === place.id; });
    if (existsIdx === -1 && allIds.indexOf(place.id) !== -1) {
      selectedPlaces.push(place);
    } else if (existsIdx !== -1 && allIds.indexOf(place.id) === -1) {
      selectedPlaces.splice(existsIdx, 1);
    }

    renderSelectedChips();
  }

  function renderSelectedChips() {
    var container = document.getElementById('selectedSpotsContainer');
    var chips = document.getElementById('selectedSpotsChips');
    var count = document.getElementById('selectedSpotsCount');
    var input = document.getElementById('selectedPlacesInput');

    count.innerText = selectedPlaces.length;
    input.value = JSON.stringify(selectedPlaces);

    if (selectedPlaces.length === 0) {
      container.style.display = 'none';
      chips.innerHTML = '';
      return;
    }

    container.style.display = 'block';
    chips.innerHTML = '';

    selectedPlaces.forEach(function(p) {
      var chip = document.createElement('span');
      chip.style.cssText = 'display:inline-flex;align-items:center;gap:6px;background:#F1F5F9;border:1px solid #CBD5E1;padding:4px 10px;border-radius:14px;font-size:0.75rem;font-weight:600;color:#0F172A;';
      chip.innerHTML = p.name + ' <button type="button" style="background:none;border:none;cursor:pointer;color:#DC2626;font-size:0.85rem;line-height:1;padding:0;">&times;</button>';
      
      chip.querySelector('button').addEventListener('click', function() {
        if (mapInstance) {
          mapInstance.togglePlaceSelection(p);
        }
        var idx = selectedPlaces.findIndex(function(item) { return item.id === p.id; });
        if (idx !== -1) selectedPlaces.splice(idx, 1);
        renderSelectedChips();
      });

      chips.appendChild(chip);
    });
  }

  window.filterMapCategory = function(cat, btn) {
    document.querySelectorAll('.osm-cat-btn').forEach(function(b) { b.classList.remove('is-active'); });
    if (btn) btn.classList.add('is-active');
    if (mapInstance) {
      mapInstance.filterCategory(cat);
    }
  };

  destSelect.addEventListener('change', initMapForCurrentDest);

  // Initial trigger if destination is already selected
  initMapForCurrentDest();

  // AI Itinerary Generation Handler
  var btnAi = document.getElementById('btnGenerateAiItinerary');
  btnAi.addEventListener('click', function() {
    var destId = parseInt(destSelect.value, 10);
    if (destId <= 0) {
      alert('Please select a destination first.');
      destSelect.focus();
      return;
    }

    var originalText = btnAi.innerHTML;
    btnAi.disabled = true;
    btnAi.innerHTML = '<span>⏳ Generating Tailored Plan...</span>';

    var payload = {
      destination_id: destId,
      customer_name: document.querySelector('input[name="full_name"]').value || 'Valued Guest',
      customer_phone: document.querySelector('input[name="phone"]').value || '',
      customer_email: document.querySelector('input[name="email"]').value || '',
      duration_days: parseInt(document.querySelector('input[name="duration_days"]').value, 10) || 3,
      travel_dates: document.querySelector('input[name="departure_date"]').value || '',
      adults: parseInt(document.querySelector('input[name="adults_count"]').value, 10) || 2,
      children: parseInt(document.querySelector('input[name="children_count"]').value, 10) || 0,
      budget_range: document.querySelector('select[name="budget_range"]').value || '',
      travel_type: document.querySelector('select[name="travel_type"]').value || 'family',
      food_pref: document.querySelector('select[name="food_preference"]').value || '',
      selected_places: selectedPlaces.map(function(p) { return p.id; }),
      language: '<?= $activeLang ?>',
    };

    fetch('<?= BASE_URL ?>/api/ai-itinerary.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(payload)
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
      btnAi.disabled = false;
      btnAi.innerHTML = originalText;

      if (data.success && data.itinerary) {
        document.getElementById('aiItineraryResultBox').style.display = 'block';
        document.getElementById('aiItineraryText').innerText = data.itinerary;
        document.getElementById('aiModelBadge').innerText = data.model_used || 'Grounded Engine';
        document.getElementById('itineraryContentInput').value = data.itinerary;

        // Scroll to preview smoothly
        document.getElementById('aiItineraryResultBox').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      } else {
        document.getElementById('aiItineraryResultBox').style.display = 'block';
        document.getElementById('aiItineraryText').innerHTML = '<div style="color:#991B1B;background:#FEF2F2;padding:14px;border-radius:8px;border:1px solid #FECACA;">⚠️ ' + (data.error || 'Unable to generate proposal at this moment. You can submit your requirements below and our team will prepare a custom plan.') + '</div>';
        document.getElementById('aiItineraryResultBox').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      }
    })
    .catch(function(err) {
      btnAi.disabled = false;
      btnAi.innerHTML = originalText;
      document.getElementById('aiItineraryResultBox').style.display = 'block';
      document.getElementById('aiItineraryText').innerHTML = '<div style="color:#991B1B;background:#FEF2F2;padding:14px;border-radius:8px;border:1px solid #FECACA;">⚠️ Network connection interrupted. Please submit your travel requirements below directly.</div>';
      document.getElementById('aiItineraryResultBox').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    });
  });

});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
