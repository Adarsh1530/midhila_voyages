<?php
/**
 * MIDHILA VOYAGES — Tour Package Editor with Day-by-Day Itinerary Manager
 */

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/database.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/csrf.php';

require_admin_login();

$db = getDB();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$package = null;
$itineraries = [];

if ($id > 0) {
    $stmt = $db->prepare("SELECT * FROM `packages` WHERE `id` = ? LIMIT 1");
    $stmt->execute([$id]);
    $package = $stmt->fetch();
    if (!$package) {
        set_flash_message('error', 'Package not found.');
        header('Location: ' . ADMIN_URL . '/packages.php');
        exit;
    }

    $itinStmt = $db->prepare("SELECT * FROM `package_itineraries` WHERE `package_id` = ? ORDER BY `day_number` ASC");
    $itinStmt->execute([$id]);
    $itineraries = $itinStmt->fetchAll();

    $imgStmt = $db->prepare("SELECT * FROM `package_images` WHERE `package_id` = ? ORDER BY `display_order` ASC, `id` ASC");
    $imgStmt->execute([$id]);
    $packageImages = $imgStmt->fetchAll();
} else {
    $packageImages = [];
}

// Fetch destinations and categories
$dests = $db->query("SELECT `id`, `name` FROM `destinations` ORDER BY `name` ASC")->fetchAll();
$cats = $db->query("SELECT `id`, `name` FROM `package_categories` ORDER BY `display_order` ASC")->fetchAll();

$adminTitle = ($package ? 'Edit ' . e($package['title']) : 'Create New Tour Package') . ' | MIDHILA VOYAGES';
$adminHeading = $package ? 'Edit Tour Package' : 'Create New Tour Package';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token()) {
        $errors[] = 'Security token expired. Please resubmit.';
    } else {
        $destId = (int)($_POST['destination_id'] ?? 0);
        $catId = (int)($_POST['category_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $slug = slugify(trim($_POST['slug'] ?? '') ?: $title);
        $subtitle = trim($_POST['subtitle'] ?? '');
        $days = max(1, (int)($_POST['duration_days'] ?? 1));
        $nights = max(0, (int)($_POST['duration_nights'] ?? 0));
        $shortDesc = trim($_POST['short_description'] ?? '');
        $overview = trim($_POST['overview'] ?? '');
        $isPriceOnRequest = !empty($_POST['is_price_on_request']) ? 1 : 0;
        $price = !empty($_POST['starting_price']) ? (float)$_POST['starting_price'] : null;
        $currency = trim($_POST['currency'] ?? 'INR');
        $inclusions = trim($_POST['inclusions'] ?? '');
        $exclusions = trim($_POST['exclusions'] ?? '');
        $seasonal = trim($_POST['seasonal_info'] ?? '');
        $altText = trim($_POST['alt_text'] ?? $title);
        $isFeatured = !empty($_POST['is_featured']) ? 1 : 0;
        $isPilgrimage = !empty($_POST['is_pilgrimage']) ? 1 : 0;
        $isPublished = !empty($_POST['is_published']) ? 1 : 0;
        $imageUrl = trim($_POST['image_url'] ?? '');

        if (empty($title) || $destId <= 0 || $catId <= 0) {
            $errors[] = 'Please fill in the package title, destination, and category.';
        }

        // Handle image upload if provided
        if (!empty($_FILES['image_file']['name'])) {
            $uploadResult = upload_image($_FILES['image_file'], 'packages', 5);
            if ($uploadResult['success']) {
                $imageUrl = $uploadResult['path'];
            } else {
                $errors[] = 'Image upload error: ' . $uploadResult['error'];
            }
        }

        if (empty($imageUrl) && !$package) {
            $errors[] = 'Featured image is required for tour packages.';
        }

        if (empty($errors)) {
            try {
                $db->beginTransaction();

                if ($package) {
                    $updPkg = $db->prepare("
                        UPDATE `packages` SET
                            `destination_id` = ?, `category_id` = ?, `title` = ?, `slug` = ?, `subtitle` = ?,
                            `duration_days` = ?, `duration_nights` = ?, `short_description` = ?, `overview` = ?,
                            `starting_price` = ?, `is_price_on_request` = ?, `currency` = ?,
                            `inclusions` = ?, `exclusions` = ?, `seasonal_info` = ?,
                            `featured_image` = ?, `alt_text` = ?,
                            `is_featured` = ?, `is_pilgrimage` = ?, `is_published` = ?
                        WHERE `id` = ?
                    ");
                    $updPkg->execute([
                        $destId, $catId, $title, $slug, $subtitle,
                        $days, $nights, $shortDesc, $overview,
                        $price, $isPriceOnRequest, $currency,
                        $inclusions, $exclusions, $seasonal,
                        $imageUrl ?: $package['featured_image'], $altText,
                        $isFeatured, $isPilgrimage, $isPublished,
                        $package['id']
                    ]);
                    $pkgId = $package['id'];
                    log_activity($_SESSION['admin_user_id'] ?? null, 'edit_package', "Updated package '{$title}'");
                } else {
                    $insPkg = $db->prepare("
                        INSERT INTO `packages` (
                            `destination_id`, `category_id`, `title`, `slug`, `subtitle`,
                            `duration_days`, `duration_nights`, `short_description`, `overview`,
                            `starting_price`, `is_price_on_request`, `currency`,
                            `inclusions`, `exclusions`, `seasonal_info`,
                            `featured_image`, `alt_text`,
                            `is_featured`, `is_pilgrimage`, `is_published`
                        ) VALUES (
                            ?, ?, ?, ?, ?,
                            ?, ?, ?, ?,
                            ?, ?, ?,
                            ?, ?, ?,
                            ?, ?,
                            ?, ?, ?
                        )
                    ");
                    $insPkg->execute([
                        $destId, $catId, $title, $slug, $subtitle,
                        $days, $nights, $shortDesc, $overview,
                        $price, $isPriceOnRequest, $currency,
                        $inclusions, $exclusions, $seasonal,
                        $imageUrl, $altText,
                        $isFeatured, $isPilgrimage, $isPublished
                    ]);
                    $pkgId = (int)$db->lastInsertId();
                    log_activity($_SESSION['admin_user_id'] ?? null, 'add_package', "Created package '{$title}'");
                }

                // Process Itineraries
                $delItin = $db->prepare("DELETE FROM `package_itineraries` WHERE `package_id` = ?");
                $delItin->execute([$pkgId]);

                $itTitles = $_POST['itin_title'] ?? [];
                $itDescs = $_POST['itin_desc'] ?? [];
                $itMeals = $_POST['itin_meals'] ?? [];
                $itStays = $_POST['itin_stay'] ?? [];

                $insItin = $db->prepare("
                    INSERT INTO `package_itineraries` (`package_id`, `day_number`, `title`, `description`, `meals_included`, `stay_location`)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");

                $dayCounter = 1;
                foreach ($itTitles as $idx => $itTitle) {
                    $itTitle = trim($itTitle);
                    $itDesc = trim($itDescs[$idx] ?? '');
                    if (!empty($itTitle) || !empty($itDesc)) {
                        $meals = trim($itMeals[$idx] ?? '');
                        $stay = trim($itStays[$idx] ?? '');
                        $insItin->execute([$pkgId, $dayCounter, $itTitle ?: ("Day " . $dayCounter), $itDesc, $meals, $stay]);
                        $dayCounter++;
                    }
                }

                // Process Deletion of Selected Supplementary Images
                if (!empty($_POST['delete_package_images']) && is_array($_POST['delete_package_images'])) {
                    $delIds = array_map('intval', $_POST['delete_package_images']);
                    $placeholders = implode(',', array_fill(0, count($delIds), '?'));
                    $delStmt = $db->prepare("DELETE FROM `package_images` WHERE `id` IN ($placeholders) AND `package_id` = ?");
                    $delIds[] = $pkgId;
                    $delStmt->execute($delIds);
                }

                // Process Multiple Gallery Uploads
                if (!empty($_FILES['gallery_files']['name']) && is_array($_FILES['gallery_files']['name'])) {
                    $insImg = $db->prepare("INSERT INTO `package_images` (`package_id`, `image_url`, `caption`, `display_order`) VALUES (?, ?, ?, ?)");
                    foreach ($_FILES['gallery_files']['name'] as $idx => $fName) {
                        if (!empty($fName) && isset($_FILES['gallery_files']['error'][$idx]) && $_FILES['gallery_files']['error'][$idx] === UPLOAD_ERR_OK) {
                            $singleFile = [
                                'name'     => $_FILES['gallery_files']['name'][$idx],
                                'type'     => $_FILES['gallery_files']['type'][$idx] ?? '',
                                'tmp_name' => $_FILES['gallery_files']['tmp_name'][$idx],
                                'error'    => $_FILES['gallery_files']['error'][$idx],
                                'size'     => $_FILES['gallery_files']['size'][$idx] ?? 0
                            ];
                            $upRes = upload_image($singleFile, 'packages', 5);
                            if ($upRes['success']) {
                                $insImg->execute([$pkgId, $upRes['path'], $title, 10]);
                            }
                        }
                    }
                }

                // Process Additional Gallery Image URLs (one per line)
                $extraUrls = trim($_POST['extra_gallery_urls'] ?? '');
                if (!empty($extraUrls)) {
                    $lines = preg_split('/[\r\n]+/', $extraUrls);
                    $insImg = $db->prepare("INSERT INTO `package_images` (`package_id`, `image_url`, `caption`, `display_order`) VALUES (?, ?, ?, ?)");
                    foreach ($lines as $line) {
                        $line = trim($line);
                        if (!empty($line)) {
                            $insImg->execute([$pkgId, $line, $title, 10]);
                        }
                    }
                }

                $db->commit();
                set_flash_message('success', "Package '{$title}' saved successfully.");
                header('Location: ' . ADMIN_URL . '/packages.php');
                exit;

            } catch (Exception $e) {
                if ($db->inTransaction()) $db->rollBack();
                error_log("Package save failed: " . $e->getMessage());
                $errors[] = 'Database operation failed: ' . $e->getMessage();
            }
        }
    }
}

require_once __DIR__ . '/includes/admin-header.php';
?>

<div style="max-width:960px;margin:0 auto;">
  
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
    <div>
      <h3 style="font-size:1.3rem;color:var(--admin-navy);"><?= $package ? 'Edit Package' : 'Create New Package' ?></h3>
      <p style="font-size:0.85rem;color:var(--admin-muted);">Configure package metadata, pricing, inclusions, and day-by-day itineraries.</p>
    </div>
    <a href="<?= ADMIN_URL ?>/packages.php" class="admin-btn admin-btn-outline">&larr; Back to Packages</a>
  </div>

  <?php if (!empty($errors)): ?>
    <div style="background:#FEF2F2;border-left:4px solid #EF4444;padding:12px 16px;border-radius:6px;color:#991B1B;font-size:0.9rem;margin-bottom:20px;">
      <?php foreach ($errors as $err): ?>
        <div>• <?= e($err) ?></div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <form method="POST" action="" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <!-- Section 1: Core Information -->
    <div class="admin-card">
      <h4 style="font-size:1.1rem;color:var(--admin-navy);margin-bottom:16px;border-bottom:1px solid var(--admin-border);padding-bottom:8px;">1. Basic Details</h4>

      <div style="margin-bottom:18px;">
        <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--admin-navy);margin-bottom:6px;">Package Title *</label>
        <input type="text" name="title" required placeholder="e.g. Kerala Serenade: Munnar Hills & Alleppey Waters" 
               value="<?= e($_POST['title'] ?? $package['title'] ?? '') ?>"
               style="width:100%;padding:10px 14px;border:1px solid var(--admin-border);border-radius:6px;font-size:0.95rem;">
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:18px;">
        <div>
          <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--admin-navy);margin-bottom:6px;">Destination *</label>
          <select name="destination_id" required style="width:100%;padding:10px 14px;border:1px solid var(--admin-border);border-radius:6px;font-size:0.95rem;background:#FFF;">
            <option value="">-- Choose Destination --</option>
            <?php foreach ($dests as $d): ?>
              <option value="<?= (int)$d['id'] ?>" <?= (($package['destination_id'] ?? 0) == $d['id']) ? 'selected' : '' ?>>
                <?= e($d['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--admin-navy);margin-bottom:6px;">Category *</label>
          <select name="category_id" required style="width:100%;padding:10px 14px;border:1px solid var(--admin-border);border-radius:6px;font-size:0.95rem;background:#FFF;">
            <option value="">-- Choose Category --</option>
            <?php foreach ($cats as $c): ?>
              <option value="<?= (int)$c['id'] ?>" <?= (($package['category_id'] ?? 0) == $c['id']) ? 'selected' : '' ?>>
                <?= e($c['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--admin-navy);margin-bottom:6px;">Slug (URL)</label>
          <input type="text" name="slug" placeholder="e.g. kerala-serenade-munnar" 
                 value="<?= e($_POST['slug'] ?? $package['slug'] ?? '') ?>"
                 style="width:100%;padding:10px 14px;border:1px solid var(--admin-border);border-radius:6px;font-size:0.95rem;">
        </div>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr 2fr;gap:16px;margin-bottom:18px;">
        <div>
          <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--admin-navy);margin-bottom:6px;">Duration (Days) *</label>
          <input type="number" name="duration_days" min="1" max="60" required 
                 value="<?= (int)($_POST['duration_days'] ?? $package['duration_days'] ?? 5) ?>"
                 style="width:100%;padding:10px 14px;border:1px solid var(--admin-border);border-radius:6px;font-size:0.95rem;">
        </div>
        <div>
          <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--admin-navy);margin-bottom:6px;">Duration (Nights) *</label>
          <input type="number" name="duration_nights" min="0" max="60" required 
                 value="<?= (int)($_POST['duration_nights'] ?? $package['duration_nights'] ?? 4) ?>"
                 style="width:100%;padding:10px 14px;border:1px solid var(--admin-border);border-radius:6px;font-size:0.95rem;">
        </div>
        <div>
          <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--admin-navy);margin-bottom:6px;">Subtitle / Tagline</label>
          <input type="text" name="subtitle" placeholder="e.g. Mist-covered tea hills and private houseboat cruise" 
                 value="<?= e($_POST['subtitle'] ?? $package['subtitle'] ?? '') ?>"
                 style="width:100%;padding:10px 14px;border:1px solid var(--admin-border);border-radius:6px;font-size:0.95rem;">
        </div>
      </div>

      <div style="margin-bottom:18px;">
        <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--admin-navy);margin-bottom:6px;">Short Description (Cards & Previews) *</label>
        <textarea name="short_description" rows="2" required placeholder="A concise 1-2 sentence hook..." 
                  style="width:100%;padding:10px 14px;border:1px solid var(--admin-border);border-radius:6px;font-size:0.95rem;"><?= e($_POST['short_description'] ?? $package['short_description'] ?? '') ?></textarea>
      </div>

      <div style="margin-bottom:18px;">
        <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--admin-navy);margin-bottom:6px;">Full Package Overview *</label>
        <textarea name="overview" rows="4" required placeholder="Detailed journey story and experiential introduction..." 
                  style="width:100%;padding:10px 14px;border:1px solid var(--admin-border);border-radius:6px;font-size:0.95rem;line-height:1.6;"><?= e($_POST['overview'] ?? $package['overview'] ?? '') ?></textarea>
      </div>
    </div>

    <!-- Section 2: Pricing, Inclusions & Media -->
    <div class="admin-card">
      <h4 style="font-size:1.1rem;color:var(--admin-navy);margin-bottom:16px;border-bottom:1px solid var(--admin-border);padding-bottom:8px;">2. Pricing &amp; Inclusions</h4>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:18px;">
        <div>
          <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--admin-navy);margin-bottom:6px;">Starting Price (₹ INR)</label>
          <input type="number" step="0.01" name="starting_price" placeholder="e.g. 24500" 
                 value="<?= e($_POST['starting_price'] ?? $package['starting_price'] ?? '') ?>"
                 style="width:100%;padding:10px 14px;border:1px solid var(--admin-border);border-radius:6px;font-size:0.95rem;">
        </div>
        <div style="display:flex;align-items:center;padding-top:24px;">
          <label style="display:flex;align-items:center;gap:8px;font-size:0.9rem;font-weight:600;cursor:pointer;">
            <input type="checkbox" name="is_price_on_request" value="1" <?= (!empty($package['is_price_on_request'])) ? 'checked' : '' ?>>
            <span>Show "Request a Quote" instead of price</span>
          </label>
        </div>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:18px;">
        <div>
          <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--admin-navy);margin-bottom:6px;">Inclusions (Comma-separated)</label>
          <textarea name="inclusions" rows="3" placeholder="4-star hotel stay, Daily breakfast & dinner, AC vehicle with driver..." 
                    style="width:100%;padding:10px 14px;border:1px solid var(--admin-border);border-radius:6px;font-size:0.9rem;"><?= e($_POST['inclusions'] ?? $package['inclusions'] ?? '') ?></textarea>
        </div>
        <div>
          <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--admin-navy);margin-bottom:6px;">Exclusions (Comma-separated)</label>
          <textarea name="exclusions" rows="3" placeholder="Airfare to destination, Monument entry tickets, Personal laundry..." 
                    style="width:100%;padding:10px 14px;border:1px solid var(--admin-border);border-radius:6px;font-size:0.9rem;"><?= e($_POST['exclusions'] ?? $package['exclusions'] ?? '') ?></textarea>
        </div>
      </div>

      <!-- Image Source Selection -->
      <div style="background:var(--admin-bg);padding:18px;border-radius:8px;border:1px solid var(--admin-border);margin-bottom:20px;">
        <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--admin-navy);margin-bottom:8px;">Featured Package Photograph *</label>
        
        <div style="display:flex;align-items:center;gap:16px;margin-bottom:14px;background:#FFF;padding:12px;border-radius:6px;border:1px solid var(--admin-border);">
          <img id="pkgImagePreview" src="<?= e(get_image_url($package['featured_image'] ?? '')) ?>" alt="Preview" style="width:100px;height:70px;object-fit:cover;border-radius:4px;border:1px solid var(--admin-border);" onerror="this.src='<?= ASSETS_URL ?>/images/placeholder.svg';">
          <div>
            <div style="font-size:0.85rem;font-weight:600;color:var(--admin-navy);">Featured Image Preview</div>
            <div style="font-size:0.75rem;color:var(--admin-muted);" id="pkgPreviewStatus"><?= !empty($package['featured_image']) ? 'Current image shown. Select a file or paste URL to replace.' : 'No image selected yet.' ?></div>
          </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
          <div>
            <span style="font-size:0.75rem;color:var(--admin-muted);display:block;margin-bottom:4px;">Upload Image File (JPG, PNG, WebP):</span>
            <input type="file" name="image_file" id="pkgImageFile" accept="image/jpeg,image/png,image/webp">
          </div>
          <div>
            <span style="font-size:0.75rem;color:var(--admin-muted);display:block;margin-bottom:4px;">Or Image Path / External Web URL:</span>
            <input type="text" name="image_url" id="pkgImageUrl" placeholder="https://images.unsplash.com/... or uploads/..." 
                   value="<?= e($_POST['image_url'] ?? $package['featured_image'] ?? '') ?>"
                   style="width:100%;padding:8px 12px;border:1px solid var(--admin-border);border-radius:6px;font-size:0.9rem;">
          </div>
        </div>

        <!-- Supplementary Package Gallery Images -->
        <div style="margin-top:20px;padding-top:16px;border-top:1px dashed var(--admin-border);">
          <label style="display:block;font-size:0.82rem;font-weight:700;color:var(--admin-navy);margin-bottom:6px;">Supplementary Gallery Images</label>
          <p style="font-size:0.78rem;color:var(--admin-muted);margin-bottom:12px;">Add additional photos showcased on the package detail page gallery.</p>

          <?php if (!empty($packageImages)): ?>
            <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(110px, 1fr));gap:12px;margin-bottom:14px;">
              <?php foreach ($packageImages as $pImg): ?>
                <div style="background:#FFF;padding:6px;border-radius:6px;border:1px solid var(--admin-border);text-align:center;">
                  <img src="<?= e(get_image_url($pImg['image_url'])) ?>" alt="" style="width:100%;height:65px;object-fit:cover;border-radius:4px;margin-bottom:4px;" onerror="this.src='<?= ASSETS_URL ?>/images/placeholder.svg';">
                  <label style="font-size:0.7rem;color:#EF4444;display:flex;align-items:center;justify-content:center;gap:4px;cursor:pointer;">
                    <input type="checkbox" name="delete_package_images[]" value="<?= (int)$pImg['id'] ?>">
                    <span>Delete</span>
                  </label>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div>
              <span style="font-size:0.75rem;color:var(--admin-muted);display:block;margin-bottom:4px;">Upload More Gallery Photos (Select Multiple):</span>
              <input type="file" name="gallery_files[]" multiple accept="image/jpeg,image/png,image/webp">
            </div>
            <div>
              <span style="font-size:0.75rem;color:var(--admin-muted);display:block;margin-bottom:4px;">Or Add Image URLs (One per line):</span>
              <textarea name="extra_gallery_urls" rows="2" placeholder="https://images.unsplash.com/...&#10;https://images.unsplash.com/..." style="width:100%;padding:6px 10px;border:1px solid var(--admin-border);border-radius:6px;font-size:0.82rem;"></textarea>
            </div>
          </div>
        </div>

      </div>

      <!-- Visibility Flags -->
      <div style="display:flex;gap:24px;flex-wrap:wrap;">
        <label style="display:flex;align-items:center;gap:8px;font-size:0.9rem;font-weight:600;cursor:pointer;">
          <input type="checkbox" name="is_published" value="1" <?= (!isset($package) || !empty($package['is_published'])) ? 'checked' : '' ?>>
          <span>Publish Package</span>
        </label>
        <label style="display:flex;align-items:center;gap:8px;font-size:0.9rem;font-weight:600;cursor:pointer;">
          <input type="checkbox" name="is_featured" value="1" <?= (!empty($package['is_featured'])) ? 'checked' : '' ?>>
          <span>Featured on Home</span>
        </label>
        <label style="display:flex;align-items:center;gap:8px;font-size:0.9rem;font-weight:600;cursor:pointer;">
          <input type="checkbox" name="is_pilgrimage" value="1" <?= (!empty($package['is_pilgrimage'])) ? 'checked' : '' ?>>
          <span>Pilgrimage Circuit</span>
        </label>
      </div>
    </div>

    <!-- Section 3: Day-by-Day Itinerary Builder -->
    <div class="admin-card">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;border-bottom:1px solid var(--admin-border);padding-bottom:8px;">
        <h4 style="font-size:1.1rem;color:var(--admin-navy);">3. Day-by-Day Itinerary Builder</h4>
        <button type="button" id="addDayBtn" class="admin-btn admin-btn-outline admin-btn-sm">+ Add Day</button>
      </div>

      <div id="itineraryContainer" style="display:flex;flex-direction:column;gap:18px;">
        <?php if (!empty($itineraries)): ?>
          <?php foreach ($itineraries as $idx => $itin): ?>
            <div class="itinerary-row" style="background:var(--admin-bg);padding:18px;border-radius:8px;border:1px solid var(--admin-border);">
              <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
                <strong style="color:var(--admin-navy);">Day <?= (int)$itin['day_number'] ?></strong>
                <button type="button" onclick="this.closest('.itinerary-row').remove()" style="color:#EF4444;font-size:0.8rem;background:none;border:none;cursor:pointer;">Remove Day</button>
              </div>
              <div style="margin-bottom:8px;">
                <input type="text" name="itin_title[]" value="<?= e($itin['title']) ?>" placeholder="Day Title (e.g. Arrival in Munnar & Spice Walk)" style="width:100%;padding:8px 12px;border:1px solid var(--admin-border);border-radius:4px;font-weight:600;">
              </div>
              <div style="margin-bottom:8px;">
                <textarea name="itin_desc[]" rows="2" placeholder="Describe the day's activities and sightseeing..." style="width:100%;padding:8px 12px;border:1px solid var(--admin-border);border-radius:4px;font-size:0.9rem;"><?= e($itin['description']) ?></textarea>
              </div>
              <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <input type="text" name="itin_meals[]" value="<?= e($itin['meals_included']) ?>" placeholder="Meals (e.g. Breakfast, Dinner)" style="width:100%;padding:6px 10px;border:1px solid var(--admin-border);border-radius:4px;font-size:0.85rem;">
                <input type="text" name="itin_stay[]" value="<?= e($itin['stay_location']) ?>" placeholder="Stay Location (e.g. Munnar Resort)" style="width:100%;padding:6px 10px;border:1px solid var(--admin-border);border-radius:4px;font-size:0.85rem;">
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="itinerary-row" style="background:var(--admin-bg);padding:18px;border-radius:8px;border:1px solid var(--admin-border);">
            <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
              <strong style="color:var(--admin-navy);">Day 1</strong>
            </div>
            <div style="margin-bottom:8px;">
              <input type="text" name="itin_title[]" placeholder="Day Title (e.g. Arrival &amp; Check-in)" style="width:100%;padding:8px 12px;border:1px solid var(--admin-border);border-radius:4px;font-weight:600;">
            </div>
            <div style="margin-bottom:8px;">
              <textarea name="itin_desc[]" rows="2" placeholder="Describe the day's activities..." style="width:100%;padding:8px 12px;border:1px solid var(--admin-border);border-radius:4px;font-size:0.9rem;"></textarea>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
              <input type="text" name="itin_meals[]" placeholder="Meals (e.g. Dinner)" style="width:100%;padding:6px 10px;border:1px solid var(--admin-border);border-radius:4px;font-size:0.85rem;">
              <input type="text" name="itin_stay[]" placeholder="Stay Location" style="width:100%;padding:6px 10px;border:1px solid var(--admin-border);border-radius:4px;font-size:0.85rem;">
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <div style="display:flex;gap:12px;margin-bottom:40px;">
      <button type="submit" class="admin-btn admin-btn-primary" style="padding:12px 28px;font-size:1rem;">
        <span><?= $package ? 'Update Tour Package' : 'Publish Tour Package' ?></span>
      </button>
      <a href="<?= ADMIN_URL ?>/packages.php" class="admin-btn admin-btn-outline" style="padding:12px 20px;">Cancel</a>
    </div>

  </form>
</div>

<script>
document.getElementById('addDayBtn').addEventListener('click', () => {
  const container = document.getElementById('itineraryContainer');
  const count = container.querySelectorAll('.itinerary-row').length + 1;
  const row = document.createElement('div');
  row.className = 'itinerary-row';
  row.style = 'background:var(--admin-bg);padding:18px;border-radius:8px;border:1px solid var(--admin-border);';
  row.innerHTML = `
    <div style="display:flex;justify-content:space-between;margin-bottom:8px;">
      <strong style="color:var(--admin-navy);">Day ${count}</strong>
      <button type="button" onclick="this.closest('.itinerary-row').remove()" style="color:#EF4444;font-size:0.8rem;background:none;border:none;cursor:pointer;">Remove Day</button>
    </div>
    <div style="margin-bottom:8px;">
      <input type="text" name="itin_title[]" placeholder="Day ${count} Title" style="width:100%;padding:8px 12px;border:1px solid var(--admin-border);border-radius:4px;font-weight:600;">
    </div>
    <div style="margin-bottom:8px;">
      <textarea name="itin_desc[]" rows="2" placeholder="Activities and sightseeing details..." style="width:100%;padding:8px 12px;border:1px solid var(--admin-border);border-radius:4px;font-size:0.9rem;"></textarea>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
      <input type="text" name="itin_meals[]" placeholder="Meals" style="width:100%;padding:6px 10px;border:1px solid var(--admin-border);border-radius:4px;font-size:0.85rem;">
      <input type="text" name="itin_stay[]" placeholder="Stay Location" style="width:100%;padding:6px 10px;border:1px solid var(--admin-border);border-radius:4px;font-size:0.85rem;">
    </div>
  `;
  container.appendChild(row);
});

// Live Featured Image Preview
(function() {
  const urlInput = document.getElementById('pkgImageUrl');
  const fileInput = document.getElementById('pkgImageFile');
  const previewImg = document.getElementById('pkgImagePreview');
  const previewStatus = document.getElementById('pkgPreviewStatus');
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
        previewStatus.textContent = 'No image selected.';
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
