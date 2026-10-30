<?php
/**
 * MIDHILA VOYAGES — Tour Packages Management
 */

require_once __DIR__ . '/includes/admin-init.php';

// Restricted to Super Admin and Travel Manage roles
require_role(['super_admin', 'travel_manage']);

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

$sql = "
    SELECT p.*, d.name AS destination_name, c.name AS category_name
    FROM `packages` p
    JOIN `destinations` d ON p.destination_id = d.id
    JOIN `package_categories` c ON p.category_id = c.id
    ORDER BY p.is_featured DESC, p.display_order ASC, p.id DESC
";
$packages = $db->query($sql)->fetchAll();

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
