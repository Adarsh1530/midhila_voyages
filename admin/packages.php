<?php
/**
 * MIDHILA VOYAGES — Tour Packages Management
 */

require_once __DIR__ . '/includes/admin-init.php';

// Handle Status Toggle or Deletion
if (isset($_GET['toggle_id'])) {
    $toggleId = (int)$_GET['toggle_id'];
    $stmt = $db->prepare("UPDATE `packages` SET `is_published` = NOT `is_published` WHERE `id` = ?");
    $stmt->execute([$toggleId]);
    set_flash_message('success', 'Package publication status toggled.');
    header('Location: ' . ADMIN_URL . '/packages.php');
    exit;
}

if (isset($_GET['delete_id'])) {
    $deleteId = (int)$_GET['delete_id'];
    try {
        $stmt = $db->prepare("DELETE FROM `packages` WHERE `id` = ?");
        $stmt->execute([$deleteId]);
        set_flash_message('success', 'Tour package deleted.');
    } catch (Exception $e) {
        set_flash_message('error', 'Cannot delete package with associated bookings.');
    }
    header('Location: ' . ADMIN_URL . '/packages.php');
    exit;
}

// Fetch destinations and categories for filters
$dests = $db->query("SELECT `id`, `name` FROM `destinations` ORDER BY `name` ASC")->fetchAll();
$cats = $db->query("SELECT `id`, `name` FROM `package_categories` ORDER BY `name` ASC")->fetchAll();

$destFilter = isset($_GET['dest_id']) ? (int)$_GET['dest_id'] : 0;
$catFilter = isset($_GET['cat_id']) ? (int)$_GET['cat_id'] : 0;
$search = trim($_GET['search'] ?? '');

$sql = "
    SELECT p.*, d.name AS destination_name, c.name AS category_name
    FROM `packages` p
    JOIN `destinations` d ON p.destination_id = d.id
    JOIN `package_categories` c ON p.category_id = c.id
    WHERE 1=1
";
$params = [];

if ($destFilter > 0) {
    $sql .= " AND p.destination_id = ?";
    $params[] = $destFilter;
}
if ($catFilter > 0) {
    $sql .= " AND p.category_id = ?";
    $params[] = $catFilter;
}
if (!empty($search)) {
    $sql .= " AND (p.title LIKE ? OR p.short_description LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}
$sql .= " ORDER BY p.is_featured DESC, p.display_order ASC, p.id DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$packages = $stmt->fetchAll();

$adminTitle = 'Manage Tour Packages | MIDHILA VOYAGES';
$adminHeading = 'Tour Packages Catalog';
require_once __DIR__ . '/includes/admin-header.php';
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:16px;">
  <div>
    <h3 style="font-size:1.15rem;color:var(--admin-navy);">All Tour Packages</h3>
    <p style="font-size:0.85rem;color:var(--admin-muted);">Manage itineraries, pricing, inclusions, and publication statuses.</p>
  </div>
  <a href="<?= ADMIN_URL ?>/package-edit.php" class="admin-btn admin-btn-primary">
    <span>+ Create New Package</span>
  </a>
</div>

<!-- Filters -->
<div class="admin-card" style="padding:16px 20px;margin-bottom:24px;">
  <form method="GET" action="<?= ADMIN_URL ?>/packages.php" style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)) auto;gap:12px;align-items:end;">
    <div>
      <label style="font-size:0.75rem;font-weight:700;color:var(--admin-muted);display:block;margin-bottom:4px;">Filter Destination</label>
      <select name="dest_id" style="width:100%;padding:8px 12px;border:1px solid var(--admin-border);border-radius:6px;font-size:0.9rem;">
        <option value="">All Destinations</option>
        <?php foreach ($dests as $d): ?>
          <option value="<?= (int)$d['id'] ?>" <?= ($destFilter == $d['id']) ? 'selected' : '' ?>><?= e($d['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div>
      <label style="font-size:0.75rem;font-weight:700;color:var(--admin-muted);display:block;margin-bottom:4px;">Filter Category</label>
      <select name="cat_id" style="width:100%;padding:8px 12px;border:1px solid var(--admin-border);border-radius:6px;font-size:0.9rem;">
        <option value="">All Categories</option>
        <?php foreach ($cats as $c): ?>
          <option value="<?= (int)$c['id'] ?>" <?= ($catFilter == $c['id']) ? 'selected' : '' ?>><?= e($c['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div>
      <label style="font-size:0.75rem;font-weight:700;color:var(--admin-muted);display:block;margin-bottom:4px;">Keyword Search</label>
      <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search package title..." style="width:100%;padding:8px 12px;border:1px solid var(--admin-border);border-radius:6px;font-size:0.9rem;">
    </div>

    <div style="display:flex;gap:6px;">
      <button type="submit" class="admin-btn admin-btn-outline">Filter</button>
      <a href="<?= ADMIN_URL ?>/packages.php" class="admin-btn admin-btn-outline">Reset</a>
    </div>
  </form>
</div>

<!-- Packages Table -->
<div class="admin-card" style="padding:0;overflow:hidden;">
  <table class="admin-table">
    <thead>
      <tr>
        <th style="width:70px;">Image</th>
        <th>Package Title</th>
        <th>Destination</th>
        <th>Category</th>
        <th>Duration</th>
        <th>Starting Price</th>
        <th>Status</th>
        <th style="text-align:right;">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($packages)): ?>
        <tr>
          <td colspan="8" style="text-align:center;padding:32px;color:var(--admin-muted);">No tour packages found matching criteria.</td>
        </tr>
      <?php else: ?>
        <?php foreach ($packages as $pkg): ?>
          <tr>
            <td>
              <img src="<?= e(get_image_url($pkg['featured_image'])) ?>" alt="" style="width:52px;height:40px;object-fit:cover;border-radius:4px;" onerror="this.onerror=null;this.src='<?= ASSETS_URL ?>/images/placeholder.svg';">
            </td>
            <td>
              <strong><a href="<?= BASE_URL ?>/package.php?slug=<?= e($pkg['slug']) ?>" target="_blank" style="color:var(--admin-navy);"><?= e($pkg['title']) ?></a></strong>
              <div style="font-size:0.75rem;color:var(--admin-muted);">/package/<?= e($pkg['slug']) ?></div>
            </td>
            <td><?= e($pkg['destination_name']) ?></td>
            <td><span class="badge badge-neutral"><?= e($pkg['category_name']) ?></span></td>
            <td><?= (int)$pkg['duration_days'] ?>D / <?= (int)$pkg['duration_nights'] ?>N</td>
            <td>
              <?php if (empty($pkg['is_price_on_request']) && !empty($pkg['starting_price'])): ?>
                <strong><?= format_price((float)$pkg['starting_price']) ?></strong>
              <?php else: ?>
                <span style="color:var(--admin-gold);font-weight:600;">On Request</span>
              <?php endif; ?>
            </td>
            <td>
              <a href="<?= ADMIN_URL ?>/packages.php?toggle_id=<?= (int)$pkg['id'] ?>" class="badge <?= !empty($pkg['is_published']) ? 'badge-success' : 'badge-danger' ?>" title="Click to toggle">
                <?= !empty($pkg['is_published']) ? 'Published' : 'Draft' ?>
              </a>
            </td>
            <td style="text-align:right;">
              <div style="display:inline-flex;gap:6px;">
                <a href="<?= ADMIN_URL ?>/package-edit.php?id=<?= (int)$pkg['id'] ?>" class="admin-btn admin-btn-outline admin-btn-sm">Edit</a>
                <a href="<?= ADMIN_URL ?>/packages.php?delete_id=<?= (int)$pkg['id'] ?>" 
                   data-confirm-delete="Are you sure you want to delete tour package '<?= e($pkg['title']) ?>'?"
                   class="admin-btn admin-btn-danger admin-btn-sm">Delete</a>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
