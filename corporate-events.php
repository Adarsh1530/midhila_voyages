<?php
/**
 * MIDHILA VOYAGES — Business & Corporate Event Booking Module
 * Conference Halls • Seminar Halls • Convention Centres across Kerala
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';

$db = getDB();
$activeLang = current_lang();

// Venue type filter
$activeVenueType = isset($_GET['type']) ? trim($_GET['type']) : 'all';

$bookingSuccess = false;
$generatedRef = '';

// Handle Corporate Booking Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_corporate_enquiry'])) {
    if (verify_csrf_token()) {
        $orgName       = trim($_POST['corporate_organisation_name'] ?? '');
        $contactPerson = trim($_POST['full_name'] ?? '');
        $phone         = trim($_POST['phone'] ?? '');
        $email         = trim($_POST['email'] ?? '');
        $eventType     = trim($_POST['corporate_event_type'] ?? 'Annual Conference');
        $venueType     = trim($_POST['corporate_venue_type'] ?? 'conference_hall');
        $venueId       = (int)($_POST['corporate_venue_id'] ?? 0);
        $eventDate     = trim($_POST['departure_date'] ?? '');
        $durationDays  = max(1, (int)($_POST['duration_days'] ?? 1));
        $participants  = max(10, (int)($_POST['corporate_participants_count'] ?? 50));
        $seating       = trim($_POST['corporate_seating_arrangement'] ?? 'Theater');
        $catering      = trim($_POST['corporate_catering_needs'] ?? 'Buffet Lunch & Hi-Tea');
        $avNeeds       = trim($_POST['corporate_av_needs'] ?? 'LED Wall & Sound');
        $needAcc       = isset($_POST['corporate_accommodation_needed']) ? 1 : 0;
        $needTrans     = isset($_POST['corporate_transport_needed']) ? 1 : 0;
        $instructions  = trim($_POST['travel_requirements'] ?? '');

        if (!empty($orgName) && !empty($contactPerson) && !empty($phone)) {
            // Fetch venue title if selected
            $venueName = null;
            if ($venueId > 0) {
                $vStmt = $db->prepare("SELECT name_en FROM corporate_venues WHERE id = ?");
                $vStmt->execute([$venueId]);
                $venueName = $vStmt->fetchColumn() ?: null;
            }

            // Generate reference
            $year = date('Y');
            $refCount = (int)$db->query("SELECT COUNT(*) FROM enquiries WHERE reference_no LIKE 'MV-{$year}-%'")->fetchColumn() + 1;
            $generatedRef = sprintf("MV-%s-%04d", $year, $refCount);

            $insStmt = $db->prepare("
                INSERT INTO enquiries
                (reference_no, full_name, email, phone, whatsapp, destination_name,
                 enquiry_type, language, departure_date, duration_days, adults_count,
                 corporate_organisation_name, corporate_event_type, corporate_venue_type,
                 corporate_venue_id, accommodation_name, corporate_participants_count,
                 corporate_seating_arrangement, corporate_catering_needs, corporate_av_needs,
                 corporate_accommodation_needed, corporate_transport_needed, travel_requirements,
                 status, workflow_status, created_at, updated_at)
                VALUES
                (?, ?, ?, ?, ?, ?, 'corporate_event', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'new', 'new', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
            ");

            $destName = $venueName ?: ('Corporate ' . ucwords(str_replace('_', ' ', $venueType)));
            $insStmt->execute([
                $generatedRef, $contactPerson, $email, $phone, $phone, $destName,
                $activeLang, $eventDate, $durationDays, $participants,
                $orgName, $eventType, $venueType,
                $venueId ?: null, $venueName, $participants,
                $seating, $catering, $avNeeds,
                $needAcc, $needTrans, $instructions
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
                    $db->prepare("INSERT INTO customers (full_name, email, phone, whatsapp, preferred_language, total_enquiries, internal_notes, created_at) VALUES (?, ?, ?, ?, ?, 1, ?, CURRENT_TIMESTAMP)")
                       ->execute([$contactPerson, $email, $phone, $phone, $activeLang, "Organisation: {$orgName}"]);
                }
            } catch (Exception $ce) {}

            // Status history
            try {
                $db->prepare("INSERT INTO booking_status_history (enquiry_id, new_status, notes, created_at) VALUES (?, 'new', 'Corporate Event Request received', CURRENT_TIMESTAMP)")
                   ->execute([$enquiryId]);
            } catch (Exception $he) {}

            // Customer acknowledgement email log
            $emailBody = "Dear " . htmlspecialchars($contactPerson) . " ({$orgName}),\n\n"
                       . "Your Corporate Event booking enquiry (#{$generatedRef}) has been received successfully.\n\n"
                       . "Event Type: {$eventType}\n"
                       . "Expected Participants: {$participants}\n"
                       . "Preferred Venue: " . ($venueName ?: ucwords(str_replace('_', ' ', $venueType))) . "\n"
                       . "Date: {$eventDate} ({$durationDays} Days)\n\n"
                       . "IMPORTANT NOTICE:\n"
                       . "Our team will verify the availability of your requested services and contact you shortly. This acknowledgement does not represent a confirmed booking.\n\n"
                       . "Warm Regards,\nMidhila Voyages Management Team";

            try {
                $db->prepare("INSERT INTO email_logs (recipient_email, recipient_name, subject, message_body, enquiry_id, status, sent_at) VALUES (?, ?, ?, ?, ?, 'sent', CURRENT_TIMESTAMP)")
                   ->execute([$email ?: 'corporate@midhilavoyages.com', $contactPerson, "Corporate Booking Enquiry - #{$generatedRef}", $emailBody, $enquiryId]);
            } catch (Exception $ee) {}

            $bookingSuccess = true;
        } else {
            set_flash_message('error', 'Please fill in Company Name, Contact Person, and Phone Number.');
        }
    }
}

// Fetch corporate venues
$vQuery = "SELECT * FROM corporate_venues WHERE is_active = 1";
$vParams = [];
if ($activeVenueType !== 'all') {
    $vQuery .= " AND venue_type = ?";
    $vParams[] = $activeVenueType;
}
$vQuery .= " ORDER BY capacity_max DESC";
$venuesStmt = $db->prepare($vQuery);
$venuesStmt->execute($vParams);
$venues = $venuesStmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = __t('corp_title') . ' | Midhila Voyages';
$pageDescription = 'Book world-class conference halls, corporate seminar venues, and convention centres across Kerala with luxury delegate stays, high-tech AV, and bespoke catering.';

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
      Corporate Enquiry Submitted
    </h3>
    <div style="background:#F1F5F9;border:1px dashed #CBD5E1;border-radius:8px;padding:12px;margin-bottom:18px;">
      <span style="font-size:0.75rem;text-transform:uppercase;color:#64748B;font-weight:700;letter-spacing:0.06em;display:block;">
        Booking Reference Number:
      </span>
      <strong style="font-family:monospace;font-size:1.25rem;color:#0F172A;letter-spacing:0.05em;"><?= e($generatedRef) ?></strong>
    </div>
    <p style="font-size:0.9rem;color:#475569;line-height:1.65;margin-bottom:24px;text-align:left;background:#FFFBEB;border-left:4px solid #D97706;padding:12px 14px;border-radius:4px;">
      <strong>Notice:</strong> <?= __t('ack_modal_text') ?>
    </p>
    <a href="<?= BASE_URL ?>/corporate-events.php" class="btn-cinematic-primary" style="width:100%;justify-content:center;">
      <span>Explore Corporate Venues</span>
    </a>
  </div>
</div>
<?php endif; ?>

<!-- HERO -->
<section style="background:#0F172A;color:#FFFFFF;padding:calc(var(--header-height, 86px) + 48px) 0 70px;position:relative;overflow:hidden;">
  <div style="position:absolute;inset:0;background-image:url('https://images.unsplash.com/photo-1511795409834-ef04bbd61622?auto=format&fit=crop&w=1920&q=80');background-size:cover;background-position:center;opacity:0.25;pointer-events:none;"></div>

  <div class="container" style="position:relative;z-index:2;text-align:center;max-width:880px;margin:0 auto;">
    <div style="display:inline-flex;align-items:center;gap:8px;background:rgba(197,160,89,0.15);border:1px solid #C5A059;padding:6px 18px;border-radius:30px;font-size:0.8rem;letter-spacing:0.12em;text-transform:uppercase;color:#E6CA85;margin-bottom:20px;font-weight:700;">
      <span>🏢 <?= __t('nav_corporate_events') ?></span>
      <span>•</span>
      <span>Executive Management</span>
    </div>

    <h1 style="font-family:'Cinzel',serif;font-size:clamp(2rem, 4.5vw, 3.2rem);color:#FFFFFF;line-height:1.2;margin-bottom:16px;">
      <?= __t('corp_title') ?>
    </h1>

    <p style="font-size:1.05rem;color:#CBD5E1;line-height:1.75;max-width:740px;margin:0 auto 28px;">
      <?= __t('corp_subtitle') ?>
    </p>

    <!-- Venue Category Filters -->
    <div style="display:flex;justify-content:center;gap:10px;flex-wrap:wrap;">
      <a href="<?= BASE_URL ?>/corporate-events.php?type=all" 
         style="padding:10px 20px;border-radius:24px;font-size:0.86rem;font-weight:700;text-decoration:none;transition:all 0.2s;<?= ($activeVenueType === 'all') ? 'background:#C5A059;color:#0F172A;' : 'background:rgba(255,255,255,0.1);color:#FFFFFF;border:1px solid rgba(255,255,255,0.2);' ?>">
        <span>All Venues</span>
      </a>
      <a href="<?= BASE_URL ?>/corporate-events.php?type=conference_hall" 
         style="padding:10px 20px;border-radius:24px;font-size:0.86rem;font-weight:700;text-decoration:none;transition:all 0.2s;<?= ($activeVenueType === 'conference_hall') ? 'background:#C5A059;color:#0F172A;' : 'background:rgba(255,255,255,0.1);color:#FFFFFF;border:1px solid rgba(255,255,255,0.2);' ?>">
        <span>1. <?= __t('nav_conference_halls') ?></span>
      </a>
      <a href="<?= BASE_URL ?>/corporate-events.php?type=seminar_hall" 
         style="padding:10px 20px;border-radius:24px;font-size:0.86rem;font-weight:700;text-decoration:none;transition:all 0.2s;<?= ($activeVenueType === 'seminar_hall') ? 'background:#C5A059;color:#0F172A;' : 'background:rgba(255,255,255,0.1);color:#FFFFFF;border:1px solid rgba(255,255,255,0.2);' ?>">
        <span>2. <?= __t('nav_seminar_halls') ?></span>
      </a>
      <a href="<?= BASE_URL ?>/corporate-events.php?type=convention_centre" 
         style="padding:10px 20px;border-radius:24px;font-size:0.86rem;font-weight:700;text-decoration:none;transition:all 0.2s;<?= ($activeVenueType === 'convention_centre') ? 'background:#C5A059;color:#0F172A;' : 'background:rgba(255,255,255,0.1);color:#FFFFFF;border:1px solid rgba(255,255,255,0.2);' ?>">
        <span>3. <?= __t('nav_convention_centres') ?></span>
      </a>
    </div>
  </div>
</section>

<!-- MAIN WORKSPACE: VENUES SHOWCASE + CORPORATE ENQUIRY FORM -->
<section style="padding:60px 0 90px;background:#FAFAFA;">
  <div class="container">
    
    <div class="booking-workspace-grid" style="align-items:start;">

      <!-- LEFT: REGISTERED CORPORATE VENUES -->
      <div>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
          <h2 style="font-size:1.25rem;color:#0F172A;font-weight:700;margin:0;">
            Featured Venues &amp; Facilities
          </h2>
          <span style="font-size:0.8rem;color:#64748B;">(<?= count($venues) ?> Available)</span>
        </div>

        <div style="display:flex;flex-direction:column;gap:24px;">
          <?php foreach ($venues as $v): ?>
            <?php
              $vLabel = match($v['venue_type']) {
                'conference_hall' => 'Conference Hall',
                'seminar_hall' => 'Seminar Hall',
                'convention_centre' => 'Convention Centre',
                default => 'Corporate Venue'
              };
            ?>
            <div class="venue-card" style="background:#FFFFFF;border-radius:14px;overflow:hidden;border:1.5px solid #E2E8F0;box-shadow:0 3px 14px rgba(0,0,0,0.03);display:flex;flex-direction:column;">
              <div style="position:relative;height:200px;background:#0F172A;overflow:hidden;">
                <img src="<?= e($v['featured_image']) ?>" alt="<?= e(loc($v, 'name')) ?>" style="width:100%;height:100%;object-fit:cover;">
                <div style="position:absolute;top:12px;left:12px;background:rgba(15,23,42,0.9);color:#C5A059;padding:4px 12px;border-radius:6px;font-size:0.75rem;font-weight:700;border:1px solid #C5A059;">
                  <?= $vLabel ?>
                </div>
                <div style="position:absolute;bottom:12px;right:12px;background:rgba(15,23,42,0.85);color:#FFFFFF;padding:4px 10px;border-radius:6px;font-size:0.75rem;font-weight:700;">
                  Capacity: <?= (int)$v['capacity_min'] ?> - <?= (int)$v['capacity_max'] ?> Pax
                </div>
              </div>

              <div style="padding:22px;">
                <h3 style="font-size:1.18rem;font-weight:700;color:#0F172A;margin-bottom:6px;">
                  <?= loc($v, 'name') ?>
                </h3>
                <div style="font-size:0.8rem;color:#64748B;margin-bottom:12px;">
                  📍 <?= e($v['location_address']) ?>
                </div>

                <p style="font-size:0.85rem;color:#475569;line-height:1.6;margin-bottom:14px;">
                  <?= loc($v, 'description') ?>
                </p>

                <!-- Seating & AV Highlights -->
                <div style="background:#F8FAFC;border-radius:8px;padding:12px;margin-bottom:16px;font-size:0.78rem;color:#334155;">
                  <?php if (!empty($v['seating_arrangements'])): ?>
                    <div style="margin-bottom:6px;">
                      <strong style="color:#0F172A;">Seating Formats:</strong> <?= e($v['seating_arrangements']) ?>
                    </div>
                  <?php endif; ?>
                  <?php if (!empty($v['audio_visual_facilities'])): ?>
                    <div style="margin-bottom:6px;">
                      <strong style="color:#0F172A;">AV Capabilities:</strong> <?= e($v['audio_visual_facilities']) ?>
                    </div>
                  <?php endif; ?>
                  <?php if ($v['accommodation_available']): ?>
                    <div style="color:#059669;font-weight:600;">
                      ✓ Delegate accommodation available on-site
                    </div>
                  <?php endif; ?>
                </div>

                <button type="button" class="admin-btn admin-btn-outline" 
                        onclick="selectVenue(<?= (int)$v['id'] ?>, '<?= e(addslashes(loc($v, 'name'))) ?>', '<?= e($v['venue_type']) ?>')"
                        style="width:100%;justify-content:center;font-size:0.84rem;font-weight:700;padding:9px;">
                  <span>Book This Venue &darr;</span>
                </button>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- RIGHT: CORPORATE ENQUIRY FORM -->
      <div style="background:#FFFFFF;border-radius:16px;padding:34px;border:1px solid #E2E8F0;box-shadow:0 8px 30px rgba(15,23,42,0.06);position:sticky;top:calc(var(--header-height, 86px) + 20px);">
        
        <div style="border-bottom:1px solid #E2E8F0;padding-bottom:16px;margin-bottom:20px;">
          <h3 style="font-size:1.3rem;color:#0F172A;font-weight:700;margin:0 0 4px;">
            Corporate Event Enquiry Form
          </h3>
          <p style="font-size:0.84rem;color:#64748B;margin:0;">
            Submit your conference, seminar, or convention requirements. Our corporate desk coordinates proposals promptly.
          </p>
        </div>

        <form method="POST" action="<?= BASE_URL ?>/corporate-events.php" id="corporateBookingForm">
          <?= csrf_field() ?>
          <input type="hidden" name="submit_corporate_enquiry" value="1">
          <input type="hidden" name="corporate_venue_id" id="formVenueId" value="0">

          <!-- Selected Venue Banner -->
          <div id="selectedVenueBadge" style="display:none;background:#EFF6FF;border:1px solid #BFDBFE;padding:10px 14px;border-radius:8px;margin-bottom:18px;">
            <div style="font-size:0.7rem;text-transform:uppercase;color:#1E40AF;font-weight:700;">Selected Venue:</div>
            <div style="font-size:0.95rem;font-weight:700;color:#1E3A8A;" id="selectedVenueName"></div>
            <button type="button" onclick="clearSelectedVenue()" style="background:none;border:none;color:#DC2626;font-size:0.75rem;cursor:pointer;padding:0;margin-top:2px;">(Change / Choose Any)</button>
          </div>

          <!-- 1. Organisation & Contact -->
          <div style="margin-bottom:20px;">
            <div class="form-group" style="margin-bottom:14px;">
              <label style="display:block;font-size:0.82rem;font-weight:700;color:#334155;margin-bottom:4px;"><?= __t('corp_org_name') ?></label>
              <input type="text" name="corporate_organisation_name" required placeholder="e.g. Infopark Technologies Ltd" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.9rem;">
            </div>

            <div class="form-grid-2col" style="margin-bottom:14px;">
              <div>
                <label style="display:block;font-size:0.82rem;font-weight:700;color:#334155;margin-bottom:4px;"><?= __t('corp_contact_person') ?></label>
                <input type="text" name="full_name" required placeholder="e.g. Rajesh Kumar" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.9rem;">
              </div>
              <div>
                <label style="display:block;font-size:0.82rem;font-weight:700;color:#334155;margin-bottom:4px;"><?= __t('form_phone') ?></label>
                <input type="tel" name="phone" required placeholder="+91 9876543210" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.9rem;">
              </div>
            </div>

            <div class="form-group" style="margin-bottom:14px;">
              <label style="display:block;font-size:0.82rem;font-weight:700;color:#334155;margin-bottom:4px;"><?= __t('form_email') ?></label>
              <input type="email" name="email" required placeholder="rajesh@company.com" style="width:100%;padding:10px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.9rem;">
            </div>
          </div>

          <!-- 2. Event Specifics -->
          <div style="margin-bottom:20px;">
            <div class="form-grid-2col" style="margin-bottom:14px;">
              <div>
                <label style="display:block;font-size:0.82rem;font-weight:700;color:#334155;margin-bottom:4px;"><?= __t('corp_event_type') ?></label>
                <select name="corporate_event_type" style="width:100%;padding:9px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.88rem;background:#FFF;">
                  <option value="Annual Conference" selected>Annual Corporate Conference</option>
                  <option value="Technical Seminar">Technical Seminar / Symposium</option>
                  <option value="Convention / Summit">Mega Convention / Industry Summit</option>
                  <option value="Board Meeting & Offsite">Board Meeting &amp; Executive Retreat</option>
                  <option value="Product Launch">Product Launch / Press Meet</option>
                  <option value="Training Workshop">Training &amp; Development Workshop</option>
                </select>
              </div>
              <div>
                <label style="display:block;font-size:0.82rem;font-weight:700;color:#334155;margin-bottom:4px;">Venue Category</label>
                <select name="corporate_venue_type" id="formVenueTypeSelect" style="width:100%;padding:9px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.88rem;background:#FFF;">
                  <option value="conference_hall" <?= ($activeVenueType === 'conference_hall') ? 'selected' : '' ?>>Conference Hall</option>
                  <option value="seminar_hall" <?= ($activeVenueType === 'seminar_hall') ? 'selected' : '' ?>>Seminar Hall</option>
                  <option value="convention_centre" <?= ($activeVenueType === 'convention_centre') ? 'selected' : '' ?>>Convention Centre</option>
                </select>
              </div>
            </div>

            <div class="form-grid-3col" style="margin-bottom:14px;">
              <div>
                <label style="display:block;font-size:0.78rem;font-weight:700;color:#334155;margin-bottom:4px;">Event Date *</label>
                <input type="date" name="departure_date" required min="<?= date('Y-m-d') ?>" style="width:100%;padding:9px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.85rem;background:#FFF;">
              </div>
              <div>
                <label style="display:block;font-size:0.78rem;font-weight:700;color:#334155;margin-bottom:4px;">Duration (Days)</label>
                <input type="number" name="duration_days" value="1" min="1" max="14" style="width:100%;padding:9px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.85rem;text-align:center;">
              </div>
              <div>
                <label style="display:block;font-size:0.78rem;font-weight:700;color:#334155;margin-bottom:4px;">Participants</label>
                <input type="number" name="corporate_participants_count" value="80" min="10" max="5000" style="width:100%;padding:9px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.85rem;text-align:center;">
              </div>
            </div>

            <div class="form-grid-2col" style="margin-bottom:14px;">
              <div>
                <label style="display:block;font-size:0.8rem;font-weight:700;color:#334155;margin-bottom:4px;"><?= __t('corp_seating') ?></label>
                <select name="corporate_seating_arrangement" style="width:100%;padding:9px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.86rem;background:#FFF;">
                  <option value="Theater" selected>Theater Style</option>
                  <option value="Classroom">Classroom Style</option>
                  <option value="U-Shape">U-Shape Style</option>
                  <option value="Banquet Round Tables">Banquet Round Tables</option>
                  <option value="Boardroom">Boardroom Format</option>
                  <option value="Cluster">Cluster Style</option>
                </select>
              </div>
              <div>
                <label style="display:block;font-size:0.8rem;font-weight:700;color:#334155;margin-bottom:4px;">Catering Plan</label>
                <select name="corporate_catering_needs" style="width:100%;padding:9px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.86rem;background:#FFF;">
                  <option value="Full Day (Lunch, Hi-Tea, Coffee Breaks)" selected>Full Day (Lunch, Hi-Tea, Coffee Breaks)</option>
                  <option value="Gala Dinner & Cocktail">Gala Dinner &amp; Evening Reception</option>
                  <option value="Hi-Tea & Snacks Only">Hi-Tea &amp; Snacks Only</option>
                  <option value="No Catering Needed">Venue Only (No Food)</option>
                </select>
              </div>
            </div>

            <div style="margin-bottom:14px;">
              <label style="display:block;font-size:0.8rem;font-weight:700;color:#334155;margin-bottom:4px;"><?= __t('corp_av_needs') ?></label>
              <input type="text" name="corporate_av_needs" placeholder="e.g. LED Video Wall, Wireless Collar Mics, Live Webcast stream" style="width:100%;padding:9px 12px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.86rem;">
            </div>

            <div style="display:flex;gap:16px;margin-bottom:14px;flex-wrap:wrap;">
              <label style="display:flex;align-items:center;gap:6px;font-size:0.82rem;font-weight:600;color:#334155;">
                <input type="checkbox" name="corporate_accommodation_needed" value="1">
                <span><?= __t('corp_need_acc') ?></span>
              </label>
              <label style="display:flex;align-items:center;gap:6px;font-size:0.82rem;font-weight:600;color:#334155;">
                <input type="checkbox" name="corporate_transport_needed" value="1">
                <span><?= __t('corp_need_transport') ?></span>
              </label>
            </div>

            <div>
              <label style="display:block;font-size:0.78rem;font-weight:600;color:#64748B;margin-bottom:4px;">Additional Requirements / Special Notes</label>
              <textarea name="travel_requirements" rows="3" placeholder="Tell us about special branding, registration desks, VIP protocol..." style="width:100%;padding:9px 12px;border:1px solid #CBD5E1;border-radius:6px;font-size:0.86rem;"></textarea>
            </div>
          </div>

          <div style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:8px;padding:12px;margin-bottom:20px;font-size:0.75rem;color:#64748B;line-height:1.5;">
            🔒 <strong>Official Notice:</strong> Venue dates and capacity are verified by Midhila Voyages directors directly with property management. An enquiry does not confirm the slot automatically.
          </div>

          <button type="submit" class="btn-cinematic-primary" style="width:100%;justify-content:center;padding:14px;font-size:1rem;">
            <span>Submit Corporate Enquiry &rarr;</span>
          </button>

        </form>

      </div>

    </div>

  </div>
</section>

<script>
  function selectVenue(vId, vName, vType) {
    document.getElementById('formVenueId').value = vId;
    document.getElementById('selectedVenueName').textContent = vName;
    document.getElementById('selectedVenueBadge').style.display = 'block';
    document.getElementById('formVenueTypeSelect').value = vType;

    if (window.innerWidth < 992) {
      document.getElementById('corporateBookingForm').scrollIntoView({ behavior: 'smooth' });
    }
  }

  function clearSelectedVenue() {
    document.getElementById('formVenueId').value = 0;
    document.getElementById('selectedVenueBadge').style.display = 'none';
  }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
