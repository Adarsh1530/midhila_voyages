<?php
/**
 * MIDHILA VOYAGES — Destination Editor (Add / Edit)
 */

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/database.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/csrf.php';

require_admin_login();
require_role(['super_admin', 'travel_manage']);

$db = getDB();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$destination = null;

if ($id > 0) {
    $stmt = $db->prepare("SELECT * FROM `destinations` WHERE `id` = ? LIMIT 1");
    $stmt->execute([$id]);
    $destination = $stmt->fetch();
    if (!$destination) {
        set_flash_message('error', 'Destination not found.');
        header('Location: ' . ADMIN_URL . '/destinations.php');
        exit;
    }
}

$adminTitle = ($destination ? 'Edit ' . e($destination['name']) : 'Add Destination') . ' | MIDHILA VOYAGES';
$adminHeading = $destination ? 'Edit Destination' : 'Add New Destination';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token()) {
        $errors[] = 'CSRF validation error. Please submit again.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $slug = slugify($name) ?: ('dest-' . time());
        $category = in_array($_POST['category'] ?? '', ['domestic', 'international']) ? $_POST['category'] : 'domestic';
        $regionState = 'Kerala';
        $country = 'India';
        $tagline = '';
        $description = "Explore the scenic beauty and attractions of {$name}, curated by Midhila Voyages.";
        $highlights = trim($_POST['highlights'] ?? '');
        $bestTime = trim($_POST['best_time_to_visit'] ?? '');
        $lat = !empty($_POST['latitude']) ? (float)$_POST['latitude'] : null;
        $lng = !empty($_POST['longitude']) ? (float)$_POST['longitude'] : null;
        $isFeatured = !empty($_POST['is_featured']) ? 1 : 0;
        $isPilgrimage = !empty($_POST['is_pilgrimage']) ? 1 : 0;
        $isPublished = !empty($_POST['is_published']) ? 1 : 0;
        $altText = trim($_POST['alt_text'] ?? $name);
        $metaTitle = trim($_POST['meta_title'] ?? '');
        $metaDescription = trim($_POST['meta_description'] ?? '');
        $imageUrl = $destination['featured_image'] ?? '';

        if (empty($name)) {
            $errors[] = 'Please enter a Destination name.';
        }

        // Handle image upload if provided (limit: 500 KB with auto-compression)
        if (!empty($_FILES['image_file']['name'])) {
            $uploadResult = upload_image($_FILES['image_file'], 'destinations', 500);
            if ($uploadResult['success']) {
                $imageUrl = $uploadResult['path'];
            } else {
                $errors[] = 'Image upload error: ' . $uploadResult['error'];
            }
        }

        if (empty($imageUrl) && !$destination) {
            $imageUrl = 'https://images.unsplash.com/photo-1593693397690-362cb9666fc2?auto=format&fit=crop&w=1200&q=80';
        }

        if (empty($errors)) {
            try {
                if ($destination) {
                    $updStmt = $db->prepare("
                        UPDATE `destinations` SET
                            `name` = ?, `slug` = ?, `category` = ?, `region_state` = ?, `country` = ?,
                            `tagline` = ?, `description` = ?, `highlights` = ?, `best_time_to_visit` = ?,
                            `featured_image` = ?, `alt_text` = ?, `latitude` = ?, `longitude` = ?,
                            `is_featured` = ?, `is_pilgrimage` = ?, `is_published` = ?,
                            `meta_title` = ?, `meta_description` = ?
                        WHERE `id` = ?
                    ");
                    $updStmt->execute([
                        $name, $slug, $category, $regionState, $country,
                        $tagline, $description, $highlights, $bestTime,
                        $imageUrl ?: $destination['featured_image'], $altText, $lat, $lng,
                        $isFeatured, $isPilgrimage, $isPublished,
                        $metaTitle, $metaDescription, $destination['id']
                    ]);
                    log_activity($_SESSION['admin_user_id'] ?? null, 'edit_destination', "Updated destination '{$name}'");
                    set_flash_message('success', "Destination '{$name}' updated successfully.");
                } else {
                    $insStmt = $db->prepare("
                        INSERT INTO `destinations` (
                            `name`, `slug`, `category`, `region_state`, `country`,
                            `tagline`, `description`, `highlights`, `best_time_to_visit`,
                            `featured_image`, `alt_text`, `latitude`, `longitude`,
                            `is_featured`, `is_pilgrimage`, `is_published`,
                            `meta_title`, `meta_description`
                        ) VALUES (
                            ?, ?, ?, ?, ?,
                            ?, ?, ?, ?,
                            ?, ?, ?, ?,
                            ?, ?, ?,
                            ?, ?
                        )
                    ");
                    $insStmt->execute([
                        $name, $slug, $category, $regionState, $country,
                        $tagline, $description, $highlights, $bestTime,
                        $imageUrl, $altText, $lat, $lng,
                        $isFeatured, $isPilgrimage, $isPublished,
                        $metaTitle, $metaDescription
                    ]);
                    log_activity($_SESSION['admin_user_id'] ?? null, 'add_destination', "Created destination '{$name}'");
                    set_flash_message('success', "Destination '{$name}' added successfully.");
                }
                header('Location: ' . ADMIN_URL . '/destinations.php');
                exit;

            } catch (Exception $e) {
                error_log("Destination save failed: " . $e->getMessage());
                $errors[] = 'Database error: Duplicate slug or query failure. Please check input.';
            }
        }
    }
}

require_once __DIR__ . '/includes/admin-header.php';
?>

<div style="max-width:860px;margin:0 auto;">
  
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
    <div>
      <h3 style="font-size:1.3rem;color:var(--admin-navy);"><?= $destination ? 'Edit Destination' : 'Add New Destination' ?></h3>
      <p style="font-size:0.85rem;color:var(--admin-muted);">Configure destination showcase information, photography, and travel details.</p>
    </div>
    <a href="<?= ADMIN_URL ?>/destinations.php" class="admin-btn admin-btn-outline">&larr; Back to Destinations</a>
  </div>

  <?php if (!empty($errors)): ?>
    <div style="background:#FEF2F2;border-left:4px solid #EF4444;padding:12px 16px;border-radius:6px;color:#991B1B;font-size:0.9rem;margin-bottom:20px;">
      <?php foreach ($errors as $err): ?>
        <div>• <?= e($err) ?></div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <div class="admin-card">
    <form method="POST" action="" enctype="multipart/form-data">
      <?= csrf_field() ?>

      <div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;margin-bottom:20px;">
        <div>
          <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--admin-navy);margin-bottom:6px;">Destination Name *</label>
          <input type="text" name="name" required placeholder="e.g. Kerala Backwaters & Hills" 
                 value="<?= e($_POST['name'] ?? $destination['name'] ?? '') ?>"
                 style="width:100%;padding:10px 14px;border:1px solid var(--admin-border);border-radius:6px;font-size:0.95rem;">
        </div>

        <div>
          <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--admin-navy);margin-bottom:6px;">Category *</label>
          <select name="category" style="width:100%;padding:10px 14px;border:1px solid var(--admin-border);border-radius:6px;font-size:0.95rem;background:#FFF;">
            <option value="" disabled>Select Your Category</option>
            <option value="domestic" <?= (($destination['category'] ?? '') === 'domestic') ? 'selected' : '' ?>>Domestic (Incredible India)</option>
            <option value="international" <?= (($destination['category'] ?? '') === 'international') ? 'selected' : '' ?>>International</option>
          </select>
        </div>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
        <div>
          <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--admin-navy);margin-bottom:6px;">Key Highlights (Comma-separated)</label>
          <input type="text" name="highlights" placeholder="Enter your highlights" 
                 value="<?= e($_POST['highlights'] ?? $destination['highlights'] ?? '') ?>"
                 style="width:100%;padding:10px 14px;border:1px solid var(--admin-border);border-radius:6px;font-size:0.95rem;">
        </div>
        <div>
          <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--admin-navy);margin-bottom:6px;">Best Time to Visit</label>
          <input type="text" name="best_time_to_visit" placeholder="Enter your best time to visit" 
                 value="<?= e($_POST['best_time_to_visit'] ?? $destination['best_time_to_visit'] ?? '') ?>"
                 style="width:100%;padding:10px 14px;border:1px solid var(--admin-border);border-radius:6px;font-size:0.95rem;">
        </div>
      </div>

      <!-- Image Source Selection -->
      <div style="background:var(--admin-bg);padding:18px;border-radius:8px;border:1px solid var(--admin-border);margin-bottom:24px;">
        <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--admin-navy);margin-bottom:8px;">Featured Photography *</label>
        
        <div>
          <input type="file" name="image_file" id="destImageFile" accept="image/jpeg,image/png,image/webp">
          <?php if (!empty($destination['featured_image'])): ?>
            <div style="font-size:0.78rem;color:var(--admin-muted);margin-top:6px;">Current photo: <?= e(basename($destination['featured_image'])) ?> (upload a new file to change)</div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Settings Flags -->
      <div style="display:flex;gap:24px;margin-bottom:28px;flex-wrap:wrap;">
        <label style="display:flex;align-items:center;gap:8px;font-size:0.9rem;font-weight:600;cursor:pointer;">
          <input type="checkbox" name="is_published" value="1" <?= (!isset($destination) || !empty($destination['is_published'])) ? 'checked' : '' ?>>
          <span>Publish Destination</span>
        </label>
        <label style="display:flex;align-items:center;gap:8px;font-size:0.9rem;font-weight:600;cursor:pointer;">
          <input type="checkbox" name="is_featured" value="1" <?= (!empty($destination['is_featured'])) ? 'checked' : '' ?>>
          <span>Feature on Homepage</span>
        </label>
        <label style="display:flex;align-items:center;gap:8px;font-size:0.9rem;font-weight:600;cursor:pointer;">
          <input type="checkbox" name="is_pilgrimage" value="1" <?= (!empty($destination['is_pilgrimage'])) ? 'checked' : '' ?>>
          <span>Pilgrimage Destination</span>
        </label>
      </div>

      <div style="display:flex;gap:14px;align-items:center;">
        <button type="submit" class="admin-btn" style="background:#16A34A;color:#FFFFFF;border:none;font-weight:800;font-size:1.05rem;padding:14px 34px;border-radius:10px;box-shadow:0 4px 14px rgba(22,163,74,0.3);cursor:pointer;display:inline-flex;align-items:center;gap:8px;">
          <span><?= $destination ? 'Save Changes' : 'Save Destination' ?></span>
        </button>
        <a href="<?= ADMIN_URL ?>/destinations.php" class="admin-btn admin-btn-outline" style="padding:13px 22px;font-weight:600;font-size:0.95rem;">Cancel</a>
      </div>

    </form>
  </div>

</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
