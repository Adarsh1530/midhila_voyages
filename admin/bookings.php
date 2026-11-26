<?php
/**
 * MIDHILA VOYAGES — Bookings Management
 * View and Manage All Customer Bookings & Personal Details (No Payment Gateway)
 */

require_once __DIR__ . '/includes/admin-init.php';

// Handle Status Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_booking_status'])) {
    if (verify_csrf_token()) {
        $bkId = (int)$_POST['booking_id'];
        $bkgStatus = $_POST['booking_status'] ?? 'pending';

        $now = date('Y-m-d H:i:s');
        $upd = $db->prepare("UPDATE `bookings` SET `booking_status` = ?, `updated_at` = ? WHERE `id` = ?");
        $upd->execute([$bkgStatus, $now, $bkId]);
        set_flash_message('success', 'Booking status updated successfully.');
        
        $redirectUrl = ADMIN_URL . '/bookings.php';
        if (!empty($_GET)) {
            $redirectUrl .= '?' . http_build_query($_GET);
        }
        header('Location: ' . $redirectUrl);
        exit;
    }
}

// Handle Manual Booking Creation (from Admin Desk)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_manual_booking'])) {
    if (verify_csrf_token()) {
        $pkgId       = (int)($_POST['package_id'] ?? 0);
        $custName    = trim($_POST['customer_name'] ?? '');
        $custPhone   = trim($_POST['customer_phone'] ?? '');
        $custEmail   = trim($_POST['customer_email'] ?? '');
        $travelDate  = trim($_POST['travel_date'] ?? '');
        $travellers  = max(1, (int)($_POST['travellers_count'] ?? 1));
        $bkgStatus   = trim($_POST['booking_status'] ?? 'confirmed');
        $requests    = trim($_POST['special_requests'] ?? '');

        if (!empty($custName) && !empty($custPhone) && $pkgId > 0) {
            $year = date('Y');
            $refCount = (int)$db->query("SELECT COUNT(*) FROM `bookings` WHERE `booking_reference` LIKE 'MV-BKG-{$year}-%'")->fetchColumn() + 1;
            $bkgRef = sprintf("MV-BKG-%s-%05d", $year, $refCount);

            $pkgStmt = $db->prepare("SELECT `starting_price` FROM `packages` WHERE `id` = ?");
            $pkgStmt->execute([$pkgId]);
            $price = (float)$pkgStmt->fetchColumn();
            $totalAmount = $price * $travellers;

            $ins = $db->prepare("
                INSERT INTO `bookings` (
                    `booking_reference`, `package_id`, `customer_name`, `customer_email`,
                    `customer_phone`, `travel_date`, `travellers_count`, `total_amount`,
                    `currency`, `booking_status`, `payment_status`, `special_requests`,
                    `created_at`, `updated_at`
                ) VALUES (
                    ?, ?, ?, ?,
                    ?, ?, ?, ?,
                    'INR', ?, 'unpaid', ?,
                    CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
                )
            ");
            $ins->execute([
                $bkgRef, $pkgId, $custName, $custEmail,
                $custPhone, $travelDate ?: date('Y-m-d'), $travellers, $totalAmount,
                $bkgStatus, $requests
            ]);

            set_flash_message('success', "Booking {$bkgRef} created successfully.");
            header('Location: ' . ADMIN_URL . '/bookings.php');
            exit;
        } else {
            set_flash_message('error', 'Please provide guest name, phone, and select a tour package.');
        }
    }
}

// Search & Filtering
$search = trim($_GET['q'] ?? '');
$filterStatus = trim($_GET['status'] ?? 'all');

$whereClauses = [];
$params = [];

if ($search !== '') {
    $whereClauses[] = "(b.booking_reference LIKE ? OR b.customer_name LIKE ? OR b.customer_phone LIKE ? OR b.customer_email LIKE ?)";
    $like = '%' . $search . '%';
    $params = array_merge($params, [$like, $like, $like, $like]);
}

if ($filterStatus !== 'all' && in_array($filterStatus, ['pending', 'confirmed', 'completed', 'cancelled'], true)) {
    $whereClauses[] = "b.booking_status = ?";
    $params[] = $filterStatus;
}

$whereSql = !empty($whereClauses) ? 'WHERE ' . implode(' AND ', $whereClauses) : '';

// Fetch filtered bookings
$query = "
    SELECT b.*, p.title AS package_title, p.duration_days, p.duration_nights, d.name AS destination_name
    FROM `bookings` b
    LEFT JOIN `packages` p ON b.package_id = p.id
    LEFT JOIN `destinations` d ON p.destination_id = d.id
    {$whereSql}
    ORDER BY b.id DESC
";
$stmt = $db->prepare($query);
$stmt->execute($params);
$bookings = $stmt->fetchAll();

// Global KPIs
$kpiTotal = (int)$db->query("SELECT COUNT(*) FROM `bookings`")->fetchColumn();
$kpiConfirmed = (int)$db->query("SELECT COUNT(*) FROM `bookings` WHERE `booking_status` = 'confirmed'")->fetchColumn();
$kpiPending = (int)$db->query("SELECT COUNT(*) FROM `bookings` WHERE `booking_status` = 'pending'")->fetchColumn();
$kpiTravellers = (int)$db->query("SELECT COALESCE(SUM(`travellers_count`), 0) FROM `bookings`")->fetchColumn();

// Packages for manual modal
$packagesList = $db->query("SELECT `id`, `title`, `starting_price` FROM `packages` WHERE `is_published` = 1 ORDER BY `title` ASC")->fetchAll();

$adminTitle = 'Manage Bookings | MIDHILA VOYAGES';
$adminHeading = 'Customer Bookings & Personal Details';
require_once __DIR__ . '/includes/admin-header.php';
?>

<!-- Section Header -->
<div class="admin-page-header">
  <div>
    <div style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.12em;color:#C5A059;font-weight:700;margin-bottom:4px;">
      Customer Journeys &bull; Reservations Registry
    </div>
    <h1 class="admin-page-title">Customer Bookings &amp; Personal Details</h1>
    <p class="admin-page-desc">
      View and manage all customer bookings, stored personal details, travel preferences, and digital vouchers.
    </p>
  </div>
  <div style="display:flex;gap:10px;align-items:center;">
    <button type="button" onclick="openNewBookingModal()" class="admin-btn admin-btn-primary admin-btn-sm" style="display:inline-flex;align-items:center;gap:6px;font-weight:700;">
      <span>+ Create New Booking</span>
    </button>
  </div>
</div>

<!-- KPI Stat Summary Cards -->
<div class="admin-stats-grid" style="grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:18px;margin-bottom:24px;">
  <div class="admin-stat-card">
    <div>
      <div class="admin-stat-label">Total Bookings</div>
      <div class="admin-stat-value"><?= number_format($kpiTotal) ?></div>
      <div class="admin-stat-sub">Stored in database</div>
    </div>
  </div>

  <div class="admin-stat-card">
    <div>
      <div class="admin-stat-label">Confirmed</div>
      <div class="admin-stat-value" style="color:#16A34A;"><?= number_format($kpiConfirmed) ?></div>
      <div class="admin-stat-sub">Ready to travel</div>
    </div>
  </div>

  <div class="admin-stat-card">
    <div>
      <div class="admin-stat-label">Pending Review</div>
      <div class="admin-stat-value" style="color:#D97706;"><?= number_format($kpiPending) ?></div>
      <div class="admin-stat-sub">Awaiting confirmation</div>
    </div>
  </div>

  <div class="admin-stat-card">
    <div>
      <div class="admin-stat-label">Total Travellers</div>
      <div class="admin-stat-value" style="color:#0F172A;"><?= number_format($kpiTravellers) ?></div>
      <div class="admin-stat-sub">Registered guests</div>
    </div>
  </div>
</div>

<!-- Search & Filter Controls -->
<div style="background:#FFFFFF;border:1px solid #E2E8F0;border-radius:10px;padding:16px 20px;margin-bottom:20px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:14px;">
  <form method="GET" action="<?= ADMIN_URL ?>/bookings.php" style="display:flex;gap:12px;flex-wrap:wrap;align-items:center;flex:1;">
    <div style="position:relative;flex:1;min-width:240px;">
      <input type="text" name="q" value="<?= e($search) ?>" placeholder="Enter your search query" 
             style="width:100%;padding:9px 14px 9px 36px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.88rem;outline:none;">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="2" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);">
        <circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line>
      </svg>
    </div>

    <select name="status" onchange="this.form.submit()" style="padding:9px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.88rem;background:#FFF;font-weight:600;">
      <option value="all" <?= ($filterStatus === 'all') ? 'selected' : '' ?>>Select status (All)</option>
      <option value="pending" <?= ($filterStatus === 'pending') ? 'selected' : '' ?>>Pending Review</option>
      <option value="confirmed" <?= ($filterStatus === 'confirmed') ? 'selected' : '' ?>>Confirmed</option>
      <option value="completed" <?= ($filterStatus === 'completed') ? 'selected' : '' ?>>Completed</option>
      <option value="cancelled" <?= ($filterStatus === 'cancelled') ? 'selected' : '' ?>>Cancelled</option>
    </select>

    <button type="submit" class="admin-btn admin-btn-sm" style="padding:9px 18px;background:#0F172A;color:#FFF;font-weight:600;border:none;border-radius:8px;">Search</button>
    <?php if (!empty($search) || $filterStatus !== 'all'): ?>
      <a href="<?= ADMIN_URL ?>/bookings.php" class="admin-btn admin-btn-outline admin-btn-sm" style="padding:8px 14px;">Clear</a>
    <?php endif; ?>
  </form>
</div>

<!-- Bookings Data Table -->
<div class="admin-table-container">
  <div style="overflow-x:auto;">
    <table class="admin-table" style="width:100%;">
      <thead>
        <tr>
          <th>Booking Ref</th>
          <th>Guest Personal Details</th>
          <th>Tour Package</th>
          <th>Travel Date &amp; Guests</th>
          <th>Special Requests</th>
          <th>Status</th>
          <th style="text-align:right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($bookings)): ?>
          <tr>
            <td colspan="7" style="text-align:center;padding:50px 20px;">
              <h4 style="font-size:1.1rem;color:var(--admin-navy);margin-bottom:6px;">No Bookings Found</h4>
              <p style="font-size:0.88rem;color:var(--admin-muted);max-width:400px;margin:0 auto 16px;">
                <?= (!empty($search) || $filterStatus !== 'all') ? 'No bookings matched your filter criteria.' : 'There are currently no customer booking reservations recorded.' ?>
              </p>
              <?php if (!empty($search) || $filterStatus !== 'all'): ?>
                <a href="<?= ADMIN_URL ?>/bookings.php" class="admin-btn admin-btn-outline">Reset Search Filters</a>
              <?php endif; ?>
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($bookings as $bk): ?>
            <?php
              $cleanPhone = preg_replace('/[^0-9]/', '', $bk['customer_phone'] ?? '');
              if (strlen($cleanPhone) === 10) $cleanPhone = '91' . $cleanPhone;
              $waMsg = urlencode("Hello {$bk['customer_name']}, this is Midhila Voyages regarding your Booking Ref #{$bk['booking_reference']}.");
              $waUrl = "https://wa.me/{$cleanPhone}?text={$waMsg}";
            ?>
            <tr>
              <!-- 1. Booking Reference -->
              <td>
                <a href="<?= BASE_URL ?>/payment-success.php?ref=<?= e($bk['booking_reference']) ?>" target="_blank" style="font-weight:700;color:#0F172A;text-decoration:none;display:inline-flex;align-items:center;gap:4px;" title="Open A4 Digital Voucher">
                  <span><?= e($bk['booking_reference']) ?></span>
                  <span style="font-size:0.8rem;color:#C5A059;">↗</span>
                </a>
                <div style="font-size:0.75rem;color:#64748B;margin-top:3px;">
                  <?= date('d M Y, h:i A', strtotime($bk['created_at'])) ?>
                </div>
              </td>

              <!-- 2. Guest Personal Details -->
              <td>
                <strong style="color:#0F172A;display:block;font-size:0.95rem;"><?= e($bk['customer_name']) ?></strong>
                <div style="font-size:0.82rem;color:#64748B;margin-top:3px;line-height:1.5;">
                  <a href="tel:<?= e($bk['customer_phone']) ?>" style="color:#0F172A;text-decoration:none;font-weight:600;">
                    <?= e($bk['customer_phone']) ?>
                  </a>
                  <?php if (!empty($bk['customer_email'])): ?>
                    <br><a href="mailto:<?= e($bk['customer_email']) ?>" style="color:#2563EB;text-decoration:none;"><?= e($bk['customer_email']) ?></a>
                  <?php endif; ?>
                </div>
              </td>

              <!-- 3. Tour Package -->
              <td>
                <div style="font-weight:600;color:#0F172A;line-height:1.35;">
                  <?= e($bk['package_title'] ?: 'Custom Tour Package') ?>
                </div>
                <div style="margin-top:4px;display:flex;gap:6px;flex-wrap:wrap;">
                  <?php if (!empty($bk['duration_days'])): ?>
                    <span style="font-size:0.72rem;background:#F1F5F9;padding:2px 6px;border-radius:4px;color:#475569;font-weight:600;">
                      <?= (int)$bk['duration_days'] ?>D / <?= (int)$bk['duration_nights'] ?>N
                    </span>
                  <?php endif; ?>
                  <?php if (!empty($bk['destination_name'])): ?>
                    <span style="font-size:0.72rem;background:#FEF3C7;color:#92400E;padding:2px 6px;border-radius:4px;font-weight:600;">
                      <?= e($bk['destination_name']) ?>
                    </span>
                  <?php endif; ?>
                </div>
              </td>

              <!-- 4. Travel Date & Guests -->
              <td>
                <div style="font-weight:600;color:#0F172A;font-size:0.9rem;">
                  <?= !empty($bk['travel_date']) ? date('d M Y', strtotime($bk['travel_date'])) : 'Flexible' ?>
                </div>
                <div style="font-size:0.8rem;color:#64748B;margin-top:2px;">
                  <?= (int)$bk['travellers_count'] ?> Guest<?= (int)$bk['travellers_count'] === 1 ? '' : 's' ?>
                </div>
              </td>

              <!-- 5. Special Requests -->
              <td style="max-width:200px;">
                <?php if (!empty($bk['special_requests'])): ?>
                  <div style="font-size:0.82rem;color:#334155;line-height:1.4;background:#F8FAFC;padding:6px 10px;border-radius:6px;border-left:2.5px solid #C5A059;">
                    <?= e(mb_strimwidth($bk['special_requests'], 0, 70, '...')) ?>
                  </div>
                <?php else: ?>
                  <span style="font-size:0.8rem;color:#94A3B8;">None specified</span>
                <?php endif; ?>
              </td>

              <!-- 6. Status -->
              <td>
                <span class="badge <?= ($bk['booking_status'] === 'confirmed') ? 'badge-success' : (($bk['booking_status'] === 'pending') ? 'badge-warning' : (($bk['booking_status'] === 'completed') ? 'badge-info' : 'badge-neutral')) ?>" style="text-transform:capitalize;font-size:0.8rem;padding:4px 10px;">
                  <?= e($bk['booking_status']) ?>
                </span>
              </td>

              <!-- 7. Actions -->
              <td style="text-align:right;">
                <div style="display:inline-flex;gap:6px;align-items:center;justify-content:flex-end;">
                  <a href="<?= e($waUrl) ?>" target="_blank" rel="noopener noreferrer" class="admin-btn admin-btn-sm" style="background:#25D366;color:#FFFFFF;border:none;font-weight:600;padding:5px 9px;border-radius:6px;display:inline-flex;align-items:center;gap:4px;" title="WhatsApp Guest">
                    <span>WhatsApp</span>
                  </a>

                  <button type="button" 
                          class="admin-btn admin-btn-outline admin-btn-sm" 
                          onclick="openStatusUpdateModal(<?= (int)$bk['id'] ?>, '<?= e(addslashes($bk['booking_reference'])) ?>', '<?= e(addslashes($bk['customer_name'])) ?>', '<?= e($bk['booking_status']) ?>')"
                          style="white-space:nowrap;padding:5px 10px;font-size:0.8rem;"
                          title="Update Status">
                    Status
                  </button>

                  <a href="<?= BASE_URL ?>/payment-success.php?ref=<?= e($bk['booking_reference']) ?>" 
                     target="_blank" 
                     class="admin-btn admin-btn-outline admin-btn-sm" 
                     style="white-space:nowrap;padding:5px 10px;font-size:0.8rem;"
                     title="View &amp; Print A4 Passenger Voucher">
                    Voucher
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

<!-- Modal 1: Update Booking Status -->
<div id="bookingStatusModal" class="admin-modal-overlay" style="display:none;" aria-hidden="true">
  <div class="admin-modal-card" style="max-width:420px;text-align:left;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;border-bottom:1px solid #E2E8F0;padding-bottom:12px;">
      <div>
        <h3 style="font-size:1.15rem;color:#0F172A;margin:0 0 2px 0;">Update Booking Status</h3>
        <span id="modalBkgRefSubtitle" style="font-size:0.8rem;color:#64748B;font-weight:600;">-</span>
      </div>
      <button type="button" onclick="closeStatusUpdateModal()" style="border:none;background:none;font-size:1.4rem;cursor:pointer;color:#64748B;">&times;</button>
    </div>

    <form method="POST" action="<?= ADMIN_URL ?>/bookings.php<?= !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '' ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="update_booking_status" value="1">
      <input type="hidden" name="booking_id" id="modalBookingId" value="0">

      <div style="margin-bottom:20px;">
        <label style="display:block;font-size:0.82rem;font-weight:700;color:#0F172A;margin-bottom:6px;">Booking Status</label>
        <select name="booking_status" id="modalBookingStatus" style="width:100%;padding:10px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.9rem;background:#FFF;">
          <option value="pending">Pending Review</option>
          <option value="confirmed">Confirmed (Guaranteed Itinerary)</option>
          <option value="completed">Completed Tour</option>
          <option value="cancelled">Cancelled</option>
        </select>
      </div>

      <div style="display:flex;gap:10px;justify-content:flex-end;">
        <button type="button" onclick="closeStatusUpdateModal()" class="admin-btn admin-btn-outline admin-btn-sm">Cancel</button>
        <button type="submit" class="admin-btn admin-btn-primary admin-btn-sm" style="background:#0F172A;">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal 2: Create Manual Booking -->
<div id="newBookingModal" class="admin-modal-overlay" style="display:none;" aria-hidden="true">
  <div class="admin-modal-card" style="max-width:520px;text-align:left;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;border-bottom:1px solid #E2E8F0;padding-bottom:12px;">
      <div>
        <h3 style="font-size:1.2rem;color:#0F172A;margin:0 0 2px 0;">Record New Booking</h3>
        <span style="font-size:0.8rem;color:#64748B;">Enter telephone or direct customer reservation details</span>
      </div>
      <button type="button" onclick="closeNewBookingModal()" style="border:none;background:none;font-size:1.4rem;cursor:pointer;color:#64748B;">&times;</button>
    </div>

    <form method="POST" action="<?= ADMIN_URL ?>/bookings.php">
      <?= csrf_field() ?>
      <input type="hidden" name="create_manual_booking" value="1">

      <div style="margin-bottom:14px;">
        <label style="display:block;font-size:0.82rem;font-weight:700;color:#0F172A;margin-bottom:4px;">Tour Package *</label>
        <select name="package_id" required style="width:100%;padding:9px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.88rem;background:#FFF;">
          <option value="" disabled selected>Select Package</option>
          <?php foreach ($packagesList as $p): ?>
            <option value="<?= (int)$p['id'] ?>"><?= e($p['title']) ?> (<?= format_price((float)$p['starting_price']) ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
        <div>
          <label style="display:block;font-size:0.82rem;font-weight:700;color:#0F172A;margin-bottom:4px;">Guest Full Name *</label>
          <input type="text" name="customer_name" required placeholder="Enter your full name" style="width:100%;padding:9px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.88rem;">
        </div>
        <div>
          <label style="display:block;font-size:0.82rem;font-weight:700;color:#0F172A;margin-bottom:4px;">Phone / WhatsApp *</label>
          <input type="tel" name="customer_phone" required placeholder="Enter your phone number" style="width:100%;padding:9px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.88rem;">
        </div>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
        <div>
          <label style="display:block;font-size:0.82rem;font-weight:700;color:#0F172A;margin-bottom:4px;">Email Address</label>
          <input type="email" name="customer_email" placeholder="Enter your email address" style="width:100%;padding:9px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.88rem;">
        </div>
        <div>
          <label style="display:block;font-size:0.82rem;font-weight:700;color:#0F172A;margin-bottom:4px;">Departure Date</label>
          <input type="date" name="travel_date" value="<?= date('Y-m-d') ?>" title="Select travel date" style="width:100%;padding:9px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.88rem;">
        </div>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
        <div>
          <label style="display:block;font-size:0.82rem;font-weight:700;color:#0F172A;margin-bottom:4px;">Number of Guests</label>
          <input type="number" name="travellers_count" min="1" max="50" value="2" placeholder="Enter your number of travellers" style="width:100%;padding:9px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.88rem;">
        </div>
        <div>
          <label style="display:block;font-size:0.82rem;font-weight:700;color:#0F172A;margin-bottom:4px;">Initial Status</label>
          <select name="booking_status" style="width:100%;padding:9px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.88rem;background:#FFF;">
            <option value="" disabled>Select booking status</option>
            <option value="confirmed" selected>Confirmed</option>
            <option value="pending">Pending Review</option>
          </select>
        </div>
      </div>

      <div style="margin-bottom:20px;">
        <label style="display:block;font-size:0.82rem;font-weight:700;color:#0F172A;margin-bottom:4px;">Special Requests / Instructions</label>
        <textarea name="special_requests" rows="2" placeholder="Enter your special requests" style="width:100%;padding:9px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.88rem;"></textarea>
      </div>

      <div style="display:flex;gap:10px;justify-content:flex-end;">
        <button type="button" onclick="closeNewBookingModal()" class="admin-btn admin-btn-outline admin-btn-sm">Cancel</button>
        <button type="submit" class="admin-btn admin-btn-primary admin-btn-sm" style="background:#0F172A;">Save &amp; Generate Voucher</button>
      </div>
    </form>
  </div>
</div>

<script>
function openStatusUpdateModal(id, ref, name, status) {
  document.getElementById('modalBookingId').value = id;
  document.getElementById('modalBkgRefSubtitle').textContent = ref + ' — ' + name;
  document.getElementById('modalBookingStatus').value = status;
  
  const modal = document.getElementById('bookingStatusModal');
  modal.style.display = 'flex';
  modal.setAttribute('aria-hidden', 'false');
}

function closeStatusUpdateModal() {
  const modal = document.getElementById('bookingStatusModal');
  modal.style.display = 'none';
  modal.setAttribute('aria-hidden', 'true');
}

function openNewBookingModal() {
  const modal = document.getElementById('newBookingModal');
  modal.style.display = 'flex';
  modal.setAttribute('aria-hidden', 'false');
}

function closeNewBookingModal() {
  const modal = document.getElementById('newBookingModal');
  modal.style.display = 'none';
  modal.setAttribute('aria-hidden', 'true');
}

// Close when clicking outside modal
document.querySelectorAll('.admin-modal-overlay').forEach(el => {
  el.addEventListener('click', function(e) {
    if (e.target === this) {
      this.style.display = 'none';
      this.setAttribute('aria-hidden', 'true');
    }
  });
});
</script>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
