<?php
/**
 * MIDHILA VOYAGES — Centralised Booking Enquiries Management
 * Covers 6 categories: Tour Packages, Custom Tours, Accommodations, Kerala Tourism, Pilgrimage, Corporate Events
 * Supports 9-step availability workflow, CSV export, multi-field search, and instant communication triggers.
 */

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/database.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/csrf.php';

require_admin_login();
require_role(['super_admin', 'travel_manage']);

$db = getDB();

// Handle Delete Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    if (verify_csrf_token()) {
        $delId = (int)($_POST['id'] ?? 0);
        if ($delId > 0) {
            $refStmt = $db->prepare("SELECT reference_no FROM `enquiries` WHERE id = ?");
            $refStmt->execute([$delId]);
            $delRef = $refStmt->fetchColumn() ?: ('#' . $delId);

            $delStmt = $db->prepare("DELETE FROM `enquiries` WHERE id = ?");
            $delStmt->execute([$delId]);

            // Clean related tables
            try {
                $db->prepare("DELETE FROM `availability_checks` WHERE enquiry_id = ?")->execute([$delId]);
                $db->prepare("DELETE FROM `customer_communications` WHERE enquiry_id = ?")->execute([$delId]);
                $db->prepare("DELETE FROM `booking_status_history` WHERE enquiry_id = ?")->execute([$delId]);
                $db->prepare("DELETE FROM `enquiry_notes` WHERE enquiry_id = ?")->execute([$delId]);
            } catch (Exception $e) {}

            log_activity($_SESSION['admin_user_id'] ?? null, 'delete_enquiry', "Deleted enquiry {$delRef}");
            set_flash_message('success', "Enquiry {$delRef} was permanently deleted.");
            header('Location: ' . ADMIN_URL . '/enquiries.php');
            exit;
        }
    }
}

// Filters & Query State
$tab = trim($_GET['tab'] ?? 'all');
$statusFilter = trim($_GET['status'] ?? '');
$search = trim($_GET['q'] ?? '');

$validTabs = ['all', 'tour_package', 'custom_tour', 'accommodation', 'kerala_tourism', 'pilgrimage', 'corporate_event'];
if (!in_array($tab, $validTabs, true)) {
    $tab = 'all';
}

// Build SQL
$whereClauses = [];
$params = [];

if ($tab !== 'all') {
    if ($tab === 'pilgrimage') {
        $whereClauses[] = "(`enquiry_type` = 'pilgrimage' OR `travel_category` LIKE '%pilgrimage%' OR `destination_name` IN ('Guruvayur', 'Sabarimala', 'Padmanabhaswamy Temple'))";
    } elseif ($tab === 'tour_package') {
        $whereClauses[] = "(`enquiry_type` = 'tour_package' OR (`enquiry_type` IS NULL AND `package_title` IS NOT NULL AND `package_title` != ''))";
    } else {
        $whereClauses[] = "`enquiry_type` = ?";
        $params[] = $tab;
    }
}

if ($statusFilter !== '') {
    $whereClauses[] = "(`workflow_status` = ? OR `status` = ?)";
    $params[] = $statusFilter;
    $params[] = $statusFilter;
}

if ($search !== '') {
    $term = '%' . $search . '%';
    $whereClauses[] = "(`reference_no` LIKE ? OR `full_name` LIKE ? OR `phone` LIKE ? OR `email` LIKE ? OR `destination_name` LIKE ? OR `corporate_organisation_name` LIKE ?)";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

$whereSQL = !empty($whereClauses) ? ('WHERE ' . implode(' AND ', $whereClauses)) : '';

// CSV Export Handler
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=midhila_central_enquiries_' . date('Y-m-d') . '.csv');
    $output = fopen('php://output', 'w');
    
    fputcsv($output, [
        'Reference No', 'Type', 'Workflow Status', 'Full Name', 'Phone', 'Email', 
        'Destination / Venue', 'Package Title', 'Accommodation', 'Rooms', 'Duration (Days)',
        'Travel Date', 'Adults', 'Children', 'Food Pref', 'Food Type', 'Sightseeing', 
        'Corporate Org', 'Event Type', 'Participants', 'Language', 'Submitted At'
    ]);
    
    $exportStmt = $db->prepare("SELECT * FROM `enquiries` {$whereSQL} ORDER BY `id` DESC");
    $exportStmt->execute($params);
    
    while ($row = $exportStmt->fetch()) {
        fputcsv($output, [
            $row['reference_no'],
            $row['enquiry_type'] ?: 'tour_package',
            $row['workflow_status'] ?: $row['status'],
            $row['full_name'],
            $row['phone'],
            $row['email'],
            $row['destination_name'] ?: ($row['corporate_venue_type'] ?? ''),
            $row['package_title'],
            $row['accommodation_name'],
            $row['rooms_count'] ?? 1,
            $row['duration_days'] ?? '',
            $row['departure_date'],
            $row['adults_count'],
            $row['children_count'],
            $row['food_preference'],
            $row['food_type'],
            $row['sightseeing_preference'],
            $row['corporate_organisation_name'],
            $row['corporate_event_type'],
            $row['corporate_participants_count'],
            $row['language'] ?? 'en',
            $row['created_at']
        ]);
    }
    fclose($output);
    exit;
}

// Fetch Enquiries
$stmt = $db->prepare("SELECT * FROM `enquiries` {$whereSQL} ORDER BY `id` DESC");
$stmt->execute($params);
$enquiries = $stmt->fetchAll();

// Counts for Tabs
function get_tab_count(PDO $db, string $tabType): int {
    try {
        if ($tabType === 'all') {
            return (int)$db->query("SELECT COUNT(*) FROM `enquiries`")->fetchColumn();
        } elseif ($tabType === 'pilgrimage') {
            return (int)$db->query("SELECT COUNT(*) FROM `enquiries` WHERE `enquiry_type` = 'pilgrimage' OR `travel_category` LIKE '%pilgrimage%' OR `destination_name` IN ('Guruvayur', 'Sabarimala', 'Padmanabhaswamy Temple')")->fetchColumn();
        } elseif ($tabType === 'tour_package') {
            return (int)$db->query("SELECT COUNT(*) FROM `enquiries` WHERE `enquiry_type` = 'tour_package' OR (`enquiry_type` IS NULL AND `package_title` IS NOT NULL AND `package_title` != '')")->fetchColumn();
        } else {
            $s = $db->prepare("SELECT COUNT(*) FROM `enquiries` WHERE `enquiry_type` = ?");
            $s->execute([$tabType]);
            return (int)$s->fetchColumn();
        }
    } catch (Exception $e) {
        return 0;
    }
}

$tabCounts = [
    'all' => get_tab_count($db, 'all'),
    'tour_package' => get_tab_count($db, 'tour_package'),
    'custom_tour' => get_tab_count($db, 'custom_tour'),
    'accommodation' => get_tab_count($db, 'accommodation'),
    'kerala_tourism' => get_tab_count($db, 'kerala_tourism'),
    'pilgrimage' => get_tab_count($db, 'pilgrimage'),
    'corporate_event' => get_tab_count($db, 'corporate_event'),
];

$adminTitle = 'Centralised Booking Enquiries | MIDHILA VOYAGES';
$adminHeading = 'Booking Enquiries & Workflow Management';

require_once __DIR__ . '/includes/admin-header.php';
?>

<!-- Header Actions -->
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:16px;">
  <div>
    <h3 style="font-size:1.25rem;color:var(--admin-navy);font-weight:700;margin-bottom:4px;">
      Centralised Enquiries Hub
    </h3>
    <p style="font-size:0.86rem;color:var(--admin-muted);margin:0;">
      Real-time customer inquiries across Tour Packages, Custom Itineraries, Accommodations, Kerala Tourism, and Corporate Events.
    </p>
  </div>
  
  <div style="display:flex;gap:10px;align-items:center;">
    <a href="<?= ADMIN_URL ?>/enquiries.php?tab=<?= urlencode($tab) ?>&status=<?= urlencode($statusFilter) ?>&q=<?= urlencode($search) ?>&export=csv" class="admin-btn admin-btn-outline" style="font-size:0.85rem;">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
      <span>Export to CSV</span>
    </a>
  </div>
</div>

<!-- Category Tabs (6 Modules + All) -->
<div style="display:flex;gap:8px;overflow-x:auto;padding-bottom:12px;margin-bottom:20px;border-bottom:1px solid #E2E8F0;">
  <?php 
  $tabsMap = [
    'all' => ['label' => 'All Inquiries', 'icon' => '📂'],
    'tour_package' => ['label' => 'Tour Packages', 'icon' => '🧭'],
    'custom_tour' => ['label' => 'Customised Tours', 'icon' => '✨'],
    'accommodation' => ['label' => 'Accommodations', 'icon' => '🏨'],
    'kerala_tourism' => ['label' => 'Kerala Tourism', 'icon' => '🌴'],
    'pilgrimage' => ['label' => 'Pilgrimage Sacred', 'icon' => '🛕'],
    'corporate_event' => ['label' => 'Corporate Events', 'icon' => '💼']
  ];
  foreach ($tabsMap as $tKey => $tMeta): 
    $isActive = ($tab === $tKey);
    $countVal = $tabCounts[$tKey] ?? 0;
  ?>
    <a href="<?= ADMIN_URL ?>/enquiries.php?tab=<?= $tKey ?><?= $statusFilter ? '&status=' . urlencode($statusFilter) : '' ?><?= $search ? '&q=' . urlencode($search) : '' ?>" 
       style="display:inline-flex;align-items:center;gap:8px;padding:9px 16px;border-radius:10px;font-size:0.86rem;font-weight:600;white-space:nowrap;text-decoration:none;transition:all 0.15s;<?= $isActive ? 'background:#0F172A;color:#FFFFFF;box-shadow:0 2px 8px rgba(15,23,42,0.12);' : 'background:#FFFFFF;color:#475569;border:1px solid #E2E8F0;' ?>">
      <span><?= $tMeta['icon'] ?></span>
      <span><?= $tMeta['label'] ?></span>
      <span style="padding:2px 7px;border-radius:999px;font-size:0.75rem;font-weight:700;<?= $isActive ? 'background:rgba(255,255,255,0.2);color:#FFFFFF;' : 'background:#F1F5F9;color:#0F172A;' ?>">
        <?= $countVal ?>
      </span>
    </a>
  <?php endforeach; ?>
</div>

<!-- Filter & Search Bar -->
<div class="admin-card" style="padding:16px 20px;margin-bottom:24px;">
  <form method="GET" action="" style="display:flex;flex-wrap:wrap;gap:12px;align-items:center;justify-content:space-between;">
    <input type="hidden" name="tab" value="<?= e($tab) ?>">
    
    <div style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;flex-grow:1;">
      <!-- Search Input -->
      <div style="position:relative;flex-grow:1;min-width:240px;max-width:380px;">
        <input type="text" name="q" value="<?= e($search) ?>" placeholder="Search by Ref #, Traveller Name, Phone, Email, Venue..." 
               style="width:100%;padding:9px 14px 9px 36px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.88rem;outline:none;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="2" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
      </div>

      <!-- Status Filter Dropdown (9 Workflow Stages) -->
      <select name="status" style="padding:9px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.88rem;background:#FFFFFF;color:#0F172A;outline:none;">
        <option value="">All Statuses (Workflow)</option>
        <option value="new" <?= ($statusFilter === 'new') ? 'selected' : '' ?>>1. New Request</option>
        <option value="checking_availability" <?= ($statusFilter === 'checking_availability') ? 'selected' : '' ?>>2. Availability Checking</option>
        <option value="availability_confirmed" <?= ($statusFilter === 'availability_confirmed') ? 'selected' : '' ?>>3. Availability Confirmed</option>
        <option value="availability_unavailable" <?= ($statusFilter === 'availability_unavailable') ? 'selected' : '' ?>>4. Unavailable</option>
        <option value="customer_contacted" <?= ($statusFilter === 'customer_contacted') ? 'selected' : '' ?>>5. Customer Contacted</option>
        <option value="followup_required" <?= ($statusFilter === 'followup_required') ? 'selected' : '' ?>>6. Follow-up Required</option>
        <option value="booking_confirmed" <?= ($statusFilter === 'booking_confirmed') ? 'selected' : '' ?>>7. Booking Confirmed</option>
        <option value="cancelled" <?= ($statusFilter === 'cancelled') ? 'selected' : '' ?>>8. Cancelled</option>
        <option value="rejected" <?= ($statusFilter === 'rejected') ? 'selected' : '' ?>>9. Rejected</option>
      </select>

      <button type="submit" class="admin-btn admin-btn-primary" style="padding:8px 16px;font-size:0.86rem;">
        Filter
      </button>

      <?php if ($search !== '' || $statusFilter !== ''): ?>
        <a href="<?= ADMIN_URL ?>/enquiries.php?tab=<?= urlencode($tab) ?>" class="admin-btn admin-btn-outline" style="padding:8px 14px;font-size:0.86rem;">
          Reset
        </a>
      <?php endif; ?>
    </div>

    <div style="font-size:0.85rem;color:#64748B;">
      Showing <strong><?= count($enquiries) ?></strong> matching inquiries
    </div>
  </form>
</div>

<!-- Enquiries Table -->
<div class="admin-table-container">
  <table class="admin-table">
    <thead>
      <tr>
        <th>Reference &amp; Type</th>
        <th>Customer &amp; Contact</th>
        <th>Trip / Event Target</th>
        <th>Travel / Event Dates</th>
        <th>Guests / Scope</th>
        <th>Workflow Status</th>
        <th>Submitted</th>
        <th style="text-align:right;">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($enquiries)): ?>
        <tr>
          <td colspan="8" style="text-align:center;padding:48px 20px;color:var(--admin-muted);">
            <div style="font-size:2rem;margin-bottom:8px;">📬</div>
            <div style="font-weight:600;font-size:1rem;color:#0F172A;margin-bottom:4px;">No enquiries found</div>
            <div style="font-size:0.85rem;">No customer records match this tab or filter criteria.</div>
          </td>
        </tr>
      <?php else: ?>
        <?php foreach ($enquiries as $enq): ?>
          <?php
            $rawStatus = $enq['workflow_status'] ?: ($enq['status'] ?? 'new');
            
            // Workflow Badge Styles
            $stMeta = match($rawStatus) {
              'new' => ['label' => 'New Request', 'class' => 'badge-danger', 'bg' => '#FEF2F2', 'color' => '#DC2626'],
              'checking_availability' => ['label' => 'Checking Availability', 'class' => 'badge-warning', 'bg' => '#FFFBEB', 'color' => '#D97706'],
              'availability_confirmed' => ['label' => 'Availability Confirmed', 'class' => 'badge-info', 'bg' => '#EFF6FF', 'color' => '#2563EB'],
              'availability_unavailable' => ['label' => 'Unavailable', 'class' => 'badge-danger', 'bg' => '#FEE2E2', 'color' => '#991B1B'],
              'customer_contacted' => ['label' => 'Customer Contacted', 'class' => 'badge-neutral', 'bg' => '#F1F5F9', 'color' => '#475569'],
              'followup_required' => ['label' => 'Follow-up Needed', 'class' => 'badge-warning', 'bg' => '#FEF3C7', 'color' => '#B45309'],
              'booking_confirmed', 'converted' => ['label' => 'Booking Confirmed', 'class' => 'badge-success', 'bg' => '#ECFDF5', 'color' => '#059669'],
              'cancelled' => ['label' => 'Cancelled', 'class' => 'badge-neutral', 'bg' => '#F1F5F9', 'color' => '#64748B'],
              'rejected' => ['label' => 'Rejected', 'class' => 'badge-danger', 'bg' => '#FEF2F2', 'color' => '#DC2626'],
              default => ['label' => ucwords(str_replace('_', ' ', $rawStatus)), 'class' => 'badge-neutral', 'bg' => '#F1F5F9', 'color' => '#475569']
            };

            $enqType = $enq['enquiry_type'] ?: 'tour_package';
            $typeBadge = match($enqType) {
              'tour_package' => ['label' => 'Package Tour', 'bg' => '#F1F5F9', 'color' => '#334155'],
              'custom_tour' => ['label' => 'Custom Tour', 'bg' => '#FEF3C7', 'color' => '#92400E'],
              'accommodation' => ['label' => 'Accommodation', 'bg' => '#EFF6FF', 'color' => '#1E40AF'],
              'kerala_tourism' => ['label' => 'Kerala Tourism', 'bg' => '#ECFDF5', 'color' => '#065F46'],
              'pilgrimage' => ['label' => 'Pilgrimage', 'bg' => '#FAF5FF', 'color' => '#6B21A8'],
              'corporate_event' => ['label' => 'Corporate Event', 'bg' => '#FDF4FF', 'color' => '#86198F'],
              default => ['label' => 'General', 'bg' => '#F1F5F9', 'color' => '#475569']
            };

            $cleanPhone = preg_replace('/[^0-9]/', '', $enq['phone']);
            $waMsg = "Hello " . $enq['full_name'] . ", greetings from Midhila Voyages! Regarding your booking enquiry (" . $enq['reference_no'] . "), our team has reviewed your requirements and would love to coordinate your arrangements.";
            $waUrl = build_whatsapp_url($cleanPhone, $waMsg);
          ?>
          <tr>
            <td>
              <a href="<?= ADMIN_URL ?>/enquiry-view.php?id=<?= (int)$enq['id'] ?>" style="font-weight:700;color:#0F172A;text-decoration:none;">
                <?= e($enq['reference_no']) ?>
              </a>
              <div style="margin-top:4px;">
                <span style="display:inline-block;padding:2px 8px;border-radius:6px;font-size:0.72rem;font-weight:700;background:<?= $typeBadge['bg'] ?>;color:<?= $typeBadge['color'] ?>;">
                  <?= $typeBadge['label'] ?>
                </span>
                <?php if (!empty($enq['language']) && $enq['language'] !== 'en'): ?>
                  <span style="font-size:0.7rem;font-weight:700;background:#E2E8F0;padding:2px 6px;border-radius:4px;color:#334155;margin-left:4px;">
                    <?= strtoupper(e($enq['language'])) ?>
                  </span>
                <?php endif; ?>
              </div>
            </td>

            <td>
              <div style="font-weight:700;color:#0F172A;"><?= e($enq['full_name']) ?></div>
              <?php if (!empty($enq['corporate_organisation_name'])): ?>
                <div style="font-size:0.78rem;color:#7E22CE;font-weight:600;"><?= e($enq['corporate_organisation_name']) ?></div>
              <?php endif; ?>
              <div style="font-size:0.82rem;color:#64748B;margin-top:2px;display:flex;align-items:center;gap:6px;">
                <a href="tel:<?= e($cleanPhone) ?>" style="color:#0F172A;font-weight:600;"><?= e($enq['phone']) ?></a>
              </div>
              <?php if (!empty($enq['email'])): ?>
                <div style="font-size:0.78rem;color:#94A3B8;"><?= e($enq['email']) ?></div>
              <?php endif; ?>
            </td>

            <td>
              <?php if ($enqType === 'corporate_event'): ?>
                <div style="font-weight:600;color:#0F172A;"><?= e($enq['corporate_event_type'] ?: 'Business Event') ?></div>
                <div style="font-size:0.78rem;color:#7E22CE;"><?= e($enq['corporate_venue_type'] ?: 'Venue Request') ?></div>
              <?php elseif (!empty($enq['accommodation_name'])): ?>
                <div style="font-weight:600;color:#0F172A;"><?= e($enq['accommodation_name']) ?></div>
                <div style="font-size:0.78rem;color:#2563EB;">📍 <?= e($enq['destination_name'] ?: 'Kerala') ?></div>
              <?php elseif (!empty($enq['package_title'])): ?>
                <div style="font-weight:600;color:#0F172A;"><?= e($enq['package_title']) ?></div>
                <div style="font-size:0.78rem;color:var(--admin-gold);"><?= e($enq['destination_name'] ?: 'Curated Circuit') ?></div>
              <?php else: ?>
                <div style="font-weight:600;color:#0F172A;"><?= e($enq['destination_name'] ?: 'Custom Plan') ?></div>
                <div style="font-size:0.78rem;color:#64748B;">Bespoke Route</div>
              <?php endif; ?>
            </td>

            <td style="font-size:0.86rem;white-space:nowrap;">
              <div><?= $enq['departure_date'] ? date('d M Y', strtotime($enq['departure_date'])) : 'Flexible Date' ?></div>
              <?php if (!empty($enq['return_date']) && $enq['return_date'] !== $enq['departure_date']): ?>
                <div style="font-size:0.76rem;color:#64748B;">to <?= date('d M Y', strtotime($enq['return_date'])) ?></div>
              <?php elseif (!empty($enq['duration_days'])): ?>
                <div style="font-size:0.76rem;color:#64748B;"><?= (int)$enq['duration_days'] ?> Days</div>
              <?php endif; ?>
            </td>

            <td style="font-size:0.85rem;">
              <?php if ($enqType === 'corporate_event'): ?>
                <strong><?= (int)($enq['corporate_participants_count'] ?? 0) ?></strong> Delegates
              <?php else: ?>
                <strong><?= (int)$enq['adults_count'] ?></strong> Adults
                <?php if ($enq['children_count'] > 0): ?>
                  , <?= (int)$enq['children_count'] ?> Child
                <?php endif; ?>
                <?php if (!empty($enq['rooms_count'])): ?>
                  <div style="font-size:0.76rem;color:#64748B;"><?= (int)$enq['rooms_count'] ?> Room(s)</div>
                <?php endif; ?>
              <?php endif; ?>
            </td>

            <td>
              <span style="display:inline-block;padding:4px 10px;border-radius:999px;font-size:0.75rem;font-weight:700;background:<?= $stMeta['bg'] ?>;color:<?= $stMeta['color'] ?>;">
                <?= $stMeta['label'] ?>
              </span>
              <?php if (!empty($enq['availability_provider_name'])): ?>
                <div style="font-size:0.72rem;color:#64748B;margin-top:3px;">
                  Chk: <?= e($enq['availability_provider_name']) ?>
                </div>
              <?php endif; ?>
            </td>

            <td style="font-size:0.82rem;color:#64748B;white-space:nowrap;">
              <?= date('d M Y', strtotime($enq['created_at'])) ?><br>
              <span style="font-size:0.75rem;color:#94A3B8;"><?= date('h:i A', strtotime($enq['created_at'])) ?></span>
            </td>

            <td style="text-align:right;white-space:nowrap;">
              <div style="display:inline-flex;gap:6px;align-items:center;">
                <a href="<?= ADMIN_URL ?>/enquiry-view.php?id=<?= (int)$enq['id'] ?>" class="admin-btn admin-btn-primary admin-btn-sm" style="padding:6px 12px;">
                  Review
                </a>
                <a href="<?= e($waUrl) ?>" target="_blank" rel="noopener noreferrer" class="admin-btn admin-btn-sm" style="background:#25D366;color:#FFF;padding:6px 10px;" title="Chat on WhatsApp">
                  WhatsApp
                </a>
                <form method="POST" action="" onsubmit="return confirm('Permanently delete enquiry #<?= e($enq['reference_no']) ?>?');" style="display:inline;">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= (int)$enq['id'] ?>">
                  <button type="submit" class="admin-btn admin-btn-danger admin-btn-sm" style="padding:6px 9px;" title="Delete Record">
                    &times;
                  </button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
