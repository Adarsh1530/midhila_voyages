<?php
/**
 * MIDHILA VOYAGES — Reports & Analytics Module
 * Comprehensive operational breakdown by date range, enquiry type, workflow status, destinations, and CSV export.
 */

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/database.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';

require_admin_login();
require_role(['super_admin', 'travel_manage']);

$db = getDB();

$startDate = trim($_GET['start_date'] ?? date('Y-m-01'));
$endDate   = trim($_GET['end_date'] ?? date('Y-m-d'));

$where = ["DATE(created_at) >= ? AND DATE(created_at) <= ?"];
$params = [$startDate, $endDate];

// CSV Export Handler
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=midhila_report_' . $startDate . '_to_' . $endDate . '.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Ref No', 'Type', 'Workflow Status', 'Full Name', 'Phone', 'Destination / Venue', 'Departure', 'Adults', 'Children', 'Budget', 'Created At']);

    $stmt = $db->prepare("SELECT * FROM `enquiries` WHERE " . implode(' AND ', $where) . " ORDER BY id DESC");
    $stmt->execute($params);
    while ($row = $stmt->fetch()) {
        fputcsv($output, [
            $row['reference_no'],
            $row['enquiry_type'] ?: 'tour_package',
            $row['workflow_status'] ?: $row['status'],
            $row['full_name'],
            $row['phone'],
            $row['destination_name'] ?: ($row['corporate_venue_type'] ?? ''),
            $row['departure_date'],
            $row['adults_count'],
            $row['children_count'],
            $row['budget_range'],
            $row['created_at']
        ]);
    }
    fclose($output);
    exit;
}

// 1. Total Enquiries in Period
$totalInPeriod = 0;
$stmt = $db->prepare("SELECT COUNT(*) FROM `enquiries` WHERE " . implode(' AND ', $where));
$stmt->execute($params);
$totalInPeriod = (int)$stmt->fetchColumn();

// 2. Breakdown by Type
$typeBreakdown = [];
$tStmt = $db->prepare("
    SELECT COALESCE(NULLIF(enquiry_type, ''), 'tour_package') AS t_name, COUNT(*) AS count_val 
    FROM `enquiries` 
    WHERE " . implode(' AND ', $where) . "
    GROUP BY t_name 
    ORDER BY count_val DESC
");
$tStmt->execute($params);
$typeBreakdown = $tStmt->fetchAll();

// 3. Status Distribution
$statusBreakdown = [];
$sStmt = $db->prepare("
    SELECT COALESCE(NULLIF(workflow_status, ''), status, 'new') AS s_name, COUNT(*) AS count_val 
    FROM `enquiries` 
    WHERE " . implode(' AND ', $where) . "
    GROUP BY s_name 
    ORDER BY count_val DESC
");
$sStmt->execute($params);
$statusBreakdown = $sStmt->fetchAll();

// 4. Top Requested Destinations
$destBreakdown = [];
$dStmt = $db->prepare("
    SELECT COALESCE(NULLIF(destination_name, ''), 'Custom Destination') AS d_name, COUNT(*) AS count_val 
    FROM `enquiries` 
    WHERE " . implode(' AND ', $where) . "
    GROUP BY d_name 
    ORDER BY count_val DESC 
    LIMIT 6
");
$dStmt->execute($params);
$destBreakdown = $dStmt->fetchAll();

$adminTitle = 'Reports & Analytics | MIDHILA VOYAGES';
$adminHeading = 'Operational Intelligence & Analytics';

require_once __DIR__ . '/includes/admin-header.php';
?>

<!-- Header -->
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:16px;">
  <div>
    <h3 style="font-size:1.25rem;color:var(--admin-navy);font-weight:700;">Executive Analytics &amp; Reports</h3>
    <p style="font-size:0.86rem;color:var(--admin-muted);margin:0;">Filter enquiries, status distribution, and destination demand over custom dates.</p>
  </div>
  <a href="<?= ADMIN_URL ?>/reports.php?start_date=<?= urlencode($startDate) ?>&end_date=<?= urlencode($endDate) ?>&export=csv" class="admin-btn admin-btn-outline">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
    <span>Export Period CSV</span>
  </a>
</div>

<!-- Date Filter Form -->
<div class="admin-card" style="padding:18px 24px;margin-bottom:28px;">
  <form method="GET" action="" style="display:flex;flex-wrap:wrap;gap:14px;align-items:flex-end;">
    <div>
      <label style="font-size:0.75rem;font-weight:700;color:#64748B;display:block;margin-bottom:4px;">Start Date</label>
      <input type="date" name="start_date" value="<?= e($startDate) ?>" required style="padding:8px 12px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.88rem;">
    </div>
    <div>
      <label style="font-size:0.75rem;font-weight:700;color:#64748B;display:block;margin-bottom:4px;">End Date</label>
      <input type="date" name="end_date" value="<?= e($endDate) ?>" required style="padding:8px 12px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.88rem;">
    </div>
    <div>
      <button type="submit" class="admin-btn admin-btn-primary" style="padding:9px 18px;">
        Generate Report
      </button>
    </div>
  </form>
</div>

<!-- Summary Counter in Period -->
<div class="admin-card" style="padding:22px 28px;margin-bottom:28px;background:linear-gradient(135deg, #0F172A 0%, #1E293B 100%);color:#FFFFFF;">
  <div style="font-size:0.8rem;text-transform:uppercase;letter-spacing:0.08em;color:#C5A059;font-weight:700;">Selected Window Summary</div>
  <div style="font-size:2.4rem;font-weight:800;line-height:1.2;margin-top:6px;"><?= $totalInPeriod ?> Enquiries</div>
  <div style="font-size:0.84rem;color:#94A3B8;margin-top:4px;">
    Recorded between <?= date('d M Y', strtotime($startDate)) ?> and <?= date('d M Y', strtotime($endDate)) ?>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:28px;">
  
  <!-- Breakdown by Enquiry Type -->
  <div class="admin-card">
    <h4 style="font-size:1.1rem;color:#0F172A;font-weight:700;margin-bottom:16px;">Breakdown by Lead Discipline</h4>
    <?php if (empty($typeBreakdown)): ?>
      <p style="font-size:0.85rem;color:#64748B;">No data in selected date range.</p>
    <?php else: ?>
      <div style="display:flex;flex-direction:column;gap:12px;">
        <?php foreach ($typeBreakdown as $tb): 
          $pct = ($totalInPeriod > 0) ? round(($tb['count_val'] / $totalInPeriod) * 100) : 0;
          $label = ucwords(str_replace('_', ' ', $tb['t_name']));
        ?>
          <div>
            <div style="display:flex;justify-content:space-between;font-size:0.86rem;font-weight:600;margin-bottom:4px;color:#0F172A;">
              <span><?= e($label) ?></span>
              <span><?= (int)$tb['count_val'] ?> (<?= $pct ?>%)</span>
            </div>
            <div style="background:#F1F5F9;height:8px;border-radius:4px;overflow:hidden;">
              <div style="background:#0F172A;height:100%;width:<?= $pct ?>%;"></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- Status Distribution -->
  <div class="admin-card">
    <h4 style="font-size:1.1rem;color:#0F172A;font-weight:700;margin-bottom:16px;">Workflow Status Distribution</h4>
    <?php if (empty($statusBreakdown)): ?>
      <p style="font-size:0.85rem;color:#64748B;">No data in selected date range.</p>
    <?php else: ?>
      <div style="display:flex;flex-direction:column;gap:12px;">
        <?php foreach ($statusBreakdown as $sb): 
          $pct = ($totalInPeriod > 0) ? round(($sb['count_val'] / $totalInPeriod) * 100) : 0;
          $label = ucwords(str_replace('_', ' ', $sb['s_name']));
        ?>
          <div>
            <div style="display:flex;justify-content:space-between;font-size:0.86rem;font-weight:600;margin-bottom:4px;color:#0F172A;">
              <span><?= e($label) ?></span>
              <span><?= (int)$sb['count_val'] ?> (<?= $pct ?>%)</span>
            </div>
            <div style="background:#F1F5F9;height:8px;border-radius:4px;overflow:hidden;">
              <div style="background:#2563EB;height:100%;width:<?= $pct ?>%;"></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

</div>

<!-- Top Destinations Demand -->
<div class="admin-card">
  <h4 style="font-size:1.1rem;color:#0F172A;font-weight:700;margin-bottom:16px;">Most Requested Horizons &amp; Destinations</h4>
  <?php if (empty($destBreakdown)): ?>
    <p style="font-size:0.85rem;color:#64748B;">No destination data available for this period.</p>
  <?php else: ?>
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));gap:14px;">
      <?php foreach ($destBreakdown as $dbItem): ?>
        <div style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:10px;padding:16px;">
          <div style="font-size:0.75rem;text-transform:uppercase;color:#64748B;font-weight:700;">Destination</div>
          <div style="font-size:1.15rem;font-weight:700;color:#0F172A;margin-top:4px;"><?= e($dbItem['d_name']) ?></div>
          <div style="font-size:0.84rem;color:#059669;font-weight:600;margin-top:4px;"><?= (int)$dbItem['count_val'] ?> Leads</div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
