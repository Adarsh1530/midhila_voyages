<?php
/**
 * MIDHILA VOYAGES — Customer Registry & Repeat Customer Intelligence
 * Centralised customer directory with repeat customer flags, enquiry history, search, and CSV export.
 */

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/database.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';

require_admin_login();
require_role(['super_admin', 'travel_manage']);

$db = getDB();

$search = trim($_GET['q'] ?? '');
$filterRepeat = isset($_GET['repeat_only']) && $_GET['repeat_only'] === '1';

// Build search SQL
$where = [];
$params = [];

if ($search !== '') {
    $where[] = "(`full_name` LIKE ? OR `phone` LIKE ? OR `email` LIKE ? OR `city` LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

if ($filterRepeat) {
    $where[] = "(`total_enquiries` > 1 OR `total_bookings` > 0)";
}

$whereSQL = !empty($where) ? ('WHERE ' . implode(' AND ', $where)) : '';

// CSV Export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=midhila_customers_' . date('Y-m-d') . '.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Customer ID', 'Full Name', 'Phone', 'WhatsApp', 'Email', 'City', 'Language', 'Total Enquiries', 'Total Bookings', 'Registered Date']);

    $expStmt = $db->prepare("SELECT * FROM `customers` {$whereSQL} ORDER BY `total_enquiries` DESC, `id` DESC");
    $expStmt->execute($params);
    while ($row = $expStmt->fetch()) {
        fputcsv($output, [
            $row['id'], $row['full_name'], $row['phone'], $row['whatsapp'],
            $row['email'], $row['city'], $row['preferred_language'] ?? 'en',
            $row['total_enquiries'], $row['total_bookings'], $row['created_at']
        ]);
    }
    fclose($output);
    exit;
}

$stmt = $db->prepare("SELECT * FROM `customers` {$whereSQL} ORDER BY `total_enquiries` DESC, `id` DESC");
$stmt->execute($params);
$customers = $stmt->fetchAll();

$totalCustomersCount = (int)$db->query("SELECT COUNT(*) FROM `customers`")->fetchColumn();
$repeatCustomersCount = (int)$db->query("SELECT COUNT(*) FROM `customers` WHERE `total_enquiries` > 1 OR `total_bookings` > 0")->fetchColumn();

$adminTitle = 'Customer Registry | MIDHILA VOYAGES';
$adminHeading = 'Customer Registry & CRM';

require_once __DIR__ . '/includes/admin-header.php';
?>

<!-- Header Actions -->
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:16px;">
  <div>
    <h3 style="font-size:1.25rem;color:var(--admin-navy);font-weight:700;">Customer Registry</h3>
    <p style="font-size:0.86rem;color:var(--admin-muted);margin:0;">Centralized directory of prospective travellers, repeat guests, and corporate organizers.</p>
  </div>
  <a href="<?= ADMIN_URL ?>/customers.php?export=csv<?= $search ? '&q=' . urlencode($search) : '' ?><?= $filterRepeat ? '&repeat_only=1' : '' ?>" class="admin-btn admin-btn-outline">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
    <span>Export CRM CSV</span>
  </a>
</div>

<!-- KPI Mini Stats -->
<div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:16px;margin-bottom:24px;">
  <div class="admin-card" style="padding:18px;margin-bottom:0;">
    <div style="font-size:0.75rem;text-transform:uppercase;color:#64748B;font-weight:700;">Total Profiles</div>
    <div style="font-size:1.8rem;font-weight:800;color:#0F172A;margin-top:4px;"><?= $totalCustomersCount ?></div>
    <div style="font-size:0.75rem;color:#64748B;">Registered Guests</div>
  </div>
  <div class="admin-card" style="padding:18px;margin-bottom:0;">
    <div style="font-size:0.75rem;text-transform:uppercase;color:#059669;font-weight:700;">Repeat Customers</div>
    <div style="font-size:1.8rem;font-weight:800;color:#059669;margin-top:4px;"><?= $repeatCustomersCount ?></div>
    <div style="font-size:0.75rem;color:#047857;">High Intent / Returning</div>
  </div>
</div>

<!-- Filter Bar -->
<div class="admin-card" style="padding:16px 20px;margin-bottom:24px;">
  <form method="GET" action="" style="display:flex;flex-wrap:wrap;gap:12px;align-items:center;justify-content:space-between;">
    <div style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;flex-grow:1;">
      <div style="position:relative;flex-grow:1;min-width:260px;max-width:380px;">
        <input type="text" name="q" value="<?= e($search) ?>" placeholder="Search customer name, phone, email, city..." style="width:100%;padding:9px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.88rem;">
      </div>

      <label style="display:inline-flex;align-items:center;gap:6px;font-size:0.86rem;font-weight:600;color:#334155;cursor:pointer;">
        <input type="checkbox" name="repeat_only" value="1" <?= $filterRepeat ? 'checked' : '' ?> onchange="this.form.submit()">
        <span>Show Repeat Customers Only</span>
      </label>

      <button type="submit" class="admin-btn admin-btn-primary" style="padding:8px 16px;">Filter</button>
      <?php if ($search || $filterRepeat): ?>
        <a href="<?= ADMIN_URL ?>/customers.php" class="admin-btn admin-btn-outline" style="padding:8px 14px;">Reset</a>
      <?php endif; ?>
    </div>

    <div style="font-size:0.84rem;color:#64748B;">
      Showing <strong><?= count($customers) ?></strong> customers
    </div>
  </form>
</div>

<!-- Table -->
<div class="admin-table-container">
  <table class="admin-table">
    <thead>
      <tr>
        <th>Customer Name</th>
        <th>Phone / WhatsApp</th>
        <th>Email Address</th>
        <th>City / Location</th>
        <th>Enquiries / Bookings</th>
        <th>Profile Type</th>
        <th style="text-align:right;">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($customers)): ?>
        <tr>
          <td colspan="7" style="text-align:center;padding:40px;color:var(--admin-muted);">
            No customer profiles match this filter. Customer profiles are automatically created whenever visitors submit inquiries.
          </td>
        </tr>
      <?php else: ?>
        <?php foreach ($customers as $c): 
          $isRepeat = ((int)$c['total_enquiries'] > 1 || (int)$c['total_bookings'] > 0);
          $cleanPhone = preg_replace('/[^0-9]/', '', $c['phone']);
          $waMsg = "Hello " . $c['full_name'] . ", greetings from Midhila Voyages! Thank you for choosing us for your journeys. How may we assist you today?";
          $waUrl = build_whatsapp_url($cleanPhone, $waMsg);
        ?>
          <tr>
            <td>
              <div style="font-weight:700;color:#0F172A;font-size:0.95rem;"><?= e($c['full_name']) ?></div>
              <div style="font-size:0.75rem;color:#64748B;">Registered: <?= date('d M Y', strtotime($c['created_at'])) ?></div>
            </td>
            <td>
              <a href="tel:<?= e($cleanPhone) ?>" style="font-weight:600;color:#0F172A;text-decoration:none;">
                <?= e($c['phone']) ?>
              </a>
              <?php if (!empty($c['whatsapp']) && $c['whatsapp'] !== $c['phone']): ?>
                <div style="font-size:0.75rem;color:#059669;">WA: <?= e($c['whatsapp']) ?></div>
              <?php endif; ?>
            </td>
            <td style="font-size:0.88rem;color:#0F172A;">
              <?= e($c['email'] ?: '—') ?>
            </td>
            <td style="font-size:0.86rem;color:#475569;">
              <?= e($c['city'] ?: ($c['state'] ?: 'India')) ?>
            </td>
            <td>
              <strong><?= (int)$c['total_enquiries'] ?></strong> Inquiries
              <?php if ((int)$c['total_bookings'] > 0): ?>
                • <strong style="color:#059669;"><?= (int)$c['total_bookings'] ?></strong> Confirmed
              <?php endif; ?>
            </td>
            <td>
              <?php if ($isRepeat): ?>
                <span class="badge badge-success" style="font-size:0.72rem;letter-spacing:0.04em;">★ Repeat Guest</span>
              <?php else: ?>
                <span class="badge badge-neutral" style="font-size:0.72rem;">New Customer</span>
              <?php endif; ?>
            </td>
            <td style="text-align:right;white-space:nowrap;">
              <a href="<?= e($waUrl) ?>" target="_blank" rel="noopener noreferrer" class="admin-btn admin-btn-sm" style="background:#25D366;color:#FFF;padding:6px 10px;">
                WhatsApp
              </a>
              <a href="<?= ADMIN_URL ?>/enquiries.php?q=<?= urlencode($c['phone']) ?>" class="admin-btn admin-btn-outline admin-btn-sm" style="margin-left:4px;">
                History &rarr;
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
