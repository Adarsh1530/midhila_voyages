<?php
/**
 * MIDHILA VOYAGES — Admin Dashboard Overview
 * Daily Operations Hub — Enquiries, Bookings, Accommodations, and Tour Packages
 */

require_once __DIR__ . '/includes/admin-init.php';

$currentRole = get_admin_role();
$db = getDB();

// 1. Live Aggregate Statistics
$totalEnquiries = (int)$db->query("SELECT COUNT(*) FROM `enquiries`")->fetchColumn();
$newEnquiries   = (int)$db->query("SELECT COUNT(*) FROM `enquiries` WHERE `workflow_status` = 'new' OR `status` = 'new'")->fetchColumn();

// 1b. Centralised Operational Workflow KPI Metrics
$wfStats = [
    'total' => $totalEnquiries,
    'new' => $newEnquiries,
    'checking_availability' => 0,
    'availability_confirmed' => 0,
    'availability_unavailable' => 0,
    'customer_contacted' => 0,
    'followup_required' => 0,
    'booking_confirmed' => 0,
    'cancelled' => 0,
    'corporate_event' => 0
];
try {
    $wfStats['checking_availability']   = (int)$db->query("SELECT COUNT(*) FROM `enquiries` WHERE `workflow_status` = 'checking_availability'")->fetchColumn();
    $wfStats['availability_confirmed']  = (int)$db->query("SELECT COUNT(*) FROM `enquiries` WHERE `workflow_status` = 'availability_confirmed'")->fetchColumn();
    $wfStats['availability_unavailable']= (int)$db->query("SELECT COUNT(*) FROM `enquiries` WHERE `workflow_status` = 'availability_unavailable'")->fetchColumn();
    $wfStats['customer_contacted']      = (int)$db->query("SELECT COUNT(*) FROM `enquiries` WHERE `workflow_status` = 'customer_contacted' OR `status` = 'contacted'")->fetchColumn();
    $wfStats['followup_required']       = (int)$db->query("SELECT COUNT(*) FROM `enquiries` WHERE `workflow_status` = 'followup_required'")->fetchColumn();
    $wfStats['booking_confirmed']       = (int)$db->query("SELECT COUNT(*) FROM `enquiries` WHERE `workflow_status` = 'booking_confirmed' OR `status` = 'converted'")->fetchColumn();
    $wfStats['cancelled']               = (int)$db->query("SELECT COUNT(*) FROM `enquiries` WHERE `workflow_status` IN ('cancelled', 'rejected') OR `status` = 'closed'")->fetchColumn();
    $wfStats['corporate_event']         = (int)$db->query("SELECT COUNT(*) FROM `enquiries` WHERE `enquiry_type` = 'corporate_event'")->fetchColumn();
} catch (Exception $e) {}

$totalBookings     = (int)$db->query("SELECT COUNT(*) FROM `bookings`")->fetchColumn();
$confirmedBookings = (int)$db->query("SELECT COUNT(*) FROM `bookings` WHERE `booking_status` = 'confirmed'")->fetchColumn();
$pendingBookings   = (int)$db->query("SELECT COUNT(*) FROM `bookings` WHERE `booking_status` = 'pending'")->fetchColumn();

$totalPackages      = (int)$db->query("SELECT COUNT(*) FROM `packages` WHERE `is_published` = 1")->fetchColumn();
$totalDestinations  = (int)$db->query("SELECT COUNT(*) FROM `destinations` WHERE `is_published` = 1")->fetchColumn();
$totalAccommodations= 0;
$totalVenues        = 0;
$totalPillars       = 0;
try {
    $totalAccommodations = (int)$db->query("SELECT COUNT(*) FROM `accommodations` WHERE `is_active` = 1")->fetchColumn();
    $totalVenues         = (int)$db->query("SELECT COUNT(*) FROM `corporate_venues` WHERE `is_active` = 1")->fetchColumn();
    $totalPillars        = (int)$db->query("SELECT COUNT(*) FROM `tourism_categories` WHERE `is_active` = 1")->fetchColumn();
} catch (Exception $e) {}

// 2. Fetch Recent Enquiries
$recentEnquiries = $db->query("
    SELECT `id`, `reference_no`, `full_name`, `phone`, `whatsapp`, `destination_name`, `accommodation_name`,
           `enquiry_type`, `workflow_status`, `travel_category`, `travel_requirements`, `status`, `created_at`
    FROM `enquiries`
    ORDER BY `id` DESC LIMIT 6
")->fetchAll();

// 3. Fetch Recent Bookings
$recentBookings = $db->query("
    SELECT b.*, p.title AS package_title
    FROM `bookings` b
    LEFT JOIN `packages` p ON b.package_id = p.id
    ORDER BY b.id DESC LIMIT 6
")->fetchAll();

$adminTitle   = 'Dashboard Overview | MIDHILA VOYAGES';
$adminHeading = 'Operations Console';

require_once __DIR__ . '/includes/admin-header.php';
?>

<!-- =====================================================================
     OPERATIONAL SUMMARY HEADER & QUICK ACTIONS
     ===================================================================== -->
<div style="background:#FFFFFF;border:1px solid #E2E8F0;border-radius:12px;padding:22px 24px;margin-bottom:28px;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
  <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;">
    <div>
      <div style="font-size:0.75rem;text-transform:uppercase;color:#C5A059;font-weight:700;letter-spacing:0.1em;margin-bottom:4px;">
        Daily Management Console
      </div>
      <h2 style="font-size:1.4rem;font-weight:700;color:#0F172A;margin:0 0 4px 0;">
        Welcome back, <?= e($_SESSION['admin_user_name'] ?? 'Operations Manager') ?>
      </h2>
      <p style="font-size:0.86rem;color:#64748B;margin:0;">
        Review client bookings, follow up on trip inquiries, and manage the Kerala tour catalog.
      </p>
    </div>

    <!-- Daily Quick Action Shortcuts -->
    <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
      <a href="<?= ADMIN_URL ?>/package-edit.php" class="admin-btn admin-btn-primary admin-btn-sm" style="display:inline-flex;align-items:center;gap:6px;font-weight:700;background:#0F172A;">
        <span>+ Add Tour Package</span>
      </a>
      <a href="<?= ADMIN_URL ?>/accommodation-edit.php" class="admin-btn admin-btn-outline admin-btn-sm" style="display:inline-flex;align-items:center;gap:6px;font-weight:600;">
        <span>+ Add Hotel / Stay</span>
      </a>
      <a href="<?= ADMIN_URL ?>/corporate-venue-edit.php" class="admin-btn admin-btn-outline admin-btn-sm" style="display:inline-flex;align-items:center;gap:6px;font-weight:600;">
        <span>+ Add Venue</span>
      </a>
      <a href="<?= ADMIN_URL ?>/offer-popup.php" class="admin-btn admin-btn-outline admin-btn-sm" style="display:inline-flex;align-items:center;gap:6px;font-weight:600;">
        <span>Manage Offer Popup</span>
      </a>
    </div>
  </div>
</div>

<!-- =====================================================================
     KEY METRICS CARDS (DAILY COCKPIT)
     ===================================================================== -->
<div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:20px;margin-bottom:28px;">
  
  <!-- Card: Inquiries -->
  <div class="admin-card" style="padding:22px;margin-bottom:0;display:flex;flex-direction:column;justify-content:space-between;">
    <div>
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
        <span style="font-size:0.75rem;text-transform:uppercase;color:#64748B;font-weight:700;letter-spacing:0.06em;">Client Inquiries</span>
        <div style="width:40px;height:40px;border-radius:10px;background:#EFF6FF;color:#2563EB;display:flex;align-items:center;justify-content:center;">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
        </div>
      </div>
      <div style="font-size:2rem;font-weight:800;color:#0F172A;line-height:1;margin-bottom:6px;"><?= $totalEnquiries ?></div>
      <div style="font-size:0.82rem;color:#64748B;">
        <?php if ($newEnquiries > 0): ?>
          <span style="color:#DC2626;font-weight:700;">● <?= $newEnquiries ?> new awaiting review</span>
        <?php else: ?>
          <span style="color:#059669;font-weight:600;">● All inquiries up to date</span>
        <?php endif; ?>
      </div>
    </div>
    <div style="margin-top:16px;padding-top:12px;border-top:1px solid #F1F5F9;">
      <a href="<?= ADMIN_URL ?>/enquiries.php" style="font-size:0.82rem;font-weight:700;color:#2563EB;display:inline-flex;align-items:center;gap:4px;">
        Open Inquiries &rarr;
      </a>
    </div>
  </div>

  <!-- Card: Confirmed Bookings -->
  <div class="admin-card" style="padding:22px;margin-bottom:0;display:flex;flex-direction:column;justify-content:space-between;">
    <div>
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
        <span style="font-size:0.75rem;text-transform:uppercase;color:#64748B;font-weight:700;letter-spacing:0.06em;">Bookings &amp; Reservations</span>
        <div style="width:40px;height:40px;border-radius:10px;background:#F0FDF4;color:#16A34A;display:flex;align-items:center;justify-content:center;">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
        </div>
      </div>
      <div style="font-size:2rem;font-weight:800;color:#0F172A;line-height:1;margin-bottom:6px;"><?= $totalBookings ?></div>
      <div style="font-size:0.82rem;color:#64748B;">
        <strong style="color:#16A34A;"><?= $confirmedBookings ?></strong> confirmed &bull; <strong style="color:#D97706;"><?= $pendingBookings ?></strong> pending
      </div>
    </div>
    <div style="margin-top:16px;padding-top:12px;border-top:1px solid #F1F5F9;">
      <a href="<?= ADMIN_URL ?>/bookings.php" style="font-size:0.82rem;font-weight:700;color:#16A34A;display:inline-flex;align-items:center;gap:4px;">
        Manage Bookings &rarr;
      </a>
    </div>
  </div>

  <!-- Card: Tour Packages Catalog -->
  <div class="admin-card" style="padding:22px;margin-bottom:0;display:flex;flex-direction:column;justify-content:space-between;">
    <div>
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
        <span style="font-size:0.75rem;text-transform:uppercase;color:#64748B;font-weight:700;letter-spacing:0.06em;">Tour Packages</span>
        <div style="width:40px;height:40px;border-radius:10px;background:#FFFBEB;color:#D97706;display:flex;align-items:center;justify-content:center;">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"></polygon></svg>
        </div>
      </div>
      <div style="font-size:2rem;font-weight:800;color:#0F172A;line-height:1;margin-bottom:6px;">
        <?= $totalPackages ?>
      </div>
      <div style="font-size:0.82rem;color:#64748B;">
        Across <strong style="color:#0F172A;"><?= $totalDestinations ?></strong> published destinations
      </div>
    </div>
    <div style="margin-top:16px;padding-top:12px;border-top:1px solid #F1F5F9;">
      <a href="<?= ADMIN_URL ?>/packages.php" style="font-size:0.82rem;font-weight:700;color:#D97706;display:inline-flex;align-items:center;gap:4px;">
        View Packages &rarr;
      </a>
    </div>
  </div>

  <!-- Card: Kerala Tourism Pillars & Stays -->
  <div class="admin-card" style="padding:22px;margin-bottom:0;display:flex;flex-direction:column;justify-content:space-between;">
    <div>
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
        <span style="font-size:0.75rem;text-transform:uppercase;color:#64748B;font-weight:700;letter-spacing:0.06em;">Kerala Stays &amp; Venues</span>
        <div style="width:40px;height:40px;border-radius:10px;background:#F8FAFC;color:#0F172A;display:flex;align-items:center;justify-content:center;border:1px solid #E2E8F0;">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
        </div>
      </div>
      <div style="font-size:2rem;font-weight:800;color:#0F172A;line-height:1;margin-bottom:6px;">
        <?= ($totalAccommodations + $totalVenues) ?>
      </div>
      <div style="font-size:0.82rem;color:#64748B;">
        <strong><?= $totalAccommodations ?></strong> Hotels &bull; <strong><?= $totalVenues ?></strong> Venues
      </div>
    </div>
    <div style="margin-top:16px;padding-top:12px;border-top:1px solid #F1F5F9;">
      <a href="<?= ADMIN_URL ?>/accommodations.php" style="font-size:0.82rem;font-weight:700;color:#0F172A;display:inline-flex;align-items:center;gap:4px;">
        Manage Accommodations &rarr;
      </a>
    </div>
  </div>

</div>

<!-- =====================================================================
     OPERATIONAL WORKFLOW STATUS PIPELINE
     ===================================================================== -->
<div class="admin-card" style="padding:22px 24px;margin-bottom:28px;">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:12px;">
    <div>
      <h3 style="font-size:1.1rem;color:#0F172A;font-weight:700;margin:0 0 4px 0;">Trip Inquiry Workflow Pipeline</h3>
      <p style="font-size:0.8rem;color:#64748B;margin:0;">Real-time breakdown of leads through the 8 availability and confirmation stages</p>
    </div>
    <a href="<?= ADMIN_URL ?>/enquiries.php" class="admin-btn admin-btn-outline admin-btn-sm" style="font-weight:600;">
      View All Inquiries
    </a>
  </div>

  <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(130px, 1fr));gap:12px;">
    
    <a href="<?= ADMIN_URL ?>/enquiries.php?status=new" style="background:#FEF2F2;border:1px solid #FCA5A5;border-radius:8px;padding:12px;text-decoration:none;display:block;">
      <div style="font-size:0.7rem;text-transform:uppercase;color:#991B1B;font-weight:700;">1. New Inquiries</div>
      <div style="font-size:1.5rem;font-weight:800;color:#991B1B;margin-top:4px;"><?= $wfStats['new'] ?></div>
    </a>

    <a href="<?= ADMIN_URL ?>/enquiries.php?status=checking_availability" style="background:#FFFBEB;border:1px solid #FCD34D;border-radius:8px;padding:12px;text-decoration:none;display:block;">
      <div style="font-size:0.7rem;text-transform:uppercase;color:#92400E;font-weight:700;">2. Checking Hotel</div>
      <div style="font-size:1.5rem;font-weight:800;color:#92400E;margin-top:4px;"><?= $wfStats['checking_availability'] ?></div>
    </a>

    <a href="<?= ADMIN_URL ?>/enquiries.php?status=availability_confirmed" style="background:#EFF6FF;border:1px solid #93C5FD;border-radius:8px;padding:12px;text-decoration:none;display:block;">
      <div style="font-size:0.7rem;text-transform:uppercase;color:#1E40AF;font-weight:700;">3. Available</div>
      <div style="font-size:1.5rem;font-weight:800;color:#1E40AF;margin-top:4px;"><?= $wfStats['availability_confirmed'] ?></div>
    </a>

    <a href="<?= ADMIN_URL ?>/enquiries.php?status=customer_contacted" style="background:#F5F3FF;border:1px solid #C4B5FD;border-radius:8px;padding:12px;text-decoration:none;display:block;">
      <div style="font-size:0.7rem;text-transform:uppercase;color:#5B21B6;font-weight:700;">4. Contacted</div>
      <div style="font-size:1.5rem;font-weight:800;color:#5B21B6;margin-top:4px;"><?= $wfStats['customer_contacted'] ?></div>
    </a>

    <a href="<?= ADMIN_URL ?>/enquiries.php?status=followup_required" style="background:#FFF7ED;border:1px solid #FDBA74;border-radius:8px;padding:12px;text-decoration:none;display:block;">
      <div style="font-size:0.7rem;text-transform:uppercase;color:#9A3412;font-weight:700;">5. Follow-Up</div>
      <div style="font-size:1.5rem;font-weight:800;color:#9A3412;margin-top:4px;"><?= $wfStats['followup_required'] ?></div>
    </a>

    <a href="<?= ADMIN_URL ?>/enquiries.php?status=booking_confirmed" style="background:#F0FDF4;border:1px solid #86EFAC;border-radius:8px;padding:12px;text-decoration:none;display:block;">
      <div style="font-size:0.7rem;text-transform:uppercase;color:#166534;font-weight:700;">6. Confirmed</div>
      <div style="font-size:1.5rem;font-weight:800;color:#166534;margin-top:4px;"><?= $wfStats['booking_confirmed'] ?></div>
    </a>

  </div>
</div>

<!-- =====================================================================
     RECENT BOOKINGS & RECENT INQUIRIES TABLES
     ===================================================================== -->
<div style="display:grid;grid-template-columns:1fr;gap:28px;">

  <!-- Section: Recent Customer Booking Reservations -->
  <div class="admin-card" style="padding:24px 28px;margin-bottom:0;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
      <div>
        <h3 style="font-size:1.15rem;color:#0F172A;font-weight:700;margin-bottom:4px;">Recent Booking Reservations</h3>
        <p style="font-size:0.82rem;color:#64748B;margin:0;">Stored customer personal details and reserved itineraries</p>
      </div>
      <a href="<?= ADMIN_URL ?>/bookings.php" class="admin-btn admin-btn-outline admin-btn-sm" style="font-weight:700;">
        <span>View All Bookings &rarr;</span>
      </a>
    </div>

    <?php if (empty($recentBookings)): ?>
      <div style="text-align:center;padding:40px 20px;background:#F8FAFC;border-radius:10px;border:1px dashed #CBD5E1;">
        <div style="font-size:2rem;margin-bottom:8px;">🎟️</div>
        <div style="font-size:0.95rem;font-weight:600;color:#0F172A;">No booking reservations recorded yet</div>
        <p style="font-size:0.84rem;color:#64748B;margin-top:4px;">Customer reservations submitted via packages will appear here immediately.</p>
      </div>
    <?php else: ?>
      <div style="overflow-x:auto;">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Ref No</th>
              <th>Customer Name</th>
              <th>Phone / WhatsApp</th>
              <th>Tour Package</th>
              <th>Departure Date</th>
              <th>Guests</th>
              <th>Status</th>
              <th style="text-align:right;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recentBookings as $b): ?>
              <?php
                $cleanPhone = preg_replace('/[^0-9]/', '', $b['customer_phone'] ?? '');
                if (strlen($cleanPhone) === 10) $cleanPhone = '91' . $cleanPhone;
                $waMsg = urlencode("Hello {$b['customer_name']}, this is Midhila Voyages regarding your Booking Ref #{$b['booking_reference']}.");
                $waBkgUrl = "https://wa.me/{$cleanPhone}?text={$waMsg}";
              ?>
              <tr>
                <td>
                  <a href="<?= BASE_URL ?>/payment-success.php?ref=<?= e($b['booking_reference']) ?>" target="_blank" style="font-family:monospace;font-size:0.86rem;font-weight:700;color:#0F172A;text-decoration:none;">
                    <?= e($b['booking_reference']) ?> ↗
                  </a>
                </td>
                <td>
                  <strong style="color:#0F172A;"><?= e($b['customer_name']) ?></strong>
                </td>
                <td>
                  <a href="tel:<?= e($b['customer_phone']) ?>" style="color:#0F172A;font-size:0.86rem;text-decoration:none;font-weight:600;">
                    📞 <?= e($b['customer_phone']) ?>
                  </a>
                </td>
                <td>
                  <div style="font-weight:600;color:#0F172A;"><?= e($b['package_title'] ?: 'Custom Tour Package') ?></div>
                </td>
                <td style="font-size:0.86rem;color:#334155;">
                  <?= !empty($b['travel_date']) ? date('d M Y', strtotime($b['travel_date'])) : 'Flexible' ?>
                </td>
                <td style="font-size:0.86rem;color:#334155;">
                  <?= (int)$b['travellers_count'] ?> Guest<?= (int)$b['travellers_count'] === 1 ? '' : 's' ?>
                </td>
                <td>
                  <span class="badge <?= ($b['booking_status'] === 'confirmed') ? 'badge-success' : (($b['booking_status'] === 'pending') ? 'badge-warning' : 'badge-neutral') ?>">
                    <?= ucfirst(e($b['booking_status'] ?? 'pending')) ?>
                  </span>
                </td>
                <td style="text-align:right;">
                  <div style="display:inline-flex;gap:6px;justify-content:flex-end;">
                    <a href="<?= e($waBkgUrl) ?>" target="_blank" rel="noopener noreferrer" class="admin-btn admin-btn-sm" style="background:#25D366;color:#FFFFFF;border:none;font-weight:600;padding:5px 10px;border-radius:6px;display:inline-flex;align-items:center;gap:4px;" title="WhatsApp Guest">
                      <span>WhatsApp</span>
                    </a>
                    <a href="<?= BASE_URL ?>/payment-success.php?ref=<?= e($b['booking_reference']) ?>" target="_blank" class="admin-btn admin-btn-outline admin-btn-sm" style="font-weight:600;padding:5px 10px;">
                      <span>Voucher</span>
                    </a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

  <!-- Section: Recent Inquiries -->
  <div class="admin-card" style="padding:24px 28px;margin-bottom:0;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
      <div>
        <h3 style="font-size:1.15rem;color:#0F172A;font-weight:700;margin-bottom:4px;">Recent Trip Inquiries</h3>
        <p style="font-size:0.82rem;color:#64748B;margin:0;">Incoming leads for Kerala holidays, pilgrimages, and customized stays</p>
      </div>
      <a href="<?= ADMIN_URL ?>/enquiries.php" class="admin-btn admin-btn-outline admin-btn-sm" style="font-weight:700;">
        <span>View All Inquiries &rarr;</span>
      </a>
    </div>

    <?php if (empty($recentEnquiries)): ?>
      <div style="text-align:center;padding:40px 20px;background:#F8FAFC;border-radius:10px;border:1px dashed #CBD5E1;">
        <div style="font-size:2rem;margin-bottom:8px;">📬</div>
        <div style="font-size:0.95rem;font-weight:600;color:#0F172A;">No inquiries received yet</div>
      </div>
    <?php else: ?>
      <div style="overflow-x:auto;">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Ref No</th>
              <th>Traveler</th>
              <th>Destination / Stay</th>
              <th>Contact Phone</th>
              <th>Status</th>
              <th>Received</th>
              <th style="text-align:right;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recentEnquiries as $enq): ?>
              <?php
                $cleanLeadPhone = preg_replace('/[^0-9]/', '', $enq['phone']);
                if (strlen($cleanLeadPhone) === 10) $cleanLeadPhone = '91' . $cleanLeadPhone;
                $leadWaText = urlencode("Hello {$enq['full_name']}, this is Midhila Voyages regarding your travel inquiry #{$enq['reference_no']}.");
                $waLeadUrl = "https://wa.me/{$cleanLeadPhone}?text={$leadWaText}";
              ?>
              <tr>
                <td>
                  <a href="<?= ADMIN_URL ?>/enquiry-view.php?id=<?= (int)$enq['id'] ?>" style="font-family:monospace;font-size:0.86rem;font-weight:700;color:#0F172A;text-decoration:none;">
                    <?= e($enq['reference_no']) ?>
                  </a>
                </td>
                <td>
                  <strong style="color:#0F172A;"><?= e($enq['full_name']) ?></strong>
                </td>
                <td>
                  <div style="font-weight:600;color:#0F172A;font-size:0.88rem;">
                    <?= e($enq['destination_name'] ?: ($enq['accommodation_name'] ?: 'Custom Tour')) ?>
                  </div>
                </td>
                <td>
                  <a href="tel:<?= e($enq['phone']) ?>" style="font-size:0.86rem;font-weight:600;color:#0F172A;text-decoration:none;">
                    📞 <?= e($enq['phone']) ?>
                  </a>
                </td>
                <td>
                  <?php
                    $displayStatus = $enq['workflow_status'] ?: ($enq['status'] ?? 'new');
                    $badgeClass = match($displayStatus) {
                      'new' => 'badge-danger',
                      'checking_availability' => 'badge-warning',
                      'availability_confirmed' => 'badge-info',
                      'availability_unavailable' => 'badge-danger',
                      'booking_confirmed', 'converted' => 'badge-success',
                      'customer_contacted' => 'badge-neutral',
                      default => 'badge-neutral'
                    };
                  ?>
                  <span class="badge <?= $badgeClass ?>"><?= ucwords(str_replace('_', ' ', e($displayStatus))) ?></span>
                </td>
                <td style="font-size:0.82rem;color:#64748B;">
                  <?= date('d M, h:i A', strtotime($enq['created_at'])) ?>
                </td>
                <td style="text-align:right;">
                  <div style="display:inline-flex;gap:6px;justify-content:flex-end;">
                    <a href="<?= e($waLeadUrl) ?>" target="_blank" rel="noopener noreferrer" class="admin-btn admin-btn-sm" style="background:#25D366;color:#FFFFFF;border:none;font-weight:600;padding:5px 10px;border-radius:6px;display:inline-flex;align-items:center;gap:4px;">
                      <span>WhatsApp</span>
                    </a>
                    <a href="<?= ADMIN_URL ?>/enquiry-view.php?id=<?= (int)$enq['id'] ?>" class="admin-btn admin-btn-outline admin-btn-sm" style="font-weight:600;padding:5px 10px;">
                      <span>View Lead</span>
                    </a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
