<?php
/**
 * MIDHILA VOYAGES — Admin Dashboard Overview
 */

$adminTitle = 'Dashboard Overview | MIDHILA VOYAGES';
$adminHeading = 'Dashboard Overview';

require_once __DIR__ . '/includes/admin-header.php';

$db = getDB();

// 1. Live Aggregate Statistics from MySQL
$totalEnquiries = (int)$db->query("SELECT COUNT(*) FROM `enquiries`")->fetchColumn();
$newEnquiries = (int)$db->query("SELECT COUNT(*) FROM `enquiries` WHERE `status` = 'new'")->fetchColumn();
$inProgressEnquiries = (int)$db->query("SELECT COUNT(*) FROM `enquiries` WHERE `status` = 'in_progress'")->fetchColumn();

$totalBookings = (int)$db->query("SELECT COUNT(*) FROM `bookings`")->fetchColumn();
$confirmedBookings = (int)$db->query("SELECT COUNT(*) FROM `bookings` WHERE `booking_status` = 'confirmed'")->fetchColumn();

$totalCapturedPayments = (float)$db->query("SELECT COALESCE(SUM(amount), 0) FROM `payments` WHERE `status` = 'captured'")->fetchColumn();

$totalPackages = (int)$db->query("SELECT COUNT(*) FROM `packages` WHERE `is_published` = 1")->fetchColumn();
$totalDestinations = (int)$db->query("SELECT COUNT(*) FROM `destinations` WHERE `is_published` = 1")->fetchColumn();

// 2. Fetch 8 Most Recent Enquiries
$recentEnquiries = $db->query("
    SELECT `id`, `reference_no`, `full_name`, `phone`, `whatsapp`, `destination_name`, `travel_category`,
           `travel_requirements`, `status`, `created_at`
    FROM `enquiries`
    ORDER BY `id` DESC LIMIT 8
")->fetchAll();

// 3. Fetch Recent Bookings
$recentBookings = $db->query("
    SELECT `id`, `booking_reference`, `customer_name`, `customer_phone`, `travel_date`,
           `total_amount`, `booking_status`, `payment_status`, `created_at`
    FROM `bookings`
    ORDER BY `id` DESC LIMIT 5
")->fetchAll();
?>

<!-- Metric Cards Grid with Editorial White Styling -->
<div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(210px, 1fr));gap:20px;margin-bottom:32px;">
  
  <!-- Metric 1: New Enquiries (Coral Accent) -->
  <div class="admin-card" style="border-left:4px solid #EF4444;padding:22px;margin-bottom:0;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;">
      <span style="font-size:0.75rem;text-transform:uppercase;color:#DC2626;font-weight:700;letter-spacing:0.06em;">New Inquiries</span>
      <span style="font-size:1.1rem;">📥</span>
    </div>
    <div style="font-size:2rem;font-weight:700;color:#0F172A;margin-top:6px;"><?= $newEnquiries ?></div>
    <div style="font-size:0.8rem;color:#64748B;margin-top:4px;">Total received: <strong style="color:#0F172A;"><?= $totalEnquiries ?></strong></div>
  </div>

  <!-- Metric 2: Follow-ups Pending (Amber Accent) -->
  <div class="admin-card" style="border-left:4px solid #F59E0B;padding:22px;margin-bottom:0;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;">
      <span style="font-size:0.75rem;text-transform:uppercase;color:#D97706;font-weight:700;letter-spacing:0.06em;">Proposals Active</span>
      <span style="font-size:1.1rem;">⚡</span>
    </div>
    <div style="font-size:2rem;font-weight:700;color:#0F172A;margin-top:6px;"><?= $inProgressEnquiries ?></div>
    <div style="font-size:0.8rem;color:#64748B;margin-top:4px;">Follow-ups pending review</div>
  </div>

  <!-- Metric 3: Confirmed Bookings (Sky Accent) -->
  <div class="admin-card" style="border-left:4px solid #0284C7;padding:22px;margin-bottom:0;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;">
      <span style="font-size:0.75rem;text-transform:uppercase;color:#0284C7;font-weight:700;letter-spacing:0.06em;">Confirmed Bookings</span>
      <span style="font-size:1.1rem;">✈️</span>
    </div>
    <div style="font-size:2rem;font-weight:700;color:#0F172A;margin-top:6px;"><?= $confirmedBookings ?></div>
    <div style="font-size:0.8rem;color:#64748B;margin-top:4px;">Total bookings: <strong style="color:#0F172A;"><?= $totalBookings ?></strong></div>
  </div>

  <!-- Metric 4: Captured Revenue (Emerald Accent) -->
  <div class="admin-card" style="border-left:4px solid #059669;padding:22px;margin-bottom:0;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;">
      <span style="font-size:0.75rem;text-transform:uppercase;color:#059669;font-weight:700;letter-spacing:0.06em;">Captured Revenue</span>
      <span style="font-size:1.1rem;">💳</span>
    </div>
    <div style="font-size:1.85rem;font-weight:700;color:#059669;margin-top:6px;"><?= format_price($totalCapturedPayments) ?></div>
    <div style="font-size:0.8rem;color:#64748B;margin-top:4px;">Verified Razorpay settlements</div>
  </div>

  <!-- Metric 5: Active Catalog (Heritage Gold Accent) -->
  <div class="admin-card" style="border-left:4px solid #C5A059;padding:22px;margin-bottom:0;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;">
      <span style="font-size:0.75rem;text-transform:uppercase;color:#A07D38;font-weight:700;letter-spacing:0.06em;">Active Catalog</span>
      <span style="font-size:1.1rem;">🧭</span>
    </div>
    <div style="font-size:1.85rem;font-weight:700;color:#0F172A;margin-top:6px;"><?= $totalPackages ?> <span style="font-size:0.9rem;font-weight:500;color:#64748B;">Pkgs</span></div>
    <div style="font-size:0.8rem;color:#64748B;margin-top:4px;"><?= $totalDestinations ?> published destinations</div>
  </div>

</div>

<!-- Visual Operations & Conversion Overview Card -->
<div class="admin-card" style="padding:24px 28px;margin-bottom:32px;">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:12px;">
    <div>
      <h3 style="font-size:1.15rem;color:#0F172A;margin-bottom:4px;font-weight:700;">Live Booking &amp; Pipeline Trajectory</h3>
      <p style="font-size:0.82rem;color:#64748B;margin:0;">Real-time conversion ratio across incoming traveller enquiries and completed bookings</p>
    </div>
    <div style="display:flex;gap:10px;">
      <span style="font-size:0.75rem;padding:4px 12px;border-radius:var(--radius-pill);background:#ECFDF5;color:#065F46;font-weight:700;border:1px solid #A7F3D0;">
        ● System Health: 100% Operational
      </span>
    </div>
  </div>

  <!-- Progress Ratio Bar -->
  <?php 
    $convRate = ($totalEnquiries > 0) ? round(($confirmedBookings / $totalEnquiries) * 100, 1) : 0;
  ?>
  <div style="display:flex;align-items:center;gap:16px;margin-bottom:12px;">
    <div style="flex-grow:1;height:10px;background:#F1F5F9;border-radius:8px;overflow:hidden;position:relative;">
      <div style="width:<?= min(100, max(8, $convRate)) ?>%;height:100%;background:linear-gradient(90deg, #F59E0B 0%, #0284C7 50%, #059669 100%);border-radius:8px;transition:width 1s ease;"></div>
    </div>
    <span style="font-size:0.9rem;font-weight:700;color:#0F172A;min-width:70px;text-align:right;"><?= $convRate ?>% Conv</span>
  </div>

  <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));gap:16px;font-size:0.82rem;color:#64748B;border-top:1px solid #F1F5F9;padding-top:16px;margin-top:16px;">
    <div><span style="color:#DC2626;">● Unreviewed Leads:</span> <strong style="color:#0F172A;"><?= $newEnquiries ?></strong></div>
    <div><span style="color:#D97706;">● Proposals Quoted:</span> <strong style="color:#0F172A;"><?= $inProgressEnquiries ?></strong></div>
    <div><span style="color:#0284C7;">● Booked Travelers:</span> <strong style="color:#0F172A;"><?= $confirmedBookings ?></strong></div>
    <div><span style="color:#059669;">● Revenue Collected:</span> <strong style="color:#059669;"><?= format_price($totalCapturedPayments) ?></strong></div>
  </div>
</div>

<!-- Quick Navigation Bar -->
<div style="display:flex;gap:12px;margin-bottom:32px;flex-wrap:wrap;">
  <a href="<?= ADMIN_URL ?>/destination-edit.php" class="admin-btn admin-btn-primary">
    <span>+ Add Destination</span>
  </a>
  <a href="<?= ADMIN_URL ?>/package-edit.php" class="admin-btn admin-btn-gold">
    <span>+ Add Package</span>
  </a>
  <a href="<?= ADMIN_URL ?>/enquiries.php" class="admin-btn admin-btn-outline">
    <span>View All Enquiries</span>
  </a>
  <a href="<?= ADMIN_URL ?>/gallery.php" class="admin-btn admin-btn-outline">
    <span>Upload Photos</span>
  </a>
</div>

<!-- Recent Enquiries Section -->
<div class="admin-card">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
    <div>
      <h3 style="font-size:1.15rem;color:var(--admin-navy);">Recent Trip Enquiries</h3>
      <p style="font-size:0.82rem;color:var(--admin-muted);">Incoming leads and custom trip planning submissions</p>
    </div>
    <a href="<?= ADMIN_URL ?>/enquiries.php" style="font-size:0.82rem;font-weight:600;color:var(--admin-gold);">&rarr; View All (<?= $totalEnquiries ?>)</a>
  </div>

  <?php if (empty($recentEnquiries)): ?>
    <p style="text-align:center;padding:30px;color:var(--admin-muted);">No enquiries received yet.</p>
  <?php else: ?>
    <div style="overflow-x:auto;">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Reference</th>
            <th>Traveller</th>
            <th>Destination</th>
            <th>Contact</th>
            <th>Status</th>
            <th>Received</th>
            <th>Actions</th>
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
              $waLeadMsg = "Hello " . $enq['full_name'] . ", this is Midhila Voyages following up regarding your enquiry (#" . $enq['reference_no'] . ") for " . ($enq['destination_name'] ?: 'your trip') . ".";
              $waLeadUrl = build_whatsapp_url($cleanPhone, $waLeadMsg);
            ?>
            <tr>
              <td>
                <a href="<?= ADMIN_URL ?>/enquiry-view.php?id=<?= (int)$enq['id'] ?>" style="font-weight:600;color:var(--admin-navy);">
                  <?= e($enq['reference_no']) ?>
                </a>
              </td>
              <td>
                <strong><?= e($enq['full_name']) ?></strong>
              </td>
              <td><?= e($enq['destination_name'] ?: 'Custom Plan') ?></td>
              <td>
                <div style="font-size:0.85rem;"><?= e($enq['phone']) ?></div>
              </td>
              <td>
                <span class="badge <?= $stClass ?>"><?= str_replace('_', ' ', e($enq['status'])) ?></span>
              </td>
              <td style="font-size:0.82rem;color:var(--admin-muted);">
                <?= date('d M, h:i A', strtotime($enq['created_at'])) ?>
              </td>
              <td>
                <div style="display:flex;gap:6px;">
                  <a href="<?= ADMIN_URL ?>/enquiry-view.php?id=<?= (int)$enq['id'] ?>" class="admin-btn admin-btn-outline admin-btn-sm">View</a>
                  <a href="<?= e($waLeadUrl) ?>" target="_blank" rel="noopener noreferrer" class="admin-btn admin-btn-sm" style="background:#25D366;color:#FFF;" title="Chat on WhatsApp">WA</a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<!-- Recent Bookings Section -->
<div class="admin-card">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
    <div>
      <h3 style="font-size:1.15rem;color:var(--admin-navy);">Recent Bookings</h3>
      <p style="font-size:0.82rem;color:var(--admin-muted);">Confirmed reservations and deposits</p>
    </div>
    <a href="<?= ADMIN_URL ?>/bookings.php" style="font-size:0.82rem;font-weight:600;color:var(--admin-gold);">&rarr; View All</a>
  </div>

  <?php if (empty($recentBookings)): ?>
    <p style="text-align:center;padding:30px;color:var(--admin-muted);">No booking transactions logged yet.</p>
  <?php else: ?>
    <div style="overflow-x:auto;">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Booking Ref</th>
            <th>Guest Name</th>
            <th>Travel Date</th>
            <th>Total Cost</th>
            <th>Booking Status</th>
            <th>Payment Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recentBookings as $bk): ?>
            <tr>
              <td><strong><?= e($bk['booking_reference']) ?></strong></td>
              <td><?= e($bk['customer_name']) ?></td>
              <td><?= date('d M Y', strtotime($bk['travel_date'])) ?></td>
              <td><?= format_price((float)$bk['total_amount']) ?></td>
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
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
