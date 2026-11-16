<?php
/**
 * MIDHILA VOYAGES — Admin Dashboard Overview
 * Clean, Friendly, and Role-Aware Operations Hub
 */

require_once __DIR__ . '/includes/admin-init.php';

$currentRole = get_admin_role();
$db = getDB();

// 1. Live Aggregate Statistics
$totalEnquiries = (int)$db->query("SELECT COUNT(*) FROM `enquiries`")->fetchColumn();
$newEnquiries   = (int)$db->query("SELECT COUNT(*) FROM `enquiries` WHERE `workflow_status` = 'new' OR `status` = 'new'")->fetchColumn();

// 1b. Centralised 10 Operational Workflow KPI Metrics
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
    $wfStats['checking_availability'] = (int)$db->query("SELECT COUNT(*) FROM `enquiries` WHERE `workflow_status` = 'checking_availability'")->fetchColumn();
    $wfStats['availability_confirmed'] = (int)$db->query("SELECT COUNT(*) FROM `enquiries` WHERE `workflow_status` = 'availability_confirmed'")->fetchColumn();
    $wfStats['availability_unavailable'] = (int)$db->query("SELECT COUNT(*) FROM `enquiries` WHERE `workflow_status` = 'availability_unavailable'")->fetchColumn();
    $wfStats['customer_contacted'] = (int)$db->query("SELECT COUNT(*) FROM `enquiries` WHERE `workflow_status` = 'customer_contacted' OR `status` = 'contacted'")->fetchColumn();
    $wfStats['followup_required'] = (int)$db->query("SELECT COUNT(*) FROM `enquiries` WHERE `workflow_status` = 'followup_required'")->fetchColumn();
    $wfStats['booking_confirmed'] = (int)$db->query("SELECT COUNT(*) FROM `enquiries` WHERE `workflow_status` = 'booking_confirmed' OR `status` = 'converted'")->fetchColumn();
    $wfStats['cancelled'] = (int)$db->query("SELECT COUNT(*) FROM `enquiries` WHERE `workflow_status` IN ('cancelled', 'rejected') OR `status` = 'closed'")->fetchColumn();
    $wfStats['corporate_event'] = (int)$db->query("SELECT COUNT(*) FROM `enquiries` WHERE `enquiry_type` = 'corporate_event'")->fetchColumn();
} catch (Exception $e) {}

$totalBookings     = (int)$db->query("SELECT COUNT(*) FROM `bookings`")->fetchColumn();
$confirmedBookings = (int)$db->query("SELECT COUNT(*) FROM `bookings` WHERE `booking_status` = 'confirmed'")->fetchColumn();

$totalCapturedPayments = (float)$db->query("SELECT COALESCE(SUM(amount), 0) FROM `payments` WHERE `status` = 'captured'")->fetchColumn();
$totalPaymentsCount    = (int)$db->query("SELECT COUNT(*) FROM `payments`")->fetchColumn();
$capturedPaymentsCount = (int)$db->query("SELECT COUNT(*) FROM `payments` WHERE `status` = 'captured'")->fetchColumn();

$totalPackages     = (int)$db->query("SELECT COUNT(*) FROM `packages` WHERE `is_published` = 1")->fetchColumn();
$totalDestinations = (int)$db->query("SELECT COUNT(*) FROM `destinations` WHERE `is_published` = 1")->fetchColumn();

// 2. Fetch Recent Enquiries (for Super Admin & Travel Manage)
$recentEnquiries = [];
if (has_role(['super_admin', 'travel_manage'])) {
    $recentEnquiries = $db->query("
        SELECT `id`, `reference_no`, `full_name`, `phone`, `whatsapp`, `destination_name`, `accommodation_name`,
               `enquiry_type`, `workflow_status`, `travel_category`, `travel_requirements`, `status`, `created_at`
        FROM `enquiries`
        ORDER BY `id` DESC LIMIT 6
    ")->fetchAll();
}

// 3. Fetch Recent Bookings (All roles)
$recentBookings = $db->query("
    SELECT `id`, `booking_reference`, `customer_name`, `customer_phone`, `travel_date`,
           `total_amount`, `booking_status`, `payment_status`, `created_at`
    FROM `bookings`
    ORDER BY `id` DESC LIMIT 5
")->fetchAll();

// 4. Fetch Recent Payments (for Payment Handle)
$recentPayments = [];
if (has_role(['payment_handle'])) {
    $recentPayments = $db->query("
        SELECT p.*, b.customer_name, b.customer_phone
        FROM `payments` p
        LEFT JOIN `bookings` b ON p.booking_id = b.id
        ORDER BY p.id DESC LIMIT 5
    ")->fetchAll();
}

$adminTitle   = 'Dashboard Overview | MIDHILA VOYAGES';
$adminHeading = (function() use ($currentRole) {
    switch ($currentRole) {
        case 'payment_handle':
            return 'Payment & Financial Dashboard';
        case 'travel_manage':
            return 'Travel Operations Dashboard';
        default:
            return 'Operations Dashboard';
    }
})();

require_once __DIR__ . '/includes/admin-header.php';
?>

<?php if (has_role(['super_admin', 'travel_manage'])): ?>
<!-- =====================================================================
     0. CENTRALISED BOOKING WORKFLOW OPERATIONS DASHBOARD (10 KPIS)
     ===================================================================== -->
<div class="admin-card" style="padding:22px 24px;margin-bottom:28px;">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;flex-wrap:wrap;gap:10px;">
    <div>
      <h3 style="font-size:1.15rem;font-weight:700;color:#0F172A;margin-bottom:2px;">Booking &amp; Availability Operations Hub</h3>
      <p style="font-size:0.82rem;color:#64748B;margin:0;">Real-time status tracking across customer journeys, property availability checks, and corporate leads.</p>
    </div>
    <a href="<?= ADMIN_URL ?>/enquiries.php" class="admin-btn admin-btn-outline admin-btn-sm" style="font-weight:700;">
      Centralised Enquiries Hub &rarr;
    </a>
  </div>

  <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));gap:12px;">
    
    <!-- 1. Total Enquiries -->
    <a href="<?= ADMIN_URL ?>/enquiries.php" style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:10px;padding:14px;text-decoration:none;transition:all 0.15s;display:block;">
      <div style="font-size:0.72rem;text-transform:uppercase;color:#64748B;font-weight:700;">Total Enquiries</div>
      <div style="font-size:1.6rem;font-weight:800;color:#0F172A;line-height:1.2;margin-top:4px;"><?= $wfStats['total'] ?></div>
      <div style="font-size:0.74rem;color:#64748B;margin-top:4px;">All Channels</div>
    </a>

    <!-- 2. New Requests -->
    <a href="<?= ADMIN_URL ?>/enquiries.php?status=new" style="background:#FEF2F2;border:1px solid #FECACA;border-radius:10px;padding:14px;text-decoration:none;transition:all 0.15s;display:block;">
      <div style="font-size:0.72rem;text-transform:uppercase;color:#991B1B;font-weight:700;">New Requests</div>
      <div style="font-size:1.6rem;font-weight:800;color:#DC2626;line-height:1.2;margin-top:4px;"><?= $wfStats['new'] ?></div>
      <div style="font-size:0.74rem;color:#B91C1C;margin-top:4px;">Needs Review</div>
    </a>

    <!-- 3. Pending Availability Checks -->
    <a href="<?= ADMIN_URL ?>/enquiries.php?status=checking_availability" style="background:#FFFBEB;border:1px solid #FDE68A;border-radius:10px;padding:14px;text-decoration:none;transition:all 0.15s;display:block;">
      <div style="font-size:0.72rem;text-transform:uppercase;color:#92400E;font-weight:700;">Checking Availability</div>
      <div style="font-size:1.6rem;font-weight:800;color:#D97706;line-height:1.2;margin-top:4px;"><?= $wfStats['checking_availability'] ?></div>
      <div style="font-size:0.74rem;color:#B45309;margin-top:4px;">Hotel / Venue Query</div>
    </a>

    <!-- 4. Availability Confirmed -->
    <a href="<?= ADMIN_URL ?>/enquiries.php?status=availability_confirmed" style="background:#EFF6FF;border:1px solid #BFDBFE;border-radius:10px;padding:14px;text-decoration:none;transition:all 0.15s;display:block;">
      <div style="font-size:0.72rem;text-transform:uppercase;color:#1E40AF;font-weight:700;">Availability Confirmed</div>
      <div style="font-size:1.6rem;font-weight:800;color:#2563EB;line-height:1.2;margin-top:4px;"><?= $wfStats['availability_confirmed'] ?></div>
      <div style="font-size:0.74rem;color:#1D4ED8;margin-top:4px;">Ready to Quote</div>
    </a>

    <!-- 5. Unavailable -->
    <a href="<?= ADMIN_URL ?>/enquiries.php?status=availability_unavailable" style="background:#FEE2E2;border:1px solid #FCA5A5;border-radius:10px;padding:14px;text-decoration:none;transition:all 0.15s;display:block;">
      <div style="font-size:0.72rem;text-transform:uppercase;color:#991B1B;font-weight:700;">Unavailable</div>
      <div style="font-size:1.6rem;font-weight:800;color:#B91C1C;line-height:1.2;margin-top:4px;"><?= $wfStats['availability_unavailable'] ?></div>
      <div style="font-size:0.74rem;color:#7F1D1D;margin-top:4px;">Alternate Needed</div>
    </a>

    <!-- 6. Customers Contacted -->
    <a href="<?= ADMIN_URL ?>/enquiries.php?status=customer_contacted" style="background:#F1F5F9;border:1px solid #CBD5E1;border-radius:10px;padding:14px;text-decoration:none;transition:all 0.15s;display:block;">
      <div style="font-size:0.72rem;text-transform:uppercase;color:#475569;font-weight:700;">Customers Contacted</div>
      <div style="font-size:1.6rem;font-weight:800;color:#334155;line-height:1.2;margin-top:4px;"><?= $wfStats['customer_contacted'] ?></div>
      <div style="font-size:0.74rem;color:#64748B;margin-top:4px;">Proposal Sent</div>
    </a>

    <!-- 7. Follow-up Required -->
    <a href="<?= ADMIN_URL ?>/enquiries.php?status=followup_required" style="background:#FEF3C7;border:1px solid #FCD34D;border-radius:10px;padding:14px;text-decoration:none;transition:all 0.15s;display:block;">
      <div style="font-size:0.72rem;text-transform:uppercase;color:#B45309;font-weight:700;">Follow-up Needed</div>
      <div style="font-size:1.6rem;font-weight:800;color:#B45309;line-height:1.2;margin-top:4px;"><?= $wfStats['followup_required'] ?></div>
      <div style="font-size:0.74rem;color:#78350F;margin-top:4px;">Pending Response</div>
    </a>

    <!-- 8. Confirmed Bookings -->
    <a href="<?= ADMIN_URL ?>/enquiries.php?status=booking_confirmed" style="background:#ECFDF5;border:1px solid #A7F3D0;border-radius:10px;padding:14px;text-decoration:none;transition:all 0.15s;display:block;">
      <div style="font-size:0.72rem;text-transform:uppercase;color:#065F46;font-weight:700;">Confirmed Bookings</div>
      <div style="font-size:1.6rem;font-weight:800;color:#059669;line-height:1.2;margin-top:4px;"><?= $wfStats['booking_confirmed'] ?></div>
      <div style="font-size:0.74rem;color:#047857;margin-top:4px;">Vouchers Active</div>
    </a>

    <!-- 9. Cancelled Bookings -->
    <a href="<?= ADMIN_URL ?>/enquiries.php?status=cancelled" style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:10px;padding:14px;text-decoration:none;transition:all 0.15s;display:block;">
      <div style="font-size:0.72rem;text-transform:uppercase;color:#64748B;font-weight:700;">Cancelled / Rejected</div>
      <div style="font-size:1.6rem;font-weight:800;color:#64748B;line-height:1.2;margin-top:4px;"><?= $wfStats['cancelled'] ?></div>
      <div style="font-size:0.74rem;color:#94A3B8;margin-top:4px;">Closed Leads</div>
    </a>

    <!-- 10. Corporate Event Enquiries -->
    <a href="<?= ADMIN_URL ?>/enquiries.php?tab=corporate_event" style="background:#FAF5FF;border:1px solid #E9D5FF;border-radius:10px;padding:14px;text-decoration:none;transition:all 0.15s;display:block;">
      <div style="font-size:0.72rem;text-transform:uppercase;color:#6B21A8;font-weight:700;">Corporate Events</div>
      <div style="font-size:1.6rem;font-weight:800;color:#7E22CE;line-height:1.2;margin-top:4px;"><?= $wfStats['corporate_event'] ?></div>
      <div style="font-size:0.74rem;color:#581C87;margin-top:4px;">Conventions &amp; Halls</div>
    </a>

  </div>
</div>
<?php endif; ?>

<!-- =====================================================================
     1. ROLE-TAILORED OPERATIONAL STAT CARDS
     ===================================================================== -->
<div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(230px, 1fr));gap:20px;margin-bottom:28px;">

  <?php if (has_role(['super_admin', 'travel_manage'])): ?>
    <!-- Card: Customer Inquiries -->
    <div class="admin-card" style="padding:22px;margin-bottom:0;display:flex;flex-direction:column;justify-content:space-between;">
      <div>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
          <span style="font-size:0.75rem;text-transform:uppercase;color:#64748B;font-weight:700;letter-spacing:0.06em;">Customer Inquiries</span>
          <div style="width:40px;height:40px;border-radius:10px;background:#EFF6FF;color:#2563EB;display:flex;align-items:center;justify-content:center;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
          </div>
        </div>
        <div style="font-size:2rem;font-weight:800;color:#0F172A;line-height:1;margin-bottom:6px;"><?= $totalEnquiries ?></div>
        <div style="font-size:0.82rem;color:#64748B;">
          <?php if ($newEnquiries > 0): ?>
            <span style="color:#DC2626;font-weight:700;">● <?= $newEnquiries ?> unreviewed</span>
          <?php else: ?>
            <span style="color:#059669;font-weight:600;">● All caught up</span>
          <?php endif; ?>
        </div>
      </div>
      <div style="margin-top:16px;padding-top:12px;border-top:1px solid #F1F5F9;">
        <a href="<?= ADMIN_URL ?>/enquiries.php" style="font-size:0.82rem;font-weight:700;color:#2563EB;display:inline-flex;align-items:center;gap:4px;">
          Manage Inquiries &rarr;
        </a>
      </div>
    </div>
  <?php endif; ?>

  <!-- Card: Confirmed Bookings -->
  <div class="admin-card" style="padding:22px;margin-bottom:0;display:flex;flex-direction:column;justify-content:space-between;">
    <div>
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
        <span style="font-size:0.75rem;text-transform:uppercase;color:#64748B;font-weight:700;letter-spacing:0.06em;">Confirmed Bookings</span>
        <div style="width:40px;height:40px;border-radius:10px;background:#F0FDF4;color:#16A34A;display:flex;align-items:center;justify-content:center;">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
        </div>
      </div>
      <div style="font-size:2rem;font-weight:800;color:#0F172A;line-height:1;margin-bottom:6px;"><?= $confirmedBookings ?></div>
      <div style="font-size:0.82rem;color:#64748B;">
        Total registered: <strong style="color:#0F172A;"><?= $totalBookings ?></strong>
      </div>
    </div>
    <div style="margin-top:16px;padding-top:12px;border-top:1px solid #F1F5F9;">
      <a href="<?= ADMIN_URL ?>/bookings.php" style="font-size:0.82rem;font-weight:700;color:#16A34A;display:inline-flex;align-items:center;gap:4px;">
        View Bookings &rarr;
      </a>
    </div>
  </div>

  <?php if (has_role(['super_admin', 'payment_handle'])): ?>
    <!-- Card: Captured Revenue -->
    <div class="admin-card" style="padding:22px;margin-bottom:0;display:flex;flex-direction:column;justify-content:space-between;">
      <div>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
          <span style="font-size:0.75rem;text-transform:uppercase;color:#64748B;font-weight:700;letter-spacing:0.06em;">Captured Revenue</span>
          <div style="width:40px;height:40px;border-radius:10px;background:#ECFDF5;color:#059669;display:flex;align-items:center;justify-content:center;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
          </div>
        </div>
        <div style="font-size:1.85rem;font-weight:800;color:#059669;line-height:1;margin-bottom:6px;"><?= format_price($totalCapturedPayments) ?></div>
        <div style="font-size:0.82rem;color:#64748B;">
          <?= $capturedPaymentsCount ?> verified checkouts
        </div>
      </div>
      <div style="margin-top:16px;padding-top:12px;border-top:1px solid #F1F5F9;">
        <a href="<?= ADMIN_URL ?>/payments.php" style="font-size:0.82rem;font-weight:700;color:#059669;display:inline-flex;align-items:center;gap:4px;">
          Audit Transactions &rarr;
        </a>
      </div>
    </div>
  <?php endif; ?>

  <?php if ($currentRole === 'payment_handle'): ?>
    <!-- Card: Payment Transactions (for Payment Handle) -->
    <div class="admin-card" style="padding:22px;margin-bottom:0;display:flex;flex-direction:column;justify-content:space-between;">
      <div>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
          <span style="font-size:0.75rem;text-transform:uppercase;color:#64748B;font-weight:700;letter-spacing:0.06em;">Gateway Transactions</span>
          <div style="width:40px;height:40px;border-radius:10px;background:#F8FAFC;color:#0F172A;display:flex;align-items:center;justify-content:center;border:1px solid #CBD5E1;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
          </div>
        </div>
        <div style="font-size:2rem;font-weight:800;color:#0F172A;line-height:1;margin-bottom:6px;"><?= $totalPaymentsCount ?></div>
        <div style="font-size:0.82rem;color:#64748B;">
          Reconciled online logs
        </div>
      </div>
      <div style="margin-top:16px;padding-top:12px;border-top:1px solid #F1F5F9;">
        <a href="<?= ADMIN_URL ?>/payments.php" style="font-size:0.82rem;font-weight:700;color:#0F172A;display:inline-flex;align-items:center;gap:4px;">
          View All Transactions &rarr;
        </a>
      </div>
    </div>
  <?php endif; ?>

  <?php if (has_role(['super_admin', 'travel_manage'])): ?>
    <!-- Card: Tour Packages Catalog -->
    <div class="admin-card" style="padding:22px;margin-bottom:0;display:flex;flex-direction:column;justify-content:space-between;">
      <div>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
          <span style="font-size:0.75rem;text-transform:uppercase;color:#64748B;font-weight:700;letter-spacing:0.06em;">Active Packages</span>
          <div style="width:40px;height:40px;border-radius:10px;background:#FFFBEB;color:#D97706;display:flex;align-items:center;justify-content:center;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"></polygon></svg>
          </div>
        </div>
        <div style="font-size:2rem;font-weight:800;color:#0F172A;line-height:1;margin-bottom:6px;">
          <?= $totalPackages ?> <span style="font-size:0.9rem;font-weight:600;color:#64748B;">Pkgs</span>
        </div>
        <div style="font-size:0.82rem;color:#64748B;">
          Across <strong style="color:#0F172A;"><?= $totalDestinations ?></strong> published destinations
        </div>
      </div>
      <div style="margin-top:16px;padding-top:12px;border-top:1px solid #F1F5F9;">
        <a href="<?= ADMIN_URL ?>/packages.php" style="font-size:0.82rem;font-weight:700;color:#D97706;display:inline-flex;align-items:center;gap:4px;">
          Manage Packages &rarr;
        </a>
      </div>
    </div>
  <?php endif; ?>

  <?php if ($currentRole === 'travel_manage'): ?>
    <!-- Card: Published Destinations (for Travel Manage) -->
    <div class="admin-card" style="padding:22px;margin-bottom:0;display:flex;flex-direction:column;justify-content:space-between;">
      <div>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
          <span style="font-size:0.75rem;text-transform:uppercase;color:#64748B;font-weight:700;letter-spacing:0.06em;">Destinations</span>
          <div style="width:40px;height:40px;border-radius:10px;background:#EFF6FF;color:#2563EB;display:flex;align-items:center;justify-content:center;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
          </div>
        </div>
        <div style="font-size:2rem;font-weight:800;color:#0F172A;line-height:1;margin-bottom:6px;">
          <?= $totalDestinations ?>
        </div>
        <div style="font-size:0.82rem;color:#64748B;">
          Curated sanctuary regions
        </div>
      </div>
      <div style="margin-top:16px;padding-top:12px;border-top:1px solid #F1F5F9;">
        <a href="<?= ADMIN_URL ?>/destinations.php" style="font-size:0.82rem;font-weight:700;color:#2563EB;display:inline-flex;align-items:center;gap:4px;">
          Manage Horizons &rarr;
        </a>
      </div>
    </div>
  <?php endif; ?>

</div>

<?php if (has_role(['super_admin', 'travel_manage'])): ?>
  <!-- =====================================================================
       2. RECENT INQUIRIES SECTION
       ===================================================================== -->
  <div class="admin-card" style="padding:24px 28px;margin-bottom:28px;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
      <div>
        <h3 style="font-size:1.15rem;color:#0F172A;font-weight:700;margin-bottom:4px;">Recent Customer Inquiries</h3>
        <p style="font-size:0.82rem;color:#64748B;margin:0;">Incoming traveller requests and customized itinerary leads</p>
      </div>
      <a href="<?= ADMIN_URL ?>/enquiries.php" class="admin-btn admin-btn-outline admin-btn-sm" style="font-weight:700;">
        <span>View All (<?= $totalEnquiries ?>) &rarr;</span>
      </a>
    </div>

    <?php if (empty($recentEnquiries)): ?>
      <div style="text-align:center;padding:40px 20px;background:#F8FAFC;border-radius:10px;border:1px dashed #CBD5E1;">
        <div style="font-size:2rem;margin-bottom:8px;">📬</div>
        <div style="font-size:0.95rem;font-weight:600;color:#0F172A;">No customer inquiries received yet</div>
        <div style="font-size:0.82rem;color:#64748B;margin-top:4px;">When visitors submit trip forms on the website, they will appear here instantly.</div>
      </div>
    <?php else: ?>
      <div style="overflow-x:auto;">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Reference</th>
              <th>Traveller Name</th>
              <th>Destination</th>
              <th>Contact Phone</th>
              <th>Status</th>
              <th>Received On</th>
              <th style="text-align:right;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recentEnquiries as $enq): ?>
              <?php
                $stClass = match($enq['status']) {
                  'new' => 'badge-info',
                  'in_progress' => 'badge-warning',
                  'converted' => 'badge-success',
                  'closed' => 'badge-neutral',
                  default => 'badge-neutral'
                };
                $cleanPhone = preg_replace('/[^0-9]/', '', $enq['phone']);
                $waLeadMsg = "Hello " . $enq['full_name'] . ", this is Midhila Voyages following up regarding your enquiry (#" . $enq['reference_no'] . ") for " . ($enq['destination_name'] ?: 'your trip') . ". How may we assist you today?";
                $waLeadUrl = build_whatsapp_url($cleanPhone, $waLeadMsg);
              ?>
              <tr>
                <td>
                  <a href="<?= ADMIN_URL ?>/enquiry-view.php?id=<?= (int)$enq['id'] ?>" style="font-family:monospace;font-size:0.86rem;font-weight:700;color:#0F172A;text-decoration:none;">
                    <?= e($enq['reference_no']) ?>
                  </a>
                </td>
                <td>
                  <strong style="color:#0F172A;font-size:0.92rem;"><?= e($enq['full_name']) ?></strong>
                </td>
                <td>
                  <span style="font-weight:600;color:#0F172A;"><?= e($enq['destination_name'] ?: 'Custom Route') ?></span>
                  <?php if (!empty($enq['accommodation_name'])): ?>
                    <div style="font-size:0.75rem;color:#2563EB;font-weight:500;">🏨 <?= e($enq['accommodation_name']) ?></div>
                  <?php endif; ?>
                </td>
                <td>
                  <a href="tel:<?= e($enq['phone']) ?>" style="font-size:0.86rem;font-weight:600;color:#0F172A;text-decoration:none;display:inline-flex;align-items:center;gap:6px;">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
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
                    <a href="<?= e($waLeadUrl) ?>" target="_blank" rel="noopener noreferrer" class="admin-btn admin-btn-sm" style="background:#25D366;color:#FFFFFF;border:none;font-weight:600;padding:6px 12px;border-radius:6px;display:inline-flex;align-items:center;gap:5px;" title="Chat directly with customer on WhatsApp">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91C2.13 13.66 2.59 15.36 3.45 16.86L2.05 22L7.3 20.62C8.75 21.41 10.38 21.83 12.04 21.83C17.5 21.83 21.95 17.38 21.95 11.92C21.95 9.27 20.92 6.78 19.05 4.91C17.18 3.03 14.69 2 12.04 2M12.05 3.67C14.25 3.67 16.31 4.53 17.87 6.09C19.42 7.65 20.28 9.72 20.28 11.92C20.28 16.46 16.58 20.15 12.04 20.15C10.56 20.15 9.11 19.76 7.85 19L7.55 18.83L4.43 19.65L5.26 16.61L5.06 16.29C4.24 15 3.8 13.47 3.8 11.91C3.81 7.37 7.5 3.67 12.05 3.67M8.53 7.33C8.37 7.33 8.1 7.39 7.87 7.64C7.65 7.89 7 8.5 7 9.71C7 10.93 7.89 12.1 8 12.27C8.14 12.44 9.76 14.94 12.25 16C12.84 16.27 13.3 16.42 13.66 16.53C14.25 16.72 14.79 16.69 15.22 16.63C15.7 16.56 16.68 16.03 16.89 15.45C17.1 14.87 17.1 14.38 17.04 14.27C16.97 14.17 16.81 14.11 16.56 13.99C16.32 13.86 15.12 13.27 14.9 13.19C14.67 13.1 14.5 13.06 14.34 13.31C14.18 13.56 13.71 14.11 13.56 14.27C13.42 14.44 13.27 14.46 13.03 14.34C12.78 14.21 11.74 13.87 10.51 12.77C9.56 11.91 8.91 10.86 8.79 10.65C8.66 10.45 8.77 10.33 8.89 10.21C9 10.1 9.15 9.91 9.28 9.76C9.41 9.62 9.45 9.5 9.53 9.35C9.62 9.2 9.58 9.06 9.52 8.94C9.45 8.82 9 7.72 8.81 7.25C8.63 6.79 8.44 6.86 8.3 6.85C8.17 6.85 8.01 6.85 7.85 6.85"/>
                      </svg>
                      <span>WhatsApp</span>
                    </a>
                    <a href="<?= ADMIN_URL ?>/enquiry-view.php?id=<?= (int)$enq['id'] ?>" class="admin-btn admin-btn-outline admin-btn-sm" style="font-weight:600;">
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
<?php endif; ?>

<?php if ($currentRole === 'payment_handle'): ?>
  <!-- =====================================================================
       2B. RECENT PAYMENT TRANSACTIONS (FOR PAYMENT HANDLE)
       ===================================================================== -->
  <div class="admin-card" style="padding:24px 28px;margin-bottom:28px;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
      <div>
        <h3 style="font-size:1.15rem;color:#0F172A;font-weight:700;margin-bottom:4px;">Recent Gateway Payment Transactions</h3>
        <p style="font-size:0.82rem;color:#64748B;margin:0;">Audit trail of online checkouts and authorization logs</p>
      </div>
      <a href="<?= ADMIN_URL ?>/payments.php" class="admin-btn admin-btn-outline admin-btn-sm" style="font-weight:700;">
        <span>Open Payments Audit &rarr;</span>
      </a>
    </div>

    <?php if (empty($recentPayments)): ?>
      <div style="text-align:center;padding:40px 20px;background:#F8FAFC;border-radius:10px;border:1px dashed #CBD5E1;">
        <div style="font-size:2rem;margin-bottom:8px;">💳</div>
        <div style="font-size:0.95rem;font-weight:600;color:#0F172A;">No payments logged yet</div>
      </div>
    <?php else: ?>
      <div style="overflow-x:auto;">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Order Ref</th>
              <th>Customer</th>
              <th>Gateway</th>
              <th>Amount</th>
              <th>Status</th>
              <th>Captured At</th>
              <th style="text-align:right;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recentPayments as $p): ?>
              <tr>
                <td>
                  <span style="font-family:monospace;font-size:0.86rem;font-weight:700;color:#0F172A;">
                    <?= e($p['payment_order_id'] ?: ('#PAY-' . $p['id'])) ?>
                  </span>
                </td>
                <td>
                  <strong style="color:#0F172A;"><?= e($p['customer_name'] ?: 'Guest Checkout') ?></strong>
                </td>
                <td><span class="badge badge-neutral"><?= strtoupper(e($p['gateway'])) ?></span></td>
                <td><strong style="color:#059669;"><?= format_price((float)$p['amount']) ?></strong></td>
                <td>
                  <span class="badge <?= ($p['status'] === 'captured') ? 'badge-success' : (($p['status'] === 'pending') ? 'badge-warning' : 'badge-danger') ?>">
                    <?= e($p['status']) ?>
                  </span>
                </td>
                <td style="font-size:0.82rem;color:#64748B;">
                  <?= date('d M, h:i A', strtotime($p['created_at'])) ?>
                </td>
                <td style="text-align:right;">
                  <a href="<?= ADMIN_URL ?>/payments.php" class="admin-btn admin-btn-outline admin-btn-sm" style="font-weight:600;">
                    <span>Audit &rarr;</span>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
<?php endif; ?>

<!-- =====================================================================
     3. RECENT BOOKINGS SECTION (ALL ROLES)
     ===================================================================== -->
<div class="admin-card" style="padding:24px 28px;margin-bottom:28px;">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
    <div>
      <h3 style="font-size:1.15rem;color:#0F172A;font-weight:700;margin-bottom:4px;">Recent Customer Reservations</h3>
      <p style="font-size:0.82rem;color:#64748B;margin:0;">Verified tour bookings and passenger manifests</p>
    </div>
    <a href="<?= ADMIN_URL ?>/bookings.php" class="admin-btn admin-btn-outline admin-btn-sm" style="font-weight:700;">
      <span>View All (<?= $totalBookings ?>) &rarr;</span>
    </a>
  </div>

  <?php if (empty($recentBookings)): ?>
    <div style="text-align:center;padding:40px 20px;background:#F8FAFC;border-radius:10px;border:1px dashed #CBD5E1;">
      <div style="font-size:2rem;margin-bottom:8px;">🎫</div>
      <div style="font-size:0.95rem;font-weight:600;color:#0F172A;">No booking records logged yet</div>
      <div style="font-size:0.82rem;color:#64748B;margin-top:4px;">Online checkout orders and manual reservations will appear here.</div>
    </div>
  <?php else: ?>
    <div style="overflow-x:auto;">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Booking Reference</th>
            <th>Guest Name</th>
            <th>Travel Date</th>
            <th>Total Amount</th>
            <th>Booking Status</th>
            <th>Payment Status</th>
            <th style="text-align:right;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recentBookings as $bk): ?>
            <tr>
              <td>
                <span style="font-family:monospace;font-size:0.86rem;font-weight:700;color:#0F172A;">
                  <?= e($bk['booking_reference']) ?>
                </span>
              </td>
              <td>
                <strong style="color:#0F172A;"><?= e($bk['customer_name']) ?></strong>
                <div style="font-size:0.78rem;color:#64748B;"><?= e($bk['customer_phone']) ?></div>
              </td>
              <td>
                <span style="font-weight:500;color:#334155;"><?= date('d M Y', strtotime($bk['travel_date'])) ?></span>
              </td>
              <td>
                <strong style="color:#059669;"><?= format_price((float)$bk['total_amount']) ?></strong>
              </td>
              <td>
                <span class="badge <?= ($bk['booking_status'] === 'confirmed') ? 'badge-success' : 'badge-warning' ?>">
                  <?= e($bk['booking_status']) ?>
                </span>
              </td>
              <td>
                <span class="badge <?= ($bk['payment_status'] === 'paid') ? 'badge-success' : (($bk['payment_status'] === 'partial') ? 'badge-warning' : 'badge-danger') ?>">
                  <?= e($bk['payment_status']) ?>
                </span>
              </td>
              <td style="text-align:right;">
                <a href="<?= ADMIN_URL ?>/bookings.php?search=<?= urlencode($bk['booking_reference']) ?>" class="admin-btn admin-btn-outline admin-btn-sm" style="font-weight:600;">
                  <span>Open in Bookings &rarr;</span>
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
