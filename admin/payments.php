<?php
/**
 * MIDHILA VOYAGES — Payment Gateway Transaction Audit & Search
 */

require_once __DIR__ . '/includes/admin-init.php';

// Search and Filter Parameters
$search = trim($_GET['search'] ?? '');
$payStatus = trim($_GET['pay_status'] ?? '');
$gateway = trim($_GET['gateway'] ?? '');

$where = [];
$params = [];

if (!empty($search)) {
    $where[] = "(p.booking_reference LIKE ? OR b.customer_name LIKE ? OR b.customer_email LIKE ? OR p.gateway_order_id LIKE ? OR p.gateway_payment_id LIKE ?)";
    $term = "%{$search}%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

if (!empty($payStatus)) {
    $where[] = "p.status = ?";
    $params[] = $payStatus;
}

if (!empty($gateway)) {
    $where[] = "p.payment_gateway = ?";
    $params[] = $gateway;
}

$whereClause = !empty($where) ? ('WHERE ' . implode(' AND ', $where)) : '';

$stmt = $db->prepare("
    SELECT p.*, b.customer_name, b.customer_email, b.customer_phone
    FROM `payments` p
    LEFT JOIN `bookings` b ON p.booking_id = b.id
    {$whereClause}
    ORDER BY p.id DESC
");
$stmt->execute($params);
$payments = $stmt->fetchAll();

// Global Financial KPIs
$kpiCollected = (float)$db->query("SELECT COALESCE(SUM(`amount`), 0) FROM `payments` WHERE `status` = 'captured'")->fetchColumn();
$kpiCapturedCount = (int)$db->query("SELECT COUNT(*) FROM `payments` WHERE `status` = 'captured'")->fetchColumn();
$kpiPendingCount = (int)$db->query("SELECT COUNT(*) FROM `payments` WHERE `status` = 'pending'")->fetchColumn();
$kpiFailedCount = (int)$db->query("SELECT COUNT(*) FROM `payments` WHERE `status` = 'failed'")->fetchColumn();

$hasActiveFilters = (!empty($search) || !empty($payStatus) || !empty($gateway));

$adminTitle = 'Payments Audit | MIDHILA VOYAGES';
$adminHeading = 'Payment Transactions & Reconciliation';
require_once __DIR__ . '/includes/admin-header.php';
?>

<!-- Section Header -->
<div class="admin-page-header">
  <div>
    <div style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.12em;color:var(--admin-gold);font-weight:700;margin-bottom:4px;">
      Financial Audit &bull; Gateway Logs
    </div>
    <h1 class="admin-page-title">Payment Transactions &amp; Reconciliation</h1>
    <p class="admin-page-desc">
      Audited ledger of gateway checkout orders, authorization captures, webhook timestamps, and digital receipts.
    </p>
  </div>
  <div style="display:flex;gap:10px;align-items:center;">
    <span style="font-size:0.82rem;background:rgba(34,197,94,0.15);border:1px solid rgba(34,197,94,0.35);padding:6px 16px;border-radius:20px;color:#86EFAC;font-weight:600;backdrop-filter:blur(10px);box-shadow:0 0 16px rgba(34,197,94,0.2);">
      Gateway: <strong>Razorpay (Active)</strong>
    </span>
  </div>
</div>

<!-- Financial KPI Summary Cards -->
<div class="admin-stats-grid">
  <div class="admin-stat-card">
    <div>
      <div class="admin-stat-label">Total Volume Captured</div>
      <div class="admin-stat-value" style="color:var(--admin-navy);"><?= format_price($kpiCollected) ?></div>
      <div class="admin-stat-sub">Verified online payments</div>
    </div>
    <div class="admin-stat-icon" style="background:rgba(34,197,94,0.15);border:1px solid rgba(34,197,94,0.3);color:#86EFAC;">
      💳
    </div>
  </div>

  <div class="admin-stat-card">
    <div>
      <div class="admin-stat-label">Captured Payments</div>
      <div class="admin-stat-value" style="color:#86EFAC;"><?= number_format($kpiCapturedCount) ?></div>
      <div class="admin-stat-sub">Successful checkouts</div>
    </div>
    <div class="admin-stat-icon" style="background:rgba(14,165,233,0.15);border:1px solid rgba(14,165,233,0.3);color:#7DD3FC;">
      ✅
    </div>
  </div>

  <div class="admin-stat-card">
    <div>
      <div class="admin-stat-label">Pending Orders</div>
      <div class="admin-stat-value" style="color:#FDE68A;"><?= number_format($kpiPendingCount) ?></div>
      <div class="admin-stat-sub">Awaiting customer auth</div>
    </div>
    <div class="admin-stat-icon" style="background:rgba(245,158,11,0.15);border:1px solid rgba(245,158,11,0.3);color:#FDE68A;">
      ⏳
    </div>
  </div>

  <div class="admin-stat-card">
    <div>
      <div class="admin-stat-label">Failed / Aborted</div>
      <div class="admin-stat-value" style="color:#FCA5A5;"><?= number_format($kpiFailedCount) ?></div>
      <div class="admin-stat-sub">Declined or abandoned</div>
    </div>
    <div class="admin-stat-icon" style="background:rgba(239,68,68,0.15);border:1px solid rgba(239,68,68,0.3);color:#FCA5A5;">
      ⚠️
    </div>
  </div>
</div>

<!-- Search & Filtering Bar -->
<div class="admin-filter-bar">
  <form method="GET" action="<?= ADMIN_URL ?>/payments.php" class="admin-filter-form no-confirm">
    <div class="admin-search-box">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="11" cy="11" r="8"></circle>
        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
      </svg>
      <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search by booking ref, customer, order ID, payment ID...">
    </div>

    <select name="pay_status" class="admin-filter-select">
      <option value="">All Payment Statuses</option>
      <option value="captured" <?= ($payStatus === 'captured') ? 'selected' : '' ?>>Captured (Success)</option>
      <option value="pending" <?= ($payStatus === 'pending') ? 'selected' : '' ?>>Pending</option>
      <option value="failed" <?= ($payStatus === 'failed') ? 'selected' : '' ?>>Failed</option>
    </select>

    <select name="gateway" class="admin-filter-select">
      <option value="">All Gateways</option>
      <option value="razorpay" <?= (strtolower($gateway) === 'razorpay') ? 'selected' : '' ?>>Razorpay</option>
    </select>

    <button type="submit" class="admin-btn admin-btn-primary" style="padding:9px 18px;">
      <span>Search</span>
    </button>

    <?php if ($hasActiveFilters): ?>
      <a href="<?= ADMIN_URL ?>/payments.php" class="admin-btn admin-btn-outline" style="padding:9px 14px;color:#EF4444;border-color:#FCA5A5;">
        <span>&times; Clear Filters</span>
      </a>
    <?php endif; ?>
  </form>

  <div style="font-size:0.85rem;color:var(--admin-muted);font-weight:600;white-space:nowrap;">
    Showing <?= count($payments) ?> transaction<?= count($payments) === 1 ? '' : 's' ?>
    <?= $hasActiveFilters ? '<span style="color:var(--admin-gold);">(Filtered)</span>' : '' ?>
  </div>
</div>

<!-- Transactions Data Table -->
<div class="admin-table-container">
  <div style="overflow-x:auto;">
    <table class="admin-table" style="width:100%;table-layout:fixed;">
      <colgroup>
        <col style="width:5%;">
        <col style="width:17%;">
        <col style="width:18%;">
        <col style="width:9%;">
        <col style="width:23%;">
        <col style="width:10%;">
        <col style="width:9%;">
        <col style="width:9%;">
      </colgroup>
      <thead>
        <tr>
          <th>ID</th>
          <th>Booking Ref</th>
          <th>Customer Details</th>
          <th>Gateway</th>
          <th>Order &amp; Payment IDs</th>
          <th>Amount</th>
          <th>Status</th>
          <th style="text-align:right;">Voucher</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($payments)): ?>
          <tr>
            <td colspan="8" style="text-align:center;padding:50px 20px;">
              <div style="font-size:2.5rem;margin-bottom:12px;">💳</div>
              <h4 style="font-size:1.1rem;color:var(--admin-navy);margin-bottom:6px;">No Payment Records Found</h4>
              <p style="font-size:0.88rem;color:var(--admin-muted);max-width:400px;margin:0 auto 16px;">
                <?= $hasActiveFilters ? 'No transaction records matched your search filter criteria. Try adjusting keywords or resetting filters.' : 'There are currently no online payment transactions logged in the system.' ?>
              </p>
              <?php if ($hasActiveFilters): ?>
                <a href="<?= ADMIN_URL ?>/payments.php" class="admin-btn admin-btn-outline">Reset Search Filters</a>
              <?php endif; ?>
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($payments as $pay): ?>
            <?php
              $badgeClass = match($pay['status']) {
                'captured' => 'badge-success',
                'pending' => 'badge-warning',
                'failed' => 'badge-danger',
                default => 'badge-neutral'
              };
            ?>
            <tr>
              <!-- ID -->
              <td>
                <span style="font-size:0.82rem;color:var(--admin-muted);font-weight:700;">#<?= (int)$pay['id'] ?></span>
              </td>

              <!-- Booking Reference -->
              <td>
                <a href="<?= BASE_URL ?>/payment-success.php?ref=<?= e($pay['booking_reference']) ?>" target="_blank" style="font-weight:700;color:var(--admin-navy);display:inline-flex;align-items:center;gap:4px;" title="View Booking Voucher">
                  <span><?= e($pay['booking_reference']) ?></span>
                  <span style="font-size:0.8rem;color:var(--admin-gold);">&nearr;</span>
                </a>
                <div style="font-size:0.75rem;color:var(--admin-muted);margin-top:2px;">
                  <?= $pay['verified_at'] ? date('d M Y, h:i A', strtotime($pay['verified_at'])) : date('d M Y, h:i A', strtotime($pay['created_at'])) ?>
                </div>
              </td>

              <!-- Customer Details -->
              <td>
                <strong style="color:var(--admin-navy);display:block;"><?= e($pay['customer_name'] ?: 'Guest Traveller') ?></strong>
                <div style="font-size:0.78rem;color:var(--admin-muted);margin-top:2px;">
                  <?php if (!empty($pay['customer_phone'])): ?>
                    <span>📞 <?= e($pay['customer_phone']) ?></span><br>
                  <?php endif; ?>
                  <?php if (!empty($pay['customer_email'])): ?>
                    <a href="mailto:<?= e($pay['customer_email']) ?>" style="color:var(--admin-teal);">✉️ <?= e($pay['customer_email']) ?></a>
                  <?php endif; ?>
                </div>
              </td>

              <!-- Gateway -->
              <td>
                <span style="font-size:0.72rem;font-weight:700;background:#F1F5F9;padding:3px 8px;border-radius:4px;color:#334155;letter-spacing:0.04em;">
                  <?= strtoupper(e($pay['payment_gateway'])) ?>
                </span>
              </td>

              <!-- Order & Payment IDs -->
              <td>
                <div style="display:flex;flex-direction:column;gap:5px;">
                  <?php if (!empty($pay['gateway_order_id'])): ?>
                    <div class="code-pill" title="Gateway Order ID">
                      <span style="font-size:0.7rem;color:var(--admin-muted);font-weight:700;">ORD:</span>
                      <code><?= e($pay['gateway_order_id']) ?></code>
                      <button type="button" class="code-copy-btn" onclick="navigator.clipboard.writeText('<?= e($pay['gateway_order_id']) ?>');this.textContent='✓';setTimeout(()=>this.textContent='📋',1200)" title="Copy Order ID">📋</button>
                    </div>
                  <?php endif; ?>

                  <?php if (!empty($pay['gateway_payment_id'])): ?>
                    <div class="code-pill" title="Gateway Payment ID">
                      <span style="font-size:0.7rem;color:var(--admin-muted);font-weight:700;">PAY:</span>
                      <code><?= e($pay['gateway_payment_id']) ?></code>
                      <button type="button" class="code-copy-btn" onclick="navigator.clipboard.writeText('<?= e($pay['gateway_payment_id']) ?>');this.textContent='✓';setTimeout(()=>this.textContent='📋',1200)" title="Copy Payment ID">📋</button>
                    </div>
                  <?php else: ?>
                    <span class="badge badge-warning" style="font-size:0.7rem;width:fit-content;">Auth Pending</span>
                  <?php endif; ?>
                </div>
              </td>

              <!-- Amount -->
              <td>
                <strong style="font-size:1.05rem;color:var(--admin-navy);">
                  <?= format_price((float)$pay['amount'], $pay['currency']) ?>
                </strong>
              </td>

              <!-- Status -->
              <td>
                <span class="badge <?= $badgeClass ?>" style="padding:4px 10px;font-size:0.78rem;">
                  <?= ucfirst(e($pay['status'])) ?>
                </span>
              </td>

              <!-- Voucher Action -->
              <td style="text-align:right;">
                <a href="<?= BASE_URL ?>/payment-success.php?ref=<?= e($pay['booking_reference']) ?>" 
                   target="_blank" 
                   class="admin-btn admin-btn-outline admin-btn-sm" 
                   style="background:#FFF;white-space:nowrap;padding:5px 10px;font-size:0.8rem;" 
                   title="View &amp; Print A4 Passenger Voucher">
                  📄 Voucher
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
