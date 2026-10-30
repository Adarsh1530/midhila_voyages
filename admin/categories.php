<?php
/**
 * MIDHILA VOYAGES — Package Categories Management
 */

require_once __DIR__ . '/includes/admin-init.php';

// Restricted to Super Admin and Travel Manage roles
require_role(['super_admin', 'travel_manage']);

// Handle Create / Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf_token()) {
        $catId = (int)($_POST['category_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $slug = slugify(trim($_POST['slug'] ?? '') ?: $name);
        $description = trim($_POST['description'] ?? '');
        $order = (int)($_POST['display_order'] ?? 0);

        if (!empty($name)) {
            try {
                if ($catId > 0) {
                    $stmt = $db->prepare("UPDATE `package_categories` SET `name` = ?, `slug` = ?, `description` = ?, `display_order` = ? WHERE `id` = ?");
                    $stmt->execute([$name, $slug, $description, $order, $catId]);
                    set_flash_message('success', "Category '{$name}' updated.");
                } else {
                    $stmt = $db->prepare("INSERT INTO `package_categories` (`name`, `slug`, `description`, `display_order`) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$name, $slug, $description, $order]);
                    set_flash_message('success', "Category '{$name}' created.");
                }
            } catch (Exception $e) {
                set_flash_message('error', 'Error saving category. Slug might already exist.');
            }
        }
    }
    header('Location: ' . ADMIN_URL . '/categories.php');
    exit;
}

// Handle Delete
if (isset($_GET['delete_id'])) {
    $delId = (int)$_GET['delete_id'];
    try {
        $stmt = $db->prepare("DELETE FROM `package_categories` WHERE `id` = ?");
        $stmt->execute([$delId]);
        set_flash_message('success', 'Category deleted.');
    } catch (Exception $e) {
        set_flash_message('error', 'Cannot delete category with associated tour packages.');
    }
    header('Location: ' . ADMIN_URL . '/categories.php');
    exit;
}

$categories = $db->query("
    SELECT c.*, COUNT(p.id) AS package_count 
    FROM `package_categories` c
    LEFT JOIN `packages` p ON p.category_id = c.id
    GROUP BY c.id
    ORDER BY c.display_order ASC
")->fetchAll();

$editCat = null;
if (isset($_GET['edit_id'])) {
    $editId = (int)$_GET['edit_id'];
    $stmt = $db->prepare("SELECT * FROM `package_categories` WHERE `id` = ? LIMIT 1");
    $stmt->execute([$editId]);
    $editCat = $stmt->fetch();
}

$adminTitle = 'Package Categories | MIDHILA VOYAGES';
$adminHeading = 'Package Categories';
require_once __DIR__ . '/includes/admin-header.php';
?>

<div style="display:grid;grid-template-columns:1fr 1.6fr;gap:28px;">
  
  <!-- Left Column: Add / Edit Form -->
  <div class="admin-card">
    <h3 style="font-size:1.15rem;color:var(--admin-navy);margin-bottom:16px;border-bottom:1px solid var(--admin-border);padding-bottom:8px;">
      <?= $editCat ? 'Edit Category' : 'Create Category' ?>
    </h3>

    <form method="POST" action="<?= ADMIN_URL ?>/categories.php">
      <?= csrf_field() ?>
      <input type="hidden" name="category_id" value="<?= (int)($editCat['id'] ?? 0) ?>">

      <div style="margin-bottom:16px;">
        <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--admin-navy);margin-bottom:6px;">Category Name *</label>
        <input type="text" name="name" required placeholder="e.g. Honeymoon Packages" 
               value="<?= e($editCat['name'] ?? '') ?>"
               style="width:100%;padding:10px 14px;border:1px solid var(--admin-border);border-radius:6px;font-size:0.95rem;">
      </div>

      <div style="margin-bottom:16px;">
        <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--admin-navy);margin-bottom:6px;">Slug (URL)</label>
        <input type="text" name="slug" placeholder="e.g. honeymoon-packages" 
               value="<?= e($editCat['slug'] ?? '') ?>"
               style="width:100%;padding:10px 14px;border:1px solid var(--admin-border);border-radius:6px;font-size:0.95rem;">
      </div>

      <div style="margin-bottom:16px;">
        <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--admin-navy);margin-bottom:6px;">Description</label>
        <textarea name="description" rows="3" placeholder="Brief summary of this category..." 
                  style="width:100%;padding:10px 14px;border:1px solid var(--admin-border);border-radius:6px;font-size:0.95rem;"><?= e($editCat['description'] ?? '') ?></textarea>
      </div>

      <div style="margin-bottom:20px;">
        <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--admin-navy);margin-bottom:6px;">Display Order</label>
        <input type="number" name="display_order" value="<?= (int)($editCat['display_order'] ?? 0) ?>" 
               style="width:100%;padding:10px 14px;border:1px solid var(--admin-border);border-radius:6px;font-size:0.95rem;">
      </div>

      <div style="display:flex;gap:10px;">
        <button type="submit" class="admin-btn admin-btn-primary">
          <span><?= $editCat ? 'Update Category' : 'Save Category' ?></span>
        </button>
        <?php if ($editCat): ?>
          <a href="<?= ADMIN_URL ?>/categories.php" class="admin-btn admin-btn-outline">Cancel</a>
        <?php endif; ?>
      </div>
    </form>
  </div>

  <!-- Right Column: Categories List -->
  <div class="admin-card" style="padding:0;overflow:hidden;">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Order</th>
          <th>Category Name</th>
          <th>Slug</th>
          <th>Packages</th>
          <th style="text-align:right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($categories as $cat): ?>
          <tr>
            <td><?= (int)$cat['display_order'] ?></td>
            <td><strong><?= e($cat['name']) ?></strong></td>
            <td><code><?= e($cat['slug']) ?></code></td>
            <td>
              <span class="badge badge-info"><?= (int)$cat['package_count'] ?> tour(s)</span>
            </td>
            <td style="text-align:right;">
              <div style="display:inline-flex;gap:6px;">
                <a href="<?= ADMIN_URL ?>/categories.php?edit_id=<?= (int)$cat['id'] ?>" class="admin-btn admin-btn-outline admin-btn-sm">Edit</a>
                <a href="<?= ADMIN_URL ?>/categories.php?delete_id=<?= (int)$cat['id'] ?>" 
                   data-confirm-delete="Are you sure you want to delete category '<?= e($cat['name']) ?>'?"
                   class="admin-btn admin-btn-danger admin-btn-sm">Delete</a>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
