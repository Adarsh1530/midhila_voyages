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

<!-- Metric Cards Grid -->
<div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:20px;margin-bottom:32px;">
  
  <div class="admin-card" style="border-left:4px solid var(--admin-gold);padding:20px;">
    <span style="font-size:0.75rem;text-transform:uppercase;color:var(--admin-muted);font-weight:700;letter-spacing:0.06em;">New Enquiries</span>
    <div style="font-size:1.85rem;font-weight:700;color:var(--admin-navy);margin-top:4px;"><?= $newEnquiries ?></div>
    <div style="font-size:0.8rem;color:var(--admin-muted);margin-top:4px;">Total received: <?= $totalEnquiries ?></div>
  </div>

  <div class="admin-card" style="border-left:4px solid var(--admin-teal);padding:20px;">
    <span style="font-size:0.75rem;text-transform:uppercase;color:var(--admin-muted);font-weight:700;letter-spacing:0.06em;">Follow-ups Pending</span>
    <div style="font-size:1.85rem;font-weight:700;color:var(--admin-teal);margin-top:4px;"><?= $inProgressEnquiries ?></div>
    <div style="font-size:0.8rem;color:var(--admin-muted);margin-top:4px;">Active proposals</div>
  </div>

  <div class="admin-card" style="border-left:4px solid #3B82F6;padding:20px;">
    <span style="font-size:0.75rem;text-transform:uppercase;color:var(--admin-muted);font-weight:700;letter-spacing:0.06em;">Confirmed Bookings</span>
    <div style="font-size:1.85rem;font-weight:700;color:var(--admin-navy);margin-top:4px;"><?= $confirmedBookings ?></div>
    <div style="font-size:0.8rem;color:var(--admin-muted);margin-top:4px;">Total bookings: <?= $totalBookings ?></div>
  </div>

  <div class="admin-card" style="border-left:4px solid #10B981;padding:20px;">
    <span style="font-size:0.75rem;text-transform:uppercase;color:var(--admin-muted);font-weight:700;letter-spacing:0.06em;">Captured Revenue</span>
    <div style="font-size:1.85rem;font-weight:700;color:#047857;margin-top:4px;"><?= format_price($totalCapturedPayments) ?></div>
    <div style="font-size:0.8rem;color:var(--admin-muted);margin-top:4px;">Verified transactions</div>
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
