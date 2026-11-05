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
$agencyWhatsApp = get_setting('whatsapp_number', '+918075021427');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token()) {
        $errors[] = 'Your security session has expired. Please refresh the page and submit again.';
    } else {
        $fullName       = trim($_POST['full_name'] ?? '');
        $email          = trim($_POST['email'] ?? '');
        $phone          = trim($_POST['phone'] ?? '');
        $whatsapp       = trim($_POST['whatsapp'] ?? $phone);
        $destId         = !empty($_POST['destination_id']) ? (int)$_POST['destination_id'] : null;
        $destName       = trim($_POST['destination_name'] ?? '');
        $pkgId          = !empty($_POST['package_id']) ? (int)$_POST['package_id'] : null;
        $pkgTitle       = trim($_POST['package_title'] ?? '');
        $enquiryType    = trim($_POST['enquiry_type'] ?? 'general_tour');
        $travelCategory = trim($_POST['travel_category'] ?? 'Custom Tour');
        $depDate        = !empty($_POST['departure_date']) ? $_POST['departure_date'] : null;
        $retDate        = !empty($_POST['return_date']) ? $_POST['return_date'] : null;
        $adults         = max(1, (int)($_POST['adults_count'] ?? 1));
        $children       = max(0, (int)($_POST['children_count'] ?? 0));
        $roomsCount     = max(1, (int)($_POST['rooms_count'] ?? 1));
        $hotelPref      = trim($_POST['hotel_preference'] ?? 'Deluxe Resort');
        $foodPref       = trim($_POST['food_preference'] ?? 'with_food');
        $foodType       = trim($_POST['food_type'] ?? 'both');
        $vehiclePref    = trim($_POST['vehicle_preference'] ?? 'Dedicated AC Sedan');
        $budget         = trim($_POST['budget_tier'] ?? 'Flexible');
        $requests       = trim($_POST['special_requests'] ?? '');

        if (empty($fullName) || strlen($fullName) < 2) {
            $errors[] = 'Please enter your full name.';
        }
        if (empty($phone) || strlen($phone) < 8) {
            $errors[] = 'Please enter a valid contact phone number.';
        }

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
                $refCount = (int)$db->query("SELECT COUNT(*) FROM enquiries WHERE reference_no LIKE 'MV-{$year}-%'")->fetchColumn() + 1;
                $refNo = sprintf("MV-%s-%04d", $year, $refCount);

                $compiledNotes = "Accommodation: {$hotelPref}\n"
                               . "Food Plan: {$foodPref} ({$foodType})\n"
                               . "Vehicle: {$vehiclePref}\n"
                               . "Rooms: {$roomsCount}\n"
                               . "Special Requests: {$requests}";

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
                    $db->prepare("INSERT INTO booking_status_history (enquiry_id, new_status, notes, created_at) VALUES (?, 'new', 'Customer submitted trip planner form', CURRENT_TIMESTAMP)")
                       ->execute([$enquiryId]);
                } catch (Exception $he) {}

                // Email log
                $emailBody = "Dear " . htmlspecialchars($fullName) . ",\n\n"
                           . "Your booking enquiry (#{$refNo}) has been received successfully.\n\n"
                           . "Destination: " . ($destName ?: 'Customized Route') . "\n"
                           . ($pkgTitle ? "Package: {$pkgTitle}\n" : "")
                           . "Dates: {$depDate} to {$retDate}\n"
                           . "Travellers: {$adults} Adults, {$children} Children\n\n"
                           . "IMPORTANT NOTICE:\n"
                           . "Our team will verify the availability of your requested services and contact you shortly. This acknowledgement does not represent a confirmed booking.\n\n"
                           . "Warm Regards,\nMidhila Voyages Management Team";

                try {
                    $db->prepare("INSERT INTO email_logs (recipient_email, recipient_name, subject, message_body, enquiry_id, status, sent_at) VALUES (?, ?, ?, ?, ?, 'sent', CURRENT_TIMESTAMP)")
                       ->execute([$email ?: 'guest@midhilavoyages.com', $fullName, "Booking Enquiry Acknowledgement - #{$refNo}", $emailBody, $enquiryId]);
                } catch (Exception $ee) {}

                // Prefilled WhatsApp message
                $waText = "Hello MIDHILA VOYAGES, I have submitted a booking enquiry (Ref: {$refNo}).\n\n"
                        . "• Name: {$fullName}\n"
                        . "• Destination: " . ($destName ?: 'Custom Route') . "\n"
                        . "• Dates: {$depDate} to {$retDate}\n"
                        . "• Travellers: {$adults} Adults" . ($children > 0 ? ", {$children} Children" : "") . "\n\n"
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
<section style="background:#0F172A;color:#FFFFFF;padding:80px 0 60px;text-align:center;">
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

        <!-- 1. Customer Details -->
        <div style="margin-bottom:26px;">
          <h3 style="font-size:1.05rem;color:#0F172A;font-weight:700;margin-bottom:14px;border-bottom:1px solid #F1F5F9;padding-bottom:6px;">
            1. Your Contact Details
          </h3>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:14px;">
            <div>
              <label style="display:block;font-size:0.82rem;font-weight:700;color:#334155;margin-bottom:4px;"><?= __t('form_full_name') ?></label>
              <input type="text" name="full_name" required placeholder="e.g. Ramesh Kumar" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.9rem;">
            </div>
            <div>
              <label style="display:block;font-size:0.82rem;font-weight:700;color:#334155;margin-bottom:4px;"><?= __t('form_phone') ?></label>
              <input type="tel" name="phone" required placeholder="+91 8075021427" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.9rem;">
            </div>
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div>
              <label style="display:block;font-size:0.82rem;font-weight:700;color:#334155;margin-bottom:4px;"><?= __t('form_email') ?></label>
              <input type="email" name="email" placeholder="ramesh@example.com" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.9rem;">
            </div>
            <div>
              <label style="display:block;font-size:0.82rem;font-weight:700;color:#334155;margin-bottom:4px;">WhatsApp Number (if different)</label>
              <input type="tel" name="whatsapp" placeholder="+91 8075021427" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.9rem;">
            </div>
          </div>
        </div>

        <!-- 2. Trip Specifics -->
        <div style="margin-bottom:26px;">
          <h3 style="font-size:1.05rem;color:#0F172A;font-weight:700;margin-bottom:14px;border-bottom:1px solid #F1F5F9;padding-bottom:6px;">
            2. Destination &amp; Dates
          </h3>
          <div style="display:grid;grid-template-columns:1.2fr 1fr 1fr;gap:16px;margin-bottom:14px;">
            <div>
              <label style="display:block;font-size:0.82rem;font-weight:700;color:#334155;margin-bottom:4px;"><?= __t('form_select_destination') ?></label>
              <select name="destination_id" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.9rem;background:#FFF;">
                <option value="0">Choose Preferred Destination</option>
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
              <label style="display:block;font-size:0.82rem;font-weight:700;color:#334155;margin-bottom:4px;"><?= __t('form_end_date') ?></label>
              <input type="date" name="return_date" min="<?= date('Y-m-d') ?>" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.9rem;background:#FFF;">
            </div>
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;">
            <div>
              <label style="display:block;font-size:0.78rem;font-weight:700;color:#334155;margin-bottom:4px;"><?= __t('form_adults') ?></label>
              <input type="number" name="adults_count" value="2" min="1" max="100" style="width:100%;padding:9px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.88rem;text-align:center;">
            </div>
            <div>
              <label style="display:block;font-size:0.78rem;font-weight:700;color:#334155;margin-bottom:4px;"><?= __t('form_children') ?></label>
              <input type="number" name="children_count" value="0" min="0" max="50" style="width:100%;padding:9px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.88rem;text-align:center;">
            </div>
            <div>
              <label style="display:block;font-size:0.78rem;font-weight:700;color:#334155;margin-bottom:4px;"><?= __t('form_rooms') ?></label>
              <input type="number" name="rooms_count" value="1" min="1" max="50" style="width:100%;padding:9px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.88rem;text-align:center;">
            </div>
          </div>
        </div>

        <!-- 3. Preferences -->
        <div style="margin-bottom:26px;">
          <h3 style="font-size:1.05rem;color:#0F172A;font-weight:700;margin-bottom:14px;border-bottom:1px solid #F1F5F9;padding-bottom:6px;">
            3. Travel &amp; Food Preferences
          </h3>
          <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;margin-bottom:14px;">
            <div>
              <label style="display:block;font-size:0.8rem;font-weight:700;color:#334155;margin-bottom:4px;">Hotel Category</label>
              <select name="hotel_preference" style="width:100%;padding:9px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.86rem;background:#FFF;">
                <option value="4-Star Resort" selected>4-Star / Luxury Resort</option>
                <option value="5-Star Palace">5-Star Palace &amp; Heritage</option>
                <option value="3-Star Hotel">3-Star Clean Sanitised Hotel</option>
                <option value="Houseboat">Private Houseboat (Alleppey)</option>
              </select>
            </div>
            <div>
              <label style="display:block;font-size:0.8rem;font-weight:700;color:#334155;margin-bottom:4px;"><?= __t('pref_food_plan') ?></label>
              <select name="food_preference" style="width:100%;padding:9px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.86rem;background:#FFF;">
                <option value="all_meals" selected><?= __t('food_all_meals') ?></option>
                <option value="breakfast_dinner"><?= __t('food_breakfast_dinner') ?></option>
                <option value="breakfast_only"><?= __t('food_breakfast_only') ?></option>
                <option value="without_food"><?= __t('food_without') ?></option>
              </select>
            </div>
            <div>
              <label style="display:block;font-size:0.8rem;font-weight:700;color:#334155;margin-bottom:4px;">Vehicle</label>
              <select name="vehicle_preference" style="width:100%;padding:9px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.86rem;background:#FFF;">
                <option value="Toyota Innova Crysta AC" selected>Innova Crysta AC</option>
                <option value="Sedan (Dzire AC)">AC Sedan (1-3 Pax)</option>
                <option value="Tempo Traveller AC">Tempo Traveller (12-17 Pax)</option>
                <option value="No Vehicle">No Transport Needed</option>
              </select>
            </div>
          </div>

          <div>
            <label style="display:block;font-size:0.78rem;font-weight:600;color:#64748B;margin-bottom:4px;">Special Requests / Temple Darshans / Sightseeing</label>
            <textarea name="special_requests" rows="3" placeholder="Specify any specific places, elderly care needs, flight arrival timings..." style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.9rem;outline:none;line-height:1.6;"></textarea>
          </div>
        </div>

        <button type="submit" class="btn-cinematic-primary" style="width:100%;justify-content:center;padding:15px;font-size:1.05rem;">
          <span><?= __t('btn_submit_enquiry') ?> &rarr;</span>
        </button>

      </form>

    </div>

  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
