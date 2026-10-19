<?php
/**
 * MIDHILA VOYAGES — Bookings Management & Search
 */

require_once __DIR__ . '/includes/admin-init.php';

// Handle Status Updates (via Modal or Direct POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_booking_status'])) {
    if (verify_csrf_token()) {
        $bkId = (int)$_POST['booking_id'];
        $bkgStatus = $_POST['booking_status'] ?? 'pending';
        $payStatus = $_POST['payment_status'] ?? 'unpaid';

        $upd = $db->prepare("UPDATE `bookings` SET `booking_status` = ?, `payment_status` = ?, `updated_at` = NOW() WHERE `id` = ?");
        $upd->execute([$bkgStatus, $payStatus, $bkId]);
        set_flash_message('success', 'Booking status updated successfully.');
        
        $redirectUrl = ADMIN_URL . '/bookings.php';
        if (!empty($_GET)) {
            $redirectUrl .= '?' . http_build_query($_GET);
        }
        header('Location: ' . $redirectUrl);
        exit;
    }
}

// Search and Filter Parameters
$search = trim($_GET['search'] ?? '');
$bkgStatus = trim($_GET['bkg_status'] ?? '');
$payStatus = trim($_GET['pay_status'] ?? '');

// Base query & filtering
$where = [];
$params = [];

if (!empty($search)) {
    $where[] = "(b.booking_reference LIKE ? OR b.customer_name LIKE ? OR b.customer_phone LIKE ? OR b.customer_email LIKE ? OR p.title LIKE ?)";
    $term = "%{$search}%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

if (!empty($bkgStatus)) {
    $where[] = "b.booking_status = ?";
    $params[] = $bkgStatus;
}

if (!empty($payStatus)) {
    $where[] = "b.payment_status = ?";
    $params[] = $payStatus;
}

$whereClause = !empty($where) ? ('WHERE ' . implode(' AND ', $where)) : '';

$stmt = $db->prepare("
    SELECT b.*, p.title AS package_title, p.duration_days, p.duration_nights, d.name AS destination_name
    FROM `bookings` b
    LEFT JOIN `packages` p ON b.package_id = p.id
    LEFT JOIN `destinations` d ON p.destination_id = d.id
    {$whereClause}
    ORDER BY b.id DESC
");
$stmt->execute($params);
$bookings = $stmt->fetchAll();

// Global KPIs (computed across all bookings)
$kpiTotal = (int)$db->query("SELECT COUNT(*) FROM `bookings`")->fetchColumn();
$kpiConfirmed = (int)$db->query("SELECT COUNT(*) FROM `bookings` WHERE `booking_status` = 'confirmed'")->fetchColumn();
$kpiPending = (int)$db->query("SELECT COUNT(*) FROM `bookings` WHERE `booking_status` = 'pending'")->fetchColumn();
$kpiRevenue = (float)$db->query("SELECT COALESCE(SUM(`total_amount`), 0) FROM `bookings` WHERE `payment_status` IN ('paid', 'partial')")->fetchColumn();

$hasActiveFilters = (!empty($search) || !empty($bkgStatus) || !empty($payStatus));

$adminTitle = 'Manage Bookings | MIDHILA VOYAGES';
$adminHeading = 'Confirmed Bookings & Reservations';
require_once __DIR__ . '/includes/admin-header.php';
?>

<!-- Section Header -->
<div class="admin-page-header">
  <div>
    <div style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.12em;color:var(--admin-gold);font-weight:700;margin-bottom:4px;">
      Customer Journeys &bull; Reservations
    </div>
    <h1 class="admin-page-title">Confirmed Bookings &amp; Reservations</h1>
    <p class="admin-page-desc">
      Manage passenger reservations, payment status reconciliations, and digital A4 travel vouchers.
    </p>
  </div>
  <div style="display:flex;gap:10px;align-items:center;">
    <span style="font-size:0.82rem;background:rgba(255,255,255,0.08);border:1px solid rgba(196,181,253,0.25);padding:6px 16px;border-radius:20px;color:var(--admin-muted);font-weight:600;backdrop-filter:blur(10px);">
      Total: <strong style="color:var(--admin-navy);"><?= number_format($kpiTotal) ?></strong>
    </span>
  </div>
</div>

<!-- KPI Stat Summary Cards -->
<div class="admin-stats-grid">
  <div class="admin-stat-card">
    <div>
      <div class="admin-stat-label">Total Bookings</div>
      <div class="admin-stat-value"><?= number_format($kpiTotal) ?></div>
      <div class="admin-stat-sub">Lifetime reservations</div>
    </div>
    <div class="admin-stat-icon" style="background:rgba(139,92,246,0.15);border:1px solid rgba(196,181,253,0.3);color:var(--admin-lavender);">
      🎫
    </div>
  </div>

  <div class="admin-stat-card">
    <div>
      <div class="admin-stat-label">Confirmed</div>
      <div class="admin-stat-value" style="color:#86EFAC;"><?= number_format($kpiConfirmed) ?></div>
      <div class="admin-stat-sub">Ready to travel</div>
    </div>
    <div class="admin-stat-icon" style="background:rgba(34,197,94,0.15);border:1px solid rgba(34,197,94,0.3);color:#86EFAC;">
      ✅
    </div>
  </div>

  <div class="admin-stat-card">
    <div>
      <div class="admin-stat-label">Pending Review</div>
      <div class="admin-stat-value" style="color:#FDE68A;"><?= number_format($kpiPending) ?></div>
      <div class="admin-stat-sub">Awaiting confirmation</div>
    </div>
    <div class="admin-stat-icon" style="background:rgba(245,158,11,0.15);border:1px solid rgba(245,158,11,0.3);color:#FDE68A;">
      ⏳
    </div>
  </div>

  <div class="admin-stat-card">
    <div>
      <div class="admin-stat-label">Total Volume Value</div>
      <div class="admin-stat-value" style="color:var(--admin-navy);"><?= format_price($kpiRevenue) ?></div>
      <div class="admin-stat-sub">Paid &amp; deposit revenue</div>
    </div>
    <div class="admin-stat-icon" style="background:rgba(14,165,233,0.15);border:1px solid rgba(14,165,233,0.3);color:#7DD3FC;">
      💰
    </div>
  </div>
</div>

<!-- Search & Filtering Bar -->
<div class="admin-filter-bar">
  <form method="GET" action="<?= ADMIN_URL ?>/bookings.php" class="admin-filter-form no-confirm">
    <div class="admin-search-box">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="11" cy="11" r="8"></circle>
        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
      </svg>
      <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search by booking ref, guest name, phone, tour...">
    </div>

    <select name="bkg_status" class="admin-filter-select">
      <option value="">All Booking Statuses</option>
      <option value="confirmed" <?= ($bkgStatus === 'confirmed') ? 'selected' : '' ?>>Confirmed</option>
      <option value="pending" <?= ($bkgStatus === 'pending') ? 'selected' : '' ?>>Pending</option>
      <option value="completed" <?= ($bkgStatus === 'completed') ? 'selected' : '' ?>>Completed</option>
      <option value="cancelled" <?= ($bkgStatus === 'cancelled') ? 'selected' : '' ?>>Cancelled</option>
    </select>

    <select name="pay_status" class="admin-filter-select">
      <option value="">All Payment Statuses</option>
      <option value="paid" <?= ($payStatus === 'paid') ? 'selected' : '' ?>>Paid (Full)</option>
      <option value="partial" <?= ($payStatus === 'partial') ? 'selected' : '' ?>>Partial (Deposit)</option>
      <option value="unpaid" <?= ($payStatus === 'unpaid') ? 'selected' : '' ?>>Unpaid</option>
      <option value="refunded" <?= ($payStatus === 'refunded') ? 'selected' : '' ?>>Refunded</option>
    </select>

    <button type="submit" class="admin-btn admin-btn-primary" style="padding:9px 18px;">
      <span>Search</span>
    </button>

    <?php if ($hasActiveFilters): ?>
      <a href="<?= ADMIN_URL ?>/bookings.php" class="admin-btn admin-btn-outline" style="padding:9px 14px;color:#EF4444;border-color:#FCA5A5;">
        <span>&times; Clear Filters</span>
      </a>
    <?php endif; ?>
  </form>

  <div style="font-size:0.85rem;color:var(--admin-muted);font-weight:600;white-space:nowrap;">
    Showing <?= count($bookings) ?> booking<?= count($bookings) === 1 ? '' : 's' ?>
    <?= $hasActiveFilters ? '<span style="color:var(--admin-gold);">(Filtered)</span>' : '' ?>
  </div>
</div>

<!-- Bookings Data Table -->
<div class="admin-table-container">
  <div style="overflow-x:auto;">
    <table class="admin-table" style="width:100%;table-layout:fixed;">
      <colgroup>
        <col style="width:18%;">
        <col style="width:20%;">
        <col style="width:22%;">
        <col style="width:14%;">
        <col style="width:14%;">
        <col style="width:12%;">
      </colgroup>
      <thead>
        <tr>
          <th>Booking Ref</th>
          <th>Guest Details</th>
          <th>Tour Package</th>
          <th>Travel Date &amp; Guests</th>
          <th>Total &amp; Status</th>
          <th style="text-align:right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($bookings)): ?>
          <tr>
            <td colspan="6" style="text-align:center;padding:50px 20px;">
              <div style="font-size:2.5rem;margin-bottom:12px;">🔍</div>
              <h4 style="font-size:1.1rem;color:var(--admin-navy);margin-bottom:6px;">No Bookings Found</h4>
              <p style="font-size:0.88rem;color:var(--admin-muted);max-width:400px;margin:0 auto 16px;">
                <?= $hasActiveFilters ? 'No bookings matched your search filter criteria. Try adjusting keywords or resetting filters.' : 'There are currently no customer booking reservations recorded.' ?>
              </p>
              <?php if ($hasActiveFilters): ?>
                <a href="<?= ADMIN_URL ?>/bookings.php" class="admin-btn admin-btn-outline">Reset Search Filters</a>
              <?php endif; ?>
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($bookings as $bk): ?>
            <tr>
              <!-- 1. Booking Reference -->
              <td>
                <a href="<?= BASE_URL ?>/payment-success.php?ref=<?= e($bk['booking_reference']) ?>" target="_blank" style="font-weight:700;color:var(--admin-navy);display:inline-flex;align-items:center;gap:4px;" title="Open A4 Digital Voucher">
                  <span><?= e($bk['booking_reference']) ?></span>
                  <span style="font-size:0.8rem;color:var(--admin-gold);">&nearr;</span>
                </a>
                <div style="font-size:0.75rem;color:var(--admin-muted);margin-top:3px;">
                  Created: <?= date('d M Y, h:i A', strtotime($bk['created_at'])) ?>
                </div>
              </td>

              <!-- 2. Guest Details -->
              <td>
                <strong style="color:var(--admin-navy);display:block;"><?= e($bk['customer_name']) ?></strong>
                <div style="font-size:0.8rem;color:var(--admin-muted);margin-top:3px;line-height:1.4;">
                  <a href="tel:<?= e($bk['customer_phone']) ?>" style="color:inherit;">📞 <?= e($bk['customer_phone']) ?></a>
                  <?php if (!empty($bk['customer_email'])): ?>
                    <br><a href="mailto:<?= e($bk['customer_email']) ?>" style="color:var(--admin-teal);">✉️ <?= e($bk['customer_email']) ?></a>
                  <?php endif; ?>
                </div>
              </td>

              <!-- 3. Tour Package -->
              <td>
                <div style="font-weight:600;color:var(--admin-navy);line-height:1.35;">
                  <?= e($bk['package_title'] ?: 'Custom Tour Package') ?>
                </div>
                <div style="margin-top:4px;display:flex;gap:6px;flex-wrap:wrap;">
                  <?php if (!empty($bk['duration_days'])): ?>
                    <span style="font-size:0.72rem;background:rgba(255,255,255,0.12);padding:2px 8px;border-radius:4px;color:#FFFFFF;font-weight:600;border:1px solid rgba(255,255,255,0.22);">
                      <?= (int)$bk['duration_days'] ?>D / <?= (int)$bk['duration_nights'] ?>N
                    </span>
                  <?php endif; ?>
                  <?php if (!empty($bk['destination_name'])): ?>
                    <span style="font-size:0.72rem;background:rgba(197,160,89,0.2);color:#DFBA73;padding:2px 8px;border-radius:4px;font-weight:600;border:1px solid rgba(197,160,89,0.35);">
                      📍 <?= e($bk['destination_name']) ?>
                    </span>
                  <?php endif; ?>
                </div>
              </td>

              <!-- 4. Travel Date & Guests -->
              <td>
                <div style="font-weight:600;color:var(--admin-navy);font-size:0.92rem;">
                  📅 <?= date('d M Y', strtotime($bk['travel_date'])) ?>
                </div>
                <div style="font-size:0.8rem;color:var(--admin-muted);margin-top:3px;">
                  👥 <?= (int)$bk['travellers_count'] ?> Guest<?= (int)$bk['travellers_count'] === 1 ? '' : 's' ?>
                </div>
              </td>

              <!-- 5. Total & Status -->
              <td>
                <strong style="font-size:1rem;color:var(--admin-navy);display:block;margin-bottom:6px;">
                  <?= format_price((float)$bk['total_amount'], $bk['currency']) ?>
                </strong>
                <div style="display:flex;flex-direction:column;gap:4px;align-items:flex-start;">
                  <span class="badge <?= ($bk['booking_status'] === 'confirmed') ? 'badge-success' : (($bk['booking_status'] === 'pending') ? 'badge-warning' : (($bk['booking_status'] === 'completed') ? 'badge-info' : 'badge-neutral')) ?>">
                    <?= e($bk['booking_status']) ?>
                  </span>
                  <span class="badge <?= ($bk['payment_status'] === 'paid') ? 'badge-success' : (($bk['payment_status'] === 'partial') ? 'badge-warning' : 'badge-danger') ?>">
                    <?= e($bk['payment_status']) ?>
                  </span>
                </div>
              </td>

              <!-- 6. Management Actions -->
              <td style="text-align:right;">
                <div style="display:inline-flex;flex-direction:column;gap:6px;align-items:flex-end;">
                  <button type="button" 
                          class="admin-btn admin-btn-outline admin-btn-sm" 
                          onclick="openStatusUpdateModal(<?= (int)$bk['id'] ?>, '<?= e(addslashes($bk['booking_reference'])) ?>', '<?= e(addslashes($bk['customer_name'])) ?>', '<?= e($bk['booking_status']) ?>', '<?= e($bk['payment_status']) ?>')"
                          style="white-space:nowrap;padding:5px 10px;font-size:0.8rem;"
                          title="Update Booking &amp; Payment Status">
                    ✏️ Update Status
                  </button>

                  <a href="<?= BASE_URL ?>/payment-success.php?ref=<?= e($bk['booking_reference']) ?>" 
                     target="_blank" 
                     class="admin-btn admin-btn-primary admin-btn-sm" 
                     style="white-space:nowrap;padding:5px 10px;font-size:0.8rem;background:var(--admin-navy);"
                     title="View &amp; Print A4 Passenger Voucher">
                    📄 View Voucher
                  </a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Interactive Status Update Modal (Replaces clunky inline table dropdowns) -->
<div id="bookingStatusModal" class="admin-modal-overlay" style="display:none;" aria-hidden="true">
  <div class="admin-modal-card" style="max-width:440px;text-align:left;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;border-bottom:1px solid var(--admin-border);padding-bottom:12px;">
      <div>
        <h3 style="font-size:1.15rem;color:var(--admin-navy);margin:0;">Update Booking Status</h3>
        <span id="modalBkgRefSubtitle" style="font-size:0.8rem;color:var(--admin-muted);font-weight:600;">-</span>
      </div>
      <button type="button" onclick="closeStatusUpdateModal()" style="border:none;background:none;font-size:1.4rem;cursor:pointer;color:var(--admin-muted);">&times;</button>
    </div>

    <form method="POST" action="<?= ADMIN_URL ?>/bookings.php<?= !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '' ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="update_booking_status" value="1">
      <input type="hidden" name="booking_id" id="modalBookingId" value="0">

      <div style="margin-bottom:16px;">
        <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--admin-navy);margin-bottom:6px;">Booking Reservation Status</label>
        <select name="booking_status" id="modalBookingStatus" class="admin-filter-select" style="width:100%;">
          <option value="pending">Pending Review</option>
          <option value="confirmed">Confirmed (Itinerary Guaranteed)</option>
          <option value="completed">Completed Tour</option>
          <option value="cancelled">Cancelled</option>
        </select>
      </div>

      <div style="margin-bottom:24px;">
        <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--admin-navy);margin-bottom:6px;">Payment Collection Status</label>
        <select name="payment_status" id="modalPaymentStatus" class="admin-filter-select" style="width:100%;">
          <option value="unpaid">Unpaid (Awaiting Payment)</option>
          <option value="partial">Partial (Booking Deposit Paid)</option>
          <option value="paid">Paid in Full (100% Captured)</option>
          <option value="refunded">Refunded / Reconciled</option>
        </select>
      </div>

      <div style="display:flex;justify-content:flex-end;gap:10px;">
        <button type="button" onclick="closeStatusUpdateModal()" class="admin-btn admin-btn-outline">Cancel</button>
        <button type="submit" class="admin-btn admin-btn-primary">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<script>
function openStatusUpdateModal(id, ref, guest, bkgStat, payStat) {
  document.getElementById('modalBookingId').value = id;
  document.getElementById('modalBkgRefSubtitle').textContent = ref + ' • ' + guest;
  document.getElementById('modalBookingStatus').value = bkgStat;
  document.getElementById('modalPaymentStatus').value = payStat;

  const modal = document.getElementById('bookingStatusModal');
  modal.style.display = 'flex';
  setTimeout(() => modal.classList.add('is-active'), 10);
}

function closeStatusUpdateModal() {
  const modal = document.getElementById('bookingStatusModal');
  modal.classList.remove('is-active');
  setTimeout(() => modal.style.display = 'none', 200);
}
</script>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
