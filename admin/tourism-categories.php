<?php
/**
 * MIDHILA VOYAGES — Kerala Tourism Categories Management Module
 * Manage the 5 core Kerala tourism categories (Pilgrimage, Hill Stations, Backwaters, Beaches, Ayurveda & Wellness)
 * and create custom tourism disciplines with full multilingual (EN, ML, TA) capability.
 */

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/database.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/csrf.php';

require_admin_login();
require_role(['super_admin', 'travel_manage']);

$db = getDB();

// Handle Edit or Add Category
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf_token()) {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'save_category') {
            $catId = (int)($_POST['id'] ?? 0);
            $slug = slugify($_POST['slug'] ?? $_POST['name_en']);
            $nameEn = trim($_POST['name_en'] ?? '');
            $nameMl = trim($_POST['name_ml'] ?? '');
            $nameTa = trim($_POST['name_ta'] ?? '');
            $taglineEn = trim($_POST['tagline_en'] ?? '');
            $taglineMl = trim($_POST['tagline_ml'] ?? '');
            $taglineTa = trim($_POST['tagline_ta'] ?? '');
            $descEn = trim($_POST['description_en'] ?? '');
            $descMl = trim($_POST['description_ml'] ?? '');
            $descTa = trim($_POST['description_ta'] ?? '');
            $featuredImg = trim($_POST['featured_image'] ?? '');
            $displayOrder = (int)($_POST['display_order'] ?? 0);
            $isKerala = isset($_POST['is_kerala_focus']) ? 1 : 0;
            $isActive = isset($_POST['is_active']) ? 1 : 0;

            if ($catId > 0) {
                $upd = $db->prepare("
                    UPDATE `tourism_categories` SET
                        `name_en` = ?, `name_ml` = ?, `name_ta` = ?,
                        `tagline_en` = ?, `tagline_ml` = ?, `tagline_ta` = ?,
                        `description_en` = ?, `description_ml` = ?, `description_ta` = ?,
                        `featured_image` = ?, `display_order` = ?, `is_kerala_focus` = ?,
                        `is_active` = ?, `updated_at` = ?
                    WHERE `id` = ?
                ");
                $upd->execute([
                    $nameEn, $nameMl, $nameTa,
                    $taglineEn, $taglineMl, $taglineTa,
                    $descEn, $descMl, $descTa,
                    $featuredImg, $displayOrder, $isKerala,
                    $isActive, date('Y-m-d H:i:s'), $catId
                ]);
                set_flash_message('success', 'Tourism category updated.');
            } else {
                $ins = $db->prepare("
                    INSERT INTO `tourism_categories`
                    (`slug`, `name_en`, `name_ml`, `name_ta`,
                     `tagline_en`, `tagline_ml`, `tagline_ta`,
                     `description_en`, `description_ml`, `description_ta`,
                     `featured_image`, `display_order`, `is_kerala_focus`, `is_active`)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $ins->execute([
                    $slug, $nameEn, $nameMl, $nameTa,
                    $taglineEn, $taglineMl, $taglineTa,
                    $descEn, $descMl, $descTa,
                    $featuredImg, $displayOrder, $isKerala, $isActive
                ]);
                set_flash_message('success', 'New tourism category added.');
            }
            header('Location: ' . ADMIN_URL . '/tourism-categories.php');
            exit;
        }

        if ($action === 'delete') {
            $catId = (int)($_POST['id'] ?? 0);
            if ($catId > 0) {
                $db->prepare("DELETE FROM `tourism_categories` WHERE id = ?")->execute([$catId]);
                set_flash_message('success', 'Tourism category deleted.');
                header('Location: ' . ADMIN_URL . '/tourism-categories.php');
                exit;
            }
        }
    }
}

$categories = $db->query("SELECT * FROM `tourism_categories` ORDER BY `display_order` ASC, `id` ASC")->fetchAll();

$editId = (int)($_GET['edit'] ?? 0);
$editCategory = null;
if ($editId > 0) {
    foreach ($categories as $cat) {
        if ((int)$cat['id'] === $editId) {
            $editCategory = $cat;
            break;
        }
    }
}

$adminTitle = 'Kerala Tourism Categories | MIDHILA VOYAGES';
$adminHeading = 'Kerala Tourism Disciplines';

require_once __DIR__ . '/includes/admin-header.php';
?>

<div style="display:grid;grid-template-columns:1.5fr 1fr;gap:28px;">
  
  <!-- Left Column: Categories List -->
  <div>
    <div style="margin-bottom:20px;">
      <h3 style="font-size:1.25rem;color:var(--admin-navy);font-weight:700;">Tourism Categories</h3>
      <p style="font-size:0.86rem;color:var(--admin-muted);margin:0;">The 5 dedicated Kerala tourism pillars and custom categories with multi-language copy.</p>
    </div>

    <div class="admin-table-container">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Discipline / Category</th>
            <th>Malayalam / Tamil</th>
            <th>Focus</th>
            <th>Status</th>
            <th style="text-align:right;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($categories as $cat): ?>
            <tr>
              <td>
                <div style="font-weight:700;color:#0F172A;font-size:0.95rem;"><?= e($cat['name_en']) ?></div>
                <div style="font-size:0.75rem;color:var(--admin-gold);font-weight:600;margin-top:2px;"><?= e($cat['tagline_en']) ?></div>
                <div style="font-size:0.75rem;color:#94A3B8;font-family:monospace;">slug: <?= e($cat['slug']) ?></div>
              </td>
              <td style="font-size:0.84rem;">
                <div style="color:#0F172A;"><?= e($cat['name_ml'] ?: '—') ?></div>
                <div style="color:#64748B;font-size:0.78rem;"><?= e($cat['name_ta'] ?: '—') ?></div>
              </td>
              <td>
                <?= $cat['is_kerala_focus'] ? '<span class="badge badge-success">Kerala Focus</span>' : '<span class="badge badge-neutral">National</span>' ?>
              </td>
              <td>
                <?= $cat['is_active'] ? '<span class="badge badge-info">Active</span>' : '<span class="badge badge-neutral">Inactive</span>' ?>
              </td>
              <td style="text-align:right;white-space:nowrap;">
                <a href="<?= ADMIN_URL ?>/tourism-categories.php?edit=<?= (int)$cat['id'] ?>" class="admin-btn admin-btn-outline admin-btn-sm" style="margin-right:4px;">
                  Edit
                </a>
                <?php if ($cat['slug'] !== 'pilgrimage'): ?>
                  <form method="POST" action="" onsubmit="return confirm('Delete this category?');" style="display:inline;">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= (int)$cat['id'] ?>">
                    <button type="submit" class="admin-btn admin-btn-danger admin-btn-sm">&times;</button>
                  </form>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Right Column: Add / Edit Form -->
  <div>
    <div class="admin-card">
      <h3 style="font-size:1.15rem;color:#0F172A;font-weight:700;margin-bottom:6px;">
        <?= $editCategory ? ('Edit: ' . e($editCategory['name_en'])) : 'Add New Category' ?>
      </h3>
      <p style="font-size:0.82rem;color:#64748B;margin-bottom:20px;">
        Define names, taglines, and descriptions across English, Malayalam, and Tamil.
      </p>

      <form method="POST" action="">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save_category">
        <input type="hidden" name="id" value="<?= (int)($editCategory['id'] ?? 0) ?>">

        <div style="margin-bottom:14px;">
          <label>Category Name (English) *</label>
          <input type="text" name="name_en" required value="<?= e($editCategory['name_en'] ?? '') ?>" placeholder="e.g. Hill Station Tourism">
        </div>

        <?php if (!$editCategory): ?>
          <div style="margin-bottom:14px;">
            <label>Category Slug (e.g. hill-stations) *</label>
            <input type="text" name="slug" required placeholder="e.g. hill-stations">
          </div>
        <?php endif; ?>

        <div style="margin-bottom:14px;">
          <label>Tagline (English)</label>
          <input type="text" name="tagline_en" value="<?= e($editCategory['tagline_en'] ?? '') ?>" placeholder="e.g. Misty Peaks &amp; Tea Plantations">
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
          <div>
            <label>Name (Malayalam / മലയാളം)</label>
            <input type="text" name="name_ml" value="<?= e($editCategory['name_ml'] ?? '') ?>" placeholder="ഹിൽ സ്റ്റേഷൻ ടൂറിസം">
          </div>
          <div>
            <label>Name (Tamil / தமிழ்)</label>
            <input type="text" name="name_ta" value="<?= e($editCategory['name_ta'] ?? '') ?>" placeholder="மலைவாசஸ்தல சுற்றுலா">
          </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
          <div>
            <label>Tagline (Malayalam)</label>
            <input type="text" name="tagline_ml" value="<?= e($editCategory['tagline_ml'] ?? '') ?>" placeholder="മലയോര കുളിർമയും തേയിലത്തോട്ടങ്ങളും">
          </div>
          <div>
            <label>Tagline (Tamil)</label>
            <input type="text" name="tagline_ta" value="<?= e($editCategory['tagline_ta'] ?? '') ?>" placeholder="பனிமூட்டமான மலைகள் மற்றும் தேயிலைத் தோட்டங்கள்">
          </div>
        </div>

        <div style="margin-bottom:14px;">
          <label>Description (English)</label>
          <textarea name="description_en" rows="3" placeholder="Overview of destinations in this category..."><?= e($editCategory['description_en'] ?? '') ?></textarea>
        </div>

        <div style="margin-bottom:14px;">
          <label>Featured Photographic Backdrop URL</label>
          <input type="text" name="featured_image" value="<?= e($editCategory['featured_image'] ?? '') ?>" placeholder="https://images.unsplash.com/...">
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px;">
          <div>
            <label>Display Order</label>
            <input type="number" name="display_order" value="<?= (int)($editCategory['display_order'] ?? 1) ?>" min="0">
          </div>
          <div style="padding-top:24px;">
            <label style="display:inline-flex;align-items:center;gap:6px;cursor:pointer;font-weight:600;">
              <input type="checkbox" name="is_kerala_focus" value="1" <?= (!isset($editCategory['is_kerala_focus']) || $editCategory['is_kerala_focus']) ? 'checked' : '' ?>>
              <span>Kerala Focus</span>
            </label>
          </div>
        </div>

        <div style="margin-bottom:20px;">
          <label style="display:inline-flex;align-items:center;gap:6px;cursor:pointer;font-weight:600;">
            <input type="checkbox" name="is_active" value="1" <?= (!isset($editCategory['is_active']) || $editCategory['is_active']) ? 'checked' : '' ?>>
            <span>Active on Website</span>
          </label>
        </div>

        <div style="display:flex;gap:10px;">
          <button type="submit" class="admin-btn admin-btn-primary">
            <?= $editCategory ? 'Save Changes' : 'Add Category' ?>
          </button>
          <?php if ($editCategory): ?>
            <a href="<?= ADMIN_URL ?>/tourism-categories.php" class="admin-btn admin-btn-outline">Cancel</a>
          <?php endif; ?>
        </div>

      </form>
    </div>
  </div>

</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
