<?php
/**
 * MIDHILA VOYAGES — Gallery Management (Add / Edit / Delete)
 */

require_once __DIR__ . '/includes/admin-init.php';

// Restricted to Super Admin and Travel Manage roles
require_role(['super_admin', 'travel_manage']);

// Handle Edit Mode Fetch
$editId = isset($_GET['edit_id']) ? (int)$_GET['edit_id'] : 0;
$editPhoto = null;
if ($editId > 0) {
    $editStmt = $db->prepare("SELECT * FROM `gallery_images` WHERE `id` = ? LIMIT 1");
    $editStmt->execute([$editId]);
    $editPhoto = $editStmt->fetch();
}

// Handle Update Photo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_photo'])) {
    if (verify_csrf_token()) {
        $photoId = (int)($_POST['photo_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $category = trim($_POST['category'] ?? 'destinations');
        $location = trim($_POST['location_name'] ?? '');
        $altText = trim($_POST['alt_text'] ?? $title);
        $imageUrl = trim($_POST['image_url'] ?? '');

        // Fetch current photo
        $curStmt = $db->prepare("SELECT `image_url` FROM `gallery_images` WHERE `id` = ?");
        $curStmt->execute([$photoId]);
        $curPhoto = $curStmt->fetch();

        if ($curPhoto) {
            if (!empty($_FILES['image_file']['name'])) {
                $res = upload_image($_FILES['image_file'], 'gallery', 5);
                if ($res['success']) {
                    $imageUrl = $res['path'];
                } else {
                    set_flash_message('error', $res['error']);
                }
            }

            $finalImage = $imageUrl ?: $curPhoto['image_url'];

            if (!empty($title) && !empty($finalImage)) {
                $updStmt = $db->prepare("UPDATE `gallery_images` SET `title` = ?, `category` = ?, `image_url` = ?, `alt_text` = ?, `location_name` = ? WHERE `id` = ?");
                $updStmt->execute([$title, $category, $finalImage, $altText, $location, $photoId]);
                log_activity($_SESSION['admin_user_id'] ?? null, 'edit_gallery_photo', "Updated gallery photo '{$title}'");
                set_flash_message('success', "Photo '{$title}' updated successfully.");
                header('Location: ' . ADMIN_URL . '/gallery.php');
                exit;
            } else {
                set_flash_message('error', 'Title and image are required.');
            }
        }
    } else {
        set_flash_message('error', 'CSRF validation failed.');
    }
}

// Handle Add Photo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_photo'])) {
    if (verify_csrf_token()) {
        $title = trim($_POST['title'] ?? '');
        $category = trim($_POST['category'] ?? 'destinations');
        $location = trim($_POST['location_name'] ?? '');
        $altText = trim($_POST['alt_text'] ?? $title);
        $imageUrl = trim($_POST['image_url'] ?? '');

        if (!empty($_FILES['image_file']['name'])) {
            $res = upload_image($_FILES['image_file'], 'gallery', 5);
            if ($res['success']) {
                $imageUrl = $res['path'];
            } else {
                set_flash_message('error', $res['error']);
            }
        }

        if (!empty($title) && !empty($imageUrl)) {
            $stmt = $db->prepare("INSERT INTO `gallery_images` (`title`, `category`, `image_url`, `alt_text`, `location_name`) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$title, $category, $imageUrl, $altText, $location]);
            log_activity($_SESSION['admin_user_id'] ?? null, 'add_gallery_photo', "Added gallery photo '{$title}'");
            set_flash_message('success', 'Photo added to gallery.');
        } else {
            set_flash_message('error', 'Please provide a photo title and image.');
        }
    } else {
        set_flash_message('error', 'CSRF validation failed.');
    }
    header('Location: ' . ADMIN_URL . '/gallery.php');
    exit;
}

// Handle Delete Photo
if (isset($_GET['delete_id'])) {
    $delId = (int)$_GET['delete_id'];
    $stmt = $db->prepare("DELETE FROM `gallery_images` WHERE `id` = ?");
    $stmt->execute([$delId]);
    log_activity($_SESSION['admin_user_id'] ?? null, 'delete_gallery_photo', "Deleted gallery photo #{$delId}");
    set_flash_message('success', 'Photo deleted from gallery.');
    header('Location: ' . ADMIN_URL . '/gallery.php');
    exit;
}

$photos = $db->query("SELECT * FROM `gallery_images` ORDER BY `id` DESC")->fetchAll();

$adminTitle = 'Photo Gallery Manager | MIDHILA VOYAGES';
$adminHeading = 'Travel Photo Gallery';
require_once __DIR__ . '/includes/admin-header.php';
?>

<div style="display:grid;grid-template-columns:1fr 2fr;gap:28px;">
  
  <!-- Add / Edit Photo Form -->
  <div class="admin-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;border-bottom:1px solid var(--admin-border);padding-bottom:8px;">
      <h3 style="font-size:1.15rem;color:var(--admin-navy);"><?= $editPhoto ? 'Edit Photo' : 'Add New Photo' ?></h3>
      <?php if ($editPhoto): ?>
        <a href="<?= ADMIN_URL ?>/gallery.php" style="font-size:0.8rem;color:var(--admin-teal);text-decoration:underline;">+ Add New Instead</a>
      <?php endif; ?>
    </div>
    
    <form method="POST" action="" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <?php if ($editPhoto): ?>
        <input type="hidden" name="update_photo" value="1">
        <input type="hidden" name="photo_id" value="<?= (int)$editPhoto['id'] ?>">
      <?php else: ?>
        <input type="hidden" name="add_photo" value="1">
      <?php endif; ?>

      <div style="margin-bottom:16px;">
        <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--admin-navy);margin-bottom:6px;">Photo Title *</label>
        <input type="text" name="title" required placeholder="e.g. Dawn Mist over Munnar Tea Valley" 
               value="<?= e($editPhoto['title'] ?? '') ?>"
               style="width:100%;padding:10px 14px;border:1px solid var(--admin-border);border-radius:6px;font-size:0.95rem;">
      </div>

      <div style="margin-bottom:16px;">
        <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--admin-navy);margin-bottom:6px;">Location Name *</label>
        <input type="text" name="location_name" required placeholder="e.g. Munnar, Kerala" 
               value="<?= e($editPhoto['location_name'] ?? '') ?>"
               style="width:100%;padding:10px 14px;border:1px solid var(--admin-border);border-radius:6px;font-size:0.95rem;">
      </div>

      <div style="margin-bottom:16px;">
        <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--admin-navy);margin-bottom:6px;">Category</label>
        <?php $currentCat = $editPhoto['category'] ?? 'destinations'; ?>
        <select name="category" style="width:100%;padding:10px 14px;border:1px solid var(--admin-border);border-radius:6px;font-size:0.95rem;background:#FFF;">
          <option value="destinations" <?= $currentCat === 'destinations' ? 'selected' : '' ?>>Destinations</option>
          <option value="pilgrimages" <?= $currentCat === 'pilgrimages' ? 'selected' : '' ?>>Pilgrimages</option>
          <option value="nature" <?= $currentCat === 'nature' ? 'selected' : '' ?>>Nature &amp; Wildlife</option>
          <option value="luxury" <?= $currentCat === 'luxury' ? 'selected' : '' ?>>Luxury Stays</option>
          <option value="heritage" <?= $currentCat === 'heritage' ? 'selected' : '' ?>>Heritage &amp; Culture</option>
        </select>
      </div>

      <!-- Live Photo Preview -->
      <div style="display:flex;align-items:center;gap:14px;margin-bottom:14px;background:var(--admin-bg);padding:10px;border-radius:6px;border:1px solid var(--admin-border);">
        <img id="galleryPreview" src="<?= e(get_image_url($editPhoto['image_url'] ?? '')) ?>" alt="Preview" style="width:90px;height:65px;object-fit:cover;border-radius:4px;border:1px solid var(--admin-border);" onerror="this.src='<?= ASSETS_URL ?>/images/placeholder.svg';">
        <div>
          <div style="font-size:0.82rem;font-weight:600;color:var(--admin-navy);">Photo Preview</div>
          <div style="font-size:0.75rem;color:var(--admin-muted);" id="galleryPreviewStatus"><?= $editPhoto ? 'Current photo shown.' : 'No photo selected.' ?></div>
        </div>
      </div>

      <div style="background:var(--admin-bg);padding:14px;border-radius:6px;border:1px solid var(--admin-border);margin-bottom:20px;">
        <label style="display:block;font-size:0.8rem;font-weight:700;color:var(--admin-navy);margin-bottom:6px;">Upload Image File (JPG, PNG, WebP &le; 5MB)</label>
        <input type="file" name="image_file" id="galleryFileInput" accept="image/jpeg,image/png,image/webp" style="margin-bottom:10px;">
        
        <label style="display:block;font-size:0.8rem;font-weight:700;color:var(--admin-navy);margin-bottom:4px;">Or Image Path / External Web URL:</label>
        <input type="text" name="image_url" id="galleryUrlInput" placeholder="https://images.unsplash.com/... or uploads/..." 
               value="<?= e($editPhoto['image_url'] ?? '') ?>"
               style="width:100%;padding:8px 12px;border:1px solid var(--admin-border);border-radius:6px;font-size:0.85rem;">
      </div>

      <div style="display:flex;gap:10px;">
        <button type="submit" class="admin-btn admin-btn-primary" style="flex:1;">
          <?= $editPhoto ? 'Update Photo' : 'Upload to Gallery' ?>
        </button>
        <?php if ($editPhoto): ?>
          <a href="<?= ADMIN_URL ?>/gallery.php" class="admin-btn admin-btn-outline">Cancel</a>
        <?php endif; ?>
      </div>
    </form>
  </div>

  <!-- Photos Grid -->
  <div class="admin-card">
    <h3 style="font-size:1.15rem;color:var(--admin-navy);margin-bottom:16px;border-bottom:1px solid var(--admin-border);padding-bottom:8px;">All Photos (<?= count($photos) ?>)</h3>

    <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(180px, 1fr));gap:16px;">
      <?php foreach ($photos as $photo): ?>
        <div style="background:var(--admin-bg);border-radius:8px;overflow:hidden;border:1px solid var(--admin-border);position:relative;">
          <img src="<?= e(get_image_url($photo['image_url'])) ?>" alt="<?= e($photo['title']) ?>" style="width:100%;height:130px;object-fit:cover;" onerror="this.src='<?= ASSETS_URL ?>/images/placeholder.svg';">
          <div style="padding:10px;">
            <div style="font-weight:600;font-size:0.85rem;color:var(--admin-navy);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="<?= e($photo['title']) ?>"><?= e($photo['title']) ?></div>
            <div style="font-size:0.75rem;color:var(--admin-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= e($photo['location_name']) ?></div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:10px;padding-top:8px;border-top:1px dashed var(--admin-border);">
              <span class="badge badge-neutral" style="font-size:0.7rem;text-transform:capitalize;"><?= e($photo['category']) ?></span>
              <div style="display:flex;gap:8px;">
                <a href="<?= ADMIN_URL ?>/gallery.php?edit_id=<?= (int)$photo['id'] ?>" style="color:var(--admin-teal);font-size:0.75rem;font-weight:600;">Edit</a>
                <a href="<?= ADMIN_URL ?>/gallery.php?delete_id=<?= (int)$photo['id'] ?>" 
                   data-confirm-delete="Are you sure you want to delete this photo from the gallery?"
                   style="color:#EF4444;font-size:0.75rem;">Delete</a>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

</div>

<script>
(function() {
  const urlInput = document.getElementById('galleryUrlInput');
  const fileInput = document.getElementById('galleryFileInput');
  const previewImg = document.getElementById('galleryPreview');
  const previewStatus = document.getElementById('galleryPreviewStatus');
  const placeholder = '<?= ASSETS_URL ?>/images/placeholder.svg';

  if (urlInput && previewImg) {
    urlInput.addEventListener('input', function() {
      const val = this.value.trim();
      if (val) {
        let fullSrc = val;
        if (!val.startsWith('http://') && !val.startsWith('https://')) {
          fullSrc = '<?= BASE_URL ?>/' + val.replace(/^\/+/, '');
        }
        previewImg.src = fullSrc;
        previewStatus.textContent = 'Previewing image URL...';
      } else {
        previewImg.src = placeholder;
        previewStatus.textContent = 'No photo selected.';
      }
    });
  }

  if (fileInput && previewImg) {
    fileInput.addEventListener('change', function() {
      if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
          previewImg.src = e.target.result;
          previewStatus.textContent = 'Ready to upload: ' + fileInput.files[0].name;
        };
        reader.readAsDataURL(this.files[0]);
      }
    });
  }
})();
</script>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
