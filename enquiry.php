<?php
/**
 * MIDHILA VOYAGES — Interactive "Plan My Trip" Enquiry System
 * Universal Travel, Tour Package & Customised Circuit Booking Portal
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';

$db = getDB();
$activeLang = current_lang();

$pageTitle = __t('btn_plan_trip') . ' | Midhila Voyages';
$pageDescription = 'Customize your vacation, Kerala tour, or sacred pilgrimage. Share your requirements and receive a personalized travel proposal.';

// Pre-fill parameters if arriving from package or destination page
$preDestId = isset($_GET['destination_id']) ? (int)$_GET['destination_id'] : 0;
$prePkgId  = isset($_GET['package_id']) ? (int)$_GET['package_id'] : 0;
$preType   = trim($_GET['type'] ?? 'general_tour');

$pkgDetails = null;
if ($prePkgId > 0) {
    $stmt = $db->prepare("SELECT id, title, destination_id FROM packages WHERE id = ? LIMIT 1");
    $stmt->execute([$prePkgId]);
    $pkgDetails = $stmt->fetch();
    if ($pkgDetails && empty($preDestId)) {
        $preDestId = (int)$pkgDetails['destination_id'];
    }
}

// Fetch active destinations & categories
$destinations = $db->query("SELECT id, name, name_ml, name_ta, region_state FROM destinations WHERE is_published = 1 ORDER BY (region_state = 'Kerala') DESC, name ASC")->fetchAll();
$categories = $db->query("SELECT id, name, slug FROM package_categories WHERE is_active = 1 ORDER BY display_order ASC")->fetchAll();

$errors = [];
$submittedRecord = null;
$agencyWhatsApp = get_setting('whatsapp_number', '+916282948227');

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (!verify_csrf_token()) {
        $errors[] = 'Your security session has expired. Please refresh the page and submit again.';
    } else {
        $destId         = !empty($_POST['destination_id']) ? (int)$_POST['destination_id'] : null;
        $destName       = trim($_POST['destination_name'] ?? '');
        $pkgId          = !empty($_POST['package_id']) ? (int)$_POST['package_id'] : null;
        $pkgTitle       = trim($_POST['package_title'] ?? '');
        $enquiryType    = trim($_POST['enquiry_type'] ?? 'general_tour');
        $travelCategory = trim($_POST['travel_category'] ?? 'Custom Tour');
        $depDate        = !empty($_POST['departure_date']) ? $_POST['departure_date'] : null;
        $retDate        = !empty($_POST['return_date']) ? $_POST['return_date'] : null;

        $seatsCount     = max(1, (int)($_POST['seats_count'] ?? 1));
        $passengersRaw  = $_POST['passengers'] ?? [];

        $passengers     = [];
        $adultsCount    = 0;
        $childrenCount  = 0;
        $infantsCount   = 0;

        if (is_array($passengersRaw)) {
            foreach ($passengersRaw as $idx => $p) {
                $pName  = trim($p['name'] ?? '');
                $pPhone = trim($p['phone'] ?? '');
                $pAge   = isset($p['age']) && $p['age'] !== '' ? (int)$p['age'] : null;

                if (!empty($pName)) {
                    $tag = 'Adult (12+ yrs)';
                    $isChild = false;
                    if ($pAge !== null) {
                        if ($pAge >= 4 && $pAge <= 12) {
                            $isChild = true;
                            $childrenCount++;
                            $tag = 'Child (4–12 yrs)';
                        } elseif ($pAge < 4) {
                            $infantsCount++;
                            $tag = 'Infant (< 4 yrs)';
                        } else {
                            $adultsCount++;
                            $tag = 'Adult (12+ yrs)';
                        }
                    } else {
                        $adultsCount++;
                    }

                    $passengers[] = [
                        'index'    => count($passengers) + 1,
                        'name'     => $pName,
                        'phone'    => $pPhone,
                        'age'      => $pAge,
                        'tag'      => $tag,
                        'is_child' => $isChild
                    ];
                }
            }
        }

        // Fallback for primary contact if submitted via legacy field names
        if (empty($passengers)) {
            $pName  = trim($_POST['full_name'] ?? '');
            $pPhone = trim($_POST['phone'] ?? '');
            $pAge   = isset($_POST['age']) && $_POST['age'] !== '' ? (int)$_POST['age'] : 30;
            if (!empty($pName)) {
                $isChild = ($pAge >= 4 && $pAge <= 12);
                if ($isChild) { $childrenCount = 1; $tag = 'Child (4–12 yrs)'; }
                else { $adultsCount = 1; $tag = 'Adult (12+ yrs)'; }
                $passengers[] = [
                    'index'    => 1,
                    'name'     => $pName,
                    'phone'    => $pPhone,
                    'age'      => $pAge,
                    'tag'      => $tag,
                    'is_child' => $isChild
                ];
            }
        }

        if (empty($passengers) || empty($passengers[0]['name'])) {
            $errors[] = 'Please enter the Full Name for Seat 1 (Primary Contact).';
        }
        if (empty($passengers[0]['phone']) || strlen($passengers[0]['phone']) < 8) {
            $errors[] = 'Please enter a valid Phone / WhatsApp Number for Seat 1.';
        }
        if (empty($depDate)) {
            $errors[] = 'Please select your Travel Starting Date.';
        }

        $fullName       = $passengers[0]['name'] ?? 'Traveler';
        $phone          = $passengers[0]['phone'] ?? '';
        $whatsapp       = $phone;
        $email          = trim($_POST['email'] ?? '');
        $adults         = max(1, $adultsCount);
        $children       = $childrenCount;
        $totalSeats     = count($passengers);
        $roomsCount     = max(1, (int)ceil($totalSeats / 2));

        $hotelPref      = trim($_POST['hotel_preference'] ?? '4-Star / Luxury Resort');
        $foodPref       = trim($_POST['food_preference'] ?? 'All meals included (Breakfast, Lunch, Dinner)');
        $foodType       = 'both';
        $vehiclePref    = trim($_POST['vehicle_preference'] ?? 'Innova Crysta AC');
        $budget         = trim($_POST['budget_tier'] ?? 'Flexible');
        $requests       = trim($_POST['special_requests'] ?? '');

        if (empty($errors)) {
            try {
                if ($destId > 0 && empty($destName)) {
                    $dStmt = $db->prepare("SELECT name FROM destinations WHERE id = ?");
                    $dStmt->execute([$destId]);
                    $destName = $dStmt->fetchColumn() ?: '';
                }
                if ($pkgId > 0 && empty($pkgTitle)) {
                    $pStmt = $db->prepare("SELECT title FROM packages WHERE id = ?");
                    $pStmt->execute([$pkgId]);
                    $pkgTitle = $pStmt->fetchColumn() ?: '';
                }

                $year = date('Y');
                $maxId = (int)$db->query("SELECT COALESCE(MAX(id), 0) FROM enquiries")->fetchColumn() + 1;
                $candRef = sprintf("MV-%s-%04d", $year, $maxId);
                
                $checkStmt = $db->prepare("SELECT COUNT(*) FROM enquiries WHERE reference_no = ?");
                $suffix = 0;
                while (true) {
                    $testRef = ($suffix === 0) ? $candRef : sprintf("MV-%s-%04d-%d", $year, $maxId, $suffix);
                    $checkStmt->execute([$testRef]);
                    if ((int)$checkStmt->fetchColumn() === 0) {
                        $refNo = $testRef;
                        break;
                    }
                    $suffix++;
                }

                $passengerRosterLines = [];
                foreach ($passengers as $p) {
                    $ph = !empty($p['phone']) ? $p['phone'] : 'Same as Primary';
                    $ageStr = $p['age'] !== null ? "Age: {$p['age']}" : "Age: Not specified";
                    $passengerRosterLines[] = "• Seat {$p['index']}: {$p['name']} | Phone: {$ph} | {$ageStr} ({$p['tag']})";
                }
                $passengerRosterText = implode("\n", $passengerRosterLines);

                $childStr = ($children === 1) ? '1 Child' : "{$children} Children";
                $adultStr = ($adults === 1) ? '1 Adult' : "{$adults} Adults";
                $compiledNotes = "Total Seats: {$totalSeats} ({$adultStr}, {$childStr}" . ($infantsCount > 0 ? ", {$infantsCount} Infants" : "") . ")\n"
                               . "Passenger Seat Details:\n{$passengerRosterText}\n\n"
                               . "Hotel Category: {$hotelPref}\n"
                               . "Food & Meal Preferences: {$foodPref}\n"
                               . "Vehicle: {$vehiclePref}\n"
                               . (!empty($requests) ? "\nSpecial Requests:\n{$requests}" : "");

                $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
                $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 250);

                $stmt = $db->prepare("
                    INSERT INTO enquiries (
                        reference_no, full_name, email, phone, whatsapp,
                        destination_id, destination_name, package_id, package_title,
                        enquiry_type, language, travel_category, departure_date, return_date,
                        adults_count, children_count, rooms_count, budget_range,
                        accommodation_preference, food_preference, food_type,
                        travel_requirements, status, workflow_status, ip_address, user_agent,
                        created_at, updated_at
                    ) VALUES (
                        ?, ?, ?, ?, ?,
                        ?, ?, ?, ?,
                        ?, ?, ?, ?, ?,
                        ?, ?, ?, ?,
                        ?, ?, ?,
                        ?, 'new', 'new', ?, ?,
                        CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
                    )
                ");

                $stmt->execute([
                    $refNo, $fullName, $email, $phone, $whatsapp,
                    $destId, $destName, $pkgId, $pkgTitle,
                    $enquiryType, $activeLang, $travelCategory, $depDate, $retDate,
                    $adults, $children, $roomsCount, $budget,
                    $hotelPref, $foodPref, $foodType,
                    $compiledNotes, $ip, $ua
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
                           ->execute([$fullName, $email, $phone, $whatsapp, $activeLang]);
                    }
                } catch (Exception $ce) {}

                // Initial status history
                try {
                    $db->prepare("INSERT INTO booking_status_history (enquiry_id, new_status, notes, created_at) VALUES (?, 'new', 'Customer submitted Plan My Journey form', CURRENT_TIMESTAMP)")
                       ->execute([$enquiryId]);
                } catch (Exception $he) {}

                // Prefilled WhatsApp message
                $passengerWaLines = [];
                foreach ($passengers as $p) {
                    $ageStr = $p['age'] !== null ? "Age: {$p['age']}" : "Age: N/A";
                    $passengerWaLines[] = "  {$p['index']}. {$p['name']} ({$ageStr} - {$p['tag']})";
                }
                $passengerWaText = implode("\n", $passengerWaLines);

                $breakdownParts = [];
                if ($adults > 0) $breakdownParts[] = ($adults === 1) ? '1 Adult' : "{$adults} Adults";
                if ($children > 0) $breakdownParts[] = ($children === 1) ? '1 Child' : "{$children} Children";
                if ($infantsCount > 0) $breakdownParts[] = ($infantsCount === 1) ? '1 Infant' : "{$infantsCount} Infants";
                $breakdownText = !empty($breakdownParts) ? ' (' . implode(', ', $breakdownParts) . ')' : '';

                $waText = "Hello MIDHILA VOYAGES, I have submitted a Plan My Journey enquiry (Ref: {$refNo}).\n\n"
                        . "• Destination: " . ($destName ?: 'Kerala Journey') . "\n"
                        . "• Travel Starting Date: {$depDate}\n"
                        . ($retDate ? "• Travel Ending Date: {$retDate}\n" : "")
                        . "• Total Seats: {$totalSeats}{$breakdownText}\n"
                        . "• Passenger Details:\n{$passengerWaText}\n\n"
                        . "• Travel Preferences:\n"
                        . "  - Hotel: {$hotelPref}\n"
                        . "  - Food: {$foodPref}\n"
                        . "  - Vehicle: {$vehiclePref}\n"
                        . (!empty($requests) ? "  - Special Requests: {$requests}\n" : "") . "\n"
                        . "Please check availability and revert.";

                $submittedRecord = [
                    'reference' => $refNo,
                    'name' => $fullName,
                    'phone' => $phone,
                    'wa_url' => build_whatsapp_url($agencyWhatsApp, $waText)
                ];
            } catch (Exception $e) {
                error_log("Enquiry submission error: " . $e->getMessage());
                $errors[] = 'A database error occurred while saving your request. Please message our direct WhatsApp hotline.';
            }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- ACKNOWLEDGEMENT MODAL -->
<?php if ($submittedRecord): ?>
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
      <strong style="font-family:monospace;font-size:1.3rem;color:#0F172A;letter-spacing:0.05em;"><?= e($submittedRecord['reference']) ?></strong>
    </div>
    <p style="font-size:0.9rem;color:#475569;line-height:1.65;margin-bottom:24px;text-align:left;background:#FFFBEB;border-left:4px solid #D97706;padding:12px 14px;border-radius:4px;">
      <strong>Notice:</strong> <?= __t('ack_modal_text') ?>
    </p>
    <div style="display:flex;gap:10px;">
      <a href="<?= e($submittedRecord['wa_url']) ?>" target="_blank" rel="noopener noreferrer" class="btn-whatsapp-solid" style="flex:1;justify-content:center;padding:12px;">
        <span>Chat on WhatsApp</span>
      </a>
      <a href="<?= BASE_URL ?>/index.php" class="btn-cinematic-secondary" style="flex:1;justify-content:center;padding:12px;">
        <span>Back to Home</span>
      </a>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- HERO -->
<section style="background:#0F172A;color:#FFFFFF;padding:calc(var(--header-height, 86px) + 48px) 0 60px;text-align:center;">
  <div class="container" style="max-width:820px;">
    <span style="font-size:0.8rem;text-transform:uppercase;letter-spacing:0.12em;color:#C5A059;font-weight:700;display:block;margin-bottom:12px;">
      Bespoke Itinerary &amp; Tour Inquiry
    </span>
    <h1 style="font-family:'Cinzel',serif;font-size:clamp(2rem, 4vw, 3rem);color:#FFFFFF;margin-bottom:14px;">
      <?= __t('btn_plan_trip') ?>
    </h1>
    <p style="font-size:0.95rem;color:#CBD5E1;line-height:1.7;margin:0 auto 20px;">
      Share your dates, family preferences, and desired route. Our team coordinates directly with verified properties and drivers to craft a flawless experience.
    </p>
    <div style="display:inline-flex;align-items:center;gap:6px;background:rgba(217,119,6,0.15);border:1px solid #D97706;padding:6px 16px;border-radius:20px;font-size:0.78rem;color:#FDE68A;">
      <span>ℹ️</span>
      <span><?= __t('disclaimer_notice') ?></span>
    </div>
  </div>
</section>

<!-- MAIN FORM -->
<section style="padding:60px 0 90px;background:#FAFAFA;">
  <div class="container" style="max-width:860px;">

    <?php if (!empty($errors)): ?>
      <div style="background:#FEF2F2;border:1px solid #FECACA;color:#991B1B;padding:16px 20px;border-radius:10px;margin-bottom:24px;">
        <ul style="margin:0;padding-left:18px;">
          <?php foreach ($errors as $err): ?>
            <li><?= e($err) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <div style="background:#FFFFFF;border-radius:18px;padding:38px 42px;border:1px solid #E2E8F0;box-shadow:0 10px 35px rgba(15,23,42,0.06);">
      
      <form method="POST" action="<?= BASE_URL ?>/enquiry.php">
        <?= csrf_field() ?>
        <?php if ($pkgDetails): ?>
          <input type="hidden" name="package_id" value="<?= (int)$pkgDetails['id'] ?>">
          <input type="hidden" name="package_title" value="<?= e($pkgDetails['title']) ?>">
          <div style="background:#EFF6FF;border:1px solid #BFDBFE;padding:12px 16px;border-radius:8px;margin-bottom:22px;display:flex;justify-content:space-between;align-items:center;">
            <div>
              <span style="font-size:0.72rem;text-transform:uppercase;color:#1E40AF;font-weight:700;">Selected Tour Package:</span>
              <div style="font-size:1.05rem;font-weight:700;color:#1E3A8A;"><?= e($pkgDetails['title']) ?></div>
            </div>
            <a href="<?= BASE_URL ?>/package.php?id=<?= (int)$pkgDetails['id'] ?>" target="_blank" style="font-size:0.8rem;color:#2563EB;font-weight:600;">View Package &rarr;</a>
          </div>
        <?php endif; ?>

        <!-- 1. Destination & Dates -->
        <div style="margin-bottom:30px;">
          <h3 style="font-size:1.05rem;color:#0F172A;font-weight:700;margin-bottom:14px;border-bottom:1px solid #F1F5F9;padding-bottom:8px;">
            1. Destination &amp; Dates
          </h3>
          <div class="form-grid-3col">
            <div>
              <label for="selectDestination" style="display:block;font-size:0.82rem;font-weight:700;color:#334155;margin-bottom:6px;">
                Select Destination *
              </label>
              <select id="selectDestination" name="destination_id" required style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.9rem;background:#FFF;cursor:pointer;">
                <option value="">Choose Preferred Destination</option>
                <?php foreach ($destinations as $d): ?>
                  <option value="<?= (int)$d['id'] ?>" <?= ((int)$d['id'] === $preDestId) ? 'selected' : '' ?>>
                    <?= loc($d, 'name') ?> (<?= e($d['region_state']) ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label for="travelStartDate" style="display:block;font-size:0.82rem;font-weight:700;color:#334155;margin-bottom:6px;">
                Travel Starting Date *
              </label>
              <input type="date" id="travelStartDate" name="departure_date" required min="<?= date('Y-m-d') ?>" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.9rem;background:#FFF;">
            </div>
            <div>
              <label for="travelEndDate" style="display:block;font-size:0.82rem;font-weight:700;color:#334155;margin-bottom:6px;">
                Travel Ending Date
              </label>
              <input type="date" id="travelEndDate" name="return_date" min="<?= date('Y-m-d') ?>" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.9rem;background:#FFF;">
            </div>
          </div>
        </div>

        <!-- 2. Number of Seats & Passenger Details -->
        <div style="margin-bottom:30px;">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;border-bottom:1px solid #F1F5F9;padding-bottom:8px;flex-wrap:wrap;gap:10px;">
            <h3 style="font-size:1.05rem;color:#0F172A;font-weight:700;margin:0;">
              2. Number of Seats
            </h3>
            <div id="passengerSummaryBadge" style="display:inline-flex;align-items:center;gap:6px;background:#EFF6FF;border:1px solid #BFDBFE;color:#1E40AF;padding:5px 14px;border-radius:20px;font-size:0.8rem;font-weight:700;">
              <span>Total: 1 Seat (1 Adult)</span>
            </div>
          </div>

          <!-- Number of Seats Selector -->
          <div style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:12px;padding:16px 20px;margin-bottom:18px;">
            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
              <div>
                <label for="seatsCountInput" style="display:block;font-size:0.88rem;font-weight:700;color:#0F172A;margin-bottom:2px;">
                  Select Number of Seats
                </label>
                <span style="font-size:0.78rem;color:#64748B;">
                  1st seat is fixed by default. Enter or select more seats to open fields for each passenger.
                </span>
              </div>
              <div style="display:inline-flex;align-items:center;gap:8px;">
                <button type="button" id="btnSeatMinus" style="width:38px;height:38px;border-radius:8px;border:1.5px solid #CBD5E1;background:#FFFFFF;font-size:1.25rem;font-weight:700;color:#0F172A;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:background 0.15s;" title="Decrease seats">&minus;</button>
                <input type="number" id="seatsCountInput" name="seats_count" value="1" min="1" max="50" style="width:68px;height:38px;text-align:center;font-size:1.05rem;font-weight:700;color:#0F172A;border:1.5px solid #CBD5E1;border-radius:8px;outline:none;">
                <button type="button" id="btnSeatPlus" style="width:38px;height:38px;border-radius:8px;border:1.5px solid #CBD5E1;background:#FFFFFF;font-size:1.25rem;font-weight:700;color:#0F172A;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:background 0.15s;" title="Increase seats">&#43;</button>
              </div>
            </div>
          </div>

          <!-- Dynamic Passengers Container -->
          <div id="passengersListContainer" style="display:flex;flex-direction:column;gap:14px;">
            <!-- Seat 1 (Default fixed) -->
            <div class="passenger-card" data-seat="1" style="background:#FFFFFF;border:1.5px solid #E2E8F0;border-radius:12px;padding:18px 20px;box-shadow:0 1px 3px rgba(0,0,0,0.03);">
              <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                <div style="display:flex;align-items:center;gap:10px;">
                  <span style="width:26px;height:26px;border-radius:50%;background:#0F172A;color:#FFFFFF;display:inline-flex;align-items:center;justify-content:center;font-size:0.78rem;font-weight:700;">1</span>
                  <strong style="font-size:0.9rem;color:#0F172A;">Seat 1 &bull; Primary Traveler / Contact</strong>
                </div>
                <span class="passenger-age-badge" style="font-size:0.75rem;font-weight:700;padding:3px 10px;border-radius:6px;background:#ECFDF5;color:#047857;border:1px solid #A7F3D0;">
                  Adult (12+ yrs)
                </span>
              </div>
              <div class="passenger-grid" style="display:grid;grid-template-columns:2fr 1.6fr 1fr;gap:14px;">
                <div>
                  <label style="display:block;font-size:0.78rem;font-weight:700;color:#475569;margin-bottom:4px;">Name *</label>
                  <input type="text" name="passengers[0][name]" required placeholder="e.g. Ramesh Kumar" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.9rem;">
                </div>
                <div>
                  <label style="display:block;font-size:0.78rem;font-weight:700;color:#475569;margin-bottom:4px;">Phone Number *</label>
                  <input type="tel" name="passengers[0][phone]" required placeholder="+91 62829 48227" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.9rem;">
                </div>
                <div>
                  <label style="display:block;font-size:0.78rem;font-weight:700;color:#475569;margin-bottom:4px;">Age *</label>
                  <input type="number" name="passengers[0][age]" min="1" max="120" value="30" required placeholder="Age" class="passenger-age-input" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.9rem;text-align:center;">
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- 3. Travel & Food Preferences -->
        <div style="margin-bottom:30px;">
          <h3 style="font-size:1.05rem;color:#0F172A;font-weight:700;margin-bottom:14px;border-bottom:1px solid #F1F5F9;padding-bottom:8px;">
            3. Travel &amp; Food Preferences
          </h3>
          <div class="form-grid-3col" style="margin-bottom:16px;">
            <div>
              <label for="hotelPref" style="display:block;font-size:0.82rem;font-weight:700;color:#334155;margin-bottom:6px;">
                Hotel Category
              </label>
              <select id="hotelPref" name="hotel_preference" style="width:100%;padding:10px 12px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.88rem;background:#FFF;cursor:pointer;">
                <option value="4-Star / Luxury Resort" selected>4-Star / Luxury Resort</option>
                <option value="5-Star Palace & Heritage">5-Star Palace &amp; Heritage</option>
                <option value="3-Star Clean Sanitised Hotel">3-Star Clean Sanitised Hotel</option>
                <option value="Private Houseboat (Alleppey)">Private Houseboat (Alleppey)</option>
                <option value="No Need">No Need</option>
              </select>
            </div>
            <div>
              <label for="foodPref" style="display:block;font-size:0.82rem;font-weight:700;color:#334155;margin-bottom:6px;">
                Food &amp; Meal Preferences
              </label>
              <select id="foodPref" name="food_preference" style="width:100%;padding:10px 12px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.88rem;background:#FFF;cursor:pointer;">
                <option value="All meals included (Breakfast, Lunch, Dinner)" selected>All meals included (Breakfast, Lunch, Dinner)</option>
                <option value="Breakfast only (CP Plan)">Breakfast only (CP Plan)</option>
                <option value="Half board (Breakfast + Dinner)">Half board (Breakfast + Dinner)</option>
                <option value="Traditional Kerala Sadya & Local Cuisine">Traditional Kerala Sadya &amp; Local Cuisine</option>
                <option value="No Need">No Need</option>
              </select>
            </div>
            <div>
              <label for="vehiclePref" style="display:block;font-size:0.82rem;font-weight:700;color:#334155;margin-bottom:6px;">
                Vehicle
              </label>
              <select id="vehiclePref" name="vehicle_preference" style="width:100%;padding:10px 12px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.88rem;background:#FFF;cursor:pointer;">
                <option value="Innova Crysta AC" selected>Innova Crysta AC</option>
                <option value="AC Sedan (1-3 Pax)">AC Sedan (1-3 Pax)</option>
                <option value="Tempo Traveller (12-17 Pax)">Tempo Traveller (12-17 Pax)</option>
                <option value="Luxury Coach / Bus">Luxury Coach / Bus</option>
                <option value="No Need">No Need</option>
              </select>
            </div>
          </div>

          <div>
            <label for="specialRequests" style="display:block;font-size:0.78rem;font-weight:600;color:#64748B;margin-bottom:6px;">
              Special Requests / Temple Darshans / Sightseeing
            </label>
            <textarea id="specialRequests" name="special_requests" rows="3" placeholder="Specify any specific places, elderly care needs, flight arrival timings..." style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.9rem;outline:none;line-height:1.6;"></textarea>
          </div>
        </div>

        <button type="submit" class="btn-cinematic-primary" style="width:100%;justify-content:center;padding:15px;font-size:1.05rem;">
          <span><?= __t('btn_submit_enquiry') ?> &rarr;</span>
        </button>

      </form>

    </div>

  </div>
</section>

<style>
@media (max-width: 640px) {
  .passenger-grid {
    grid-template-columns: 1fr !important;
    gap: 10px !important;
  }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const seatsInput = document.getElementById('seatsCountInput');
  const btnMinus = document.getElementById('btnSeatMinus');
  const btnPlus = document.getElementById('btnSeatPlus');
  const container = document.getElementById('passengersListContainer');
  const summaryBadge = document.getElementById('passengerSummaryBadge');
  const startDateInput = document.getElementById('travelStartDate');
  const endDateInput = document.getElementById('travelEndDate');

  // Sync return date min with starting date
  if (startDateInput && endDateInput) {
    startDateInput.addEventListener('change', function() {
      if (startDateInput.value) {
        endDateInput.min = startDateInput.value;
        if (endDateInput.value && endDateInput.value < startDateInput.value) {
          endDateInput.value = startDateInput.value;
        }
      }
    });
  }

  function getClassification(age) {
    if (age === null || age === '' || isNaN(age)) {
      return { text: 'Age required', bg: '#F1F5F9', color: '#64748B', border: '#CBD5E1' };
    }
    age = parseInt(age, 10);
    if (age >= 4 && age <= 12) {
      return { text: 'Child (4–12 yrs)', bg: '#FEF3C7', color: '#92400E', border: '#FDE68A' };
    } else if (age < 4) {
      return { text: 'Infant (< 4 yrs)', bg: '#F3E8FF', color: '#6B21A8', border: '#E9D5FF' };
    } else {
      return { text: 'Adult (12+ yrs)', bg: '#ECFDF5', color: '#047857', border: '#A7F3D0' };
    }
  }

  function updateCardBadge(card) {
    const ageInput = card.querySelector('.passenger-age-input');
    const badge = card.querySelector('.passenger-age-badge');
    if (!ageInput || !badge) return;
    const c = getClassification(ageInput.value);
    badge.textContent = c.text;
    badge.style.background = c.bg;
    badge.style.color = c.color;
    badge.style.border = '1px solid ' + c.border;
  }

  function updateSummary() {
    const cards = container.querySelectorAll('.passenger-card');
    let adultCount = 0;
    let childCount = 0;
    let infantCount = 0;

    cards.forEach(function(card) {
      const ageInput = card.querySelector('.passenger-age-input');
      const age = ageInput ? parseInt(ageInput.value, 10) : 30;
      if (!isNaN(age)) {
        if (age >= 4 && age <= 12) {
          childCount++;
        } else if (age < 4) {
          infantCount++;
        } else {
          adultCount++;
        }
      } else {
        adultCount++;
      }
    });

    const totalSeats = cards.length;
    let parts = [];
    if (adultCount > 0) parts.push(adultCount + ' Adult' + (adultCount > 1 ? 's' : ''));
    if (childCount > 0) parts.push(childCount + ' Child' + (childCount > 1 ? 'ren' : ''));
    if (infantCount > 0) parts.push(infantCount + ' Infant' + (infantCount > 1 ? 's' : ''));

    const partsText = parts.length > 0 ? (' (' + parts.join(', ') + ')') : '';
    if (summaryBadge) {
      summaryBadge.innerHTML = '<span>Total: ' + totalSeats + ' Seat' + (totalSeats > 1 ? 's' : '') + partsText + '</span>';
    }
  }

  function renderSeats(targetCount) {
    targetCount = Math.max(1, Math.min(50, parseInt(targetCount, 10) || 1));
    seatsInput.value = targetCount;

    let currentCards = container.querySelectorAll('.passenger-card');
    let currentCount = currentCards.length;

    if (targetCount > currentCount) {
      for (let i = currentCount + 1; i <= targetCount; i++) {
        const idx = i - 1;
        const card = document.createElement('div');
        card.className = 'passenger-card';
        card.setAttribute('data-seat', i);
        card.style.cssText = 'background:#FFFFFF;border:1.5px solid #E2E8F0;border-radius:12px;padding:18px 20px;box-shadow:0 1px 3px rgba(0,0,0,0.03);animation:fadeIn 0.25s ease;';
        
        card.innerHTML = 
          '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">' +
            '<div style="display:flex;align-items:center;gap:10px;">' +
              '<span style="width:26px;height:26px;border-radius:50%;background:#475569;color:#FFFFFF;display:inline-flex;align-items:center;justify-content:center;font-size:0.78rem;font-weight:700;">' + i + '</span>' +
              '<strong style="font-size:0.9rem;color:#0F172A;">Seat ' + i + ' &bull; Passenger Details</strong>' +
            '</div>' +
            '<span class="passenger-age-badge" style="font-size:0.75rem;font-weight:700;padding:3px 10px;border-radius:6px;background:#ECFDF5;color:#047857;border:1px solid #A7F3D0;">' +
              'Adult (12+ yrs)' +
            '</span>' +
          '</div>' +
          '<div class="passenger-grid" style="display:grid;grid-template-columns:2fr 1.6fr 1fr;gap:14px;">' +
            '<div>' +
              '<label style="display:block;font-size:0.78rem;font-weight:700;color:#475569;margin-bottom:4px;">Name *</label>' +
              '<input type="text" name="passengers[' + idx + '][name]" required placeholder="Passenger ' + i + ' Name" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.9rem;">' +
            '</div>' +
            '<div>' +
              '<label style="display:block;font-size:0.78rem;font-weight:700;color:#475569;margin-bottom:4px;">Phone Number (Optional)</label>' +
              '<input type="tel" name="passengers[' + idx + '][phone]" placeholder="Phone Number" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.9rem;">' +
            '</div>' +
            '<div>' +
              '<label style="display:block;font-size:0.78rem;font-weight:700;color:#475569;margin-bottom:4px;">Age *</label>' +
              '<input type="number" name="passengers[' + idx + '][age]" min="1" max="120" value="25" required placeholder="Age" class="passenger-age-input" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.9rem;text-align:center;">' +
            '</div>' +
          '</div>';

        container.appendChild(card);
        attachCardListeners(card);
      }
    } else if (targetCount < currentCount) {
      for (let i = currentCount; i > targetCount; i--) {
        const cardToRemove = container.querySelector('.passenger-card[data-seat="' + i + '"]');
        if (cardToRemove && i > 1) { // Seat 1 is always fixed!
          cardToRemove.remove();
        }
      }
    }

    updateSummary();
  }

  function attachCardListeners(card) {
    const ageInput = card.querySelector('.passenger-age-input');
    if (ageInput) {
      ageInput.addEventListener('input', function() {
        updateCardBadge(card);
        updateSummary();
      });
      ageInput.addEventListener('change', function() {
        updateCardBadge(card);
        updateSummary();
      });
    }
  }

  // Attach to initial seat 1 card
  const initialCard = container.querySelector('.passenger-card[data-seat="1"]');
  if (initialCard) {
    attachCardListeners(initialCard);
    updateCardBadge(initialCard);
  }
  updateSummary();

  // Plus / Minus Button Handlers
  if (btnMinus) {
    btnMinus.addEventListener('click', function() {
      let val = parseInt(seatsInput.value, 10) || 1;
      if (val > 1) {
        renderSeats(val - 1);
      }
    });
  }

  if (btnPlus) {
    btnPlus.addEventListener('click', function() {
      let val = parseInt(seatsInput.value, 10) || 1;
      if (val < 50) {
        renderSeats(val + 1);
      }
    });
  }

  if (seatsInput) {
    seatsInput.addEventListener('change', function() {
      let val = parseInt(seatsInput.value, 10) || 1;
      renderSeats(val);
    });
    seatsInput.addEventListener('keyup', function() {
      let val = parseInt(seatsInput.value, 10);
      if (!isNaN(val) && val >= 1) {
        renderSeats(val);
      }
    });
  }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
