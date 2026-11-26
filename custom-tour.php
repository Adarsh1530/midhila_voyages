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
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['submit_custom_tour'])) {
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
              <input type="text" name="full_name" required placeholder="Enter your full name" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.9rem;">
            </div>
            <div>
              <label style="display:block;font-size:0.82rem;font-weight:700;color:#334155;margin-bottom:4px;"><?= __t('form_phone') ?></label>
              <input type="tel" name="phone" required placeholder="Enter your phone number" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.9rem;">
            </div>
          </div>

          <div class="form-grid-2col">
            <div>
              <label style="display:block;font-size:0.82rem;font-weight:700;color:#334155;margin-bottom:4px;"><?= __t('form_email') ?></label>
              <input type="email" name="email" placeholder="Enter your email address" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.9rem;">
            </div>
            <div>
              <label style="display:block;font-size:0.82rem;font-weight:700;color:#334155;margin-bottom:4px;"><?= __t('form_starting_location') ?></label>
              <input type="text" name="starting_location" placeholder="Enter your starting location" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.9rem;">
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
                <option value="0">Select Your Destination</option>
                <?php foreach ($destinations as $d): ?>
                  <option value="<?= (int)$d['id'] ?>" <?= ((int)$d['id'] === $preDestId) ? 'selected' : '' ?>>
                    <?= loc($d, 'name') ?> (<?= e($d['region_state']) ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label style="display:block;font-size:0.82rem;font-weight:700;color:#334155;margin-bottom:4px;"><?= __t('form_start_date') ?></label>
              <input type="date" name="departure_date" required min="<?= date('Y-m-d') ?>" title="Select Your Travel Starting Date" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.9rem;background:#FFF;">
            </div>
            <div>
              <label style="display:block;font-size:0.82rem;font-weight:700;color:#334155;margin-bottom:4px;"><?= __t('form_days') ?></label>
              <input type="number" name="duration_days" value="4" min="1" max="45" placeholder="Enter your number of days" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.9rem;text-align:center;">
            </div>
          </div>

          <div class="form-grid-3col">
            <div>
              <label style="display:block;font-size:0.82rem;font-weight:700;color:#334155;margin-bottom:4px;"><?= __t('form_adults') ?></label>
              <input type="number" name="adults_count" value="2" min="1" max="100" placeholder="Enter your number of adults" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.9rem;text-align:center;">
            </div>
            <div>
              <label style="display:block;font-size:0.82rem;font-weight:700;color:#334155;margin-bottom:4px;"><?= __t('form_children') ?></label>
              <input type="number" name="children_count" value="0" min="0" max="50" placeholder="Enter your number of children" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.9rem;text-align:center;">
            </div>
            <div>
              <label style="display:block;font-size:0.82rem;font-weight:700;color:#334155;margin-bottom:4px;"><?= __t('form_travel_type') ?></label>
              <select name="travel_type" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.9rem;background:#FFF;">
                <option value="" disabled>Select Your Travel Type</option>
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

        <button type="submit" class="btn-cinematic-primary" style="width:100%;justify-content:center;padding:15px;font-size:1.05rem;margin-top:10px;">
          <span>Submit Customised Tour Request &rarr;</span>
        </button>

      </form>

    </div>

  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
