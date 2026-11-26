<?php
/**
 * MIDHILA VOYAGES — Centralised Booking Enquiry Inspection & Availability Workflow
 * Full support for:
 * 1. Detailed Enquiry Parameters (Tour, Custom, Accommodation, Kerala, Corporate)
 * 2. 9-Step Availability Workflow Status
 * 3. Hotel / Venue Availability Checking Record
 * 4. Customer Communications Log & Mandatory Email Acknowledgement
 * 5. Full Status Change & Audit Trail
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

$stmt = $db->prepare("SELECT * FROM `enquiries` WHERE `id` = ? LIMIT 1");
$stmt->execute([$id]);
$enquiry = $stmt->fetch();

if (!$enquiry) {
    set_flash_message('error', 'Enquiry record not found.');
    header('Location: ' . ADMIN_URL . '/enquiries.php');
    exit;
}

$adminId = $_SESSION['admin_user_id'] ?? null;
$currentAdmin = get_logged_in_admin();
$adminName = $currentAdmin['full_name'] ?? 'Staff Admin';

// -------------------------------------------------------------
// ACTION 1: UPDATE WORKFLOW STATUS
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_workflow_status'])) {
    if (verify_csrf_token()) {
        $oldStatus = $enquiry['workflow_status'] ?: ($enquiry['status'] ?? 'new');
        $newStatus = trim($_POST['workflow_status'] ?? 'new');
        $statusNote = trim($_POST['status_note'] ?? '');
        $now = date('Y-m-d H:i:s');

        // Map to legacy status column as well
        $legacyStatus = match($newStatus) {
            'new' => 'new',
            'checking_availability' => 'in_progress',
            'availability_confirmed' => 'in_progress',
            'availability_unavailable' => 'in_progress',
            'customer_contacted' => 'contacted',
            'followup_required' => 'contacted',
            'booking_confirmed' => 'converted',
            'cancelled' => 'closed',
            'rejected' => 'closed',
            default => 'new'
        };

        $upd = $db->prepare("UPDATE `enquiries` SET `workflow_status` = ?, `status` = ?, `updated_at` = ? WHERE `id` = ?");
        $upd->execute([$newStatus, $legacyStatus, $now, $id]);

        // Audit Log
        try {
            $hStmt = $db->prepare("INSERT INTO `booking_status_history` (`enquiry_id`, `admin_id`, `old_status`, `new_status`, `notes`) VALUES (?, ?, ?, ?, ?)");
            $hStmt->execute([$id, $adminId, $oldStatus, $newStatus, $statusNote]);
        } catch (Exception $e) {}

        log_activity($adminId, 'workflow_status_change', "Changed enquiry #{$enquiry['reference_no']} to {$newStatus}");
        set_flash_message('success', "Workflow status updated to: " . ucwords(str_replace('_', ' ', $newStatus)));
        header('Location: ' . ADMIN_URL . '/enquiry-view.php?id=' . $id);
        exit;
    }
}



// -------------------------------------------------------------
// ACTION 3: SEND CUSTOMER ACKNOWLEDGEMENT EMAIL
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_acknowledgement_email'])) {
    if (verify_csrf_token()) {
        $recipientEmail = trim($_POST['recipient_email'] ?? $enquiry['email']);
        $recipientName = trim($enquiry['full_name']);
        $refNo = $enquiry['reference_no'];

        if (!empty($recipientEmail)) {
            $mandatoryDisclaimer = "Your booking enquiry has been received successfully. Our team will verify the availability of your requested services and contact you shortly. This acknowledgement does not represent a confirmed booking.";
            $subject = "Midhila Voyages Booking Enquiry Acknowledgement - Ref: " . $refNo;
            $body = "Dear " . $recipientName . ",\n\n"
                  . $mandatoryDisclaimer . "\n\n"
                  . "Enquiry Summary:\n"
                  . "• Booking Reference: " . $refNo . "\n"
                  . "• Destination / Venue: " . ($enquiry['destination_name'] ?: ($enquiry['corporate_venue_type'] ?? 'Custom Journey')) . "\n"
                  . "• Requested Dates: " . ($enquiry['departure_date'] ?: 'Flexible') . "\n"
                  . "• Travellers / Delegates: " . (int)$enquiry['adults_count'] . " Adults" . ($enquiry['children_count'] > 0 ? (", " . (int)$enquiry['children_count'] . " Children") : "") . "\n\n"
                  . "Our dedicated concierge desk is actively reviewing property and chauffeur availability. Should you require urgent changes, kindly connect with us at +91 62829 48227 or reply directly to this notice.\n\n"
                  . "Warm Regards,\nMidhila Voyages Operations Desk\ninfo@midhilavoyages.com";

            // Log Email
            try {
                $emStmt = $db->prepare("INSERT INTO `email_logs` (`recipient_email`, `recipient_name`, `subject`, `message_body`, `enquiry_id`, `status`) VALUES (?, ?, ?, ?, ?, 'sent')");
                $emStmt->execute([$recipientEmail, $recipientName, $subject, $body, $id]);

                $commStmt = $db->prepare("INSERT INTO `customer_communications` (`enquiry_id`, `admin_id`, `channel`, `subject`, `message`, `status`) VALUES (?, ?, 'email', ?, ?, 'sent')");
                $commStmt->execute([$id, $adminId, $subject, $body]);
            } catch (Exception $e) {}

            log_activity($adminId, 'send_email', "Sent acknowledgement email to {$recipientEmail} for #{$refNo}");
            set_flash_message('success', 'Customer acknowledgement email logged and dispatched.');
            header('Location: ' . ADMIN_URL . '/enquiry-view.php?id=' . $id);
            exit;
        } else {
            set_flash_message('error', 'Customer email address is not registered.');
        }
    }
}

// -------------------------------------------------------------
// ACTION 4: RECORD CUSTOMER COMMUNICATION (CALL / WA / IN PERSON)
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['log_communication'])) {
    if (verify_csrf_token()) {
        $channel = trim($_POST['comm_channel'] ?? 'phone');
        $commSubject = trim($_POST['comm_subject'] ?? 'Discussion with client');
        $commMessage = trim($_POST['comm_message'] ?? '');

        if (!empty($commMessage)) {
            $commStmt = $db->prepare("INSERT INTO `customer_communications` (`enquiry_id`, `admin_id`, `channel`, `subject`, `message`, `status`) VALUES (?, ?, ?, ?, ?, 'recorded')");
            $commStmt->execute([$id, $adminId, $channel, $commSubject, $commMessage]);

            // Auto advance status to customer_contacted if currently new or checking
            if (in_array($enquiry['workflow_status'], ['new', 'checking_availability', 'availability_confirmed'])) {
                $db->prepare("UPDATE `enquiries` SET `workflow_status` = 'customer_contacted', `status` = 'contacted' WHERE `id` = ?")->execute([$id]);
            }

            set_flash_message('success', 'Customer communication note logged.');
            header('Location: ' . ADMIN_URL . '/enquiry-view.php?id=' . $id);
            exit;
        }
    }
}

// -------------------------------------------------------------
// ACTION 5: ADD INTERNAL NOTE
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_note'])) {
    if (verify_csrf_token()) {
        $noteText = trim($_POST['note_text'] ?? '');
        if (!empty($noteText)) {
            $insNote = $db->prepare("INSERT INTO `enquiry_notes` (`enquiry_id`, `admin_id`, `note`) VALUES (?, ?, ?)");
            $insNote->execute([$id, $adminId, $noteText]);
            set_flash_message('success', 'Internal note recorded.');
            header('Location: ' . ADMIN_URL . '/enquiry-view.php?id=' . $id);
            exit;
        }
    }
}

// Fetch Notes
$notes = [];
try {
    $notesStmt = $db->prepare("
        SELECT n.*, COALESCE(a.full_name, 'Staff') AS author_name 
        FROM `enquiry_notes` n
        LEFT JOIN `admins` a ON n.admin_id = a.id
        WHERE n.enquiry_id = ?
        ORDER BY n.id DESC
    ");
    $notesStmt->execute([$id]);
    $notes = $notesStmt->fetchAll();
} catch (Exception $e) {}



// Fetch Status History
$statusHistory = [];
try {
    $shStmt = $db->prepare("
        SELECT sh.*, COALESCE(a.full_name, 'Staff') AS staff_name
        FROM `booking_status_history` sh
        LEFT JOIN `admins` a ON sh.admin_id = a.id
        WHERE sh.enquiry_id = ?
        ORDER BY sh.id DESC
    ");
    $shStmt->execute([$id]);
    $statusHistory = $shStmt->fetchAll();
} catch (Exception $e) {}

// Fetch Communications
$communications = [];
try {
    $cmStmt = $db->prepare("
        SELECT cm.*, COALESCE(a.full_name, 'Staff') AS staff_name
        FROM `customer_communications` cm
        LEFT JOIN `admins` a ON cm.admin_id = a.id
        WHERE cm.enquiry_id = ?
        ORDER BY cm.id DESC
    ");
    $cmStmt->execute([$id]);
    $communications = $cmStmt->fetchAll();
} catch (Exception $e) {}

// Pre-fill WhatsApp message
$cleanPhone = preg_replace('/[^0-9]/', '', $enquiry['phone']);
$waFollowUp = "Hello " . $enquiry['full_name'] . ", greetings from Midhila Voyages! I am following up on your booking enquiry (#" . $enquiry['reference_no'] . ") for " . ($enquiry['destination_name'] ?: 'your planned journey') . ". Our team has verified the options for you. Please let me know a convenient time to speak.";
$waUrl = build_whatsapp_url($cleanPhone, $waFollowUp);

$currentStatus = $enquiry['workflow_status'] ?: ($enquiry['status'] ?? 'new');

$adminTitle = 'Enquiry #' . e($enquiry['reference_no']) . ' | MIDHILA VOYAGES';
$adminHeading = 'Enquiry Details: ' . e($enquiry['reference_no']);

require_once __DIR__ . '/includes/admin-header.php';
?>

<!-- Back Navigation & Quick Actions -->
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
  <a href="<?= ADMIN_URL ?>/enquiries.php" class="admin-btn admin-btn-outline">
    &larr; Back to Enquiries
  </a>
  <div style="display:flex;gap:10px;align-items:center;">
    <a href="tel:<?= e($cleanPhone) ?>" class="admin-btn admin-btn-outline" style="font-weight:700;">
      Call Customer
    </a>
    <a href="<?= e($waUrl) ?>" target="_blank" rel="noopener noreferrer" class="admin-btn" style="background:#25D366;color:#FFF;font-weight:700;">
      Open WhatsApp
    </a>
  </div>
</div>



<div style="display:grid;grid-template-columns:1.8fr 1.2fr;gap:24px;">
  
  <!-- =====================================================================
       LEFT COLUMN: CUSTOMER PREFERENCES & TRIP DETAILS
       ===================================================================== -->
  <div>
    
    <!-- Header Summary Card -->
    <div class="admin-card">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;border-bottom:1px solid #E2E8F0;padding-bottom:16px;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
        <div>
          <span style="font-size:0.75rem;text-transform:uppercase;color:#64748B;font-weight:700;letter-spacing:0.05em;">Booking Reference</span>
          <h2 style="font-size:1.5rem;color:#0F172A;font-weight:800;letter-spacing:-0.02em;margin-top:2px;">
            <?= e($enquiry['reference_no']) ?>
          </h2>
          <div style="margin-top:6px;display:flex;gap:6px;align-items:center;">
            <span class="badge badge-info" style="text-transform:uppercase;font-size:0.72rem;">
              <?= e($enquiry['enquiry_type'] ?: 'Tour Package') ?>
            </span>
            <span class="badge badge-neutral" style="font-size:0.72rem;">
              Language: <?= strtoupper(e($enquiry['language'] ?? 'EN')) ?>
            </span>
          </div>
        </div>

        <!-- Quick Status Form -->
        <form method="POST" action="" style="display:flex;flex-direction:column;gap:8px;align-items:flex-end;">
          <?= csrf_field() ?>
          <input type="hidden" name="update_workflow_status" value="1">
          <div style="display:flex;gap:8px;">
            <select name="workflow_status" style="padding:8px 12px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.85rem;background:#FFFFFF;font-weight:600;color:#0F172A;">
              <option value="" disabled>Select Your Workflow Status</option>
              <option value="new" <?= ($currentStatus === 'new') ? 'selected' : '' ?>>1. New Request</option>
              <option value="checking_availability" <?= ($currentStatus === 'checking_availability') ? 'selected' : '' ?>>2. Availability Checking</option>
              <option value="availability_confirmed" <?= ($currentStatus === 'availability_confirmed') ? 'selected' : '' ?>>3. Availability Confirmed</option>
              <option value="availability_unavailable" <?= ($currentStatus === 'availability_unavailable') ? 'selected' : '' ?>>4. Unavailable</option>
              <option value="customer_contacted" <?= ($currentStatus === 'customer_contacted') ? 'selected' : '' ?>>5. Customer Contacted</option>
              <option value="followup_required" <?= ($currentStatus === 'followup_required') ? 'selected' : '' ?>>6. Follow-up Required</option>
              <option value="booking_confirmed" <?= ($currentStatus === 'booking_confirmed') ? 'selected' : '' ?>>7. Booking Confirmed</option>
              <option value="cancelled" <?= ($currentStatus === 'cancelled') ? 'selected' : '' ?>>8. Cancelled</option>
              <option value="rejected" <?= ($currentStatus === 'rejected') ? 'selected' : '' ?>>9. Rejected</option>
            </select>
            <button type="submit" class="admin-btn admin-btn-primary admin-btn-sm" style="padding:8px 14px;">Update</button>
          </div>
          <input type="text" name="status_note" placeholder="Enter your status note" style="font-size:0.78rem;padding:4px 8px;border:1px solid #CBD5E1;border-radius:6px;width:240px;">
        </form>
      </div>

      <!-- Customer Contact Details Grid -->
      <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:16px;margin-bottom:24px;background:#F8FAFC;padding:18px;border-radius:10px;border:1px solid #E2E8F0;">
        <div>
          <span style="font-size:0.72rem;color:#64748B;text-transform:uppercase;font-weight:700;">Customer Name</span>
          <div style="font-size:1.05rem;font-weight:700;color:#0F172A;"><?= e($enquiry['full_name']) ?></div>
          <?php if (!empty($enquiry['corporate_organisation_name'])): ?>
            <div style="font-size:0.8rem;color:#7E22CE;font-weight:600;">Org: <?= e($enquiry['corporate_organisation_name']) ?></div>
          <?php endif; ?>
        </div>
        <div>
          <span style="font-size:0.72rem;color:#64748B;text-transform:uppercase;font-weight:700;">Phone Number</span>
          <div style="font-size:1.05rem;font-weight:600;color:#0F172A;">
            <a href="tel:<?= e($cleanPhone) ?>" style="color:inherit;text-decoration:none;"><?= e($enquiry['phone']) ?></a>
          </div>
        </div>
        <div>
          <span style="font-size:0.72rem;color:#64748B;text-transform:uppercase;font-weight:700;">Email Address</span>
          <div style="font-size:0.92rem;color:#0F172A;word-break:break-all;"><?= e($enquiry['email'] ?: 'Not Provided') ?></div>
        </div>
        <div>
          <span style="font-size:0.72rem;color:#64748B;text-transform:uppercase;font-weight:700;">Starting Location</span>
          <div style="font-size:0.92rem;color:#0F172A;"><?= e($enquiry['starting_location'] ?: 'Not Specified') ?></div>
        </div>
      </div>

      <!-- Tourism & Travel Parameters -->
      <h4 style="font-size:1rem;color:#0F172A;font-weight:700;margin-bottom:14px;display:flex;align-items:center;gap:8px;">
        Travel &amp; Journey Parameters
      </h4>
      <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(170px, 1fr));gap:14px;background:#FFFFFF;border:1px solid #E2E8F0;padding:18px;border-radius:10px;margin-bottom:20px;">
        <div>
          <span style="font-size:0.72rem;color:#64748B;text-transform:uppercase;">Destination:</span>
          <div style="font-weight:700;color:#0F172A;"><?= e($enquiry['destination_name'] ?: 'Custom Plan') ?></div>
        </div>
        <div>
          <span style="font-size:0.72rem;color:#64748B;text-transform:uppercase;">Package Circuit:</span>
          <div style="font-weight:600;color:var(--admin-gold);"><?= e($enquiry['package_title'] ?: 'Bespoke Route') ?></div>
        </div>
        <div>
          <span style="font-size:0.72rem;color:#64748B;text-transform:uppercase;">Travel Dates:</span>
          <div style="font-weight:600;color:#0F172A;"><?= e($enquiry['departure_date'] ?: 'Flexible') ?> to <?= e($enquiry['return_date'] ?: 'Flexible') ?></div>
        </div>
        <div>
          <span style="font-size:0.72rem;color:#64748B;text-transform:uppercase;">Travellers:</span>
          <div style="font-weight:700;color:#0F172A;"><?= (int)$enquiry['adults_count'] ?> Adults <?= $enquiry['children_count'] > 0 ? (', ' . (int)$enquiry['children_count'] . ' Children') : '' ?></div>
        </div>
        <div>
          <span style="font-size:0.72rem;color:#64748B;text-transform:uppercase;">Transport Style:</span>
          <div style="font-weight:600;"><?= e($enquiry['travel_type'] ?: ($enquiry['travel_category'] ?: 'Chauffeur Driven')) ?></div>
        </div>
        <div>
          <span style="font-size:0.72rem;color:#64748B;text-transform:uppercase;">Food Preference:</span>
          <div style="font-weight:600;"><?= ucwords(str_replace('_', ' ', e($enquiry['food_preference'] ?: 'Standard'))) ?></div>
        </div>
        <div>
          <span style="font-size:0.72rem;color:#64748B;text-transform:uppercase;">Diet / Food Type:</span>
          <div style="font-weight:600;"><?= ucwords(str_replace('_', ' ', e($enquiry['food_type'] ?: 'Both Veg / Non-Veg'))) ?></div>
        </div>
        <div>
          <span style="font-size:0.72rem;color:#64748B;text-transform:uppercase;">Sightseeing Option:</span>
          <div style="font-weight:600;"><?= ucwords(str_replace('_', ' ', e($enquiry['sightseeing_preference'] ?: 'Included'))) ?></div>
        </div>
          <div>
            <span style="font-size:0.72rem;color:#64748B;text-transform:uppercase;">Budget Range:</span>
            <div style="font-weight:600;"><?= e($enquiry['budget_range'] ?: 'Standard') ?></div>
          </div>
          
          <?php if (!empty($enquiry['preferred_room_types'])): ?>
            <div style="grid-column:1/-1;">
              <span style="font-size:0.72rem;color:#64748B;text-transform:uppercase;">Preferred Room &amp; View Types:</span>
              <div style="font-weight:600;color:#0F172A;margin-top:2px;">
                <?php 
                  $rTypes = @json_decode($enquiry['preferred_room_types'], true);
                  if (is_array($rTypes)) {
                    foreach ($rTypes as $rt) {
                      echo '<span class="badge badge-neutral" style="margin-right:6px;margin-bottom:4px;">' . e(ucwords(str_replace('_', ' ', $rt))) . '</span>';
                    }
                  } else {
                    echo e($enquiry['preferred_room_types']);
                  }
                ?>
              </div>
            </div>
          <?php endif; ?>

          <?php if (!empty($enquiry['required_facilities'])): ?>
            <div style="grid-column:1/-1;">
              <span style="font-size:0.72rem;color:#64748B;text-transform:uppercase;">Required Amenities &amp; Facilities:</span>
              <div style="font-weight:600;color:#0F172A;margin-top:2px;">
                <?php 
                  $facs = @json_decode($enquiry['required_facilities'], true);
                  if (is_array($facs)) {
                    foreach ($facs as $fc) {
                      echo '<span class="badge badge-info" style="margin-right:6px;margin-bottom:4px;">' . e(ucwords(str_replace('_', ' ', $fc))) . '</span>';
                    }
                  } else {
                    echo e($enquiry['required_facilities']);
                  }
                ?>
              </div>
            </div>
          <?php endif; ?>

          <?php if (!empty($enquiry['sightseeing_places'])): ?>
            <div style="grid-column:1/-1;">
              <span style="font-size:0.72rem;color:#64748B;text-transform:uppercase;">Specific Sightseeing Places Requested:</span>
              <div style="font-size:0.88rem;color:#0F172A;margin-top:2px;"><?= nl2br(e($enquiry['sightseeing_places'])) ?></div>
            </div>
          <?php endif; ?>
        </div>

      <!-- Travel Requirements & Dietary Instructions -->
      <?php if (!empty($enquiry['travel_requirements']) || !empty($enquiry['food_special_instructions'])): ?>
        <div style="background:#FFFBEB;border:1px solid #FDE68A;padding:16px 20px;border-radius:10px;margin-bottom:20px;">
          <h5 style="font-size:0.88rem;font-weight:700;color:#92400E;margin-bottom:6px;">Client Notes &amp; Dietary Instructions:</h5>
          <?php if (!empty($enquiry['travel_requirements'])): ?>
            <p style="font-size:0.88rem;color:#78350F;line-height:1.6;margin-bottom:6px;">
              <strong>Requirements:</strong> <?= nl2br(e($enquiry['travel_requirements'])) ?>
            </p>
          <?php endif; ?>
          <?php if (!empty($enquiry['food_special_instructions'])): ?>
            <p style="font-size:0.88rem;color:#78350F;line-height:1.6;margin:0;">
              <strong>Special Food Instructions:</strong> <?= nl2br(e($enquiry['food_special_instructions'])) ?>
            </p>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <!-- Customer-Selected Mapped Places (OpenStreetMap) -->
      <?php if (!empty($enquiry['selected_places_json'])): ?>
        <?php 
          $selPlaces = @json_decode($enquiry['selected_places_json'], true);
          if (is_array($selPlaces) && !empty($selPlaces)):
        ?>
          <div style="background:#F0FDF4;border:1px solid #BBF7D0;padding:18px 20px;border-radius:10px;margin-bottom:20px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
              <h5 style="font-size:0.92rem;font-weight:700;color:#166534;margin:0;display:flex;align-items:center;gap:6px;">
                <span>Customer Selected Spots (OpenStreetMap Directory):</span>
              </h5>
              <span class="badge badge-success"><?= count($selPlaces) ?> Selected</span>
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:10px;">
              <?php foreach ($selPlaces as $sp): ?>
                <?php 
                  $spName = is_array($sp) ? ($sp['name'] ?? 'Unnamed Spot') : (string)$sp;
                  $spCat  = is_array($sp) ? ($sp['category'] ?? 'attraction') : 'place';
                  $spLat  = is_array($sp) ? ($sp['latitude'] ?? null) : null;
                  $spLng  = is_array($sp) ? ($sp['longitude'] ?? null) : null;
                  $spPrice = is_array($sp) ? ($sp['indicative_price'] ?? 0) : 0;
                  $osmLink = ($spLat && $spLng) ? "https://www.openstreetmap.org/?mlat={$spLat}&mlon={$spLng}#map=16/{$spLat}/{$spLng}" : null;
                ?>
                <div style="background:#FFFFFF;border:1px solid #DCFCE7;border-radius:8px;padding:10px 14px;box-shadow:0 1px 3px rgba(0,0,0,0.02);">
                  <div style="font-weight:700;font-size:0.86rem;color:#0F172A;display:flex;justify-content:space-between;align-items:flex-start;">
                    <span><?= e($spName) ?></span>
                    <span class="badge <?= ($spCat === 'hotel' || $spCat === 'resort') ? 'badge-info' : (($spCat === 'restaurant' || $spCat === 'cafe') ? 'badge-warning' : 'badge-success') ?>" style="font-size:0.65rem;">
                      <?= ucfirst(e($spCat)) ?>
                    </span>
                  </div>
                  <?php if ($spPrice > 0): ?>
                    <div style="font-size:0.75rem;color:#059669;font-weight:600;margin-top:2px;">₹<?= number_format($spPrice) ?></div>
                  <?php endif; ?>
                  <?php if ($osmLink): ?>
                    <div style="margin-top:4px;">
                      <a href="<?= e($osmLink) ?>" target="_blank" rel="noopener noreferrer" style="font-size:0.72rem;color:#2563EB;font-weight:600;">View Coordinates &nearr;</a>
                    </div>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>
      <?php endif; ?>

      <!-- Grounded AI Custom Itinerary Proposal -->
      <?php if (!empty($enquiry['itinerary_content'])): ?>
        <div style="background:#F8FAFC;border:1.5px solid #CBD5E1;padding:20px;border-radius:10px;margin-bottom:20px;">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;flex-wrap:wrap;gap:8px;">
            <h5 style="font-size:0.95rem;font-weight:700;color:#0F172A;margin:0;display:flex;align-items:center;gap:6px;">
              <span>Grounded AI Travel Proposal &amp; Schedule</span>
            </h5>
            <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('itineraryRawContent').innerText).then(()=>alert('Itinerary copied to clipboard!'))" class="admin-btn admin-btn-outline" style="padding:4px 10px;font-size:0.75rem;">
              Copy Proposal
            </button>
          </div>
          <div id="itineraryRawContent" style="background:#FFFFFF;border:1px solid #E2E8F0;border-radius:8px;padding:16px;font-size:0.86rem;line-height:1.7;color:#334155;white-space:pre-wrap;max-height:420px;overflow-y:auto;font-family:inherit;">
<?= htmlspecialchars($enquiry['itinerary_content']) ?>
          </div>
        </div>
      <?php endif; ?>

      <!-- Dispatch Customer Acknowledgement Email Form -->
      <div style="border-top:1px solid #E2E8F0;padding-top:18px;">
        <h4 style="font-size:0.95rem;color:#0F172A;font-weight:700;margin-bottom:8px;">Send Acknowledgement Email</h4>
        <p style="font-size:0.82rem;color:#64748B;margin-bottom:12px;">
          Sends the verified acknowledgment template to the customer with their reference number and the required booking disclaimer.
        </p>
        <form method="POST" action="" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
          <?= csrf_field() ?>
          <input type="hidden" name="send_acknowledgement_email" value="1">
          <input type="email" name="recipient_email" value="<?= e($enquiry['email']) ?>" required placeholder="Enter your email address" style="flex-grow:1;min-width:240px;padding:8px 12px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.88rem;">
          <button type="submit" class="admin-btn admin-btn-primary" style="padding:8px 16px;font-size:0.86rem;">
            Dispatch Email Notice
          </button>
        </form>
      </div>

    </div>

  </div>

  <!-- =====================================================================
       RIGHT COLUMN: STATUS AUDIT TRAIL, COMMUNICATIONS & INTERNAL NOTES
       ===================================================================== -->
  <div>

    <!-- Log Customer Communication Box -->
    <div class="admin-card" style="margin-bottom:24px;">
      <h3 style="font-size:1.1rem;color:#0F172A;font-weight:700;margin-bottom:12px;">Log Customer Communication</h3>
      <form method="POST" action="">
        <?= csrf_field() ?>
        <input type="hidden" name="log_communication" value="1">
        
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:10px;">
          <div>
            <label style="font-size:0.75rem;font-weight:700;color:#64748B;">Channel</label>
            <select name="comm_channel" style="width:100%;padding:8px 10px;border:1.5px solid #CBD5E1;border-radius:6px;font-size:0.85rem;background:#FFF;">
              <option value="" disabled>Select Your Communication Channel</option>
              <option value="phone">Phone Call</option>
              <option value="whatsapp">WhatsApp Message</option>
              <option value="email">Email</option>
              <option value="in_person">In-Person Discussion</option>
            </select>
          </div>
          <div>
            <label style="font-size:0.75rem;font-weight:700;color:#64748B;">Subject</label>
            <input type="text" name="comm_subject" value="Follow-up regarding dates and hotel options" placeholder="Enter your subject" style="width:100%;padding:8px 10px;border:1.5px solid #CBD5E1;border-radius:6px;font-size:0.85rem;">
          </div>
        </div>

        <div style="margin-bottom:12px;">
          <label style="font-size:0.75rem;font-weight:700;color:#64748B;">Summary of Conversation</label>
          <textarea name="comm_message" rows="3" required placeholder="Enter your conversation summary" style="width:100%;padding:8px 12px;border:1.5px solid #CBD5E1;border-radius:6px;font-size:0.86rem;outline:none;"></textarea>
        </div>

        <button type="submit" class="admin-btn admin-btn-primary admin-btn-sm" style="width:100%;justify-content:center;">
          Record Communication
        </button>
      </form>
    </div>

    <!-- Internal Staff Notes -->
    <div class="admin-card" style="margin-bottom:24px;">
      <h3 style="font-size:1.1rem;color:#0F172A;font-weight:700;margin-bottom:12px;">Internal Staff Notes</h3>
      
      <!-- Add Note Form -->
      <form method="POST" action="" style="margin-bottom:16px;">
        <?= csrf_field() ?>
        <input type="hidden" name="add_note" value="1">
        <textarea name="note_text" rows="3" required placeholder="Enter your internal note" 
                  style="width:100%;padding:8px 12px;border:1.5px solid #CBD5E1;border-radius:6px;font-size:0.86rem;margin-bottom:8px;outline:none;"></textarea>
        <button type="submit" class="admin-btn admin-btn-primary admin-btn-sm">Add Note</button>
      </form>

      <!-- Existing Notes List -->
      <div style="display:flex;flex-direction:column;gap:10px;max-height:240px;overflow-y:auto;">
        <?php if (empty($notes)): ?>
          <p style="font-size:0.82rem;color:#64748B;text-align:center;padding:12px;">No internal notes yet.</p>
        <?php else: ?>
          <?php foreach ($notes as $note): ?>
            <div style="background:#F8FAFC;padding:10px 12px;border-radius:6px;border:1px solid #E2E8F0;font-size:0.84rem;">
              <div style="display:flex;justify-content:space-between;margin-bottom:3px;font-size:0.74rem;color:#64748B;">
                <strong><?= e($note['author_name']) ?></strong>
                <span><?= date('d M, h:i A', strtotime($note['created_at'])) ?></span>
              </div>
              <div style="line-height:1.5;color:#0F172A;"><?= nl2br(e($note['note'])) ?></div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- Status Change Audit Trail -->
    <div class="admin-card">
      <h3 style="font-size:1.1rem;color:#0F172A;font-weight:700;margin-bottom:12px;">Workflow Status Audit Trail</h3>
      <div style="display:flex;flex-direction:column;gap:10px;max-height:280px;overflow-y:auto;">
        <?php if (empty($statusHistory)): ?>
          <div style="font-size:0.82rem;color:#64748B;padding:12px;text-align:center;">
            Initial status: <strong style="color:#0F172A;"><?= e($currentStatus) ?></strong>
          </div>
        <?php else: ?>
          <?php foreach ($statusHistory as $sh): ?>
            <div style="border-left:3px solid #0F172A;padding-left:10px;font-size:0.82rem;">
              <div style="font-weight:700;color:#0F172A;">
                <?= ucwords(str_replace('_', ' ', $sh['old_status'] ?? 'new')) ?> &rarr; <span style="color:#2563EB;"><?= ucwords(str_replace('_', ' ', $sh['new_status'])) ?></span>
              </div>
              <div style="font-size:0.74rem;color:#64748B;margin-top:2px;">
                Updated by <?= e($sh['staff_name']) ?> • <?= date('d M Y, h:i A', strtotime($sh['created_at'])) ?>
              </div>
              <?php if (!empty($sh['notes'])): ?>
                <div style="color:#475569;margin-top:2px;font-style:italic;">"<?= e($sh['notes']) ?>"</div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

  </div>

</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
