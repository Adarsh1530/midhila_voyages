<?php
/**
 * MIDHILA VOYAGES — Destination-Based Customised Tour & Accommodation Booking
 * Dynamic Destination & Property Matching with Comprehensive Guest Preferences
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';

$db = getDB();
$activeLang = current_lang();

// Selected destination via GET
$selectedDestId = isset($_GET['destination_id']) ? (int)$_GET['destination_id'] : 0;
$selectedCategorySlug = isset($_GET['cat']) ? trim($_GET['cat']) : '';

// Handle Booking Form Submission
$bookingSuccess = false;
$generatedRef = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_accommodation_enquiry'])) {
    if (verify_csrf_token()) {
        $fullName     = trim($_POST['full_name'] ?? '');
        $phone        = trim($_POST['phone'] ?? '');
        $email        = trim($_POST['email'] ?? '');
        $destId       = (int)($_POST['destination_id'] ?? 0);
        $accId        = (int)($_POST['accommodation_id'] ?? 0);
        $startDate    = trim($_POST['departure_date'] ?? '');
        $endDate      = trim($_POST['return_date'] ?? '');
        $adults       = max(1, (int)($_POST['adults_count'] ?? 1));
        $children     = max(0, (int)($_POST['children_count'] ?? 0));
        $roomsCount   = max(1, (int)($_POST['rooms_count'] ?? 1));
        $durationDays = max(1, (int)($_POST['duration_days'] ?? 1));
        $totalTravel  = $adults + $children;
        $travelType   = trim($_POST['travel_type'] ?? 'family');
        $budgetRange  = trim($_POST['budget_range'] ?? '');
        $addReqs      = trim($_POST['travel_requirements'] ?? '');

        // Preferences
        $roomTypes    = isset($_POST['room_types']) && is_array($_POST['room_types']) ? implode(', ', $_POST['room_types']) : '';
        $foodPref     = trim($_POST['food_preference'] ?? 'with_food');
        $foodType     = trim($_POST['food_type'] ?? 'both');
        $foodNotes    = trim($_POST['food_special_instructions'] ?? '');
        $sightPref    = trim($_POST['sightseeing_preference'] ?? 'with_sightseeing');
        $sightPlaces  = trim($_POST['sightseeing_places'] ?? '');
        $facilities   = isset($_POST['facilities']) && is_array($_POST['facilities']) ? implode(', ', $_POST['facilities']) : '';

        if (!empty($fullName) && !empty($phone) && $destId > 0) {
            // Fetch destination name
            $destStmt = $db->prepare("SELECT name FROM destinations WHERE id = ?");
            $destStmt->execute([$destId]);
            $destName = $destStmt->fetchColumn() ?: 'Custom Destination';

            // Fetch accommodation name if selected
            $accName = null;
            if ($accId > 0) {
                $accStmt = $db->prepare("SELECT name_en FROM accommodations WHERE id = ?");
                $accStmt->execute([$accId]);
                $accName = $accStmt->fetchColumn() ?: null;
            }

            // Generate unique sequential reference
            $year = date('Y');
            $refCount = (int)$db->query("SELECT COUNT(*) FROM enquiries WHERE reference_no LIKE 'MV-{$year}-%'")->fetchColumn() + 1;
            $generatedRef = sprintf("MV-%s-%04d", $year, $refCount);

            // Insert into enquiries table
            $insStmt = $db->prepare("
                INSERT INTO enquiries
                (reference_no, full_name, email, phone, whatsapp, destination_id, destination_name,
                 enquiry_type, language, departure_date, return_date, adults_count, children_count,
                 rooms_count, duration_days, travel_type, budget_range, accommodation_id, accommodation_name,
                 preferred_room_types, food_preference, food_type, food_special_instructions,
                 sightseeing_preference, sightseeing_places, required_facilities, travel_requirements,
                 status, workflow_status, created_at, updated_at)
                VALUES
                (?, ?, ?, ?, ?, ?, ?, 'accommodation', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'new', 'new', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
            ");

            $insStmt->execute([
                $generatedRef, $fullName, $email, $phone, $phone, $destId, $destName,
                $activeLang, $startDate, $endDate, $adults, $children,
                $roomsCount, $durationDays, $travelType, $budgetRange, $accId ?: null, $accName,
                $roomTypes, $foodPref, $foodType, $foodNotes,
                $sightPref, $sightPlaces, $facilities, $addReqs
            ]);
            $enquiryId = (int)$db->lastInsertId();

            // Register / Update in customers table
            try {
                $custCheck = $db->prepare("SELECT id, total_enquiries FROM customers WHERE phone = ? LIMIT 1");
                $custCheck->execute([$phone]);
                $cust = $custCheck->fetch(PDO::FETCH_ASSOC);

                if ($cust) {
                    $updCust = $db->prepare("UPDATE customers SET total_enquiries = total_enquiries + 1, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                    $updCust->execute([$cust['id']]);
                } else {
                    $insCust = $db->prepare("INSERT INTO customers (full_name, email, phone, whatsapp, preferred_language, total_enquiries, created_at) VALUES (?, ?, ?, ?, ?, 1, CURRENT_TIMESTAMP)");
                    $insCust->execute([$fullName, $email, $phone, $phone, $activeLang]);
                }
            } catch (Exception $ce) {
                error_log("Customer log notice: " . $ce->getMessage());
            }

            // Log initial status history
            try {
                $db->prepare("INSERT INTO booking_status_history (enquiry_id, old_status, new_status, notes, created_at) VALUES (?, NULL, 'new', 'Enquiry submitted online by customer', CURRENT_TIMESTAMP)")
                   ->execute([$enquiryId]);
            } catch (Exception $he) {}

            // Record customer acknowledgement email log with required disclaimer
            $emailBody = "Dear " . htmlspecialchars($fullName) . ",\n\n"
                       . "Your booking enquiry (#{$generatedRef}) has been received successfully.\n\n"
                       . "Selected Destination: {$destName}\n"
                       . ($accName ? "Preferred Accommodation: {$accName}\n" : "")
                       . "Dates: {$startDate} to {$endDate}\n"
                       . "Travellers: {$adults} Adults, {$children} Children ({$roomsCount} Rooms)\n\n"
                       . "IMPORTANT NOTICE:\n"
                       . "Our team will verify the availability of your requested services and contact you shortly. This acknowledgement does not represent a confirmed booking.\n\n"
                       . "Warm Regards,\nMidhila Voyages Management Team";

            try {
                $db->prepare("INSERT INTO email_logs (recipient_email, recipient_name, subject, message_body, enquiry_id, status, sent_at) VALUES (?, ?, ?, ?, ?, 'sent', CURRENT_TIMESTAMP)")
                   ->execute([$email ?: 'guest@midhilavoyages.com', $fullName, "Booking Enquiry Acknowledgement - #{$generatedRef}", $emailBody, $enquiryId]);
            } catch (Exception $ee) {}

            $bookingSuccess = true;
        } else {
            set_flash_message('error', 'Please fill in your name, phone number, and select a destination.');
        }
    }
}

// Fetch all destinations with their registered accommodation counts
$destinationsStmt = $db->query("
    SELECT d.id, d.name, d.slug, d.name_ml, d.name_ta, d.region_state,
           COUNT(a.id) as accommodation_count
    FROM destinations d
    LEFT JOIN accommodations a ON a.destination_id = d.id AND a.is_active = 1
    WHERE d.is_published = 1
    GROUP BY d.id
    ORDER BY (d.region_state = 'Kerala') DESC, d.name ASC
");
$allDestinations = $destinationsStmt->fetchAll(PDO::FETCH_ASSOC);

// If no destination is selected by query, default to the first one with accommodations (e.g. Munnar)
if ($selectedDestId === 0 && !empty($allDestinations)) {
    foreach ($allDestinations as $d) {
        if ($d['accommodation_count'] > 0) {
            $selectedDestId = (int)$d['id'];
            break;
        }
    }
    if ($selectedDestId === 0) {
        $selectedDestId = (int)$allDestinations[0]['id'];
    }
}

// Fetch accommodations registered for the selected destination
$accQuery = "SELECT a.*, d.name as destination_name FROM accommodations a 
             LEFT JOIN destinations d ON a.destination_id = d.id 
             WHERE a.is_active = 1";
$accParams = [];

if ($selectedDestId > 0) {
    $accQuery .= " AND a.destination_id = ?";
    $accParams[] = $selectedDestId;
}

$accQuery .= " ORDER BY a.indicative_price ASC";
$accStmt = $db->prepare($accQuery);
$accStmt->execute($accParams);
$availableAccommodations = $accStmt->fetchAll(PDO::FETCH_ASSOC);

// Get selected destination info
$currentDest = null;
foreach ($allDestinations as $d) {
    if ((int)$d['id'] === $selectedDestId) {
        $currentDest = $d;
        break;
    }
}

$pageTitle = __t('nav_accommodation') . ' & Customised Tour Booking | Midhila Voyages';
$pageDescription = 'Filter registered luxury chalets, tea plantation resorts, backwater houseboats, and beach sanctuaries across Kerala with customized dining and sightseeing.';

require_once __DIR__ . '/includes/header.php';
?>

<!-- Leaflet Interactive Maps (OpenStreetMap Permitted Tiles) -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>

<!-- ACKNOWLEDGEMENT MODAL POPUP ON SUCCESSFUL SUBMISSION -->
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
    <a href="<?= BASE_URL ?>/accommodation.php" class="btn-cinematic-primary" style="width:100%;justify-content:center;">
      <span>Continue Browsing</span>
    </a>
  </div>
</div>
<?php endif; ?>

<!-- PAGE HERO -->
<section style="background:#0F172A;color:#FFFFFF;padding:calc(var(--header-height, 86px) + 48px) 0 60px;position:relative;overflow:hidden;">
  <div class="container" style="position:relative;z-index:2;text-align:center;max-width:860px;">
    <span style="font-size:0.8rem;text-transform:uppercase;letter-spacing:0.12em;color:#C5A059;font-weight:700;display:block;margin-bottom:12px;">
      Destination-Linked Properties &amp; Customised Routing
    </span>
    <h1 style="font-family:'Cinzel',serif;font-size:clamp(2rem, 4vw, 3rem);color:#FFFFFF;margin-bottom:14px;">
      <?= __t('nav_accommodation') ?> &amp; Tour Booking
    </h1>
    <p style="font-size:0.95rem;color:#CBD5E1;line-height:1.7;max-width:720px;margin:0 auto 20px;">
      Select your preferred destination below to automatically reveal registered verified accommodations. Customise your room view, food plan, private vehicle, and sightseeing in a single booking request.
    </p>
    <div style="display:inline-flex;align-items:center;gap:6px;background:rgba(217,119,6,0.15);border:1px solid #D97706;padding:6px 16px;border-radius:20px;font-size:0.78rem;color:#FDE68A;">
      <span>ℹ️</span>
      <span><?= __t('disclaimer_notice') ?></span>
    </div>
  </div>
</section>

<!-- MAIN INTERACTIVE FILTER & BOOKING WORKSPACE -->
<section style="padding:50px 0 80px;background:#FAFAFA;">
  <div class="container">

    <!-- STEP 1: DESTINATION SELECTOR PILLS -->
    <div style="background:#FFFFFF;border-radius:14px;padding:24px;border:1px solid #E2E8F0;box-shadow:0 2px 10px rgba(0,0,0,0.03);margin-bottom:36px;">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:10px;">
        <div>
          <span style="font-size:0.72rem;font-weight:700;text-transform:uppercase;color:#C5A059;letter-spacing:0.06em;">Step 1</span>
          <h2 style="font-size:1.25rem;color:#0F172A;font-weight:700;margin:0;">
            Select Travel Destination to Reveal Stays
          </h2>
        </div>
        <div style="font-size:0.82rem;color:#64748B;">
          Showing registered accommodations in: <strong style="color:#0F172A;"><?= e(loc($currentDest, 'name')) ?></strong>
        </div>
      </div>

      <!-- Quick Destination Switcher Buttons -->
      <div style="display:flex;flex-wrap:wrap;gap:8px;" id="destinationPillList">
        <?php foreach ($allDestinations as $d): ?>
          <?php 
            $isSelected = ((int)$d['id'] === $selectedDestId);
            $hasStays = ($d['accommodation_count'] > 0);
          ?>
          <a href="<?= BASE_URL ?>/accommodation.php?destination_id=<?= (int)$d['id'] ?>" 
             style="display:inline-flex;align-items:center;gap:8px;padding:8px 14px;border-radius:8px;font-size:0.86rem;font-weight:600;text-decoration:none;transition:all 0.2s;<?= $isSelected ? 'background:#0F172A;color:#FFFFFF;box-shadow:0 4px 10px rgba(15,23,42,0.15);' : ($hasStays ? 'background:#F1F5F9;color:#334155;border:1px solid #E2E8F0;' : 'background:#FAFAFA;color:#94A3B8;border:1px dashed #E2E8F0;') ?>">
            <span>📍 <?= loc($d, 'name') ?></span>
            <?php if ($hasStays): ?>
              <span style="font-size:0.72rem;background:<?= $isSelected ? '#C5A059' : '#E2E8F0' ?>;color:<?= $isSelected ? '#0F172A' : '#475569' ?>;padding:1px 6px;border-radius:10px;font-weight:700;">
                <?= $d['accommodation_count'] ?>
              </span>
            <?php endif; ?>
          </a>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- 2-COLUMN LAYOUT: ACCOMMODATIONS LIST + CUSTOMISED BOOKING FORM -->
    <div class="booking-workspace-grid" style="align-items:start;">

      <!-- LEFT COLUMN: DISPLAY OF ACCOMMODATIONS FOR SELECTED DESTINATION -->
      <div>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
          <h3 style="font-size:1.15rem;color:#0F172A;font-weight:700;margin:0;">
            Accommodations in <?= e(loc($currentDest, 'name')) ?>
          </h3>
          <span style="font-size:0.8rem;color:#64748B;">
            (<?= count($availableAccommodations) ?> Available Properties)
          </span>
        </div>

        <!-- Interactive Leaflet Map for Accommodations & Nearby Places -->
        <div style="background:#FFFFFF;border-radius:12px;border:1px solid #E2E8F0;overflow:hidden;margin-bottom:20px;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
          <div style="padding:10px 14px;background:#F8FAFC;border-bottom:1px solid #E2E8F0;display:flex;justify-content:space-between;align-items:center;">
            <span style="font-size:0.75rem;font-weight:700;color:#64748B;text-transform:uppercase;">Mapped Stays &amp; Highlights</span>
            <span style="font-size:0.72rem;color:#059669;font-weight:600;">✓ OpenStreetMap Verified</span>
          </div>
          <div id="accLeafletMap" style="height:250px;width:100%;"></div>
        </div>

        <?php if (empty($availableAccommodations)): ?>
          <div style="background:#FFFFFF;border-radius:12px;padding:40px 24px;text-align:center;border:1px dashed #CBD5E1;">
            <div style="font-size:2.5rem;margin-bottom:10px;">🏨</div>
            <h4 style="font-size:1.05rem;color:#0F172A;font-weight:700;margin-bottom:6px;">No accommodations directly listed yet for this region</h4>
            <p style="font-size:0.85rem;color:#64748B;line-height:1.6;margin-bottom:16px;">
              You can still submit your requirements using the form on the right. Our team will verify and book verified partner properties in <?= e(loc($currentDest, 'name')) ?> for you.
            </p>
          </div>
        <?php else: ?>
          <div style="display:flex;flex-direction:column;gap:20px;">
            <?php foreach ($availableAccommodations as $acc): ?>
              <div class="acc-card" style="background:#FFFFFF;border-radius:12px;overflow:hidden;border:1.5px solid #E2E8F0;box-shadow:0 3px 12px rgba(0,0,0,0.03);display:flex;flex-direction:column;transition:border-color 0.2s;" data-acc-id="<?= (int)$acc['id'] ?>" data-acc-name="<?= e(loc($acc, 'name')) ?>">
                
                <div style="position:relative;height:180px;background:#0F172A;overflow:hidden;">
                  <img src="<?= e($acc['featured_image'] ?: 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=800&q=80') ?>" 
                       alt="<?= e(loc($acc, 'name')) ?>" style="width:100%;height:100%;object-fit:cover;">
                  <div style="position:absolute;top:12px;left:12px;background:rgba(15,23,42,0.85);color:#FFFFFF;padding:4px 10px;border-radius:6px;font-size:0.72rem;font-weight:700;backdrop-filter:blur(4px);">
                    <?= e($acc['category']) ?>
                  </div>
                  <?php if ($acc['indicative_price'] > 0): ?>
                    <div style="position:absolute;bottom:12px;right:12px;background:#059669;color:#FFFFFF;padding:4px 10px;border-radius:6px;font-size:0.82rem;font-weight:700;">
                      <?= format_price((float)$acc['indicative_price']) ?> <span style="font-size:0.68rem;font-weight:400;">/ night</span>
                    </div>
                  <?php endif; ?>
                </div>

                <div style="padding:20px;">
                  <h4 style="font-size:1.15rem;font-weight:700;color:#0F172A;margin-bottom:4px;">
                    <?= loc($acc, 'name') ?>
                  </h4>
                  <div style="font-size:0.78rem;color:#64748B;margin-bottom:12px;display:flex;align-items:center;gap:4px;">
                    <span>📍</span>
                    <span><?= e($acc['location_address']) ?></span>
                  </div>

                  <!-- Room Categories & Views -->
                  <?php if (!empty($acc['room_categories'])): ?>
                    <div style="font-size:0.78rem;color:#334155;margin-bottom:8px;">
                      <strong>Available Room Categories:</strong>
                      <div style="color:#64748B;margin-top:2px;"><?= e($acc['room_categories']) ?></div>
                    </div>
                  <?php endif; ?>

                  <!-- Facilities & Food -->
                  <div style="display:flex;flex-wrap:wrap;gap:6px;margin:12px 0;">
                    <?php if (!empty($acc['room_facilities'])): ?>
                      <?php foreach (array_slice(explode(',', $acc['room_facilities']), 0, 4) as $fac): ?>
                        <span style="font-size:0.7rem;background:#F1F5F9;color:#475569;padding:2px 8px;border-radius:4px;font-weight:600;">
                          ✓ <?= trim($fac) ?>
                        </span>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </div>

                  <?php if (!empty($acc['special_instructions'])): ?>
                    <div style="font-size:0.75rem;color:#D97706;background:#FFFBEB;padding:6px 10px;border-radius:6px;margin-bottom:14px;">
                      <strong>Special Note:</strong> <?= e($acc['special_instructions']) ?>
                    </div>
                  <?php endif; ?>

                  <button type="button" class="btn-select-acc admin-btn admin-btn-outline" 
                          onclick="selectAccommodation(<?= (int)$acc['id'] ?>, '<?= e(addslashes(loc($acc, 'name'))) ?>')"
                          style="width:100%;justify-content:center;font-size:0.84rem;font-weight:700;padding:8px;">
                    <span>Select This Property for Booking &darr;</span>
                  </button>
                </div>

              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- RIGHT COLUMN: THE COMPREHENSIVE CUSTOMISED TOUR & ACCOMMODATION ENQUIRY FORM -->
      <div style="background:#FFFFFF;border-radius:16px;padding:32px;border:1px solid #E2E8F0;box-shadow:0 8px 30px rgba(15,23,42,0.06);position:sticky;top:100px;">
        
        <div style="border-bottom:1px solid #E2E8F0;padding-bottom:16px;margin-bottom:20px;">
          <span style="font-size:0.72rem;font-weight:700;text-transform:uppercase;color:#C5A059;letter-spacing:0.06em;">Step 2</span>
          <h3 style="font-size:1.3rem;color:#0F172A;font-weight:700;margin:0 0 4px;">
            Customised Booking Enquiry
          </h3>
          <p style="font-size:0.84rem;color:#64748B;margin:0;">
            Specify your preferences. Our travel directors will contact the property, verify availability, and revert promptly.
          </p>
        </div>

        <form method="POST" action="<?= BASE_URL ?>/accommodation.php" id="accommodationBookingForm">
          <?= csrf_field() ?>
          <input type="hidden" name="submit_accommodation_enquiry" value="1">
          <input type="hidden" name="destination_id" id="formDestinationId" value="<?= (int)$selectedDestId ?>">
          <input type="hidden" name="accommodation_id" id="formAccommodationId" value="0">

          <!-- Selected Property Banner (if chosen) -->
          <div id="selectedPropertyBadge" style="display:none;background:#EFF6FF;border:1px solid #BFDBFE;padding:10px 14px;border-radius:8px;margin-bottom:18px;">
            <div style="font-size:0.7rem;text-transform:uppercase;color:#1E40AF;font-weight:700;">Selected Property:</div>
            <div style="font-size:0.95rem;font-weight:700;color:#1E3A8A;" id="selectedPropertyName"></div>
            <button type="button" onclick="clearSelectedAcc()" style="background:none;border:none;color:#DC2626;font-size:0.75rem;cursor:pointer;padding:0;margin-top:2px;">(Change / Clear Selection)</button>
          </div>

          <!-- Section A: Guest Information -->
          <div style="margin-bottom:20px;">
            <h4 style="font-size:0.88rem;text-transform:uppercase;color:#0F172A;font-weight:700;letter-spacing:0.04em;margin-bottom:12px;border-bottom:1px solid #F1F5F9;padding-bottom:6px;">
              A. Guest Information
            </h4>
            
            <div class="form-group" style="margin-bottom:14px;">
              <label style="display:block;font-size:0.82rem;font-weight:700;color:#334155;margin-bottom:4px;"><?= __t('form_full_name') ?></label>
              <input type="text" name="full_name" required placeholder="e.g. Maya Varma" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.9rem;outline:none;">
            </div>

            <div class="form-grid-2col" style="margin-bottom:14px;">
              <div>
                <label style="display:block;font-size:0.82rem;font-weight:700;color:#334155;margin-bottom:4px;"><?= __t('form_phone') ?></label>
                <input type="tel" name="phone" required placeholder="+91 9876543210" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.9rem;outline:none;">
              </div>
              <div>
                <label style="display:block;font-size:0.82rem;font-weight:700;color:#334155;margin-bottom:4px;"><?= __t('form_email') ?></label>
                <input type="email" name="email" required placeholder="maya@example.com" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.9rem;outline:none;">
              </div>
            </div>

            <div class="form-grid-2col" style="margin-bottom:14px;">
              <div>
                <label style="display:block;font-size:0.82rem;font-weight:700;color:#334155;margin-bottom:4px;"><?= __t('form_start_date') ?></label>
                <input type="date" name="departure_date" required min="<?= date('Y-m-d') ?>" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.9rem;outline:none;background:#FFF;">
              </div>
              <div>
                <label style="display:block;font-size:0.82rem;font-weight:700;color:#334155;margin-bottom:4px;"><?= __t('form_end_date') ?></label>
                <input type="date" name="return_date" min="<?= date('Y-m-d') ?>" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.9rem;outline:none;background:#FFF;">
              </div>
            </div>

            <div class="form-grid-3col">
              <div>
                <label style="display:block;font-size:0.78rem;font-weight:700;color:#334155;margin-bottom:4px;"><?= __t('form_adults') ?></label>
                <input type="number" name="adults_count" value="2" min="1" max="50" style="width:100%;padding:9px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.88rem;text-align:center;">
              </div>
              <div>
                <label style="display:block;font-size:0.78rem;font-weight:700;color:#334155;margin-bottom:4px;"><?= __t('form_children') ?></label>
                <input type="number" name="children_count" value="0" min="0" max="30" style="width:100%;padding:9px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.88rem;text-align:center;">
              </div>
              <div>
                <label style="display:block;font-size:0.78rem;font-weight:700;color:#334155;margin-bottom:4px;"><?= __t('form_rooms') ?></label>
                <input type="number" name="rooms_count" value="1" min="1" max="30" style="width:100%;padding:9px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.88rem;text-align:center;">
              </div>
            </div>
          </div>

          <!-- Section B: Accommodation & View Preferences -->
          <div style="margin-bottom:20px;">
            <h4 style="font-size:0.88rem;text-transform:uppercase;color:#0F172A;font-weight:700;letter-spacing:0.04em;margin-bottom:12px;border-bottom:1px solid #F1F5F9;padding-bottom:6px;">
              B. Room Category &amp; View Preferences
            </h4>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
              <label style="display:flex;align-items:center;gap:6px;font-size:0.82rem;color:#334155;">
                <input type="checkbox" name="room_types[]" value="Mountain-view">
                <span><?= __t('room_mountain_view') ?></span>
              </label>
              <label style="display:flex;align-items:center;gap:6px;font-size:0.82rem;color:#334155;">
                <input type="checkbox" name="room_types[]" value="Tea plantation-view">
                <span><?= __t('room_tea_view') ?></span>
              </label>
              <label style="display:flex;align-items:center;gap:6px;font-size:0.82rem;color:#334155;">
                <input type="checkbox" name="room_types[]" value="Valley-view">
                <span><?= __t('room_valley_view') ?></span>
              </label>
              <label style="display:flex;align-items:center;gap:6px;font-size:0.82rem;color:#334155;">
                <input type="checkbox" name="room_types[]" value="Lake-view">
                <span><?= __t('room_lake_view') ?></span>
              </label>
              <label style="display:flex;align-items:center;gap:6px;font-size:0.82rem;color:#334155;">
                <input type="checkbox" name="room_types[]" value="Backwater-view">
                <span><?= __t('room_backwater_view') ?></span>
              </label>
              <label style="display:flex;align-items:center;gap:6px;font-size:0.82rem;color:#334155;">
                <input type="checkbox" name="room_types[]" value="Beach-view">
                <span><?= __t('room_beach_view') ?></span>
              </label>
              <label style="display:flex;align-items:center;gap:6px;font-size:0.82rem;color:#334155;">
                <input type="checkbox" name="room_types[]" value="Deluxe room" checked>
                <span><?= __t('room_deluxe') ?></span>
              </label>
              <label style="display:flex;align-items:center;gap:6px;font-size:0.82rem;color:#334155;">
                <input type="checkbox" name="room_types[]" value="Luxury Suite">
                <span><?= __t('room_luxury') ?></span>
              </label>
            </div>
          </div>

          <!-- Section C & D: Food Preferences & Types -->
          <div style="margin-bottom:20px;">
            <h4 style="font-size:0.88rem;text-transform:uppercase;color:#0F172A;font-weight:700;letter-spacing:0.04em;margin-bottom:12px;border-bottom:1px solid #F1F5F9;padding-bottom:6px;">
              C &amp; D. Food Preferences &amp; Diet
            </h4>
            
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
              <div>
                <label style="display:block;font-size:0.8rem;font-weight:700;color:#334155;margin-bottom:4px;"><?= __t('pref_food_plan') ?></label>
                <select name="food_preference" style="width:100%;padding:9px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.86rem;background:#FFF;">
                  <option value="with_food"><?= __t('food_with') ?></option>
                  <option value="all_meals" selected><?= __t('food_all_meals') ?></option>
                  <option value="breakfast_dinner"><?= __t('food_breakfast_dinner') ?></option>
                  <option value="breakfast_only"><?= __t('food_breakfast_only') ?></option>
                  <option value="without_food"><?= __t('food_without') ?></option>
                </select>
              </div>
              <div>
                <label style="display:block;font-size:0.8rem;font-weight:700;color:#334155;margin-bottom:4px;"><?= __t('pref_food_type') ?></label>
                <select name="food_type" style="width:100%;padding:9px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.86rem;background:#FFF;">
                  <option value="both" selected><?= __t('food_type_both') ?></option>
                  <option value="vegetarian"><?= __t('food_type_veg') ?></option>
                  <option value="non_vegetarian"><?= __t('food_type_non_veg') ?></option>
                  <option value="special_dietary"><?= __t('food_type_special') ?></option>
                </select>
              </div>
            </div>

            <div>
              <label style="display:block;font-size:0.78rem;font-weight:600;color:#64748B;margin-bottom:4px;"><?= __t('food_instructions') ?></label>
              <input type="text" name="food_special_instructions" placeholder="e.g. Jain food required, non-spicy for kids" style="width:100%;padding:8px 12px;border:1px solid #CBD5E1;border-radius:6px;font-size:0.85rem;">
            </div>
          </div>

          <!-- Section E: Sightseeing Preferences -->
          <div style="margin-bottom:20px;">
            <h4 style="font-size:0.88rem;text-transform:uppercase;color:#0F172A;font-weight:700;letter-spacing:0.04em;margin-bottom:12px;border-bottom:1px solid #F1F5F9;padding-bottom:6px;">
              E. Sightseeing &amp; Private Transport
            </h4>
            
            <div style="margin-bottom:10px;">
              <select name="sightseeing_preference" style="width:100%;padding:9px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.86rem;background:#FFF;">
                <option value="with_sightseeing" selected><?= __t('sightseeing_with') ?></option>
                <option value="private_vehicle"><?= __t('sightseeing_private') ?></option>
                <option value="customised_sightseeing"><?= __t('sightseeing_customised') ?></option>
                <option value="group_sightseeing"><?= __t('sightseeing_group') ?></option>
                <option value="without_sightseeing"><?= __t('sightseeing_without') ?></option>
              </select>
            </div>

            <div>
              <label style="display:block;font-size:0.78rem;font-weight:600;color:#64748B;margin-bottom:4px;"><?= __t('sightseeing_places_wish') ?></label>
              <textarea name="sightseeing_places" rows="2" placeholder="e.g. Eravikulam National park, Mattupetty boating, Spice garden" style="width:100%;padding:8px 12px;border:1px solid #CBD5E1;border-radius:6px;font-size:0.85rem;"></textarea>
            </div>
          </div>

          <!-- Section F: Facilities Required -->
          <div style="margin-bottom:24px;">
            <h4 style="font-size:0.88rem;text-transform:uppercase;color:#0F172A;font-weight:700;letter-spacing:0.04em;margin-bottom:10px;border-bottom:1px solid #F1F5F9;padding-bottom:6px;">
              F. Required Facilities
            </h4>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
              <label style="display:flex;align-items:center;gap:6px;font-size:0.8rem;color:#334155;">
                <input type="checkbox" name="facilities[]" value="Wi-Fi" checked>
                <span><?= __t('fac_wifi') ?></span>
              </label>
              <label style="display:flex;align-items:center;gap:6px;font-size:0.8rem;color:#334155;">
                <input type="checkbox" name="facilities[]" value="Air Conditioning" checked>
                <span><?= __t('fac_ac') ?></span>
              </label>
              <label style="display:flex;align-items:center;gap:6px;font-size:0.8rem;color:#334155;">
                <input type="checkbox" name="facilities[]" value="Swimming Pool">
                <span><?= __t('fac_pool') ?></span>
              </label>
              <label style="display:flex;align-items:center;gap:6px;font-size:0.8rem;color:#334155;">
                <input type="checkbox" name="facilities[]" value="Parking">
                <span><?= __t('fac_parking') ?></span>
              </label>
              <label style="display:flex;align-items:center;gap:6px;font-size:0.8rem;color:#334155;">
                <input type="checkbox" name="facilities[]" value="Balcony">
                <span><?= __t('fac_balcony') ?></span>
              </label>
              <label style="display:flex;align-items:center;gap:6px;font-size:0.8rem;color:#334155;">
                <input type="checkbox" name="facilities[]" value="Accessibility / Senior Friendly">
                <span><?= __t('fac_accessibility') ?></span>
              </label>
            </div>
          </div>

          <div style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:8px;padding:12px;margin-bottom:20px;font-size:0.75rem;color:#64748B;line-height:1.5;">
            🔒 <strong>Booking Policy:</strong> We do not automatically confirm room availability. Our dedicated operations director will independently verify the requested rooms with the accommodation partner and revert with confirmation and verified pricing.
          </div>

          <button type="submit" class="btn-cinematic-primary" style="width:100%;justify-content:center;padding:14px;font-size:1rem;">
            <span><?= __t('btn_submit_enquiry') ?> &rarr;</span>
          </button>
        </form>

      </div>

    </div>

  </div>
</section>

<script>
  function selectAccommodation(accId, accName) {
    document.getElementById('formAccommodationId').value = accId;
    document.getElementById('selectedPropertyName').textContent = accName;
    document.getElementById('selectedPropertyBadge').style.display = 'block';

    // Highlight selected card
    document.querySelectorAll('.acc-card').forEach(function(card) {
      if (card.getAttribute('data-acc-id') == accId) {
        card.style.borderColor = '#0F172A';
        card.style.boxShadow = '0 0 0 3px rgba(15,23,42,0.1)';
      } else {
        card.style.borderColor = '#E2E8F0';
        card.style.boxShadow = '0 3px 12px rgba(0,0,0,0.03)';
      }
    });

    // Smooth scroll to form on mobile
    if (window.innerWidth < 992) {
      document.getElementById('accommodationBookingForm').scrollIntoView({ behavior: 'smooth' });
    }
  }

  function clearSelectedAcc() {
    document.getElementById('formAccommodationId').value = 0;
    document.getElementById('selectedPropertyBadge').style.display = 'none';
    document.querySelectorAll('.acc-card').forEach(function(card) {
      card.style.borderColor = '#E2E8F0';
      card.style.boxShadow = '0 3px 12px rgba(0,0,0,0.03)';
    });
  }
</script>

<!-- Leaflet & OSM Interactive Scripts -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="<?= ASSETS_URL ?>/js/osm-leaflet-map.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  var destId = <?= (int)$selectedDestId ?>;
  var destLat = <?= (float)($currentDest['latitude'] ?: 10.0889) ?>;
  var destLng = <?= (float)($currentDest['longitude'] ?: 77.0595) ?>;

  if (document.getElementById('accLeafletMap') && destId > 0) {
    new MidhilaMap({
      containerId: 'accLeafletMap',
      destinationId: destId,
      latitude: destLat,
      longitude: destLng,
      zoom: 12
    });
  }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
