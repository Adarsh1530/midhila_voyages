<?php
/**
 * MIDHILA VOYAGES — Corporate Venues Management Module
 * Manage Conference Halls, Seminar Auditoriums, and Convention Centres.
 */

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/database.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/csrf.php';

require_admin_login();
require_role(['super_admin', 'travel_manage']);

$db = getDB();

// Handle Toggle or Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf_token()) {
        $action = $_POST['action'] ?? '';
        $vId = (int)($_POST['id'] ?? 0);

        if ($action === 'toggle' && $vId > 0) {
            $stmt = $db->prepare("UPDATE `corporate_venues` SET `is_active` = 1 - `is_active` WHERE `id` = ?");
            $stmt->execute([$vId]);
            set_flash_message('success', 'Corporate venue status updated.');
            header('Location: ' . ADMIN_URL . '/corporate-venues.php');
            exit;
        }

        if ($action === 'delete' && $vId > 0) {
            $stmt = $db->prepare("DELETE FROM `corporate_venues` WHERE `id` = ?");
            $stmt->execute([$vId]);
            set_flash_message('success', 'Corporate venue deleted successfully.');
            header('Location: ' . ADMIN_URL . '/corporate-venues.php');
            exit;
        }
    }
}

$typeFilter = trim($_GET['venue_type'] ?? '');
$search = trim($_GET['q'] ?? '');

$where = [];
$params = [];

if ($typeFilter !== '') {
    $where[] = "`venue_type` = ?";
    $params[] = $typeFilter;
}
if ($search !== '') {
    $where[] = "(`name_en` LIKE ? OR `location_address` LIKE ? OR `city` LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

$whereSQL = !empty($where) ? ('WHERE ' . implode(' AND ', $where)) : '';

$stmt = $db->prepare("SELECT * FROM `corporate_venues` {$whereSQL} ORDER BY `id` DESC");
$stmt->execute($params);
$venues = $stmt->fetchAll();

$adminTitle = 'Corporate Venues | MIDHILA VOYAGES';
$adminHeading = 'Business & Corporate Venues';

require_once __DIR__ . '/includes/admin-header.php';
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:16px;">
  <div>
    <h3 style="font-size:1.25rem;color:var(--admin-navy);font-weight:700;">Corporate Event Venues</h3>
    <p style="font-size:0.86rem;color:var(--admin-muted);margin:0;">Manage conference halls, seminar auditoriums, and convention centres.</p>
  </div>
  <a href="<?= ADMIN_URL ?>/corporate-venue-edit.php" class="admin-btn admin-btn-primary">
    <span>+ Register New Venue</span>
  </a>
</div>

<!-- Filter Bar -->
<div class="admin-card" style="padding:16px 20px;margin-bottom:24px;">
  <form method="GET" action="" style="display:flex;flex-wrap:wrap;gap:12px;align-items:center;">
    <div style="position:relative;flex-grow:1;min-width:240px;max-width:340px;">
      <input type="text" name="q" value="<?= e($search) ?>" placeholder="Search venue name, city, location..." style="width:100%;padding:9px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.88rem;">
    </div>

    <select name="venue_type" style="padding:9px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.88rem;background:#FFFFFF;">
      <option value="">All Venue Types</option>
      <option value="conference_hall" <?= ($typeFilter === 'conference_hall') ? 'selected' : '' ?>>Conference Hall</option>
      <option value="seminar_hall" <?= ($typeFilter === 'seminar_hall') ? 'selected' : '' ?>>Seminar Hall</option>
      <option value="convention_centre" <?= ($typeFilter === 'convention_centre') ? 'selected' : '' ?>>Convention Centre</option>
    </select>

    <button type="submit" class="admin-btn admin-btn-primary" style="padding:8px 16px;">Filter</button>
    <?php if ($search || $typeFilter): ?>
      <a href="<?= ADMIN_URL ?>/corporate-venues.php" class="admin-btn admin-btn-outline" style="padding:8px 14px;">Reset</a>
    <?php endif; ?>
  </form>
</div>

<!-- Venues Table -->
<div class="admin-table-container">
  <table class="admin-table">
    <thead>
      <tr>
        <th>Venue &amp; Location</th>
        <th>Category</th>
        <th>Capacities (Theater / Round)</th>
        <th>AV &amp; Facilities</th>
        <th>Base Price</th>
        <th>Status</th>
        <th style="text-align:right;">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($venues)): ?>
        <tr>
          <td colspan="7" style="text-align:center;padding:40px;color:var(--admin-muted);">
            No corporate venues registered yet. Click "Register New Venue" to add one.
          </td>
        </tr>
      <?php else: ?>
        <?php foreach ($venues as $vn): 
          $typeLabel = match($vn['venue_type']) {
            'conference_hall' => 'Conference Hall',
            'seminar_hall' => 'Seminar Hall',
            'convention_centre' => 'Convention Centre',
            default => ucwords(str_replace('_', ' ', $vn['venue_type']))
          };
        ?>
          <tr>
            <td>
              <div style="font-weight:700;color:#0F172A;font-size:0.95rem;"><?= e($vn['name_en']) ?></div>
              <?php if (!empty($vn['name_ml']) || !empty($vn['name_ta'])): ?>
                <div style="font-size:0.75rem;color:#64748B;">
                  <?= e($vn['name_ml'] ?: $vn['name_ta']) ?>
                </div>
              <?php endif; ?>
              <div style="font-size:0.8rem;color:#94A3B8;margin-top:2px;">
                📍 <?= e($vn['city'] ?: 'Kerala') ?> • <?= e($vn['location_address'] ?: '') ?>
              </div>
            </td>
            <td>
              <span class="badge badge-info"><?= e($typeLabel) ?></span>
            </td>
            <td>
              <strong><?= (int)$vn['capacity_theater'] ?></strong> Theater<br>
              <span style="font-size:0.78rem;color:#64748B;"><?= (int)$vn['capacity_round_table'] ?> Round Table</span>
            </td>
            <td style="font-size:0.82rem;color:#475569;max-width:240px;">
              <?= e(truncate_words($vn['av_equipment_specs'] ?? 'Projectors, PA Systems, Microphones', 8)) ?>
            </td>
            <td>
              <strong style="color:#0F172A;">₹<?= number_format($vn['indicative_price'], 2) ?></strong>
              <div style="font-size:0.74rem;color:#64748B;"><?= e($vn['price_unit'] ?: 'per day') ?></div>
            </td>
            <td>
              <form method="POST" action="" style="display:inline;">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="toggle">
                <input type="hidden" name="id" value="<?= (int)$vn['id'] ?>">
                <button type="submit" style="background:none;border:none;cursor:pointer;padding:0;">
                  <?php if ($vn['is_active']): ?>
                    <span class="badge badge-success">Active</span>
                  <?php else: ?>
                    <span class="badge badge-neutral">Inactive</span>
                  <?php endif; ?>
                </button>
              </form>
            </td>
            <td style="text-align:right;white-space:nowrap;">
              <a href="<?= ADMIN_URL ?>/corporate-venue-edit.php?id=<?= (int)$vn['id'] ?>" class="admin-btn admin-btn-outline admin-btn-sm" style="margin-right:4px;">
                Edit
              </a>
              <form method="POST" action="" onsubmit="return confirm('Delete venue <?= e($vn['name_en']) ?>?');" style="display:inline;">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= (int)$vn['id'] ?>">
                <button type="submit" class="admin-btn admin-btn-danger admin-btn-sm">
                  Delete
                </button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
