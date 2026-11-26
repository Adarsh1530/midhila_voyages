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

$popupOfferActive = 1;
try {
    $poStmt = $db->query("SELECT setting_value FROM `site_settings` WHERE setting_key = 'popup_offer_active'");
    if ($poRow = $poStmt->fetch()) {
        $popupOfferActive = (int)$poRow['setting_value'];
    }
} catch (Exception $e) {}

$adminTitle   = 'Easy Admin Manager | MIDHILA VOYAGES';
$adminHeading = 'Website Manager';

require_once __DIR__ . '/includes/admin-header.php';
?>

<!-- =====================================================================
     FRIENDLY TOP WELCOME BAR
     ===================================================================== -->
<div style="background:#FFFFFF;border:1px solid #E2E8F0;border-radius:14px;padding:22px 26px;margin-bottom:26px;box-shadow:0 1px 3px rgba(0,0,0,0.03);">
  <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;">
    <div>
      <h2 style="font-size:1.45rem;font-weight:800;color:#0F172A;margin:0 0 6px 0;display:flex;align-items:center;gap:8px;">
        <span>Welcome, <?= e($_SESSION['admin_user_name'] ?? 'Midhila Manager') ?>!</span>
      </h2>
      <p style="font-size:0.9rem;color:#64748B;margin:0;">
        Click any button below to update your website instantly — simple, fast, and easy for anyone!
      </p>
    </div>

    <div style="display:flex;gap:10px;align-items:center;">
      <a href="<?= BASE_URL ?>/index.php" target="_blank" class="admin-btn admin-btn-outline" style="font-weight:700;padding:10px 18px;display:inline-flex;align-items:center;gap:8px;font-size:0.9rem;">
        <span>View Live Website</span> &nearr;
      </a>
    </div>
  </div>
</div>

<!-- =====================================================================
     6 BIG, EASY ACTION CARDS (CHILD-SIMPLE WEBSITE MANAGEMENT)
     ===================================================================== -->
<div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(310px, 1fr));gap:20px;margin-bottom:32px;">

  <!-- Card 1: Tour Packages -->
  <div style="background:#F0F7FF;border:1.5px solid #BAE6FD;border-radius:14px;padding:22px 24px;display:flex;flex-direction:column;justify-content:space-between;box-shadow:0 1px 3px rgba(0,0,0,0.02);">
    <div>
      <div style="display:flex;align-items:center;justify-content:flex-end;margin-bottom:12px;">
        <span style="background:#0284C7;color:#FFFFFF;font-size:0.78rem;font-weight:800;padding:3px 10px;border-radius:20px;">
          <?= $totalPackages ?> PACKAGES LIVE
        </span>
      </div>
      <h3 style="font-size:1.25rem;font-weight:800;color:#0F172A;margin:0 0 6px 0;">Tour Packages</h3>
      <p style="font-size:0.86rem;color:#475569;margin:0 0 16px 0;line-height:1.5;">
        Add new holiday tours, edit prices, change photos, and update trip days shown on your website.
      </p>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;padding-top:12px;border-top:1px solid #E0F2FE;">
      <a href="<?= ADMIN_URL ?>/package-edit.php" class="admin-btn admin-btn-primary" style="font-size:0.88rem;padding:9px 12px;font-weight:700;text-align:center;justify-content:center;">
        Add Package
      </a>
      <a href="<?= ADMIN_URL ?>/packages.php" class="admin-btn admin-btn-outline" style="font-size:0.88rem;padding:9px 12px;font-weight:700;text-align:center;justify-content:center;background:#FFFFFF;">
        Manage (<?= $totalPackages ?>)
      </a>
    </div>
  </div>

  <!-- Card 2: Customer Inquiries -->
  <div style="background:#FFF7ED;border:1.5px solid #FED7AA;border-radius:14px;padding:22px 24px;display:flex;flex-direction:column;justify-content:space-between;box-shadow:0 1px 3px rgba(0,0,0,0.02);">
    <div>
      <div style="display:flex;align-items:center;justify-content:flex-end;margin-bottom:12px;">
        <span style="background:<?= $newEnquiries > 0 ? '#DC2626' : '#EA580C' ?>;color:#FFFFFF;font-size:0.78rem;font-weight:800;padding:3px 10px;border-radius:20px;">
          <?= $newEnquiries > 0 ? "{$newEnquiries} NEW LEADS" : "{$totalEnquiries} TOTAL LEADS" ?>
        </span>
      </div>
      <h3 style="font-size:1.25rem;font-weight:800;color:#0F172A;margin:0 0 6px 0;">Customer Inquiries</h3>
      <p style="font-size:0.86rem;color:#475569;margin:0 0 16px 0;line-height:1.5;">
        <?= $newEnquiries > 0 ? "<strong style='color:#DC2626;'>{$newEnquiries} customer inquiries</strong> are waiting for your response!" : "All customer leads are up to date. Click below to contact travelers." ?>
      </p>
    </div>
    <div style="padding-top:12px;border-top:1px solid #FFEDD5;">
      <a href="<?= ADMIN_URL ?>/enquiries.php" class="admin-btn" style="width:100%;font-size:0.88rem;padding:9px 12px;font-weight:700;text-align:center;justify-content:center;background:#EA580C;color:#FFFFFF;border:none;">
        Open Customer Inquiries (WhatsApp) &rarr;
      </a>
    </div>
  </div>

  <!-- Card 4: Tour Bookings -->
  <div style="background:#FAF5FF;border:1.5px solid #E9D5FF;border-radius:14px;padding:22px 24px;display:flex;flex-direction:column;justify-content:space-between;box-shadow:0 1px 3px rgba(0,0,0,0.02);">
    <div>
      <div style="display:flex;align-items:center;justify-content:flex-end;margin-bottom:12px;">
        <span style="background:#9333EA;color:#FFFFFF;font-size:0.78rem;font-weight:800;padding:3px 10px;border-radius:20px;">
          <?= $totalBookings ?> BOOKINGS
        </span>
      </div>
      <h3 style="font-size:1.25rem;font-weight:800;color:#0F172A;margin:0 0 6px 0;">Tour Bookings</h3>
      <p style="font-size:0.86rem;color:#475569;margin:0 0 16px 0;line-height:1.5;">
        View customer personal booking details, reservation travel dates, and generate printable travel vouchers.
      </p>
    </div>
    <div style="padding-top:12px;border-top:1px solid #F3E8FF;">
      <a href="<?= ADMIN_URL ?>/bookings.php" class="admin-btn" style="width:100%;font-size:0.88rem;padding:9px 12px;font-weight:700;text-align:center;justify-content:center;background:#9333EA;color:#FFFFFF;border:none;">
        View Bookings &amp; Print Vouchers &rarr;
      </a>
    </div>
  </div>

  <!-- Card 5: Destinations -->
  <div style="background:#FFFBEB;border:1.5px solid #FDE68A;border-radius:14px;padding:22px 24px;display:flex;flex-direction:column;justify-content:space-between;box-shadow:0 1px 3px rgba(0,0,0,0.02);">
    <div>
      <div style="display:flex;align-items:center;justify-content:flex-end;margin-bottom:12px;">
        <span style="background:#D97706;color:#FFFFFF;font-size:0.78rem;font-weight:800;padding:3px 10px;border-radius:20px;">
          <?= $totalDestinations ?> DESTINATIONS
        </span>
      </div>
      <h3 style="font-size:1.25rem;font-weight:800;color:#0F172A;margin:0 0 6px 0;">Destinations &amp; Places</h3>
      <p style="font-size:0.86rem;color:#475569;margin:0 0 16px 0;line-height:1.5;">
        Manage Kerala tourist spots like Munnar, Alleppey, Wayanad, Kovalam, and sacred temple yatras.
      </p>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;padding-top:12px;border-top:1px solid #FEF3C7;">
      <a href="<?= ADMIN_URL ?>/destination-edit.php" class="admin-btn admin-btn-primary" style="font-size:0.88rem;padding:9px 12px;font-weight:700;text-align:center;justify-content:center;background:#B45309;border-color:#B45309;">
        Add Place
      </a>
      <a href="<?= ADMIN_URL ?>/destinations.php" class="admin-btn admin-btn-outline" style="font-size:0.88rem;padding:9px 12px;font-weight:700;text-align:center;justify-content:center;background:#FFFFFF;">
        View (<?= $totalDestinations ?>)
      </a>
    </div>
  </div>

  <!-- Card 6: Special Offer Popup Banner -->
  <div style="background:#FDF2F8;border:1.5px solid #FBCFE8;border-radius:14px;padding:22px 24px;display:flex;flex-direction:column;justify-content:space-between;box-shadow:0 1px 3px rgba(0,0,0,0.02);">
    <div>
      <div style="display:flex;align-items:center;justify-content:flex-end;margin-bottom:12px;">
        <?php if ($popupOfferActive): ?>
          <span style="background:#16A34A;color:#FFFFFF;font-size:0.78rem;font-weight:800;padding:3px 10px;border-radius:20px;">
            POPUP ACTIVE
          </span>
        <?php else: ?>
          <span style="background:#64748B;color:#FFFFFF;font-size:0.78rem;font-weight:800;padding:3px 10px;border-radius:20px;">
            POPUP PAUSED
          </span>
        <?php endif; ?>
      </div>
      <h3 style="font-size:1.25rem;font-weight:800;color:#0F172A;margin:0 0 6px 0;">Special Offer Banner</h3>
      <p style="font-size:0.86rem;color:#475569;margin:0 0 16px 0;line-height:1.5;">
        Turn your website offer popup ON or OFF with 1 click. Change offer banner text, discounts, and photo.
      </p>
    </div>
    <div style="padding-top:12px;border-top:1px solid #FCE7F3;">
      <a href="<?= ADMIN_URL ?>/offer-popup.php" class="admin-btn" style="width:100%;font-size:0.88rem;padding:9px 12px;font-weight:700;text-align:center;justify-content:center;background:#DB2777;color:#FFFFFF;border:none;">
        Turn On/Off or Change Offer &rarr;
      </a>
    </div>
  </div>

</div>

<!-- =====================================================================
     RECENT CUSTOMER INQUIRIES (LEADS WITH 1-CLICK WHATSAPP & CALL)
     ===================================================================== -->
<div style="display:grid;grid-template-columns:1fr;gap:28px;">

  <!-- Section: Recent Customer Inquiries -->
  <div class="admin-card" style="padding:24px 28px;margin-bottom:0;border-radius:14px;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;flex-wrap:wrap;gap:12px;">
      <div>
        <h3 style="font-size:1.2rem;color:#0F172A;font-weight:800;margin-bottom:4px;display:flex;align-items:center;gap:8px;">
          <span>Recent Customer Inquiries</span>
          <?php if ($newEnquiries > 0): ?>
            <span style="background:#DC2626;color:#FFF;font-size:0.75rem;padding:2px 8px;border-radius:999px;font-weight:700;"><?= $newEnquiries ?> New</span>
          <?php endif; ?>
        </h3>
        <p style="font-size:0.84rem;color:#64748B;margin:0;">Travelers who filled the inquiry form on your website. Chat with them on WhatsApp with 1 tap!</p>
      </div>
      <a href="<?= ADMIN_URL ?>/enquiries.php" class="admin-btn admin-btn-outline admin-btn-sm" style="font-weight:700;">
        <span>View All Inquiries (<?= $totalEnquiries ?>) &rarr;</span>
      </a>
    </div>

    <?php if (empty($recentEnquiries)): ?>
      <div style="text-align:center;padding:40px 20px;background:#F8FAFC;border-radius:12px;border:1.5px dashed #CBD5E1;">
        <div style="font-size:1.05rem;font-weight:700;color:#0F172A;">No Inquiries Received Yet</div>
        <p style="font-size:0.86rem;color:#64748B;margin-top:6px;max-width:500px;margin-left:auto;margin-right:auto;">
          When visitors fill the inquiry form on your website, their name, destination, and phone number will appear right here with a green WhatsApp chat button!
        </p>
      </div>
    <?php else: ?>
      <div style="overflow-x:auto;">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Ref No</th>
              <th>Customer Name</th>
              <th>Destination / Stay</th>
              <th>Phone Number</th>
              <th>Status</th>
              <th>Received</th>
              <th style="text-align:right;">Quick Actions</th>
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
                  <strong style="color:#0F172A;font-size:0.95rem;"><?= e($enq['full_name']) ?></strong>
                </td>
                <td>
                  <div style="font-weight:600;color:#0F172A;font-size:0.88rem;">
                    <?= e($enq['destination_name'] ?: ($enq['accommodation_name'] ?: 'Custom Tour')) ?>
                  </div>
                </td>
                <td>
                  <a href="tel:<?= e($enq['phone']) ?>" style="font-size:0.88rem;font-weight:700;color:#0F172A;text-decoration:none;">
                    <?= e($enq['phone']) ?>
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
                    <a href="<?= e($waLeadUrl) ?>" target="_blank" rel="noopener noreferrer" class="admin-btn admin-btn-sm" style="background:#25D366;color:#FFFFFF;border:none;font-weight:700;padding:6px 12px;border-radius:6px;display:inline-flex;align-items:center;gap:4px;">
                      <span>WhatsApp</span>
                    </a>
                    <a href="<?= ADMIN_URL ?>/enquiry-view.php?id=<?= (int)$enq['id'] ?>" class="admin-btn admin-btn-outline admin-btn-sm" style="font-weight:600;padding:6px 12px;">
                      <span>View</span>
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

  <!-- Section: Recent Bookings -->
  <div class="admin-card" style="padding:24px 28px;margin-bottom:0;border-radius:14px;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;flex-wrap:wrap;gap:12px;">
      <div>
        <h3 style="font-size:1.2rem;color:#0F172A;font-weight:800;margin-bottom:4px;display:flex;align-items:center;gap:8px;">
          <span>Recent Tour Bookings</span>
        </h3>
        <p style="font-size:0.84rem;color:#64748B;margin:0;">Stored customer personal details and instant printable vouchers</p>
      </div>
      <a href="<?= ADMIN_URL ?>/bookings.php" class="admin-btn admin-btn-outline admin-btn-sm" style="font-weight:700;">
        <span>View All Bookings (<?= $totalBookings ?>) &rarr;</span>
      </a>
    </div>

    <?php if (empty($recentBookings)): ?>
      <div style="text-align:center;padding:40px 20px;background:#F8FAFC;border-radius:12px;border:1.5px dashed #CBD5E1;">
        <div style="font-size:1.05rem;font-weight:700;color:#0F172A;">No Bookings Recorded Yet</div>
        <p style="font-size:0.86rem;color:#64748B;margin-top:6px;max-width:500px;margin-left:auto;margin-right:auto;">
          When travelers book any tour package, their reservation details will appear here with an instant printable voucher and WhatsApp link.
        </p>
      </div>
    <?php else: ?>
      <div style="overflow-x:auto;">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Ref No</th>
              <th>Customer Name</th>
              <th>Phone Number</th>
              <th>Tour Package</th>
              <th>Departure Date</th>
              <th>Guests</th>
              <th>Status</th>
              <th style="text-align:right;">Quick Actions</th>
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
                  <strong style="color:#0F172A;font-size:0.95rem;"><?= e($b['customer_name']) ?></strong>
                </td>
                <td>
                  <a href="tel:<?= e($b['customer_phone']) ?>" style="color:#0F172A;font-size:0.88rem;text-decoration:none;font-weight:700;">
                    <?= e($b['customer_phone']) ?>
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
                    <a href="<?= e($waBkgUrl) ?>" target="_blank" rel="noopener noreferrer" class="admin-btn admin-btn-sm" style="background:#25D366;color:#FFFFFF;border:none;font-weight:700;padding:6px 12px;border-radius:6px;display:inline-flex;align-items:center;gap:4px;" title="WhatsApp Guest">
                      <span>WhatsApp</span>
                    </a>
                    <a href="<?= BASE_URL ?>/payment-success.php?ref=<?= e($b['booking_reference']) ?>" target="_blank" class="admin-btn admin-btn-outline admin-btn-sm" style="font-weight:700;padding:6px 12px;">
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

</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
