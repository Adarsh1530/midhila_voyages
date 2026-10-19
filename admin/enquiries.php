<?php
/**
 * MIDHILA VOYAGES — Enquiry Management & CSV Export
 */

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/database.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';

require_admin_login();

$db = getDB();

// CSV Export Handler
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=midhila_enquiries_' . date('Y-m-d') . '.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Ref No', 'Full Name', 'Phone', 'Email', 'Destination', 'Package', 'Category', 'Departure', 'Return', 'Adults', 'Children', 'Budget', 'Status', 'Date Received']);
    
    $exportStmt = $db->query("SELECT * FROM `enquiries` ORDER BY `id` DESC");
    while ($row = $exportStmt->fetch()) {
        fputcsv($output, [
            $row['reference_no'], $row['full_name'], $row['phone'], $row['email'],
            $row['destination_name'], $row['package_title'], $row['travel_category'],
            $row['departure_date'], $row['return_date'], $row['adults_count'],
            $row['children_count'], $row['budget_range'], $row['status'], $row['created_at']
        ]);
    }
    fclose($output);
    exit;
}

$statusFilter = $_GET['status'] ?? '';
$search = trim($_GET['search'] ?? '');

$sql = "SELECT * FROM `enquiries` WHERE 1=1";
$params = [];

if (!empty($statusFilter)) {
    $sql .= " AND `status` = ?";
    $params[] = $statusFilter;
}
if (!empty($search)) {
    $sql .= " AND (`reference_no` LIKE ? OR `full_name` LIKE ? OR `phone` LIKE ? OR `destination_name` LIKE ?)";
    $term = "%{$search}%";
    $params[] = $term; $params[] = $term; $params[] = $term; $params[] = $term;
}
$sql .= " ORDER BY `id` DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$enquiries = $stmt->fetchAll();

$adminTitle = 'Manage Enquiries | MIDHILA VOYAGES';
$adminHeading = 'Trip Enquiries & Leads';

require_once __DIR__ . '/includes/admin-header.php';
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:16px;">
  <div>
    <h3 style="font-size:1.15rem;color:var(--admin-navy);">Customer Trip Enquiries</h3>
    <p style="font-size:0.85rem;color:var(--admin-muted);">Follow up with prospective travellers and coordinate proposal itineraries.</p>
  </div>
  <a href="<?= ADMIN_URL ?>/enquiries.php?export=csv" class="admin-btn admin-btn-outline">
    <span>📥 Export to CSV</span>
  </a>
</div>

<!-- Search & Status Filter Bar -->
<div class="admin-card" style="padding:16px 20px;margin-bottom:24px;">
  <form method="GET" action="<?= ADMIN_URL ?>/enquiries.php" style="display:grid;grid-template-columns:1fr 200px auto;gap:12px;align-items:end;">
    <div>
      <label style="font-size:0.75rem;font-weight:700;color:var(--admin-muted);display:block;margin-bottom:4px;">Search Lead</label>
      <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search by name, phone, reference, or destination..." 
             style="width:100%;padding:8px 12px;border:1px solid var(--admin-border);border-radius:6px;font-size:0.9rem;">
    </div>

    <div>
      <label style="font-size:0.75rem;font-weight:700;color:var(--admin-muted);display:block;margin-bottom:4px;">Status Filter</label>
      <select name="status" style="width:100%;padding:8px 12px;border:1px solid var(--admin-border);border-radius:6px;font-size:0.9rem;background:#FFF;">
        <option value="">All Statuses</option>
        <option value="new" <?= ($statusFilter === 'new') ? 'selected' : '' ?>>New</option>
        <option value="in_progress" <?= ($statusFilter === 'in_progress') ? 'selected' : '' ?>>In Progress</option>
        <option value="contacted" <?= ($statusFilter === 'contacted') ? 'selected' : '' ?>>Contacted</option>
        <option value="converted" <?= ($statusFilter === 'converted') ? 'selected' : '' ?>>Converted</option>
        <option value="closed" <?= ($statusFilter === 'closed') ? 'selected' : '' ?>>Closed</option>
      </select>
    </div>

    <div style="display:flex;gap:6px;">
      <button type="submit" class="admin-btn admin-btn-outline">Filter</button>
      <a href="<?= ADMIN_URL ?>/enquiries.php" class="admin-btn admin-btn-outline">Reset</a>
    </div>
  </form>
</div>

<!-- Enquiries Table -->
<div class="admin-card" style="padding:0;overflow:hidden;">
  <table class="admin-table">
    <thead>
      <tr>
        <th>Ref No</th>
        <th>Traveller</th>
        <th>Destination / Package</th>
        <th>Travel Dates</th>
        <th>Guests</th>
        <th>Status</th>
        <th>Received</th>
        <th style="text-align:right;">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($enquiries)): ?>
        <tr>
          <td colspan="8" style="text-align:center;padding:36px;color:var(--admin-muted);">No enquiries match your search filter.</td>
        </tr>
      <?php else: ?>
        <?php foreach ($enquiries as $enq): ?>
          <?php
            $stClass = match($enq['status']) {
              'new' => 'badge-info',
              'in_progress' => 'badge-warning',
              'contacted' => 'badge-neutral',
              'converted' => 'badge-success',
              'closed' => 'badge-danger',
              default => 'badge-neutral'
            };
            $cleanPhone = preg_replace('/[^0-9]/', '', $enq['phone']);
            $waLeadMsg = "Hello " . $enq['full_name'] . ", greetings from Midhila Voyages. Following up on your trip enquiry (#" . $enq['reference_no'] . ").";
            $waLeadUrl = build_whatsapp_url($cleanPhone, $waLeadMsg);
          ?>
          <tr>
            <td>
              <a href="<?= ADMIN_URL ?>/enquiry-view.php?id=<?= (int)$enq['id'] ?>" style="font-weight:700;color:var(--admin-navy);">
                <?= e($enq['reference_no']) ?>
              </a>
            </td>
            <td>
              <strong><?= e($enq['full_name']) ?></strong>
              <div style="font-size:0.8rem;color:var(--admin-muted);"><?= e($enq['phone']) ?></div>
            </td>
            <td>
              <?= e($enq['destination_name'] ?: 'Custom Plan') ?>
              <?php if (!empty($enq['package_title'])): ?>
                <div style="font-size:0.75rem;color:var(--admin-gold);"><?= e($enq['package_title']) ?></div>
              <?php endif; ?>
            </td>
            <td style="font-size:0.85rem;">
              <?= $enq['departure_date'] ? date('d M Y', strtotime($enq['departure_date'])) : 'Flexible' ?>
            </td>
            <td>
              <?= (int)$enq['adults_count'] ?>A <?= ($enq['children_count'] > 0) ? ', ' . (int)$enq['children_count'] . 'C' : '' ?>
            </td>
            <td>
              <span class="badge <?= $stClass ?>"><?= str_replace('_', ' ', e($enq['status'])) ?></span>
            </td>
            <td style="font-size:0.82rem;color:var(--admin-muted);">
              <?= date('d M, h:i A', strtotime($enq['created_at'])) ?>
            </td>
            <td style="text-align:right;">
              <div style="display:inline-flex;gap:6px;">
                <a href="<?= ADMIN_URL ?>/enquiry-view.php?id=<?= (int)$enq['id'] ?>" class="admin-btn admin-btn-outline admin-btn-sm">View</a>
                <a href="<?= e($waLeadUrl) ?>" target="_blank" rel="noopener noreferrer" class="admin-btn admin-btn-sm" style="background:#25D366;color:#FFF;">WhatsApp</a>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
