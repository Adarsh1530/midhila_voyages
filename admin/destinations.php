<?php
/**
 * MIDHILA VOYAGES — Destination Management
 */

require_once __DIR__ . '/includes/admin-init.php';

// Handle Status Toggle or Deletion
if (isset($_GET['toggle_id'])) {
    $toggleId = (int)$_GET['toggle_id'];
    $stmt = $db->prepare("UPDATE `destinations` SET `is_published` = NOT `is_published` WHERE `id` = ?");
    $stmt->execute([$toggleId]);
    set_flash_message('success', 'Destination publication status updated.');
    header('Location: ' . ADMIN_URL . '/destinations.php');
    exit;
}

if (isset($_GET['delete_id'])) {
    $deleteId = (int)$_GET['delete_id'];
    try {
        $stmt = $db->prepare("DELETE FROM `destinations` WHERE `id` = ?");
        $stmt->execute([$deleteId]);
        set_flash_message('success', 'Destination removed successfully.');
    } catch (Exception $e) {
        set_flash_message('error', 'Cannot delete this destination because packages are linked to it.');
    }
    header('Location: ' . ADMIN_URL . '/destinations.php');
    exit;
}

// Fetch all destinations
$search = trim($_GET['search'] ?? '');
if (!empty($search)) {
    $stmt = $db->prepare("SELECT * FROM `destinations` WHERE `name` LIKE ? OR `region_state` LIKE ? ORDER BY `display_order` ASC");
    $stmt->execute(["%{$search}%", "%{$search}%"]);
} else {
    $stmt = $db->query("SELECT * FROM `destinations` ORDER BY `category` ASC, `display_order` ASC, `name` ASC");
}
$destinations = $stmt->fetchAll();

$adminTitle = 'Manage Destinations | MIDHILA VOYAGES';
$adminHeading = 'Destinations Catalog';
require_once __DIR__ . '/includes/admin-header.php';
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:16px;">
  <div>
    <h3 style="font-size:1.15rem;color:var(--admin-navy);">All Destinations</h3>
    <p style="font-size:0.85rem;color:var(--admin-muted);">Manage domestic and international destinations presented across the website.</p>
  </div>
  <a href="<?= ADMIN_URL ?>/destination-edit.php" class="admin-btn admin-btn-primary">
    <span>+ Add New Destination</span>
  </a>
</div>

<!-- Search Bar -->
<div class="admin-card" style="padding:16px 20px;margin-bottom:24px;">
  <form method="GET" action="<?= ADMIN_URL ?>/destinations.php" style="display:flex;gap:12px;">
    <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search destination name or region..." 
           style="flex-grow:1;padding:8px 14px;border:1px solid var(--admin-border);border-radius:6px;font-size:0.9rem;">
    <button type="submit" class="admin-btn admin-btn-outline">Search</button>
    <?php if (!empty($search)): ?>
      <a href="<?= ADMIN_URL ?>/destinations.php" class="admin-btn admin-btn-outline">Reset</a>
    <?php endif; ?>
  </form>
</div>

<!-- Destinations Table -->
<div class="admin-card" style="padding:0;overflow:hidden;">
  <table class="admin-table">
    <thead>
      <tr>
        <th style="width:70px;">Image</th>
        <th>Destination Name</th>
        <th>Category</th>
        <th>Region / Country</th>
        <th>Featured</th>
        <th>Status</th>
        <th style="text-align:right;">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($destinations)): ?>
        <tr>
          <td colspan="7" style="text-align:center;padding:32px;color:var(--admin-muted);">No destinations found.</td>
        </tr>
      <?php else: ?>
        <?php foreach ($destinations as $dest): ?>
          <tr>
            <td>
              <img src="<?= e(get_image_url($dest['featured_image'])) ?>" alt="" style="width:52px;height:40px;object-fit:cover;border-radius:4px;" onerror="this.onerror=null;this.src='<?= ASSETS_URL ?>/images/placeholder.svg';">
            </td>
            <td>
              <strong><a href="<?= BASE_URL ?>/destination.php?slug=<?= e($dest['slug']) ?>" target="_blank" style="color:var(--admin-navy);"><?= e($dest['name']) ?></a></strong>
              <div style="font-size:0.75rem;color:var(--admin-muted);">/destination/<?= e($dest['slug']) ?></div>
            </td>
            <td>
              <span class="badge <?= ($dest['category'] === 'domestic') ? 'badge-info' : 'badge-gold' ?>" style="text-transform:uppercase;">
                <?= e($dest['category']) ?>
              </span>
            </td>
            <td><?= e($dest['region_state']) ?>, <?= e($dest['country']) ?></td>
            <td>
              <?= !empty($dest['is_featured']) ? '<span style="color:var(--admin-gold);font-weight:700;">★ Featured</span>' : '<span style="color:var(--admin-muted);">-</span>' ?>
            </td>
            <td>
              <a href="<?= ADMIN_URL ?>/destinations.php?toggle_id=<?= (int)$dest['id'] ?>" class="badge <?= !empty($dest['is_published']) ? 'badge-success' : 'badge-danger' ?>" title="Click to toggle">
                <?= !empty($dest['is_published']) ? 'Published' : 'Hidden' ?>
              </a>
            </td>
            <td style="text-align:right;">
              <div style="display:inline-flex;gap:6px;">
                <a href="<?= ADMIN_URL ?>/destination-edit.php?id=<?= (int)$dest['id'] ?>" class="admin-btn admin-btn-outline admin-btn-sm">Edit</a>
                <a href="<?= ADMIN_URL ?>/destinations.php?delete_id=<?= (int)$dest['id'] ?>" 
                   data-confirm-delete="Are you sure you want to delete destination '<?= e($dest['name']) ?>'?"
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
