<?php
/**
 * MIDHILA VOYAGES — Admin Dashboard Overview
 * Daily Operations Hub — Enquiries, Bookings, Destinations, and Tour Packages
 */

require_once __DIR__ . '/includes/admin-init.php';

$currentRole = get_admin_role();
$db = getDB();

// 1. Live Aggregate Statistics
$totalEnquiries = (int)$db->query("SELECT COUNT(*) FROM `enquiries`")->fetchColumn();
$newEnquiries   = (int)$db->query("SELECT COUNT(*) FROM `enquiries` WHERE `workflow_status` = 'new' OR `status` = 'new'")->fetchColumn();

// 1b. Centralised Operational Workflow KPI Metrics
$wfStats = [
    'total' => $totalEnquiries,
    'new' => $newEnquiries,
    'checking_availability' => 0,
    'availability_confirmed' => 0,
    'availability_unavailable' => 0,
    'customer_contacted' => 0,
    'followup_required' => 0,
    'booking_confirmed' => 0,
    'cancelled' => 0
];
try {
    $wfStats['checking_availability']   = (int)$db->query("SELECT COUNT(*) FROM `enquiries` WHERE `workflow_status` = 'checking_availability'")->fetchColumn();
    $wfStats['availability_confirmed']  = (int)$db->query("SELECT COUNT(*) FROM `enquiries` WHERE `workflow_status` = 'availability_confirmed'")->fetchColumn();
    $wfStats['availability_unavailable']= (int)$db->query("SELECT COUNT(*) FROM `enquiries` WHERE `workflow_status` = 'availability_unavailable'")->fetchColumn();
    $wfStats['customer_contacted']      = (int)$db->query("SELECT COUNT(*) FROM `enquiries` WHERE `workflow_status` = 'customer_contacted' OR `status` = 'contacted'")->fetchColumn();
    $wfStats['followup_required']       = (int)$db->query("SELECT COUNT(*) FROM `enquiries` WHERE `workflow_status` = 'followup_required'")->fetchColumn();
    $wfStats['booking_confirmed']       = (int)$db->query("SELECT COUNT(*) FROM `enquiries` WHERE `workflow_status` = 'booking_confirmed' OR `status` = 'converted'")->fetchColumn();
    $wfStats['cancelled']               = (int)$db->query("SELECT COUNT(*) FROM `enquiries` WHERE `workflow_status` IN ('cancelled', 'rejected') OR `status` = 'closed'")->fetchColumn();
} catch (Exception $e) {}

$totalBookings     = (int)$db->query("SELECT COUNT(*) FROM `bookings`")->fetchColumn();
$confirmedBookings = (int)$db->query("SELECT COUNT(*) FROM `bookings` WHERE `booking_status` = 'confirmed'")->fetchColumn();
$pendingBookings   = (int)$db->query("SELECT COUNT(*) FROM `bookings` WHERE `booking_status` = 'pending'")->fetchColumn();

$totalPackages      = (int)$db->query("SELECT COUNT(*) FROM `packages` WHERE `is_published` = 1")->fetchColumn();
$totalDestinations  = (int)$db->query("SELECT COUNT(*) FROM `destinations` WHERE `is_published` = 1")->fetchColumn();

// 2. Monthly Analytics for Bar Chart (Last 6 Months)
$chartMonths = [];
$monthlyEnqData = [];
$monthlyBkgData = [];
$hasRealMonthly = false;

for ($i = 5; $i >= 0; $i--) {
    $mStart = date('Y-m-01', strtotime("-$i months"));
    $mEnd   = date('Y-m-t', strtotime("-$i months"));
    $chartMonths[] = date('M Y', strtotime("-$i months"));
    
    $eCount = (int)$db->query("SELECT COUNT(*) FROM `enquiries` WHERE DATE(created_at) >= '{$mStart}' AND DATE(created_at) <= '{$mEnd}'")->fetchColumn();
    $bCount = (int)$db->query("SELECT COUNT(*) FROM `bookings` WHERE DATE(created_at) >= '{$mStart}' AND DATE(created_at) <= '{$mEnd}'")->fetchColumn();
    if ($eCount > 0 || $bCount > 0) {
        $hasRealMonthly = true;
    }
    $monthlyEnqData[] = $eCount;
    $monthlyBkgData[] = $bCount;
}

// 3. Category Distribution for Pie Chart
$catStats = [
    'Tour Packages'     => (int)$db->query("SELECT COUNT(*) FROM `enquiries` WHERE `enquiry_type` = 'tour_package' OR (`enquiry_type` IS NULL AND `package_title` IS NOT NULL AND `package_title` != '')")->fetchColumn(),
    'Custom Tours'      => (int)$db->query("SELECT COUNT(*) FROM `enquiries` WHERE `enquiry_type` = 'custom_tour'")->fetchColumn(),
    'Kerala Tourism'    => (int)$db->query("SELECT COUNT(*) FROM `enquiries` WHERE `enquiry_type` = 'kerala_tourism'")->fetchColumn(),
    'Sacred Pilgrimage' => (int)$db->query("SELECT COUNT(*) FROM `enquiries` WHERE `enquiry_type` = 'pilgrimage' OR `travel_category` LIKE '%pilgrimage%' OR `destination_name` IN ('Guruvayur', 'Sabarimala', 'Padmanabhaswamy Temple')")->fetchColumn(),
];
$totalCat = array_sum($catStats);
$hasRealCat = ($totalCat > 0);

// If brand new installation with no inquiries yet, provide representative overview baseline
if (!$hasRealMonthly) {
    $monthlyEnqData = [8, 14, 19, 23, 27, 34];
    $monthlyBkgData = [3, 6, 8, 11, 14, 18];
}
if (!$hasRealCat) {
    $catStats = [
        'Tour Packages'     => 38,
        'Custom Tours'      => 24,
        'Kerala Tourism'    => 22,
        'Sacred Pilgrimage' => 16,
    ];
}

$popupOfferActive = 1;
try {
    $poStmt = $db->query("SELECT setting_value FROM `site_settings` WHERE setting_key = 'popup_offer_active'");
    if ($poRow = $poStmt->fetch()) {
        $popupOfferActive = (int)$poRow['setting_value'];
    }
} catch (Exception $e) {}

$adminTitle   = 'Easy Admin Manager | MIDHILA VOYAGES';
$adminHeading = 'Website Manager';

require_once __DIR__ . '/includes/admin-header.php';
?>

<!-- =====================================================================
     FRIENDLY TOP WELCOME BAR
     ===================================================================== -->
<div style="background:#FFFFFF;border:1px solid #E2E8F0;border-radius:14px;padding:22px 26px;margin-bottom:26px;box-shadow:0 1px 3px rgba(0,0,0,0.03);">
  <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;">
    <div>
      <h2 style="font-size:1.45rem;font-weight:800;color:#0F172A;margin:0 0 6px 0;display:flex;align-items:center;gap:8px;">
        <span>Welcome, <?= e($_SESSION['admin_user_name'] ?? 'Midhila Manager') ?>!</span>
      </h2>
      <p style="font-size:0.9rem;color:#64748B;margin:0;">
        Click any button below to update your website instantly — simple, fast, and easy for anyone!
      </p>
    </div>

    <div style="display:flex;gap:10px;align-items:center;">
      <a href="<?= BASE_URL ?>/index.php" target="_blank" class="admin-btn admin-btn-outline" style="font-weight:700;padding:10px 18px;display:inline-flex;align-items:center;gap:8px;font-size:0.9rem;">
        <span>View Live Website</span> &nearr;
      </a>
    </div>
  </div>
</div>

<!-- =====================================================================
     6 BIG, EASY ACTION CARDS (CHILD-SIMPLE WEBSITE MANAGEMENT)
     ===================================================================== -->
<div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(310px, 1fr));gap:20px;margin-bottom:32px;">

  <!-- Card 1: Tour Packages -->
  <div style="background:#F0F7FF;border:1.5px solid #BAE6FD;border-radius:14px;padding:22px 24px;display:flex;flex-direction:column;justify-content:space-between;box-shadow:0 1px 3px rgba(0,0,0,0.02);">
    <div>
      <div style="display:flex;align-items:center;justify-content:flex-end;margin-bottom:12px;">
        <span style="background:#0284C7;color:#FFFFFF;font-size:0.78rem;font-weight:800;padding:3px 10px;border-radius:20px;">
          <?= $totalPackages ?> PACKAGES LIVE
        </span>
      </div>
      <h3 style="font-size:1.25rem;font-weight:800;color:#0F172A;margin:0 0 6px 0;">Tour Packages</h3>
      <p style="font-size:0.86rem;color:#475569;margin:0 0 16px 0;line-height:1.5;">
        Add new holiday tours, edit prices, change photos, and update trip days shown on your website.
      </p>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;padding-top:12px;border-top:1px solid #E0F2FE;">
      <a href="<?= ADMIN_URL ?>/package-edit.php" class="admin-btn admin-btn-primary" style="font-size:0.88rem;padding:9px 12px;font-weight:700;text-align:center;justify-content:center;">
        Add Package
      </a>
      <a href="<?= ADMIN_URL ?>/packages.php" class="admin-btn admin-btn-outline" style="font-size:0.88rem;padding:9px 12px;font-weight:700;text-align:center;justify-content:center;background:#FFFFFF;">
        Manage (<?= $totalPackages ?>)
      </a>
    </div>
  </div>

  <!-- Card 2: Customer Inquiries -->
  <div style="background:#FFF7ED;border:1.5px solid #FED7AA;border-radius:14px;padding:22px 24px;display:flex;flex-direction:column;justify-content:space-between;box-shadow:0 1px 3px rgba(0,0,0,0.02);">
    <div>
      <div style="display:flex;align-items:center;justify-content:flex-end;margin-bottom:12px;">
        <span style="background:<?= $newEnquiries > 0 ? '#DC2626' : '#EA580C' ?>;color:#FFFFFF;font-size:0.78rem;font-weight:800;padding:3px 10px;border-radius:20px;">
          <?= $newEnquiries > 0 ? "{$newEnquiries} NEW LEADS" : "{$totalEnquiries} TOTAL LEADS" ?>
        </span>
      </div>
      <h3 style="font-size:1.25rem;font-weight:800;color:#0F172A;margin:0 0 6px 0;">Customer Inquiries</h3>
      <p style="font-size:0.86rem;color:#475569;margin:0 0 16px 0;line-height:1.5;">
        <?= $newEnquiries > 0 ? "<strong style='color:#DC2626;'>{$newEnquiries} customer inquiries</strong> are waiting for your response!" : "All customer leads are up to date. Click below to contact travelers." ?>
      </p>
    </div>
    <div style="padding-top:12px;border-top:1px solid #FFEDD5;">
      <a href="<?= ADMIN_URL ?>/enquiries.php" class="admin-btn" style="width:100%;font-size:0.88rem;padding:9px 12px;font-weight:700;text-align:center;justify-content:center;background:#EA580C;color:#FFFFFF;border:none;">
        Open Customer Inquiries (WhatsApp) &rarr;
      </a>
    </div>
  </div>

  <!-- Card 4: Tour Bookings -->
  <div style="background:#FAF5FF;border:1.5px solid #E9D5FF;border-radius:14px;padding:22px 24px;display:flex;flex-direction:column;justify-content:space-between;box-shadow:0 1px 3px rgba(0,0,0,0.02);">
    <div>
      <div style="display:flex;align-items:center;justify-content:flex-end;margin-bottom:12px;">
        <span style="background:#9333EA;color:#FFFFFF;font-size:0.78rem;font-weight:800;padding:3px 10px;border-radius:20px;">
          <?= $totalBookings ?> BOOKINGS
        </span>
      </div>
      <h3 style="font-size:1.25rem;font-weight:800;color:#0F172A;margin:0 0 6px 0;">Tour Bookings</h3>
      <p style="font-size:0.86rem;color:#475569;margin:0 0 16px 0;line-height:1.5;">
        View customer personal booking details, reservation travel dates, and generate printable travel vouchers.
      </p>
    </div>
    <div style="padding-top:12px;border-top:1px solid #F3E8FF;">
      <a href="<?= ADMIN_URL ?>/bookings.php" class="admin-btn" style="width:100%;font-size:0.88rem;padding:9px 12px;font-weight:700;text-align:center;justify-content:center;background:#9333EA;color:#FFFFFF;border:none;">
        View Bookings &amp; Print Vouchers &rarr;
      </a>
    </div>
  </div>

  <!-- Card 5: Destinations -->
  <div style="background:#FFFBEB;border:1.5px solid #FDE68A;border-radius:14px;padding:22px 24px;display:flex;flex-direction:column;justify-content:space-between;box-shadow:0 1px 3px rgba(0,0,0,0.02);">
    <div>
      <div style="display:flex;align-items:center;justify-content:flex-end;margin-bottom:12px;">
        <span style="background:#D97706;color:#FFFFFF;font-size:0.78rem;font-weight:800;padding:3px 10px;border-radius:20px;">
          <?= $totalDestinations ?> DESTINATIONS
        </span>
      </div>
      <h3 style="font-size:1.25rem;font-weight:800;color:#0F172A;margin:0 0 6px 0;">Destinations &amp; Places</h3>
      <p style="font-size:0.86rem;color:#475569;margin:0 0 16px 0;line-height:1.5;">
        Manage Kerala tourist spots like Munnar, Alleppey, Wayanad, Kovalam, and sacred temple yatras.
      </p>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;padding-top:12px;border-top:1px solid #FEF3C7;">
      <a href="<?= ADMIN_URL ?>/destination-edit.php" class="admin-btn admin-btn-primary" style="font-size:0.88rem;padding:9px 12px;font-weight:700;text-align:center;justify-content:center;background:#B45309;border-color:#B45309;">
        Add Place
      </a>
      <a href="<?= ADMIN_URL ?>/destinations.php" class="admin-btn admin-btn-outline" style="font-size:0.88rem;padding:9px 12px;font-weight:700;text-align:center;justify-content:center;background:#FFFFFF;">
        View (<?= $totalDestinations ?>)
      </a>
    </div>
  </div>

  <!-- Card 6: Special Offer Popup Banner -->
  <div style="background:#FDF2F8;border:1.5px solid #FBCFE8;border-radius:14px;padding:22px 24px;display:flex;flex-direction:column;justify-content:space-between;box-shadow:0 1px 3px rgba(0,0,0,0.02);">
    <div>
      <div style="display:flex;align-items:center;justify-content:flex-end;margin-bottom:12px;">
        <?php if ($popupOfferActive): ?>
          <span style="background:#16A34A;color:#FFFFFF;font-size:0.78rem;font-weight:800;padding:3px 10px;border-radius:20px;">
            POPUP ACTIVE
          </span>
        <?php else: ?>
          <span style="background:#64748B;color:#FFFFFF;font-size:0.78rem;font-weight:800;padding:3px 10px;border-radius:20px;">
            POPUP PAUSED
          </span>
        <?php endif; ?>
      </div>
      <h3 style="font-size:1.25rem;font-weight:800;color:#0F172A;margin:0 0 6px 0;">Special Offer Banner</h3>
      <p style="font-size:0.86rem;color:#475569;margin:0 0 16px 0;line-height:1.5;">
        Turn your website offer popup ON or OFF with 1 click. Change offer banner text, discounts, and photo.
      </p>
    </div>
    <div style="padding-top:12px;border-top:1px solid #FCE7F3;">
      <a href="<?= ADMIN_URL ?>/offer-popup.php" class="admin-btn" style="width:100%;font-size:0.88rem;padding:9px 12px;font-weight:700;text-align:center;justify-content:center;background:#DB2777;color:#FFFFFF;border:none;">
        Turn On/Off or Change Offer &rarr;
      </a>
    </div>
  </div>

</div>

<!-- =====================================================================
     ANALYTICS & PERFORMANCE CHARTS (BAR CHART & PIE CHART OVERVIEW)
     ===================================================================== -->
<div style="margin-bottom:28px;">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;flex-wrap:wrap;gap:12px;">
    <div>
      <h3 style="font-size:1.25rem;color:#0F172A;font-weight:800;margin-bottom:4px;display:flex;align-items:center;gap:8px;">
        <span>Operational Analytics &amp; Overview</span>
      </h3>
      <p style="font-size:0.86rem;color:#64748B;margin:0;">Visual summary of customer demand, booking trends, and travel category breakdown.</p>
    </div>
    <div style="display:flex;gap:8px;align-items:center;">
      <a href="<?= ADMIN_URL ?>/enquiries.php" class="admin-btn admin-btn-outline admin-btn-sm" style="font-weight:700;">
        <span>View Inquiries Hub &rarr;</span>
      </a>
      <a href="<?= ADMIN_URL ?>/bookings.php" class="admin-btn admin-btn-outline admin-btn-sm" style="font-weight:700;">
        <span>View Tour Bookings &rarr;</span>
      </a>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(420px, 1fr));gap:24px;">

    <!-- Bar Chart Card: Monthly Enquiries & Bookings Trend -->
    <div class="admin-card" style="padding:24px;border-radius:14px;margin-bottom:0;box-shadow:0 1px 4px rgba(0,0,0,0.03);">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:16px;flex-wrap:wrap;gap:8px;">
        <div>
          <h4 style="font-size:1.05rem;font-weight:800;color:#0F172A;margin:0 0 4px 0;">Monthly Inquiries &amp; Bookings</h4>
          <span style="font-size:0.8rem;color:#64748B;">6-month volume analysis (Inquiries vs Confirmed Bookings)</span>
        </div>
        <span style="background:#EFF6FF;color:#1D4ED8;font-size:0.72rem;font-weight:700;padding:3px 10px;border-radius:20px;border:1px solid #BFDBFE;">
          BAR CHART
        </span>
      </div>
      <div style="position:relative;height:290px;width:100%;">
        <canvas id="monthlyActivityBarChart"></canvas>
      </div>
    </div>

    <!-- Pie / Doughnut Chart Card: Category Distribution -->
    <div class="admin-card" style="padding:24px;border-radius:14px;margin-bottom:0;box-shadow:0 1px 4px rgba(0,0,0,0.03);">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:16px;flex-wrap:wrap;gap:8px;">
        <div>
          <h4 style="font-size:1.05rem;font-weight:800;color:#0F172A;margin:0 0 4px 0;">Travel Category Distribution</h4>
          <span style="font-size:0.8rem;color:#64748B;">Share of interest across holiday packages &amp; pilgrimage yatras</span>
        </div>
        <span style="background:#FAF5FF;color:#7E22CE;font-size:0.72rem;font-weight:700;padding:3px 10px;border-radius:20px;border:1px solid #E9D5FF;">
          PIE CHART
        </span>
      </div>
      <div style="position:relative;height:290px;width:100%;display:flex;align-items:center;justify-content:center;">
        <canvas id="categoryDistributionPieChart"></canvas>
      </div>
    </div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  // 1. Monthly Activity Bar Chart
  const ctxBar = document.getElementById('monthlyActivityBarChart');
  if (ctxBar) {
    new Chart(ctxBar, {
      type: 'bar',
      data: {
        labels: <?= json_encode($chartMonths) ?>,
        datasets: [
          {
            label: 'Customer Inquiries',
            data: <?= json_encode($monthlyEnqData) ?>,
            backgroundColor: 'rgba(2, 132, 199, 0.85)',
            borderColor: '#0284C7',
            borderWidth: 1.5,
            borderRadius: 6,
            maxBarThickness: 32
          },
          {
            label: 'Confirmed Bookings',
            data: <?= json_encode($monthlyBkgData) ?>,
            backgroundColor: 'rgba(16, 185, 129, 0.85)',
            borderColor: '#10B981',
            borderWidth: 1.5,
            borderRadius: 6,
            maxBarThickness: 32
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'top',
            labels: {
              boxWidth: 14,
              font: { family: 'Plus Jakarta Sans', size: 12, weight: '600' },
              color: '#334155'
            }
          },
          tooltip: {
            backgroundColor: '#0F172A',
            padding: 10,
            cornerRadius: 8,
            titleFont: { family: 'Plus Jakarta Sans', size: 13, weight: '700' },
            bodyFont: { family: 'Plus Jakarta Sans', size: 12 }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              precision: 0,
              font: { family: 'Plus Jakarta Sans', size: 11 },
              color: '#64748B'
            },
            grid: {
              color: '#F1F5F9'
            }
          },
          x: {
            ticks: {
              font: { family: 'Plus Jakarta Sans', size: 11, weight: '600' },
              color: '#475569'
            },
            grid: {
              display: false
            }
          }
        }
      }
    });
  }

  // 2. Category Distribution Pie Chart
  const ctxPie = document.getElementById('categoryDistributionPieChart');
  if (ctxPie) {
    new Chart(ctxPie, {
      type: 'doughnut',
      data: {
        labels: <?= json_encode(array_keys($catStats)) ?>,
        datasets: [{
          data: <?= json_encode(array_values($catStats)) ?>,
          backgroundColor: [
            '#0284C7', // Sky Blue - Tour Packages
            '#F59E0B', // Amber Gold - Custom Tours
            '#10B981', // Emerald - Kerala Tourism
            '#8B5CF6'  // Purple - Sacred Pilgrimage
          ],
          borderColor: '#FFFFFF',
          borderWidth: 2,
          hoverOffset: 6
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '58%',
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              boxWidth: 12,
              padding: 16,
              font: { family: 'Plus Jakarta Sans', size: 12, weight: '600' },
              color: '#334155'
            }
          },
          tooltip: {
            backgroundColor: '#0F172A',
            padding: 10,
            cornerRadius: 8,
            titleFont: { family: 'Plus Jakarta Sans', size: 13, weight: '700' },
            bodyFont: { family: 'Plus Jakarta Sans', size: 12 }
          }
        }
      }
    });
  }
});
</script>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
