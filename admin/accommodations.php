<?php
/**
 * MIDHILA VOYAGES — Accommodations Management Module
 * Manage Hotels, Resorts, Houseboats, and Homestays linked by Destination.
 */

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/database.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/csrf.php';

require_admin_login();
require_role(['super_admin', 'travel_manage']);

$db = getDB();

// Handle Toggle Active or Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf_token()) {
        $action = $_POST['action'] ?? '';
        $accId = (int)($_POST['id'] ?? 0);

        if ($action === 'toggle' && $accId > 0) {
            $stmt = $db->prepare("UPDATE `accommodations` SET `is_active` = 1 - `is_active` WHERE `id` = ?");
            $stmt->execute([$accId]);
            set_flash_message('success', 'Accommodation status updated.');
            header('Location: ' . ADMIN_URL . '/accommodations.php');
            exit;
        }

        if ($action === 'delete' && $accId > 0) {
            $stmt = $db->prepare("DELETE FROM `accommodations` WHERE `id` = ?");
            $stmt->execute([$accId]);
            set_flash_message('success', 'Accommodation deleted successfully.');
            header('Location: ' . ADMIN_URL . '/accommodations.php');
            exit;
        }
    }
}

// Filters
$destFilter = (int)($_GET['destination_id'] ?? 0);
$catFilter = trim($_GET['category'] ?? '');
$search = trim($_GET['q'] ?? '');

$where = [];
$params = [];

if ($destFilter > 0) {
    $where[] = "a.`destination_id` = ?";
    $params[] = $destFilter;
}
if ($catFilter !== '') {
    $where[] = "a.`category` = ?";
    $params[] = $catFilter;
}
if ($search !== '') {
    $where[] = "(a.`name_en` LIKE ? OR a.`location_address` LIKE ? OR a.`contact_person` LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

$whereSQL = !empty($where) ? ('WHERE ' . implode(' AND ', $where)) : '';

$accommodations = $db->prepare("
    SELECT a.*, d.name AS destination_name 
    FROM `accommodations` a
    LEFT JOIN `destinations` d ON a.destination_id = d.id
    {$whereSQL}
    ORDER BY a.id DESC
");
$accommodations->execute($params);
$items = $accommodations->fetchAll();

// Fetch Destinations for Filter Dropdown
$destinations = $db->query("SELECT id, name FROM `destinations` WHERE is_published = 1 ORDER BY name ASC")->fetchAll();

$adminTitle = 'Accommodations Management | MIDHILA VOYAGES';
$adminHeading = 'Destination Accommodations';

require_once __DIR__ . '/includes/admin-header.php';
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:16px;">
  <div>
    <h3 style="font-size:1.25rem;color:var(--admin-navy);font-weight:700;">Accommodations Registry</h3>
    <p style="font-size:0.86rem;color:var(--admin-muted);margin:0;">Manage resorts, hotels, and houseboats filtered dynamically by destination.</p>
  </div>
  <a href="<?= ADMIN_URL ?>/accommodation-edit.php" class="admin-btn admin-btn-primary">
    <span>+ Register New Accommodation</span>
  </a>
</div>

<!-- Filter Bar -->
<div class="admin-card" style="padding:16px 20px;margin-bottom:24px;">
  <form method="GET" action="" style="display:flex;flex-wrap:wrap;gap:12px;align-items:center;">
    <div style="position:relative;flex-grow:1;min-width:240px;max-width:340px;">
      <input type="text" name="q" value="<?= e($search) ?>" placeholder="Search property name, address, contact..." style="width:100%;padding:9px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.88rem;">
    </div>

    <select name="destination_id" style="padding:9px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.88rem;background:#FFFFFF;">
      <option value="0">All Destinations</option>
      <?php foreach ($destinations as $dst): ?>
        <option value="<?= (int)$dst['id'] ?>" <?= ($destFilter === (int)$dst['id']) ? 'selected' : '' ?>>
          <?= e($dst['name']) ?>
        </option>
      <?php endforeach; ?>
    </select>

    <select name="category" style="padding:9px 14px;border:1.5px solid #CBD5E1;border-radius:8px;font-size:0.88rem;background:#FFFFFF;">
      <option value="">All Categories</option>
      <option value="Resort" <?= ($catFilter === 'Resort') ? 'selected' : '' ?>>Resort</option>
      <option value="Hotel" <?= ($catFilter === 'Hotel') ? 'selected' : '' ?>>Hotel</option>
      <option value="Premium Houseboat" <?= ($catFilter === 'Premium Houseboat') ? 'selected' : '' ?>>Houseboat</option>
      <option value="Ayurveda Retreat" <?= ($catFilter === 'Ayurveda Retreat') ? 'selected' : '' ?>>Ayurveda Retreat</option>
      <option value="Homestay" <?= ($catFilter === 'Homestay') ? 'selected' : '' ?>>Homestay</option>
    </select>

    <button type="submit" class="admin-btn admin-btn-primary" style="padding:8px 16px;">Filter</button>
    <?php if ($search || $destFilter || $catFilter): ?>
      <a href="<?= ADMIN_URL ?>/accommodations.php" class="admin-btn admin-btn-outline" style="padding:8px 14px;">Reset</a>
    <?php endif; ?>
  </form>
</div>

<!-- Accommodations Table -->
<div class="admin-table-container">
  <table class="admin-table">
    <thead>
      <tr>
        <th>Property &amp; Location</th>
        <th>Category</th>
        <th>Destination</th>
        <th>Room Views &amp; Categories</th>
        <th>Indicative Price</th>
        <th>Status</th>
        <th style="text-align:right;">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($items)): ?>
        <tr>
          <td colspan="7" style="text-align:center;padding:40px;color:var(--admin-muted);">
            No accommodations registered yet. Click "Register New Accommodation" to add one.
          </td>
        </tr>
      <?php else: ?>
        <?php foreach ($items as $acc): ?>
          <tr>
            <td>
              <div style="font-weight:700;color:#0F172A;font-size:0.95rem;"><?= e($acc['name_en']) ?></div>
              <?php if (!empty($acc['name_ml']) || !empty($acc['name_ta'])): ?>
                <div style="font-size:0.75rem;color:#64748B;">
                  <?= e($acc['name_ml'] ?: $acc['name_ta']) ?>
                </div>
              <?php endif; ?>
              <div style="font-size:0.8rem;color:#94A3B8;margin-top:2px;">
                <?= e($acc['location_address'] ?: 'Kerala, India') ?>
              </div>
            </td>
            <td>
              <span class="badge badge-info"><?= e($acc['category']) ?></span>
            </td>
            <td>
              <strong><?= e($acc['destination_name'] ?: 'General Kerala') ?></strong>
            </td>
            <td style="font-size:0.82rem;max-width:240px;">
              <?php if (!empty($acc['room_views'])): ?>
                <div style="color:#059669;font-weight:600;margin-bottom:2px;">
                  Views: <?= e(truncate_words($acc['room_views'], 6)) ?>
                </div>
              <?php endif; ?>
              <div style="color:#64748B;">
                <?= e(truncate_words($acc['room_categories'] ?? 'Standard, Deluxe', 6)) ?>
              </div>
            </td>
            <td>
              <strong style="color:#0F172A;">₹<?= number_format($acc['indicative_price'], 2) ?></strong>
              <div style="font-size:0.74rem;color:#64748B;"><?= e($acc['price_unit'] ?: 'per night') ?></div>
            </td>
            <td>
              <form method="POST" action="" style="display:inline;">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="toggle">
                <input type="hidden" name="id" value="<?= (int)$acc['id'] ?>">
                <button type="submit" style="background:none;border:none;cursor:pointer;padding:0;">
                  <?php if ($acc['is_active']): ?>
                    <span class="badge badge-success">Active</span>
                  <?php else: ?>
                    <span class="badge badge-neutral">Inactive</span>
                  <?php endif; ?>
                </button>
              </form>
            </td>
            <td style="text-align:right;white-space:nowrap;">
              <a href="<?= ADMIN_URL ?>/accommodation-edit.php?id=<?= (int)$acc['id'] ?>" class="admin-btn admin-btn-outline admin-btn-sm" style="margin-right:4px;">
                Edit
              </a>
              <form method="POST" action="" onsubmit="return confirm('Delete accommodation <?= e($acc['name_en']) ?>?');" style="display:inline;">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= (int)$acc['id'] ?>">
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
